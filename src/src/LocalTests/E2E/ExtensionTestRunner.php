<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\IO\Output;
use RuntimeException;
use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use function QIT_CLI\banner;

/**
 * Handles per-plugin test flow:
 * - DB restore (except for the first plugin)
 * - Lifecycle setup scripts
 * - Running <test.command> -- <runnerArgs>
 * - Lifecycle teardown
 * - Copying artifacts (CTRF / Allure)
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

	public function __construct( Docker $docker ) {
		$this->docker = $docker;
	}

	/**
	 * Attach the shared TestResult instance so we can record partial CTRF data.
	 */
	public function set_test_result( TestResult $test_result ): void {
		$this->test_result = $test_result;
	}

	/**
	 * Runs all steps for a single plugin with action="test":
	 * - DB import (unless first)
	 * - lifecycle.setup scripts
	 * - test.command
	 * - lifecycle.teardown
	 * - Copies CTRF and Allure artifacts
	 *
	 * @param E2EEnvInfo $env_info
	 * @param array $test_item
	 * @param SymfonyStyle $io
	 * @param bool $is_first
	 *
	 * @return int Command::SUCCESS or Command::FAILURE
	 */
	public function run_single_plugin_tests(
		E2EEnvInfo $env_info,
		array $test_item,
		SymfonyStyle $io,
		bool $is_first
	): int {
		$plugin_dir = $test_item['path_in_host'] ?? '';
		$slug       = $test_item['slug'] ?? 'unknown';

		// Load config from $test_item['config'] (stored in orchestrator)
		$config = $test_item['config'] ?? [];

		if ( ! $plugin_dir || ! is_dir( $plugin_dir ) ) {
			$io->error( "Invalid or missing plugin directory for {$slug}" );

			return Command::FAILURE;
		}

		// 1) Restore DB if not the first plugin
		if ( ! $is_first ) {
			$io->writeln( "<info>[Restoring baseline DB state]</info>" );
			$this->docker->run_inside_docker( $env_info, [ 'wp', 'db', 'import', '/qit/snapshot.sql' ] );
		}

		// 2) lifecycle.setup scripts
		if ( ! empty( $config['lifecycle']['setup'] ) && is_array( $config['lifecycle']['setup'] ) ) {
			foreach ( $config['lifecycle']['setup'] as $script ) {
				$this->run_script_if_exists(
					$env_info,
					$test_item,
					rtrim( $plugin_dir, '/' ) . '/' . $script,
					'Isolated Setup',
					$io
				);
			}
		}

		// 3) Run test.command
		$code = $this->run_test_command( $env_info, $plugin_dir, $slug, $config, $io );

		// 4) If CTRF results exist, add pluginSlug
		$this->tag_ctrf_with_plugin_slug( $plugin_dir, $slug, $config );

		// 5) lifecycle.teardown
		if ( ! empty( $config['lifecycle']['teardown'] ) && is_array( $config['lifecycle']['teardown'] ) ) {
			foreach ( $config['lifecycle']['teardown'] as $script ) {
				$this->run_script_if_exists(
					$env_info,
					$test_item,
					rtrim( $plugin_dir, '/' ) . '/' . $script,
					'Isolated Teardown',
					$io
				);
			}
		}

		// 6) Copy artifacts
		$this->collect_plugin_artifacts( $test_item, $config, $io );

		return $code;
	}

	/**
	 * Runs "<test.command> -- <runnerArgs>" by splitting test.command into argv, then appending -- plus any runner_args.
	 * Return Command::SUCCESS or Command::FAILURE.
	 */
	protected function run_test_command(
		E2EEnvInfo $env_info,
		string $plugin_dir,
		string $slug,
		array $config,
		SymfonyStyle $io
	): int {
		if ( empty( $config['test']['command'] ) ) {
			$io->error( "No test.command defined for plugin: {$slug}" );

			return Command::FAILURE;
		}

		banner( $io, "Running tests for $slug", true, false, "🧪" );

		// Optionally do npm install if node_modules doesn't exist
		$node_modules = rtrim( $plugin_dir, '/' ) . '/node_modules';
		if ( ! is_dir( $node_modules ) ) {
			$io->text( "No 'node_modules' found in {$plugin_dir}, running npm install..." );
			$install_process = new Process( [ 'npm', 'install' ], $plugin_dir );
			$install_exit    = $install_process->run( function ( $type, $buffer ) use ( $io ) {
				$io->write( $buffer );
			} );
			if ( $install_exit !== 0 ) {
				$io->error( "npm install failed:\n" . $install_process->getErrorOutput() );

				return Command::FAILURE;
			}
		}

		// Build "<test.command> -- <runnerArgs>"
		$base_cmd = $config['test']['command'];
		$test_cmd = preg_split( '/\s+/', $base_cmd );
		if ( ! empty( $env_info->runner_args ) ) {
			$test_cmd[] = '--';
			$test_cmd   = array_merge( $test_cmd, $env_info->runner_args );
		}

		// Merge environment variables from multiple sources
		$docker_env_vars = App::getVar( 'QIT_DOCKER_ENV_VARS' ) ?: [];
		$all_env         = array_merge(
			$docker_env_vars,
			$env_info->env_vars ?? [],
			[
				'IS_QIT'       => 'true',
				'QIT_SITE_URL' => $env_info->site_url,
			]
		);

		$process = new Process( $test_cmd, $plugin_dir );
		$process->setEnv( $all_env );

		try {
			$exit_code = $process->run( function ( $type, $buffer ) use ( $io ) {
				$io->write( $buffer );
			} );
			if ( $exit_code !== 0 ) {
				throw new RuntimeException( "Test command failed:\n" . $process->getErrorOutput() );
			}
		} catch ( \Exception $e ) {
			$io->error( "Plugin {$slug} test error: " . $e->getMessage() );

			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}

	/**
	 * If test.results.ctrf is set, load that JSON and add pluginSlug/phase=Test to each test.
	 */
	protected function tag_ctrf_with_plugin_slug( string $plugin_dir, string $slug, array $config ): void {
		$ctrf_rel = $config['test']['results']['ctrf'] ?? '';
		if ( ! $ctrf_rel ) {
			return;
		}
		$ctrf_file = rtrim( $plugin_dir, '/' ) . '/' . ltrim( $ctrf_rel, '/' );
		if ( ! file_exists( $ctrf_file ) ) {
			return;
		}
		$data = @json_decode( file_get_contents( $ctrf_file ), true );
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
	 * Run a lifecycle script (setup or teardown) inside Docker, capturing partial CTRF.
	 */
	public function run_script_if_exists(
		E2EEnvInfo $env_info,
		array $test_item,
		string $script_path,
		string $phase,
		SymfonyStyle $io
	): void {
		if ( ! $this->test_result ) {
			return;
		}

		$plugin_slug = $test_item['slug'] ?? 'unknown';

		if ( ! file_exists( $script_path ) ) {
			return;
		}

		$io->writeln( "<info>{$phase} script for {$plugin_slug}</info>" );

		$docker_dir = $test_item['path_in_php_container'] ?? '';
		$relative   = str_replace( $test_item['path_in_host'], '', $script_path );

		$command_args = [
			'bash',
			'-c',
			sprintf(
				'cd %s && bash %s',
				escapeshellarg( dirname( rtrim( $docker_dir, '/' ) . '/' . ltrim( $relative, '/' ) ) ),
				escapeshellarg( basename( $script_path ) )
			),
		];

		$start_time = microtime( true );
		try {
			$output_instance = App::make( Output::class );
			$output = $this->docker->run_inside_docker( $env_info, $command_args, [], null, 300, 'php', false, function ( $type, $buffer ) use ( $output_instance ) {
				if ( ! is_scalar( $buffer ) ) {
					return;
				}

				if ( $type === 'err' ) {
					if ( $output_instance->isVerbose() ) {
						$output_instance->write( $buffer );
						return;
					}
				} else {
					$output_instance->write( $buffer );
				}
			} );
			$exit_code = 0;
		} catch ( \Exception $e ) {
			$output    = $e->getMessage();
			$exit_code = 1;
		}
		$stop_time   = microtime( true );
		$duration_ms = (int) round( ( $stop_time - $start_time ) * 1000 );

		$stdout = explode( "\n", $output );

		// Remove anything that starts with "Notice:".
		$stdout = [];

		$capture = [
			'exit_code' => $exit_code,
			'stdout'    => $stdout,
			'stderr'    => [],
			'start'     => $start_time,
			'stop'      => $stop_time,
			'duration'  => $duration_ms,
		];

		// Log the execution of this script as a CTRF test.
		$ctrf_dir = rtrim( $this->test_result->get_results_dir(), '/' ) . '/ctrf';

		$ctrf_snippet = $this->build_ctrf_snippet(
			"{$phase} - {$plugin_slug}",
			$capture,
			$phase,
			$plugin_slug,
			$script_path
		);
		$this->merge_ctrf_snippet( $ctrf_snippet, $ctrf_dir, $io );
	}

	protected function build_ctrf_snippet(
		string $test_name,
		array $capture,
		string $phase,
		string $plugin_slug,
		string $file_path
	): array {
		$start_epoch = (int) $capture['start'];
		$stop_epoch  = (int) $capture['stop'];
		$status      = ( $capture['exit_code'] === 0 ) ? 'passed' : 'failed';

		return [
			'$schema'      => 'http://json-schema.org/draft-07/schema#',
			'reportFormat' => 'CTRF',
			'specVersion'  => '1.0.0',
			'reportId'     => uniqid( 'test-step-', true ),
			'timestamp'    => gmdate( 'c' ),
			'generatedBy'  => 'QIT_SpecE2ETestRunner',
			'results'      => [
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
						'suite'    => 'bootstrap/' . basename( $file_path ),
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

	protected function merge_ctrf_snippet( array $snippet, string $ctrf_dir, SymfonyStyle $io ): void {
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
			$io->text( "CTRF binary not found at {$ctrf_path}. Installing now..." );
			$install_ctrf = new Process( [ 'npm', 'install', 'ctrf', '--no-save' ], $qit_dir );
			$install_code = $install_ctrf->run();
			if ( $install_code !== 0 ) {
				$io->error( "Failed to install CTRF:\n" . $install_ctrf->getErrorOutput() );

				return;
			}
		}
		$merge_cmd = new Process( [ $ctrf_path, 'merge', $ctrf_dir ] );
		$merge_cmd->setTimeout( 120 );

		$merge_code = $merge_cmd->run();
		if ( $merge_code !== 0 ) {
			$io->error( "Failed to merge CTRF results:\n" . $merge_cmd->getErrorOutput() );
		}
	}

	public function post_process_ctrf_json( string $ctrf_file ): void {
		if ( ! file_exists( $ctrf_file ) ) {
			return;
		}
		$raw  = file_get_contents( $ctrf_file );
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
			if ( empty( $test['extra']['pluginSlug'] ) ) {
				$test['extra']['pluginSlug'] = '';
			}
		}
		unset( $test );

		file_put_contents( $ctrf_file, json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	protected function collect_plugin_artifacts( array $test_item, array $config, SymfonyStyle $io ): void {
		if ( ! $this->test_result ) {
			return;
		}

		$slug        = $test_item['slug'] ?? 'unknown';
		$plugin_dir  = $test_item['path_in_host'] ?? '';
		$results_dir = $this->test_result->get_results_dir();

		@mkdir( $results_dir, 0755, true );
		@mkdir( $results_dir . '/ctrf', 0755, true );
		@mkdir( $results_dir . '/allure', 0755, true );

		$ctrf_rel   = $config['test']['results']['ctrf'] ?? '';
		$allure_rel = $config['test']['results']['allure'] ?? '';

		// CTRF
		if ( $ctrf_rel ) {
			$full_ctrf = rtrim( $plugin_dir, '/' ) . '/' . ltrim( $ctrf_rel, '/' );
			if ( file_exists( $full_ctrf ) ) {
				copy( $full_ctrf, "{$results_dir}/ctrf/{$slug}.json" );
			} else {
				$io->warning( "Plugin {$slug} did not produce {$ctrf_rel}." );
			}
		}

		// Allure
		if ( $allure_rel ) {
			$full_allure = rtrim( $plugin_dir, '/' ) . '/' . ltrim( $allure_rel, '/' );
			if ( is_dir( $full_allure ) ) {
				$fs = new Filesystem();
				$fs->mirror( $full_allure, "{$results_dir}/allure/{$slug}" );
			}
		}
	}
}