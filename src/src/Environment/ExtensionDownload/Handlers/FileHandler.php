<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

class FileHandler extends Handler {
	public function populate_extension_versions( array $extensions ): void {
		// No-op.
	}

	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		foreach ( $extensions as $e ) {
			if ( ! empty( $e->downloaded_source ) ) {
				// Extension already handled (possibly by a custom handler).
				continue;
			}

			if ( ! file_exists( $e->source ) ) {
				throw new \RuntimeException( 'File not found: ' . $e->source );
			}

			$e->downloaded_source = $e->source;
		}
	}
}
