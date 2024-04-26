<?php

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;

class QITTestExtension implements Extension {
	public function bootstrap( Configuration $configuration, Facade $facade, ParameterCollection $parameters ): void {
		$facade->registerSubscriber( new \QITTestStart() );
		$facade->registerSubscriber( new \QITTestFinish() );
	}
}

class QITTestStart implements ExecutionStartedSubscriber {
	public function notify( ExecutionStarted $event ): void {
		if ( empty( $GLOBALS['QIT_HOME'] ) || empty( $GLOBALS['qit'] ) ) {
			throw new \LogicException( 'The "QIT_HOME" and "qit" GLOBALS must be set.' );
		}

		// Enable dev mode.
		$dev = new Process( [ $GLOBALS['qit'], 'dev' ] );
		$dev->setEnv( [ 'QIT_HOME' => $GLOBALS['QIT_HOME'] ] );
		$dev->mustRun();

		// Add the environment.
		$add_environment = new Process( [
			$GLOBALS['qit'],
			'backend:add',
			'--manager_url',
			$_ENV['QIT_CUSTOM_TESTS_URL'],
			'--qit_secret',
			$_ENV['QIT_CUSTOM_TESTS_SECRET'],
			'--environment',
			$_ENV['QIT_CUSTOM_TESTS_ENV'],
		] );
		$add_environment->setEnv( [ 'QIT_HOME' => $GLOBALS['QIT_HOME'] ] );
		$add_environment->mustRun();

		// Add the partner account that will be used.
		$add_partner = new Process( [
			$GLOBALS['qit'],
			'partner:add',
			'--user',
			$_ENV['QIT_CUSTOM_TESTS_USER'],
			'--qit_token',
			$_ENV['QIT_CUSTOM_TESTS_USER_QIT_TOKEN'],
		] );
		$add_partner->setEnv( [ 'QIT_HOME' => $GLOBALS['QIT_HOME'] ] );
		$add_partner->mustRun();

		// Copy files from __DIR__. '/cache' to $GLOBALS['QIT_HOME'] . '/cache'
		$fs = new Filesystem();
		$fs->mirror( __DIR__ . '/cache', $GLOBALS['QIT_HOME'] . '/cache' );
	}
}

class QITTestFinish implements ExecutionFinishedSubscriber {
	public function notify( ExecutionFinished $event ): void {
		self::delete_temp_environment();
	}

	public static function delete_temp_environment(): void {
		if ( empty( $GLOBALS['QIT_HOME'] ) || empty( $GLOBALS['qit'] ) ) {
			throw new \LogicException( 'The "QIT_HOME" and "qit" GLOBALS must be set.' );
		}

		$fs = new Filesystem();

		// Check that $GLOBALS['QIT_HOME'] is inside this directory.
		if ( strpos( $GLOBALS['QIT_HOME'], __DIR__ ) !== 0 ) {
			throw new \RuntimeException( sprintf( 'The QIT_HOME directory is not inside the test directory. This is a security risk. QIT_HOME: %s, Test Directory: %s', $GLOBALS['QIT_HOME'], __DIR__ ) );
		}

		if ( $fs->exists( $GLOBALS['QIT_HOME'] ) ) {
			// Copy files from $GLOBALS['QIT_HOME'] . '/cache' to __DIR__. '/cache'
			$fs->mirror( $GLOBALS['QIT_HOME'] . '/cache', __DIR__ . '/cache' );

			$fs->remove( $GLOBALS['QIT_HOME'] );
		}
	}
}