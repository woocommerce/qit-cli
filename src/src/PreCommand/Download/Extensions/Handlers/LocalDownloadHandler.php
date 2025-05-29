<?php

namespace QIT_CLI\PreCommand\Download\Extensions\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;

class LocalDownloadHandler extends Handler {
	public function __construct( OutputInterface $output ) {
		parent::__construct( $output );
	}

	public function populate_extension_versions( array $extensions ): void {
		file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Skipping version population for local extensions\n", FILE_APPEND );
	}

	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		$output = App::make( Output::class );

		foreach ( $extensions as $ext ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Processing extension '{$ext->slug}' with source_type: {$ext->from}\n", FILE_APPEND );

			$source_path = null;
			if ( $ext->from === 'directory' ) {
				if ( empty( $ext->directory ) || ! is_string( $ext->directory ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Missing directory for '{$ext->slug}'\n", FILE_APPEND );
					throw new \RuntimeException( "Local extension '{$ext->slug}' must have a non-empty 'directory' path." );
				}
				$source_path = $ext->directory;
			} elseif ( in_array( $ext->from, [ 'zip', 'build' ], true ) ) {
				if ( empty( $ext->source ) || ! is_string( $ext->source ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Missing source for '{$ext->slug}' (source_type: {$ext->from})\n", FILE_APPEND );
					throw new \RuntimeException( "Local extension '{$ext->slug}' must have a non-empty 'source' path for source_type '{$ext->from}'." );
				}
				$source_path = $ext->source;
			} else {
				file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Invalid source_type '{$ext->from}' for '{$ext->slug}'\n", FILE_APPEND );
				throw new \RuntimeException( "Invalid source_type '{$ext->from}' for '{$ext->slug}'." );
			}

			if ( ! file_exists( $source_path ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Path not found for '{$ext->slug}' at '$source_path'\n", FILE_APPEND );
				throw new \RuntimeException( "Local path not found for '{$ext->slug}' at '$source_path'." );
			}

			if ( in_array( $ext->from, [ 'zip', 'build' ], true ) && is_file( $source_path ) && pathinfo( $source_path, PATHINFO_EXTENSION ) === 'zip' ) {
				// Validate ZIP even if downloaded_source is set
				try {
					App::make( Zipper::class )->validate_zip( $source_path );
					file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Validated ZIP for '{$ext->slug}' at '$source_path'\n", FILE_APPEND );
				} catch ( \Exception $e ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Invalid ZIP for '{$ext->slug}' at '$source_path': {$e->getMessage()}\n", FILE_APPEND );
					throw new \RuntimeException( "Invalid ZIP file: $source_path" );
				}

				$cache_file = $this->make_cache_path(
					$cache_dir,
					$ext->type,
					$ext->slug,
					$ext->version ?? 'local',
					$source_path
				);

				if ( ! file_exists( $cache_file ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Copying ZIP for '{$ext->slug}' from '$source_path' to '$cache_file'\n", FILE_APPEND );
					copy( $source_path, $cache_file );
				}
				$ext->downloaded_source = $cache_file;
			} elseif ( $ext->from === 'directory' && is_dir( $source_path ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Using directory for '{$ext->slug}' at '$source_path'\n", FILE_APPEND );
				$ext->downloaded_source = $source_path;
			} else {
				file_put_contents( '/tmp/qit/qit_debug.log', "LocalDownloadHandler: Invalid path for '{$ext->slug}' at '$source_path'\n", FILE_APPEND );
				throw new \RuntimeException( "Invalid local path for '{$ext->slug}' at '$source_path'." );
			}

			if ( $output->isVeryVerbose() ) {
				$output->writeln( "Using local {$ext->type} {$ext->slug} at {$ext->downloaded_source}." );
			}
		}
	}
}