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

class StartEnvironmentCommand extends DynamicCommand {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var Cache */
	protected $cache;

	protected static $defaultName = 'env:start'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

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

		$this->setDescription( 'Starts a local test environment.' )
			->addOption( 'json', 'j', InputOption::VALUE_NEGATABLE, 'Whether to return raw JSON format.', false )
			->setAliases( [ 'env:up' ] );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
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

		$env_info = $this->e2e_environment->up();

		$output->writeln( '<info>Environment up.</info>' );

		if ( $input->getOption( 'json' ) ) {
			$output->write( json_encode( $env_info ) );
		}

		return Command::SUCCESS;
	}
}
