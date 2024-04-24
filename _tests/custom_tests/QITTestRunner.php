<?php

use lucatume\DI52\App;
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
			->addArgument( 'partner_user', InputArgument::OPTIONAL, 'The QIT username to authenticate with.', getenv( 'QIT_CUSTOM_TESTS_USER' ) ?: '' )
			->addArgument( 'partner_qit_token', InputArgument::OPTIONAL, 'The QIT token to authenticate the password with.', getenv( 'QIT_CUSTOM_TESTS_ENV_TOKEN' ) ?: '' )
			->addArgument( 'env_secret', InputArgument::OPTIONAL, 'The QIT secret to use to change environments.', getenv( 'QIT_CUSTOM_TESTS_SECRET' ) ?: '' )
			->addArgument( 'env_url', InputArgument::OPTIONAL, 'The QIT Manager environment URL to authenticate against.', getenv( 'QIT_CUSTOM_TESTS_URL' ) ?: 'https://stagingcompatibilitydashboard.wpcomstaging.com/' )
			->addArgument( 'env_type', InputArgument::OPTIONAL, 'The environment being added.', getenv( 'QIT_CUSTOM_TESTS_ENV' ) ?: 'staging' )
			->setDescription( 'Run self-tests for the Custom Tests' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( empty( $input->getArgument( 'partner_user' ) ) || empty( $input->getArgument( 'partner_qit_token' ) ) || empty( $input->getArgument( 'env_secret' ) ) || empty( $input->getArgument( 'env_url' ) ) || empty( $input->getArgument( 'env_type' ) ) ) {
			$output->writeln( '<error>Missing required arguments.</error>' );

			return Command::FAILURE;
		}

		$this->init( $input );

		// We are now authenticated in the environment as the partner, so we just need to run the tests.
		$phpunit = new Process( [
			PHP_BINARY,
			'vendor/bin/phpunit',
			'tests',
			'--bootstrap',
			'./tests/bootstrap.php'
		] );
		$phpunit->run( static function ( $type, $buffer ) use ( $output ) {
			$output->write( $buffer );
		} );

		return $phpunit->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
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
		$dev = new Process( [ App::getVar( 'qit' ), 'dev' ] );
		$this->set_qit_home( $dev );
		$dev->mustRun();

		// Add the environment.
		$add_environment = new Process( [
			App::getVar( 'qit' ),
			'backend:add',
			'--manager_url',
			$input->getArgument( 'env_url' ),
			'--qit_secret',
			$input->getArgument( 'env_secret' ),
			'--environment',
			$input->getArgument( 'env_type' ),
		] );
		$this->set_qit_home( $add_environment );
		$add_environment->mustRun();

		// Add the partner account that will be used.
		$add_partner = new Process( [
			App::getVar( 'qit' ),
			'partner:add',
			'--user',
			$input->getArgument( 'partner_user' ),
			'--qit_token',
			$input->getArgument( 'partner_qit_token' ),
		] );
		$this->set_qit_home( $add_partner );
		$add_partner->mustRun();
	}

	protected function set_qit_home( Process $process ): void {
		$process->setEnv( [ 'QIT_HOME' => __DIR__ . '/tmp_qit_config' ] );
	}
}