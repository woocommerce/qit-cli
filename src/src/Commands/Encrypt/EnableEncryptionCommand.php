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
			->setDescription( 'Enable encryption of the config files.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( ! extension_loaded( 'openssl' ) ) {
			$output->writeln( 'The extension "openssl" is not loaded. Please enable it in your php.ini file.' );

			return Command::FAILURE;
		}

		if ( ! extension_loaded( 'shmop' ) ) {
			$output->writeln( 'Did you know? You can enable the extension "shmop" in your php.ini file to only enter your decryption password once.' );
		}

		if ( ! empty( $this->environment->get_configured_environments( false ) ) ) {
			if ( Config::is_development_mode() ) {
				$question = "Do you want to enable encryption? You will need to re-add the Partner(s)/Environment(s) (y/n)";
			} else {
				$question = "Do you want to enable encryption? You will need to re-add the Partner(s) (y/n)";
			}
			if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "<question>$question </question>", false ) ) ) {
				$output->writeln( 'Operation cancelled.' );

				return Command::SUCCESS;
			}
		}

		$question = new Question( "<question>Please enter a password to encrypt.</question> " );
		$question->setHidden( true );
		$question->setHiddenFallback( false );
		$password = $this->getHelper( 'question' )->ask( $input, $output, $question );

		$this->encryption->enable_encryption( $password );

		return Command::SUCCESS;
	}
}
