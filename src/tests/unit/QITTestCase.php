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
		App::setVar( sprintf( 'mock_%s%s', \QIT_CLI\get_manager_url(), '/wp-json/cd/v2/cli/sync' ), file_get_contents( __DIR__ . '/data/sync-v2.json' ) );
		App::setVar( sprintf( 'mock_%s%s', \QIT_CLI\get_manager_url(), '/wp-json/cd/v2/cli/sync/extensions' ), file_get_contents( __DIR__ . '/data/sync-v2-extensions.json' ) );
		// Set a mock manager secret so maybe_sync_extensions() passes the auth guard.
		App::make( \QIT_CLI\Cache::class )->set( 'manager_secret', 'test-secret', -1 );
		App::make( ManagerSync::class )->maybe_sync( true );
		App::offsetUnset( 'QIT_ACTIVATION_TEST' );
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
		$application->assertCommandIsSuccessful( $application->getDisplay() . $application->getErrorOutput() );
	}

	protected function createMinimalPluginZip( string $slug, string $version ): string {
		$filename = "{$slug}.php";
		$content  = "<?php\n/**\n * Plugin Name: " . ucwords( str_replace( '-', ' ', $slug ) ) . "\n * Version: {$version}\n */";

		$zip  = new \ZipArchive();
		$temp = tempnam( sys_get_temp_dir(), 'zip' );
		if ( $temp === false ) {
			$this->fail( "Failed to create temporary file for ZIP" );
		}
		try {
			if ( ! $zip->open( $temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) ) {
				$this->fail( "Failed to create ZIP file at $temp" );
			}
			$zip->addFromString( "{$slug}/{$filename}", $content );
			$zip->close();

			$zipContent = file_get_contents( $temp );
			if ( $zipContent === false ) {
				$this->fail( "Failed to read ZIP content from $temp" );
			}

			return $zipContent;
		} finally {
			unlink( $temp );
		}
	}

	protected function recursive_rmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		
		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->recursive_rmdir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}