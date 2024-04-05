<?php

namespace QIT_CLI\Environment;

use QIT_CLI\WooExtensionsList;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;

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

		foreach ( $plugins_or_themes as $key => $extension ) {
			if ( is_string( $extension ) ) {
				$extension = $this->parse_string_extension( $extension, $default_action );
			} elseif ( is_array( $extension ) && ! isset( $extension['slug'] ) ) {
				if ( ! is_numeric( $key ) ) {
					$extension['slug'] = $key;
				}
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
					// Source is not a slug of a ID. Try to infer it.
					try {
						/*
						 * If the source is a URL, we can try to infer the slug from the file that is being downloaded, eg:
						 *
						 * https://github.com/foo/bar/releases/qit-beaver.zip (inferred slug is "qit-beaver")
						 *
						 * If the source is a local file path, we infer from the basename of the file without the extension, eg:
						 * /path/to/qit-beaver.zip (inferred slug is "qit-beaver")
						 *
						 * If it's a directory, it's the basename of the dir, eg:
						 * /path/to/qit-beaver (inferred slug is "qit-beaver")
						 */
						$extension['slug'] = pathinfo( normalize_path( $extension['source'] ), PATHINFO_FILENAME );

						// Validate it's found.
						$this->woo_extensions_list->get_woo_extension_id_by_slug( $extension['slug'] );
					} catch ( \Exception $e ) {
						throw new \Exception( "Please provide a valid 'slug' for the plugin with source '{$extension['source']}'." );
					}
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

		// Default parsed structure.
		$parsed_short_syntax = [
			'source'      => '',
			'action'    => $default_action,
			'test_tags' => [],
		];

		// Known actions.
		$actions     = [ 'install', 'bootstrap', 'test' ];
		$actionFound = false;

		foreach ( $actions as $action ) {
			$actionPattern = ":$action";
			$actionPos     = strpos( $extension, $actionPattern );

			// Check if action is found and is either at the end or followed by another ':'.
			if ( $actionPos !== false && ( strlen( $extension ) == $actionPos + strlen( $actionPattern ) || $extension[ $actionPos + strlen( $actionPattern ) ] == ':' ) ) {
				$actionFound                   = true;
				$parsed_short_syntax['source']   = substr( $extension, 0, $actionPos );
				$parsed_short_syntax['action'] = $action;

				// Extract and process 'test_tags' if any.
				$testTagsStr = substr( $extension, $actionPos + strlen( $actionPattern ) + 1 );
				if ( ! empty( $testTagsStr ) ) {
					$parsed_short_syntax['test_tags'] = array_filter( array_map( 'trim', explode( ',', $testTagsStr ) ), 'strlen' );
				}

				break;
			}
		}

		// If no action is found, the entire string is considered as 'slug'.
		if ( ! $actionFound ) {
			$parsed_short_syntax['source'] = $extension;
		}

		return $parsed_short_syntax;
	}

}
