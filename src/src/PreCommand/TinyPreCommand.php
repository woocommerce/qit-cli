<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\PreCommand\Configuration\ConfigMerger;
use QIT_CLI\PreCommand\Configuration\ConfigurationResolver;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use function QIT_CLI\is_option_explicitly_provided;

/**
 * Tiny PreCommand implementation with just the essentials.
 *
 * This replaces the entire pipeline system with a simple, lazy-loading approach.
 * Nothing is resolved until something actually asks for it.
 */
final class TinyPreCommand implements PreCommandAware {

	private InputInterface $input;
	private ?string $config_file;
	private ConfigurationResolver $parser;
	private ConfigMerger $merger;

	/**
	 * Lazy-memoised parse
	 *
	 * @var ResolvedConfiguration|null
	 */
	private ?ResolvedConfiguration $cfg = null;

	public function __construct(
		InputInterface $input,
		?string $config_file,
		ConfigurationResolver $parser,
		ConfigMerger $merger
	) {
		$this->input       = $input;
		$this->config_file = $config_file;
		$this->parser      = $parser;
		$this->merger      = $merger;
	}

	/**
	 * Lazy configuration parsing - only parse once when needed.
	 */
	private function cfg(): ResolvedConfiguration {
		if ( $this->cfg === null ) {
 		// Debug: trace configuration loading
 		file_put_contents( '/tmp/tiny_precommand_debug.json', json_encode( [
 			'config_file' => $this->config_file,
 			'file_exists' => $this->config_file ? file_exists( $this->config_file ) : false,
 		], JSON_PRETTY_PRINT ) );
			
			// Fix: ConfigurationResolver::resolve() requires 3 parameters
			$this->cfg = $this->parser->resolve( $this->config_file, null, null );
		}
		return $this->cfg;
	}

	/**
	 * Extract CLI defaults using the simplified approach.
	 * Only includes options that were NOT explicitly provided by the user.
	 *
	 * @return array<string, mixed>
	 */
	private function extractDefaults(): array {
		$defaults = [];

		// Get all options from input
		foreach ( $this->input->getOptions() as $name => $value ) {
			// Skip framework options
			if ( in_array( $name, [ 'help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction' ], true ) ) {
				continue;
			}

			// Skip explicitly provided options - they should be handled by extractExplicit()
			if ( $this->isExplicitlyProvided( $name ) ) {
				continue;
			}

			// Use automatic pluralization for array options
			$config_key              = $this->normalizeKey( $name, $value );
			$defaults[ $config_key ] = $value;
		}

		return $defaults;
	}

	/**
	 * Extract explicitly provided CLI options.
	 *
	 * @return array<string, mixed>
	 */
	private function extractExplicit(): array {
		$explicit = [];

		// Get all options from input
		foreach ( $this->input->getOptions() as $name => $value ) {
			// Skip framework options
			if ( in_array( $name, [ 'help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction' ], true ) ) {
				continue;
			}

			// Only include if explicitly provided (not just default value)
			if ( $this->isExplicitlyProvided( $name ) ) {
				$config_key = $this->normalizeKey( $name, $value );

				// Handle special cases
				if ( $name === 'phpstan_level' && $value !== null ) {
					$value = (int) $value;
				}

				// For boolean flags, treat explicitly provided null as true
				// For boolean flags with values, set the flag to true but preserve the value
				if ( $this->isBooleanFlag( $name ) ) {
					if ( $value === null ) {
						$value = true;
					} else {
						// For tunnel, we need both tunnel=true and tunnel_type=value
						if ( $name === 'tunnel' && $value !== null ) {
							$explicit['tunnel'] = true;
							$explicit['tunnel_type'] = $value;
							continue; // Skip the normal assignment below
						} else {
							$value = true; // Other boolean flags with values still become true
						}
					}
				}

				$explicit[ $config_key ] = $value;
			}
		}

		return $explicit;
	}

	/**
	 * Check if an option was explicitly provided by the user.
	 */
	private function isExplicitlyProvided( string $option_name ): bool {
		return is_option_explicitly_provided( $this->input, $option_name );
	}

	/**
	 * Check if an option is a boolean flag that should be treated as true when present.
	 */
	private function isBooleanFlag( string $option_name ): bool {
		$boolean_flags = [
			'object_cache',
			'tunnel',
			'json',
			'skip_activating_plugins',
			'skip_activating_themes',
		];

		return in_array( $option_name, $boolean_flags, true );
	}

	/**
	 * Normalize CLI option name to config key using automatic pluralization.
	 *
	 * @param mixed $value
	 */
	private function normalizeKey( string $cli_name, $value ): string {
		// For array values, pluralize if not already plural
		if ( is_array( $value ) ) {
			return str_ends_with( $cli_name, 's' ) ? $cli_name : $cli_name . 's';
		}

		// Special case mappings for consistency
		if ( $cli_name === 'env' ) {
			return 'env_vars';
		}

		return $cli_name;
	}

	public function get_environment_config( string $env = 'default' ): array {
		$defaults = $this->extractDefaults();
		$cli      = $this->extractExplicit();

		try {
			$json = $this->cfg()->get_environment( $env );
			// Debug: log what we got from environment
			file_put_contents( '/tmp/environment_config_debug.json', json_encode( [
				'env' => $env,
				'json' => $json,
			], JSON_PRETTY_PRINT ) );
		} catch ( \RuntimeException $e ) {
			$json = [];
			// Debug: log the exception
			file_put_contents( '/tmp/environment_config_debug.json', json_encode( [
				'env' => $env,
				'error' => $e->getMessage(),
				'json' => [],
			], JSON_PRETTY_PRINT ) );
		}

		// Apply profile defaults for environment commands
		// env:up is an e2e command, so apply e2e profile defaults
		$profile_defaults = [];
		try {
			$profile_defaults = $this->cfg()->get_test_config( 'e2e', 'default' );
			// Debug: log profile defaults
			file_put_contents( '/tmp/profile_defaults_debug.json', json_encode( [
				'profile_defaults' => $profile_defaults,
				'original_defaults' => $defaults,
			], JSON_PRETTY_PRINT ) );
		} catch ( \InvalidArgumentException $e ) {
			// No profile defaults available, use empty array
			file_put_contents( '/tmp/profile_defaults_debug.json', json_encode( [
				'error' => $e->getMessage(),
				'original_defaults' => $defaults,
			], JSON_PRETTY_PRINT ) );
		}

		// Merge in correct precedence order: CLI > Profile Defaults > Environment Config > Command Defaults
		// First merge command defaults with environment config (environment takes precedence over command defaults)
		$base = $this->merger->merge( [], $json, $defaults );
		// Then merge with profile defaults (profile takes precedence over environment)
		$base = $this->merger->merge( [], $profile_defaults, $base );
		// Finally merge with CLI (CLI takes precedence over everything)
		return $this->merger->merge( $cli, [], $base );
	}

	public function get_current_test_profile( string $test_type, string $profile = 'default' ): array {
		$defaults = $this->extractDefaults();
		$cli      = $this->extractExplicit();

		try {
			$json = $this->cfg()->get_test_config( $test_type, $profile );
		} catch ( \RuntimeException $e ) {
			$json = [];
		}

		return $this->merger->merge( $cli, $json, $defaults );
	}
}
