<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\EnvParser;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExecEnvironmentCommand extends QITCommand {
	use EnvironmentSelectionTrait;

	protected EnvironmentMonitor $environment_monitor;

	protected Docker $docker;

	protected static $defaultName = 'env:exec'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct(
		EnvironmentMonitor $environment_monitor,
		Docker $docker
	) {
		$this->environment_monitor = $environment_monitor;
		$this->docker              = $docker;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Execute a command inside the PHP container of a running test environment.' )
			->addArgument( 'command_to_run', InputArgument::REQUIRED, 'The command to execute in the environment' )
			->addOption(
				'env_var',
				null,
				InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
				'Environment variable in key=value format'
			)
			->addOption( 'env_id', null, InputOption::VALUE_OPTIONAL, 'The environment ID to use' )
			->addOption( 'user', null, InputOption::VALUE_OPTIONAL, 'The user to run the command as' )
			->addOption( 'timeout', null, InputOption::VALUE_OPTIONAL, 'Timeout for the command', 300 )
			->addOption( 'image', null, InputOption::VALUE_OPTIONAL, 'The Docker image to use', 'php' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// Use the trait method to select environment
		$environment = $this->select_environment( $this->environment_monitor, $input, $output, 'env_id' );

		if ( ! $environment ) {
			return self::FAILURE;
		}

		$command_to_run = $input->getArgument( 'command_to_run' );
		// Use "PAGER=more" because we run Alpine images that have a minimalist version of "less".
		$env_vars = array_merge( [ 'PAGER' => 'more' ], App::make( EnvParser::class )->parse( $input->getOption( 'env_var' ) ) );
		$user     = $input->getOption( 'user' );
		$timeout  = $input->getOption( 'timeout' ) !== null ? (int) $input->getOption( 'timeout' ) : 300;
		$image    = $input->getOption( 'image' ) ?: 'php';

		try {
			$this->docker->run_inside_docker( $environment, [ '/bin/bash', '-c', $command_to_run ], $env_vars, $user, $timeout, $image, true );
		} catch ( \Exception $e ) {
			$output->writeln( '<error>' . $e->getMessage() . '</error>' );

			return self::FAILURE;
		}

		return self::SUCCESS;
	}

	/**
	 * @param array<string,scalar> $env_var_options
	 *
	 * @return array<string,string>
	 *
	private function parse_env_vars( array $env_var_options ): array {
		$env_vars = [];
		foreach ( $env_var_options as $e ) {
			$parts = explode( '=', $e, 2 );
			if ( count( $parts ) === 2 ) {
				$env_vars[ $parts[0] ] = $parts[1];
			}
		}

		return $env_vars;
	}
	 */
}
