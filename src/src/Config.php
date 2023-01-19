<?php

namespace QIT_CLI;

use QIT_CLI\IO\Output;

class Config {
	/** @var array<scalar|array<scalar>> */
	protected $schema = [
		'user'                 => '',
		'application_password' => '',
		'test_types'           => [],
		'cache'                => [],
	];

	/** @var array<scalar|array<scalar>> $config */
	protected $config = [];

	public function __construct() {
		$this->init_config();
	}


	/**
	 * @param string $key The key to retrieve from the config file.
	 *
	 * @throws \InvalidArgumentException When the provided key does not exist in the config.
	 *
	 * @return mixed The value stored in the config.
	 */
	public function get( string $key ) {
		if ( ! array_key_exists( $key, $this->config ) ) {
			throw new \InvalidArgumentException( "The config item $key does not exist." );
		}

		return $this->config[ $key ];
	}

	/**
	 * Set something in the config file.
	 *
	 * @param string $key The key to save in the config file.
	 * @param mixed  $value The value to save in the config file. Must be able to be cast to JSON.
	 *
	 * @throws \InvalidArgumentException When could not write to the CD Config file.
	 *
	 * @return void
	 */
	public function set( string $key, $value ) {
		if ( ! array_key_exists( $key, $this->schema ) ) {
			throw new \InvalidArgumentException( "Cannot write to QIT CLI file, as $key is not in the config schema." );
		}

		$this->config[ $key ] = $value;
		$this->save();
	}

	/**
	 * @param string              $key The cache key.
	 * @param scalar|array<mixed> $value The cache value.
	 * @param int                 $expire How many seconds from now should this cache expire. -1 for no expiration.
	 *
	 * @return void
	 */
	public function set_cache( string $key, $value, int $expire ): void {
		try {
			$cache = $this->get( 'cache' );

			if ( ! is_array( $cache ) ) {
				$cache = [];
			}
		} catch ( \Exception $e ) {
			$cache = [];
		}

		if ( $expire !== - 1 ) {
			$expire = time() + $expire;
		}

		$cache[ $key ] = [
			'expire' => $expire,
			'value'  => $value,
		];

		$this->set( 'cache', $cache );
	}

	/**
	 * @param string $key The cache key to get.
	 * @param bool   $ignore_expiration If true, it will return an expired cache entry if available.
	 *
	 * @return mixed|null Whatever is in the cache, either a scalar or an array of scalars or array of arrays of scalars. Null if cache not found.
	 */
	public function get_cache( string $key, bool $ignore_expiration = false ) {
		try {
			$cache = $this->get( 'cache' );

			if ( ! is_array( $cache ) ) {
				$cache = [];
			}
		} catch ( \Exception $e ) {
			$cache = [];
		}

		// Delete expired caches.
		$deleted = 0;
		foreach ( $cache as $k => $c ) {
			// Skip caches with no expiration.
			if ( $c['expire'] === - 1 ) {
				continue;
			}

			if ( $ignore_expiration === false && time() > $c['expire'] ) {
				$deleted ++;
				unset( $cache[ $k ] );
			}
		}

		if ( $deleted > 0 ) {
			if ( App::make( Output::class )->isVeryVerbose() ) {
				App::make( Output::class )->writeln( "[Info]: Deleting $deleted expired cache entries." );
			}

			$this->set( 'cache', $cache );
		}

		if ( ! array_key_exists( $key, $cache ) ) {
			return null;
		}

		return $cache[ $key ]['value'];
	}

	/**
	 * @param string $key The cache key to delete.
	 *
	 * @return void
	 */
	public function delete_cache( string $key ) {
		try {
			$cache = $this->get( 'cache' );

			if ( ! is_array( $cache ) ) {
				$cache = [];
			}
		} catch ( \Exception $e ) {
			$cache = [];
		}

		unset( $cache[ $key ] );

		$this->set( 'cache', $cache );
	}

	/**
	 * Read the config file and store it on this instance.
	 *
	 * @throws \RuntimeException When could not read the config file.
	 */
	protected function init_config(): void {
		if ( ! file_exists( $this->get_config_filepath() ) ) {
			return;
		}

		$data = file_get_contents( $this->get_config_filepath() );

		if ( $data === false ) {
			throw new \RuntimeException( 'Could not read config file. Please check if PHP has read permissions on file ' . $this->get_config_filepath() );
		}

		$config = json_decode( $data, true ) ?: [];

		// Generate an array with the existing data. Fill-in the blanks with the schema.
		$config = array_merge( $this->schema, $config );

		// Remove items that are not in the schema.
		$config = array_intersect_key( $config, $this->schema );

		$this->config = $config;
	}

	/**
	 * Write the config to file.
	 *
	 * @throws \RuntimeException When could not write to the config file.
	 *
	 * @return void
	 */
	public function save(): void {
		$written = file_put_contents( $this->get_config_filepath(), json_encode( $this->config, JSON_PRETTY_PRINT ) );

		if ( ! $written ) {
			throw new \RuntimeException( sprintf( "Could not write to the file %s. Please check if it's writable.", $this->get_config_dir() . '/.woo-qit-cli' ) );
		}
	}

	/**
	 * @throws \RuntimeException When it can't find the QIT CLI directory.
	 * @return string The path to the QIT CLI directory.
	 */
	protected function get_config_dir(): string {
		// Windows alternative.
		if ( ! empty( getenv( 'QIT_CLI_CONFIG_DIR' ) ) ) {
			if ( ! file_exists( getenv( 'QIT_CLI_CONFIG_DIR' ) ) ) {
				throw new \RuntimeException( sprintf( 'The QIT_CLI_CONFIG_DIR environment variable is defined, but points to a non-existing directory: %s', getenv( 'QIT_CLI_CONFIG_DIR' ) ) );
			}

			return getenv( 'QIT_CLI_CONFIG_DIR' );
		}

		// Unix.
		if ( isset( $_SERVER['HOME'] ) ) {
			if ( ! file_exists( $_SERVER['HOME'] ) ) {
				throw new \RuntimeException( sprintf( 'The HOME environment variable is defined, but points to a non-existing directory: %s', $_SERVER['HOME'] ) );
			}

			return $_SERVER['HOME'];
		}

		$message = '';

		if ( is_windows() ) {
			$message .= 'The QIT CLI is meant to run on Unix environments, such as Windows WSL, Linux, or Mac. On native Windows, ';
		}

		$message .= "You need to set an environment variable 'QIT_CLI_CONFIG_DIR' pointing to a writable directory where the QIT CLI can write it's config file. Do NOT use a directory inside your plugin, as the config file will hold sensitive information that should not be included in your plugin.";

		throw new \RuntimeException( $message );
	}

	/**
	 * @return string The file path of the QIT CLI config file.
	 */
	public function get_config_filepath(): string {
		return App::getVar( 'override_cd_config_file', $this->get_config_dir() . '/.woo-qit-cli' );
	}

	/**
	 * Delete the CD Config file, resetting the QIT CLI to a clean state.
	 *
	 * @throws \RuntimeException When could not delete the config file.
	 *
	 * @return void
	 */
	public function reset(): void {
		if ( file_exists( $this->get_config_filepath() ) ) {
			$unlinked = unlink( $this->get_config_filepath() );
			if ( ! $unlinked ) {
				throw new \RuntimeException( 'Could not delete config file. Please check if PHP has read permissions on file ' . $this->get_config_filepath() );
			}
		}
	}

	/**
	 * @return bool True if the QIT CLI is initialized. False if not.
	 * @throws \RuntimeException When the QIT CLI config file exists, but is not readable.
	 */
	public function is_initialized(): bool {
		if ( ! file_exists( $this->get_config_filepath() ) ) {
			return false;
		}

		if ( ! is_readable( $this->get_config_filepath() ) ) {
			throw new \RuntimeException( sprintf( 'The config file exists but it\'s not readable: %s', $this->get_config_dir() . '/.woo-qit-cli' ) );
		}

		$json = json_decode( file_get_contents( $this->get_config_filepath() ), true );

		if ( ! is_array( $json ) ) {
			return false;
		}

		$has_application_password_auth = ! empty( $json['user'] ) && ! empty( $json['application_password'] );
		$has_cd_secret                 = ! empty( $json['cache']['cd_secret'] );

		if ( ! $has_application_password_auth && ! $has_cd_secret ) {
			return false;
		}

		return true;
	}
}
