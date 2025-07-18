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
		if ( $this instanceof ConfigurableTestCommand || $this instanceof EnvironmentCommand ) {
			$this->addOption(
				'config',
				'',
				InputOption::VALUE_OPTIONAL,
				'Path to the qit.json configuration file',
				null
			);
		}

		if ( $this instanceof ConfigurableTestCommand || $this instanceof LocalTestCommand ) {
			$this->addOption(
				'profile',
				'',
				InputOption::VALUE_OPTIONAL,
				'Test profile to use',
				'default'
			);
		}

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
			if ( $this instanceof ConfigurableTestCommand || $this instanceof EnvironmentCommand ) {
				// Use ./qit.json if it exists and --config is not set
				$config_file = $input->getOption( 'config' );
				if ( $config_file === null && file_exists( getcwd() . '/qit.json' ) ) {
					$config_file = getcwd() . '/qit.json';
				}

				$handler                 = App::make( PreCommandHandler::class );
				$this->precommand_result = $handler->handle( $this, $input, $output, $config_file );
			}

			return $this->doExecute( $input, $output );
		} catch ( \QIT_CLI\PreCommand\PrecommandEarlyReturn $e ) {
			// This is normal in tests - just return success
			return Command::SUCCESS;
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::FAILURE;
		}
	}

	protected function getPreCommandResult(): ?object {
		return $this->precommand_result;
	}

	abstract protected function doExecute( InputInterface $input, OutputInterface $output ): int;
}
