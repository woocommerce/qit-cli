<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\ExtensionDownload\Extension;
use QIT_CLI\RequestBuilder;
use function QIT_CLI\get_manager_url;

class QITHandler extends Handler {
	public function maybe_download( Extension $extension, string $cache_dir, EnvInfo $env_info ): void {

	}

	/**
	 * @param array<Extension> $extensions
	 * @param string $cache_dir
	 *
	 * @throws \QIT_CLI\Exceptions\DoingAutocompleteException
	 * @throws \QIT_CLI\Exceptions\NetworkErrorException
	 */
	public function download_extensions( array $extensions, string $cache_dir ): void {
		$extensions_to_download = array_filter( $extensions, function ( Extension $v ) {
			return ! file_exists( $v->path );
		} );

		$extensions_to_download = implode( ',', array_map( function ( $v ) {
			return $v->extension;
		}, $extensions_to_download ) );

		if ( empty( $extensions_to_download ) ) {
			return;
		}

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'extensions' => $extensions_to_download,
			] )
			->request();

		/**
		 * @param $download_urls array{
		 *     array {
		 *         url: string,
		 *         version: string,
		 *     }
		 * }
		 */
		$download_urls = json_decode( $response, true );

		if ( empty( $download_urls ) || ! is_array( $download_urls ) ) {
			throw new \RuntimeException( 'No download URLs received.' );
		}

		// Validate that all extensions we asked are here and are in the format we expect.
		foreach ( $extensions as $e ) {
			if ( ! array_key_exists( $e->extension, $download_urls ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $e->extension );
			}

			if ( empty( $download_urls[ $e->extension ]['url'] ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $e->extension );
			}

			if ( empty( $download_urls[ $e->extension ]['version'] ) ) {
				throw new \RuntimeException( 'No version found for ' . $e->extension );
			}
		}

		foreach ( $extensions as $e ) {
			RequestBuilder::download_file( $download_urls[ $e->extension ]['url'], "$cache_dir/{$e->type}/{$e->extension}.zip" );
		}
	}
}