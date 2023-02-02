<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Config;
use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SwitchEnvironment extends Command {
	protected static $defaultName = 'env:switch'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Environment $environment */
	protected $environment;

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Switch to another QIT environment.' )
			->addArgument( 'environment', InputArgument::OPTIONAL, '(Optional) The environment to switch to.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		// Optionaly allow the environment to be passed as an argument.
		if ( ! empty( $input->getArgument( 'environment' ) ) ) {
			$this->environment->switch_to_environment( strtolower( $input->getArgument( 'environment' ) ) );
			$output->writeln( '<info>Environment switched.</info>' );

			return Command::SUCCESS;
		}

		$environments = $this->environment->get_configured_environment_names( false, true );

		if ( empty( $environments ) ) {
			$output->writeln( '<info>No environments configured.</info>' );

			return Command::SUCCESS;
		}

		$current_environment = Config::get_current_environment();

		$question = new ChoiceQuestion(
			"Current environment: $current_environment. Please choose a new environment to switch to.",
			array_merge( $environments, [ '[Cancel]' ] ),
			count( $environments ) // Cancel is the default.
		);

		$new_environment = $this->getHelper( 'question' )->ask( $input, $output, $question );

		switch ( $new_environment ) {
			case '[Cancel]':
				return Command::SUCCESS;
			default:
				$this->environment->switch_to_environment( $new_environment );
				$output->writeln( "<info>Environment switched to $new_environment.</info>" );

				return Command::SUCCESS;
		}
	}
}
