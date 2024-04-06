<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;

class URLHandler extends Handler {
	/**
	 * @inheritDoc
	 */
	public function populate_extension_versions( array $extensions ): void {
		// No-op.
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

			// URLs that ends with ".zip".
			if ( substr( $e->source, - 4 ) !== '.zip' ) {
				throw new \InvalidArgumentException( 'We currently only support .zip URLs' );
			}

			// As version is "undefined", cache burst is shorter: Hour of the day (0-24).
			$cache_burst = gmdate( 'G' );

			$cache_file = $this->make_cache_path( $cache_dir, $e->type, $e->slug, $e->version, $cache_burst );

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

			RequestBuilder::download_file( $e->source, $cache_file );
			$e->downloaded_source = $cache_file;
		}
	}
}
