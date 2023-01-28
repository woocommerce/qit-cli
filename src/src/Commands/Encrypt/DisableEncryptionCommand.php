<?php

namespace QIT_CLI\Commands\Encrypt;

use QIT_CLI\App;
use QIT_CLI\Encryption;
use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class DisableEncryptionCommand extends Command {
	protected static $defaultName = 'encryption:disable'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected $encryption;
	protected $environment;

	public function __construct( Encryption $encryption, Environment $environment ) {
		$this->encryption  = $encryption;
		$this->environment = $environment;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Disables encryption of the config files.' )
			->addOption( 'force', 'f', InputOption::VALUE_NONE, 'Force disable encryption without asking for confirmation.' )
			->addOption( 'key', 'k', InputOption::VALUE_OPTIONAL, 'The old encryption key. If provided, the current config files are preserved.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$encryption_key = $input->getOption( 'key' ) ?: Encryption::get_default_password();

		if ( $input->getOption( 'force' ) !== true ) {
			if ( empty( $encryption_key ) && $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "<question>Do you know the encryption key? (y/n) </question>", false ) ) ) {
				$question = new Question( 'Please enter the current encryption key: ' );
				$question->setHidden( true );
				$question->setHiddenFallback( false );

				$encryption_key = $this->getHelper( 'question' )->ask( $input, $output, $question );
			} else {
				if ( ! empty( $this->environment->get_environment_files() ) ) {
					if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "<question>If you disable encryption, you need to re-add the Partners/Environments. Continue? (y/n) </question>", false ) ) ) {
						return Command::SUCCESS;
					}
				}
			}
		}

		$this->encryption->disable_encryption( $encryption_key );

		return Command::SUCCESS;
	}
}
