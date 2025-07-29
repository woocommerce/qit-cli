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
			// Fix: ConfigurationResolver::resolve() requires 3 parameters
			$this->cfg = $this->parser->resolve( $this->config_file, null, null );
		}
		return $this->cfg;
	}

	/**
	 * Extract CLI defaults using the simplified approach.
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
		// Handle QIT_SELF_TEST=precommand early return
		if ( getenv( 'QIT_SELF_TEST' ) === 'precommand' ) {
			$this->handleEarlyReturn();
		}

		$defaults = $this->extractDefaults();
		$cli      = $this->extractExplicit();

		try {
			$json = $this->cfg()->get_environment( $env );
		} catch ( \RuntimeException $e ) {
			$json = [];
		}

		return $this->merger->merge( $cli, $json, $defaults );
	}

	public function get_current_test_profile( string $test_type, string $profile = 'default' ): array {
		// Handle QIT_SELF_TEST=precommand early return
		if ( getenv( 'QIT_SELF_TEST' ) === 'precommand' ) {
			$this->handleEarlyReturn();
		}

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
	 * Handle QIT_SELF_TEST=precommand early return.
	 */
	private function handleEarlyReturn(): void {
		// Create a simple data structure for testing
		$data = [
			'resolved_config' => $this->cfg(),
			'env_config'      => $this->extractExplicit(),
			'test_config'     => $this->extractExplicit(),
		];

		// Throw PrecommandEarlyReturn exception with JSON data
		throw new PrecommandEarlyReturn( json_encode( $data, JSON_PRETTY_PRINT ) );
	}
}
