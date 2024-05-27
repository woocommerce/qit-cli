<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\E2E\Runner\E2ERunner;
use QIT_CLI\LocalTests\E2E\Runner\PlaywrightRunner;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class E2ETestManager {
	/** @var Docker $docker */
	protected $docker;

	/** @var OutputInterface $output */
	protected $output;

	/** @var PlaywrightCodegen */
	protected $playwright_codegen;

	/** @var LocalTestRunNotifier */
	protected $notifier;

	/**
	 * @var array<string, string>
	 */
	public static $test_modes = [
		'headless' => 'headless',
		'ui'       => 'ui',
		'codegen'  => 'codegen',
	];

	/** @var bool */
	public static $has_report = false;

	public function __construct(
		Docker $docker,
		PlaywrightCodegen $playwright_codegen,
		OutputInterface $output,
		LocalTestRunNotifier $notifier
	) {
		$this->docker             = $docker;
		$this->output             = $output;
		$this->playwright_codegen = $playwright_codegen;
		$this->notifier           = $notifier;
	}

	/**
	 * @param E2EEnvInfo  $env_info
	 * @param string      $test_mode One of the allowed test modes.
	 * @param bool        $bootstrap_only If true, will only bootstrap.
	 * @param string|null $shard
	 *
	 * @return array{int,int|string} The exit status code and the report URL.
	 */
	public function run_tests( E2EEnvInfo $env_info, string $test_mode, bool $bootstrap_only, ?string $shard = null, bool $no_upload_report = false ): array {
		$test_result = TestResult::init_from( $env_info );
		$test_result->no_upload_report = $no_upload_report;

		$this->output->writeln( '<info>Bootstrapping Plugins</info>' );

		/**
		 * Bootstrap all plugins.
		 */
		foreach ( $env_info->tests as $test_info ) {
			if ( $test_info['action'] !== Extension::ACTIONS['bootstrap'] && $test_info['action'] !== Extension::ACTIONS['test'] ) {
				continue;
			}

			$plugin_slug = $test_info['slug'];

			$env_vars = [
				'QIT_TEST_TAG' => $test_info['test_tag'],
				'QIT_SLUG'     => $plugin_slug,
				'QIT_TEST_DIR' => $test_info['path_in_php_container'],
			];

			// bootstrap.php.
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/bootstrap.php' ) ) {
				$this->output->writeln( sprintf( 'Bootstrapping %s %s', $plugin_slug, $test_info['path_in_php_container'] . '/bootstrap/bootstrap.php' ) );
				try {
					$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "php {$test_info['path_in_php_container']}/bootstrap/bootstrap.php" ], $env_vars );
					$test_result->register_bootstrap( $plugin_slug, 'bootstrap.php', 'success' );
				} catch ( \Exception $e ) {
					$test_result->register_bootstrap( $plugin_slug, 'bootstrap.php', 'failed' );
				}
			} else {
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.php', 'not_present' );
			}

			// bootstrap.sh.
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/bootstrap.sh' ) ) {
				$this->output->writeln( sprintf( 'Bootstrapping %s %s', $plugin_slug, $test_info['path_in_php_container'] . '/bootstrap/bootstrap.sh' ) );
				try {
					$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "bash {$test_info['path_in_php_container']}/bootstrap/bootstrap.sh" ], $env_vars );
					$test_result->register_bootstrap( $plugin_slug, 'bootstrap.sh', 'success' );
				} catch ( \Exception $e ) {
					$test_result->register_bootstrap( $plugin_slug, 'bootstrap.sh', 'failed' );
				}
			} else {
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.sh', 'not_present' );
			}

			// must-use-plugin.php.
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/must-use-plugin.php' ) ) {
				$this->output->writeln( sprintf( 'Moving must-use plugin of %s %s', $plugin_slug, $test_info['path_in_php_container'] . '/bootstrap/must-use-plugin.php' ) );
				try {
					$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "mv {$test_info['path_in_php_container']}/bootstrap/must-use-plugin.php /var/www/html/wp-content/mu-plugins/qit-mu-$plugin_slug.php" ], $env_vars );
					$test_result->register_bootstrap( $plugin_slug, 'must-use-plugin.php', 'success' );
				} catch ( \Exception $e ) {
					$test_result->register_bootstrap( $plugin_slug, 'must-use-plugin.php', 'failed' );
				}
			} else {
				$test_result->register_bootstrap( $plugin_slug, 'must-use-plugin.php', 'not_present' );
			}
		}

		if ( $bootstrap_only ) {
			if ( $test_mode === 'codegen' ) {
				$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );
				$io->success( "Open the site URL above on Playwright Codegen and start generating tests.\nLearn More: https://qit.woo.com/docs/custom-tests/generating-tests#codegen" );

				$this->playwright_codegen->open_codegen( $env_info );

				return 0;
			} else {
				$this->output->writeln( '' );

				$question = new Question( '<comment>Environment ready. Press "Enter" when you are done to terminate it.</comment>' );
				$question->setValidator( function ( $answer ) {
					return $answer;
				} );
				( new QuestionHelper() )->ask( App::make( InputInterface::class ), $this->output, $question );

				return 0;
			}
		}

		if ( empty( $env_info->tests ) ) {
			throw new \RuntimeException( 'No tests found for the given plugins.' );
		}

		$test_phases = 0;

		foreach ( $env_info->tests as $test_info ) {
			if ( $test_info['action'] === Extension::ACTIONS['test'] ) {
				++$test_phases;
			}
		}

		if ( $test_phases > 1 ) {
			// Do a DB export.
			$this->output->writeln( '<info>Exporting DB</info>' );
			$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', 'wp db export /tmp/qit-bootstrap.sql' ] );
		}

		$this->output->writeln( '<info>Running E2E Tests</info>' );

		$tests_to_run = [
			'playwright' => [],
		];

		/**
		 * Split the test to be run.
		 */
		foreach ( $env_info->tests as $test_info ) {
			if ( $test_info['action'] === Extension::ACTIONS['test'] && E2ERunner::find_runner_type( $test_info['path_in_host'] ) === 'playwright' ) {
				$tests_to_run['playwright'][] = $test_info;
			}
		}

		// "Not found" by default.
		$exit_status_code = 127;

		/**
		 * Run the tests.
		 */
		if ( ! empty( $tests_to_run['playwright'] ) ) {
			$exit_status_code = App::make( PlaywrightRunner::class )->run_test( $env_info, $tests_to_run['playwright'], $test_result, $test_mode, $shard );
		}

		if ( static::$has_report ) {
			$this->output->writeln( '' );
			$this->output->writeln( 'To open last HTML report run: qit e2e-report' );
		}

		$test_result->set_status( 'completed' );

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( sprintf( '[Verbose] Test artifacts directory: %s', $test_result->get_results_dir() ) );
		}

		// Copy debug.log to results dir, if present.
		try {
			$this->docker->copy_from_docker( $env_info, '/var/www/html/wp-content/debug.log', $test_result->get_results_dir() . '/debug.log' );
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// No-op, a debug.log was not present.
		}

		$report_url = $this->notifier->notify_test_finished( $test_result );

		if ( file_exists( $test_result->get_results_dir() . '/report/index.html' ) ) {
			App::make( Cache::class )->set( 'last_e2e_report', json_encode( [
				'local_playwright' => $test_result->get_results_dir() . '/report',
				'remote_qit'       => $report_url,
			] ), MONTH_IN_SECONDS );
			E2ETestManager::$has_report = true;
		}

		return [
			$exit_status_code,
			$report_url,
		];
	}
}
