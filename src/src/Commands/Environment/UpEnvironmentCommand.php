<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Cache;
use QIT_CLI\Commands\DynamicCommand;
use QIT_CLI\Commands\DynamicCommandCreator;
use QIT_CLI\Environment\Environments\E2EEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpEnvironmentCommand extends DynamicCommand {
	/** @var E2EEnvironment */
	protected $e2e_environment;
	protected $cache;

	protected static $defaultName = 'env:up'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( E2EEnvironment $e2e_environment, Cache $cache ) {
		$this->e2e_environment = $e2e_environment;
		$this->cache           = $cache;
		parent::__construct( static::$defaultName );
	}

	protected function configure() {
		$schemas = $this->cache->get_manager_sync_data( 'schemas' );

		if ( ! is_array( $schemas['e2e']['properties'] ) ) {
			throw new \RuntimeException( 'E2E schema not set or incomplete.' );
		}

		DynamicCommandCreator::add_schema_to_command( $this, $schemas['e2e'], [ 'compatibility' ] );

		$this->setDescription( 'Starts a local test environment.' )
		     ->setAliases( [ 'env:start' ] );
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

		$this->e2e_environment->up();

		$output->writeln( "<info>Environment up.</info>" );

		return Command::SUCCESS;
	}
}