<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\ExtensionDownload\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;

class URLHandler extends Handler {
	/**
	 * @inheritDoc
	 */
	public function populate_extension_versions( array $extensions ): void {
		// No-op.
	}

	public function assign_handler_to_extension( string $extesion_input, Extension $extension ): void {
		// URLs that ends with ".zip".
		if ( substr( $extesion_input, - 4 ) !== '.zip' ) {
			throw new \InvalidArgumentException( 'We currently only support .zip URLs' );
		}

		$extension->download_url         = $extesion_input;
		$extension->extension_identifier = md5( $extesion_input );
	}

	/**
	 * @param array<Extension> $extensions
	 * @param string           $cache_dir
	 *
	 * @throws \RuntimeException If an error occurs during downloading or file handling.
	 */
	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		$output = App::make( Output::class );

		foreach ( $extensions as $e ) {
			// As version is "undefined", cache burst is shorter: Hour of the day (0-24).
			$cache_burst = gmdate( 'G' );

			$cache_file = $this->make_cache_path( $cache_dir, $e->type, $e->extension_identifier, $e->version, $cache_burst );

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

			RequestBuilder::download_file( $e->download_url, $cache_file );
			$e->path = $cache_file;
		}
	}
}
