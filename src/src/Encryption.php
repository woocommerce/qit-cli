<?php

namespace QIT_CLI;

use Laminas\Crypt\Hybrid;
use Laminas\Crypt\PublicKey\Rsa\PrivateKey;
use Laminas\Crypt\PublicKey\Rsa\PublicKey;
use Laminas\Crypt\PublicKey\RsaOptions;
use QIT_CLI\Commands\Encrypt\ChangeEncryptionKeyCommand;
use QIT_CLI\Exceptions\EncryptionException;
use QIT_CLI\IO\Input;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;

class Encryption {
	/** @var string The path to the directory to store the SSH keys. */
	protected $ssh_keys_dir;

	protected $encryption_disabled;

	public function __construct() {
		$this->ssh_keys_dir = Environment::get_qit_dir();
	}

	protected function is_encryption_disabled( bool $recheck = false ) {
		if ( is_null( $this->encryption_disabled ) || $recheck ) {
			$this->encryption_disabled = file_exists( $this->ssh_keys_dir . '.encryption-disabled' ) || ! extension_loaded( 'openssl' );
		}

		return $this->encryption_disabled;
	}

	public function init() {
		if ( $this->is_encryption_disabled( true ) ) {
			return;
		}

		// Tweak the question when changing keys to be more clear.
		if ( App::make( Input::class )->getFirstArgument() === ChangeEncryptionKeyCommand::getDefaultName() ) {
			App::setVar( 'ask_encryption_password', 'Please enter the old password:' );
		}

		if ( file_exists( $this->ssh_keys_dir . '/private.key' ) && file_exists( $this->ssh_keys_dir . '/public.key' ) ) {
			return;
		}

		// Make sure we don't have any leftover keys.
		$this->delete_keys();

		// Create generic key.
		$this->generate_key();
	}

	public function disable_encryption( ?string $old_password = null ) {
		if ( $this->is_encryption_disabled( true ) ) {
			throw new \LogicException( 'Encryption is already disabled.' );
		}

		// Re-write unencrypted config files.
		if ( ! is_null( $old_password ) ) {
			$envs = [];
			foreach ( App::make( Environment::class )->get_environment_files() as $env_file_path ) {
				$envs[ $env_file_path ] = $this->decrypt( file_get_contents( $env_file_path ) );
			}

			foreach ( $envs as $env_file_path => $env_file_contents ) {
				$written = file_put_contents( $env_file_path, $env_file_contents );

				if ( $written === false ) {
					throw new \RuntimeException( 'Could not write env file.' );
				}
			}
		}

		$this->delete_keys();

		if ( ! touch( $this->ssh_keys_dir . '.encryption-disabled' ) ) {
			throw new \RuntimeException( sprintf( 'Could not enable encryption. Please create the file %s manually and delete all config files.', $this->ssh_keys_dir . '.encryption-disabled' ) );
		}

		$this->is_encryption_disabled( true );
	}

	public static function get_default_password(): string {
		return 'no_password';
	}

	public function enable_encryption() {
		if ( ! $this->is_encryption_disabled( true ) ) {
			throw new \LogicException( 'Encryption is already enabled.' );
		}

		try {
			$this->generate_key();

			$this->encryption_disabled = false;

			foreach ( App::make( Environment::class )->get_environment_files() as $env_file_path ) {
				$written = file_put_contents( $env_file_path, $this->encrypt( file_get_contents( $env_file_path ) ) );

				if ( $written === false ) {
					throw new \RuntimeException( 'Could not write env file.' );
				}
			}

			// Persist enable encryption.
			if ( ! unlink( $this->ssh_keys_dir . '.encryption-disabled' ) ) {
				throw new \RuntimeException( sprintf( 'Could not enable encryption. Please delete the file %s manually.', $this->ssh_keys_dir . '.encryption-disabled' ) );
			}

			$this->is_encryption_disabled( true );
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

	public function change_encryption( string $new_password ) {
		$envs = [];
		foreach ( App::make( Environment::class )->get_environment_files() as $env_file_path ) {
			$envs[ $env_file_path ] = $this->decrypt( file_get_contents( $env_file_path ) );
		}

		$this->generate_key( $new_password );

		foreach ( $envs as $env_file_path => $env_file_contents ) {
			$written = file_put_contents( $env_file_path, $this->encrypt( $env_file_contents ) );

			if ( ! $written ) {
				throw new \RuntimeException( 'Could not write env file.' );
			}
		}
	}

	public function using_default_key() {
		if ( ! file_exists( $this->ssh_keys_dir . 'public.key' ) ) {
			return true;
		}

		$public_key = new PublicKey( file_get_contents( $this->ssh_keys_dir . 'public.key' ) );
		$test_value = ( new Hybrid() )->encrypt( 'foo', $public_key );

		try {
			$private_key = new PrivateKey( file_get_contents( $this->ssh_keys_dir . 'private.key' ), static::get_default_password() );
			$decrypted   = ( new Hybrid() )->decrypt( $test_value, $private_key );

			return $decrypted === 'foo';
		} catch ( \Exception $e ) {
			return false;
		}
	}

	public function maybe_ask_password() {
		// Early bail: Already asked for password.
		if ( ! is_null( App::getVar( 'enc_password' ) ) ) {
			return;
		}

		// Early bail: Not using a custom encryption password.
		if ( $this->using_default_key() ) {
			return;
		}

		$password = $this->get_encryption_password_from_shared_memory();

		if ( is_null( $password ) ) {
			$question = new Question( App::getVar( 'ask_encryption_password', 'Please enter the encryption password:' ) );
			$question->setHidden( true );
			$question->setHiddenFallback( false );

			$password = ( new QuestionHelper() )->ask( App::make( Input::class ), App::make( Output::class ), $question );
		}

		App::setVar( 'enc_password', $password );
	}

	public function generate_key( ?string $password = null ) {
		if ( is_null( $password ) ) {
			$password = static::get_default_password();
		}

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
		if ( $this->is_encryption_disabled() ) {
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
		// Early bail: Encryption is disabled. Return the plain text as is, unless we are in Unit Tests.
		if ( $this->is_encryption_disabled() && ! defined( 'UNIT_TESTS' ) ) {
			return $cipher_text;
		}

		if ( $cipher_text === '' ) {
			return '';
		}

		App::make( self::class )->maybe_ask_password();

		$password = App::getVar( 'enc_password', static::get_default_password() );

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
