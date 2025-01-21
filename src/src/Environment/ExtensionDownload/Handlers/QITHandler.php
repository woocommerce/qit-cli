<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use function QIT_CLI\get_manager_url;

class QITHandler extends Handler {
	public function populate_extension_versions( array $extensions ): void {
		$output                 = App::make( Output::class );
		$extensions_to_download = array_filter( $extensions, function ( Extension $v ) {
			return ! file_exists( $v->downloaded_source );
		} );

		$extensions_to_download_slugs = implode( ',', array_map( function ( $v ) {
			return $v->slug;
		}, $extensions_to_download ) );

		if ( empty( $extensions_to_download_slugs ) ) {
			return;
		}

		$start    = microtime( true );
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'sut_slug'   => App::getVar( 'QIT_SUT_SLUG', '' ),
				'extensions' => $extensions_to_download_slugs,
			] )
			->request();
		if ( $output->isVerbose() ) {
			$output->writeln( sprintf(
				'Fetched versions for %d extensions from QIT in %f seconds.',
				count( $extensions ),
				microtime( true ) - $start
			) );
		}

		/**
		 * @param array<string, array{
		 *     slug: string,
		 *     url: string,
		 *     version: string
		 * }> $download_urls Array where the key is the 'Requested slug' and each value is an
		 *                  array with 'slug' (resolved slug), 'url', and 'version'.
		 *
		 * Eg, difference between requested slug and resolved slug:
		 * Requested slug: --plugins product-bundles
		 * Resolved slug: woocommerce-product-bundles
		 */
		$download_urls = json_decode( $response, true );

		// Handle errors from QIT Manager.
		if ( ! is_array( $download_urls ) ) {
			throw new \RuntimeException( 'No valid JSON response from QIT Manager.' );
		}

		// If the Manager returned an error code and message, just throw it.
		if ( isset( $download_urls['code'], $download_urls['message'] ) ) {
			throw new \RuntimeException( $download_urls['message'] );
		}

		// If no URLs returned, it's likely the Manager already returned a scenario message in code/message above.
		// If we got here without a code/message but still no urls, something unexpected happened.
		if ( empty( $download_urls['urls'] ) || ! is_array( $download_urls['urls'] ) ) {
			throw new \RuntimeException( 'No download URLs received from QIT Manager. Please ensure your extension is recognized or check if QIT Manager provided an error.' );
		}

		$urls = $download_urls['urls'];

		// Ensure all requested have a download URL.
		foreach ( $extensions as $e ) {
			if ( ! array_key_exists( $e->slug, $urls ) || empty( $urls[ $e->slug ]['url'] ) ) {
				// The Manager should have returned a scenario-based error before this point.
				// If we get here, it means the response is incomplete/unexpected.
				throw new \RuntimeException( sprintf( 'No download URL found for extension "%s".', $e->slug ) );
			}

			$e->slug    = $urls[ $e->slug ]['slug'];
			$e->version = $urls[ $e->slug ]['version'];
			$e->source  = $urls[ $e->slug ]['url'];
		}
	}

	/**
	 * @param array<\QIT_CLI\Environment\Extension> $extensions
	 * @param string                                $cache_dir
	 *
	 * @throws \RuntimeException If an error occurs during downloading or file handling.
	 */
	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		$output = App::make( Output::class );

		foreach ( $extensions as $e ) {
			if ( ! empty( $e->downloaded_source ) ) {
				// Extension already handled (possibly by a custom handler).
				continue;
			}

			$cache_file = $this->make_cache_path( $cache_dir, $e->type, $e->slug, $e->version, $e->source );

			// Cache hit?
			if ( file_exists( $cache_file ) ) {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Using cached {$e->type} {$e->slug}." );
				}
				$e->downloaded_source = $cache_file;

				continue;
			} else {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Cache miss on {$e->type} {$e->slug}." );
				}
			}

			if ( empty( $e->source ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $e->slug );
			}

			RequestBuilder::download_file( $e->source, $cache_file );

			try {
				App::make( Zipper::class )->validate_zip( $cache_file );
			} catch ( \Exception $exception ) {
				unlink( $cache_file );
				throw new \RuntimeException( sprintf( 'Could not download zip file from URL %s.', $e->source ) );
			}

			$e->downloaded_source = $cache_file;
		}
	}
}
