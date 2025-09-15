<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\E2E\E2EEnvironment;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EnterEnvironmentCommand extends QITCommand {
	use EnvironmentSelectionTrait;

	protected E2EEnvironment $e2e_environment;
	protected EnvironmentMonitor $environment_monitor;
	protected Docker $docker;

	protected static $defaultName = 'env:enter'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		E2EEnvironment $e2e_environment,
		EnvironmentMonitor $environment_monitor,
		Docker $docker
	) {
		$this->e2e_environment     = $e2e_environment;
		$this->environment_monitor = $environment_monitor;
		$this->docker              = $docker;
		parent::__construct( static::$defaultName ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	protected function configure(): void {
		parent::configure();
		$this
			->addOption( 'user', 'u', InputOption::VALUE_OPTIONAL, 'The user to enter the environment as.', '' )
			->addOption( 'dev', 'd', InputOption::VALUE_NEGATABLE, 'Enter the environment as a developer. This installs some quality-of-life tooling inside the Alpine container, such as bash and less.', true )
			->setDescription( 'Enter the PHP container of a running test environment.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// Use the trait method to select environment (no option, only QIT_ENV_ID)
		$environment = $this->select_environment( $this->environment_monitor, $input, $output, null );

		if ( ! $environment ) {
			return self::FAILURE;
		}

		$this->docker->enter_environment( $environment, 'php', '/bin/bash', $input->getOption( 'user' ), $input->getOption( 'dev' ) );

		return self::SUCCESS;
	}
}
