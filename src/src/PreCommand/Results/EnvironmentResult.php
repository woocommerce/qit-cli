<?php

namespace QIT_CLI\PreCommand\Results;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;

/**
 * Result for environment commands - contains resolved environment configuration
 */
class EnvironmentResult {
	public ResolvedConfiguration $configuration;
	public E2EEnvInfo $env_info;
	public array $resolved_extensions;
	public array $downloaded_paths;

	public function __construct(
		ResolvedConfiguration $configuration,
		E2EEnvInfo $env_info
	) {
		$this->configuration = $configuration;
		$this->env_info      = $env_info;

		// Extract resolved extensions from env_info
		$this->resolved_extensions = array_merge(
			$env_info->plugins ?? [],
			$env_info->themes ?? []
		);

		// Extract downloaded paths
		$this->downloaded_paths = $this->extract_downloaded_paths();
	}

	/**
	 * Extract downloaded paths from resolved extensions
	 */
	protected function extract_downloaded_paths(): array {
		$paths = [];

		foreach ( $this->resolved_extensions as $extension ) {
			if ( ! empty( $extension->downloaded_source ) ) {
				$paths[ $extension->slug ] = $extension->downloaded_source;
			}
		}

		return $paths;
	}
}
