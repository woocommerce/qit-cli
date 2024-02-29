<?php

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI\Config;

class ConfigTest extends TestCase {
	private $testDir;
	private static $originalEnvVars;

	protected function setUp(): void {
		if ( is_null( static::$originalEnvVars ) ) {
			static::$originalEnvVars = [
				'QIT_HOME'        => getenv( 'QIT_HOME' ),
				'APPDATA'         => getenv( 'APPDATA' ),
				'HOME'            => getenv( 'HOME' ),
				'XDG_CONFIG_HOME' => getenv( 'XDG_CONFIG_HOME' ),
			];
		}

		// Reset environment variables to a default state for testing.
		putenv( 'QIT_HOME=' );
		putenv( 'APPDATA=' );
		putenv( 'HOME=' );
		putenv( 'XDG_CONFIG_HOME=' );

		$this->testDir = __DIR__ . '/data/config/';
	}

	protected function tearDown(): void {
		// Restore original environment variables.
		foreach ( static::$originalEnvVars as $var => $value ) {
			if ( $value === false ) {
				putenv( $var );
			} else {
				putenv( "$var=$value" );
			}
		}
		App::offsetUnset( 'MIMICK_WINDOWS' );
		App::offsetUnset( 'MIMICK_XDG' );
	}

	public function test_get_qit_dir_with_qit_home_override(): void {
		$expectedDir = $this->testDir . 'woo-qit-cli/';

		foreach ( [ 'windows', 'unix' ] as $os ) {
			if ( $os === 'windows' ) {
				App::setVar( 'MIMICK_WINDOWS', true );
			} else {
				App::offsetUnset( 'MIMICK_WINDOWS' );
			}

			// Regular.
			putenv( 'QIT_HOME=' . $this->testDir );
			$this->assertEquals( $expectedDir, Config::get_qit_dir() );

			// Missing trailing slash.
			putenv( 'QIT_HOME=' . rtrim( $this->testDir, '/' ) );
			$this->assertEquals( $expectedDir, Config::get_qit_dir() );

			// Windows directory separator - Non windows.
			putenv( 'QIT_HOME=' . str_replace( '/', '\\', $this->testDir ) );
			$this->assertEquals( $expectedDir, Config::get_qit_dir() );

			// Windows directory separator missing trailing slash - Non windows.
			putenv( 'QIT_HOME=' . rtrim( str_replace( '/', '\\', $this->testDir ), '\\' ) );
			$this->assertEquals( $expectedDir, Config::get_qit_dir() );
		}
	}

	public function test_get_qit_dir_on_windows(): void {
		App::setVar( 'MIMICK_WINDOWS', true );

		putenv( 'APPDATA=' . $this->testDir );
		$expectedDir = $this->testDir . 'woo-qit-cli/';
		$this->assertEquals( $expectedDir, Config::get_qit_dir() );

		// Simulate Windows environment
		putenv( 'APPDATA=' . str_replace( '/', '\\', $this->testDir ) );
		$this->assertEquals( $expectedDir, Config::get_qit_dir() );
	}

	public function test_get_qit_dir_with_home(): void {
		putenv( 'HOME=' . $this->testDir );
		$expectedDir = $this->testDir . 'woo-qit-cli/';
		$this->assertEquals( $expectedDir, Config::get_qit_dir() );

		// Simulate Windows separator.
		putenv( 'HOME=' . str_replace( '/', '\\', $this->testDir ) );
		$this->assertEquals( $expectedDir, Config::get_qit_dir() );
	}

	public function test_get_qit_dir_with_home_and_xdg(): void {
		putenv( 'HOME=' . $this->testDir );
		putenv( 'XDG_CONFIG_HOME=' . $this->testDir . '.config' );
		App::setVar( 'MIMICK_XDG', true );
		$expectedDir = $this->testDir . '.config/woo-qit-cli/';
		$this->assertEquals( $expectedDir, Config::get_qit_dir() );
	}

	public function test_get_qit_dir_throws_if_needed(): void {
		putenv( 'QIT_HOME' );
		putenv( 'APPDATA' );
		putenv( 'HOME' );
		putenv( 'XDG_CONFIG_HOME' );

		$this->expectException( \RuntimeException::class );
		Config::get_qit_dir();
	}
}
