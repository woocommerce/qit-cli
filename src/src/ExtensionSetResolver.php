<?php

namespace QIT_CLI;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Environment\PluginsAndThemesParser;

class ExtensionSetResolver {
	/** @var ManagerSync $manager_sync */
	protected $manager_sync;

	/** @var Cache $cache */
	protected $cache;

	/** @var PluginsAndThemesParser */
	protected $plugins_and_themes_parser;

	public function __construct( Cache $cache, ManagerSync $manager_sync, PluginsAndThemesParser $plugins_and_themes_parser ) {
		$this->cache = $cache;
		$this->manager_sync = $manager_sync;
		$this->plugins_and_themes_parser = $plugins_and_themes_parser;
	}

	/**
	 * Force re-sync to fetch WooExtensions list associated with the current Partner.
	 *
	 * @throws \Exception|\RuntimeException When could not retrieve list of WooCommerce extensions.
	 */
	public function fetch_extension_sets_availabl(): void {
		$this->manager_sync->maybe_sync( true );
	}

	/**
	 * @return array<string> Gets the Woo Extensions list that the current authenticated user has access to.
	 */
	public function get_extension_set( string $set ): array {
		try {
			$sets = $this->cache->get_manager_sync_data( 'extension_sets' );

			if ( ! isset( $sets[ $set ] ) ) {
				return [];
			}

			return $sets[ $set ];
		} catch ( \Exception $e ) {
			return [];
		}
	}

	/**
	* Update the env info by resolving any extension sets to extensions.
	* Looks for extension_set in options, resolves them to actual extensions,
	* and adds any non-duplicate extensions to the env info plugins list.
	*
	* @param EnvInfo $env_info The current environment info object.
	* @param array<string,array<string,mixed>> $options_to_env_info The parsed options containing possible extension sets.
	* @return EnvInfo The updated environment info object with resolved extensions
	*/
	public function resolve( EnvInfo $env_info, array $options_to_env_info ): EnvInfo {
		// Check if we have any extension sets to process.
		if ( empty( $options_to_env_info['overrides']['extension_set'] ) ) {
			return $env_info;
		}

		// Get the extension set name.
		$set_name = $options_to_env_info['overrides']['extension_set'];

		// Get the extensions for this set.
		$extensions = $this->get_extension_set( $set_name );

		// If no extensions found, return original env info.
		if ( empty( $extensions ) ) {
			return $env_info;
		}

		// Track existing plugin slugs to avoid duplicates.
		$existing_slugs = array_map(
			function( $plugin ) {
				return $plugin->slug;
			},
			$env_info->plugins
		);

		// Convert extensions to array format for parser.
		$extensions_to_parse = array_map(function($extension) {
			return ['slug' => $extension];
		}, $extensions);

		// Parse extensions using the parser.
		$parsed_extensions = $this->plugins_and_themes_parser->parse_extensions(
			$extensions_to_parse,
			Extension::TYPES['plugin'],
			Extension::ACTIONS['activate']
		);

		// Add each parsed extension that isn't already in the plugins list.
		foreach ( $parsed_extensions as $extension ) {
			if ( ! in_array( $extension->slug, $existing_slugs, true ) ) {
				$env_info->plugins[] = $extension;
			}
		}

		return $env_info;
	}
}
