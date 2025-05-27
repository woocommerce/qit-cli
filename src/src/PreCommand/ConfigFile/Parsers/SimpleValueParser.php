<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class SimpleValueParser extends AbstractConfigParser {
	public function parse( $value, string $key ) {
		if ( ! is_scalar( $value ) ) {
			throw new \RuntimeException( "'$key' in qit.json must be a scalar." );
		}

		return $value;
	}
}
