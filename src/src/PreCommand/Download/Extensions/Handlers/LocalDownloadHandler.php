<?php

namespace QIT_CLI\PreCommand\Download\Extensions\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;

class LocalDownloadHandler extends Handler {
	public function __construct( OutputInterface $output ) {
		parent::__construct( $output );
	}

	public function populate_extension_versions( array $extensions ): void {
		// No-op: Version is not determined for local sources
	}

	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		foreach ( $extensions as $ext ) {
			if ( ! empty( $ext->downloaded_source ) ) {
				continue;
			}

			if ( empty( $ext->directory ) || ! is_string( $ext->directory ) ) {
				throw new \RuntimeException( "Local extension '{$ext->slug}' must have a non-empty 'directory' path." );
			}

			if ( ! file_exists( $ext->directory ) ) {
				throw new \RuntimeException( 'Local path not found: ' . $ext->directory );
			}

			if ( is_file( $ext->directory ) && pathinfo( $ext->directory, PATHINFO_EXTENSION ) === 'zip' ) {
				$cache_file = $this->make_cache_path(
					$cache_dir,
					$ext->type,
					$ext->slug,
					$ext->version ?? 'local',
					$ext->directory
				);

				if ( ! file_exists( $cache_file ) ) {
					copy( $ext->directory, $cache_file );
					try {
						App::make( Zipper::class )->validate_zip( $cache_file );
					} catch ( \Exception $exception ) {
						unlink( $cache_file );
						throw new \RuntimeException( 'Invalid local zip file: ' . $ext->directory );
					}
				}
				$ext->downloaded_source = $cache_file;
			} elseif ( is_dir( $ext->directory ) ) {
				$ext->downloaded_source = $ext->directory;
			} else {
				throw new \RuntimeException( 'Invalid local path: ' . $ext->directory );
			}
		}
	}
}