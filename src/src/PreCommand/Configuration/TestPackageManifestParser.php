<?php

namespace QIT_CLI\PreCommand\Configuration;

/**
 * Parser for test package manifest files
 */
class TestPackageManifestParser extends BaseJsonParser {
	protected function getSchemaType(): string {
		return 'test-package';
	}

	protected function applyBusinessLogic( array $config ): array {
		// Normalize lifecycle commands
		if ( isset( $config['lifecycle'] ) ) {
			foreach ( $config['lifecycle'] as $phase => &$phaseConfig ) {
				foreach ( [ 'setup', 'teardown', 'run' ] as $hook ) {
					if ( isset( $phaseConfig[ $hook ] ) ) {
						$phaseConfig[ $hook ] = $this->normalizeLifecycleCommands( $phaseConfig[ $hook ] );
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
			$testDir = $this->resolvePath( $config['test_dir'] );
			if ( ! is_dir( $testDir ) ) {
				throw new \RuntimeException( 'Test directory not found: ' . $config['test_dir'] );
			}
		}

		return $config;
	}

	/**
	 * Normalize lifecycle commands
	 */
	private function normalizeLifecycleCommands( $commands ): array {
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
					$filePath = $this->resolvePath( $command['command'] );
					if ( ! file_exists( $filePath ) ) {
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
	private function resolvePath( string $path ): string {
		if ( strpos( $path, './' ) === 0 ) {
			return $this->rootPath . '/' . substr( $path, 2 );
		}

		return $path;
	}
}
