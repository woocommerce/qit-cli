<?php

namespace QIT_CLI\Environment;

use RuntimeException;

/**
 * Manages secret validation, injection, and redaction for test packages.
 *
 * This class ensures that:
 * 1. Required secrets are present before test execution
 * 2. Secrets are safely injected into commands
 * 3. Secret values are redacted from all output
 * 4. QIT never stores or transmits raw secret values
 *
 * Only redacts actual values of declared secrets, not pattern-based heuristics.
 */
class SecretManager {
	/**
	 * @var array<string> Names of required secrets
	 */
	private array $secret_names = [];

	/**
	 * @var array<string> Values to redact from output
	 */
	private array $secret_values = [];

	/**
	 * Validate that all required secrets are present in the environment.
	 *
	 * @param array<string> $required_secrets List of required secret environment variable names.
	 * @throws RuntimeException If any required secret is missing.
	 */
	public function validate( array $required_secrets ): void {
		$missing = [];

		foreach ( $required_secrets as $name ) {
			$value = getenv( $name );
			if ( $value === false || $value === '' ) {
				$missing[] = $name;
			} else {
				// Track for redaction
				$this->secret_names[]  = $name;
				$this->secret_values[] = $value;
			}
		}

		if ( ! empty( $missing ) ) {
			$message = "Missing required secrets:\n";
			foreach ( $missing as $name ) {
				$message .= "  • {$name}\n";
			}
			$message .= "\nHow to fix:\n";
			$message .= "  1. Set environment variables:\n";
			foreach ( $missing as $name ) {
				$message .= "     export {$name}='your-value'\n";
			}
			$message .= "  2. Or use an env file: --env-file=.env.test\n";
			$message .= "  3. See test package documentation for obtaining credentials\n";

			throw new RuntimeException( $message );
		}
	}

	/**
	 * Redact secret values from text.
	 *
	 * Only redacts actual values of secrets that were declared and validated,
	 * not pattern-based heuristics.
	 *
	 * @param string $text Text that may contain secrets.
	 * @return string Text with secrets replaced by [REDACTED] markers.
	 */
	public function redact( string $text ): string {
		// Only redact actual secret values that were declared by test packages
		foreach ( $this->secret_values as $index => $value ) {
			if ( strlen( $value ) > 3 ) { // Don't redact very short values to avoid false positives
				$secret_name = $this->secret_names[ $index ] ?? 'SECRET';
				$text        = str_replace( $value, '[REDACTED:' . $secret_name . ']', $text );
				// Also redact URL-encoded version
				$text = str_replace( urlencode( $value ), '[REDACTED:' . $secret_name . ']', $text );
				// Also redact base64-encoded version
				$text = str_replace( base64_encode( $value ), '[REDACTED:' . $secret_name . ']', $text );
			}
		}

		return $text;
	}

	/**
	 * Get environment variables for Docker --env flags (names only, not values).
	 *
	 * @return array<string> List of environment variable names to pass to Docker
	 */
	public function get_docker_env_flags(): array {
		return $this->secret_names;
	}

	/**
	 * Get environment variables as key-value pairs for process execution.
	 *
	 * @return array<string,string> Environment variables to merge with process env
	 */
	public function get_env_array(): array {
		$env = [];
		foreach ( $this->secret_names as $name ) {
			$value = getenv( $name );
			if ( $value !== false ) {
				$env[ $name ] = $value;
			}
		}
		return $env;
	}


	/**
	 * Add additional values to redact (e.g., from manifest envs).
	 *
	 * @param string $name Name of the secret (for reporting).
	 * @param string $value Value to redact.
	 */
	public function add_secret_value( string $name, string $value ): void {
		if ( ! in_array( $name, $this->secret_names, true ) ) {
			$this->secret_names[] = $name;
		}
		if ( ! in_array( $value, $this->secret_values, true ) ) {
			$this->secret_values[] = $value;
		}
	}

	/**
	 * Clear all tracked secrets (useful for testing).
	 */
	public function clear(): void {
		$this->secret_names  = [];
		$this->secret_values = [];
	}

	/**
	 * Get count of tracked secrets (useful for testing/debugging).
	 *
	 * @return int Number of secrets being tracked
	 */
	public function get_secret_count(): int {
		return count( $this->secret_names );
	}
}
