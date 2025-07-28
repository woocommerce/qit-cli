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
	 * @param array<string,mixed> $cli_params CLI-provided values (highest priority)
	 * @param array<string,mixed> $config_values Values from qit.json (medium priority)  
	 * @param array<string,mixed> $command_defaults Command default values (lowest priority)
	 * 
	 * @return array<string,mixed> Merged configuration with null values filtered out
	 */
	public function merge( array $cli_params, array $config_values, array $command_defaults ): array {
		// Start with command defaults
		$result = $command_defaults;
		
		// Override with config values (but only non-null ones)
		foreach ( $config_values as $key => $value ) {
			if ( $value !== null ) {
				$result[ $key ] = $value;
			}
		}
		
		// Override with CLI params (but only non-null ones)
		foreach ( $cli_params as $key => $value ) {
			if ( $value !== null ) {
				$result[ $key ] = $value;
			}
		}
		
		// Filter out any remaining null values from defaults
		return array_filter( $result, static fn( $value ) => $value !== null );
	}
}