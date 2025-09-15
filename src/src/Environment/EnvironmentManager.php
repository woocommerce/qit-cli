<?php

namespace QIT_CLI\Environment;

use RuntimeException;

/**
 * Centralized manager for all environment variable handling.
 *
 * This class is the single source of truth for:
 * - Parsing environment variables from CLI (--env, --env_file)
 * - Validating required secrets
 * - Storing environment variables
 * - Distributing them to execution contexts (host, Docker, Node.js)
 * - Redacting sensitive values from output
 *
 * Design principles:
 * - Single responsibility: All env var logic in one place
 * - Immutable after initialization: Prevents state bugs
 * - Clear separation between regular vars and secrets
 * - Secure by default: Secrets are never exposed in logs
 */
class EnvironmentManager {
	/**
	 * @var array<string,string> All environment variables (regular + secrets)
	 */
	private array $env_vars = [];

	/**
	 * @var array<string> Names of variables that are secrets
	 */
	private array $secret_names = [];

	/**
	 * @var array<string,string> Secret name => value mapping for redaction
	 */
	private array $secret_values = [];

	/**
	 * @var bool Whether this manager has been initialized
	 */
	private bool $initialized = false;

	/**
	 * Initialize the manager with environment variables from various sources.
	 *
	 * @param array<string,string> $cli_env_vars Variables from --env options.
	 * @param array<string,string> $file_env_vars Variables from --env_file options.
	 * @param array<string>        $required_secrets Names of required secrets.
	 * @throws RuntimeException If initialization fails or called twice.
	 */
	public function initialize( array $cli_env_vars = [], array $file_env_vars = [], array $required_secrets = [] ): void {
		if ( $this->initialized ) {
			throw new RuntimeException( 'EnvironmentManager can only be initialized once' );
		}

		// Merge environment variables (CLI takes precedence over files)
		$this->env_vars = array_merge( $file_env_vars, $cli_env_vars );

		// Validate and register secrets
		if ( ! empty( $required_secrets ) ) {
			$this->validate_and_register_secrets( $required_secrets );
		}

		// Apply to PHP environment for backward compatibility and subprocess inheritance
		$this->apply_to_php_environment();

		$this->initialized = true;
	}

	/**
	 * Validate that required secrets are present and register them for tracking.
	 *
	 * @param array<string> $required_secrets Names of required secrets.
	 * @throws RuntimeException If any required secret is missing.
	 */
	private function validate_and_register_secrets( array $required_secrets ): void {
		$missing = [];

		foreach ( $required_secrets as $name ) {
			// Check in our env_vars first, then fall back to system environment
			$value = $this->env_vars[ $name ] ?? getenv( $name );

			if ( $value === false || $value === '' ) {
				$missing[] = $name;
			} else {
				// Register for tracking and redaction
				$this->secret_names[]         = $name;
				$this->secret_values[ $name ] = $value;

				// Ensure it's in our env_vars for distribution
				if ( ! isset( $this->env_vars[ $name ] ) ) {
					$this->env_vars[ $name ] = $value;
				}
			}
		}

		if ( ! empty( $missing ) ) {
			$this->throw_missing_secrets_error( $missing );
		}
	}

	/**
	 * Apply environment variables to PHP's environment.
	 * This ensures subprocess inheritance and backward compatibility.
	 */
	private function apply_to_php_environment(): void {
		foreach ( $this->env_vars as $key => $value ) {
			putenv( "{$key}={$value}" );
		}
	}

	/**
	 * Get environment variables for host command execution.
	 *
	 * @return array<string,string> Environment variables to pass to Process.
	 */
	public function get_host_env(): array {
		$this->ensure_initialized();
		return $this->env_vars;
	}

	/**
	 * Get environment variables for Docker command execution.
	 *
	 * @return array<string,string> Environment variables to pass to Docker
	 */
	public function get_docker_env(): array {
		$this->ensure_initialized();
		return $this->env_vars;
	}

	/**
	 * Get Docker --env flags for secure secret passing.
	 * Returns just the names of secrets that should use --env NAME pattern.
	 *
	 * @return array<string> Secret names to pass with --env flag
	 */
	public function get_docker_secret_flags(): array {
		$this->ensure_initialized();
		return $this->secret_names;
	}

	/**
	 * Get environment variables for the Process that runs Docker.
	 * This ensures --env NAME pattern works correctly.
	 *
	 * @return array<string,string> Environment for the Docker process itself
	 */
	public function get_docker_process_env(): array {
		$this->ensure_initialized();

		// We need to ensure secrets are in the Docker process environment
		// so that --env NAME can find them
		$process_env = getenv();

		// Add our secrets to ensure they're available
		foreach ( $this->secret_names as $name ) {
			if ( isset( $this->secret_values[ $name ] ) ) {
				$process_env[ $name ] = $this->secret_values[ $name ];
			}
		}

		return $process_env;
	}

	/**
	 * Redact secret values from text.
	 *
	 * @param string $text Text that may contain secrets.
	 * @return string Text with secrets replaced by [REDACTED] markers.
	 */
	public function redact( string $text ): string {
		foreach ( $this->secret_values as $name => $value ) {
			if ( strlen( $value ) > 3 ) { // Don't redact very short values
				// Redact the plain value
				$text = str_replace( $value, '[REDACTED:' . $name . ']', $text );
				// Also redact URL-encoded version
				$text = str_replace( urlencode( $value ), '[REDACTED:' . $name . ']', $text );
				// Also redact base64-encoded version
				$text = str_replace( base64_encode( $value ), '[REDACTED:' . $name . ']', $text );
			}
		}

		return $text;
	}

	/**
	 * Check if a variable name is a registered secret.
	 *
	 * @param string $name Variable name to check.
	 * @return bool True if this is a secret.
	 */
	public function is_secret( string $name ): bool {
		$this->ensure_initialized();
		return in_array( $name, $this->secret_names, true );
	}

	/**
	 * Get count of tracked secrets (for testing/debugging).
	 *
	 * @return int Number of secrets being tracked
	 */
	public function get_secret_count(): int {
		return count( $this->secret_names );
	}

	/**
	 * Clear all state (useful for testing).
	 */
	public function reset(): void {
		$this->env_vars      = [];
		$this->secret_names  = [];
		$this->secret_values = [];
		$this->initialized   = false;
	}

	/**
	 * Ensure the manager has been initialized.
	 *
	 * @throws RuntimeException If not initialized.
	 */
	private function ensure_initialized(): void {
		if ( ! $this->initialized ) {
			throw new RuntimeException( 'EnvironmentManager must be initialized before use' );
		}
	}

	/**
	 * Throw a helpful error for missing secrets.
	 *
	 * @param array<string> $missing Names of missing secrets.
	 * @throws RuntimeException Always.
	 */
	private function throw_missing_secrets_error( array $missing ): void {
		$message = "Missing required secrets:\n";
		foreach ( $missing as $name ) {
			$message .= "  • {$name}\n";
		}
		$message .= "\nHow to fix:\n";
		$message .= "  1. Set via command line:\n";
		foreach ( $missing as $name ) {
			$message .= "     --env {$name}=your-value\n";
		}
		$message .= "  2. Or use an env file:\n";
		$message .= "     --env_file .env.secrets\n";
		$message .= "  3. Or export in your shell:\n";
		foreach ( $missing as $name ) {
			$message .= "     export {$name}='your-value'\n";
		}

		throw new RuntimeException( $message );
	}
}
