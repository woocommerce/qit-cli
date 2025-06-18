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

		return array_map( function ( $env_info_array ) {
			return EnvInfo::from_array( is_array( $env_info_array ) ? $env_info_array : [] );
		}, array_values( $env_info_data ) );
	}

	public function get_env_info_by_id( string $env_info_id ): EnvInfo {
		if ( empty( $env_info_id ) ) {
			throw new \Exception( 'Environment not found.' );
		}
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
		$environments = $this->get();

		// Store by env_id, ensuring serialization
		$environments[ $env_info->env_id ] = json_decode( json_encode( $env_info ), true );

		$this->cache->set( 'environment_monitor', json_encode( $environments ), WEEK_IN_SECONDS );

		return true;
	}

	public function environment_stopped( EnvInfo $env_info ): bool {
		// Filter out the stopped environment
		$environments = array_filter( $this->get(), function ( $stored_env_info ) use ( $env_info ) {
			return $stored_env_info->env_id !== $env_info->env_id;
		} );

		// Reindex and serialize
		$serialized_environments = array_map( function ( EnvInfo $env ) {
			return json_decode( json_encode( $env ), true );
		}, array_values( $environments ) );

		$this->cache->set( 'environment_monitor', json_encode( $serialized_environments ), WEEK_IN_SECONDS );

		return true;
	}
}