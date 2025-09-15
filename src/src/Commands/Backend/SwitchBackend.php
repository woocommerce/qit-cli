<?php

namespace QIT_CLI\Commands\Backend;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Config;
use QIT_CLI\ManagerBackend;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SwitchBackend extends QITCommand {
	protected static $defaultName = 'backend:switch'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected ManagerBackend $manager_backend;

	public function __construct( ManagerBackend $manager_backend ) {
		$this->manager_backend = $manager_backend;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Switch to another Manager Backend.' )
			->addArgument( 'backend', InputArgument::OPTIONAL, '(Optional) The backend to switch to.' )
			->setAliases( [ 'switch' ] );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// Optionaly allow the environment to be passed as an argument.
		if ( ! empty( $input->getArgument( 'backend' ) ) ) {
			$this->manager_backend->switch_to_manager_backend( strtolower( $input->getArgument( 'backend' ) ) );
			$output->writeln( sprintf( '<info>Backend switched to "%s".</info>', $input->getArgument( 'backend' ) ) );
			$output->writeln( '<info>Sync complete.</info>' );
			return self::SUCCESS;
		}

		$backends = $this->manager_backend->get_configured_manager_backend_names();

		if ( empty( $backends ) ) {
			$output->writeln( '<info>No backends configured.</info>' );
			return self::SUCCESS;
		}

		$current_backend = Config::get_current_manager_backend();

		$question = new ChoiceQuestion(
			"Current Manager: $current_backend. Please choose a new backend to switch to.",
			array_merge( $backends, [ '[Cancel]' ] ),
			count( $backends ) // Cancel is the default.
		);

		$new_backend = $this->getHelper( 'question' )->ask( $input, $output, $question );

		switch ( $new_backend ) {
			case '[Cancel]':
				return self::SUCCESS;
			default:
				$this->manager_backend->switch_to_manager_backend( $new_backend );
				$output->writeln( "<info>Backend switched to \"$new_backend\".</info>" );
				$output->writeln( '<info>Sync complete.</info>' );
				break;
		}

		return self::SUCCESS;
	}
}
