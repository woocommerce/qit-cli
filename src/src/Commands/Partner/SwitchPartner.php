<?php

namespace QIT_CLI\Commands\Partner;

use QIT_CLI\Config;
use QIT_CLI\ManagerBackend;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SwitchPartner extends Command {
	protected static $defaultName = 'partner:switch'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var ManagerBackend $manager_backend */
	protected $manager_backend;

	public function __construct( ManagerBackend $manager_backend ) {
		$this->manager_backend = $manager_backend;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Switch to another Partner.' )
			->addArgument( 'user', InputArgument::OPTIONAL, '(Optional) The partner user to switch to.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		// Optionaly allow the Partner to be passed as an argument.
		if ( ! empty( $input->getArgument( 'user' ) ) ) {
			$this->manager_backend->switch_to_partner( $input->getArgument( 'user' ) );
			$output->writeln( "<info>Switched to Partner {$input->getArgument( 'user' )} successfully.</info>" );

			return Command::SUCCESS;
		}

		$manager_backends = ManagerBackend::get_configured_manager_backends( true );

		if ( empty( $manager_backends ) ) {
			$output->writeln( '<info>No Partners configured.</info>' );

			return Command::SUCCESS;
		}

		$current_environment = Config::get_current_manager_backend();

		$human_readable_partner = explode( '-', $current_environment );
		$human_readable_partner = end( $human_readable_partner );

		$question = new ChoiceQuestion(
			sprintf( 'Current Partner: %s. Please choose a new Partner to switch to.', $human_readable_partner ),
			array_merge( array_map( static function ( $e ) {
				$h = explode( '-', $e );
				return end( $h );
			}, $manager_backends ), [ '[Cancel]' ] ),
			count( $manager_backends ) // Cancel is the default.
		);

		$new_environment = $this->getHelper( 'question' )->ask( $input, $output, $question );

		switch ( $new_environment ) {
			case '[Cancel]':
				return Command::SUCCESS;
			default:
				$this->manager_backend->switch_to_partner( $new_environment );
				$output->writeln( "<info>Switched to Partner $new_environment successfully.</info>" );

				return Command::SUCCESS;
		}
	}
}
