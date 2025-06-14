<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\PreCommand\Interfaces\ConfigurableTestCommand;
use QIT_CLI\PreCommand\Interfaces\EnvironmentCommand;
use QIT_CLI\PreCommand\Interfaces\LocalTestCommand;
use QIT_CLI\PreCommand\PreCommandHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class QITCommand extends Command {
	protected InputInterface $input;
	protected OutputInterface $output;
	protected ?object $precommand_result = null;

	protected function configure(): void {
		// Add config option if command uses configuration
		if ( $this instanceof ConfigurableTestCommand || $this instanceof EnvironmentCommand ) {
			$this->addOption(
				'config',
				'',
				InputOption::VALUE_OPTIONAL,
				'Path to the qit.json configuration file',
				'./qit.json'
			);
		}

		// Add profile option for test commands
		if ( $this instanceof ConfigurableTestCommand || $this instanceof LocalTestCommand ) {
			$this->addOption(
				'profile',
				'',
				InputOption::VALUE_OPTIONAL,
				'Test profile to use',
				'default'
			);
		}

		// Add environment option for environment commands
		if ( $this instanceof EnvironmentCommand ) {
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
			// Only run PreCommand if the command implements one of our interfaces
			if ( $this instanceof ConfigurableTestCommand || $this instanceof EnvironmentCommand ) {
				$handler                 = App::make( PreCommandHandler::class );
				$this->precommand_result = $handler->handle( $this, $input, $output );
			}

			return $this->doExecute( $input, $output );

		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}
	}

	/**
	 * Get the PreCommand result
	 */
	protected function getPreCommandResult(): ?object {
		return $this->precommand_result;
	}

	/**
	 * The actual command execution - to be implemented by child classes
	 */
	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}