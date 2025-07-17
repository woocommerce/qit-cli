<?php

namespace QIT_CLI\PreCommand\Interfaces;

/**
 * Interface for remote test commands (API-based tests)
 * These commands need test configuration from qit.json but don't run locally
 */
interface ConfigurableTestCommand {
	/**
	 * Get the test type (e.g., 'security', 'phpstan', 'compatibility')
	 */
	public function get_test_type(): string;

	/**
	 * Get the test profile name
	 */
	public function get_test_profile(): string;
}
