<?php

namespace QIT_CLI\Performance\Commands;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Performance\Environment\PerformanceEnvInfo;
use QIT_CLI\Performance\PerformanceTestManager;
use QIT_CLI\LocalTests\EnvironmentRunner;
use QIT_CLI\OptionReuseTrait;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Tunnel\TunnelRunner;
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

	protected static $defaultName = 'run:performance'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		Cache $cache,
		PerformanceTestManager $performance_test_manager,
		EnvironmentRunner $environment_runner
	) {
		$this->cache = $cache;
		$this->performance_test_manager = $performance_test_manager;
		$this->environment_runner = $environment_runner;

		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['performance']['properties'] ) ) {
			throw new \RuntimeException( 'Performance schema not set or incomplete.' );
		}

		// Use dedicated performance schema
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
			$options = $this->parse_options( $input );
			$env_up_options = $options['env_up'];
			$env_up_options['--tunnel'] = TunnelRunner::get_tunnel_value( $input );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );
			return Command::FAILURE;
		}

		// Add the SUT as a plugin for the environment
		$plugins = $input->getOption( 'plugin' ) ?: [];
		$plugins[] = $sut;
		$env_up_options['--plugin'] = $plugins;

		// Handle the --source option
		if ( ! empty( $input->getOption( 'source' ) ) ) {
			$env_up_options['--source'] = $input->getOption( 'source' );
		}

		$env_up_options['--json'] = true;

		if ( $output->isVerbose() ) {
			$env_up_options['--verbose'] = true;
		} elseif ( $output->isVeryVerbose() ) {
			$env_up_options['--very-verbose'] = true;
		}

		try {
			// Set environment variables
			putenv( 'QIT_HIDE_SITE_INFO=1' );
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO=DOCKER' );
			putenv( 'QIT_UP_AND_TEST=1' );
			putenv( 'QIT_ENVIRONMENT_TYPE=performance' );

			// Create the Performance environment using EnvironmentRunner
			/** @var PerformanceEnvInfo $env_info */
			$env_info = $this->environment_runner->run_environment( $env_up_options );

			// Set up SUT information
			$env_info->sut_slug = $sut;
			$env_info->sut_type = 'plugin'; // Default to plugin
			$env_info->test_tag = $input->getOption( 'test_tag' ) ?? '';

			// Set the output interface for the performance test manager
			$this->performance_test_manager->set_output( $output );

			// Run performance tests using PerformanceTestManager
			$exit_code = $this->performance_test_manager->run_tests( $env_info );

			// Handle JSON output if requested
			if ( $input->getOption( 'json' ) ) {
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
			}

			return $exit_code;

		} catch ( \Exception $e ) {
			$output->writeln( "<error>Performance test failed: {$e->getMessage()}</error>" );
			return Command::FAILURE;
		} finally {
			// Clean up environment variables
			putenv( 'QIT_HIDE_SITE_INFO' );
			putenv( 'QIT_EXPOSE_ENVIRONMENT_TO' );
			putenv( 'QIT_UP_AND_TEST' );
			putenv( 'QIT_ENVIRONMENT_TYPE' );
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
}