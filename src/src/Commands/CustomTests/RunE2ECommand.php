<?php
/*
 * We need this to shut down the environment if the user
 * press "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
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

	/**
	 * 0 is success.
	 * 1 is either Playwright failed an assertion from a user-perspective, or a PHP fatal error has been logged.
	 * 2 is reserved, so we skip it.
	 * 3 is a warning, such as a PHP error that is not fatal.
	 *
	 * @link https://tldp.org/LDP/abs/html/exitcodes.html
	 */
	const WARNING = 3;

	public function __construct(
		E2EEnvironment $e2e_environment,
		Cache $cache,
		E2ETestManager $e2e_test_manager,
		WooExtensionsList $woo_extensions_list,
		LocalTestRunNotifier $test_run_notifier,
		PluginDependencies $dependencies
	) {
		$this->e2e_environment     = $e2e_environment;
		$this->cache               = $cache;
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
			->addArgument( 'test', InputArgument::OPTIONAL, '(Optional) The tests for the main extension under test. Accepts test tags, or a test directory. If not set, will use the "default" test tag of this extension.' )
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'The source of the main extension under test. Accepts a slug, a file, a URL. If not provided, the source will be the slug.' )
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
			->addOption( 'pw_options', null, InputOption::VALUE_OPTIONAL, 'Additional options and parameters to pass to Playwright, eg: "--retries=0", etc.' )
			->addOption( 'dependencies', null, InputOption::VALUE_OPTIONAL, 'How to handle dependencies of the SUT and additional plugins. Possible values: ' . implode( ', ', Extension::ACTIONS ), Extension::ACTIONS['bootstrap'] )
			->addOption( 'ui', null, InputOption::VALUE_NONE, 'Runs tests in UI mode.' )
			->addOption( 'codegen', 'c', InputOption::VALUE_NONE, 'Run environment for Codegen.' )
			->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'If set, it will just start the environment and keep it running until shut down.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$this->prepare_output( $output );

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
			$source = ''; // No source provided, it will be inferred from the SUT.
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
			// Remove wrapping double quotes if they exist.
			if ( substr( $pw_options, 0, 1 ) === '"' && substr( $pw_options, - 1 ) === '"' ) {
				$pw_options = substr( $pw_options, 1, - 1 );
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
		$this->finalize_sut_definition(
			$woo_extension,
			$woo_extension_id,
			$source,
			$test,
			$input->getOption( 'dependencies' ),
			$env_up_options,
			$input
		);

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

		if ( ! empty( $woo_extension ) ) {
			App::setVar( 'QIT_SUT', (int) $woo_extension_id );
		}

		App::setVar( 'should_upload_report', ! $input->getOption( 'no_upload_report' ) );
		App::setVar( 'QIT_ENV_UP_OPTIONS', $env_up_options );

		if ( getenv( 'QIT_TESTING_ENV_CONFIG' ) ) {
			// Print out the final merged configuration for inspection.
			$this->output->writeln( json_encode( $env_up_options, JSON_PRETTY_PRINT ) );

			// Early return before environment setup and test execution.
			return Command::SUCCESS;
		}

		if ( $wait ) {
			putenv( 'QIT_HIDE_SITE_INFO=0' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		} else {
			putenv( 'QIT_HIDE_SITE_INFO=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO=DOCKER' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		$up_exit_status_code = $env_up_command->run(
			new ArrayInput( $env_up_options ),
			new StreamOutput( $resource_stream )
		);

		putenv( 'QIT_HIDE_SITE_INFO' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		$up_output = stream_get_contents( $resource_stream, - 1, 0 );

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

	/**
	 * Prepare the output for JSON mode if needed.
	 *
	 * @param OutputInterface $output
	 *
	 * @return void
	 */
	protected function prepare_output( OutputInterface $output ): void {
		$this->output = $output;

		if ( App::getVar( 'QIT_JSON_MODE' ) === true || defined( 'UNIT_TESTS' ) ) {
			// Ensure $output is a StreamOutput to call getStream().
			if ( ! $output instanceof StreamOutput ) {
				throw new \RuntimeException( 'QIT_JSON_MODE is set, but OutputInterface is not a StreamOutput.' );
			}

			$stream = $output->getStream();
			if ( ! stream_filter_append( $stream, 'qit_json' ) ) {
				exit( 152 );
			}
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

	/**
	 * @param array<string> $env_vars
	 *
	 * @return void
	 */
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

	/**
	 * Finalize the SUT definition by merging CLI and config:
	 * - Load config
	 * - Determine final source and test_tags
	 * - Ensure action=test
	 *
	 * @param string|null                                  $woo_extension
	 * @param int|null                                     $woo_extension_id
	 * @param string|null                                  $source
	 * @param string|null                                  $test
	 * @param string                                       $dependencies_option
	 * @param array<string,array|bool|string|int|string[]> $env_up_options
	 *
	 * @return void
	 */
	protected function finalize_sut_definition(
		$woo_extension,
		$woo_extension_id,
		$source,
		$test,
		$dependencies_option,
		array &$env_up_options,
		InputInterface $input
	) {
		if ( empty( $woo_extension ) ) {
			return;
		}

		// Validate dependencies action.
		if ( $dependencies_option !== 'none' && ! in_array( $dependencies_option, Extension::ACTIONS, true ) ) {
			throw new \RuntimeException( sprintf(
				'Invalid dependencies action. Possible values: none, %s',
				implode( ', ', Extension::ACTIONS )
			) );
		}

		// Load qit.yml.
		$env_config = App::make( \QIT_CLI\Environment\EnvConfigLoader::class )->load_config();

		// Determine SUT base configuration.
		if ( isset( $env_config['plugins'][ $woo_extension ] ) ) {
			$sut_base = $env_config['plugins'][ $woo_extension ];
		} else {
			$sut_base = [
				'slug'      => $woo_extension,
				'source'    => $woo_extension,
				'test_tags' => [ 'default' ],
			];
		}

		// Override SUT source if provided.
		if ( ! empty( $source ) ) {
			$sut_base['source'] = $source;
		}

		// If CLI provides test tags, override qit.yml tags entirely.
		if ( ! empty( $test ) ) {
			$cli_tags              = array_map( 'trim', explode( ',', $test ) );
			$sut_base['test_tags'] = $cli_tags;
		} else {
			// If no tags in qit.yml and none via CLI, default to ['default'].
			if ( empty( $sut_base['test_tags'] ) ) {
				$sut_base['test_tags'] = [ 'default' ];
			}
		}

		// Ensure SUT action is set to 'test' if not already set.
		if ( ! isset( $sut_base['action'] ) ) {
			$sut_base['action'] = Extension::ACTIONS['test'];
		}

		// Process dependencies.
		$this->process_dependencies( $woo_extension_id, $dependencies_option, $env_up_options );

		// Merge SUT back into env_config.
		$env_config['plugins'][ $woo_extension ] = $sut_base;

		// Handle additional plugins passed via CLI.
		$cli_plugins = $input->getOption( 'plugin' );
		if ( ! empty( $cli_plugins ) ) {
			foreach ( $cli_plugins as $cli_plugin ) {
				// Format: slug[:action[:comma-separated-tags]].
				$parts      = explode( ':', $cli_plugin );
				$cli_slug   = $parts[0];
				$cli_action = Extension::ACTIONS['test'];
				$cli_tags   = [];

				if ( isset( $parts[1] ) && in_array( $parts[1], Extension::ACTIONS, true ) ) {
					$cli_action = $parts[1];
					if ( isset( $parts[2] ) && ! empty( $parts[2] ) ) {
						$cli_tags = array_map( 'trim', explode( ',', $parts[2] ) );
					}
				} else {
					// If no explicit action found, any extra parts are considered tags.
					if ( isset( $parts[1] ) ) {
						$cli_tags = array_map( 'trim', explode( ',', $parts[1] ) );
					}
				}

				// If plugin not defined in qit.yml, create a new entry.
				if ( ! isset( $env_config['plugins'][ $cli_slug ] ) ) {
					$env_config['plugins'][ $cli_slug ] = [
						'slug'      => $cli_slug,
						'source'    => $cli_slug,
						'test_tags' => empty( $cli_tags ) ? [ 'default' ] : $cli_tags,
						'action'    => $cli_action,
					];
				} else {
					// Override any existing qit.yml tags completely with CLI tags if provided.
					if ( ! empty( $cli_tags ) ) {
						$env_config['plugins'][ $cli_slug ]['test_tags'] = $cli_tags;
					} elseif ( empty( $env_config['plugins'][ $cli_slug ]['test_tags'] ) ) {
						$env_config['plugins'][ $cli_slug ]['test_tags'] = [ 'default' ];
					}

					$env_config['plugins'][ $cli_slug ]['action'] = $cli_action;
				}
			}
		}

		// Normalize test_tags to ensure arrays and split if any leftover commas exist (unlikely now).
		foreach ( $env_config['plugins'] as $plugin_slug => &$plugin_config ) {
			if ( ! isset( $plugin_config['test_tags'] ) || empty( $plugin_config['test_tags'] ) ) {
				$plugin_config['test_tags'] = [ 'default' ];
			} else {
				$plugin_config['test_tags'] = (array) $plugin_config['test_tags'];
				$normalized_tags            = [];
				foreach ( $plugin_config['test_tags'] as $tag ) {
					if ( strpos( $tag, ',' ) !== false ) {
						$split_tags      = array_map( 'trim', explode( ',', $tag ) );
						$normalized_tags = array_merge( $normalized_tags, $split_tags );
					} else {
						$normalized_tags[] = $tag;
					}
				}
				$plugin_config['test_tags'] = $normalized_tags;
			}

			if ( ! isset( $plugin_config['action'] ) ) {
				$plugin_config['action'] = Extension::ACTIONS['test'];
			}

			if ( ! isset( $plugin_config['slug'] ) ) {
				$plugin_config['slug'] = $plugin_slug;
			}

			if ( ! isset( $plugin_config['source'] ) ) {
				$plugin_config['source'] = $plugin_slug;
			}
		}
		unset( $plugin_config );

		// Update env_up_options with the final plugin list.
		$env_up_options['--plugin'] = array_values( $env_config['plugins'] );
	}

	/**
	 * @param int|null                                     $woo_extension_id
	 * @param string                                       $dependencies_option
	 * @param array<string,array|bool|string|int|string[]> $env_up_options
	 *
	 * @return void
	 */
	protected function process_dependencies( $woo_extension_id, $dependencies_option, array &$env_up_options ): void {
		if ( $dependencies_option === 'none' ) {
			return;
		}

		$dependencies = $this->dependencies->get_plugin_and_php_ext_dependencies( $woo_extension_id, [] );

		// Initialize arrays if not already set.
		if ( ! isset( $env_up_options['--php_extension'] ) ) {
			$env_up_options['--php_extension'] = [];
		}

		if ( ! isset( $env_up_options['--plugin'] ) ) {
			$env_up_options['--plugin'] = [];
		}

		// Add PHP extensions, avoiding duplicates.
		foreach ( $dependencies['php_extensions'] as $php_extension ) {
			$this->output->writeln( sprintf( 'Adding PHP extension dependency: %s', $php_extension ) );
			if ( ! in_array( $php_extension, $env_up_options['--php_extension'], true ) ) {
				$env_up_options['--php_extension'][] = $php_extension;
			}
		}

		// Add Plugins, avoiding duplicates and handling --woo option if necessary.
		foreach ( $dependencies['plugins'] as $dep_plugin ) {
			// If --woo is set and the plugin is a WooCommerce plugin, skip it.
			if ( ! empty( $env_up_options['--woo'] ) && stripos( $dep_plugin, 'woocommerce:' ) !== false ) {
				continue;
			}

			// Extract the plugin slug (before the colon, if present).
			$plugin_slug = strtok( $dep_plugin, ':' );

			// Check if the plugin is already present in env_up_options['--plugin'].
			$already_present = false;
			foreach ( $env_up_options['--plugin'] as $existing_plugin ) {
				if ( stripos( $existing_plugin, $plugin_slug ) !== false ) {
					$already_present = true;
					break;
				}
			}

			if ( ! $already_present ) {
				$formatted_plugin = "{$dep_plugin}:{$dependencies_option}";
				$this->output->writeln( sprintf( 'Adding plugin dependency: %s', $formatted_plugin ) );
				$env_up_options['--plugin'][] = $formatted_plugin;
			}
		}
	}
}
