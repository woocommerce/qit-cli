<?php

namespace QIT_CLI\LocalTests\E2E;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\LocalTests\E2E\Runner\E2ERunner;
use QIT_CLI\LocalTests\E2E\Runner\PlaywrightRunner;
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

	/**
	 * @var array<string, string>
	 */
	public static $test_modes = [
		'headless' => 'headless',
		'ui'       => 'ui',
		'codegen'  => 'codegen',
	];

	public static $has_report = false;

	public function __construct( Docker $docker, OutputInterface $output ) {
		$this->docker = $docker;
		$this->output = $output;
	}

	/**
	 * @param E2EEnvInfo $env_info
	 * @param string $sut - System Under Test.
	 * @param string $compatibility_mode - "default", "full", or a comma-separated list of plugin slugs.
	 * @param string $test_mode One of the allowed test modes.
	 * @param bool $bootstrap_only If true, will only bootstrap.
	 */
	public function run_tests( E2EEnvInfo $env_info, string $sut, string $compatibility_mode, string $test_mode, bool $bootstrap_only ): void {
		$test_result = TestResult::init_from( $env_info );

		if ( empty( $env_info->tests ) ) {
			throw new \RuntimeException( 'No tests found for the given plugins.' );
		}

		$this->output->writeln( '<info>Bootstrapping Plugins</info>' );

		/**
		 * Bootstrap all plugins.
		 */
		foreach ( $env_info->tests as $plugin_slug => $test_info ) {
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/bootstrap.php' ) ) {
				$this->output->writeln( sprintf( 'Bootstrapping %s %s', $plugin_slug, $test_info['path_in_container'] . '/bootstrap/bootstrap.php' ) );
				$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "php /qit/tests/e2e/$plugin_slug/bootstrap/bootstrap.php" ] );
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.php', 'processed' );
			} else {
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.php', 'not_present' );
			}
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/bootstrap.sh' ) ) {
				$this->output->writeln( sprintf( 'Bootstrapping %s %s', $plugin_slug, $test_info['path_in_container'] . '/bootstrap/bootstrap.sh' ) );
				$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "bash /qit/tests/e2e/$plugin_slug/bootstrap/bootstrap.sh" ] );
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.sh', 'processed' );
			} else {
				$test_result->register_bootstrap( $plugin_slug, 'bootstrap.sh', 'not_present' );
			}
			if ( file_exists( $test_info['path_in_host'] . '/bootstrap/must-use-plugin.php' ) ) {
				$this->output->writeln( sprintf( 'Moving must-use plugin of %s %s', $plugin_slug, $test_info['path_in_container'] . '/bootstrap/must-use-plugin.php' ) );
				$this->docker->run_inside_docker( $env_info, [ 'bash', '-c', "mv /qit/tests/e2e/$plugin_slug/bootstrap/must-use-plugin.php /var/www/html/wp-content/mu-plugins/qit-mu-$plugin_slug.php" ] );
				$test_result->register_bootstrap( $plugin_slug, 'must-use-plugin.php', 'processed' );
			} else {
				$test_result->register_bootstrap( $plugin_slug, 'must-use-plugin.php', 'not_present' );
			}
		}

		if ( $bootstrap_only ) {
			if ( $test_mode === 'codegen' ) {
				$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );

				$io->success( 'Run Playwright Codegen from your computer now. Learn More: https://qit.woo.com/docs/codegen' );
			}

			$this->output->writeln( '' );

			$question = new Question( '<comment>Environment ready. Press "Enter" when you are done to terminate it.</comment>' );
			$question->setValidator( function ( $answer ) {
				return $answer;
			} );
			( new QuestionHelper )->ask( App::make( InputInterface::class ), $this->output, $question );

			return;
		}

		$this->output->writeln( '<info>Running E2E Tests</info>' );

		if ( $compatibility_mode !== 'default' && $compatibility_mode !== 'full' ) {
			$compatibility_mode = array_map( 'trim', explode( ',', $compatibility_mode ) );
		}

		$tests_to_run = [
			'playwright' => [],
		];

		/**
		 * Split the test to be run.
		 */
		foreach ( $env_info->tests as $plugin_slug => $test_info ) {
			$is_sut = $plugin_slug === $sut;

			if ( $compatibility_mode === 'default' && ! $is_sut ) {
				continue;
			}

			if ( $compatibility_mode === 'full' || $is_sut || in_array( $plugin_slug, $compatibility_mode, true ) ) {
				if ( E2ERunner::find_runner_type( $test_info['path_in_host'] ) === 'playwright' ) {
					$tests_to_run['playwright'][] = $test_info;
				}
			}
		}

		/**
		 * Run the tests.
		 */
		if ( ! empty( $tests_to_run['playwright'] ) ) {
			App::make( PlaywrightRunner::class )->run_test( $env_info, $tests_to_run['playwright'], $test_result, $test_mode );
		}

		if ( static::$has_report ) {
			$this->output->writeln( 'To open last HTML report run:' );
			$this->output->writeln( 'qit e2e-report' );
		}

		$test_result->set_status( 'completed' );

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( sprintf( '[Verbose] Test artifacts directory: %s', $test_result->get_results_dir() ) );
		}
	}
}
