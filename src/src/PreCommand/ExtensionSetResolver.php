<?php

namespace QIT_CLI\PreCommand;

use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\ManagerSync;
use QIT_CLI\Environment\Extension;
use QIT_CLI\WPORGExtensionsList;
use QIT_CLI\App;

class ExtensionSetResolver {
	/** @var ManagerSync $manager_sync */
	protected $manager_sync;

	/** @var Cache $cache */
	protected $cache;

	public function __construct( Cache $cache, ManagerSync $manager_sync ) {
		$this->cache        = $cache;
		$this->manager_sync = $manager_sync;
	}

	public function fetch_extension_sets_available(): void {
		$this->manager_sync->maybe_sync( true );
	}

	public function get_extension_set( string $set ): array {
		try {
			$sets = $this->cache->get_manager_sync_data( 'extension_sets' );
			file_put_contents( '/tmp/qit_debug.log', "get_extension_set('$set') cache contents: " . json_encode( $sets ) . "\n", FILE_APPEND );
			if ( ! isset( $sets[ $set ] ) ) {
				file_put_contents( '/tmp/qit_debug.log', "Set '$set' not found in cache\n", FILE_APPEND );

				return [];
			}
			file_put_contents( '/tmp/qit_debug.log', "Returning set '$set': " . json_encode( $sets[ $set ] ) . "\n", FILE_APPEND );

			return $sets[ $set ];
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit_debug.log', "Cache error for extension sets: " . $e->getMessage() . "\n", FILE_APPEND );

			return [];
		}
	}

	public function resolve( EnvInfo $env_info, array $options_to_env_info ): EnvInfo {
		file_put_contents( '/tmp/qit_debug.log', "Resolving extension set with options: " . json_encode( $options_to_env_info ) . "\n", FILE_APPEND );
		if ( empty( $options_to_env_info['overrides']['extension_set'] ) ) {
			file_put_contents( '/tmp/qit_debug.log', "No extension set provided\n", FILE_APPEND );

			return $env_info;
		}

		$set_name = $options_to_env_info['overrides']['extension_set'];
		file_put_contents( '/tmp/qit_debug.log', "Processing extension set: $set_name\n", FILE_APPEND );

		$extensions = $this->get_extension_set( $set_name );

		if ( empty( $extensions ) ) {
			file_put_contents( '/tmp/qit_debug.log', "No extensions found for set: $set_name\n", FILE_APPEND );

			return $env_info;
		}

		$existing_slugs = array_map(
			function ( $plugin ) {
				return is_object( $plugin ) ? $plugin->slug : $plugin;
			},
			$env_info->plugins
		);

		$wporg_extensions_list = App::make( WPORGExtensionsList::class );

		foreach ( $extensions as $extension ) {
			if ( ! in_array( $extension, $existing_slugs, true ) ) {
				$ext_obj                      = new Extension( $extension, 'plugin' );
				$ext_obj->added_automatically = 'Added via extension set';
				if ( $wporg_extensions_list->is_wporg_plugin( $extension ) ) {
					$ext_obj->from = 'wporg';
					try {
						$info             = $wporg_extensions_list->get_plugin_download_info( $extension );
						$ext_obj->source  = $info['url'];
						$ext_obj->version = $info['version'];
					} catch ( \Exception $e ) {
						file_put_contents( '/tmp/qit_debug.log', "Failed to fetch WP.org info for $extension: " . $e->getMessage() . "\n", FILE_APPEND );
					}
				} else {
					$ext_obj->populate_from(); // Fallback for non-wporg plugins
				}
				$env_info->plugins[] = $ext_obj;
				$existing_slugs[]    = $extension;
				file_put_contents( '/tmp/qit_debug.log', "Added extension: $extension with properties added_automatically='Added via extension set', from='" . ( $ext_obj->from ?? 'null' ) . "'\n", FILE_APPEND );
			}
		}

		return $env_info;
	}
}