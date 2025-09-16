<?php

namespace QIT_CLI\Commands\Partner;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Config;
use QIT_CLI\ManagerBackend;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SwitchPartner extends QITCommand {
	protected static $defaultName = 'partner:switch'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected ManagerBackend $manager_backend;

	public function __construct( ManagerBackend $manager_backend ) {
		$this->manager_backend = $manager_backend;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Switch to another Partner.' )
			->addArgument( 'user', InputArgument::OPTIONAL, '(Optional) The partner user to switch to.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// Optionaly allow the Partner to be passed as an argument.
		if ( ! empty( $input->getArgument( 'user' ) ) ) {
			$this->manager_backend->switch_to_partner( $input->getArgument( 'user' ) );
			$output->writeln( "<info>Switched to Partner {$input->getArgument( 'user' )} successfully.</info>" );
			return self::SUCCESS;
		}

		$manager_backends = ManagerBackend::get_configured_manager_backends( true );

		if ( empty( $manager_backends ) ) {
			$output->writeln( '<info>No Partners configured.</info>' );
			return self::SUCCESS;
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

		if ( $new_environment === '[Cancel]' || is_null( $new_environment ) ) {
			$output->writeln( '<info>Partner switch cancelled.</info>' );
			return self::SUCCESS;
		}

		$this->manager_backend->switch_to_partner( $new_environment );

		$output->writeln( "<info>Switched to Partner $new_environment successfully.</info>" );

		return self::SUCCESS;
	}
}
