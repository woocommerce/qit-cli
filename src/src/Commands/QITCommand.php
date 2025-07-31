<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\PreCommand\PreCommandAware;
use QIT_CLI\PreCommand\TinyPreCommand;
use QIT_CLI\PreCommand\Configuration\ConfigurationResolver;
use QIT_CLI\PreCommand\Configuration\ConfigMerger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class QITCommand extends Command implements PreCommandAware {
	protected InputInterface $input;
	protected OutputInterface $output;
	protected ?TinyPreCommand $tiny_pre_command = null;

	protected function configure(): void {
		// Always add config stuff
		$this->addOption(
			'config',
			'',
			InputOption::VALUE_OPTIONAL,
			'Path to the qit.json configuration file',
			null
		);

		// If instance of RunE2ECommand, RunActivationCommand or DynamicCommand, add test profile stuff
		if ( $this instanceof \QIT_CLI\Commands\CustomTests\RunE2ECommand || 
		     $this instanceof \QIT_CLI\Commands\RunActivationTestCommand ||
		     $this instanceof \QIT_CLI\Commands\DynamicCommand ) {
			$this->addOption(
				'profile',
				'',
				InputOption::VALUE_OPTIONAL,
				'Test profile to use',
				'default'
			);
		}

		// If instance of UpEnvironmentCommand or RunE2ECommand or RunActivationCommand, add environment stuff
		if ( $this instanceof \QIT_CLI\Commands\Environment\UpEnvironmentCommand ||
		     $this instanceof \QIT_CLI\Commands\CustomTests\RunE2ECommand ||
		     $this instanceof \QIT_CLI\Commands\RunActivationTestCommand ) {
			$this->addOption(
				'environment',
				'e',
				InputOption::VALUE_OPTIONAL,
				'Environment name from configuration',
				'default'
			);
		}
	}

	public function execute( InputInterface $input, OutputInterface $output ): int {
		$this->input  = $input;
		$this->output = $output;

		try {
			// Create TinyPreCommand for commands that need configuration capabilities
			if ( $this instanceof \QIT_CLI\Commands\Environment\UpEnvironmentCommand ||
			     $this instanceof \QIT_CLI\Commands\CustomTests\RunE2ECommand ||
			     $this instanceof \QIT_CLI\Commands\RunActivationTestCommand ||
			     $this instanceof \QIT_CLI\Commands\DynamicCommand ) {
				
				// Use ./qit.json if it exists and --config is not set
				$config_file = $input->getOption( 'config' );
				if ( $config_file === null && file_exists( getcwd() . '/qit.json' ) ) {
					$config_file = getcwd() . '/qit.json';
				}

				// Create TinyPreCommand instance - lazy and simple
				$this->tiny_pre_command = new TinyPreCommand(
					$input,
					$config_file,
					fn() => App::make( ConfigurationResolver::class ),
					fn() => App::make( ConfigMerger::class )
				);
			}

			return $this->doExecute( $input, $output );
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<e>{$e->getMessage()}</e>" );

			return Command::FAILURE;
		}
	}

	/**
	 * Implement PreCommandAware interface - get environment configuration.
	 */
	public function get_environment_config( string $env = 'default' ): array {
		if ( $this->tiny_pre_command === null ) {
			throw new \RuntimeException( 'PreCommand not available for this command type' );
		}
		return $this->tiny_pre_command->get_environment_config( $env );
	}

	/**
	 * Implement PreCommandAware interface - get test profile configuration.
	 */
	public function get_current_test_profile( string $test_type, string $profile = 'default' ): array {
		if ( $this->tiny_pre_command === null ) {
			throw new \RuntimeException( 'PreCommand not available for this command type' );
		}
		return $this->tiny_pre_command->get_current_test_profile( $test_type, $profile );
	}


	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}
