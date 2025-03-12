<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use function QIT_CLI\get_manager_url;

/**
 * This handler queries the QIT Manager to retrieve download URLs for
 * WordPress.org or WooCommerce.com extensions.
 */
class QITHandler extends Handler {
	/**
	 * Fills in each Extension object’s version/source by requesting
	 * download info from the Manager.
	 *
	 * @param Extension[] $extensions
	 *
	 * @throws \RuntimeException If there's an issue retrieving or parsing URLs.
	 */
	public function populate_extension_versions( array $extensions ): void {
		$output = App::make( Output::class );

		// Filter out any extensions we've already downloaded.
		$extensions_to_download = array_filter(
			$extensions,
			static function ( Extension $ext ) {
				return ! file_exists( $ext->downloaded_source );
			}
		);

		// If there's nothing to download, skip the request entirely.
		if ( empty( $extensions_to_download ) ) {
			return;
		}

		// Build a comma-separated list of slugs for "extensions"
		$slugs = array_map(
			static function ( Extension $ext ) {
				return $ext->slug;
			},
			$extensions_to_download
		);
		$extensions_string = implode( ',', $slugs );

		// Also build a slug=>type map so the Manager knows how to treat each slug.
		// For example: [ 'woocommerce' => 'plugin', 'storefront' => 'theme' ]
		$types_map = [];
		foreach ( $extensions_to_download as $ext ) {
			$types_map[ $ext->slug ] = $ext->type;
		}

		$start = microtime( true );

		// Send the old "extensions" plus our new "types" map.
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'sut_slug'   => App::getVar( 'QIT_SUT_SLUG', '' ),
				'extensions' => $extensions_string,
				'types'      => $types_map,
			] )
			->request();

		if ( $output->isVerbose() ) {
			$output->writeln( sprintf(
				'Fetched versions for %d extensions from QIT in %f seconds.',
				count( $extensions_to_download ),
				microtime( true ) - $start
			) );
		}

		/**
		 * The response should be JSON of the form:
		 * {
		 *   "urls": {
		 *     "some-slug": { "slug":"...", "url":"...", "version":"..." },
		 *     ...
		 *   }
		 * }
		 *
		 * or an error shape like:
		 * {
		 *   "code": "...",
		 *   "message": "..."
		 * }
		 */
		$download_urls = json_decode( $response, true );

		if ( ! is_array( $download_urls ) ) {
			throw new \RuntimeException( 'No valid JSON response from QIT Manager.' );
		}

		if ( isset( $download_urls['code'], $download_urls['message'] ) ) {
			throw new \RuntimeException( $download_urls['message'] );
		}

		if ( empty( $download_urls['urls'] ) || ! is_array( $download_urls['urls'] ) ) {
			throw new \RuntimeException(
				'No download URLs received from QIT Manager. ' .
				'Please ensure your extension is recognized or check if the Manager provided an error.'
			);
		}

		$urls = $download_urls['urls'];

		// Match them up with the original $extensions array.
		foreach ( $extensions as $ext ) {
			// If already downloaded (or not in $extensions_to_download), skip.
			if ( file_exists( $ext->downloaded_source ) ) {
				continue;
			}

			$original_slug = $ext->slug;
			if ( ! array_key_exists( $original_slug, $urls ) ) {
				throw new \RuntimeException(
					sprintf( 'No download URL found for extension "%s".', $original_slug )
				);
			}

			// Update the extension with the resolved slug, version, and direct download URL.
			$ext->slug    = $urls[ $original_slug ]['slug'];
			$ext->version = $urls[ $original_slug ]['version'];
			$ext->source  = $urls[ $original_slug ]['url'];
		}
	}

	/**
	 * Downloads each required Extension if not already cached.
	 *
	 * @param Extension[] $extensions
	 * @param string      $cache_dir
	 *
	 * @throws \RuntimeException on download error or invalid ZIP
	 */
	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		$output = App::make( Output::class );

		foreach ( $extensions as $ext ) {
			// Skip if already handled.
			if ( ! empty( $ext->downloaded_source ) ) {
				continue;
			}

			// Construct a unique path for caching based on type, slug, version, and source.
			$cache_file = $this->make_cache_path(
				$cache_dir,
				$ext->type,
				$ext->slug,
				$ext->version,
				$ext->source
			);

			// Check if we already have it cached locally.
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

			// Ensure we have a valid download URL from the populate_extension_versions step.
			if ( empty( $ext->source ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $ext->slug );
			}

			// Actually download the ZIP file to $cache_file
			RequestBuilder::download_file( $ext->source, $cache_file );

			// Validate the ZIP
			try {
				App::make( Zipper::class )->validate_zip( $cache_file );
			} catch ( \Exception $exception ) {
				// Clean up the partial/corrupt download
				unlink( $cache_file );
				throw new \RuntimeException(
					sprintf( 'Could not download zip file from URL %s.', $ext->source )
				);
			}

			// Mark it as downloaded
			$ext->downloaded_source = $cache_file;
		}
	}
}
