<?php

namespace QIT_CLI\PreCommand\Results;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;

/**
 * Result for local test commands - contains everything needed to run tests locally
 */
class LocalTestResult extends EnvironmentResult {
	public array $test_packages;
	public array $test_config;

	public function __construct(
		ResolvedConfiguration $configuration,
		E2EEnvInfo $env_info,
		array $test_packages,
		array $test_config
	) {
		parent::__construct( $configuration, $env_info );

		$this->test_packages = $test_packages;
		$this->test_config   = $test_config;
	}

	/**
	 * Get test packages organized by reference
	 */
	public function get_test_packages_by_reference(): array {
		return $this->test_packages;
	}

	/**
	 * Get all test package paths
	 */
	public function get_test_package_paths(): array {
		$paths = [];

		foreach ( $this->test_packages as $ref => $package ) {
			if ( isset( $package['path'] ) ) {
				$paths[ $ref ] = $package['path'];
			} elseif ( isset( $package['downloaded_path'] ) ) {
				$paths[ $ref ] = $package['downloaded_path'];
			}
		}

		return $paths;
	}

	/**
	 * Check if all required test packages are available
	 */
	public function has_all_test_packages(): bool {
		if ( empty( $this->test_config['test_packages'] ) ) {
			return true; // No packages required
		}

		foreach ( $this->test_config['test_packages'] as $required ) {
			if ( ! isset( $this->test_packages[ $required ] ) ) {
				return false;
			}
		}

		return true;
	}
}
