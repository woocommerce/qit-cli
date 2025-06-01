<?php

namespace QIT_CLI\PreCommand\ConfigFile\Parsers;

use QIT_CLI\App;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;

class SutParser extends AbstractConfigParser {
	protected SourceParser $source_parser;
	protected WooExtensionsList $woo_extension_list;
	protected WPORGExtensionsList $wporg_extension_list;

	public function __construct( SourceParser $source_parser, WooExtensionsList $woo_extension_list, WPORGExtensionsList $wporg_extension_list ) {
		$this->source_parser        = $source_parser;
		$this->woo_extension_list   = $woo_extension_list;
		$this->wporg_extension_list = $wporg_extension_list;
	}

	public function parse( $value, array $context = [] ): array {
		file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: Parsing SUT config: " . print_r( $value, true ) . "\n", FILE_APPEND );

		if ( ! is_array( $value ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT must be an array\n", FILE_APPEND );
			throw new \RuntimeException( 'SUT configuration must be an array.' );
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

		// If source is not provided, infer wporg or wccom
		if ( ! isset( $value['source'] ) || ! is_array( $value['source'] ) ) {
			$slug = $value['slug'];
			file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: No source provided for SUT '$slug', attempting to infer source\n", FILE_APPEND );

			// Check WPOrg
			try {
				if ( $value['type'] === 'plugin' && $this->wporg_extension_list->is_wporg_plugin( $slug ) ) {
					$value['source'] = [
						'type'    => 'wporg',
						'version' => 'stable',
					];
					file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: Inferred wporg source for SUT '$slug'\n", FILE_APPEND );
				} elseif ( $value['type'] === 'theme' && $this->wporg_extension_list->is_wporg_theme( $slug ) ) {
					$value['source'] = [
						'type'    => 'wporg',
						'version' => 'stable',
					];
					file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: Inferred wporg source for SUT '$slug'\n", FILE_APPEND );
				}
			} catch ( \Exception $e ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: Failed to check WPOrg for '$slug': " . $e->getMessage() . "\n", FILE_APPEND );
			}

			// Check WCCOM if not found in WPOrg
			if ( ! isset( $value['source'] ) ) {
				try {
					$this->woo_extension_list->get_woo_extension_id_by_slug( $slug );
					$value['source'] = [
						'type'    => 'wccom',
						'version' => 'stable',
					];
					file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: Inferred wccom source for SUT '$slug'\n", FILE_APPEND );
				} catch ( \UnexpectedValueException $e ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT '$slug' not found in WPOrg or WCCOM\n", FILE_APPEND );
					throw new \RuntimeException( "SUT '$slug' not found in WordPress.org or WooCommerce.com. Please specify a 'source' object." );
				}
			}
		}

		// Delegate source validation to SourceParser
		$value['source'] = $this->source_parser->parse( $value['source'], [
			'slug'    => $value['slug'],
			'context' => 'sut.source'
		] );

		file_put_contents( '/tmp/qit/qit_debug.log', "SutParser: SUT parsing completed\n", FILE_APPEND );

		return $value;
	}
}