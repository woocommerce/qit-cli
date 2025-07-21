<?php

namespace QIT_CLI\LocalTests\Performance\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Environment\Extension;
use QIT_CLI\LocalTests\EnvironmentRunner;
use QIT_CLI\LocalTests\LocalTestRunNotifier;
use QIT_CLI\LocalTests\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\LocalTests\Performance\PerformanceTestManager;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Tunnel\TunnelRunner;
use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

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

	protected static $defaultName = 'run:performance'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		Cache $cache,
		PerformanceTestManager $performance_test_manager,
		EnvironmentRunner $environment_runner,
		LocalTestRunNotifier $test_run_notifier,
		WooExtensionsList $woo_extensions_list
	) {
		$this->cache                    = $cache;
		$this->performance_test_manager = $performance_test_manager;
		$this->environment_runner       = $environment_runner;
		$this->test_run_notifier        = $test_run_notifier;
		$this->woo_extensions_list      = $woo_extensions_list;

		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['performance']['properties'] ) ) {
			throw new \RuntimeException( 'Performance schema not set or incomplete.' );
		}

		// Use dedicated performance schema.
		DynamicCommandCreator::add_schema_to_command( $this, $schemas['performance'], [
			'php_version',
		], [] );

		$this
			->setDescription( 'Run Performance tests.' )
			->setHelp( 'Run k6 performance tests against a given extension.' )
			->addArgument( 'woo_extension', InputArgument::REQUIRED, 'A WooCommerce Extension Slug or Marketplace ID.' )
			->addOption( 'source', 's', InputOption::VALUE_OPTIONAL, 'The source of the main extension under test. Accepts a slug, a file, a URL.' )
			->addOption( 'test_tag', null, InputOption::VALUE_OPTIONAL, 'The performance test tag to run.', '' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'wp' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'woo' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_version' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'object_cache' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'plugin' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'php_extension' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'tunnel' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'require' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'extension_set' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'json' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'volume' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env' )
			->reuseOption( UpEnvironmentCommand::getDefaultName(), 'env_file' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$sut = $input->getArgument( 'woo_extension' );

		try {
			// Prepare environment options.
			$env_up_options = $this->prepare_environment_options( $input, $sut );

			// Set performance-specific environment variables.
			$this->set_performance_environment_variables();

			// Create environment and run tests.
			$env_info = $this->setup_environment_and_run_tests( $env_up_options, $sut, $input, $output );

			// Handle JSON output if requested.
			return $this->handle_json_output( $input, $output, $env_info );

		} catch ( \Exception $e ) {
			$output->writeln( "<error>Performance test failed: {$e->getMessage()}</error>" );
			return Command::FAILURE;
		} finally {
			$this->cleanup_environment_variables();
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
	 * Prepare environment options for the test.
	 *
	 * @return array<string, mixed>
	 */
	private function prepare_environment_options( InputInterface $input, string $sut ): array {
		$options        = $this->parse_options( $input );
		$env_up_options = $options['env_up'];

		// Add SUT as a plugin with proper extension structure.
		$plugins = $input->getOption( 'plugin' ) ?: [];

		// Create structured extension definition.
		$sut_extension = [
			'slug'      => $sut,
			'source'    => $input->getOption( 'source' ) ?: $sut,
			'action'    => Extension::ACTIONS['bootstrap'],
			'test_tags' => [ $input->getOption( 'test_tag' ) ?: 'default' ],
			'priority'  => Extension::PRIORITY_LOW,
		];

		$plugins[]                  = json_encode( $sut_extension );
		$env_up_options['--plugin'] = $plugins;

		// Add source if provided.
		if ( $input->getOption( 'source' ) ) {
			$env_up_options['--source'] = $input->getOption( 'source' );
		}

		// Common options.
		$env_up_options['--environment_type'] = 'performance';
		$env_up_options['--json']             = true;
		$env_up_options['--tunnel']           = TunnelRunner::get_tunnel_value( $input );

		// Verbosity.
		if ( $input->getOption( 'verbose' ) ) {
			$env_up_options['--verbose'] = true;
		}

		return $env_up_options;
	}

	/**
	 * Set performance-specific environment variables.
	 */
	private function set_performance_environment_variables(): void {
		putenv( 'QIT_HIDE_SITE_INFO=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_EXPOSE_ENVIRONMENT_TO=DOCKER' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_UP_AND_TEST=1' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_ENVIRONMENT_TYPE=performance' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
	}

	/**
	 * Clean up environment variables.
	 */
	private function cleanup_environment_variables(): void {
		putenv( 'QIT_HIDE_SITE_INFO' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_UP_AND_TEST' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
		putenv( 'QIT_ENVIRONMENT_TYPE' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
	}

	/**
	 * Setup environment and run tests.
	 *
	 * @param array<string, mixed> $env_up_options
	 */
	private function setup_environment_and_run_tests( array $env_up_options, string $sut, InputInterface $input, OutputInterface $output ): int {
		// Create environment.
		$env_info = $this->environment_runner->run_environment( $env_up_options );

		// Satisfy Phan check.
		if ( ! $env_info instanceof PerformanceEnvInfo ) {
			throw new \RuntimeException( 'Expected PerformanceEnvInfo, got ' . get_class( $env_info ) );
		}

		// Configure SUT information.
		$env_info->sut_slug = $sut;
		$env_info->sut_type = 'plugin';
		$env_info->test_tag = $input->getOption( 'test_tag' ) ?? '';

		// Notify test started.
		$woo_extension_id = $this->resolve_woo_extension_id( $sut, $output );
		$this->test_run_notifier->notify_test_started(
			$woo_extension_id,
			$input->getOption( 'woo' ) ?? 'latest',
			$env_info,
			$input->getOption( 'source' ) && file_exists( $input->getOption( 'source' ) ),
			false
		);

		// Run tests.
		$this->performance_test_manager->set_output( $output );
		return $this->performance_test_manager->run_tests( $env_info );
	}

	/**
	 * Handle JSON output if requested.
	 */
	private function handle_json_output( InputInterface $input, OutputInterface $output, int $exit_code ): int {
		if ( ! $input->getOption( 'json' ) ) {
			return $exit_code;
		}

		$test_run_id = App::make( Cache::class )->get( 'QIT_LAST_LOCAL_TEST_FINISHED' );

		if ( empty( $test_run_id ) ) {
			$output->writeln( json_encode( [ 'error' => 'No test run ID found.' ] ) );
			return Command::FAILURE;
		}

		$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-single' ) )
			->with_method( 'POST' )
			->with_post_body( [ 'test_run_id' => $test_run_id ] )
			->with_retry( 3 )
			->request();

		$output->writeln( $json );
		return $exit_code;
	}

	/**
	 * Resolve WooCommerce extension ID from slug or ID.
	 */
	private function resolve_woo_extension_id( string $woo_extension_raw, OutputInterface $output ): int {
		if ( is_numeric( $woo_extension_raw ) ) {
			return (int) $woo_extension_raw;
		}

		try {
			return $this->woo_extensions_list->get_woo_extension_id_by_slug( $woo_extension_raw );
		} catch ( \Exception $e ) {
			$error_message = sprintf( 'Extension "%s" not found in WooCommerce extensions list.', $woo_extension_raw );
			$output->writeln( sprintf( '<error>%s</error>', $error_message ) );
			throw new \RuntimeException( $error_message );
		}
	}
}
