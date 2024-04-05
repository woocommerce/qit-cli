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

			// If "source" is empty  'source' to 'slug' if 'source' is not provided.
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

			foreach ( $extension['test_tags'] as $test_tag ) {
				if ( ! file_exists( $test_tag ) ) {
					if ( ! preg_match( '/^[a-z0-9-_]+$/i', $test_tag ) ) {
						throw new \InvalidArgumentException( sprintf( 'Invalid test tag "%s". Test tags must either be alphanumeric strings (dashes and underscores allowed), a local zip file, or a directory.', $test_tag ) );
					}
				} else {
					// File exists. If it's a file, it must be a zip one.
					if ( is_file( $test_tag ) ) {
						if ( pathinfo( $test_tag, PATHINFO_EXTENSION ) !== 'zip' ) {
							throw new \InvalidArgumentException( sprintf( 'Invalid test tag "%s". Test tags must either be alphanumeric strings (dashes and underscores allowed), a local zip file, or a directory.', $test_tag ) );
						}
					}
				}
			}

			if ( ! in_array( $extension['action'], Extension::$allowed_actions, true ) ) {
				throw new \InvalidArgumentException( sprintf( 'Invalid action "%s". Valid actions are: %s', $extension['action'], implode( ', ', Extension::$allowed_actions ) ) );
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

		/**
		 * Short Syntax Parsing:
		 *
		 * If the string isn't JSON, parse it as a "short syntax" string, which goes like this:
		 *
		 * {source}:{action}:{test_tags}
		 *
		 * - Source can be slugs, IDs, URLs or file paths.
		 * - Action is optional and is one of the strings in Extension::$allowed_actions (e.g., "install", "bootstrap", "test").
		 * - Test tags are optional and can be a comma-separated list of alphanumeric strings, or local paths.
		 *
		 * Parsing Logic:
		 *
		 * The parser searches for the "action:" part of the string.
		 * - If no action is found, the entire string is considered as the source. In this case, there are no test tags.
		 * - If an action is found, the left part is the source, the right part, if present, is the test tags, which we explode by comma.
		 */
		$parsed_short_syntax = [
			'source'    => '',
			'action'    => $default_action,
			'test_tags' => [],
		];

		$action_found = false;

		foreach ( Extension::$allowed_actions as $action ) {
			$action_pattern = ":$action";
			$action_pos     = strpos( $extension, $action_pattern );

			// Continue. Action not found.
			if ( $action_pos === false ) {
				continue;
			}

			$action_found = true;

			// Anything on the left of the action is the source.
			$parsed_short_syntax['source'] = substr( $extension, 0, $action_pos );
			$parsed_short_syntax['action'] = $action;

			// Anything on the right of the action is the test_tags, if any.
			$test_tag_str = substr( $extension, $action_pos + strlen( $action_pattern ) + 1 );

			if ( ! empty( $test_tag_str ) ) {
				// We explode the test tags by comma.
				// array_map(trim) will normalize "foo, bar" into "foo,bar"
				// array_filter will remove empty strings.
				$parsed_short_syntax['test_tags'] = array_filter( array_map( 'trim', explode( ',', $test_tag_str ) ), 'strlen' );
			}

			break;
		}

		// If no action is found, the entire string is considered as 'slug'.
		if ( ! $action_found ) {
			$parsed_short_syntax['source'] = $extension;
		}

		return $parsed_short_syntax;
	}
}
