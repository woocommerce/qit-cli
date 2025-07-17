<?php

namespace QIT_CLI\PreCommand\Results;

use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;

/**
 * Result for remote test commands - contains configuration and test settings
 */
class ConfigurationResult {
	public ResolvedConfiguration $configuration;
	public array $test_config;
	public array $api_payload;

	public function __construct( ResolvedConfiguration $configuration, array $test_config ) {
		$this->configuration = $configuration;
		$this->test_config   = $test_config;

		// Prepare API payload from configuration
		$this->api_payload = $this->prepare_api_payload();
	}

	/**
	 * Prepare the base API payload from configuration
	 */
	protected function prepare_api_payload(): array {
		$payload = [];

		// Add test configuration parameters
		foreach ( $this->test_config as $key => $value ) {
			// Skip internal configuration keys
			if ( in_array( $key, [ 'environment', 'test_packages', 'extends' ], true ) ) {
				continue;
			}
			$payload[ $key ] = $value;
		}

		// Add SUT information if available
		if ( $this->configuration->sut ) {
			$payload['sut_slug'] = $this->configuration->sut['slug'];
			$payload['sut_type'] = $this->configuration->sut['type'];
		}

		return $payload;
	}
}
