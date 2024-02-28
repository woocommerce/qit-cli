<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Cache;
use QIT_CLI\Config;

class EnvironmentDownloader {
	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	public function maybe_download( string $env_name ): void {
		$backend_hashes = $this->cache->get_manager_sync_data( 'environments' );

		if ( ! isset( $backend_hashes[ $env_name ]['checksum'] ) || ! isset( $backend_hashes[ $env_name ]['url'] ) ) {
			throw new \RuntimeException( 'E2E environment not set or incomplete.' );
		}


		if ( $this->cache->get( 'e2e_environment_hash' ) !== $backend_hashes[ $env_name ]['checksum'] ) {
			if ( ! file_exists( Config::get_qit_dir() . '/environments' ) ) {
				mkdir( Config::get_qit_dir() . '/environments' );
			}

			if ( defined( 'UNIT_TESTS' ) ) {
				if ( ! file_exists( sprintf( Config::get_qit_dir() . '/environments/%s.zip', $env_name ) ) ) {
					throw new \RuntimeException( $env_name . ' environment not found for tests.' );
				}
			} else {
				// Download the environment.
				file_put_contents( sprintf( Config::get_qit_dir() . '/environments/%s.zip', $env_name ), file_get_contents( $backend_hashes[ $env_name ]['url'] ) );
			}

			// Extract the environment.
			$zip = new \ZipArchive();
			$zip->open( sprintf( Config::get_qit_dir() . '/environments/%s.zip', $env_name ) );
			$zip->extractTo( Config::get_qit_dir() . '/environments/' . $env_name );
			$zip->close();

			$this->cache->set( 'e2e_environment_hash', $backend_hashes[ $env_name ], MONTH_IN_SECONDS );
		}
	}
}
