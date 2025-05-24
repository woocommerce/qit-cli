<?php
/*
 * We need this to shut down the environment if the user
 * presses "Ctrl+C" and has the "pcntl" extension installed.
 */
declare( ticks=1 );

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\PluginDependencies;
use QIT_CLI\LocalTests\E2E\CustomE2ERunner;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\Environment\Extension;
use QIT_CLI\LocalTests\EnvironmentRunner;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\TestGroup;
use QIT_CLI\Tunnel\TunnelRunner;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use function QIT_CLI\is_windows;

class RunE2ECommand extends DynamicCommand {
	use OptionReuseTrait;

	protected E2EEnvironment $e2e_environment;
	protected Cache $cache;
	protected OutputInterface $output;
	protected CustomE2ERunner $spec_custom_test_orchestrator;
	protected WooExtensionsList $woo_extensions_list;
	protected LocalTestRunNotifier $test_run_notifier;
	protected PluginDependencies $dependencies;
	protected EnvironmentRunner $environment_runner;
	protected TestGroup $test_group;

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
		CustomE2ERunner $spec_custom_test_orchestrator,
		WooExtensionsList $woo_extensions_list,
		LocalTestRunNotifier $test_run_notifier,
		PluginDependencies $dependencies,
		EnvironmentRunner $environment_runner,
		TestGroup $test_group
	) {
		$this->e2e_environment               = $e2e_environment;
		$this->cache                         = $cache;
		$this->woo_extensions_list           = $woo_extensions_list;
		$this->test_run_notifier             = $test_run_notifier;
		$this->dependencies                  = $dependencies;
		$this->environment_runner            = $environment_runner;
		$this->test_group                    = $test_group;
		$this->spec_custom_test_orchestrator = $spec_custom_test_orchestrator;

		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure(): void {
		parent::configure();

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
			->addArgument(
				'runner_args',
				InputArgument::IS_ARRAY,
				'Any arguments after a double-dash (--) go here.'
			)
			->addOption( 'source', null, InputOption::VALUE_OPTIONAL, 'The source of the main extension under test. Accepts a slug, a file, a URL. If not provided, the source will be the slug.' )
			// ->addOption( 'sut_action', null, InputOption::VALUE_OPTIONAL, 'What action to take on the SUT. Possible values: ' . implode( ', ', Extension::ACTIONS ), Extension::ACTIONS['test'] )
			->addOption( 'pw_test_tag', null, InputOption::VALUE_OPTIONAL, 'The Playwright test tag to run.', '' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'wp_version' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'woo_version' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'plugin' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'theme' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'volume' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_extension' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'object_cache' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'skip_activating_plugins' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'skip_activating_themes' )
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
			->addOption( 'dependencies_mode', null, InputOption::VALUE_OPTIONAL, 'How to handle dependencies for recognized WooCommerce plugins. Possible values: ' . implode( ', ', PluginDependencies::DEPENDENCY_MODES['env_test'] ), PluginDependencies::DEPENDENCY_MODES['env_test']['bootstrap'] )
			->addOption( 'ui', null, InputOption::VALUE_NONE, 'Runs tests in UI mode.' )
			->addOption( 'codegen', 'c', InputOption::VALUE_NONE, 'Run environment for Codegen.' )
			->addOption( 'up_only', 'u', InputOption::VALUE_NONE, 'If set, it will just start the environment and keep it running until shut down.' )
			->addOption( 'group', 'g', InputOption::VALUE_NEGATABLE, '(Optional) Register the test run into a group.', false )
			->addOption( 'no_group', 'ng', InputOption::VALUE_NEGATABLE, 'If set, the CLI will not attempt to match the local test run with a group.', false );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		$this->prepare_output( $output );

		if ( is_windows() ) {
			$output->writeln( '<comment>To run E2E Tests on Windows, please use WSL.</comment>' );

			return self::FAILURE;
		}

		try {
			$options                    = $this->parse_options( $input );
			$env_up_options             = $options['env_up'];
			$env_up_options['--tunnel'] = TunnelRunner::get_tunnel_value( $input );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return self::FAILURE;
		}

		try {
			$wait = (bool) $input->getOption( 'up_only' );

			[ $test_mode, $wait ] = [ 'headless', $wait ];
		} catch ( \RuntimeException $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return self::INVALID;
		}

		App::setVar( 'TEST_MODE', $test_mode );

		$result = $this->validate_input( $input, $output, $wait );
		if ( $result !== self::SUCCESS ) {
			return $result;
		}

		$this->configure_pw_options( $input );
		$this->parse_env_vars( $input->getOption( 'env' ) );

		$woo_extension_raw = $input->getArgument( 'woo_extension' );
		[ $woo_extension_id, $woo_extension_slug, $sut_type_or_code ] = $this->resolve_woo_extension( $woo_extension_raw, $output );
		if ( $sut_type_or_code === self::INVALID ) {
			// Failed to resolve extension.
			return self::INVALID;
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

			$test_type = ! empty( $input->getArgument( 'test' ) ) ? 'activation' : 'e2e';

			try {
				$env_vars      = getenv();
				$input_options = $input;
				$this->test_group->create_or_update( $group_options, $test_type, $output, $input_options, $env_vars );
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '<comment>%s</comment>', $e->getMessage() ) );

				return self::FAILURE;
			}

			$output->writeln( sprintf( '<info>Group item successfully added.</info>' ) );

			return self::SUCCESS;
		}

		if ( $input->getOption( 'skip_activating_plugins' ) ) {
			$this->e2e_environment->set_skip_activating_plugins( true );
		}

		if ( $input->getOption( 'skip_activating_themes' ) ) {
			$this->e2e_environment->set_skip_activating_themes( true );
		}

		if ( ! empty( $input->getOption( 'config' ) ) ) {
			App::setVar( 'QIT_CONFIG_OVERRIDE', $input->getOption( 'config' ) );
		}

		$shard = $input->getOption( 'shard' );
		if ( $shard && preg_match( '/^\d+\/\d+$/', $shard ) ) { // phpcs:ignore
			// Already validated in validate_input().
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

		if ( ! $wait ) {
			putenv( 'QIT_HIDE_SITE_INFO=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		try {
			/** @var E2EEnvInfo $env_info */
			$env_info = $this->environment_runner->run_environment( $env_up_options );

			$env_info->runner_args = $input->getArgument( 'runner_args' );

			if ( getenv( 'QIT_SELF_TEST' ) === 'env_info' ) {
				$output->write( json_encode( $env_info ) );

				return self::SUCCESS;
			}
		} catch ( \Exception $e ) {
			$this->output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return self::FAILURE;
		} finally {
			putenv( 'QIT_HIDE_SITE_INFO' );    // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		}

		$test_tag = $input->getOption( 'pw_test_tag' ) ?? 'full';

		if ( $env_info instanceof E2EEnvInfo && ! empty( $woo_extension_id ) ) {
			$env_info->sut_slug             = $woo_extension_slug;
			$env_info->sut_id               = $woo_extension_id;
			$env_info->sut_type             = $sut_type;
			$env_info->pw_test_tag          = $test_tag;
			$env_info->is_development_build = $input->getOption( 'source' ) && file_exists( $input->getOption( 'source' ) );
			$env_info->notify               = $input->getOption( 'notify' );
		}

		$GLOBALS['env_to_shutdown'] = $env_info;

		if ( getenv( 'QIT_SELF_TEST' ) === 'env_up' ) {
			$output->write( json_encode( $env_info ) );

			return self::SUCCESS;
		}

		$shard            = $input->getOption( 'shard' );
		$io               = new SymfonyStyle( $input, $output );
		$exit_status_code = $this->spec_custom_test_orchestrator->run_custom_e2e_tests( $env_info, $io, $input->getOption( 'up_only' ) );
		$io               = new SymfonyStyle( $input, $output );
		$io->setDecorated( true );

		// If "up_only" or "bootstrap", don't print final message. Assume if we got here, user already used the environment and already destroyed it.
		if ( $wait ) {
			return self::SUCCESS;
		}

		if ( $exit_status_code === self::SUCCESS ) {
			$io->success( "Tests passed. Run 'qit e2e-report' to view the report." );

			return self::SUCCESS;
		} else {
			$io->error( "Tests failed. Run 'qit e2e-report' to view the report." );

			return self::FAILURE;
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
		} catch ( \Exception $e ) { // phpcs:ignore
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
				exit( 0 );
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
				$parsed_options['env_up']["--$option_name"] = $option_value;
			}
		}

		return $parsed_options;
	}

	/**
	 * @param array<string> $env_vars
	 *
	 * @return void
	 *
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
	}*/

	/**
	 * @param string|null $woo_extension_raw
	 * @param OutputInterface $output
	 *
	 * @return array{0:int|null,1:string|null,2:string|int|null} Array containing:
	 *                                                            [woo_extension_id, woo_extension_slug, sut_type or self::INVALID]
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

			return [ null, null, self::INVALID ];
		}

		$sut_type = $this->woo_extensions_list->get_woo_extension_type( $woo_extension_id );
		putenv( "QIT_SUT=$woo_extension_slug" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv

		return [ $woo_extension_id, $woo_extension_slug, $sut_type ];
	}

	private function validate_input( InputInterface $input, OutputInterface $output, bool $wait ): int {
		$woo     = $input->getOption( 'woo_version' );
		$plugins = $input->getOption( 'plugin' );

		if ( ! empty( $woo ) && ! empty( $plugins ) && in_array( 'woocommerce', $plugins, true ) ) {
			$output->writeln( '<error>Cannot use both "--woo" and "--plugin woocommerce" together.</error>' );

			return self::INVALID;
		}

		$shard = $input->getOption( 'shard' );
		if ( ! is_null( $shard ) ) {
			if ( ! preg_match( '/^\d+\/\d+$/', $shard ) ) {
				$output->writeln( '<error>Invalid shard format. Should be current/total, e.g., 1/5.</error>' );

				return self::INVALID;
			}
			[ $current, $total ] = explode( '/', $shard );
			if ( $current <= 0 || $current > $total ) {
				$output->writeln( '<error>Invalid shard format. current must be > 0 and <= total.</error>' );

				return self::INVALID;
			}
		}

		$woo_extension_raw = $input->getArgument( 'woo_extension' );
		if ( empty( $woo_extension_raw ) ) {
			if ( ! empty( $input->getOption( 'source' ) ) || ! empty( $input->getOption( 'sut_action' ) ) ) {
				$output->writeln( '<error>The extension parameter is required when source or sut_action is set.</error>' );

				return self::INVALID;
			}
			if ( ! $wait ) {
				$output->writeln( '<error>The extension parameter is required unless in --up_only or --codegen mode.</error>' );

				return self::INVALID;
			}
		}

		return self::SUCCESS;
	}

	private function configure_pw_options( InputInterface $input ): void {
		$pw_options = $input->getOption( 'pw_options' ) ?? '';
		if ( ! empty( $pw_options ) ) {
			// Strip surrounding quotes if present.
			if ( substr( $pw_options, 0, 1 ) === '"' && substr( $pw_options, - 1 ) === '"' ) {
				$pw_options = substr( $pw_options, 1, - 1 );
			}
		}

		if ( $input->getOption( 'update_snapshots' ) ) {
			$pw_options .= ' --update-snapshots';
		}

		App::setVar( 'pw_options', $pw_options );
	}

	/**
	 * @param InputInterface $input
	 * @param array<mixed> $env_up_options
	 * @param string|null $woo_extension_slug
	 * @param string|null $sut_type 'plugin', 'theme', or null.
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
		$test_arg      = $input->getArgument( 'test' );
		$cli_test_tags = $test_arg ? explode( ',', $test_arg ) : [];
		$cli_source    = $input->getOption( 'source' );

		// STEP 1: Find & parse any existing entry for this slug from qit.yml or earlier merges.
		$old_index     = null;
		$existing_data = [];

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
}
