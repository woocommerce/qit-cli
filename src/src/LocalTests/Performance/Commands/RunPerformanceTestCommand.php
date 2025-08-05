<?php

namespace QIT_CLI\LocalTests\Performance\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\LocalTests\EnvironmentRunner;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\LocalTests\Performance\PerformanceTestManager;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\PluginDependencies;
use QIT_CLI\TestGroup;
use QIT_CLI\Tunnel\TunnelRunner;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunPerformanceTestCommand extends DynamicCommand {
	use OptionReuseTrait;

	/** @var Cache */
	protected $cache;

	/** @var PerformanceTestManager */
	protected $performance_test_manager;

	/** @var EnvironmentRunner */
	protected $environment_runner;

	/** @var LocalTestRunNotifier */
	protected $test_run_notifier;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	/** @var PluginDependencies */
	protected $dependencies;

	/** @var TestGroup */
	protected $test_group;

	protected static $defaultName = 'run:performance'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		Cache $cache,
		PerformanceTestManager $performance_test_manager,
		EnvironmentRunner $environment_runner,
		LocalTestRunNotifier $test_run_notifier,
		WooExtensionsList $woo_extensions_list,
		PluginDependencies $dependencies,
		TestGroup $test_group
	) {
		$this->cache                    = $cache;
		$this->performance_test_manager = $performance_test_manager;
		$this->environment_runner       = $environment_runner;
		$this->test_run_notifier        = $test_run_notifier;
		$this->woo_extensions_list      = $woo_extensions_list;
		$this->dependencies             = $dependencies;
		$this->test_group               = $test_group;

		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure(): void {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['performance']['properties'] ) ) {
			throw new \RuntimeException( 'Performance schema not set or incomplete.' );
		}

		$this
			->setDescription( 'Run Performance tests.' )
			->setHelp( 'Run k6 performance tests against a given extension.' )
			->addArgument( 'woo_extension', InputArgument::OPTIONAL, 'The slug or WooCommerce ID of the main extension under test.' )
			->addArgument( 'test', InputArgument::OPTIONAL, '(Optional) The tests for the main extension under test. Accepts test tags, or a test directory. If not set, will use the "default" test tag of this extension.' )
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'The source of the main extension under test. Accepts a slug, a file, a URL. If not provided, the source will be the slug.' )
			->addOption( 'sut_action', null, InputOption::VALUE_OPTIONAL, 'What action to take on the SUT. Possible values: ' . implode( ', ', Extension::ACTIONS ), Extension::ACTIONS['test'] )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'wp' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'woo' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_version' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'plugin' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'theme' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'volume' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_extension' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'require' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'config' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'object_cache' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'skip_activating_plugins' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'skip_activating_themes' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'tunnel' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'json' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env_file' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'extension_set' )
			->addOption( 'no_upload_report', null, InputOption::VALUE_NONE, 'Do not upload the report to QIT Manager.' )
			->addOption( 'notify', null, InputOption::VALUE_NONE, 'If set, failures will be notified to the author of the SUT.' )
			->addOption( 'dependencies_mode', null, InputOption::VALUE_OPTIONAL, 'How to handle dependencies for recognized WooCommerce plugins. Possible values: ' . implode( ', ', PluginDependencies::DEPENDENCY_MODES['env_test'] ), PluginDependencies::DEPENDENCY_MODES['env_test']['bootstrap'] )
			->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'If set, it will just start the environment and keep it running until shut down.' )
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, '(Optional) Register the test run into a group.', false )
			->addOption( 'no_group', 'ng', InputOption::VALUE_NEGATABLE, 'If set, the CLI will not attempt to match the local test run with a group.', false );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		try {
			$options                    = $this->parse_options( $input );
			$env_up_options             = $options['env_up'];
			$env_up_options['--tunnel'] = TunnelRunner::get_tunnel_value( $input );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		$wait = $input->getOption( 'up_only' );

		$result = $this->validate_input( $input, $output, $wait );
		if ( $result !== Command::SUCCESS ) {
			return $result;
		}

		$this->parse_env_vars( $input->getOption( 'env' ) );

		$woo_extension_raw = $input->getArgument( 'woo_extension' );
		[ $woo_extension_id, $woo_extension_slug, $sut_type_or_code ] = $this->resolve_woo_extension( $woo_extension_raw, $output );
		if ( $sut_type_or_code === Command::INVALID ) {
			// Failed to resolve extension.
			return Command::INVALID;
		}
		$sut_type = $sut_type_or_code;

		$group = $input->getOption( 'group' );

		if ( $group ) {
			$group_options = [
				'woo_id' => $woo_extension_id,
				'local'  => true,
			];

			if ( ! empty( $input->getOption( 'extension_set' ) ) ) {
				$group_options['extension_set'] = $input->getOption( 'extension_set' );
			}

			$test_type = 'performance';

			try {
				$env_vars      = getenv();
				$input_options = $input;
				$this->test_group->create_or_update( $group_options, $test_type, $output, $input_options, $env_vars );
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );
				return Command::FAILURE;
			}

			$output->writeln( sprintf( '<info>Group item successfully added.</info>' ) );

			return Command::SUCCESS;
		}

		if ( ! empty( $input->getOption( 'config' ) ) ) {
			App::setVar( 'QIT_CONFIG_OVERRIDE', $input->getOption( 'config' ) );
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

		$this->handle_termination();

		if ( ! empty( $woo_extension_slug ) ) {
			App::setVar( 'QIT_SUT', $woo_extension_id );
			App::setVar( 'QIT_SUT_SLUG', $woo_extension_slug );
		}

		$env_up_options = $this->add_sut_to_env_up_options( $input, $env_up_options, $woo_extension_slug, $sut_type );

		App::setVar( 'should_upload_report', ! $input->getOption( 'no_upload_report' ) );
		App::setVar( 'QIT_ENV_UP_OPTIONS', $env_up_options );

		if ( $wait ) {
			putenv( 'QIT_HIDE_SITE_INFO=0' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		} else {
			putenv( 'QIT_HIDE_SITE_INFO=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO=DOCKER' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_ENVIRONMENT_TYPE=performance' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		// Add the environment type to the env:up options.
		$env_up_options['--environment_type'] = 'performance';

		try {
			/** @var PerformanceEnvInfo $env_info */
			$env_info = $this->environment_runner->run_environment( $env_up_options );

			$GLOBALS['env_to_shutdown'] = $env_info;

			if ( getenv( 'QIT_SELF_TEST' ) === 'env_info' ) {
				$output->write( json_encode( $env_info ) );

				return Command::SUCCESS;
			}
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		} finally {
			putenv( 'QIT_HIDE_SITE_INFO' );    // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		$test_tag = $input->getArgument( 'test' ) ?? '';

		if ( $env_info instanceof PerformanceEnvInfo && ! empty( $woo_extension_id ) ) {
			$env_info->sut_slug = $woo_extension_slug;
			$env_info->sut_type = $sut_type;
			$env_info->test_tag = $test_tag;

			$this->test_run_notifier->notify_test_started(
				$woo_extension_id,
				$input->getOption( 'woo' ) ?? 'latest',
				$env_info,
				$input->getOption( 'source' ) && file_exists( $input->getOption( 'source' ) ),
				$input->getOption( 'notify' )
			);
		}

		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->write( json_encode( $env_info ) );

			return Command::SUCCESS;
		}

		// If "up_only", don't run tests, just keep environment running.
		if ( $wait ) {
			return Command::SUCCESS;
		}

		// Run tests.
		$this->performance_test_manager->set_output( $output );
		$exit_status_code = $this->performance_test_manager->run_tests( $env_info );

		if ( $exit_status_code === Command::SUCCESS ) {
			$output->writeln( '<info>Performance tests passed.</info>' );
			return Command::SUCCESS;
		} else {
			$output->writeln( '<error>Performance tests failed.</error>' );
			return Command::FAILURE;
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

		App::setVar( 'QIT_PERFORMANCE_ENV_VARS', $parsed_vars );
	}

	/**
	 * @param string|null     $woo_extension_raw
	 * @param OutputInterface $output
	 *
	 * @return array{0:int|null,1:string|null,2:string|int|null} Array containing:
	 *                                                            [woo_extension_id, woo_extension_slug, sut_type or Command::INVALID]
	 */
	private function resolve_woo_extension( ?string $woo_extension_raw, OutputInterface $output ): array {
		if ( empty( $woo_extension_raw ) ) {
			return [ null, null, null ]; // no extension provided.
		}

		try {
			if ( is_numeric( $woo_extension_raw ) ) {
				$woo_extension_id   = (int) $woo_extension_raw;
				$woo_extension_slug = $this->woo_extensions_list->get_woo_extension_slug_by_id( $woo_extension_id );
			} else {
				$woo_extension_slug = $woo_extension_raw;
				$woo_extension_id   = $this->woo_extensions_list->get_woo_extension_id_by_slug( $woo_extension_slug );
			}
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return [ null, null, Command::INVALID ];
		}

		$sut_type = $this->woo_extensions_list->get_woo_extension_type( $woo_extension_id );
		putenv( "QIT_SUT=$woo_extension_slug" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		return [ $woo_extension_id, $woo_extension_slug, $sut_type ];
	}

	private function validate_input( InputInterface $input, OutputInterface $output, bool $wait ): int {
		$woo     = $input->getOption( 'woo' );
		$plugins = $input->getOption( 'plugin' );

		if ( ! empty( $woo ) && ! empty( $plugins ) && in_array( 'woocommerce', $plugins, true ) ) {
			$output->writeln( '<error>Cannot use both "--woo" and "--plugin woocommerce" together.</error>' );

			return Command::INVALID;
		}

		$woo_extension_raw = $input->getArgument( 'woo_extension' );
		if ( empty( $woo_extension_raw ) ) {
			if ( ! empty( $input->getOption( 'source' ) ) || ! empty( $input->getOption( 'sut_action' ) ) ) {
				$output->writeln( '<error>The extension parameter is required when source or sut_action is set.</error>' );

				return Command::INVALID;
			}
			if ( ! $wait ) {
				$output->writeln( '<error>The extension parameter is required unless in --up_only mode.</error>' );

				return Command::INVALID;
			}
		}

		return Command::SUCCESS;
	}

	/**
	 * @param InputInterface $input
	 * @param array<mixed>   $env_up_options
	 * @param string|null    $woo_extension_slug
	 * @param string|null    $sut_type 'plugin', 'theme', or null.
	 *
	 * @return array<mixed> Updated env_up_options.
	 */
	private function add_sut_to_env_up_options( InputInterface $input, array $env_up_options, ?string $woo_extension_slug, ?string $sut_type ): array {
		if ( ! $woo_extension_slug ) {
			return $env_up_options;
		}

		// If no explicit SUT type, default to 'plugin'.
		if ( ! $sut_type ) {
			$sut_type = 'plugin';
		}

		$key = ( $sut_type === 'theme' ) ? '--theme' : '--plugin';

		// Gather CLI overrides.
		$cli_action    = $input->getOption( 'sut_action' );
		$cli_test      = $input->getArgument( 'test' );
		$cli_test_tags = $cli_test ? explode( ',', $cli_test ) : [];
		$cli_source    = $input->getOption( 'source' );

		// STEP 1: Find & parse any existing entry for this slug from qit.yml or earlier merges.
		$old_index     = null;
		$existing_data = [];

		if ( ! empty( $env_up_options[ $key ] ) ) {
			foreach ( $env_up_options[ $key ] as $i => $entry ) {
				$decoded = json_decode( $entry, true );
				// If it's valid JSON with the correct slug.
				if ( is_array( $decoded ) && ! empty( $decoded['slug'] ) && $decoded['slug'] === $woo_extension_slug ) {
					$old_index     = $i;
					$existing_data = $decoded;
					break;
				}
			}
		}

		// STEP 2: Remove that old entry if found, so we don't have duplicates.
		if ( $old_index !== null ) {
			array_splice( $env_up_options[ $key ], $old_index, 1 );
		}

		// STEP 3: Partially merge the user's CLI overrides onto the existing data.
		// If there was no old entry, $existing_data is empty → we start fresh.
		// Ensure we at least have 'slug'.
		$extension_data         = $existing_data;
		$extension_data['slug'] = $woo_extension_slug;

		// If CLI explicitly sets a source, override the old one.
		if ( $cli_source ) {
			$extension_data['source'] = $cli_source;
		}

		// If CLI explicitly sets an action, override the old one.
		if ( $cli_action ) {
			$extension_data['action'] = $cli_action;
		}

		// Currently, CLI test tags override any existing test_tags.
		// We could change it to array_merge below to merge instead.
		if ( ! empty( $cli_test_tags ) ) {
			$extension_data['test_tags'] = $cli_test_tags;
		} else {
			// If we never set test_tags, ensure it at least exists.
			if ( empty( $extension_data['test_tags'] ) ) {
				$extension_data['test_tags'] = [ 'default' ];
			}
		}

		$extension_data['priority'] = Extension::PRIORITY_LOW;

		// STEP 4: Re‐insert this final single definition for that slug.
		$env_up_options[ $key ][] = json_encode( $extension_data );

		return $env_up_options;
	}

	/**
	 * Handle termination signals and register shutdown functions to ensure environment cleanup.
	 */
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

	/**
	 * Shutdown the test environment if one is running.
	 */
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
		} catch ( \Exception $e ) { // phpcs:ignore
			// no-op.
		}
	}
}
