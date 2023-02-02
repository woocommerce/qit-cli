<?php

namespace QIT_CLI;

use QIT_CLI\Exceptions\IOException;

class Config {
	protected $config_file;

	protected $config = [];

	protected $schema = [
		'current_environment' => '',
		'encryption'          => false,
		'development_mode'    => false,
	];

	protected $did_init = false;

	public function __construct() {
		if ( count( func_get_args() ) > 0 ) {
			// Please do not add arguments to this constructor.
			throw new \LogicException();
		}

		$this->config_file = Environment::get_qit_dir() . '.qit-config.json';
	}

	public static function set_development_mode( bool $development_mode ): void {
		App::make( Config::class )->set( 'development_mode', $development_mode );
	}

	/**
	 * @return bool True if running in Developer mode. False if not.
	 */
	public static function is_development_mode(): bool {
		return (bool) App::make( Config::class )->get( 'development_mode' );
	}

	public static function set_current_environment( string $environment ): void {
		App::make( Config::class )->set( 'current_environment', $environment );
	}

	public static function get_current_environment(): string {
		return (string) App::make( Config::class )->get( 'current_environment' );
	}

	public static function set_encryption( bool $encryption ): void {
		App::make( Config::class )->set( 'encryption', $encryption );
	}

	public static function is_encryption_enabled(): bool {
		return (bool) App::make( Config::class )->get( 'encryption' );
	}

	protected function set( string $key, $value ) {
		$this->maybe_init();
		$this->config[ $key ] = $value;
		$this->save();
	}

	/**
	 * @param string $key The config key to retrieve.
	 *
	 * @return scalar|null
	 */
	protected function get( string $key ) {
		$this->maybe_init();

		return $this->config[ $key ] ?? null;
	}

	/**
	 * @return void
	 * @throws IOException
	 */
	protected function save() {
		$json    = json_encode( $this->config, JSON_PRETTY_PRINT );
		$written = file_put_contents( $this->config_file, $json );

		if ( ! $written ) {
			throw IOException::cant_write_to_file( $this->config_file );
		}
	}

	protected function maybe_init() {
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
}