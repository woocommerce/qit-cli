<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Commands\Encrypt\ChangeEncryptionKeyCommand;
use QIT_CLI\Commands\Encrypt\DisableEncryptionCommand;
use QIT_CLI\Commands\Encrypt\EnableEncryptionCommand;
use QIT_CLI\Commands\WooExtensionsCommand;
use QIT_CLI\Encryption;
use QIT_CLI\Environment;

class EncryptionTest extends QITTestCase {
	protected $encryption;

	public function setUp(): void {
		$this->encryption = App::make( Encryption::class );

		parent::setUp();
	}

	public function test_encrypted_by_default() {
		$application_tester = $this->make_application_tester();

		// Get Woo Extensions.
		$application_tester->run( [
			'command'   => WooExtensionsCommand::getDefaultName(),
			'--refresh' => true,
		], [ 'capture_stderr_separately' => true ] );

		// Assert Cache file is encrypted.
		$encrypted_config = file_get_contents( Environment::get_qit_dir() . '/.env-tests' );

		$encryption       = App::make( Encryption::class );
		$decrypted_config = $encryption->decrypt( $encrypted_config );
		$json             = json_decode( $decrypted_config, true );

		$this->assertIsArray( $json );
		$this->assertCommandIsSuccessful( $application_tester );
	}

	public function test_can_disable_encryption_from_beginning() {
		$application_tester = $this->make_application_tester();

		// Disable Encryption.
		$application_tester->run( [
			'command' => DisableEncryptionCommand::getDefaultName(),
			'--force' => true,
			'--key'   => Encryption::get_default_password()
		], [ 'capture_stderr_separately' => true ] );

		// Get Woo Extensions.
		$application_tester->run( [
			'command'   => WooExtensionsCommand::getDefaultName(),
			'--refresh' => true,
		], [ 'capture_stderr_separately' => true ] );

		// Assert Cache file is plain text.
		$decrypted_config = file_get_contents( Environment::get_qit_dir() . '/.env-tests' );
		$json             = json_decode( $decrypted_config, true );

		$this->assertIsArray( $json );
		$this->assertCommandIsSuccessful( $application_tester );
	}

	public function test_can_disable_encryption_afterwards() {
		$application_tester = $this->make_application_tester();

		// Get Woo Extensions.
		$application_tester->run( [
			'command'   => WooExtensionsCommand::getDefaultName(),
			'--refresh' => true,
		], [ 'capture_stderr_separately' => true ] );

		$encrypted_config = file_get_contents( Environment::get_qit_dir() . '/.env-tests' );
		$this->assertIsArray( json_decode( $this->encryption->decrypt( $encrypted_config ), true ) );

		// Disable Encryption.
		$application_tester->run( [
			'command' => DisableEncryptionCommand::getDefaultName(),
			'--force' => true
		], [ 'capture_stderr_separately' => true ] );

		// Assert Cache file is plain text.
		$decrypted_config = file_get_contents( Environment::get_qit_dir() . '/.env-tests' );
		$json             = json_decode( $decrypted_config, true );

		$this->assertIsArray( $json );
		$this->assertCommandIsSuccessful( $application_tester );
	}

	public function test_can_enable_encryption() {
		$application_tester = $this->make_application_tester();

		// Get Woo Extensions.
		$application_tester->run( [
			'command'   => WooExtensionsCommand::getDefaultName(),
			'--refresh' => true,
		], [ 'capture_stderr_separately' => true ] );

		$encrypted_config = file_get_contents( Environment::get_qit_dir() . '/.env-tests' );
		$this->assertIsArray( json_decode( $this->encryption->decrypt( $encrypted_config ), true ) );

		// Disable Encryption.
		$application_tester->run( [
			'command' => DisableEncryptionCommand::getDefaultName(),
			'--force' => true
		], [ 'capture_stderr_separately' => true ] );

		$decrypted_config = file_get_contents( Environment::get_qit_dir() . '/.env-tests' );
		$json             = json_decode( $decrypted_config, true );

		// Enable Encryption.
		$application_tester->run( [
			'command' => EnableEncryptionCommand::getDefaultName(),
		], [ 'capture_stderr_separately' => true ] );

		$encrypted_config = file_get_contents( Environment::get_qit_dir() . '/.env-tests' );
		$this->assertIsArray( json_decode( $this->encryption->decrypt( $encrypted_config ), true ) );

		$this->assertIsArray( $json );
		$this->assertCommandIsSuccessful( $application_tester );
	}

	public function test_can_change_encryption() {
		$application_tester = $this->make_application_tester();

		// Get Woo Extensions.
		$application_tester->run( [
			'command'   => WooExtensionsCommand::getDefaultName(),
			'--refresh' => true,
		], [ 'capture_stderr_separately' => true ] );

		$encrypted_config = file_get_contents( Environment::get_qit_dir() . '/.env-tests' );
		$this->assertIsArray( json_decode( $this->encryption->decrypt( $encrypted_config ), true ) );

		// Change encryption key.
		$application_tester->run( [
			'command' => ChangeEncryptionKeyCommand::getDefaultName(),
			'--old-key' => Encryption::get_default_password(),
			'--new-key' => '123'
		], [
			'capture_stderr_separately' => true,
			'interactive'               => true,
		] );

		$encrypted_config = file_get_contents( Environment::get_qit_dir() . '/.env-tests' );
		$this->assertIsArray( json_decode( $this->encryption->decrypt( $encrypted_config ), true ) );

		$this->assertCommandIsSuccessful( $application_tester );
	}
}