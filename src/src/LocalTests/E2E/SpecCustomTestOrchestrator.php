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

/**
 * Orchestrates custom E2E tests with a required qit-e2e.json (no fallback).
 * - Reads & caches each plugin’s qit-e2e.json into $this->pluginConfigs
 * - Runs shared lifecycle steps, plugin-specific steps, merges results, etc.
 */
class SpecCustomTestOrchestrator {
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
	 * @var SharedSetupRunner
	 */
	protected $shared_setup_runner;

	/**
	 * @var array<string,array>  Holds parsed qit-e2e.json data keyed by plugin path
	 */
	private $pluginConfigs = [];

	public function __construct(
		Docker $docker,
		LocalTestRunNotifier $local_test_run_notifier,
		SharedSetupRunner $shared_setup_runner,
		ExtensionTestRunner $extension_test_runner
	) {
		$this->docker                  = $docker;
		$this->local_test_run_notifier = $local_test_run_notifier;
		$this->extension_test_runner   = $extension_test_runner;
		$this->shared_setup_runner     = $shared_setup_runner;
	}

	/**
	 * Main entry point for custom E2E tests orchestration.
	 *
	 * @param E2EEnvInfo $env_info
	 * @param SymfonyStyle $io
	 * @param bool $up_only Whether to just bring up the environment without running tests
	 *
	 * @return int
	 */
	public function run_custom_e2e_tests( E2EEnvInfo $env_info, SymfonyStyle $io, bool $up_only ) {
		// 1) Notify that a test run started
		$this->local_test_run_notifier->notify_test_started(
			$env_info->sut_id,
			$env_info->woo_version,
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
			$config     = $this->getConfigForPlugin( $plugin_dir );

			$test_item['config'] = $config;

			if ( ! empty( $config['muPlugins'] ) && is_array( $config['muPlugins'] ) ) {
				foreach ( $config['muPlugins'] as $relativePath ) {
					$host_path = rtrim( $plugin_dir, '/' ) . '/' . $relativePath;
					$this->docker->copy_into_docker(
						$env_info,
						$host_path,
						'/var/www/html/wp-content/mu-plugins/' . basename( $relativePath )
					);
				}
			}
		}

		unset($test_item);

		// 5) Shared setup (for anything that has action=bootstrap or action=test)
		$this->shared_setup_runner->run_shared_setup( $env_info, $io, $this->extension_test_runner );

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
		$io->writeln( '<info>[db export]</info>' );
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

		// 9) Shared teardown
		$this->shared_setup_runner->run_shared_teardown( $env_info, $io, $this->extension_test_runner );

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
	private function getConfigForPlugin( string $pluginDir ): array {
		if ( isset( $this->pluginConfigs[ $pluginDir ] ) ) {
			return $this->pluginConfigs[ $pluginDir ];
		}
		$this->pluginConfigs[ $pluginDir ] = $this->loadQitE2EConfig( $pluginDir );

		return $this->pluginConfigs[ $pluginDir ];
	}

	/**
	 * Actually reads qit-e2e.json from disk and decodes it.
	 *
	 * @param string $pluginDir
	 *
	 * @return array
	 */
	private function loadQitE2EConfig( string $pluginDir ): array {
		$configFile = rtrim( $pluginDir, '/' ) . '/qit-e2e.json';

		if ( ! file_exists( $configFile ) ) {
			throw new RuntimeException( "No qit-e2e.json found in $pluginDir" );
		}
		$raw  = file_get_contents( $configFile );
		$data = json_decode( $raw, true );

		if ( ! is_array( $data ) ) {
			throw new RuntimeException( "Invalid JSON in $configFile" );
		}

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
}