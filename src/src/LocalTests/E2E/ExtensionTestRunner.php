<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Handles per-plugin test running (DB restore, local setup, npm calls, etc.).
 */
class ExtensionTestRunner {
	/**
	 * @var Docker
	 */
	protected $docker;

	/**
	 * @var TestResult|null
	 */
	protected $test_result = null;

	/**
	 * Constructor
	 *
	 * @param Docker $docker
	 */
	public function __construct( Docker $docker ) {
		$this->docker = $docker;
	}

	/**
	 * Set the TestResult instance once it's available
	 *
	 * @param TestResult $test_result
	 */
	public function set_test_result( TestResult $test_result ) {
		$this->test_result = $test_result;
	}

	/**
	 * Restores DB, runs the plugin's local setups, calls `npm run qit-e2e`, runs teardown,
	 * and copies artifacts out.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param array $test_item
	 * @param SymfonyStyle $io
	 *
	 * @return int
	 */
	public function run_single_plugin_tests( E2EEnvInfo $env_info, array $test_item, SymfonyStyle $io, bool $is_first ) {
		$slug = $test_item['slug'] ?? 'unknown';

		if ( ! $is_first ) {
			// 1) Restore DB
			$io->writeln( '<comment>[db import] for ' . $slug . '</comment>' );
			$this->docker->run_inside_docker( $env_info, [ 'wp', 'db', 'import', '/qit/snapshot.sql' ] );
		}

		// 2) plugin-specific setup
		$this->run_script_if_exists( $env_info, $test_item, 'setup.sh', 'Isolated Setup', $io );

		// 3) "npm install" (if needed) and "npm run qit-e2e" on the host
		$host_path = $test_item['path_in_host'] ?? '';
		$io->section( "Running 'npm run qit-e2e' on plugin: " . $slug );

		$code = Command::SUCCESS;

		try {
			if ( ! is_dir( $host_path . '/node_modules' ) ) {
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
			}

			// Build the full command: `npm run qit-e2e -- <runnerArgs>`.
			$test_cmd = [ 'npm', 'run', 'qit-e2e' ];
			if ( ! empty( $env_info->runner_args ) ) {
				$test_cmd[] = '--';
				$test_cmd   = array_merge( $test_cmd, $env_info->runner_args );
			}

			$docker_env_vars = App::getVar( 'QIT_DOCKER_ENV_VARS' ) ?: [];

			$test_process = new Process( $test_cmd, $host_path );
			$test_process->setEnv( array_merge( $docker_env_vars, [
				'IS_QIT'       => 'true',
				'QIT_SITE_URL' => $env_info->site_url,
			] ) );
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

		// 3) Immediately post-process CTRF if it exists, adding pluginSlug and phase = Test
		$ctrf_file = $host_path . '/results/ctrf.json';
		if ( file_exists( $ctrf_file ) ) {
			$this->add_extra_ctrf_data_to_extension_tests( $ctrf_file, $slug );
		}

		// 4) plugin teardown
		$this->run_script_if_exists( $env_info, $test_item, 'teardown.sh', 'Plugin Teardown', $io );

		// 5) Collect artifacts
		$this->collect_plugin_artifacts( $test_item, $io );

		return $code;
	}

	protected function add_extra_ctrf_data_to_extension_tests( string $ctrf_file, string $slug ) {
		if ( ! file_exists( $ctrf_file ) ) {
			return;
		}

		$data = json_decode( file_get_contents( $ctrf_file ), true );
		if ( ! is_array( $data ) ) {
			return;
		}

		if ( ! empty( $data['results']['tests'] ) && is_array( $data['results']['tests'] ) ) {
			foreach ( $data['results']['tests'] as &$test ) {
				if ( ! isset( $test['extra'] ) ) {
					$test['extra'] = [];
				}
				$test['extra']['pluginSlug'] = $slug;
				$test['extra']['phase']      = 'Test';
			}
		}

		file_put_contents( $ctrf_file, json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	/**
	 * If a script (like 'setup.sh' or 'shared-setup.sh') exists, run it in Docker and record the CTRF snippet.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param array $test_item
	 * @param string $script_name
	 * @param string $phase e.g. "Shared Setup", "Isolated Setup", "Plugin Teardown", etc.
	 * @param SymfonyStyle $io
	 */
	public function run_script_if_exists( $env_info, $test_item, $script_name, $phase, $io ) {
		if ( ! $this->test_result ) {
			// If for some reason it's called before set_test_result(), skip
			return;
		}

		$plugin_slug   = $test_item['slug'] ?? 'unknown';
		$host_path     = $test_item['path_in_host'] ?? '';
		$docker_dir    = $test_item['path_in_php_container'] ?? '';
		$env_vars      = App::getVar( 'QIT_DOCKER_ENV_VARS' ) ?: [];
		$possible_file = rtrim( $host_path, '/' ) . '/bootstrap/' . $script_name;

		if ( ! file_exists( $possible_file ) ) {
			return; // script not present, skip.
		}

		$test_title = "{$phase} bash script from {$plugin_slug}";
		$io->writeln( "<info>{$test_title}</info>" );

		// Actually run the script inside Docker.
		$command_to_run = [
			'bash',
			'-c',
			sprintf( 'cd %s/bootstrap && bash %s', $docker_dir, $script_name ),
		];
		$capture        = $this->run_command_and_capture( $env_info, $command_to_run, $env_vars );

		// Then record it as a partial CTRF snippet.
		$ctrf_snippet = $this->build_ctrf_snippet( $test_title, $capture, $phase, $plugin_slug, $script_name );

		// Merge it into the CTRF results folder.
		$ctrf_dir = $this->test_result->get_results_dir() . '/ctrf';
		$this->merge_ctrf_snippet( $ctrf_snippet, $ctrf_dir, $io );
	}

	/**
	 * Copies plugin's ./results/ctrf.json and ./results/allure/ from host to final results directory.
	 *
	 * @param array $test_item
	 * @param SymfonyStyle $io
	 */
	protected function collect_plugin_artifacts( $test_item, $io ) {
		if ( ! $this->test_result ) {
			return;
		}

		$results_dir = $this->test_result->get_results_dir();

		if ( ! is_dir( $results_dir ) ) {
			@mkdir( $results_dir, 0755, true );
		}
		if ( ! is_dir( $results_dir . '/ctrf' ) ) {
			@mkdir( $results_dir . '/ctrf', 0755, true );
		}
		if ( ! is_dir( $results_dir . '/allure' ) ) {
			@mkdir( $results_dir . '/allure', 0755, true );
		}

		$plugin_slug = $test_item['slug'] ?? 'unknown';

		if ( file_exists( $test_item['path_in_host'] . '/results/ctrf.json' ) ) {
			copy(
				$test_item['path_in_host'] . '/results/ctrf.json',
				$results_dir . '/ctrf/' . $plugin_slug . '.json'
			);
		} else {
			$io->warning( 'Plugin ' . $plugin_slug . ' did not produce results/ctrf.json' );
		}

		if ( file_exists( $test_item['path_in_host'] . '/results/allure' ) ) {
			$fs = App::make( Filesystem::class );
			$fs->mirror(
				$test_item['path_in_host'] . '/results/allure',
				$results_dir . '/allure/' . $plugin_slug
			);
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
	protected function run_command_and_capture( $env_info, array $command_args, array $env_vars = [] ) {
		$start_time = microtime( true );

		try {
			$output    = $this->docker->run_inside_docker( $env_info, $command_args, $env_vars, null, 300, 'php', true );
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
	protected function build_ctrf_snippet( $test_name, $capture, $phase = '', $plugin_slug = '', $file_path = '' ) {
		$start_epoch = (int) $capture['start'];
		$stop_epoch  = (int) $capture['stop'];
		$status      = ( $capture['exit_code'] === 0 ) ? 'passed' : 'failed';
		$suite       = 'bootstrap/' . basename( $file_path );

		return [
			'$schema'      => 'http://json-schema.org/draft-07/schema#',
			'reportFormat' => 'CTRF',
			'specVersion'  => '1.0.0',
			'reportId'     => uniqid( 'test-step-', true ),
			'timestamp'    => gmdate( 'c' ),
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
						'suite'    => $suite,
						'extra'    => [
							'phase'      => $phase,
							'pluginSlug' => $plugin_slug,
						],
						'stdout'   => $capture['stdout'],
						'stderr'   => $capture['stderr'],
					],
				],
			],
		];
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
		}
	}

	/**
	 * Reads a CTRF JSON file and ensures there's always a "phase" in "extra",
	 * modifies test "name" if you want it more friendly, etc.
	 *
	 * @param string $ctrf_file
	 */
	public function post_process_ctrf_json( string $ctrf_file ): void {
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

			// Optionally alter the "name" based on the phase.
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
}
