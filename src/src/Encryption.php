<?php

namespace QIT_CLI;

use Laminas\Crypt\Hybrid;
use Laminas\Crypt\PublicKey\Rsa\PrivateKey;
use Laminas\Crypt\PublicKey\Rsa\PublicKey;
use Laminas\Crypt\PublicKey\RsaOptions;
use QIT_CLI\Exceptions\EncryptionException;
use QIT_CLI\IO\Input;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

class Encryption {
	/** @var string The path to the directory to store the SSH keys. */
	protected $ssh_keys_dir;

	public function __construct() {
		$this->ssh_keys_dir = Config::get_qit_dir();
	}

	public function disable_encryption() {
		Config::set_encryption( false );
		App::make(Environment::class)->delete_all_environments();
		$this->delete_keys();
	}

	public function enable_encryption( string $password ) {
		try {
			$this->generate_key( $password );

			Config::set_encryption( true );

			foreach ( App::make( Environment::class )->get_configured_environments( false ) as $env_file_path ) {
				$written = file_put_contents( $env_file_path, $this->encrypt( file_get_contents( $env_file_path ) ) );

				if ( $written === false ) {
					throw new \RuntimeException( 'Could not write env file.' );
				}
			}
		} catch ( \Exception $e ) {
			$this->disable_encryption();
			throw new \RuntimeException( 'Could not enable encryption.' );
		}
	}

	protected function delete_keys() {
		if ( file_exists( $this->ssh_keys_dir . '/private.key' ) ) {
			unlink( $this->ssh_keys_dir . '/private.key' );
		}

		if ( file_exists( $this->ssh_keys_dir . '/public.key' ) ) {
			unlink( $this->ssh_keys_dir . '/public.key' );
		}
	}

	public function get_decryption_password() {
		// Early bail: Already asked for password.
		if ( ! is_null( App::getVar( 'enc_password' ) ) ) {
			return App::getVar( 'enc_password' );
		}

		$password = $this->get_encryption_password_from_shared_memory();

		if ( is_null( $password ) ) {
			$question = new Question( App::getVar( 'ask_encryption_password', 'Please enter the encryption password:' ) );
			$question->setHidden( true );
			$question->setHiddenFallback( false );

			$password = ( new QuestionHelper() )->ask( App::make( Input::class ), App::make( Output::class ), $question );
		}

		App::setVar( 'enc_password', $password );

		return $password;
	}

	public function generate_key( string $password ) {
		// Generate public and private key.
		$rsa_options = new RsaOptions( [
			'pass_phrase' => $password,
		] );
		$rsa_options->generateKeys( [
			'private_key_bits' => 4096,
		] );

		if ( ! file_put_contents( $this->ssh_keys_dir . '/private.key', $rsa_options->getPrivateKey()->toString() ) ) {
			throw new \RuntimeException( 'Could not write private.key file.' );
		}

		if ( ! file_put_contents( $this->ssh_keys_dir . '/public.key', $rsa_options->getPublicKey()->toString() ) ) {
			throw new \RuntimeException( 'Could not write public.key file.' );
		}

		App::setVar( 'enc_password', $password );
		$this->save_encryption_password_to_shared_memory( $password );
	}

	public function encrypt( string $plain_text ): string {
		if ( ! Config::is_encryption_enabled() ) {
			return $plain_text;
		}

		$public_key = new PublicKey( file_get_contents( $this->ssh_keys_dir . 'public.key' ) );

		try {
			return ( new Hybrid() )->encrypt( $plain_text, $public_key );
		} catch ( \Exception $e ) {
			throw EncryptionException::encrypt_error( $e );
		}
	}

	public function decrypt( string $cipher_text ): string {
		if ( ! Config::is_encryption_enabled() ) {
			return $cipher_text;
		}

		if ( $cipher_text === '' ) {
			return '';
		}

		$password = $this->get_decryption_password();

		$private_key = new PrivateKey( file_get_contents( $this->ssh_keys_dir . 'private.key' ), $password );

		try {
			$plain_text = ( new Hybrid() )->decrypt( $cipher_text, $private_key );
			$this->save_encryption_password_to_shared_memory( $password );

			return $plain_text;
		} catch ( \Exception $e ) {
			throw EncryptionException::decrypt_error( $e );
		}
	}

	/**
	 * @return string|null The password stored in the shared memory, if any.
	 */
	protected function get_encryption_password_from_shared_memory() {
		// Early bail: If "shmop" is not available, simply do not persist the password in memory.
		if ( ! extension_loaded( 'shmop' ) ) {
			return null;
		}

		$shared_memory = @shmop_open( 723894723984732, "c", 0600, 1000 );

		if ( $shared_memory === false ) {
			$shared_memory = shmop_open( 723894723984732, "a", 0600, 1000 );
		}

		$password = shmop_read( $shared_memory, 0, 1000 );

		shmop_close( $shared_memory );

		if ( empty( trim( $password ) ) ) {
			return null;
		}



		return trim( $password );
	}

	/**
	 * @param string $password The password to store in the shared memory.
	 *
	 * @return void
	 */
	protected function save_encryption_password_to_shared_memory( string $password ): void {
		// Early bail: If "shmop" is not available, simply do not persist the password in memory.
		if ( ! extension_loaded( 'shmop' ) ) {
			return;
		}

		$shared_memory = @shmop_open( 723894723984732, "c", 0600, 1000 );

		if ( $shared_memory === false ) {
			$shared_memory = shmop_open( 723894723984732, "w", 0600, 1000 );
		}

		$written = shmop_write( $shared_memory, str_pad( $password, 1000 ), 0 );

		if ( $written === false ) {
			throw EncryptionException::password_persist_exception();
		}
	}
}
