<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * PHP 7.2–compatible, uses snake_case variables and "thisCase" for class name.
 * Manages "Custom E2E" tests by simply calling each plugin's "npm run qit-e2e"
 * and handling shared setups, DB snapshots, etc.
 */
class SpecE2ETestRunner {
	/**
	 * @var Docker
	 */
	protected $docker;

	/**
	 * @var LocalTestRunNotifier
	 */
	protected $local_test_run_notifier;

	protected $test_result;

	/**
	 * Constructor
	 *
	 * @param Docker $docker
	 * @param LocalTestRunNotifier $local_test_run_notifier
	 */
	public function __construct( Docker $docker, LocalTestRunNotifier $local_test_run_notifier ) {
		$this->docker                  = $docker;
		$this->local_test_run_notifier = $local_test_run_notifier;
	}

	/**
	 * Main entry point that coordinates all custom E2E tests for the environment.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param SymfonyStyle $io
	 * @param bool $up_only
	 *
	 * @return int
	 */
	public function run_custom_e2e_tests( $env_info, $io, $up_only ) {
		$this->local_test_run_notifier->notify_test_started(
			$env_info->sut_id,
			$env_info->woo_version,
			$env_info,
			$env_info->is_development_build,
			$env_info->notify
		);

		// Initialize test-result tracking
		$test_result       = TestResult::init_from( $env_info );
		$this->test_result = $test_result;
		$exit_code         = Command::SUCCESS;

		// Basic check that we have a tests array
		if ( empty( $env_info->tests ) || ! is_array( $env_info->tests ) ) {
			$io->error( 'No test definitions found in $env_info->tests.' );

			return Command::FAILURE;
		}

		// 1) Run shared-setup across all items that have action=bootstrap or action=test
		$this->run_shared_setup( $env_info, $io );

		if ( $up_only ) {
			App::make( Output::class )->writeln( '' );

			$question = new Question( '<comment>Environment ready. Press "Enter" when you are done to terminate it.</comment>' );
			$question->setValidator( function ( $answer ) {
				return $answer;
			} );
			( new QuestionHelper() )->ask( App::make( InputInterface::class ), App::make( Output::class ), $question );

			return Command::SUCCESS;
		}

		// 2) Snapshot DB
		$this->db_export( $env_info, $io );

		// 3) For each item whose action = test
		$testable_items = array_filter( $env_info->tests, function ( $item ) {
			return isset( $item['action'] ) && $item['action'] === Extension::ACTIONS['test'];
		} );

		foreach ( $testable_items as $test_item ) {
			$code = $this->run_single_plugin_tests( $env_info, $test_item, $io, $test_result );
			if ( $code !== Command::SUCCESS ) {
				$exit_code = $code;
			}
		}

		// 4) Shared teardown
		$this->run_shared_teardown( $env_info, $io );

		// 5) Merge CTRF + Allure from all plugins
		$this->merge_results( $env_info, $io, $test_result );

		// 6) Upload results to QIT Manager
		try {
			[ $reportUrl, $override_exit ] = $this->local_test_run_notifier->notify_test_finished( $test_result );

			if ( $override_exit !== null ) {
				$exit_code = $override_exit;
			}
			$io->writeln( "<info>Raw Allure results have been uploaded. View final report at: {$reportUrl}</info>" );
		} catch ( \Exception $e ) {
			$io->error( 'Could not finalize results to QIT Manager: ' . $e->getMessage() );
			$exit_code = Command::FAILURE;
		}

		return $exit_code;
	}

	/**
	 * Runs any shared-setup.* (sh|php|js) script for items whose action is bootstrap or test.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param SymfonyStyle $io
	 */
	protected function run_shared_setup( $env_info, $io ) {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if ( $test_item['action'] !== Extension::ACTIONS['bootstrap']
			     && $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			$this->run_script_if_exists( $env_info, $test_item, 'shared-setup.sh', 'Shared Setup', $io );
		}
	}

	/**
	 * Exports (snapshots) the DB so we can restore it before each plugin test.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param SymfonyStyle $io
	 */
	protected function db_export( $env_info, $io ) {
		$io->writeln( '<info>[db export]</info>' );
		$this->docker->run_inside_docker( $env_info, [ 'wp', 'db', 'export', '/qit/snapshot.sql' ] );
	}

	/**
	 * Restores DB, runs the plugin's local setups, calls `npm run qit-e2e`, runs teardown,
	 * and copies artifacts out.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param array $test_item
	 * @param SymfonyStyle $io
	 * @param TestResult $test_result
	 *
	 * @return int
	 */
	protected function run_single_plugin_tests( $env_info, $test_item, $io, $test_result ) {
		$slug = isset( $test_item['slug'] ) ? $test_item['slug'] : 'unknown';

		// 1) Restore DB
		$io->writeln( '<comment>[db import] for ' . $slug . '</comment>' );
		$this->docker->run_inside_docker( $env_info, [ 'wp', 'db', 'import', '/qit/snapshot.sql' ] );

		// 2) plugin-specific setup
		// CHANGED: Instead of "Plugin Setup (Shell)", pass "Isolated Setup"
		$this->run_script_if_exists( $env_info, $test_item, 'setup.sh', 'Isolated Setup', $io );

		// 3) "npm install" (if needed) and "npm run qit-e2e" on the host
		$host_path = isset( $test_item['path_in_host'] ) ? $test_item['path_in_host'] : '';
		$io->section( "Running 'npm install && npm run qit-e2e' on host for plugin: " . $slug );

		$code = Command::SUCCESS;

		try {
			// (Optional logic for npm install only if node_modules is absent…)
			$io->text( "No 'node_modules' found in {$host_path}, running npm install..." );
			$install_process = new Process( [ 'npm', 'install' ], $host_path );
			$install_exit    = $install_process->run( function ( $type, $buffer ) use ( $io ) {
				if ( $type === Process::ERR ) {
					$io->write( $buffer, false, OutputInterface::OUTPUT_RAW );
				} else {
					$io->write( $buffer );
				}
			} );

			if ( $install_exit !== 0 ) {
				throw new \RuntimeException(
					"npm install failed:\n" . $install_process->getErrorOutput()
				);
			}

			// Build the full command: `npm run qit-e2e -- <runnerArgs>`
			$test_cmd = [ 'npm', 'run', 'qit-e2e' ];
			if ( ! empty( $env_info->runner_args ) ) {
				$test_cmd[] = '--';
				$test_cmd   = array_merge( $test_cmd, $env_info->runner_args );
			}

			$test_process = new Process( $test_cmd, $host_path );
			$test_process->setEnv( [
				'IS_QIT'       => 'true',
				'QIT_SITE_URL' => $env_info->site_url,
			] );
			$test_exit = $test_process->run( function ( $type, $buffer ) use ( $io ) {
				if ( $type === Process::ERR ) {
					$io->write( $buffer, false, OutputInterface::OUTPUT_RAW );
				} else {
					$io->write( $buffer );
				}
			} );

			if ( $test_exit !== 0 ) {
				throw new \RuntimeException(
					"npm run qit-e2e failed:\n" . $test_process->getErrorOutput()
				);
			}
		} catch ( \Exception $e ) {
			$io->error( "Plugin $slug E2E test error on host: " . $e->getMessage() );
			$code = Command::FAILURE;
		}

		// 4) plugin teardown
		$this->run_script_if_exists( $env_info, $test_item, 'teardown.sh', 'Plugin Teardown', $io );

		// 5) Collect artifacts
		$this->collect_plugin_artifacts( $test_item, $test_result, $io );

		return $code;
	}

	/**
	 * Runs any shared-teardown.* scripts (sh|php|js) for items with action=bootstrap or test.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param SymfonyStyle $io
	 */
	protected function run_shared_teardown( $env_info, $io ) {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if ( $test_item['action'] !== Extension::ACTIONS['bootstrap']
			     && $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}
			$this->run_script_if_exists( $env_info, $test_item, 'shared-teardown.sh', 'Shared Teardown', $io );
		}
	}

	protected function merge_results( $env_info, $io, $test_result ) {
		$io->writeln( '<info>Merging CTRF & Allure results on the host...</info>' );

		$ctrf_dir        = $test_result->get_results_dir() . '/ctrf';
		$allure_root_dir = $test_result->get_results_dir() . '/allure';

		$ctrf_exists = is_dir( $ctrf_dir ) && count( glob( $ctrf_dir . '/*.json' ) ) > 0;

		$allure_exists = false;
		if ( is_dir( $allure_root_dir ) ) {
			$json_files = glob( $allure_root_dir . '/**/*.json' );
			if ( ! empty( $json_files ) ) {
				$allure_exists = true;
			}
		}

		if ( ! $ctrf_exists && ! $allure_exists ) {
			$io->writeln( '<comment>No CTRF or Allure data found to merge. Skipping...</comment>' );

			return;
		}

		$qit_dir     = Config::get_qit_dir();
		$ctrf_path   = $qit_dir . '/node_modules/.bin/ctrf';
		$allure_path = $qit_dir . '/node_modules/.bin/allure';

		// 2) CTRF merge
		if ( $ctrf_exists ) {
			$io->writeln( '<info>CTRF JSON found. Ensuring CTRF is installed...</info>' );

			if ( ! ( is_file( $ctrf_path ) && is_executable( $ctrf_path ) ) ) {
				$io->writeln( "<comment>No ctrf binary found at $ctrf_path. Attempting npm install ctrf...</comment>" );
				$install_ctrf = new Process( [ 'npm', 'install', '--prefix', $qit_dir, 'ctrf' ], $qit_dir );
				$install_ctrf->setTimeout( 300 );
				$install_ctrf->run();

				if ( ! $install_ctrf->isSuccessful() ) {
					throw new \RuntimeException(
						sprintf(
							'Failed to install CTRF. NPM error: %s',
							$install_ctrf->getErrorOutput()
						)
					);
				}
				if ( ! ( is_file( $ctrf_path ) && is_executable( $ctrf_path ) ) ) {
					throw new \RuntimeException(
						sprintf(
							'CTRF was installed, but still no executable at %s',
							$ctrf_path
						)
					);
				}
				$io->writeln( "<info>CTRF installed successfully at $ctrf_path</info>" );
			}

			$io->writeln( '<info>Merging CTRF JSON results...</info>' );
			$ctrf_proc = new Process( [ $ctrf_path, 'merge', $ctrf_dir ] );
			$ctrf_proc->setTimeout( 300 );
			$ctrf_proc->run();

			if ( ! $ctrf_proc->isSuccessful() ) {
				throw new \RuntimeException(
					sprintf(
						'Failed to merge CTRF results. Error: %s',
						$ctrf_proc->getErrorOutput()
					)
				);
			}
			$io->writeln( '<info>CTRF merge completed successfully.</info>' );

			$merged_file = $test_result->get_results_dir() . '/ctrf/ctrf-report.json';
			if ( file_exists( $merged_file ) ) {
				$this->post_process_ctrf_json( $merged_file );
				$io->writeln( "<info>Post-processed merged CTRF: {$merged_file}</info>" );
			} else {
				$io->warning( "No merged CTRF file found at {$merged_file}." );
			}
		}

		// 3) Allure merge & generate (if present)
		if ( $allure_exists ) {
			// In your scenario, you upload raw Allure data and handle merges in a remote pipeline.
			$io->writeln( '<info>Allure data found (raw results will be uploaded)...</info>' );
		}
	}

	/**
	 * Reads a CTRF JSON file and ensures there's always a "phase" in "extra",
	 * modifies test "name" if you want it more friendly, etc.
	 *
	 * @param string $ctrf_file
	 */
	protected function post_process_ctrf_json( string $ctrf_file ): void {
		if ( ! file_exists( $ctrf_file ) ) {
			return;
		}
		$raw = file_get_contents( $ctrf_file );
		if ( ! $raw ) {
			return;
		}
		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			return;
		}

		if ( empty( $data['results']['tests'] ) || ! is_array( $data['results']['tests'] ) ) {
			return;
		}

		foreach ( $data['results']['tests'] as &$test ) {
			if ( empty( $test['extra'] ) || ! is_array( $test['extra'] ) ) {
				$test['extra'] = [];
			}
			if ( empty( $test['extra']['phase'] ) ) {
				$test['extra']['phase'] = 'Test';
			}
			$phase = strtolower( $test['extra']['phase'] );

			if ( empty( $test['extra']['pluginSlug'] ) ) {
				$test['extra']['pluginSlug'] = '';
			}
			$slug = $test['extra']['pluginSlug'];

			// Optionally alter the "name" based on the phase
			if ( $phase === 'shared setup' ) {
				$test['name'] = "Shared Setup of {$slug}";
			} elseif ( $phase === 'isolated setup' ) {
				$test['name'] = "Isolated Setup of {$slug}";
			} elseif ( $phase === 'teardown' ) {
				$test['name'] = "Teardown of {$slug}";
			}
		}
		unset( $test );

		file_put_contents( $ctrf_file, json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Writes the snippet to a temporary file and merges it into the final CTRF directory.
	 *
	 * @param array $snippet
	 * @param string $ctrf_dir
	 * @param SymfonyStyle $io
	 */
	protected function merge_ctrf_snippet( array $snippet, $ctrf_dir, SymfonyStyle $io ) {
		if ( ! is_dir( $ctrf_dir ) ) {
			@mkdir( $ctrf_dir, 0755, true );
		}

		$temp_file = tempnam( sys_get_temp_dir(), 'ctrf_step_' ) . '.json';
		file_put_contents( $temp_file, json_encode( $snippet, JSON_PRETTY_PRINT ) );

		$partial_name = basename( $temp_file );
		$destination  = rtrim( $ctrf_dir, '/' ) . '/' . $partial_name;
		rename( $temp_file, $destination );

		$qit_dir   = Config::get_qit_dir();
		$ctrf_path = $qit_dir . '/node_modules/.bin/ctrf';

		if ( ! file_exists( $ctrf_path ) ) {
			$io->warning( "CTRF binary not found at $ctrf_path. Make sure it’s installed." );

			return;
		}

		$merge_cmd = new Process( [ $ctrf_path, 'merge', $ctrf_dir ] );
		$merge_cmd->setTimeout( 120 );
		$merge_code = $merge_cmd->run();

		if ( $merge_code !== 0 ) {
			$io->error( "Failed to merge CTRF results:\n" . $merge_cmd->getErrorOutput() );
		} else {
			$io->writeln( "<info>Merged partial CTRF snippet into $ctrf_dir successfully.</info>" );
		}
	}

	/**
	 * Executes a command inside Docker, capturing stdout/stderr. Returns an array with:
	 * ['exit_code', 'stdout', 'stderr', 'start', 'stop', 'duration'].
	 *
	 * @param E2EEnvInfo $env_info
	 * @param array $command_args
	 *
	 * @return array
	 */
	protected function run_command_and_capture( $env_info, array $command_args ) {
		$start_time = microtime( true );

		try {
			$output    = $this->docker->run_inside_docker( $env_info, $command_args, [], null, 300, 'php', true );
			$exit_code = 0;
		} catch ( \Exception $e ) {
			$output    = $e->getMessage();
			$exit_code = 1;
		}

		$stop_time   = microtime( true );
		$duration_ms = (int) round( ( $stop_time - $start_time ) * 1000 );

		return [
			'exit_code' => $exit_code,
			'stdout'    => explode( "\n", $output ),
			'stderr'    => [],
			'start'     => $start_time,
			'stop'      => $stop_time,
			'duration'  => $duration_ms,
		];
	}

	/**
	 * Builds a minimal CTRF JSON structure for a single script-run (like setup.sh) so it appears in final results.
	 *
	 * @param string $test_name
	 * @param array $capture
	 * @param string $phase
	 * @param string $plugin_slug
	 *
	 * @return array
	 */
	protected function build_ctrf_snippet( $test_name, $capture, $phase = '', $plugin_slug = '' ) {
		$start_epoch = (int) $capture['start'];
		$stop_epoch  = (int) $capture['stop'];
		$status      = ( $capture['exit_code'] === 0 ) ? 'passed' : 'failed';

		return [
			'$schema'      => 'http://json-schema.org/draft-07/schema#',
			'reportFormat' => 'CTRF',
			'specVersion'  => '1.0.0',
			'reportId'     => uniqid( 'test-step-', true ),
			'timestamp'    => date( 'c' ),
			'generatedBy'  => 'QIT_SpecE2ETestRunner',

			'results' => [
				'tool'    => [
					'name'    => 'SpecE2ERunner',
					'version' => '1.0.0',
				],
				'summary' => [
					'tests'   => 1,
					'passed'  => ( $status === 'passed' ) ? 1 : 0,
					'failed'  => ( $status === 'failed' ) ? 1 : 0,
					'skipped' => 0,
					'pending' => 0,
					'other'   => 0,
					'suites'  => 1,
					'start'   => $start_epoch,
					'stop'    => $stop_epoch,
				],
				'tests'   => [
					[
						'name'     => $test_name,
						'status'   => $status,
						'duration' => $capture['duration'],
						'start'    => $start_epoch,
						'stop'     => $stop_epoch,
						'suite'    => $plugin_slug,
						'extra'    => [
							'phase'      => $phase,
							'pluginSlug' => $plugin_slug,
						],
						'stdout'   => $capture['stdout'],
						'stderr'   => $capture['stderr'],
					]
				],
			],
		];
	}

	/**
	 * If a script (like 'setup.sh') exists, run it in Docker and record the CTRF snippet.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param array $test_item
	 * @param string $script_name
	 * @param string $phase e.g. "Shared Setup", "Isolated Setup", "Plugin Teardown (Shell)", etc.
	 * @param SymfonyStyle $io
	 */
	protected function run_script_if_exists( $env_info, $test_item, $script_name, $phase, $io ) {
		$plugin_slug   = isset( $test_item['slug'] ) ? $test_item['slug'] : 'unknown';
		$host_path     = isset( $test_item['path_in_host'] ) ? $test_item['path_in_host'] : '';
		$docker_dir    = isset( $test_item['path_in_php_container'] ) ? $test_item['path_in_php_container'] : '';
		$possible_file = rtrim( $host_path, '/' ) . '/bootstrap/' . $script_name;

		if ( ! file_exists( $possible_file ) ) {
			return; // script not present, skip.
		}

		$test_title = "{$phase} bash script from {$plugin_slug}";

		// Let the operator see what's happening:
		$io->writeln( "<info>{$test_title}</info>" );

		// Actually run the script inside Docker
		$command_to_run = [
			'bash',
			'-c',
			sprintf( 'cd %s/bootstrap && bash %s', $docker_dir, $script_name )
		];
		$capture        = $this->run_command_and_capture( $env_info, $command_to_run );

		// Then record it as a partial CTRF snippet
		$ctrf_snippet = $this->build_ctrf_snippet( $test_title, $capture, $phase, $plugin_slug );

		// Merge it into the CTRF results folder
		$ctrf_dir = $this->test_result->get_results_dir() . '/ctrf';
		$this->merge_ctrf_snippet( $ctrf_snippet, $ctrf_dir, $io );
	}

	/**
	 * Copies plugin's ./results/ctrf.json and ./results/allure/ from host to final results directory.
	 *
	 * @param array $test_item
	 * @param TestResult $test_result
	 * @param SymfonyStyle $io
	 */
	protected function collect_plugin_artifacts( $test_item, $test_result, $io ) {
		$results_dir = $test_result->get_results_dir();

		if ( ! is_dir( $results_dir ) ) {
			@mkdir( $results_dir, 0755, true );
		}
		if ( ! is_dir( $results_dir . '/ctrf' ) ) {
			@mkdir( $results_dir . '/ctrf', 0755, true );
		}
		if ( ! is_dir( $results_dir . '/allure' ) ) {
			@mkdir( $results_dir . '/allure', 0755, true );
		}

		if ( file_exists( $test_item['path_in_host'] . '/results/ctrf.json' ) ) {
			copy(
				$test_item['path_in_host'] . '/results/ctrf.json',
				$results_dir . '/ctrf/' . $test_item['slug'] . '.json'
			);
		} else {
			$io->warning( 'Plugin ' . $test_item['slug'] . ' did not produce results/ctrf.json' );
		}

		if ( file_exists( $test_item['path_in_host'] . '/results/allure' ) ) {
			$fs = App::make( Filesystem::class );
			$fs->mirror(
				$test_item['path_in_host'] . '/results/allure',
				$results_dir . '/allure/' . $test_item['slug']
			);
		}
	}
}