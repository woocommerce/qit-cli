<?php

namespace QIT_CLI\PreCommand\Interfaces;

/**
 * Interface for commands that need environment setup
 * These commands need to resolve extensions and prepare Docker environments
 */
interface EnvironmentCommand {
	/**
	 * Get the environment name to use
	 */
	public function get_environment_name(): string;

	/**
	 * Whether to download extensions or just resolve configuration
	 */
	public function should_prepare_environment(): bool;
}
