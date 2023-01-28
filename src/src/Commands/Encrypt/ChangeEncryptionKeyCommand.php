<?php

namespace QIT_CLI\Commands\Encrypt;

use QIT_CLI\App;
use QIT_CLI\Encryption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ChangeEncryptionKeyCommand extends Command {
	protected static $defaultName = 'encryption:change'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected $encryption;

	public function __construct( Encryption $encryption ) {
		$this->encryption = $encryption;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Change the encryption password.' )
			->addOption( 'old-key', 'o', InputOption::VALUE_OPTIONAL, '(Optional) The old encryption key.' )
			->addOption( 'new-key', 'k', InputOption::VALUE_OPTIONAL, '(Optional) The new encryption key.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( ! empty( $input->getOption( 'old-key' ) ) ) {
			App::setVar( 'enc_password', $input->getOption( 'old-key' ) );
		}

		if ( ! empty( $input->getOption( 'new-key' ) ) ) {
			$new_password = $input->getOption( 'new-key' );
		} else {
			$question = new Question( 'Please enter the password to encrypt the key with: ' );
			$question->setHidden( true );
			$question->setHiddenFallback( false );

			$new_password = $this->getHelper( 'question' )->ask( $input, $output, $question );
		}


		$this->encryption->change_encryption( $new_password );

		return Command::SUCCESS;
	}
}
