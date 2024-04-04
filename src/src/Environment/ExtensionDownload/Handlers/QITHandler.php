<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use function QIT_CLI\get_manager_url;

class QITHandler extends Handler {
	public function populate_extension_versions( array $extensions ): void {
		$output                 = App::make( Output::class );
		$extensions_to_download = array_filter( $extensions, function ( Extension $v ) {
			return ! file_exists( $v->path );
		} );

		$extensions_to_download = implode( ',', array_map( function ( $v ) {
			return $v->extension_identifier;
		}, $extensions_to_download ) );

		if ( empty( $extensions_to_download ) ) {
			return;
		}

		$start    = microtime( true );
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'extensions' => $extensions_to_download,
			] )
			->request();
		if ( $output->isVerbose() ) {
			$output->writeln( sprintf( 'Fetched versions for %d extensions from QIT in %f seconds.', count( $extensions ), microtime( true ) - $start ) );
		}

		/**
		 * @param array<string, array{
		 *     slug: string,
		 *     url: string,
		 *     version: string
		 * }> $download_urls Array where the key is the 'Requested slug' and each value is an
		 *                  array with 'slug' (that matches the actual slug), 'url', and 'version'.
		 *
		 * Eg, difference between requested slug and actual slug:
		 * Requested slug: --plugins product-bundles
		 * Actual slug: woocommerce-product-bundles
		 */
		$download_urls = json_decode( $response, true );

		if ( ! is_array( $download_urls ) || empty( $download_urls['urls'] ) || ! is_array( $download_urls['urls'] ) ) {
			throw new \RuntimeException( 'No download URLs received.' );
		}

		$download_urls = $download_urls['urls'];

		// Validate that all extensions we asked are here and are in the format we expect.
		foreach ( $extensions as $e ) {
			if ( ! array_key_exists( $e->extension_identifier, $download_urls ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $e->extension_identifier );
			}

			if ( empty( $download_urls[ $e->extension_identifier ]['url'] ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $e->extension_identifier );
			}

			if ( empty( $download_urls[ $e->extension_identifier ]['version'] ) ) {
				throw new \RuntimeException( 'No version found for ' . $e->extension_identifier );
			}

			if ( empty( $download_urls[ $e->extension_identifier ]['slug'] ) ) {
				throw new \RuntimeException( 'No slug found for ' . $e->extension_identifier );
			}
		}

		foreach ( $extensions as $e ) {
			// Eg: "product-bundles".
			$requested_slug = $e->extension_identifier;

			// Eg: "woocommerce-product-bundles".
			$actual_slug = $download_urls[ $e->extension_identifier ]['slug'];

			$e->extension_identifier = $actual_slug;
			$e->version              = $download_urls[ $requested_slug ]['version'];
			$e->download_url         = $download_urls[ $requested_slug ]['url'];
		}
	}

	/**
	 * @param array<\QIT_CLI\Environment\Extension> $extensions
	 * @param string           $cache_dir
	 *
	 * @throws \RuntimeException If an error occurs during downloading or file handling.
	 */
	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		$output = App::make( Output::class );

		foreach ( $extensions as $e ) {
			if ( ! empty( $e->path ) ) {
				// Extension already handled (possibly by a custom handler).
				continue;
			}

			$cache_file = $this->make_cache_path( $cache_dir, $e->type, $e->extension_identifier, $e->version );

			// Cache hit?
			if ( file_exists( $cache_file ) ) {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Using cached {$e->type} {$e->extension_identifier}." );
				}
				$e->path = $cache_file;

				continue;
			} else {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Cache miss on {$e->type} {$e->extension_identifier}." );
				}
			}

			if ( empty( $e->download_url ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $e->extension_identifier );
			}

			RequestBuilder::download_file( $e->download_url, $cache_file );
			$e->path = $cache_file;
		}
	}
}
