<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI\Commands\WooExtensionsCommand;
use QIT_CLI\Encryption;
use QIT_CLI\Environment;
use QIT_CLI\ManagerSync;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;

abstract class QITTestCase extends TestCase {
	public function setUp(): void {
		parent::setUp();

		qit_tests_clean_config_dir();
		App::make( Environment::class )->create_environment( 'tests' );
		App::make( Encryption::class )->init();
		App::setVar( sprintf( 'mock_%s%s', \QIT_CLI\get_manager_url(), '/wp-json/cd/v1/cli/sync' ), file_get_contents( __DIR__ . '/data/sync.json' ) );
		App::make( ManagerSync::class )->maybe_sync( true );
	}

	protected function make_application_tester( Command ...$commands ): ApplicationTester {
		$application = clone $GLOBALS['qit_application'];

		foreach ( $commands as $c ) {
			$application->add( $c );
		}

		// This command is not available when in offline mode, so let's make it available for convenience.
		$application->add( App::make( WooExtensionsCommand::class ) );

		return new ApplicationTester( $application );
	}

	protected function assertCommandIsSuccessful( ApplicationTester $application ) {
		$error_output = $application->getErrorOutput();
		if ( ! empty( $error_output ) ) {
			$this->fail( sprintf( 'Command failed with error output: %s', $error_output ) );
		}
		$application->assertCommandIsSuccessful();
	}
}