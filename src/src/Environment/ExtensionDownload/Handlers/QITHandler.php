<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\ExtensionDownload\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use function QIT_CLI\get_manager_url;

class QITHandler extends Handler {
	public function maybe_download( Extension $extension, string $cache_dir, EnvInfo $env_info ): void {
		$output = App::make( Output::class );

		// Cache hit?
		if ( file_exists( "$cache_dir/{$extension->type}/{$extension->extension_identifier}.zip" ) ) {
			if ( $output->isVeryVerbose() ) {
				$output->writeln( "Using cached {$extension->type} {$extension->extension_identifier}." );
			}
			$extension->path = "$cache_dir/{$extension->type}/{$extension->extension_identifier}.zip";

			return;
		} else {
			if ( $output->isVeryVerbose() ) {
				$output->writeln( "Cache miss on {$extension->type} {$extension->extension_identifier}." );
			}
		}
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
			return $v->extension_identifier;
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
			if ( ! array_key_exists( $e->extension_identifier, $download_urls ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $e->extension_identifier );
			}

			if ( empty( $download_urls[ $e->extension_identifier ]['url'] ) ) {
				throw new \RuntimeException( 'No download URL found for ' . $e->extension_identifier );
			}

			if ( empty( $download_urls[ $e->extension_identifier ]['version'] ) ) {
				throw new \RuntimeException( 'No version found for ' . $e->extension_identifier );
			}
		}

		foreach ( $extensions as $e ) {
			RequestBuilder::download_file( $download_urls[ $e->extension_identifier ]['url'], "$cache_dir/{$e->type}/{$e->extension_identifier}.zip" );
		}
	}
}