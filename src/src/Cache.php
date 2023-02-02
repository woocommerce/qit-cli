<?php

namespace QIT_CLI;

use QIT_CLI\IO\Output;

class Cache {
	/** @var array<scalar|array<scalar>> $cache */
	protected $cache = [];

	/** @var Environment $environment */
	protected $environment;

	protected $did_init = false;

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}

	/**
	 * @param string              $key The cache key.
	 * @param scalar|array<mixed> $value The cache value.
	 * @param int                 $expire How many seconds from now should this cache expire. -1 for no expiration. 0 for only current request.
	 *
	 * @return void
	 */
	public function set_cache( string $key, $value, int $expire ): void {
		$this->maybe_init_cache();

		if ( $expire !== - 1 ) {
			$expire = time() + $expire;
		}

		$this->cache[ $key ] = [
			'expire' => $expire,
			'value'  => $value,
		];

		if ( $expire !== 0 ) {
			$this->save();
		}
	}

	/**
	 * @param string $key The cache key to get.
	 * @param bool   $ignore_expiration If true, it will return an expired cache entry if available.
	 *
	 * @return mixed|null Whatever is in the cache, either a scalar or an array of scalars or array of arrays of scalars. Null if cache not found.
	 */
	public function get_cache( string $key, bool $ignore_expiration = false ) {
		$this->maybe_init_cache();

		// Delete expired caches.
		$deleted = 0;
		foreach ( $this->cache as $k => $c ) {
			// Skip caches with no expiration.
			if ( $c['expire'] === - 1 ) {
				continue;
			}

			if ( $ignore_expiration === false && time() > $c['expire'] ) {
				$deleted ++;
				unset( $this->cache[ $k ] );
			}
		}

		if ( $deleted > 0 ) {
			if ( App::make( Output::class )->isVeryVerbose() ) {
				App::make( Output::class )->writeln( "[Info]: Deleting $deleted expired cache entries." );
			}

			$this->save();
		}

		if ( ! array_key_exists( $key, $this->cache ) ) {
			return null;
		}

		return $this->cache[ $key ]['value'];
	}

	/**
	 * @param string $key The cache key to delete.
	 *
	 * @return void
	 */
	public function delete_cache( string $key ) {
		unset( $this->cache[ $key ] );
		$this->save();
	}

	/**
	 * Read the cache file and store it on this instance.
	 *
	 * @throws \RuntimeException When could not read the cache file.
	 */
	protected function maybe_init_cache(): void {
		if ( $this->did_init ) {
			return;
		} else {
			$this->did_init = true;
		}

		if ( ! file_exists( $this->environment->get_cache_filepath() ) ) {
			return;
		}

		$data = file_get_contents( $this->environment->get_cache_filepath() );

		if ( $data === false ) {
			throw new \RuntimeException( 'Could not read cache file. Please check if PHP has read permissions on file ' . $this->environment->get_cache_filepath() );
		}

		$data = App::make( Encryption::class )->decrypt( $data );

		$cache = json_decode( $data, true );

		if ( ! is_array( $cache ) ) {
			throw new \RuntimeException( 'Could not parse cache file. Resetting environment, please remove the current Partner/Environment and add it again.' );
		}

		$this->cache = $cache;
	}

	/**
	 * Write the cache to file.
	 *
	 * @return void
	 * @throws \RuntimeException When could not write to the cache file.
	 */
	protected function save(): void {
		$data = App::make( Encryption::class )->encrypt( json_encode( $this->cache ) );

		$written = file_put_contents( $this->environment->get_cache_filepath(), $data );

		if ( ! $written ) {
			throw new \RuntimeException( sprintf( "Could not write to the file %s. Please check if it's writable.", Environment::get_qit_dir() . '.woo-qit-cli' ) );
		}
	}

	/**
	 * @return bool True if the QIT CLI is initialized. False if not.
	 * @throws \RuntimeException When the QIT CLI cache file exists, but is not readable.
	 */
	public function is_initialized(): bool {
		if ( ! file_exists( $this->environment->get_cache_filepath() ) ) {
			return false;
		}

		if ( ! is_readable( $this->environment->get_cache_filepath() ) ) {
			throw new \RuntimeException( sprintf( 'The cache file exists but it\'s not readable: %s', Environment::get_qit_dir() . '.woo-qit-cli' ) );
		}

		$json = json_decode( file_get_contents( $this->environment->get_cache_filepath() ), true );

		if ( ! is_array( $json ) ) {
			return false;
		}

		$has_application_password_auth = ! empty( $json['cache']['user'] ) && ! empty( $json['cache']['application_password'] );
		$has_cd_secret                 = ! empty( $json['cache']['cd_secret'] );

		if ( ! $has_application_password_auth && ! $has_cd_secret ) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $key The key to get.
	 *
	 * @throws \UnexpectedValueException When requested a key that does not exist in the sync data.
	 *
	 * @return scalar|array<mixed> The value of the key.
	 */
	public function get_manager_sync_data( string $key ) {
		$manager_data = $this->get_cache( App::make( ManagerSync::class )->sync_cache_key );

		if ( ! is_array( $manager_data ) ) {
			throw new \UnexpectedValueException( 'The manager sync data is not an array.' );
		}

		if ( ! array_key_exists( $key, $manager_data ) ) {
			throw new \UnexpectedValueException( "The manager sync data does not have the key '$key'." );
		}

		return $manager_data[ $key ];
	}
}
