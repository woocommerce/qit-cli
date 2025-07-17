<?php

namespace QIT_CLI\PreCommand\Interfaces;

/**
 * Interface for local test commands (need full environment + test packages)
 * These commands run tests locally in Docker environments
 */
interface LocalTestCommand extends EnvironmentCommand {
	/**
	 * Get the test type for loading test packages
	 */
	public function get_test_type(): string;

	/**
	 * Get the test profile for loading test packages
	 */
	public function get_test_profile(): string;
}
