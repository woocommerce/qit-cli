<?php

namespace QIT_CLI\Environment;

use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Output\OutputInterface;

class PluginsAndThemesParser {
	/** @var OutputInterface */
	protected $output;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	public function __construct( OutputInterface $output, WooExtensionsList $woo_extensions_list ) {
		$this->output              = $output;
		$this->woo_extensions_list = $woo_extensions_list;
	}

	public function parse_extensions( array $plugins_or_themes, string $default_action = 'install' ): array {
		$parsed_extensions = [];

		foreach ( $plugins_or_themes as $extension ) {
			if ( is_string( $extension ) ) {
				$extension = $this->parse_string_extension( $extension, $default_action );
			}

			if ( ! isset( $extension['source'] ) && ! isset( $extension['slug'] ) ) {
				throw new \Exception( "Please provide a 'source' or 'slug' for the plugin." );
			}

			// Infer slug if not set.
			if ( ! isset( $extension['slug'] ) ) {
				try {
					if ( is_numeric( $extension['source'] ) ) {
						$extension['slug'] = $this->woo_extensions_list->get_woo_extension_slug_by_id( $extension['source'] );
					} else {
						$this->woo_extensions_list->get_woo_extension_id_by_slug( $extension['source'] );
						$extension['slug'] = $extension['source'];
					}
				} catch ( \Exception $e ) {
					throw new \Exception( "Please provide a valid 'slug' for the plugin with source '{$extension['source']}'." );
				}
			}

			// Set 'source' to 'slug' if 'source' is not provided.
			if ( ! isset( $extension['source'] ) && isset( $extension['slug'] ) ) {
				$extension['source'] = $extension['slug'];
			}

			// Set default action if not provided.
			if ( ! isset( $extension['action'] ) ) {
				$extension['action'] = $default_action;
			}

			// Ensure test_tags is set.
			if ( empty( $extension['test_tags'] ) ) {
				$extension['test_tags'] = [ 'default' ];
			}

			if ( ! in_array( $extension['action'], [ 'install', 'bootstrap', 'test' ], true ) ) {
				throw new \InvalidArgumentException( sprintf( 'Invalid action "%s". Valid actions are: %s', $extension['action'], implode( ', ', [ 'install', 'bootstrap', 'test' ] ) ) );
			}

			// Sort by key.
			ksort( $extension, SORT_STRING );

			$parsed_extensions[] = $extension;
		}

		return $parsed_extensions;
	}

	protected function parse_string_extension( string $extension, string $default_action ): array {
		$json_array = json_decode( $extension, true );

		// Early bail: Long format, JSON.
		if ( ! is_null( $json_array ) ) {
			return $json_array;
		}

		// If it's not a JSON, then it must be a short syntax.
		$parsed_short_syntax           = [];
		$parts                         = explode( ':', $extension );
		$parsed_short_syntax['slug']   = $parts[0];
		$parsed_short_syntax['action'] = $parts[1] ?? $default_action;
		if ( isset( $parts[2] ) ) {
			// "rc,feature-foo" => ['rc', 'feature-foo'].
			// "rc, feature-foo" => ['rc', 'feature-foo'] (That's why we use array_map - trim).
			// "rc,feature-foo," => ['rc', 'feature-foo'] (That's why we use array_filter - strlen).
			$parsed_short_syntax['test_tags'] = array_filter( array_map( 'trim', explode( ',', $parts[2] ) ), 'strlen' );
		}

		return $parsed_short_syntax;
	}
}
