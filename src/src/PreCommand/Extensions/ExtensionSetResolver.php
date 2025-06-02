<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\ManagerSync;
use QIT_CLI\respondent\PreCommand\Extension\ExtensionResolver;
use QIT_CLI\Config;
use function QIT_CLI\normalize_path;

class ExtensionSetResolver {
	protected $manager_sync;
	protected $cache;
	protected $extension_resolver;

	public function __construct( Cache $cache, ManagerSync $manager_sync, ExtensionResolver $extension_resolver ) {
		$this->cache              = $cache;
		$this->manager_sync       = $manager_sync;
		$this->extension_resolver = $extension_resolver;
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
			file_put_contents( '/tmp/qit_debug.log', 'Cache error for extension sets: ' . $e->getMessage() . "\n", FILE_APPEND );
			return [];
		}
	}

	public function resolve( EnvInfo $env_info, array $options_to_env_info ): EnvInfo {
		file_put_contents( '/tmp/qit_debug.log', 'ExtensionSetResolver: Resolving with options: ' . json_encode( $options_to_env_info ) . "\n", FILE_APPEND );
		if ( empty( $options_to_env_info['overrides']['extension_set'] ) ) {
			file_put_contents( '/tmp/qit_debug.log', "ExtensionSetResolver: No extension set provided\n", FILE_APPEND );
			return $env_info;
		}

		$set_name = $options_to_env_info['overrides']['extension_set'];
		file_put_contents( '/tmp/qit_debug.log', "ExtensionSetResolver: Processing extension set: $set_name\n", FILE_APPEND );

		$extensions = $this->get_extension_set( $set_name );

		if ( empty( $extensions ) ) {
			file_put_contents( '/tmp/qit_debug.log', "ExtensionSetResolver: No extensions found for set: $set_name\n", FILE_APPEND );
			return $env_info;
		}

		$existing_slugs = array_map(
			fn( $plugin ) => is_object( $plugin ) ? $plugin->slug : $plugin,
			$env_info->plugins
		);

		$new_plugins = [];
		foreach ( $extensions as $extension ) {
			if ( ! in_array( $extension, $existing_slugs, true ) ) {
				$ext_obj                      = new Extension( $extension, 'plugin' );
				$ext_obj->added_automatically = 'Added via extension set';
				$new_plugins[]                = $ext_obj;
				$existing_slugs[]             = $extension;
				file_put_contents( '/tmp/qit_debug.log', "ExtensionSetResolver: Added extension: $extension with properties added_automatically='Added via extension set'\n", FILE_APPEND );
			}
		}

		$cache_dir      = normalize_path( Config::get_qit_dir() . 'cache' );
		$all_extensions = array_merge( $env_info->plugins, $new_plugins, $env_info->themes );
		$resolved       = $this->extension_resolver->resolve( $all_extensions, $env_info, $cache_dir );

		$env_info->plugins        = $resolved->get_plugins();
		$env_info->themes         = $resolved->get_themes();
		$env_info->php_extensions = array_unique( array_merge( $env_info->php_extensions, $resolved->get_php_extensions() ) );

		return $env_info;
	}
}
