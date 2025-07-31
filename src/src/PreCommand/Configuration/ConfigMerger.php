<?php

namespace QIT_CLI\PreCommand\Configuration;

/**
 * ConfigMerger handles the precedence algorithm for merging configuration values.
 *
 * This class implements the "three-layer sandwich" approach:
 * CLI parameters > Config values > Command defaults
 *
 * It also handles null filtering to ensure defaults are properly respected.
 */
final class ConfigMerger {

	/**
	 * Merge configuration values with proper precedence.
	 *
	 * @param array<string,mixed> $cli_params CLI-provided values (highest priority).
	 * @param array<string,mixed> $config_values Values from qit.json (medium priority).
	 * @param array<string,mixed> $command_defaults Command default values (lowest priority).
	 *
	 * @return array<string,mixed> Merged configuration with null values filtered out
	 */
	public function merge( array $cli_params, array $config_values, array $command_defaults ): array {
		// Debug output to understand what's being passed
		$debug_info = [
			'cli_params'       => $cli_params,
			'config_values'    => $config_values,
			'command_defaults' => $command_defaults,
		];
		file_put_contents( '/tmp/config_merger_debug.json', json_encode( $debug_info, JSON_PRETTY_PRINT ) . "\n", FILE_APPEND );

		// List options that should be merged and deduplicated instead of replaced
		$list_options = [ 'plugins', 'themes', 'volumes', 'php_extensions' ];

		// Start with command defaults
		$result = $command_defaults;

		// Override with config values (but only non-null ones)
		foreach ( $config_values as $key => $value ) {
			if ( $value !== null ) {
				if ( in_array( $key, $list_options, true ) && is_array( $value ) && isset( $result[ $key ] ) && is_array( $result[ $key ] ) ) {
					// Merge and deduplicate arrays for list options - config values first, then defaults
					$result[ $key ] = array_values( array_unique( array_merge( $value, $result[ $key ] ) ) );
				} else {
					$result[ $key ] = $value;
				}
			}
		}

		// Override with CLI params (but only non-null ones)
		foreach ( $cli_params as $key => $value ) {
			if ( $value !== null ) {
				if ( in_array( $key, $list_options, true ) && is_array( $value ) && isset( $result[ $key ] ) && is_array( $result[ $key ] ) ) {
					// Merge and deduplicate arrays for list options - CLI values last (highest precedence)
					$result[ $key ] = array_values( array_unique( array_merge( $result[ $key ], $value ) ) );
				} else {
					$result[ $key ] = $value;
				}
			}
		}

		// Filter out any remaining null values from defaults
		return array_filter( $result, static fn( $value ) => $value !== null );
	}
}
