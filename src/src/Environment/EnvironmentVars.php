<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Config;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;

/**
 * Centralized environment variable mapping for QIT environments.
 *
 * This is the single source of truth for environment variables that are:
 * 1. Set by the orchestrator when running tests
 * 2. Exported when using `qit env:source` for manual testing
 *
 * This ensures consistency between automated and manual testing workflows.
 */
class EnvironmentVars {
	/** @var Config */
	private Config $config;

	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * Get the environment variable mapping for a given environment.
	 *
	 * These variables are used by:
	 * - Test packages (via process.env in Node.js)
	 * - playwright.config.js (for baseURL etc)
	 * - qit-helpers.js (for WP-CLI commands)
	 *
	 * @param E2EEnvInfo $env_info The environment information.
	 * @return array<string, string> Key-value pairs of environment variables.
	 */
	public function get_mapping( E2EEnvInfo $env_info ): array {
		$vars = [
			// Core QIT variables
			'QIT'           => '1',  // Indicates running in QIT context
			'QIT_ENV_ID'    => $env_info->env_id,
			'QIT_SITE_URL'  => $env_info->site_url,
			'QIT_WP_ADMIN'  => $env_info->site_url . '/wp-admin',

			// Standard Playwright/testing variables
			'BASE_URL'      => $env_info->site_url,
			'WP_ADMIN_URL'  => $env_info->site_url . '/wp-admin',

			// Database connection
			'DB_HOST'       => 'localhost',
			'DB_PORT'       => (string) $env_info->db_port,
			'DB_NAME'       => 'wordpress',
			'DB_USER'       => 'root',
			'DB_PASSWORD'   => 'root',

			// WordPress details
			'WP_USERNAME'   => 'admin',
			'WP_PASSWORD'   => 'password',

			// Container details (for advanced use)
			'PHP_CONTAINER' => $env_info->php_container ?? '',
			'DB_CONTAINER'  => $env_info->db_container ?? '',
		];

		// Add any dynamic environment-specific variables
		if ( ! empty( $env_info->additional_vars ) ) {
			$vars = array_merge( $vars, $env_info->additional_vars );
		}

		return $vars;
	}

	/**
	 * Generate a shell script that exports environment variables.
	 *
	 * This creates a source-able shell script for manual testing.
	 *
	 * @param E2EEnvInfo $env_info The environment information.
	 * @return string The shell script content.
	 */
	public function generate_source_file( E2EEnvInfo $env_info ): string {
		$vars = $this->get_mapping( $env_info );

		$content  = "#!/bin/bash\n";
		$content .= "# QIT Environment Variables\n";
		$content .= '# Generated: ' . gmdate( 'Y-m-d H:i:s' ) . "\n";
		$content .= "# Environment: {$env_info->env_id}\n";
		$content .= "#\n";
		$content .= "# This file is auto-generated. Do not edit manually.\n";
		$content .= "# To regenerate: qit env:source {$env_info->env_id}\n";
		$content .= "\n";

		// Export all variables
		foreach ( $vars as $key => $value ) {
			// Properly escape values for shell
			$escaped_value = str_replace( '"', '\\"', $value );
			$content      .= sprintf( "export %s=\"%s\"\n", $key, $escaped_value );
		}

		// Add feedback when sourced
		$content .= "\n";
		$content .= "# Provide feedback when sourced\n";
		$content .= 'if [ -n "$BASH_VERSION" ] || [ -n "$ZSH_VERSION" ]; then' . "\n";
		$content .= '  echo "✓ QIT environment variables loaded"' . "\n";
		$content .= '  echo "  Environment: $QIT_ENV_ID"' . "\n";
		$content .= '  echo "  Site URL: $QIT_SITE_URL"' . "\n";
		$content .= "fi\n";

		return $content;
	}

	/**
	 * Get the path where environment source files should be stored.
	 *
	 * @return string The directory path.
	 */
	public function get_env_directory(): string {
		$dir = $this->config::get_qit_dir() . '/environments';
		if ( ! is_dir( $dir ) ) {
			mkdir( $dir, 0755, true );
		}

		return $dir;
	}

	/**
	 * Save environment file for a given environment.
	 *
	 * @param E2EEnvInfo $env_info The environment information.
	 * @return array<string> Paths to the created files.
	 */
	public function save_environment_file( E2EEnvInfo $env_info ): array {
		$dir = $this->get_env_directory();

		// Shell script
		$shell_file = $dir . '/' . $env_info->env_id . '.sh';
		file_put_contents( $shell_file, $this->generate_source_file( $env_info ) );
		chmod( $shell_file, 0755 );

		// Update the "current" symlink
		$current_link = $dir . '/current.sh';
		if ( file_exists( $current_link ) || is_link( $current_link ) ) {
			unlink( $current_link );
		}
		symlink( $shell_file, $current_link );

		return [
			'shell'   => $shell_file,
			'current' => $current_link,
		];
	}
}
