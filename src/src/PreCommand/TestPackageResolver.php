<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use function QIT_CLI\normalize_path;

class TestPackageResolver {
	protected TestPackageDownloader $package_downloader;

	public function __construct( TestPackageDownloader $package_downloader ) {
		$this->package_downloader = $package_downloader;
	}

	/**
	 * Resolve test packages for a given test type and profile
	 */
	public function resolve(
		ResolvedConfiguration $config,
		string $test_type,
		string $profile
	): array {
		// Get test configuration
		$test_config = $config->get_test_config( $test_type, $profile );

		if ( empty( $test_config['test_packages'] ) ) {
			return [];
		}

		$resolved_packages = [];
		$remote_packages   = [];

		// Separate local and remote packages
		foreach ( $test_config['test_packages'] as $package_ref ) {
			$package_info = $config->get_test_package( $package_ref );

			if ( $package_info['remote'] ?? false ) {
				$remote_packages[ $package_ref ] = $package_info;
			} else {
				// Local package - already resolved
				$resolved_packages[ $package_ref ] = $package_info;
			}
		}

		// Download remote packages if any
		if ( ! empty( $remote_packages ) ) {
			$cache_dir  = normalize_path( Config::get_qit_dir() . 'cache' );
			$downloaded = $this->package_downloader->download( $remote_packages, $cache_dir );

			// Merge downloaded packages
			foreach ( $downloaded as $ref => $manifest ) {
				$resolved_packages[ $ref ] = array_merge( $remote_packages[ $ref ], $manifest );
			}
		}

		return $resolved_packages;
	}
}
