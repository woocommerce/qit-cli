<?php

namespace QIT_CLI\Commands\Encrypt;

use QIT_CLI\Config;
use QIT_CLI\Encryption;
use QIT_CLI\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DisableEncryptionCommand extends Command {
	protected static $defaultName = 'encryption:disable'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected function configure() {
		$this
			->setDescription( 'Disables encryption of the config files.' )
			->addOption( 'force', 'f', InputOption::VALUE_NONE, 'Force disable encryption without asking for confirmation.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( $input->getOption( 'force' ) !== true ) {
			if ( ! empty( Environment::get_configured_environments() ) ) {
				if ( ! $this->getHelper( 'question' )->ask( $input, $output, new ConfirmationQuestion( "<question>If you disable encryption, you need to re-add the Partners/Environments. Continue? (y/n) </question>", false ) ) ) {
					return Command::SUCCESS;
				}
			}
		}

		$qit_dir = Config::get_qit_dir();

		try {
			Config::set_encryption( false );

			Encryption::delete_keys();

			// Delete config files.
			foreach ( Environment::get_configured_environments() as $file ) {
				if ( ! unlink( $file ) ) {
					throw new \RuntimeException( "Could not delete file: $file" );
				}
			}
		} catch ( \Exception $e ) {
			$output->writeln( $e->getMessage() );
			$output->writeln( "Please empty the directory $qit_dir manually." );

			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}
