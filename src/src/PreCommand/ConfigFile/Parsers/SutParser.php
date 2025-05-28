<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class SutParser extends AbstractConfigParser {
	protected SourceParser $source_parser;

	public function __construct( SourceParser $source_parser ) {
		$this->source_parser = $source_parser;
	}

	public function parse( $value, array $context = [] ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: Parsing SUT config: " . print_r( $value, true ) . "\n", FILE_APPEND );

		if ( ! is_array( $value ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT must be an array\n", FILE_APPEND );
			throw new \RuntimeException( 'SUT must be an array.' );
		}

		if ( ! isset( $value['type'] ) || ! is_string( $value['type'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT missing type\n", FILE_APPEND );
			throw new \RuntimeException( 'SUT must contain a "type" key with a string value.' );
		}

		$valid_types = [ 'plugin', 'theme' ];
		if ( ! in_array( $value['type'], $valid_types, true ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: Invalid SUT type: {$value['type']}\n", FILE_APPEND );
			throw new \RuntimeException( "Invalid SUT type '{$value['type']}'. Must be one of: " . implode( ', ', $valid_types ) );
		}

		if ( ! isset( $value['slug'] ) || ! is_string( $value['slug'] ) || empty( $value['slug'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT missing or empty slug\n", FILE_APPEND );
			throw new \RuntimeException( 'SUT must contain a non-empty "slug" string.' );
		}

		if ( ! isset( $value['source_type'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT missing source_type\n", FILE_APPEND );
			throw new \RuntimeException( 'SUT must contain a "source_type" key.' );
		}

		// Delegate source validation to SourceParser
		$value = $this->source_parser->parse( $value );

		file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT parsing completed\n", FILE_APPEND );

		return $value;
	}
}