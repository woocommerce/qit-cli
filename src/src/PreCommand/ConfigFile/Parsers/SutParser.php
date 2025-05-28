<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class SutParser extends AbstractConfigParser {
	protected SourceParser $source_parser;

	public function __construct( SourceParser $source_parser ) {
		$this->source_parser = $source_parser;
	}

	public function parse( $value, array $context = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'sut must be an array.' );
		}

		if ( ! isset( $value['type'] ) || ! is_string( $value['type'] ) ) {
			throw new \RuntimeException( 'sut must contain a "type" key with a string value.' );
		}

		$valid_types = [ 'plugin', 'theme' ];
		if ( ! in_array( $value['type'], $valid_types, true ) ) {
			throw new \RuntimeException( "Invalid sut type '{$value['type']}'. Must be one of: " . implode( ', ', $valid_types ) );
		}

		if ( ! isset( $value['slug'] ) || ! is_string( $value['slug'] ) || empty( $value['slug'] ) ) {
			throw new \RuntimeException( 'sut must contain a non-empty "slug" string.' );
		}

		if ( ! isset( $value['source'] ) ) {
			throw new \RuntimeException( 'sut must contain a "source" key.' );
		}

		$value['source'] = $this->source_parser->parse( $value['source'] );

		return $value;
	}
}