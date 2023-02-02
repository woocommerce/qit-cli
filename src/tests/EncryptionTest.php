<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Commands\Encrypt\DisableEncryptionCommand;
use QIT_CLI\Commands\WooExtensionsCommand;
use QIT_CLI\Encryption;
use QIT_CLI\Environment;

class EncryptionTest extends QITTestCase {
	protected $encryption;

	public function setUp(): void {
		$this->encryption = App::make( Encryption::class );

		parent::setUp();
	}

	public function test_plain_text_by_default() {
		$application_tester = $this->make_application_tester();

		// Get Woo Extensions.
		$application_tester->run( [
			'command'   => WooExtensionsCommand::getDefaultName(),
			'--refresh' => true,
		], [ 'capture_stderr_separately' => true ] );

		// Assert Cache file is plain text.
		$json = json_decode( file_get_contents( App::make( Environment::class )->get_cache()->get_cache_file_path() ), true );

		$this->assertIsArray( $json );
		$this->assertCommandIsSuccessful( $application_tester );
	}

	public function test_can_enable_encryption() {
		$application_tester = $this->make_application_tester();

		$this->encryption->enable_encryption( '123' );

		// Get Woo Extensions.
		$application_tester->run( [
			'command'   => WooExtensionsCommand::getDefaultName(),
			'--refresh' => true,
		], [ 'capture_stderr_separately' => true ] );

		$encrypted_config = file_get_contents( App::make( Environment::class )->get_cache()->get_cache_file_path(), true );
		$this->assertNull( json_decode( $encrypted_config, true ) );

		$decrypted_config = $this->encryption->decrypt( $encrypted_config );
		$this->assertIsArray( json_decode( $decrypted_config, true ) );
	}

	public function test_can_disable_encryption() {
		$application_tester = $this->make_application_tester();

		$this->encryption->enable_encryption( '123' );

		// Get Woo Extensions.
		$application_tester->run( [
			'command'   => WooExtensionsCommand::getDefaultName(),
			'--refresh' => true,
		], [ 'capture_stderr_separately' => true ] );

		$encrypted_config = file_get_contents( App::make( Environment::class )->get_cache()->get_cache_file_path(), true );
		$this->assertNull( json_decode( $encrypted_config, true ) );

		$decrypted_config = $this->encryption->decrypt( $encrypted_config );
		$this->assertIsArray( json_decode( $decrypted_config, true ) );

		// Disable encryption.
		$application_tester->run( [
			'command'   => DisableEncryptionCommand::getDefaultName(),
			'--force' => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertEmpty( Environment::get_configured_environments() );
	}
}