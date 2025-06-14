<?php
/*
 * We need this to shut down the environment if the user
 * presses "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\LocalTests\E2E\CustomE2ERunner;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\Results\LocalTestResult;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\is_windows;

class RunE2ECommand extends QITCommand implements LocalTestCommand {
	protected E2EEnvironment $e2e_environment;
	protected CustomE2ERunner $spec_custom_test_orchestrator;
	protected WooExtensionsList $woo_extensions_list;

	protected static $defaultName = 'run:e2e';

	/**
	 * 0 is success.
	 * 1 is either Playwright failed an assertion from a user-perspective, or a PHP fatal error has been logged.
	 * 2 is reserved, so we skip it.
	 * 3 is a warning, such as a PHP error that is not fatal.
	 */
	const WARNING = 3;

	public function __construct(
		E2EEnvironment $e2e_environment,
		CustomE2ERunner $spec_custom_test_orchestrator,
		WooExtensionsList $woo_extensions_list
	) {
		$this->e2e_environment               = $e2e_environment;
		$this->spec_custom_test_orchestrator = $spec_custom_test_orchestrator;
		$this->woo_extensions_list           = $woo_extensions_list;
		parent::__construct();
	}

	// LocalTestCommand interface implementation
	public function getEnvironmentName(): string {
		return $this->input->getOption( 'environment' ) ?? 'default';
	}

	public function shouldPrepareEnvironment(): bool {
		// Don't prepare if just checking config or in up_only mode
		return ! $this->input->getOption( 'up_only' );
	}

	public function getTestType(): string {
		return 'e2e';
	}

	public function getTestProfile(): string {
		return $this->input->getOption( 'profile' ) ?? 'default';
	}

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Run E2E tests' )
		     ->addArgument( 'woo_extension', InputArgument::OPTIONAL, 'Extension slug or ID' )
		     ->addArgument( 'test', InputArgument::OPTIONAL, 'Test tags or directory', 'default' )
		     ->addArgument( 'runner_args', InputArgument::IS_ARRAY, 'Arguments after --' )

			// SUT options
			 ->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'Source of the extension' )

			// Environment overrides
			 ->addOption( 'wp_version', null, InputOption::VALUE_OPTIONAL, 'WordPress version' )
		     ->addOption( 'woo_version', null, InputOption::VALUE_OPTIONAL, 'WooCommerce version' )
		     ->addOption( 'php_version', null, InputOption::VALUE_OPTIONAL, 'PHP version' )
		     ->addOption( 'plugin', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional plugins', [] )
		     ->addOption( 'theme', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional themes', [] )
		     ->addOption( 'volume', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Volume mappings', [] )
		     ->addOption( 'php_extension', 'x', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extensions', [] )
		     ->addOption( 'object_cache', 'o', InputOption::VALUE_NONE, 'Enable Object Cache' )
		     ->addOption( 'skip_activating_plugins', 's', InputOption::VALUE_NONE, 'Skip activating plugins' )
		     ->addOption( 'skip_activating_themes', 'st', InputOption::VALUE_NONE, 'Skip activating themes' )
		     ->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunneling', 'no_tunnel' )
		     ->addOption( 'env', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment variables', [] )
		     ->addOption( 'env_file', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Environment files', [] )

			// Test options
			 ->addOption( 'pw_test_tag', null, InputOption::VALUE_OPTIONAL, 'Playwright test tag', '' )
		     ->addOption( 'shard', null, InputOption::VALUE_OPTIONAL, 'Playwright sharding' )
		     ->addOption( 'update_snapshots', null, InputOption::VALUE_NONE, 'Update snapshots' )
		     ->addOption( 'pw_options', null, InputOption::VALUE_OPTIONAL, 'Additional Playwright options' )

			// Execution options
			 ->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'Just start environment' )
		     ->addOption( 'ui', null, InputOption::VALUE_NONE, 'Run in UI mode' )
		     ->addOption( 'codegen', 'c', InputOption::VALUE_NONE, 'Run environment for Codegen' )
		     ->addOption( 'no_upload_report', null, InputOption::VALUE_NONE, 'Skip report upload' )
		     ->addOption( 'notify', null, InputOption::VALUE_NONE, 'Notify on failures' )
		     ->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, 'Register into a group', false )
		     ->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'JSON output', false );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To run E2E Tests on Windows, please use WSL.</comment>' );

			return self::FAILURE;
		}

		/** @var LocalTestResult $result */
		$result = $this->getPreCommandResult();

		// The PreCommand has already:
		// - Parsed qit.json
		// - Resolved test configuration for the profile
		// - Downloaded all extensions (SUT + dependencies)
		// - Downloaded all test packages
		// - Created the environment configuration
		// - Everything is ready to run!

		$env_info      = $result->env_info;
		$test_packages = $result->test_packages;

		// Validate input
		$shard = $input->getOption( 'shard' );
		if ( $shard && ! $this->validateShard( $shard, $output ) ) {
			return self::INVALID;
		}

		// Handle SUT from CLI if provided
		$this->handleSUT( $input, $env_info );

		// Apply additional CLI overrides
		$this->applyCliOverrides( $input, $env_info );

		// Handle termination gracefully
		$this->handle_termination();
		$GLOBALS['env_to_shutdown'] = $env_info;

		// Configure Playwright options
		$this->configure_pw_options( $input );

		// Set global variables
		App::setVar( 'should_upload_report', ! $input->getOption( 'no_upload_report' ) );
		if ( $env_info->sut ?? null ) {
			App::setVar( 'QIT_SUT', $env_info->sut['id'] );
			App::setVar( 'QIT_SUT_SLUG', $env_info->sut['slug'] );
		}

		// For testing
		if ( getenv( 'QIT_SELF_TEST' ) === 'env_info' ) {
			$output->write( json_encode( $env_info ) );

			return self::SUCCESS;
		}

		// Set up environment
		if ( $env_info->skip_activating_plugins ) {
			$this->e2e_environment->set_skip_activating_plugins( true );
		}
		if ( $env_info->skip_activating_themes ) {
			$this->e2e_environment->set_skip_activating_themes( true );
		}

		// Initialize environment
		$this->e2e_environment->init( $env_info );
		$this->e2e_environment->up( 'up' );

		// If up_only, just keep running
		if ( $input->getOption( 'up_only' ) ) {
			$output->writeln( $env_info->site_url );
			$output->writeln( '<info>Environment is up. Press Ctrl+C to shut down.</info>' );
			while ( true ) {
				sleep( 1 );
			}
		}

		// Run tests
		$io          = new SymfonyStyle( $input, $output );
		$exit_status = $this->spec_custom_test_orchestrator->run_custom_e2e_tests(
			$env_info,
			$io,
			false
		);

		// Output results
		if ( $exit_status === Command::SUCCESS ) {
			$io->success( "Tests passed. Run 'qit e2e-report' to view the report." );
		} else {
			$io->error( "Tests failed. Run 'qit e2e-report' to view the report." );
		}

		return $exit_status;
	}

	protected function validateShard( string $shard, OutputInterface $output ): bool {
		if ( ! preg_match( '/^\d+\/\d+$/', $shard ) ) {
			$output->writeln( '<error>Invalid shard format. Should be current/total, e.g., 1/5.</error>' );

			return false;
		}
		[ $current, $total ] = explode( '/', $shard );
		if ( $current <= 0 || $current > $total ) {
			$output->writeln( '<error>Invalid shard format. Current must be > 0 and <= total.</error>' );

			return false;
		}

		return true;
	}

	protected function handleSUT( InputInterface $input, $env_info ): void {
		$woo_extension = $input->getArgument( 'woo_extension' );
		if ( ! $woo_extension ) {
			return;
		}

		try {
			if ( is_numeric( $woo_extension ) ) {
				$woo_id   = (int) $woo_extension;
				$woo_slug = $this->woo_extensions_list->get_woo_extension_slug_by_id( $woo_id );
			} else {
				$woo_slug = $woo_extension;
				$woo_id   = $this->woo_extensions_list->get_woo_extension_id_by_slug( $woo_slug );
			}

			$sut_type = $this->woo_extensions_list->get_woo_extension_type( $woo_id );

			$env_info->sut = [
				'slug' => $woo_slug,
				'id'   => $woo_id,
				'type' => $sut_type,
			];

			// Handle source override
			if ( $source = $input->getOption( 'source' ) ) {
				$env_info->is_development_build = file_exists( $source );
			}
		} catch ( \Exception $e ) {
			// If we can't resolve it, that's ok - might be using qit.json SUT
		}
	}

	protected function applyCliOverrides( InputInterface $input, $env_info ): void {
		// Test configuration
		$env_info->pw_test_tag = $input->getOption( 'pw_test_tag' ) ?: 'full';
		$env_info->notify      = $input->getOption( 'notify' );
		$env_info->runner_args = $input->getArgument( 'runner_args' );

		// Test mode
		if ( $input->getOption( 'ui' ) ) {
			App::setVar( 'TEST_MODE', 'ui' );
		} elseif ( $input->getOption( 'codegen' ) ) {
			App::setVar( 'TEST_MODE', 'codegen' );
		} else {
			App::setVar( 'TEST_MODE', 'headless' );
		}
	}

	private function configure_pw_options( InputInterface $input ): void {
		$pw_options = $input->getOption( 'pw_options' ) ?? '';

		if ( $input->getOption( 'update_snapshots' ) ) {
			$pw_options .= ' --update-snapshots';
		}

		App::setVar( 'pw_options', $pw_options );
	}

	private function handle_termination(): void {
		register_shutdown_function( static function () {
			static::shutdown_test_run();
		} );

		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function (): void {
				static::shutdown_test_run();
				exit( 0 );
			};
			pcntl_signal( SIGINT, $signal_handler );
			pcntl_signal( SIGTERM, $signal_handler );
		}
	}

	public static function shutdown_test_run(): void {
		static $did_shutdown = false;
		if ( $did_shutdown ) {
			return;
		}
		$did_shutdown = true;

		if ( App::getVar( 'QIT_JSON_MODE' ) !== true ) {
			echo "\nShutting down environment...\n";
		}

		if ( ! empty( $GLOBALS['env_to_shutdown'] ) ) {
			try {
				Environment::down( $GLOBALS['env_to_shutdown'] );
			} catch ( \Exception $e ) {
				// Silent fail
			}
		}
	}
}