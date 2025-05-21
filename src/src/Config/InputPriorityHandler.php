<?php

namespace QIT_CLI\Config;

use Symfony\Component\Console\Input\InputInterface;
use function QIT_CLI\is_option_explicitly_provided;

class InputPriorityHandler {
	public function merge_inputs( array $cli_params, array $config_values, array $command_defaults ): array {
		// Merge with precedence: CLI params > config values > command defaults
		$merged = array_merge( $command_defaults, $config_values, $cli_params );

		// Remove null values to ensure defaults are respected
		return array_filter( $merged, function ( $value ) {
			return ! is_null( $value );
		} );
	}

	public function get_config_from_input( InputInterface $input, array $config_values, array $command_defaults ): array {
		$cli_params = [];
		foreach ( $input->getOptions() as $key => $value ) {
			// Only include explicitly provided options using is_option_explicitly_provided
			if ( is_option_explicitly_provided( $input, $key ) ) {
				$cli_params[ $key ] = $value;
			}
		}

		return $this->merge_inputs( $cli_params, $config_values, $command_defaults );
	}
}