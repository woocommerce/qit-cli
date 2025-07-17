<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\EnvInfo;

class EnvironmentMonitor {
	/** @var Cache */
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

		// Decode JSON and use array_map to transform the data
		$env_info_data = json_decode( $env_info_json, true );

		if ( ! is_array( $env_info_data ) ) {
			return [];
		}

		$environments = [];
		foreach ( $env_info_data as $env_id => $env_info_array ) {
			if ( is_array( $env_info_array ) ) {
				$env_info                = EnvInfo::from_array( $env_info_array );
				$environments[ $env_id ] = $env_info;
			}
		}

		return $environments;
	}

	public function get_env_info_by_id( string $env_info_id ): EnvInfo {
		if ( empty( $env_info_id ) ) {
			throw new \Exception( 'Environment not found.' );
		}
		$environments = $this->get();
		if ( isset( $environments[ $env_info_id ] ) ) {
			return $environments[ $env_info_id ];
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
		$environments = $this->get();

		// Store the EnvInfo object directly
		$environments[ $env_info->env_id ] = $env_info;

		// Serialize to JSON for caching
		$serialized_environments = [];
		foreach ( $environments as $env_id => $env ) {
			$serialized_environments[ $env_id ] = json_decode( json_encode( $env ), true );
		}

		$this->cache->set( 'environment_monitor', json_encode( $serialized_environments ), WEEK_IN_SECONDS );

		return true;
	}

	public function environment_stopped( EnvInfo $env_info ): bool {
		$environments = array_filter( $this->get(), function ( EnvInfo $stored_env_info ) use ( $env_info ) {
			return $stored_env_info->env_id !== $env_info->env_id;
		} );

		$serialized_environments = [];
		foreach ( $environments as $env_id => $env ) {
			$serialized_environments[ $env_id ] = json_decode( json_encode( $env ), true );
		}

		$this->cache->set( 'environment_monitor', json_encode( $serialized_environments ), WEEK_IN_SECONDS );

		return true;
	}
}
