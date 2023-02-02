<?php

namespace QIT_CLI;

use QIT_CLI\Exceptions\IOException;
use QIT_CLI\IO\Output;

class Config {
	/** @var string  */
	protected $config_file;

	/** @var array<scalar> */
	protected $config = [];

	/** @var array<scalar> */
	protected $schema = [
		'current_environment' => 'default',
		'encryption'          => false,
		'development_mode'    => false,
		'proxy_url'           => '127.0.0.1:8080',
		'last_diagnosis'      => 0,
	];

	/** @var bool */
	protected $did_init = false;

	public function __construct() {
		if ( count( func_get_args() ) > 0 ) {
			// Please do not add arguments to this constructor.
			throw new \LogicException();
		}

		$this->config_file = self::get_qit_dir() . '.qit-config.json';
		$this->init();
	}

	public static function set_development_mode( bool $development_mode ): void {
		App::make( self::class )->set( 'development_mode', $development_mode );
	}

	/**
	 * @return bool True if running in Developer mode. False if not.
	 */
	public static function is_development_mode(): bool {
		return (bool) App::make( self::class )->get( 'development_mode' );
	}

	public static function set_current_environment( string $environment ): void {
		App::make( self::class )->set( 'current_environment', $environment );
	}

	public static function get_current_environment(): string {
		return (string) App::make( self::class )->get( 'current_environment' ) ?: 'default';
	}

	public static function set_encryption( bool $encryption ): void {
		App::make( self::class )->set( 'encryption', $encryption );
	}

	public static function is_encryption_enabled(): bool {
		return (bool) App::make( self::class )->get( 'encryption' );
	}

	public static function set_proxy_url( string $proxy_url ): void {
		App::make( self::class )->set( 'proxy_url', $proxy_url );
	}

	public static function get_proxy_url(): string {
		return (string) App::make( self::class )->get( 'proxy_url' ) ?: '127.0.0.1:8080';
	}

	public static function set_last_diagnosis( int $last_diagnosis ): void {
		App::make( self::class )->set( 'last_diagnosis', $last_diagnosis );
	}

	public static function get_last_diagnosis(): int {
		return (int) App::make( self::class )->get( 'last_diagnosis' ) ?: 0;
	}

	/**
	 * @param string $key
	 * @param scalar $value
	 *
	 * @return void
	 * @throws IOException When can't write to file.
	 */
	protected function set( string $key, $value ): void {
		$this->config[ $key ] = $value;
		$this->save();
	}

	/**
	 * @param string $key The config key to retrieve.
	 *
	 * @return scalar|null
	 */
	protected function get( string $key ) {
		return $this->config[ $key ] ?? null;
	}

	/**
	 * @return void
	 * @throws IOException If can't write to file.
	 */
	protected function save() {
		$json    = json_encode( $this->config, JSON_PRETTY_PRINT );
		$written = file_put_contents( $this->config_file, $json );

		if ( ! $written ) {
			throw IOException::cant_write_to_file( $this->config_file );
		}
	}

	protected function init(): void {
		if ( $this->did_init ) {
			return;
		} else {
			$this->did_init = true;
		}

		if ( file_exists( $this->config_file ) ) {
			$config = json_decode( file_get_contents( $this->config_file ), true );

			if ( ! is_array( $config ) ) {
				throw new \UnexpectedValueException( 'Config is not JSON.' );
			}

			// Generate an array with the existing data. Fill-in the blanks with the schema.
			$config = array_merge( $this->schema, $config );

			// Remove items that are not in the schema.
			$config = array_intersect_key( $config, $this->schema );

			$this->config = $config;
		}
	}

	/**
	 * @throws \RuntimeException When it can't find the QIT CLI directory.
	 * @return string The path to the QIT CLI directory.
	 */
	public static function get_qit_dir(): string {
		$normalize_path = static function ( string $path ): string {
			// Converts Windows-style directory separator to Unix-style. Makes sure it ends with a trailing slash.
			return rtrim( str_replace( '\\', '/', $path ), '/\\' ) . '/';
		};

		// Windows alternative.
		if ( ! empty( getenv( 'QIT_CLI_CONFIG_DIR' ) ) ) {
			if ( ! file_exists( getenv( 'QIT_CLI_CONFIG_DIR' ) ) ) {
				throw new \RuntimeException( sprintf( 'The QIT_CLI_CONFIG_DIR environment variable is defined, but points to a non-existing directory: %s', getenv( 'QIT_CLI_CONFIG_DIR' ) ) );
			}

			return $normalize_path( getenv( 'QIT_CLI_CONFIG_DIR' ) );
		}

		// Unix.
		if ( isset( $_SERVER['HOME'] ) ) {
			if ( ! file_exists( $_SERVER['HOME'] ) ) {
				throw new \RuntimeException( sprintf( 'The HOME environment variable is defined, but points to a non-existing directory: %s', $_SERVER['HOME'] ) );
			}

			$cache_dir = $normalize_path( $_SERVER['HOME'] ) . '.woo-qit-cli/';

			if ( ! file_exists( $cache_dir ) ) {
				$dir_created = mkdir( $cache_dir );

				if ( ! $dir_created ) {
					throw new \RuntimeException( sprintf( 'Could not create the QIT CLI config directory: %s. Please try to create the directory manually. ', $cache_dir ) );
				}
			}

			/*
			 * Make sure the directory has the correct permissions. (0700)
			 * Try to set it, warn if cannot.
			 */
			if ( decoct( fileperms( $cache_dir ) & 0777 ) !== '700' && ! chmod( $cache_dir, 0700 ) && ! App::getVar( 'WARNED_DIR_PERMISSION', false ) ) {
				App::make( Output::class )->writeln(
					sprintf(
						'<info>QIT Warning: Could not set permissions on the QIT CLI config directory. Please check that PHP has write permission to file: %s</info>',
						$cache_dir
					)
				);

				// Show this only once per request.
				App::setVar( 'WARNED_DIR_PERMISSION', true );
			}

			return $cache_dir;
		}

		$message = '';

		if ( is_windows() ) {
			$message .= 'The QIT CLI is meant to run on Unix environments, such as Windows WSL, Linux, or Mac. On native Windows, ';
		}

		$message .= "You need to set an environment variable 'QIT_CLI_CONFIG_DIR' pointing to a writable directory where the QIT CLI can write it's config file. Do NOT use a directory inside your plugin, as the config file will hold sensitive information that should not be included in your plugin.";

		throw new \RuntimeException( $message );
	}
}
