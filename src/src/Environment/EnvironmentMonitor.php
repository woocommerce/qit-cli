<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use function QIT_CLI\normalize_path;

class EnvironmentMonitor {
	/**
	 * @return array<EnvInfo>
	 */
	public function get(): array {
		$temp_envs_dir = Environment::get_temp_envs_dir();

		if ( ! is_dir( $temp_envs_dir ) ) {
			return [];
		}

		$environments = [];

		/** @var \DirectoryIterator $file_info */
		foreach ( new \DirectoryIterator( $temp_envs_dir ) as $file_info ) {
			if ( $file_info->isDot() || $file_info->isLink() || ! $file_info->isDir() ) {
				continue;
			}

			$env_info_file = $file_info->getPathname() . '/env_info.json';

			if ( ! file_exists( $env_info_file ) ) {
				// Directory exists but no env_info.json — either in-progress setup or orphaned.
				continue;
			}

			$json = file_get_contents( $env_info_file );

			if ( $json === false ) {
				continue;
			}

			$env_info_array = json_decode( $json, true );

			if ( ! is_array( $env_info_array ) ) {
				continue;
			}

			$env_info                          = EnvInfo::from_array( $env_info_array );
			$environments[ $env_info->env_id ] = $env_info;
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
		$normalized_path = normalize_path( $temporary_path );

		foreach ( $this->get() as $env_info ) {
			if ( normalize_path( $env_info->temporary_env ) === $normalized_path ) {
				return $env_info;
			}
		}

		throw new \Exception( 'Environment not found.' );
	}

	public function environment_added_or_updated( EnvInfo $env_info ): bool {
		$env_info_file = rtrim( $env_info->temporary_env, '/' ) . '/env_info.json';
		$tmp_file      = rtrim( $env_info->temporary_env, '/' ) . '/.env_info.json.tmp';

		$json = json_encode( $env_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		if ( $json === false ) {
			throw new \RuntimeException( 'Failed to serialize environment info.' );
		}

		if ( file_put_contents( $tmp_file, $json ) === false ) {
			throw new \RuntimeException( sprintf( 'Failed to write environment info to %s', $tmp_file ) );
		}

		if ( ! rename( $tmp_file, $env_info_file ) ) {
			// Clean up the temp file on failure.
			if ( file_exists( $tmp_file ) ) {
				unlink( $tmp_file );
			}
			throw new \RuntimeException( sprintf( 'Failed to atomically write environment info to %s', $env_info_file ) );
		}

		return true;
	}

	public function environment_stopped( EnvInfo $env_info ): bool {
		$env_info_file = rtrim( $env_info->temporary_env, '/' ) . '/env_info.json';

		if ( file_exists( $env_info_file ) ) {
			unlink( $env_info_file );
		}

		return true;
	}
}
