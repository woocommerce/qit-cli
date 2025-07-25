<?php

namespace QIT_CLI\PreCommand\Configuration\Merger;

use Symfony\Component\Console\Input\InputInterface;

class CLIInputMerger {
	/**
	 * @param array<string, mixed> $cli_params
	 * @param array<string, mixed> $config_values
	 * @param array<string, mixed> $command_defaults
	 * @return array<string, mixed>
	 */
	public function merge_inputs( array $cli_params, array $config_values, array $command_defaults ): array {
		// Merge with precedence: CLI params > config values > command defaults
		$merged = array_merge( $command_defaults, $config_values, $cli_params );

		// Remove null values to ensure defaults are respected
		return array_filter( $merged, function ( $value ) {
			return ! is_null( $value );
		} );
	}

	/**
	 * @param InputInterface        $input
	 * @param array<string, mixed>  $config_values
	 * @param array<string, mixed>  $command_defaults
	 * @param array<string, string> $pluralizable_keys
	 * @return array<string, mixed>
	 */
	public function get_config_from_input( InputInterface $input, array $config_values, array $command_defaults, array $pluralizable_keys ): array {
		$cli_params = [];
		foreach ( $input->getOptions() as $key => $value ) {
			// Include options that are explicitly provided or have non-default values
			if ( $value !== $command_defaults[ $key ] && $value !== null ) {
				$config_key                = $pluralizable_keys[ $key ] ?? $key;
				$cli_params[ $config_key ] = $value;
			}
		}

		return $this->merge_inputs( $cli_params, $config_values, $command_defaults );
	}
}
