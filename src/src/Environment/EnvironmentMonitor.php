<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\EnvInfo;

class EnvironmentMonitor {
	/** @var Cache $cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * @return array<EnvInfo>
	 */
	public function get(): array {
		$env_info_json = $this->cache->get( 'environment_monitor' );

		if ( $env_info_json === null ) {
			return [];
		}

		// Decode JSON and use array_map to transform the data.
		return array_map( function ( $env_info_json ) {
			return EnvInfo::from_array( $env_info_json );
		}, json_decode( $env_info_json, true ) );
	}

	public function get_env_info_by_id( string $env_info_id ): EnvInfo {
		if ( empty( $env_info_id ) ) {
			throw new \Exception( 'Environment not found.' );
		}
		foreach ( $this->get() as $env_info ) {
			if ( $env_info->env_id == $env_info_id ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison,Universal.Operators.StrictComparisons.LooseEqual
				return $env_info;
			}
		}

		throw new \Exception( 'Environment not found.' );
	}

	public function get_env_info_by_path( string $temporary_path ): EnvInfo {
		foreach ( $this->get() as $env_info ) {
			if ( $env_info->temporary_env === $temporary_path ) {
				return $env_info;
			}
		}

		throw new \Exception( 'Environment not found.' );
	}

	public function environment_added_or_updated( EnvInfo $env_info ): bool {
		$environments                      = $this->get();
		$environments[ $env_info->env_id ] = $env_info;
		$this->cache->set( 'environment_monitor', json_encode( $environments ), WEEK_IN_SECONDS );

		return true;
	}

	public function environment_stopped( EnvInfo $env_info ): bool {
		// Filter out the stopped environment.
		$environments = array_filter( $this->get(), function ( $key ) use ( $env_info ) {
			// Handle any kind of string type junggling while still using strict comparison.
			if ( is_numeric( $key ) && is_numeric( $env_info->env_id ) ) {
				return (int) $key !== (int) $env_info->env_id;
			}
			return $key !== $env_info->env_id;
		}, ARRAY_FILTER_USE_KEY );

		$this->cache->set( 'environment_monitor', json_encode( $environments ), WEEK_IN_SECONDS );

		return true;
	}
}
