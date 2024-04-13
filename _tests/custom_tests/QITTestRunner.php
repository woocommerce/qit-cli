<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class QITTestRunner extends Command {
	protected static $defaultName = 'run';

	protected function configure() {
		$this
			->addOption( 'user', InputArgument::REQUIRED, 'The QIT username to authenticate with.', getenv( 'QIT_SELF_TEST_CUSTOM_USER' ) )
			->addOption( 'qit_token', InputArgument::REQUIRED, 'The QIT token to authenticate the password with.', getenv( 'QIT_SELF_TEST_CUSTOM_PARTNER_QIT_TOKEN' ) )
			->addOption( 'qit_secret', InputArgument::REQUIRED, 'The QIT secret to use to change environments.', getenv( 'QIT_SELF_TEST_CUSTOM_QIT_MANAGER_QIT_SECRET' ) )
			->addOption( 'manager_url', InputArgument::REQUIRED, 'The QIT Manager environment URL to authenticate against.', getenv( 'QIT_SELF_TEST_CUSTOM_MANAGER_URL' ) )
			->addOption( 'environment', InputArgument::REQUIRED, 'The environment being added.', getenv( 'QIT_SELF_TEST_CUSTOM_ENVIRONMENT' ) )
			->setDescription( 'Run self-tests for the Custom Tests' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$this->init( $input );

		// We are now authenticated in the environment as the partner, so we just need to run the tests.

		return Command::SUCCESS;
	}

	/**
	 * This method will:
	 *
	 * - Reset the QIT config used for this test.
	 * - Connect to a specific environment.
	 * - Authenticate to a partner account.
	 *
	 * This allows any further interactions to be made to this configured environment, as this partner.
	 */
	protected function init( InputInterface $input ) {
		// Delete the "tmp_qit_config" directory if it exists.
		$fs = new Filesystem();
		if ( $fs->exists( __DIR__ . '/tmp_qit_config' ) ) {
			$fs->remove( __DIR__ . '/tmp_qit_config' );
		}

		// Enable dev mode.
		$dev = new Process( [ \QIT_CLI\App::getVar( 'qit' ), 'dev' ] );
		$this->set_qit_home( $dev );
		$dev->mustRun();

		// Add the environment.
		$add_environment = new Process( [
			\QIT_CLI\App::getVar( 'qit' ),
			'backend:add',
			'--manager_url',
			$input->getOption( 'manager_url' ),
			'--qit_secret',
			$input->getOption( 'qit_secret' ),
			'--environment',
			$input->getOption( 'environment' ),
		] );
		$this->set_qit_home( $add_environment );
		$add_environment->mustRun();

		// Add the partner account that will be used.
		$add_partner = new Process( [
			\QIT_CLI\App::getVar( 'qit' ),
			'partner:add',
			'--user',
			$input->getOption( 'user' ),
			'--qit_token',
			$input->getOption( 'qit_token' ),
		] );
		$this->set_qit_home( $add_partner );
		$add_partner->mustRun();
	}

	protected function set_qit_home( Process $process ): void {
		$process->setEnv( [ 'QIT_HOME' => __DIR__ . '/tmp_qit_config' ] );
	}
}