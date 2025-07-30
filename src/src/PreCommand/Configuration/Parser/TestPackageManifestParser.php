<?php

namespace QIT_CLI\PreCommand\Configuration\Parser;

use QIT_CLI\PreCommand\Objects\TestPackageManifest;

/**
 * Parser for test package manifest files
 */
class TestPackageManifestParser extends BaseJsonParser {
	protected function get_schema_type(): string {
		return 'test-package';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return TestPackageManifest
	 */
	public function parse( string $file_path ): TestPackageManifest {
		// Accept directory entries during parsing
		if ( is_dir( $file_path ) && file_exists( $file_path . '/manifest.json' ) ) {
			$file_path .= '/manifest.json';
		}

		$this->root_path = dirname( $file_path );

		// Load and validate JSON
		$config = $this->load_and_validate_json( $file_path );

		// Apply business logic
		$config = $this->apply_business_logic( $config );

		return new TestPackageManifest( $config );
	}

	/**
	 * @param array<string, mixed> $config
	 * @return array<string, mixed>
	 */
	protected function apply_business_logic( array $config ): array {
		// Normalize phase commands
		if ( isset( $config['test']['phases'] ) ) {
			foreach ( $config['test']['phases'] as $phase => &$commands ) {
				$config['test']['phases'][ $phase ] = $this->normalize_phase_commands( $commands );
			}
		}

		// Keep paths relative - they should be relative to the manifest file
		// No normalization needed for mu_plugins, test_results, or test_dir

		// Convert env to strings
		if ( isset( $config['envs'] ) ) {
			foreach ( $config['envs'] as $key => &$value ) {
				if ( is_bool( $value ) ) {
					$value = $value ? 'true' : 'false';
				} elseif ( is_numeric( $value ) ) {
					$value = (string) $value;
				}
			}
		}

		// Validate test_dir exists if specified (relative to manifest location)
		if ( isset( $config['test_dir'] ) ) {
			$test_dir = $this->resolve_path( $config['test_dir'] );
			if ( ! is_dir( $test_dir ) ) {
				throw new \RuntimeException( 'Test directory not found: ' . $config['test_dir'] );
			}
		}

		return $config;
	}

	/**
	 * Normalize phase commands
	 *
	 * @param mixed $commands
	 * @return array<int, string|array<string, mixed>>
	 */
	private function normalize_phase_commands( $commands ): array {
		if ( ! is_array( $commands ) ) {
			return [];
		}

		$normalized = [];

		foreach ( $commands as $command ) {
			if ( is_string( $command ) ) {
				$normalized[] = $command;
			} elseif ( is_array( $command ) && isset( $command['command'] ) ) {
				// Add safety check for command string
				if ( ! is_string( $command['command'] ) ) {
					throw new \RuntimeException( 'Command must be a string, got: ' . gettype( $command['command'] ) );
				}

				// Check if command references a file
				if ( strpos( $command['command'], './' ) === 0 ) {
					$file_path = $this->resolve_path( $command['command'] );
					if ( ! file_exists( $file_path ) ) {
						throw new \RuntimeException( "Phase script not found: {$command['command']}" );
					}
				}
				$normalized[] = $command;
			}
		}

		return $normalized;
	}

	/**
	 * Resolve a path relative to the manifest file location
	 * This is only used for validation, not for modifying the config
	 */
	private function resolve_path( string $path ): string {
		if ( strpos( $path, './' ) === 0 ) {
			return $this->root_path . '/' . substr( $path, 2 );
		}

		return $path;
	}
}
