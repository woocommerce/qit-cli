<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use function QIT_CLI\normalize_path;

class TestPackageResolver {
	protected TestPackageDownloader $package_downloader;

	/** @var array<string,array<string,mixed>> */
	protected array $metadata = [];

	public function __construct( TestPackageDownloader $package_downloader ) {
		$this->package_downloader = $package_downloader;
	}

	/**
	 * Resolve test packages for a given test type and profile
	 *
	 * @return array<string,\QIT_CLI\PreCommand\Objects\TestPackageManifest>
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

			// Check if this is a remote package by looking at its manifest source
			// For now, treat all packages as local since we need to examine the actual structure
			$resolved_packages[ $package_ref ] = $package_info;
		}

		return $resolved_packages;
	}

	/**
	 * Return metadata for all resolved packages.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function get_metadata(): array {
		return $this->metadata;
	}
}
