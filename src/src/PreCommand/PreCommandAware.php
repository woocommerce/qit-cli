<?php

namespace QIT_CLI\PreCommand;

/**
 * Simple PreCommand interface with just two essential methods.
 *
 * This replaces the entire complex pipeline/façade/capability-matrix system
 * with a pay-as-you-go approach where commands only get what they ask for.
 */
interface PreCommandAware {
	/**
	 * Merge CLI + qit.json + defaults and give me just the env section I asked for.
	 *
	 * @param string $env Environment name (default: 'default').
	 * @return array<string, mixed> Merged environment configuration
	 */
	public function get_environment_config( string $env = 'default' ): array;

	/**
	 * Merge CLI + qit.json + defaults and give me the test-type/profile section I asked for.
	 *
	 * @param string $test_type Test type (e.g. "e2e", "security").
	 * @param string $profile Profile name (default: 'default').
	 * @return array<string, mixed> Merged test profile configuration
	 */
	public function get_current_test_profile( string $test_type, string $profile = 'default' ): array;
}
