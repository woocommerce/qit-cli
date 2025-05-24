<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

class GroupParser extends AbstractConfigParser {
	private TestParser $test_parser;

	public function __construct( TestParser $test_parser ) {
		$this->test_parser = $test_parser;
	}

	public function parse( $value, array $context = [] ): array {
		if ( ! is_array( $value ) ) {
			throw new \RuntimeException( 'Groups must be an array.' );
		}

		foreach ( $value as $group_name => $test_refs ) {
			if ( ! is_string( $group_name ) ) {
				throw new \RuntimeException( 'Group name must be a string.' );
			}
			if ( ! is_array( $test_refs ) ) {
				throw new \RuntimeException( "Test references for group '$group_name' must be an array." );
			}
			if ( empty( $test_refs ) ) {
				throw new \RuntimeException( "Test references for group '$group_name' cannot be empty." );
			}
			$seen_refs = [];
			foreach ( $test_refs as $test_type => $profiles ) {
				if ( ! is_string( $test_type ) ) {
					throw new \RuntimeException( "Test type in group '$group_name' must be a string." );
				}
				if ( ! is_array( $profiles ) ) {
					throw new \RuntimeException( "Profiles for test type '$test_type' in group '$group_name' must be an array." );
				}
				foreach ( $profiles as $profile ) {
					if ( ! is_string( $profile ) ) {
						throw new \RuntimeException( "Profile in group '$group_name' for test type '$test_type' must be a string." );
					}
					$ref_key = "$test_type.$profile";
					if ( in_array( $ref_key, $seen_refs ) ) {
						throw new \RuntimeException( "Duplicate test reference '$ref_key' in group '$group_name'." );
					}
					$seen_refs[] = $ref_key;
					try {
						$this->test_parser->get( $test_type, $profile );
					} catch ( \RuntimeException $e ) {
						throw new \RuntimeException( "Test profile '$profile' for type '$test_type' in group '$group_name' not found in tests configuration." );
					}
				}
			}
		}

		return $value;
	}
}