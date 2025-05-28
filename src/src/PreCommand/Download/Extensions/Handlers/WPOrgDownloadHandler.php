<?php

namespace QIT_CLI\PreCommand\Download\Extensions\Handlers;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WPORGExtensionsList;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\normalize_path;

class WPOrgDownloadHandler extends Handler {
	/** @var WPORGExtensionsList */
	protected $wporg_extensions_list;

	/** @var Cache */
	protected $cache;

	public function __construct( OutputInterface $output, WPORGExtensionsList $wporg_extensions_list, Cache $cache ) {
		parent::__construct( $output );
		$this->wporg_extensions_list = $wporg_extensions_list;
		$this->cache                 = $cache;
	}

	/**
	 * Fetches the latest version from WP.org API.
	 *
	 * @param string $slug The extension slug.
	 * @param string $type 'plugin' or 'theme'.
	 *
	 * @return string|null The version number or null if not found.
	 */
	protected function get_wporg_version( string $slug, string $type ): ?string {
		$cache_key      = "wporg_{$type}_version_{$slug}";
		$cached_version = $this->cache->get( $cache_key );

		if ( $cached_version ) {
			return $cached_version;
		}

		try {
			$api_url = $type === 'plugin' ?
				sprintf( 'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=%s', urlencode( $slug ) ) :
				sprintf( 'https://api.wordpress.org/themes/info/1.2/?action=theme_information&request[slug]=%s', urlencode( $slug ) );

			$response = ( new RequestBuilder( $api_url ) )
				->with_method( 'GET' )
				->with_expected_status_codes( [ 200 ] )
				->request();

			$data = json_decode( $response, true );
			if ( is_array( $data ) && isset( $data['version'] ) ) {
				$this->cache->set( $cache_key, $data['version'], HOUR_IN_SECONDS );

				return $data['version'];
			}
		} catch ( \Exception $e ) {
			$this->output->writeln( "<comment>Failed to fetch version for {$slug} ({$type}): {$e->getMessage()}</comment>" );
		}

		return null;
	}

	/**
	 * Extracts the version from a cached zip file.
	 *
	 * @param string $zip_path Path to the zip file.
	 * @param string $slug The extension slug.
	 * @param string $type 'plugin' or 'theme'.
	 *
	 * @return string|null The version number or null if not found.
	 */
	protected function get_cached_version( string $zip_path, string $slug, string $type ): ?string {
		if ( ! file_exists( $zip_path ) ) {
			return null;
		}

		$zip = new \ZipArchive();
		if ( $zip->open( $zip_path ) !== true ) {
			return null;
		}

		$version = null;
		if ( $type === 'plugin' ) {
			for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
				$filename = $zip->getNameIndex( $i );
				if ( preg_match( "#^$slug/[^/]+\.php$#", $filename ) ) {
					$contents = $zip->getFromIndex( $i );
					if ( $contents && preg_match( '#Plugin Name:#i', $contents ) ) {
						if ( preg_match( '#Version:\s*([^\r\n]+)#i', $contents, $matches ) ) {
							$version = trim( $matches[1] );
							break;
						}
					}
				}
			}
		} elseif ( $type === 'theme' ) {
			$style_css = $zip->getFromName( "$slug/style.css" );
			if ( $style_css && preg_match( '#Version:\s*([^\r\n]+)#i', $style_css, $matches ) ) {
				$version = trim( $matches[1] );
			}
		}

		$zip->close();

		return $version;
	}

	public function populate_extension_versions( array $extensions ): void {
		$output                 = App::make( Output::class );
		$extensions_to_download = array_filter(
			$extensions,
			static function ( Extension $ext ) {
				return ! file_exists( $ext->downloaded_source ?? '' );
			}
		);

		if ( empty( $extensions_to_download ) ) {
			return;
		}

		$start = microtime( true );

		foreach ( $extensions_to_download as $ext ) {
			$version = $this->get_wporg_version( $ext->slug, $ext->type );
			if ( ! $version ) {
				throw new \RuntimeException( "Could not determine version for '{$ext->slug}' ({$ext->type}) from WordPress.org." );
			}

			$cache_file = $this->make_cache_path(
				$cache_dir ?? normalize_path( Config::get_qit_dir() . 'cache' ),
				$ext->type,
				$ext->slug,
				$version,
				"https://wordpress.org/{$ext->type}s/{$ext->slug}"
			);

			if ( file_exists( $cache_file ) ) {
				$cached_version = $this->get_cached_version( $cache_file, $ext->slug, $ext->type );
				if ( $cached_version === $version ) {
					$ext->downloaded_source = $cache_file;
					if ( $output->isVeryVerbose() ) {
						$output->writeln( "Using cached {$ext->type} {$ext->slug} (version {$version})." );
					}
					continue;
				}
			}

			try {
				if ( $ext->type === 'plugin' && $this->wporg_extensions_list->is_wporg_plugin( $ext->slug ) ) {
					$info = $this->wporg_extensions_list->get_plugin_download_info( $ext->slug );
				} elseif ( $ext->type === 'theme' && $this->wporg_extensions_list->is_wporg_theme( $ext->slug ) ) {
					$info = $this->wporg_extensions_list->get_theme_download_info( $ext->slug );
				} else {
					throw new \RuntimeException( "Extension '{$ext->slug}' ({$ext->type}) not found in WordPress.org." );
				}
				$ext->source  = $info['url'];
				$ext->version = $info['version'];
			} catch ( \Exception $e ) {
				throw new \RuntimeException( "1 Failed to fetch WordPress.org info for '{$ext->slug}' ({$ext->type}): " . $e->getMessage() );
			}
		}

		if ( $output->isVerbose() ) {
			$output->writeln( sprintf(
				'Fetched versions for %d WordPress.org extensions in %f seconds.',
				count( $extensions_to_download ),
				microtime( true ) - $start
			) );
		}
	}

	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		$output = App::make( Output::class );

		foreach ( $extensions as $ext ) {
			if ( ! empty( $ext->downloaded_source ) ) {
				continue;
			}

			$cache_file = $this->make_cache_path(
				$cache_dir,
				$ext->type,
				$ext->slug,
				$ext->version,
				$ext->source
			);

			if ( file_exists( $cache_file ) ) {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Using cached {$ext->type} {$ext->slug}." );
				}
				$ext->downloaded_source = $cache_file;
				continue;
			} else {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Cache miss on {$ext->type} {$ext->slug}." );
				}
			}

			if ( empty( $ext->source ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $ext->slug );
			}

			RequestBuilder::download_file( $ext->source, $cache_file );

			try {
				App::make( Zipper::class )->validate_zip( $cache_file );
			} catch ( \Exception $exception ) {
				unlink( $cache_file );
				throw new \RuntimeException(
					sprintf( 'Could not download zip file from URL %s.', $ext->source )
				);
			}

			$ext->downloaded_source = $cache_file;
		}
	}
}