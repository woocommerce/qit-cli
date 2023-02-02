<?php

namespace QIT_CLI\Commands\Encrypt;

use QIT_CLI\Encryption;
use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
			->addOption( 'force', 'f', InputOption::VALUE_NONE, 'Force disable encryption without asking for confirmation.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( $input->getOption( 'force' ) !== true ) {
			if ( ! empty( $this->environment->get_configured_environments( false ) ) ) {
				if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "<question>If you disable encryption, you need to re-add the Partners/Environments. Continue? (y/n) </question>", false ) ) ) {
					return Command::SUCCESS;
				}
			}
		}

		$this->encryption->disable_encryption();

		return Command::SUCCESS;
	}
}
