<?php
/*
 * We need this to shut down the environment if the user
 * press "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\EnvConfigLoader;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Extension;
use QIT_CLI\LocalTests\E2E\E2ETestManager;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\PluginDependencies;
use QIT_CLI\Tunnel\TunnelRunner;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\is_windows;

class RunE2ECommand extends DynamicCommand {
	use OptionReuseTrait;

	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var Cache */
	protected $cache;

	/** @var OutputInterface */
	protected $output;

	/** @var E2ETestManager */
	protected $e2e_test_manager;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	/** @var LocalTestRunNotifier */
	protected $test_run_notifier;

	/** @var PluginDependencies */
	protected $dependencies;

	protected static $defaultName = 'run:e2e'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	const WARNING = 3;

	public function __construct(
		E2EEnvironment $e2e_environment,
		Cache $cache,
		OutputInterface $output,
		E2ETestManager $e2e_test_manager,
		WooExtensionsList $woo_extensions_list,
		LocalTestRunNotifier $test_run_notifier,
		PluginDependencies $dependencies
	) {
		$this->e2e_environment     = $e2e_environment;
		$this->cache               = $cache;
		$this->output              = $output;
		$this->e2e_test_manager    = $e2e_test_manager;
		$this->woo_extensions_list = $woo_extensions_list;
		$this->test_run_notifier   = $test_run_notifier;
		$this->dependencies        = $dependencies;

		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['e2e']['properties'] ) ) {
			throw new \RuntimeException( 'E2E schema not set or incomplete.' );
		}

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['e2e'], [], [
			'php_version',
		] );

		$this
			->addArgument( 'woo_extension', InputArgument::OPTIONAL, 'The slug or WooCommerce ID of the main extension under test.' )
			->addArgument( 'test', InputArgument::OPTIONAL, '(Optional) The tests for the main extension under test. Accepts test tags, or a test directory.' )
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'The source of the main extension under test. If not provided, the source will be the slug.' )
			->addOption( 'sut_action', null, InputOption::VALUE_OPTIONAL, 'What action to take on the SUT. Possible values: ' . implode( ', ', Extension::ACTIONS ), Extension::ACTIONS['test'] )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'wp' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'woo' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'plugin' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'theme' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'volume' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_extension' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'require' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'config' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'object_cache' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'skip_activating_plugins' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'tunnel' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'json' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'volume' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env_file' )
			->addOption( 'shard', null, InputOption::VALUE_OPTIONAL, 'Playwright Sharding argument.' )
			->addOption( 'no_upload_report', null, InputOption::VALUE_NONE, 'Do not upload the report to QIT Manager.' )
			->addOption( 'update_snapshots', null, InputOption::VALUE_NONE, 'Update snapshots where applicable (eg: Playwright Snapshots).' )
			->addOption( 'notify', null, InputOption::VALUE_NONE, 'If set, failures will be notified to the author of the SUT.' )
			->addOption( 'pw_options', null, InputOption::VALUE_OPTIONAL, 'Additional options to pass to Playwright.' )
			->addOption( 'dependencies', null, InputOption::VALUE_OPTIONAL, 'How to handle SUT dependencies. Possible: "activate", "bootstrap", "test", or "none"', Extension::ACTIONS['bootstrap'] )
			->addOption( 'ui', null, InputOption::VALUE_NONE, 'Runs tests in UI mode.' )
			->addOption( 'codegen', 'c', InputOption::VALUE_NONE, 'Run environment for Codegen.' )
			->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'If set, it will just start the environment and keep it running until shut down.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>To run E2E Tests on Windows, please use WSL.</comment>' );
			return Command::FAILURE;
		}

		try {
			$options                    = $this->parse_options( $input );
			$env_up_options             = $options['env_up'];
			$env_up_options['--tunnel'] = TunnelRunner::get_tunnel_value( $input );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );
			return Command::FAILURE;
		}

		// Determine test mode.
		if ( $input->getOption( 'ui' ) && $input->getOption( 'codegen' ) ) {
			$output->writeln( '<error>Cannot run tests in both "UI" and "Codegen" mode at the same time.</error>' );
			return Command::INVALID;
		}

		if ( $input->getOption( 'ui' ) ) {
			$test_mode = E2ETestManager::$test_modes['ui'];
		} elseif ( $input->getOption( 'codegen' ) ) {
			putenv( 'QIT_CODEGEN=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			$test_mode = E2ETestManager::$test_modes['codegen'];
		} else {
			$test_mode = E2ETestManager::$test_modes['headless'];
		}

		App::setVar( 'TEST_MODE', $test_mode );

		$wait                = $input->getOption( 'up_only' ) || $test_mode === E2ETestManager::$test_modes['codegen'];
		$woo_extension       = $input->getArgument( 'woo_extension' );
		$test                = $input->getArgument( 'test' );
		$woocommerce_version = $input->getOption( 'woo' );
		$shard               = $input->getOption( 'shard' );
		$update_snapshots    = $input->getOption( 'update_snapshots' );
		$pw_options          = $input->getOption( 'pw_options' ) ?? '';
		$sut_action          = $input->getOption( 'sut_action' );
		$this->parse_env_vars( $input->getOption( 'env' ) );

		// Validate source for SUT.
		if ( empty( $input->getOption( 'source' ) ) ) {
			$source = '';
		} else {
			$resolved_source = realpath( $input->getOption( 'source' ) );
			if ( $resolved_source && file_exists( $resolved_source ) ) {
				$source = $resolved_source;
			} else {
				$source = $input->getOption( 'source' );
			}
		}

		// Prevent "--woo" and "--plugin woocommerce" usage together.
		if ( ! empty( $woocommerce_version ) && ! empty( $input->getOption( 'plugin' ) ) ) {
			foreach ( $input->getOption( 'plugin' ) as $p ) {
				if ( $p === 'woocommerce' ) {
					$output->writeln( '<error>Cannot use both "--woo" and "--plugin woocommerce" together.</error>' );
					return Command::INVALID;
				}
			}
		}

		if ( ! empty( $pw_options ) ) {
			if ( substr( $pw_options, 0, 1 ) === '"' && substr( $pw_options, -1 ) === '"' ) {
				$pw_options = substr( $pw_options, 1, -1 );
			}
		}

		if ( ! empty( $update_snapshots ) ) {
			$pw_options .= ' --update-snapshots';
		}

		App::setVar( 'pw_options', $pw_options );

		// Validate extension parameter if needed.
		if ( empty( $woo_extension ) ) {
			if ( ! empty( $source ) || ! empty( $sut_action ) ) {
				$output->writeln( '<error>The extension parameter is required when source or sut_action is set.</error>' );
				return Command::INVALID;
			}
			if ( ! $wait ) {
				$output->writeln( '<error>The extension parameter is required unless in --up_only or --codegen mode.</error>' );
				return Command::INVALID;
			}
		}

		$woo_extension_id = null;
		$sut_type         = null;
		if ( ! empty( $woo_extension ) ) {
			// Validate WooExtension.
			try {
				if ( is_numeric( $woo_extension ) ) {
					$woo_extension_id = $woo_extension;
					$woo_extension    = $this->woo_extensions_list->get_woo_extension_slug_by_id( $woo_extension );
				} else {
					$woo_extension_id = $this->woo_extensions_list->get_woo_extension_id_by_slug( $woo_extension );
				}
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );
				return Command::INVALID;
			}

			$sut_type = $this->woo_extensions_list->get_woo_extension_type( $woo_extension_id );
		}

		if ( $input->getOption( 'skip_activating_plugins' ) ) {
			$this->e2e_environment->set_skip_activating_plugins( true );
		}

		if ( ! empty( $input->getOption( 'config' ) ) ) {
			App::setVar( 'QIT_CONFIG_OVERRIDE', $input->getOption( 'config' ) );
		}

		// Before calling env:up, we finalize the SUT definition.
		// This merges CLI and config, ensures correct precedence and action=test for SUT.
		$final_sut_extension = $this->finalize_sut_definition(
			$woo_extension,
			$woo_extension_id,
			$sut_action,
			$source,
			$test,
			$woocommerce_version,
			$input->getOption( 'dependencies' )
		);

		if ( $final_sut_extension ) {
			// Mark that we have a finalized SUT and store it.
			App::setVar( 'QIT_FINAL_SUT_SLUG', $woo_extension );
			App::setVar( 'QIT_FINAL_SUT_DEFINITION', $final_sut_extension );
		}

		if ( ! is_null( $shard ) ) {
			if ( ! preg_match( '/^\d+\/\d+$/', $shard ) ) {
				$output->writeln( '<error>Invalid shard format. Should be current/total, e.g., 1/5.</error>' );
				return Command::INVALID;
			}
			[ $current, $total ] = explode( '/', $shard );
			if ( $current <= 0 || $current > $total ) {
				$output->writeln( '<error>Invalid shard format. current must be > 0 and <= total.</error>' );
				return Command::INVALID;
			}
		}

		$additional_volumes         = [];
		$env_up_options['--volume'] = $additional_volumes;
		$env_up_options['--json']   = true;

		if ( $output->isVerbose() ) {
			$env_up_options['--verbose'] = true;
		} elseif ( $output->isVeryVerbose() ) {
			$env_up_options['--very-verbose'] = true;
		}

		if ( $input->getOption( 'object_cache' ) ) {
			$env_up_options['--object_cache'] = true;
		}

		$env_up_command = $this->getApplication()->find( UpEnvironmentCommand::getDefaultName() );

		$this->handle_termination();

		$resource_stream = fopen( 'php://temp', 'w+' );

		if ( $wait ) {
			putenv( 'QIT_HIDE_SITE_INFO=0' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		} else {
			putenv( 'QIT_HIDE_SITE_INFO=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO=DOCKER' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		if ( ! empty( $woo_extension ) ) {
			App::setVar( 'QIT_SUT', (int) $woo_extension_id );
		}

		App::setVar( 'should_upload_report', ! $input->getOption( 'no_upload_report' ) );
		App::setVar( 'QIT_ENV_UP_OPTIONS', $env_up_options );

		$up_exit_status_code = $env_up_command->run(
			new ArrayInput( $env_up_options ),
			new StreamOutput( $resource_stream )
		);

		$up_output = stream_get_contents( $resource_stream, -1, 0 );

		if ( $up_exit_status_code === 1337 ) {
			$this->output->write( $up_output );
			return Command::SUCCESS;
		}

		$env_json = json_decode( $up_output, true );
		if ( ! is_array( $env_json ) || empty( $env_json['env_id'] ) ) {
			$this->output->writeln( sprintf( '<error>Failed to parse environment JSON. Output: %s</error>', $up_output ) );
			return Command::FAILURE;
		}

		/** @var E2EEnvInfo $env_info */
		$env_info = E2EEnvInfo::from_array( $env_json );
		App::singleton( E2EEnvInfo::class, $env_info );

		if ( ! empty( $woo_extension_id ) ) {
			$env_info->sut_slug = $woo_extension;
			$env_info->sut_id   = $woo_extension_id;
			$env_info->sut_type = $sut_type;

			$is_development = file_exists( $source );
			$this->test_run_notifier->notify_test_started(
				$woo_extension_id,
				$woocommerce_version ?? 'none',
				$env_info,
				$is_development,
				$input->getOption( 'notify' )
			);
		}

		$GLOBALS['env_to_shutdown'] = $env_info;

		if ( $up_exit_status_code !== Command::SUCCESS ) {
			$this->output->writeln( sprintf( '<error>Failed to start the environment. Output: %s</error>', $up_output ) );
			Environment::down( $env_json['env_id'] );
			return Command::FAILURE;
		}

		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->write( json_encode( $env_info ) );
			return Command::SUCCESS;
		}

		$exit_status_code = $this->e2e_test_manager->run_tests( $env_info, $test_mode, $wait, $shard );
		$io               = new SymfonyStyle( $input, $output );
		$io->setDecorated( true );

		if ( $exit_status_code === Command::SUCCESS ) {
			$io->success( "Tests passed. Run 'qit e2e-report' to view the report." );
			return Command::SUCCESS;
		} elseif ( $exit_status_code === self::WARNING ) {
			if ( $test_mode === E2ETestManager::$test_modes['headless'] ) {
				$io->warning( "Tests passed with a warning. Run 'qit e2e-report' to view the report." );
			}
			return self::WARNING;
		} else {
			if ( $test_mode === E2ETestManager::$test_modes['headless'] ) {
				$io->error( "Tests failed. Run 'qit e2e-report' to view the report." );
			}
			return Command::FAILURE;
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

		if ( empty( $GLOBALS['env_to_shutdown'] ) || ! $GLOBALS['env_to_shutdown'] instanceof EnvInfo ) {
			return;
		}

		try {
			Environment::down( $GLOBALS['env_to_shutdown'] );
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// no-op.
		}
	}

	private function handle_termination(): void {
		register_shutdown_function( static function () {
			static::shutdown_test_run();
		} );

		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function (): void {
				static::shutdown_test_run();
				exit;
			};
			pcntl_signal( SIGINT, $signal_handler );
			pcntl_signal( SIGTERM, $signal_handler );
		}
	}

	protected function parse_options( InputInterface $input, bool $filter_to_send = true ): array {
		$options = parent::parse_options( $input, false );

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
				$parsed_options['env_up'][ "--$option_name" ] = $option_value;
			}
		}

		return $parsed_options;
	}

	protected function parse_env_vars( array $env_vars ): void {
		$parsed_vars = [];
		foreach ( $env_vars as $env_var ) {
			$env_var = explode( '=', $env_var, 2 );
			if ( count( $env_var ) !== 2 ) {
				throw new \RuntimeException( 'Invalid environment variable format. Use "--env FOO=bar".' );
			}

			$key   = trim( $env_var[0] );
			$value = trim( $env_var[1] );

			if ( ! preg_match( '/^[A-Za-z0-9_]+$/', $key ) ) {
				throw new \RuntimeException( 'Invalid env var name. Letters, numbers, underscores only.' );
			}

			$parsed_vars[ $key ] = $value;
		}

		App::setVar( 'QIT_PW_ENV_VARS', $parsed_vars );
	}

	protected function finalize_sut_definition(
		$woo_extension,
		$woo_extension_id,
		$sut_action,
		$source,
		$test,
		$woocommerce_version,
		$dependencies_option
	) {
		if ( empty( $woo_extension ) ) {
			return null;
		}

		// Validate dependencies action.
		if ( $dependencies_option !== 'none' && ! in_array( $dependencies_option, \QIT_CLI\Environment\Extension::ACTIONS, true ) ) {
			throw new \RuntimeException(sprintf(
				'Invalid dependencies action. Possible values: none, %s',
				implode( ', ', \QIT_CLI\Environment\Extension::ACTIONS )
			));
		}

		// Load qit.yml config.
		$env_config    = App::make( \QIT_CLI\Environment\EnvConfigLoader::class )->load_config();
		$plugin_config = $env_config['plugins'] ?? $env_config['plugin'] ?? [];

		// Determine if qit.yml defines the SUT.
		if ( isset( $plugin_config[ $woo_extension ] ) ) {
			// SUT is defined in qit.yml, use it as the base.
			$sut_base = $plugin_config[ $woo_extension ];
		} else {
			// SUT not in qit.yml, create a default entry.
			$sut_base = [
				'slug'      => $woo_extension,
				// Default source is the slug if qit.yml doesn't provide one.
				'source'    => $woo_extension,
				'test_tags' => [ 'default' ],
			];
		}

		// If CLI --source is given, override the source from qit.yml or default.
		if ( ! empty( $source ) ) {
			$sut_base['source'] = $source;
		} else { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedElse
			// If qit.yml had a local source (e.g., "./woocommerce-amazon-s3-storage"), keep it.
			// Check if qit.yml actually defined it. If at the start it had "./", it should still be here.
			// We'll see this in the debug output if it changes.
		}

		// If CLI test argument provided, override test_tags.
		if ( ! empty( $test ) ) {
			$sut_base['test_tags'] = [ $test ];
		} elseif ( empty( $sut_base['test_tags'] ) ) {
			$sut_base['test_tags'] = [ 'default' ];
		}

		// Respect qit.yml action if defined, else default to 'test'.
		if ( ! isset( $sut_base['action'] ) ) {
			$sut_base['action'] = \QIT_CLI\Environment\Extension::ACTIONS['test'];
		}

		// Handle dependencies if needed.
		if ( $dependencies_option !== 'none' ) {
			$dependencies = $this->dependencies->get_plugin_and_php_ext_dependencies( $woo_extension_id, [] );
			foreach ( $dependencies['php_extensions'] as $php_extension ) {
				$this->output->writeln( sprintf( 'Adding PHP extension dependency: %s', $php_extension ) );
				App::setVar( 'QIT_ADDITIONAL_PHP_EXT', $php_extension );
			}
			foreach ( $dependencies['plugins'] as $dep_plugin ) {
				$this->output->writeln( sprintf( 'Adding dependency: %s', $dep_plugin ) );
				App::pushVar( 'QIT_ADDITIONAL_PLUGINS', "$dep_plugin:$dependencies_option" );
			}
		}

		// Save updated config.
		$plugin_config[ $woo_extension ] = $sut_base;
		App::setVar( 'QIT_FINAL_SUT_SLUG', $woo_extension );
		App::setVar( 'QIT_FINAL_SUT_MODIFIED_CONFIG', $plugin_config );

		return null; // We don't add a second definition directly to env_up_options.
	}
}
