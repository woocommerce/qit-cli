<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\ExtensionDownload\Extension;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use function QIT_CLI\normalize_path;

abstract class Handler {
	/**
	 * Populates the "version" property of the given extensions.
	 *
	 * This is used for cache bursting.
	 *
	 * @param array<Extension> $extensions The extensions to get versions for.
	 *
	 * @return void
	 */
	abstract public function populate_extension_versions( array $extensions ): void;

	abstract public function maybe_download_extensions( array $extensions, string $cache_dir ): void;

	/**
	 * When assigning a handler to a given extension, we call this callback to be used if needed.
	 */
	public function assign_handler_to_extension( string $extension_input, Extension $extension ): void {
		// No-op by default.
	}

	/**
	 * @param string $cache_dir The cache directory.
	 * @param string $type The type of the extension to make a path for.
	 * @param string $extension_identifier The extension identifier.
	 * @param string $extension_version The extension version.
	 * @param string $cache_burst A cache burst string, defaults to the week of the year.
	 * @param string $file_format The file format.
	 *
	 * @return string
	 * @throws \lucatume\DI52\ContainerException
	 */
	protected function make_cache_path( string $cache_dir, string $type, string $extension_identifier, string $extension_version, string $cache_burst = '', string $file_format = 'zip' ): string {
		if ( empty( $cache_burst ) ) {
			// Get the number of the day, from 1 to 365 - basically means the cache is busted every day or so.
			$cache_burst = gmdate( 'z' );
		}

		// Make sure $type is as expected.
		if ( ! in_array( $type, [ 'plugin', 'theme' ] ) ) {
			throw new \InvalidArgumentException( sprintf( 'Invalid type "%s", should be "plugin" or "theme".', $type ) );
		}

		// Make sure $cache_dir is as expected (should be inside qit config dir).
		if ( strpos( normalize_path( $cache_dir ), Config::get_qit_dir() ) !== 0 ) {
			throw new \InvalidArgumentException( sprintf( 'Invalid cache dir "%s", expected to be inside of "%s"', normalize_path( $cache_dir ), Config::get_qit_dir() ) );
		}

		// Make sure $extension-identifier is a valid slug, as we will use it as the file name.
		if ( ! ExtensionDownloader::is_valid_plugin_slug( $extension_identifier ) ) {
			throw new \InvalidArgumentException( sprintf( 'Invalid extension identifier "%s", should be a valid plugin slug.', $extension_identifier ) );
		}

		$cache_path = "$cache_dir/$type/$extension_identifier-$extension_version-$cache_burst.$file_format";

		if ( ! file_exists( dirname( $cache_path ) ) ) {
			if ( ! mkdir( dirname( $cache_path ), 0755, true ) ) {
				throw new \RuntimeException( sprintf( 'Could not create cache directory "%s".', dirname( $cache_path ) ) );
			}
		}

		// Keep track of how often the cache is accessed to delete old entries.
		$last_accesses = App::make( Cache::class )->get( 'last_extension_cache_access' ) ?? [];

		// Cleanup files that haven't been accessed in over 7 days.
		foreach ( $last_accesses as $k => $v ) {
			if ( $v['access'] < time() - WEEK_IN_SECONDS ) {
				if ( file_exists( $v['path'] ) && strpos( normalize_path( $v['path'] ), Config::get_qit_dir() ) === 0 ) {
					unlink( $v['path'] );
				}
				unset( $last_accesses[ $k ] );
			}
		}

		$last_accesses[ $extension_identifier ] = [
			'path'   => $cache_path,
			'access' => time(),
		];

		App::make( Cache::class )->set( 'last_extension_cache_access', $last_accesses, MONTH_IN_SECONDS );

		return $cache_path;
	}
}
