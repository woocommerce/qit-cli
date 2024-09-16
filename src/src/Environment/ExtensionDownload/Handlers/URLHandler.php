<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use lucatume\DI52\ContainerException;
use QIT_CLI\App;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;

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
	 * @throws ContainerException If an error occurs during dependency resolution.
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

			// Cache burst: Day of the year (0-365), hour of the day (0-23), and current 5-minute interval of the hour (0-11).
			// Effectively, this will cache-bust every 5 minutes.
			$cache_burst = gmdate( 'z-G-' ) . floor( (int) gmdate( 'i' ) / 5 );

			$cache_file = $this->make_cache_path( $cache_dir, $e->type, $e->slug, $e->version, $e->source, $cache_burst );

			// Cache hit?
			if ( file_exists( $cache_file ) ) {
				$output->writeln( "Using cached {$e->type} {$e->slug}." );

				$e->downloaded_source = $cache_file;

				continue;
			} else {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Cache miss on {$e->type} {$e->slug}." );
				}
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
