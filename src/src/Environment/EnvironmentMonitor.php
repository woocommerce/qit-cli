<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Cache;

class EnvironmentMonitor {
	/** @var array|mixed */
	protected $environment_monitor;

	/** @var Cache $cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	public function get(): array {
		$this->environment_monitor = $this->cache->get( 'environment_monitor' ) ?? [];

		foreach ( $this->environment_monitor as $key => $env_info ) {
			if ( is_array( $env_info ) ) {
				$this->environment_monitor[ $key ] = EnvInfo::from_array( $env_info );
			}
		}

		return $this->environment_monitor;
	}

	public function get_env_info_by_id( string $env_info_id ) {
		$environments = $this->get();

		/** @var EnvInfo $env_info */
		foreach ( $environments as $env_info ) {
			if ( $env_info->get_id() === $env_info_id ) {
				return $env_info;
			}
		}

		throw new \Exception( 'Environment not found.' );
	}

	public function get_env_info_by_path( string $temporary_path ) {
		$environments = $this->get();

		foreach ( $environments as $env_info ) {
			if ( $env_info->temporary_env === $temporary_path ) {
				return $env_info;
			}
		}

		throw new \Exception( 'Environment not found.' );
	}

	public function environment_added_or_updated( EnvInfo $env_info ): bool {
		$environments                        = $this->get();
		$environments[ $env_info->get_id() ] = $env_info;
		$this->cache->set( 'environment_monitor', $environments, WEEK_IN_SECONDS );

		return true;
	}

	public function environment_stopped( EnvInfo $env_info ): bool {
		$this->cache->set( 'environment_monitor', array_diff_key( $this->get(), [ $env_info->get_id() => true ] ), WEEK_IN_SECONDS );

		return true;
	}
}