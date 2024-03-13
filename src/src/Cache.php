<?php

namespace QIT_CLI;

use QIT_CLI\IO\Output;

class Cache {
	/** @var array<scalar|array<scalar>> $cache */
	protected $cache = [];

	/** @var Environment $environment */
	protected $environment;

	/** @var bool */
	protected $did_init = false;

	/** @var string */
	protected $cache_file_path;

	public function __construct() {
		$this->cache_file_path = $this->make_cache_path_for_environment( Config::get_current_environment() );
		$this->init_cache();
	}

	public function get_cache_file_path(): string {
		return $this->cache_file_path;
	}

	public function make_cache_path_for_environment( string $environment ): string {
		return Config::get_qit_dir() . ".env-$environment.json";
	}

	/**
	 * @param string              $key The cache key.
	 * @param scalar|array<mixed> $value The cache value.
	 * @param int                 $expire How many seconds from now should this cache expire. -1 for no expiration. 0 for only current request.
	 *
	 * @return void
	 */
	public function set( string $key, $value, int $expire ): void {
		if ( ! $this->did_init ) {
			throw new \LogicException( 'Cache not initialized.' );
		}

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
	public function get( string $key, bool $ignore_expiration = false ) {
		// Delete expired caches.
		$deleted = 0;
		foreach ( $this->cache as $k => $c ) {
			// Skip caches with no expiration.
			if ( $c['expire'] === - 1 ) {
				continue;
			}

			if ( $ignore_expiration === false && time() > $c['expire'] ) {
				++$deleted;
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
	public function delete( string $key ) {
		unset( $this->cache[ $key ] );
		$this->save();
	}

	/**
	 * Read the cache file and store it on this instance.
	 *
	 * @throws \RuntimeException When could not read the cache file.
	 */
	protected function init_cache(): void {
		$this->did_init = true;

		if ( ! file_exists( $this->cache_file_path ) ) {
			return;
		}

		$data = file_get_contents( $this->cache_file_path );

		if ( $data === false ) {
			throw new \RuntimeException( 'Could not read cache file. Please check if PHP has read permissions on file ' . $this->cache_file_path );
		}

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
		$written = file_put_contents( $this->cache_file_path, json_encode( $this->cache ) );

		if ( ! $written ) {
			throw new \RuntimeException( sprintf( "Could not write to the file %s. Please check if it's writable.", Config::get_qit_dir() . '.woo-qit-cli' ) );
		}
	}

	/**
	 * @param string $key The key to get.
	 *
	 * @return scalar|array<mixed> The value of the key.
	 * @throws \UnexpectedValueException When requested a key that does not exist in the sync data.
	 */
	public function get_manager_sync_data( string $key ) {
		$manager_data = $this->get( App::make( ManagerSync::class )->sync_cache_key );

		if ( ! is_array( $manager_data ) ) {
			throw new \UnexpectedValueException( 'The manager sync data is not an array.' );
		}

		if ( ! array_key_exists( $key, $manager_data ) ) {
			throw new \UnexpectedValueException( "The manager sync data does not have the key '$key'." );
		}

		return $manager_data[ $key ];
	}
}
