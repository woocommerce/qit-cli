<?php

namespace QIT_CLI\PreCommand\Configuration;

use Opis\JsonSchema\{Validator, ValidationResult, Errors\ErrorFormatter};

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

		// Normalize paths in mu_plugins
		if ( isset( $config['mu_plugins'] ) ) {
			foreach ( $config['mu_plugins'] as &$plugin ) {
				$plugin = $this->normalizePath( $plugin );
			}
		}

		// Normalize paths in test_results
		if ( isset( $config['test_results'] ) ) {
			foreach ( $config['test_results'] as $format => &$path ) {
				$path = $this->normalizePath( $path );
			}
		}

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

		// Validate test_dir exists if specified
		if ( isset( $config['test_dir'] ) ) {
			$testDir = $this->normalizePath( $config['test_dir'] );
			if ( ! is_dir( $testDir ) ) {
				throw new \RuntimeException( "Test directory not found: $testDir" );
			}
		}

		return $config;
	}

	private function normalizeLifecycleCommands( $commands ): array {
		if ( ! is_array( $commands ) ) {
			return [];
		}

		$normalized = [];

		foreach ( $commands as $command ) {
			if ( is_string( $command ) ) {
				$normalized[] = $command;
			} elseif ( is_array( $command ) && isset( $command['command'] ) ) {
				if ( strpos( $command['command'], './' ) === 0 ) {
					$filePath = $this->normalizePath( $command['command'] );
					if ( ! file_exists( $filePath ) ) {
						throw new \RuntimeException( "Lifecycle script not found: {$command['command']}" );
					}
				}
				$normalized[] = $command;
			}
		}

		return $normalized;
	}
}