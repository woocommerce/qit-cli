<?php

namespace QIT_CLI\PreCommand\Parsers;

class SimpleValueParser extends AbstractConfigParser {
	public function parse( $value, array $context = [] ): string {
		if ( ! is_string( $value ) ) {
			throw new \RuntimeException( 'Value must be a string.' );
		}

		$key = $context['key'] ?? '';
		if ( $key === 'type' && ! in_array( $value, [ 'plugin', 'theme', 'website' ] ) ) {
			throw new \RuntimeException( 'Invalid type. Must be plugin, theme, or website.' );
		}

		return $value;
	}
}