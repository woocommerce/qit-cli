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

	/**
	 * // phpcs:disable
	 *
	 * @param array<int|string, string|array{
	 *     source?: string,
	 *     slug?: string,
	 *     action?: string,
	 *     test_tags?: array<string>,
	 * }> $plugins_or_themes
	 * // phpcs:enable
	 * @param string                          $type One of Extension::$allowed_types.
	 * @param string                          $default_action One of Extension::ACTIONS.
	 *
	 * @return array<Extension>
	 * @throws \Exception If it couldn't parse the extensions.
	 * @throws \InvalidArgumentException If the extensions are invalid.
	 * @throws \LogicException If the type is invalid.
	 *
	 * @see Extension::$allowed_types
	 * @see Extension::ACTIONS
	 */
	public function parse_extensions( array $plugins_or_themes, string $type, string $default_action = Extension::ACTIONS['activate'] ): array {
		$parsed_extensions = [];

		if ( ! in_array( $type, Extension::$allowed_types, true ) ) {
			throw new \LogicException( sprintf( 'Invalid type "%s". Valid types are: %s', $type, implode( ', ', Extension::$allowed_types ) ) );
		}

		foreach ( $plugins_or_themes as $potential_slug => $extension ) {
			if ( is_string( $extension ) ) {
				/*
				 * Short-syntax like "qit-beaver:test:rc,foo-feature"
				 */
				$extension = $this->parse_string_extension( $extension, $default_action );
			} elseif ( is_array( $extension ) ) {
				/*
				 * Arrays comes from config files.
				 */
				$extension = $this->parse_array_extension( $extension, $potential_slug );
			}

			if ( ! isset( $extension['source'] ) && ! isset( $extension['slug'] ) ) {
				throw new \Exception( "Please provide a 'source' or 'slug' for the plugin." );
			}

			// Infer slug if not set.
			if ( ! isset( $extension['slug'] ) ) {
				try {
					if ( is_numeric( $extension['source'] ) ) {
						$extension['slug'] = $this->woo_extensions_list->get_woo_extension_slug_by_id( (int) $extension['source'] );
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

						if ( $this->output->isVerbose() ) {
							$this->output->writeln( sprintf( '<comment>Inferred slug "%s" from source "%s".</comment>', $extension['slug'], $extension['source'] ) );
						}

						// Validate it's found.
						$this->woo_extensions_list->get_woo_extension_id_by_slug( $extension['slug'] );
					} catch ( \Exception $e ) {
						throw new \Exception( "Could not find an extension with slug {$extension['slug']}. (Inferred from '{$extension['source']}')" );
					}
				}
			}

			// If "source" is empty, use slug as the source.
			if ( ! isset( $extension['source'] ) ) {
				// @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset
				$extension['source'] = $extension['slug'];
			}

			// Set default action if not provided.
			$extension['action'] = $extension['action'] ?? $default_action;

			// Ensure test_tags is set.
			// @phpstan-ignore-next-line.
			if ( empty( $extension['test_tags'] ) || ! is_array( $extension['test_tags'] ) ) {
				$extension['test_tags'] = [ 'default' ];
			}

			// @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset
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

			if ( ! in_array( $extension['action'], Extension::ACTIONS, true ) ) {
				throw new \InvalidArgumentException( sprintf( 'Invalid action "%s". Valid actions are: %s', $extension['action'], implode( ', ', Extension::ACTIONS ) ) );
			}

			// Sort by key.
			ksort( $extension, SORT_STRING );

			$extension_instance            = new Extension();
			$extension_instance->slug      = $extension['slug'];
			$extension_instance->source    = $extension['source'];
			$extension_instance->action    = $extension['action'];
			$extension_instance->test_tags = $extension['test_tags'];
			$extension_instance->type      = $type;

			// Check if this "slug" is already defined, if it is, override it.
			foreach ( $parsed_extensions as $k => $p ) {
				if ( $p->slug === $extension_instance->slug ) {
					$parsed_extensions[ $k ] = $extension_instance;
					$this->output->writeln( sprintf( '<comment>Overriding extension "%s".</comment>', $extension['slug'] ) );
					continue 2;
				}
			}

			$parsed_extensions[] = $extension_instance;
		}

		return $parsed_extensions;
	}

	/**
	 * @param string $extension
	 * @param string $default_action
	 *
	 * @return array{
	 *     source: string,
	 *     action: string,
	 *     test_tags: array<string>,
	 * }
	 */
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

			// Anything on the right of the action is the test_tags, if any.
			$test_tag_str = substr( $extension, $action_pos + strlen( $action_pattern ) + 1 );

			if ( ! empty( $test_tag_str ) ) {
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

		return $parsed_short_syntax;
	}

	/**
	 * @param array<mixed> $extension
	 * @param int|string   $potential_slug
	 *
	 * @return array{
	 *     action?: string,
	 *     slug?: string,
	 *     source?: string,
	 *     test_tags?: array<string>,
	 * } The parsed extension.
	 * @throws \Exception When the extension can't be found.
	 * @throws \InvalidArgumentException When couldn't parse or validate the extension that was defined in a config file.
	 */
	protected function parse_array_extension( array $extension, $potential_slug ): array {
		/*
		 * "$potential_slug" will usually hold the slug, example:
		 *
		 * plugins:
		 *  qit-beaver: (This is $potential_slug)
		 *      source: ~/qit-beaver
		 *      action: install
		 *
		 * We use this key, unless a slug is explicitly defined:
		 * plugins:
		 *  qit-beaver:
		 *      slug: qit-beaver (if this is set, we use this)
		 *      source: ~/qit-beaver
		 */
		if ( ! is_numeric( $potential_slug ) && ! isset( $extension['slug'] ) ) {
			$extension['slug'] = $potential_slug;
		}

		/*
		 * Validate only allowed keys are defined.
		 *
		 * This is useful to clearly inform the user about typos, such as "test_tag" instead of "test_tags".
		 */
		$allowed_keys = [
			'action',
			'slug',
			'source',
			'test_tags',
		];
		foreach ( $extension as $k => $v ) {
			if ( ! in_array( $k, $allowed_keys, true ) ) {
				throw new \InvalidArgumentException( sprintf( 'Invalid key "%s" in extension array. Expected keys: %s', $k, implode( ', ', $allowed_keys ) ) );
			}
		}

		// If user set a "source", make sure it's valid.
		if ( isset( $extension['source'] ) ) {
			// @phpstan-ignore-next-line
			if ( ! is_string( $extension['source'] ) ) {
				throw new \InvalidArgumentException( sprintf( 'Invalid source "%s". Source must be a string.', $extension['source'] ) );
			}

			if ( empty( $extension['source'] ) ) {
				throw new \InvalidArgumentException( 'If set, source cannot be empty.' );
			}
		}

		// If user set an "action", make sure it's valid.
		if ( isset( $extension['action'] ) ) {
			if ( ! in_array( $extension['action'], Extension::ACTIONS, true ) ) {
				throw new \InvalidArgumentException( sprintf( 'Invalid action "%s". Valid actions are: %s', $extension['action'], implode( ', ', Extension::ACTIONS ) ) );
			}
		}

		// If user set "test_tags", make sure it's valid.
		if ( isset( $extension['test_tags'] ) ) {
			// @phpstan-ignore-next-line
			if ( ! is_array( $extension['test_tags'] ) ) {
				$example              = $extension;
				$example['test_tags'] = [
					'example-foo',
					'example-bar',
				];
				throw new \InvalidArgumentException( sprintf( "\"test_tags\" must be an array. \n\nActual:\n%s \n\nExpected: \n%s.", json_encode( $extension, JSON_PRETTY_PRINT ), json_encode( $example, JSON_PRETTY_PRINT ) ) );
			}
		}

		if ( isset( $extension['slug'] ) ) {
			try {
				$this->woo_extensions_list->get_woo_extension_id_by_slug( $extension['slug'] );
			} catch ( \Exception $e ) {
				// Plugin not found, or no permission.
				throw new \Exception( sprintf( 'Plugin with slug "%s" not found.', $extension['slug'] ) );
			}
		}

		return $extension;
	}
}
