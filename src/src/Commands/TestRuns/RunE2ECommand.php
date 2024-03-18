<?php

namespace QIT_CLI\Commands\TestRuns;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use QIT_CLI\Tests\E2E\E2ETestManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Process\Process;
use function QIT_CLI\is_windows;
use function QIT_CLI\normalize_path;

/*
 * We need this to shut down the environment if the user
 * press "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

class RunE2ECommand extends DynamicCommand {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var Cache */
	protected $cache;

	/** @var OutputInterface */
	protected $output;

	/** @var E2ETestManager */
	protected $e2e_test_manager;

	protected static $defaultName = 'run:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		E2EEnvironment $e2e_environment,
		Cache $cache,
		OutputInterface $output,
		E2ETestManager $e2e_test_manager
	) {
		$this->e2e_environment  = $e2e_environment;
		$this->cache            = $cache;
		$this->output           = $output;
		$this->e2e_test_manager = $e2e_test_manager;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['e2e']['properties'] ) ) {
			throw new \RuntimeException( 'E2E schema not set or incomplete.' );
		}

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['e2e'],
			[ 'woocommerce_version' ]
		);

		// Extension slug/ID.
		$this->addArgument(
			'woo_extension',
			InputArgument::REQUIRED,
			'A WooCommerce Extension Slug or Marketplace ID.'
		);

		// Extension slug/ID.
		$this->addArgument(
			'path',
			InputArgument::OPTIONAL,
			'Path to your E2E tests (Optional, if not set, it will try to download your custom tests that you have previously uploaded to QIT)'
		);

		// Add "mode" option, which can be "headless", "headed", "ui" or "codegen"..
		$this->addOption(
			'mode',
			'm',
			InputOption::VALUE_REQUIRED,
			'Test mode: "headless", "headed", "ui" or "codegen".',
			'headless'
		);

		// If "woo_extension" (SUT) is a theme, set this.
		$this->addOption(
			'testing_theme',
			'tt',
			InputOption::VALUE_NONE,
			'If the "woo_extension" is a theme, set this flag.'
		);

		// If "woo_extension" (SUT) is a theme, set this.
		$this->addOption(
			'woocommerce_version',
			'woo',
			InputOption::VALUE_REQUIRED,
			'The WooCommerce Version. Accepts "nightly", "stable", or a GitHub Tag (eg: 8.6.1).',
			'stable'
		);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>Warning: It is highly recommended to run this script from Windows Subsystem for Linux (WSL) when using Windows.</comment>' );
		}

		try {
			$options        = $this->parse_options( $input );
			$env_up_options = $options['env_up'];
			$other_options  = $options['other'];
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		foreach ( $other_options as $k => $o ) {
			if ( ! in_array( $k, [ 'compatibility', 'woocommerce_version' ], true ) ) {
				$this->output->writeln( sprintf( '<comment>Option "%s" is not part of the "env:up" command definition and will be ignored.</comment>', $k ) );
			}
		}

		if ( ! in_array( $input->getOption( 'mode' ), E2ETestManager::$test_modes ) ) {
			$output->writeln( sprintf( '<error>Invalid test mode. Allowed modes are: %s.</error>', implode( ', ', E2ETestManager::$test_modes ) ) );

			return Command::INVALID;
		}

		$woo_extension       = $input->getArgument( 'woo_extension' );
		$compatibility       = $other_options['compatibility'];
		$test_mode           = $input->getOption( 'mode' );
		$woocommerce_version = $input->getOption('woocommerce_version');

		if ( $woocommerce_version === 'nightly' ) {
			$env_up_options['--plugins'][] = 'https://github.com/woocommerce/woocommerce/releases/download/nightly/woocommerce-trunk-nightly.zip';
		} elseif ( $woocommerce_version === 'stable' ) {
			$env_up_options['--plugins'][] = 'woocommerce';
		} else {
			$env_up_options['--plugins'][] = "https://github.com/woocommerce/woocommerce/releases/download/$woocommerce_version/woocommerce.zip";
		}

		if ( $input->getOption( 'testing_theme' ) === 'true' ) {
			$env_up_options['--themes'][] = $woo_extension;
		} else {
			$env_up_options['--plugins'][] = $woo_extension;
		}

		$additional_volumes = [];

		$path = $input->getArgument( 'path' );

		if ( file_exists( $path ) ) {
			putenv( sprintf( 'QIT_CUSTOM_TESTS_PATH="%s"', $path ) );

			// Mount the tests as read-only.
			$additional_volumes[] = normalize_path( $path ) . ':' . "/qit/tests/$woo_extension/e2e/";
		}

		if ( ! ExtensionDownloader::is_valid_plugin_slug( $woo_extension ) ) {
			$output->writeln( sprintf( '<error>Invalid WooCommerce Extension Slug or Marketplace ID: "%s"</error>', $woo_extension ) );

			return Command::FAILURE;
		}

		$env_up_options['--volumes'] = $additional_volumes;
		$env_up_options['--json'] = true;

		if ( $output->isVerbose() ) {
			$env_up_options['--verbose'] = true;
		} elseif ( $output->isVeryVerbose() ) {
			$env_up_options['--very-verbose'] = true;
		}

		// Invoke the "env:up" Command.
		$env_up_command = $this->getApplication()->find( UpEnvironmentCommand::getDefaultName() );

		// Schedule a catch-all for this environment to be terminated when this script ends (ungracefully).
		$this->handle_termination();

		$resource_stream = fopen( 'php://temp', 'w+' );

		putenv( 'QIT_UP_AND_TEST=1' );
		putenv( sprintf( 'QIT_SUT="%s"', $woo_extension ) );

		$up_exit_status_code = $env_up_command->run(
			new ArrayInput( $env_up_options ),
			new StreamOutput( $resource_stream )
		);

		// Read from the stream.
		$up_output = stream_get_contents( $resource_stream, - 1, 0 );
		$env_json  = json_decode( $up_output, true );

		if ( ! is_array( $env_json ) || empty( $env_json['env_id'] ) ) {
			$this->output->writeln( sprintf( '<error>Failed to parse the environment JSON. Output: %s</error>', $up_output ) );

			return Command::FAILURE;
		}

		/** @var E2EEnvInfo $env_info */
		$env_info = EnvInfo::from_array( $env_json );

		// Store in $GLOBALS so that's available in the shutdown function.
		$GLOBALS['env_to_shutdown'] = $env_info;

		if ( $up_exit_status_code !== Command::SUCCESS ) {
			$this->output->writeln( sprintf( '<error>Failed to start the environment. Output: %s</error>', stream_get_contents( $resource_stream, - 1, 0 ) ) );
			Environment::down( $env_json['env_id'], new NullOutput() );

			return Command::FAILURE;
		}

		$this->output->writeln( 'Running E2E...' );

		$this->e2e_test_manager->run_tests( $env_info, $woo_extension, $compatibility, $test_mode );

		// Codegen has to run on host.

		/**
		 * docker run \
		 * --add-host=hostmachine:host-gateway \
		 * --rm \
		 * --init \
		 * -it \
		 * -v $(pwd):/home/pwuser/tests \
		 * mcr.microsoft.com/playwright:v1.41.0-jammy \
		 * /bin/sh -c "cd /home/pwuser && npm install @playwright/test@1.41.0 playwright@1.41.0 && npx playwright install && npx playwright test"
		 */

		return Command::SUCCESS;
	}

	/**
	 * Ask the user a question, without blocking the process from doing other things.
	 *
	 * We use this to print the "Press Enter to terminate..." message, and once
	 * pressed, we terminate the environment gracefully.
	 *
	 * Without this, when asked, PHP would stop, waiting for an answer.
	 *
	 * We print this question because otherwise the user wouldn't have a way to
	 * "correctly" interrupt the test if they don't have the "pcntl" extension.
	 *
	 * The "pcntl" extension allows us to capture "signals" such as "Ctrl+C",
	 * and handle them in PHP. If they have it installed, we handle it.
	 */
	public static function press_enter_to_terminate_callback( Process $playwright_process, array $processes = [] ): void {
		stream_set_blocking( STDIN, 0 );
		while ( $playwright_process->isRunning() ) {
			$user_input = stream_get_contents( STDIN );
			if ( $user_input !== false && strlen( $user_input ) > 0 ) {
				$playwright_process->stop();
				foreach ( $processes as $p ) {
					$p->stop();
				}
				break;
			}

			usleep( 200000 );  // Sleep for 0.2 second.
		}
		stream_set_blocking( STDIN, 1 );
	}


	public static function shutdown_test_run() {
		echo "\nShutting down environment...\n";
		// Env not up or could not parse the "up" JSON.
		if ( empty( $GLOBALS['env_to_shutdown'] ) || ! $GLOBALS['env_to_shutdown'] instanceof EnvInfo ) {
			return;
		}
		try {
			Environment::down( $GLOBALS['env_to_shutdown'], new NullOutput() );
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// no-op.
		}
	}

	/**
	 * This function will shut down the temporary environment
	 * when the test finishes or is aborted.
	 */
	private function handle_termination(): void {
		/*
		 * PHP-handled termination, such as:
		 * - Script finished executing.
		 * - There was a fatal, an exception, etc.
		 */
		register_shutdown_function( static function () {
			echo "SHUTDOWN\n";
			self::shutdown_test_run();
		} );

		/*
		 * If "pcntl" extension is available, handle OS signals, such as:
		 *
		 * - Ctrl+C (SIGINT)
		 * - kill (SIGTERM)
		 *
		 * This way if a test is running and the user kills the process
		 * with "Ctrl+C" we can terminate the temporary environments.
		 */
		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function ( int $signal ): void {
				self::shutdown_test_run();
				exit;
			};

			pcntl_signal( SIGINT, static function ( int $signal ): void {
				echo "SIGINT\n";
				self::shutdown_test_run();
				exit;
			} ); // Ctrl+C
			pcntl_signal( SIGTERM, static function ( int $signal ): void {
				echo "SIGTERM\n";
				self::shutdown_test_run();
				exit;
			} ); // eg: kill 123, where "123" is the PID of this PHP process.
		}
	}

	private function webserver(): void {
		$playwright_server = new Process( [
			App::make( Docker::class )->find_docker(),
			'run',
			"--network=$network",
			'--name=playwright_server',
			'--tty',
			'--rm',
			'--init',
			'--user',
			implode( ':', Docker::get_user_and_group() ),
			'-v',
			'/tmp/foo/tests:/home/pwuser/tests',
			'--add-host=host.docker.internal:host-gateway',
			'mcr.microsoft.com/playwright:v1.41.0-jammy',
			'sh',
			'-c',
			// Headless.
			//'cd /home/pwuser && npm install @playwright/test@1.41.0 playwright@1.41.0 && npx playwright install && npx playwright test',
			'cd /home/pwuser && npx -y playwright@1.41.0 run-server --port 3000 --host 0.0.0.0',
		] );

		$playwright_server->mustRun( function ( $type, $out ) {
			$this->output->write( $out );
		} );

		/**
		 * docker run \
		 * --add-host=hostmachine:host-gateway \
		 * --rm \
		 * --init \
		 * -it \
		 * -v $(pwd):/home/pwuser/tests \
		 * mcr.microsoft.com/playwright:v1.41.0-jammy \
		 * /bin/sh -c "cd /home/pwuser && npm install @playwright/test@1.41.0 playwright@1.41.0 && npx playwright install && npx playwright test"
		 */
		$playwright_process = new Process( [
			App::make( Docker::class )->find_docker(),
			'run',
			"--network=$network",
			'--tty',
			'--rm',
			'--init',
			'--user',
			implode( ':', Docker::get_user_and_group() ),
			'-v',
			'/tmp/foo/tests:/home/pwuser/tests',
			'--add-host=host.docker.internal:host-gateway',
			'mcr.microsoft.com/playwright:v1.41.0-jammy',
			'sh',
			'-c',
			// Headless.
			//'cd /home/pwuser && npm install @playwright/test@1.41.0 playwright@1.41.0 && npx playwright install && npx playwright test',
			'cd /home/pwuser && npm install @playwright/test@1.41.0 playwright@1.41.0 && npx playwright install && PW_TEST_CONNECT_WS_ENDPOINT=ws://playwright_server:3000/ npx playwright test --ui-port=8085 --ui-host=0.0.0.0',
		] );

		$playwright_process->mustRun( function ( $type, $out ) {
			$this->output->write( $out );
		} );
	}

	/**
	 * This function will validate the required options, categorize them
	 * into options that are to be sent to "env:up" and the rest.
	 *
	 * This can then be used to pass to "env:up" the options that it needs,
	 * while using the other options for the rest.
	 *
	 * @param InputInterface $input
	 *
	 * @return array<mixed>
	 */
	protected function parse_options( InputInterface $input ): array {
		$options = parent::parse_options( $input );

		// Iterate over all options of UpEnvironmentCommand
		// Remote keys in $options array that are not part of the definition
		// Notify user if that happens.
		$up_command_option_names = array_map( function ( $option ) {
			return $option->getName();
		}, $this->getApplication()->find( UpEnvironmentCommand::getDefaultName() )->getDefinition()->getOptions() );

		$parsed_options = [
			'env_up' => [],
			'other'  => [],
		];

		foreach ( $options as $option_name => $option_value ) {
			if ( ! in_array( $option_name, $up_command_option_names, true ) ) {
				$parsed_options['other'][ $option_name ] = $option_value;
			} else {
				$parsed_options['env_up']["--$option_name"] = $option_value;
			}
		}

		return $parsed_options;
	}
}
