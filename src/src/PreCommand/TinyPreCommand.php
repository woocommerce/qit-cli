<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\PreCommand\Configuration\ConfigMerger;
use QIT_CLI\PreCommand\Configuration\ConfigurationResolver;
use QIT_CLI\PreCommand\Configuration\ResolvedConfiguration;
use QIT_CLI\PreCommand\Objects\SutInput;
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
			// Build CLI SUT to pass to resolver for proper precedence
			$sut_cli = $this->buildCliSut();

			// Pass CLI SUT parameters to resolver
			$this->cfg = $this->parser->resolve(
				$this->config_file,
				$sut_cli?->slug ?? null,
				$sut_cli?->type ?? null
			);
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
							$explicit['tunnel']      = true;
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

		return $cli_name;
	}

	public function get_environment_config( string $env = 'default' ): array {
		// Validate environment options at the point where they're actually processed
		$this->validateEnvironmentOptions();

		$defaults = $this->extractDefaults();
		$cli      = $this->extractExplicit();

		try {
			$json = $this->cfg()->get_environment( $env );
			// Debug: log what we got from environment
			file_put_contents( '/tmp/environment_config_debug.json', json_encode( [
				'env'  => $env,
				'json' => $json,
			], JSON_PRETTY_PRINT ) );
		} catch ( \RuntimeException $e ) {
			$json = [];
			// Debug: log the exception
			file_put_contents( '/tmp/environment_config_debug.json', json_encode( [
				'env'   => $env,
				'error' => $e->getMessage(),
				'json'  => [],
			], JSON_PRETTY_PRINT ) );
		}

		// Apply profile defaults for environment commands
		// env:up is an e2e command, so apply e2e profile defaults
		$profile_defaults = [];
		try {
			$profile_defaults = $this->cfg()->get_test_config( 'e2e', 'default' );
			// Debug: log profile defaults
			file_put_contents( '/tmp/profile_defaults_debug.json', json_encode( [
				'profile_defaults'  => $profile_defaults,
				'original_defaults' => $defaults,
			], JSON_PRETTY_PRINT ) );
		} catch ( \InvalidArgumentException $e ) {
			// No profile defaults available, use empty array
			file_put_contents( '/tmp/profile_defaults_debug.json', json_encode( [
				'error'             => $e->getMessage(),
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

	/**
	 * Build SUT configuration from CLI arguments.
	 *
	 * @return SutInput|null Returns null if no SUT specified via CLI
	 */
	private function buildCliSut(): ?SutInput {
		// 1. positional slug
		$slug = $this->input->hasArgument( 'sut' ) ? trim( (string) $this->input->getArgument( 'sut' ) ) : '';
		$zip  = $this->input->getOption( 'zip' );          // may be null|string

		if ( $slug === '' && $zip === null ) {
			return null; // user did not specify SUT via CLI
		}

		$sut           = new SutInput();
		$sut->from_cli = true;

		// slug
		if ( $slug !== '' ) {
			$sut->slug   = $slug;
			$sut->type   = 'plugin'; // default – may adjust later with --type
			$sut->source = [ 'type' => 'wporg' ];          // default source
		}

		// --zip flag beats wporg source
		if ( $zip !== null || $this->input->getParameterOption( '--zip' ) === null /*flag alone*/ ) {
			$target      = $zip ?? ( $sut->slug ?? 'sut' ) . '.zip';
			$sut->slug   = $sut->slug ?? pathinfo( $target, PATHINFO_FILENAME );
			$sut->type   = 'plugin';                      // still a plugin
			$sut->source = preg_match( '#^https?://#', $target )
				? [
					'type' => 'url',
					'url'  => $target,
				]
				: [
					'type' => 'local',
					'path' => realpath( $target ) ?: $target,
				];
		}

		// TODO: --sut-type|--theme etc.

		return $sut;
	}

	/**
	 * Get resolved SUT configuration with precedence handling.
	 *
	 * @return array<string,mixed>|null Returns null if no SUT configured
	 */
	public function getResolvedSut(): ?array {
		$sut_cli = $this->buildCliSut();

		// Check for SUT config at root level first, then test profile level
		$sut_config = $this->cfg()->sut ?? [];

		// Fall back to test profile if no root-level SUT
		if ( empty( $sut_config ) ) {
			$sut_config = $this->cfg()->get_test_config( 'e2e', 'default' )['sut'] ?? [];
		}

		// Apply precedence: CLI > config
		if ( $sut_cli ) {
			return $sut_cli->toArray();
		}

		return ! empty( $sut_config ) ? $sut_config : null;
	}

	/**
	 * Get SUT warning message if CLI overrides config.
	 *
	 * @return string|null Warning message or null if no conflict
	 */
	public function getSutWarning(): ?string {
		$sut_cli = $this->buildCliSut();

		// We need to get the original config SUT, not the resolved one
		// Parse config directly without CLI SUT parameters to get original config
		if ( ! $this->config_file || ! file_exists( $this->config_file ) ) {
			return null;
		}

		$original_config = json_decode( file_get_contents( $this->config_file ), true );
		if ( ! $original_config ) {
			return null;
		}

		$sut_config = $original_config['sut'] ?? [];

		if ( $sut_cli && ! empty( $sut_config ) && ( $sut_cli->slug !== ( $sut_config['slug'] ?? '' ) ) ) {
			return sprintf(
				'Using CLI slug "%s" instead of qit.json value "%s"',
				$sut_cli->slug,
				$sut_config['slug'] ?? 'unknown'
			);
		}

		return null;
	}

	/**
	 * Validate environment-related CLI options.
	 *
	 * @throws \InvalidArgumentException If validation fails
	 */
	public function validateEnvironmentOptions(): void {
		// Validate --environment option
		$envOpt = $this->input->getOption( 'environment' );
		if ( $envOpt !== null && ! preg_match( '/^[A-Za-z0-9_-]+$/', $envOpt ) ) {
			throw new \InvalidArgumentException(
				"--environment expects a name like 'production' or 'php82', "
				. "got '{$envOpt}'. Did you mean --env?"
			);
		}

		// Validate each --env value
		$envVars = $this->input->getOption( 'env' ) ?? [];
		foreach ( $envVars as $pair ) {
			if ( ! str_contains( $pair, '=' ) ) {
				throw new \InvalidArgumentException(
					"Invalid --env '{$pair}'. Expected KEY=VAL. "
					. "Did you mean --environment={$pair} ?"
				);
			}
		}
	}
}
