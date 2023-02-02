<?php

namespace QIT_CLI\Commands\Encrypt;

use QIT_CLI\Config;
use QIT_CLI\Encryption;
use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class EnableEncryptionCommand extends Command {
	protected static $defaultName = 'encryption:enable'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected $encryption;
	protected $environment;

	public function __construct( Encryption $encryption, Environment $environment ) {
		$this->encryption  = $encryption;
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Enable encryption of the QIT config files.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( ! extension_loaded( 'openssl' ) ) {
			$output->writeln( 'The extension "openssl" is not loaded. Please enable it in your php.ini file.' );

			return Command::FAILURE;
		}

		$output->writeln( sprintf( '<info>Preparing to encrypt the QIT config files located at %s:</info>', Config::get_qit_dir() ) );

		if ( ! extension_loaded( 'shmop' ) ) {
			$output->writeln( 'The extension "shmop" is not loaded. Without it, you will need to enter the decryption password on every interaction with the tool. To enable the "shmop" extension, please uncomment ";extension=shmop" in your php.ini file, or install the "shmop" extension if it doesn\'t exist.' );
			$question = 'Do you want to proceed with encrypting the config files? (y/n)';
			if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "<question>$question </question>", false ) ) ) {
				$output->writeln( 'Operation cancelled.' );

				return Command::SUCCESS;
			}
		}

		$question = new Question( '<question>Please enter a password to encrypt.</question> ' );
		$question->setHidden( true );
		$question->setHiddenFallback( false );
		$password = $this->getHelper( 'question' )->ask( $input, $output, $question );

		$this->encryption->enable_encryption( $password );

		$output->writeln( 'QIT config files encrypted.' );

		return Command::SUCCESS;
	}
}
