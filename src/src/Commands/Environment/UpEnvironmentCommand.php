<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Environment\Environments\E2EEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_windows;

class UpEnvironmentCommand extends DynamicCommand {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var Cache */
	protected $cache;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, Cache $cache ) {
		$this->e2e_environment = $e2e_environment;
		$this->cache           = $cache;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['e2e']['properties'] ) ) {
			throw new \RuntimeException( 'E2E schema not set or incomplete.' );
		}

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['e2e'], [
			'compatibility',
			'optional_features',
			'additional_woo_plugins',
			'additional_wordpress_plugins',
		] );

		$this
			->setDescription( 'Starts a local test environment.' )
			->addOption( 'php-ext', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'PHP extension to install in the environment.', [] )
			->addOption( 'with-object-cache', 'c', InputOption::VALUE_NONE, 'Whether to enable Object Cache (Redis) in the environment.' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Whether to return raw JSON format.', false )
			->addOption( 'attached', 'a', InputOption::VALUE_NONE, 'Whether to attach to the environment after starting it.' )
			->setAliases( [ 'env:start' ]
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( is_windows() ) {
			$output->writeln( '<comment>Warning: It is highly recommended to run this script from Windows Subsystem for Linux (WSL) when using Windows.</comment>' );
		}

		try {
			$options = $this->parse_options( $input );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );

			return Command::FAILURE;
		}

		if ( $output->isVeryVerbose() ) {
			// Print the current options being used.
			$output->writeln( sprintf( 'Starting environment with options: %s', json_encode( $options ) ) );
		}

		$this->cache->set( 'environment_up_options', $options, DAY_IN_SECONDS );

		if ( $this->getDefinition()->hasOption( 'wordpress_version' ) ) {
			$this->e2e_environment->set_wordpress_version( $options['wordpress_version'] );
		}

		if ( $this->getDefinition()->hasOption( 'woocommerce_version' ) ) {
			$this->e2e_environment->set_woocommerce_version( $options['woocommerce_version'] );
		}

		if ( $this->getDefinition()->hasOption( 'php_version' ) ) {
			$this->e2e_environment->set_php_version( $options['php_version'] );
		}

		if ( $input->getOption( 'with-object-cache' ) ) {
			$this->e2e_environment->set_enable_object_cache( true );
		}

		if ( $input->getOption( 'php-ext' ) ) {
			$this->e2e_environment->set_php_extensions( $input->getOption( 'php-ext' ) );
		}

		$env_info = $this->e2e_environment->up( $input->getOption( 'attached' ) );

		$output->writeln( '<info>Environment up.</info>' );

		if ( $input->getOption( 'json' ) ) {
			$output->write( json_encode( $env_info ) );
		}

		// Print the site URL as the last information for easy programmatic integrations.
		$output->writeln( $env_info->site_url );

		return Command::SUCCESS;
	}
}
