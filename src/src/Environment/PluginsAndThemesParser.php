<?php

namespace QIT_CLI\Environment;

use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;
use Symfony\Component\Console\Output\OutputInterface;

class PluginsAndThemesParser {
	/** @var OutputInterface */
	protected $output;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	/** @var WPORGExtensionsList */
	protected $wporg_extensions_list;

	public function __construct(
		OutputInterface $output,
		WooExtensionsList $woo_extensions_list,
		WPORGExtensionsList $wporg_extensions_list
	) {
		$this->output                = $output;
		$this->woo_extensions_list   = $woo_extensions_list;
		$this->wporg_extensions_list = $wporg_extensions_list;
	}

	/**
	 * @param array<int|string,string|array{source?:string,slug?:string,action?:string,test_tags?:array<string>,priority?: int}> $plugins_or_themes
	 * @param string                                                                                                             $type
	 * @param string                                                                                                             $default_action
	 *
	 * @return array<Extension>
	 */
	public function parse_extensions( array $plugins_or_themes, string $type, string $default_action = Extension::ACTIONS['activate'] ): array {
		$parsed_extensions = [];

		if ( ! in_array( $type, Extension::TYPES, true ) ) {
			throw new \LogicException( sprintf( 'Invalid type "%s". Valid types are: %s', $type, implode( ', ', Extension::TYPES ) ) );
		}

		foreach ( $plugins_or_themes as $potential_slug => $extension ) {
			$string_extension = null;
			if ( is_string( $extension ) ) {
				$string_extension = $extension;
				$extension        = $this->parse_string_extension( $extension, $default_action );
			} elseif ( is_array( $extension ) ) {
				$extension = $this->parse_array_extension( $extension, $potential_slug );
			} elseif ( $extension instanceof Extension ) { // @phpstan-ignore-line
				// Handle the case where we already have an Extension object.
				// Check if there's already a matching slug so we can override it,
				// just like we do for strings/arrays.
				$this->maybe_override_or_insert( $extension, $parsed_extensions );
			}

			if ( ! isset( $extension['source'] ) && ! isset( $extension['slug'] ) ) { // @phpstan-ignore-line
				throw new \Exception( "Please provide a 'source' or 'slug' for the plugin." );
			}

			// Infer slug if not set.
			if ( ! isset( $extension['slug'] ) ) {
				$extension['slug'] = $this->infer_slug_from_source( $extension['source'] ?? '' ); // @phpstan-ignore-line
			}

			// If "source" is empty, use slug as the source.
			if ( ! isset( $extension['source'] ) ) { // @phpstan-ignore-line
				$extension['source'] = $extension['slug'];
			}

			// Set default action if not provided.
			$extension['action'] = $extension['action'] ?? $default_action;

			// Ensure test_tags is set.
			if ( empty( $extension['test_tags'] ) || ! is_array( $extension['test_tags'] ) ) { // @phpstan-ignore-line
				$extension['test_tags'] = [ 'default' ];
			}

			foreach ( $extension['test_tags'] as $test_tag ) {
				$this->validate_test_tag( $test_tag );
			}

			if ( ! in_array( $extension['action'], Extension::ACTIONS, true ) ) {
				throw new \InvalidArgumentException(
					sprintf( 'Invalid action "%s". Valid actions are: %s', $extension['action'], implode( ', ', Extension::ACTIONS ) )
				);
			}

			ksort( $extension, SORT_STRING );

			$extension_instance            = new Extension();
			$extension_instance->slug      = $extension['slug'];
			$extension_instance->source    = $extension['source'];
			$extension_instance->action    = $extension['action'];
			$extension_instance->test_tags = $extension['test_tags'];
			$extension_instance->type      = $type;
			$extension_instance->priority  = $extension['priority'] ?? Extension::PRIORITY_MEDIUM; // @phpstan-ignore-line

			// No SUT overriding logic here. The caller must ensure correct action for SUT.

			// If slug already defined, override it with the newest definition.
			$this->maybe_override_or_insert( $extension_instance, $parsed_extensions );
		}

		// Add wccom_ids where possible.
		foreach ( $parsed_extensions as $k => $extension ) {
			if ( ! isset( $extension->wccom_id ) ) {
				try {
					$extension->wccom_id = $this->woo_extensions_list->get_woo_extension_id_by_slug( $extension->slug );
				} catch ( \Exception $e ) { // phpcs:ignore
					// No ID found, do nothing.
				}
			}
		}

		return $parsed_extensions;
	}

	/**
	 * Check if the extension is already in the list and override it if necessary.
	 *
	 * @param Extension        $extension
	 * @param array<Extension> $parsed_extensions
	 */
	private function maybe_override_or_insert( Extension $extension, array &$parsed_extensions ): void {
		foreach ( $parsed_extensions as $k => $already_parsed ) {
			if ( $extension->slug === $already_parsed->slug ) {
				if ( $extension->priority < $already_parsed->priority ) {
					if ( $this->output->isVeryVerbose() ) {
						$this->output->writeln( sprintf(
							'<comment>Skipping override of slug "%s" because priority %d < %d.</comment>',
							$extension->slug,
							$extension->priority,
							$already_parsed->priority
						) );
					}

					return;
				}

				// Override.
				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( sprintf(
						'<comment>Overriding extension "%s" with priority %d (old was %d).</comment>',
						$extension->slug,
						$extension->priority,
						$already_parsed->priority
					) );
				}
				$parsed_extensions[ $k ] = $extension;

				return;
			}
		}

		// No matching slug found, so append it.
		$parsed_extensions[] = $extension;
	}

	/**
	 * Infer slug from source if possible.
	 */
	/**
	 * Infers a slug from the given string "source".
	 * Flow:
	 *   1) If it's an HTTP(S) URL → parse the remote filename.
	 *   2) If it contains a slash → check if file exists locally and parse filename if so.
	 *   3) If numeric → treat as woo ID.
	 *   4) If "valid plugin slug" → check local woo list, then WP.org if not found.
	 *   5) Otherwise, fallback to pathinfo filename.
	 */
	protected function infer_slug_from_source( string $source ): string {
		// 1) If it starts with http:// or https:// → remote URL
		if ( preg_match( '/^https?:\/\//i', $source ) ) {
			$filename = pathinfo( \QIT_CLI\normalize_path( $source ), PATHINFO_FILENAME );
			if ( empty( $filename ) ) {
				throw new \Exception( "Could not infer slug from remote source '{$source}'." );
			}

			return $filename;
		} else {
			// Throw on any other protocol.
			if ( preg_match( '/^\w{2,}:\/\//', $source ) ) {
				throw new \Exception( "Only http(s) protocol is supported. Provided: '{$source}'" );
			}
		}

		// 2) If it contains slash/backslash, treat it as potential local file.
		if ( strpos( $source, '/' ) !== false || strpos( $source, '\\' ) !== false ) {
			if ( file_exists( $source ) ) {
				$filename = pathinfo( \QIT_CLI\normalize_path( $source ), PATHINFO_FILENAME );
				if ( empty( $filename ) ) {
					throw new \Exception( "Could not infer slug from local source '{$source}'." );
				}

				return $filename;
			}
			// If slash but not an existing file, we keep going.
		}

		// 3) If numeric => treat as a woo ID.
		if ( is_numeric( $source ) ) {
			$id = $this->woo_extensions_list->get_woo_extension_slug_by_id( (int) $source );

			return $id;
		}

		// 4) If "valid plugin slug," do woo check, then WP.org.
		if ( $this->is_valid_plugin_slug( $source ) ) {
			// Try local woo.
			try {
				$this->woo_extensions_list->get_woo_extension_id_by_slug( $source );

				return $source; // recognized woo extension.
			} catch ( \Exception $e ) {
				// Not known by woo => check WP.org.
				if ( $this->wporg_extensions_list->is_wporg_plugin( $source ) ) {
					return $source;  // recognized plugin slug.
				} elseif ( $this->wporg_extensions_list->is_wporg_theme( $source ) ) {
					return $source;  // recognized theme slug.
				}
			}
		}

		// 5) Fallback → parse final path segment from the string.
		$filename = pathinfo( \QIT_CLI\normalize_path( $source ), PATHINFO_FILENAME );
		if ( empty( $filename ) ) {
			throw new \Exception( "Could not infer slug from '{$source}'." );
		}

		return $filename;
	}

	protected function is_valid_plugin_slug( string $slug ): bool {
		return (bool) preg_match( '/^[a-z0-9_-]+$/i', $slug );
	}

	/**
	 * Validate a test tag.
	 */
	protected function validate_test_tag( string $test_tag ): void {
		if ( ! file_exists( $test_tag ) ) {
			if ( ! preg_match( '/^[a-z0-9-_]+$/i', $test_tag ) ) {
				// Has "/" but not "http".
				$looks_like_local_path = strpos( $test_tag, '/' ) !== false && strpos( $test_tag, 'http' ) === false;

				if ( $looks_like_local_path ) {
					$attempted_path = rtrim( getcwd(), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . ltrim( $test_tag, DIRECTORY_SEPARATOR );

					throw new \InvalidArgumentException( sprintf(
						'Invalid test tag "%s". If this is a file/directory, please make sure that it exists. ' .
						'Attempted path: %s. Please provide an existing file/directory or use a valid alphanumeric tag (with optional dashes/underscores).',
						$test_tag,
						$attempted_path
					) );
				} else {
					throw new \InvalidArgumentException(
						sprintf( 'Invalid test tag "%s". Test tags must be alphanumeric (dashes/underscores allowed), zip file, or directory.', $test_tag )
					);
				}
			}
		} else {
			// It's a file/directory. If file, must be zip.
			if ( is_file( $test_tag ) && pathinfo( $test_tag, PATHINFO_EXTENSION ) !== 'zip' ) {
				throw new \InvalidArgumentException(
					sprintf( 'Invalid test tag "%s". Must be alphanumeric, zip file, or directory.', $test_tag )
				);
			}
		}
	}

	/**
	 * @param string $extension
	 * @param string $default_action
	 *
	 * @return array{
	 *     source: string,
	 *     action: string,
	 *     test_tags: array<string>,
	 *     priority: int,
	 * }
	 */
	public function parse_string_extension( string $extension, string $default_action ): array {
		$json_array = json_decode( $extension, true );

		// Early bail: Long format, JSON.
		if ( ! is_null( $json_array ) && ! is_numeric( $json_array ) ) {
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
		 * - Action is optional and is one of the strings in Extension::ACTIONS (e.g., "install", "bootstrap", "test").
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

		foreach ( Extension::ACTIONS as $action ) {
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

			// Anything on the right of the action is the test_tags, if any, and the slug.
			$test_tag_str = substr( $extension, $action_pos + strlen( $action_pattern ) + 1 );

			if ( ! empty( $test_tag_str ) ) {
				// Starts with "base64", remove it and base64_decode the rest.
				if ( strpos( $test_tag_str, 'base64' ) === 0 ) {
					$test_tag_str = base64_decode( substr( $test_tag_str, 6 ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				}

				// Left of ":" is the test_tag_str, right is the slug.
				if ( strpos( $test_tag_str, ':' ) !== false ) {
					[ $test_tag_str, $parsed_short_syntax['slug'] ] = explode( ':', $test_tag_str, 2 );
				}
				// We explode the test tags by comma.
				// array_map(trim) will normalize "foo, bar" into "foo,bar"
				// array_filter will remove empty strings.
				$parsed_short_syntax['test_tags'] = array_filter( array_map( 'trim', explode( ',', $test_tag_str ) ), static function ( $item ) {
					return strlen( $item ) > 0;
				} );
			}

			break;
		}

		// If no action is found, the entire string is considered as 'slug'.
		if ( ! $action_found ) {
			$parsed_short_syntax['source'] = $extension;
		}

		$parsed_short_syntax['priority'] = Extension::PRIORITY_MEDIUM;

		return $parsed_short_syntax;
	}

	/**
	 * Parse an individual plugin/theme entry from the environment config.
	 *
	 * @param array{action?: string, slug?: string, source?: string, test_tags?: string[]} $extension
	 *
	 * @param int|string                                                                   $potential_slug The JSON config key for this plugin/theme.
	 *
	 * @return array{
	 *     action?: string,
	 *     slug: string,
	 *     source: string,
	 *     test_tags?: array<string>,
	 *     priority: int,
	 * } This will always have 'slug' and 'source' when returned.
	 *
	 * @throws \InvalidArgumentException If the extension array is invalid or
	 *                                   we could not figure out a valid slug/source.
	 */
	protected function parse_array_extension( array $extension, $potential_slug ): array {
		// Which keys are allowed in the extension’s array.
		$allowed_keys = [ 'action', 'slug', 'source', 'test_tags' ];
		foreach ( $extension as $k => $v ) {
			if ( ! in_array( $k, $allowed_keys, true ) ) {
				throw new \InvalidArgumentException(
					sprintf(
						'Invalid key "%s" in extension array. Allowed keys: %s',
						$k,
						implode( ', ', $allowed_keys )
					)
				);
			}
		}

		// ACTION: Validate if set.
		if ( isset( $extension['action'] ) ) {
			if ( ! in_array( $extension['action'], Extension::ACTIONS, true ) ) {
				throw new \InvalidArgumentException(
					sprintf(
						'Invalid action "%s". Valid actions are: %s',
						$extension['action'],
						implode( ', ', Extension::ACTIONS )
					)
				);
			}
		}

		// TEST_TAGS: Validate if set.
		if ( isset( $extension['test_tags'] ) ) {
			if ( ! is_array( $extension['test_tags'] ) ) { // @phpstan-ignore-line
				$example              = $extension;
				$example['test_tags'] = [ 'example-foo', 'example-bar' ];
				throw new \InvalidArgumentException(
					sprintf(
						"\"test_tags\" must be an array.\n\nActual:\n%s\n\nExpected:\n%s",
						json_encode( $extension, JSON_PRETTY_PRINT ),
						json_encode( $example, JSON_PRETTY_PRINT )
					)
				);
			}
		}

		/*
		 * SOURCE
		 *
		 * 1) Extract $source if set, else use the $potential_slug.
		 * 2) Validate that it’s a non-empty string.
		 * 3) Normalize JSON-escaped slashes.
		 */
		$source = array_key_exists( 'source', $extension ) ? $extension['source'] : (string) $potential_slug;

		if ( is_numeric( $source ) ) {
			$source = $this->woo_extensions_list->get_woo_extension_slug_by_id( (int) $source );
		}

		if ( ! is_string( $source ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Invalid source. Must be a string, got: %s',
					(string) $source
				)
			);
		}
		if ( $source === '' ) {
			throw new \InvalidArgumentException(
				'If "source" is set, it cannot be empty.'
			);
		}
		// Fix "https:\/\/" => "https://".
		$source              = str_replace( '\\/', '/', $source );
		$extension['source'] = $source;

		/*
		 * SLUG
		 *
		 * 1) If a slug is explicitly set, use it.
		 * 2) Else infer from numeric key (Woo.com ID), or if valid slug, or from $source.
		 */
		$slug = array_key_exists( 'slug', $extension ) ? $extension['slug'] : null;

		if ( $slug === null ) {
			if ( is_numeric( $potential_slug ) ) {
				$woo_id = (int) $potential_slug;
				$slug   = $this->woo_extensions_list->get_woo_extension_slug_by_id( $woo_id );
			} elseif ( $this->is_valid_plugin_slug( (string) $potential_slug ) ) {
				$slug = (string) $potential_slug;
			} else {
				// Fall back to inferring from $source.
				$slug = $this->infer_slug_from_source( $source );
			}
		}

		if ( ! is_string( $slug ) || $slug === '' ) {
			throw new \InvalidArgumentException(
				sprintf(
					"Could not determine a valid slug for '%s'. Please set 'slug' or a valid 'source'.",
					(string) $potential_slug
				)
			);
		}

		$extension['slug']     = $slug;
		$extension['priority'] = Extension::PRIORITY_MEDIUM;

		return $extension;
	}
}
