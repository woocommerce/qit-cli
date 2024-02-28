<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\E2EEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestartEnvironmentCommand extends Command {
	/** @var E2EEnvironment */
	protected $e2e_environment;

	/** @var Cache */
	protected $cache;

	/** @var StartEnvironmentCommand */
	protected $up_command;

	/** @var StopEnvironmentCommand */
	protected $down_command;

	protected static $defaultName = 'env:restart'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		E2EEnvironment $e2e_environment,
		Cache $cache,
		StartEnvironmentCommand $up_command,
		StopEnvironmentCommand $down_command
	) {
		$this->e2e_environment = $e2e_environment;
		$this->cache           = $cache;
		$this->up_command      = $up_command;
		$this->down_command    = $down_command;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure() {
		$this->setDescription( 'Restarts a local test environment.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$environment_options = $this->cache->get( 'environment_up_options' ) ?? [];

		$this->down_command->run( new ArrayInput( [] ), $output );

		// Prepend $environment_options with "--" when it's valid UpEnvironmentCommand option.
		foreach ( $environment_options as $key => $value ) {
			if ( $this->up_command->getDefinition()->hasOption( $key ) ) {
				$environment_options[ "--$key" ] = $value;
				unset( $environment_options[ $key ] );
			}
		}

		return $this->up_command->run( new ArrayInput( $environment_options ), $output );
	}
}
