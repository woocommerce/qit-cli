<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\ManagerSync;
use QIT_CLI\SafeRemove;
use ZipArchive;

class EnvironmentDownloader {
	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	public function maybe_download( string $env_name ): void {
		$manager_hashes = $this->cache->get_manager_sync_data( 'environments' );

		if ( ! isset( $manager_hashes[ $env_name ]['checksum'] ) || ! isset( $manager_hashes[ $env_name ]['url'] ) ) {
			throw new \RuntimeException( 'E2E environment not set or incomplete.' );
		}

		$local_hash = $this->cache->get( "{$env_name}_environment_hash" );

		// Early bail: The local environment matches the last-known checksum that the Manager informed us, so no need to query it.
		if ( $local_hash === $manager_hashes[ $env_name ]['checksum'] ) {
			return;
		}

		$environments_dir = Config::get_qit_dir() . '/environments';
		if ( ! file_exists( $environments_dir ) ) {
			mkdir( $environments_dir );
		}

		$temp_zip_path  = $environments_dir . '/temp_' . $env_name . '.zip';
		$final_zip_path = $environments_dir . '/' . $env_name . '.zip';

		if ( file_exists( $final_zip_path ) ) {
			unlink( $final_zip_path );
		}

		if ( file_exists( $temp_zip_path ) ) {
			unlink( $temp_zip_path );
		}

		if ( defined( 'UNIT_TESTS' ) ) {
			if ( ! file_exists( $final_zip_path ) ) {
				throw new \RuntimeException( $env_name . ' environment not found for tests. Tried: ' . $final_zip_path );
			}

			return;
		}

		$env_contents = @file_get_contents( $manager_hashes[ $env_name ]['url'] );

		// If the download fails, we might have an outdated checksum in the cache. Try to sync and download again.
		// This can happen in a brief time window if the environment is re-generated upstream and this client hasn't synced yet.
		if ( ! $env_contents ) {
			App::make( ManagerSync::class )->maybe_sync( true );
			$manager_hashes = $this->cache->get_manager_sync_data( 'environments' );
			$env_contents   = file_get_contents( $manager_hashes[ $env_name ]['url'] );

			if ( ! $env_contents ) {
				throw new \RuntimeException( 'Could not download environment.' );
			}
		}

		// Save to temp zip.
		file_put_contents( $temp_zip_path, $env_contents );

		// Validate zip integrity.
		$zip = new \ZipArchive();
		$res = $zip->open( $temp_zip_path, ZipArchive::CHECKCONS );
		if ( $res === true ) {
			if ( ! rename( $temp_zip_path, $final_zip_path ) ) {
				throw new \RuntimeException( 'Could not rename temp ZIP to final ZIP.' );
			}

			// Delete old environment extracted files.
			if ( file_exists( $environments_dir . '/' . $env_name ) ) {
				$safe_remove = App::make( SafeRemove::class );
				$safe_remove->delete_dir( $environments_dir . '/' . $env_name, Config::get_qit_dir() );
			}

			if ( ! $zip->extractTo( $environments_dir . '/' . $env_name ) ) {
				throw new \RuntimeException( 'Could not extract environment ZIP.' );
			}

			// Ensure .sh files in the extracted directory are executable, just in case.
			$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $environments_dir . '/' . $env_name ) );
			foreach ( $iterator as $file ) {
				if ( $file->isFile() && $file->getExtension() === 'sh' ) {
					chmod( $file->getPathname(), 0755 );
				}
			}

			$zip->close();
			$this->cache->set( "{$env_name}_environment_hash", $manager_hashes[ $env_name ]['checksum'], MONTH_IN_SECONDS );
		} else {
			unlink( $temp_zip_path );
			switch ( $res ) {
				case ZipArchive::ER_NOZIP:
					$error = 'Not a ZIP archive.';
					break;
				case ZipArchive::ER_INCONS:
					$error = 'Consistency check failed.';
					break;
				case ZipArchive::ER_CRC:
					$error = 'Checksum failed.';
					break;
				default:
					$error = '';
			}

			throw new \RuntimeException( "Downloaded ZIP file is invalid or corrupted. $error" );
		}
	}
}
