<?php

namespace QIT_CLI\Commands\Encrypt;

use QIT_CLI\Encryption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class EnableEncryptionCommand extends Command {
	protected static $defaultName = 'encryption:enable'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected $encryption;

	public function __construct( Encryption $encryption ) {
		$this->encryption = $encryption;
		parent::__construct();
	}

	protected function configure() {
		$this
			->setDescription( 'Enable encryption of the config files.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$this->encryption->enable_encryption();

		return Command::SUCCESS;
	}
}
