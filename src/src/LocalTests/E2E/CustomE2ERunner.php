<?php

namespace QIT_CLI\LocalTests\E2E;

use RuntimeException;
use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use function QIT_CLI\banner;

/**
 * Orchestrates custom E2E tests with a required qit-e2e.json (no fallback).
 * - Reads & caches each plugin’s qit-e2e.json into $this->pluginConfigs
 * - Runs shared lifecycle steps, plugin-specific steps, merges results, etc.
 */
class CustomE2ERunner {
	/**
	 * @var Docker
	 */
	protected $docker;

	/**
	 * @var LocalTestRunNotifier
	 */
	protected $local_test_run_notifier;

	/**
	 * @var TestResult|null
	 */
	protected $test_result = null;

	/**
	 * @var ExtensionTestRunner
	 */
	protected $extension_test_runner;

	/**
	 * @var array<string,array>  Holds parsed qit-e2e.json data keyed by plugin path
	 */
	private $plugin_configs = [];

	public function __construct(
		Docker $docker,
		LocalTestRunNotifier $local_test_run_notifier,
		ExtensionTestRunner $extension_test_runner
	) {
		$this->docker                  = $docker;
		$this->local_test_run_notifier = $local_test_run_notifier;
		$this->extension_test_runner   = $extension_test_runner;
	}

	/**
	 * Main entry point for custom E2E tests orchestration.
	 *
	 * @param E2EEnvInfo   $env_info
	 * @param SymfonyStyle $io
	 * @param bool         $up_only Whether to just bring up the environment without running tests.
	 *
	 * @return int
	 */
	public function run_custom_e2e_tests( E2EEnvInfo $env_info, SymfonyStyle $io, bool $up_only ) {
		// 1) Notify that a test run started
		$this->local_test_run_notifier->notify_test_started(
			isset( $env_info->sut['id'] ) ? $env_info->sut['id'] : 0,
			$env_info->woo,
			$env_info,
			$env_info->is_development_build,
			$env_info->notify
		);

		// 2) Create/attach a TestResult object
		$this->test_result = TestResult::init_from( $env_info );
		$this->extension_test_runner->set_test_result( $this->test_result );

		// 3) Basic checks
		if ( empty( $env_info->tests ) || ! is_array( $env_info->tests ) ) {
			$io->error( 'No test definitions found in $env_info->tests.' );

			return Command::FAILURE;
		}

		// 4) Preload each plugin’s qit-e2e.json and copy muPlugins
		foreach ( $env_info->tests as &$test_item ) {
			if ( empty( $test_item['path_in_host'] ) ) {
				continue;
			}
			$plugin_dir = $test_item['path_in_host'];
			$config     = $this->get_config_for_plugin( $plugin_dir );

			$test_item['config'] = $config;

			if ( ! empty( $config['muPlugins'] ) && is_array( $config['muPlugins'] ) ) {
				foreach ( $config['muPlugins'] as $relative_path ) {
					$host_path = rtrim( $plugin_dir, '/' ) . '/' . $relative_path;
					$this->docker->copy_into_docker(
						$env_info,
						$host_path,
						'/var/www/html/wp-content/mu-plugins/' . basename( $relative_path )
					);
				}
			}
		}

		unset( $test_item );

		// 5) Shared setup (for anything that has action=bootstrap or action=test)
		$this->run_shared_setup( $env_info, $io, $this->extension_test_runner );

		// 6) up_only scenario: just bring env up and wait
		if ( $up_only ) {
			App::make( Output::class )->writeln( '' );
			$io->writeln( '<info>Environment ID:</info> ' . $env_info->env_id );
			$io->writeln( '<info>URL:</info> ' . $env_info->site_url );
			$io->writeln( '' );
			$io->writeln( 'You can run your tests manually now. For example:' );
			$io->writeln( '  export QIT_SITE_URL=' . $env_info->site_url );
			$io->writeln( '  npm run qit-e2e' );
			$io->writeln( '' );
			$io->writeln( 'When you are done, press "Enter" here (or run "qit env:down ' . $env_info->env_id . '") to terminate.' );

			$question = new Question( '<comment>Press Enter to terminate the environment...</comment>' );
			( new QuestionHelper() )->ask(
				App::make( InputInterface::class ),
				App::make( Output::class ),
				$question
			);

			return Command::SUCCESS;
		}

		// 7) Normal test flow: do baseline snapshot, then run tests
		$io->writeln( '<info>[Saving baseline DB state]</info>' );
		$this->docker->run_inside_docker( $env_info, [ 'wp', 'db', 'export', '/qit/snapshot.sql' ] );

		// 8) Run tests for each item with action=test
		$testable_items = array_filter(
			$env_info->tests,
			function ( $item ) {
				return ( isset( $item['action'] ) && $item['action'] === Extension::ACTIONS['test'] );
			}
		);

		$is_first  = true;
		$exit_code = Command::SUCCESS;

		foreach ( $testable_items as $test_item ) {
			$code = $this->extension_test_runner->run_single_plugin_tests( $env_info, $test_item, $io, $is_first );
			if ( $code !== Command::SUCCESS ) {
				$exit_code = $code;
			}
			$is_first = false;
		}

		banner( $io, 'Post-Processing', true, true, '⚙️' );

		// 9) Shared teardown
		$this->run_shared_teardown( $env_info, $io, $this->extension_test_runner );

		// 10) Merge final CTRF/Allure
		$this->merge_results( $env_info, $io, $this->test_result );

		// 11) Notify test finished
		try {
			[ $report_url, $override_exit ] = $this->local_test_run_notifier->notify_test_finished( $this->test_result );
			if ( $override_exit !== null ) {
				$exit_code = $override_exit;
			}
			$io->writeln( "\n<info>Test run finished. Report URL:</info>\n{$report_url}\n" );
		} catch ( \Exception $e ) {
			$io->error( 'Could not finalize results to QIT Manager: ' . $e->getMessage() );
			$exit_code = Command::FAILURE;
		}

		return $exit_code;
	}

	/**
	 * Load (and cache) qit-e2e.json for the given plugin directory.
	 */
	private function get_config_for_plugin( string $plugin_dir ): array {
		if ( isset( $this->plugin_configs[ $plugin_dir ] ) ) {
			return $this->plugin_configs[ $plugin_dir ];
		}

		$config_file = rtrim( $plugin_dir, '/' ) . '/qit-e2e.json';

		if ( ! file_exists( $config_file ) ) {
			throw new RuntimeException( "No qit-e2e.json found in $plugin_dir" );
		}

		$raw  = file_get_contents( $config_file );
		$data = json_decode( $raw, true );

		if ( ! is_array( $data ) ) {
			throw new RuntimeException( "Invalid JSON in $config_file" );
		}

		$this->plugin_configs[ $plugin_dir ] = $data;

		return $data;
	}

	/**
	 * Merge CTRF + Allure results from all plugins into final artifacts.
	 */
	protected function merge_results( E2EEnvInfo $env_info, SymfonyStyle $io, TestResult $test_result ) {
		$ctrf_dir   = $test_result->get_results_dir() . '/ctrf';
		$allure_dir = $test_result->get_results_dir() . '/allure';

		$has_ctrf   = ( is_dir( $ctrf_dir ) && count( glob( $ctrf_dir . '/*.json' ) ) > 0 );
		$has_allure = false;

		if ( is_dir( $allure_dir ) ) {
			$json_files = glob( $allure_dir . '/**/*.json' );
			$has_allure = ! empty( $json_files );
		}

		if ( ! $has_ctrf && ! $has_allure ) {
			$io->writeln( '<comment>No CTRF or Allure data found to merge. Skipping...</comment>' );

			return;
		}

		$qit_dir   = Config::get_qit_dir();
		$ctrf_path = $qit_dir . '/node_modules/.bin/ctrf';

		// Merge CTRF
		if ( $has_ctrf ) {
			if ( ! ( is_file( $ctrf_path ) && is_executable( $ctrf_path ) ) ) {
				$io->warning( "No 'ctrf' binary found at $ctrf_path." );
			} else {
				$merge_cmd = new Process( [ $ctrf_path, 'merge', $ctrf_dir ] );
				$merge_cmd->setTimeout( 300 );
				$merge_cmd->run();
				if ( ! $merge_cmd->isSuccessful() ) {
					throw new RuntimeException(
						sprintf(
							'Failed to merge CTRF results. Error: %s',
							$merge_cmd->getErrorOutput()
						)
					);
				}
				// Post-process final merged file if desired
				$merged_file = $ctrf_dir . '/ctrf-report.json';
				$this->extension_test_runner->post_process_ctrf_json( $merged_file );
			}
		}

		// Allure
		if ( $has_allure ) {
			$io->writeln( '<info>Allure data found (raw results will be uploaded)...</info>' );
		}
	}

	public function run_shared_setup( $env_info, SymfonyStyle $io, ExtensionTestRunner $extension_test_runner ): void {
		$will_have_shared_setup = false;

		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if (
				$test_item['action'] !== Extension::ACTIONS['bootstrap']
				&& $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			$plugin_dir = $test_item['path_in_host'] ?? '';
			$config     = $test_item['config'] ?? [];

			if ( ! empty( $config['lifecycle']['sharedSetup'] ) && is_array( $config['lifecycle']['sharedSetup'] ) ) {
				$will_have_shared_setup = true;
				break;
			}
		}

		if ( $will_have_shared_setup ) {
			banner( $io, 'Shared Setup', false, true, '⚙️' );
		}

		foreach ( $env_info->tests as $test_item ) {
			// We only run shared setup for items with action=bootstrap or action=test
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if (
				$test_item['action'] !== Extension::ACTIONS['bootstrap']
				&& $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			$plugin_dir = $test_item['path_in_host'] ?? '';
			$config     = $test_item['config'] ?? [];

			if ( ! empty( $config['lifecycle']['sharedSetup'] ) && is_array( $config['lifecycle']['sharedSetup'] ) ) {
				foreach ( $config['lifecycle']['sharedSetup'] as $script ) {
					$extension_test_runner->run_script_if_exists(
						$env_info,
						$test_item,
						rtrim( $plugin_dir, '/' ) . '/' . $script,
						'Shared Setup',
						$io
					);
				}
			}
		}
	}

	public function run_shared_teardown( $env_info, SymfonyStyle $io, ExtensionTestRunner $extension_test_runner ): void {
		foreach ( $env_info->tests as $test_item ) {
			if ( empty( $test_item['action'] ) ) {
				continue;
			}
			if (
				$test_item['action'] !== Extension::ACTIONS['bootstrap']
				&& $test_item['action'] !== Extension::ACTIONS['test']
			) {
				continue;
			}

			$plugin_dir = $test_item['path_in_host'] ?? '';
			$config     = $test_item['config'] ?? [];

			if ( ! empty( $config['lifecycle']['sharedTeardown'] ) && is_array( $config['lifecycle']['sharedTeardown'] ) ) {
				foreach ( $config['lifecycle']['sharedTeardown'] as $script ) {
					$extension_test_runner->run_script_if_exists(
						$env_info,
						$test_item,
						rtrim( $plugin_dir, '/' ) . '/' . $script,
						'Shared Teardown',
						$io
					);
				}
			}
		}
	}
}
