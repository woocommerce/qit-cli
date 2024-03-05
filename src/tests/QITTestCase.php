<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI\Commands\WooExtensionsCommand;
use QIT_CLI\Config;
use QIT_CLI\ManagerBackend;
use QIT_CLI\ManagerSync;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;

abstract class QITTestCase extends TestCase {
	public function setUp(): void {
		parent::setUp();

		qit_tests_reset_config_dir();
		App::offsetUnset( Config::class );
		Config::set_current_manager_environment( 'tests' );
		App::make( ManagerBackend::class )->add_manager_backend( 'tests' );
		App::setVar( sprintf( 'mock_%s%s', \QIT_CLI\get_manager_url(), '/wp-json/cd/v1/cli/sync' ), file_get_contents( __DIR__ . '/data/sync.json' ) );
		App::make( ManagerSync::class )->maybe_sync( true );
	}

	protected function make_application_tester( ?callable $callback = null ): ApplicationTester {
		$application = clone $GLOBALS['qit_application'];

		if ( ! is_null( $callback ) ) {
			$callback( $application );
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