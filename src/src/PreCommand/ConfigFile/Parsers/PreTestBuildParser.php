<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class PreTestBuildParser extends AbstractConfigParser {
	public function parse( $value, array $context = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'pre_test_build must be an array.' );
		}
		if ( ! isset( $value['command'] ) || ! is_string( $value['command'] ) ) {
			throw new \RuntimeException( 'pre_test_build must contain a "command" key with a string value.' );
		}
		if ( ! isset( $value['output'] ) || ! is_string( $value['output'] ) ) {
			throw new \RuntimeException( 'pre_test_build must contain an "output" key with a string value.' );
		}

		return $value;
	}
}
