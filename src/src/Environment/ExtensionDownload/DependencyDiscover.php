<?php

namespace QIT_CLI\Environment\ExtensionDownload;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\RequestBuilder;
use function QIT_CLI\get_manager_url;

/**
 * A simple helper class to discover plugin/theme dependencies before the main download step.
 *
 * Usage (in your UpEnvironmentCommand or wherever):
 *
 *   $env_info = // build or load initial EnvInfo
 *   App::make(DependencyDiscover::class)->discover_dependencies($env_info);
 *   // now $env_info->plugins/themes has newly discovered items
 */
class DependencyDiscover {
	/**
	 * Calls the manager’s /cli/download-urls endpoint with the slugs
	 * in $env_info->plugins/themes, merges newly discovered dependencies
	 * into $env_info->plugins or $env_info->themes.
	 *
	 * @param EnvInfo $env_info
	 *
	 * @return void
	 */
	public function discover_dependencies( EnvInfo $env_info ): void {
		// 1) Gather slugs & build a "slug => type" map from existing extensions
		$slugs     = [];
		$types_map = [];

		// Existing plugins
		foreach ( $env_info->plugins as $ext ) {
			$slugs[]                 = $ext->slug;
			$types_map[ $ext->slug ] = ! empty( $ext->type )
				? $ext->type
				: Extension::TYPES['plugin'];
		}

		// Existing themes
		foreach ( $env_info->themes as $ext ) {
			$slugs[]                 = $ext->slug;
			$types_map[ $ext->slug ] = ! empty( $ext->type )
				? $ext->type
				: Extension::TYPES['theme'];
		}

		// If no slugs, nothing to discover.
		if ( empty( $slugs ) ) {
			return;
		}

		// 2) Call the manager
		$slugs_string = implode( ',', $slugs );

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'extensions' => $slugs_string,
				'types'      => $types_map
			] )
			->request();

		$decoded = json_decode( $response, true );
		if ( ! is_array( $decoded ) || empty( $decoded['urls'] ) ) {
			// Manager returned nothing or invalid data, so bail out or throw:
			return; // or throw new \RuntimeException('No valid "urls" from manager.');
		}

		$urls = $decoded['urls']; // e.g. [ "astra-pro"=>["slug"=>"astra-pro","url"=>"...","type"=>"plugin"], ...]

		// 3) Determine which slugs are NEW (not in $slugs) and create Extension objects
		foreach ( $urls as $slug => $info ) {
			if ( in_array( $slug, $slugs, true ) ) {
				// Already known, skip
				continue;
			}

			// This is a new dependency:
			$new          = new Extension();
			$new->slug    = $info['slug'] ?? $slug;
			$new->source  = $info['url'] ?? '';
			$new->version = $info['version'] ?? 'undefined';

			// If manager provides a "type", use it; default to "plugin" if not.
			$new->type = ! empty( $info['type'] )
				? $info['type']
				: Extension::TYPES['plugin'];

			// Optional defaults for newly discovered items:
			$new->handler   = \QIT_CLI\Environment\ExtensionDownload\Handlers\QITHandler::class;
			$new->action    = Extension::ACTIONS['bootstrap'];
			$new->test_tags = [];
			$new->priority  = Extension::PRIORITY_LOW;
			$new->wccom_id  = null;

			// 4) Insert into the appropriate array
			if ( $new->type === Extension::TYPES['theme'] ) {
				$env_info->themes[] = $new;
			} else {
				$env_info->plugins[] = $new;
			}
		}
	}
}