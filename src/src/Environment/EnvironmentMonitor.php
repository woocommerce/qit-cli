<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Cache;

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
		return array_map( function ( $env_info_json ) {
			return EnvInfo::from_array( $env_info_json );
		}, json_decode( $this->cache->get( 'environment_monitor' ), true ) ?? [] );
	}

	public function get_env_info_by_id( string $env_info_id ): EnvInfo {
		foreach ( $this->get() as $env_info ) {
			if ( $env_info->env_id === $env_info_id ) {
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
			return $key !== $env_info->env_id;
		}, ARRAY_FILTER_USE_KEY );

		$this->cache->set( 'environment_monitor', json_encode( $environments ), WEEK_IN_SECONDS );

		return true;
	}

}
