<?php

namespace QIT_CLI\PreCommand\Configuration\Parser;

/**
 * Parser for test package manifest files
 */
class TestPackageManifestParser extends BaseJsonParser {
	protected function get_schema_type(): string {
		return 'test-package';
	}

	protected function apply_business_logic( array $config ): array {
		// Normalize lifecycle commands
		if ( isset( $config['lifecycle'] ) ) {
			foreach ( $config['lifecycle'] as $phase => &$phase_config ) {
				foreach ( [ 'setup', 'teardown', 'run' ] as $hook ) {
					if ( isset( $phase_config[ $hook ] ) ) {
						$phase_config[ $hook ] = $this->normalize_lifecycle_commands( $phase_config[ $hook ] );
					}
				}
			}
		}

		// Keep paths relative - they should be relative to the manifest file
		// No normalization needed for mu_plugins, test_results, or test_dir

		// Convert env_vars to strings
		if ( isset( $config['env_vars'] ) ) {
			foreach ( $config['env_vars'] as $key => &$value ) {
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
	 * Normalize lifecycle commands
	 */
	private function normalize_lifecycle_commands( $commands ): array {
		if ( ! is_array( $commands ) ) {
			return [];
		}

		$normalized = [];

		foreach ( $commands as $command ) {
			if ( is_string( $command ) ) {
				$normalized[] = $command;
			} elseif ( is_array( $command ) && isset( $command['command'] ) ) {
				// Check if command references a file
				if ( strpos( $command['command'], './' ) === 0 ) {
					$file_path = $this->resolve_path( $command['command'] );
					if ( ! file_exists( $file_path ) ) {
						throw new \RuntimeException( "Lifecycle script not found: {$command['command']}" );
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
