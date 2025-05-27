<?php

namespace QIT_CLI\PreCommand\Download\Extensions;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Extension;
use QIT_CLI\PreCommand\Download\Extensions\ExtensionDownloader;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;

abstract class Handler {
	/** @var OutputInterface */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * Sets the version property of the given extensions.
	 *
	 * @param array<Extension> $extensions The extensions to get versions for.
	 *
	 * @return void
	 */
	abstract public function populate_extension_versions( array $extensions ): void;

	/**
	 * Downloads extensions if needed and sets the downloaded_source property.
	 *
	 * @param array<Extension> $extensions
	 * @param string $cache_dir
	 *
	 * @return void
	 */
	abstract public function maybe_download_extensions( array $extensions, string $cache_dir ): void;

	/**
	 * Creates a cache path for an extension.
	 *
	 * @param string $cache_dir The cache directory.
	 * @param string $type The type of the extension ('plugin' or 'theme').
	 * @param string $extension_identifier The extension slug.
	 * @param string $extension_version The extension version.
	 * @param string $extension_source The source of the extension (e.g., URL).
	 * @param string $cache_burst A cache burst string (defaults to week of the year).
	 * @param string $file_format The file format (default: 'zip').
	 *
	 * @return string The cache path.
	 */
	protected function make_cache_path( string $cache_dir, string $type, string $extension_identifier, string $extension_version, string $extension_source, string $cache_burst = '', string $file_format = 'zip' ): string {
		if ( empty( $cache_burst ) ) {
			if ( $extension_version !== 'undefined' ) {
				$cache_burst = gmdate( 'z' );
			} else {
				$cache_burst = gmdate( 'YmdH' );
			}
		}

		if ( ! in_array( $type, [ 'plugin', 'theme' ], true ) ) {
			throw new \InvalidArgumentException( sprintf( 'Invalid type "%s", should be "plugin" or "theme".', $type ) );
		}

		if ( strpos( normalize_path( $cache_dir ), Config::get_qit_dir() ) !== 0 ) {
			throw new \InvalidArgumentException( sprintf( 'Invalid cache dir "%s", expected to be inside of "%s"', normalize_path( $cache_dir ), Config::get_qit_dir() ) );
		}

		if ( ! ExtensionDownloader::is_valid_plugin_slug( $extension_identifier ) ) {
			throw new \InvalidArgumentException( sprintf( 'Invalid extension identifier "%s", should be a valid plugin slug.', $extension_identifier ) );
		}

		$source_hash = md5( $extension_source );

		$cache_path = "$cache_dir/$type/$extension_identifier-$source_hash-$extension_version-$cache_burst.$file_format";

		if ( ! file_exists( dirname( $cache_path ) ) ) {
			if ( ! mkdir( dirname( $cache_path ), 0755, true ) ) {
				throw new \RuntimeException( sprintf( 'Could not create cache directory "%s".', dirname( $cache_path ) ) );
			}
		}

		$last_accesses = App::make( Cache::class )->get( 'last_extension_cache_access' ) ?? [];

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