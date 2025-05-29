<?php

namespace QIT_CLI\PreCommand\Download\Extensions\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;

class UrlDownloadHandler extends Handler {
	public function __construct( OutputInterface $output ) {
		parent::__construct( $output );
	}

	public function populate_extension_versions( array $extensions ): void {
		// No-op: Version is not determined for remote zips
	}

	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		$output = App::make( Output::class );

		foreach ( $extensions as $ext ) {
			if ( ! empty( $ext->downloaded_source ) ) {
				continue;
			}

			if ( empty( $ext->source ) || ! is_string( $ext->source ) ) {
				throw new \RuntimeException( "Zip extension '{$ext->slug}' must have a non-empty 'source' URL." );
			}

			if ( substr( $ext->source, - 4 ) !== '.zip' ) {
				throw new \InvalidArgumentException( "Source URL for '{$ext->slug}' must end with .zip" );
			}

			$cache_burst = gmdate( 'z-G-' ) . floor( (int) gmdate( 'i' ) / 5 );
			$cache_file  = $this->make_cache_path( $cache_dir, $ext->type, $ext->slug, $ext->version ?? 'undefined', $ext->source, $cache_burst );

			if ( file_exists( $cache_file ) ) {
				$output->writeln( "Using cached {$ext->type} {$ext->slug}." );
				$ext->downloaded_source = $cache_file;
				continue;
			} else {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Cache miss on {$ext->type} {$ext->slug}." );
				}
			}

			try {
				RequestBuilder::download_file( $ext->source, $cache_file );
			} catch ( \Exception $e ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "UrlDownloadHandler: Download failed for '{$ext->slug}' from {$ext->source}: {$e->getMessage()}\n", FILE_APPEND );
				throw new \RuntimeException( "Failed to download {$ext->type} from URL: {$ext->source}" );
			}

			try {
				App::make( Zipper::class )->validate_zip( $cache_file );
			} catch ( \Exception $e ) {
				unlink( $cache_file );
				file_put_contents( '/tmp/qit/qit_debug.log', "UrlDownloadHandler: Invalid ZIP for '{$ext->slug}' from {$ext->source}: {$e->getMessage()}\n", FILE_APPEND );
				throw new \RuntimeException( "Invalid ZIP file downloaded from URL: {$ext->source}" );
			}

			$ext->downloaded_source = $cache_file;
		}
	}
}