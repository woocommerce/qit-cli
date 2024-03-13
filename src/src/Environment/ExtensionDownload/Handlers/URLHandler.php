<?php

namespace QIT_CLI\Environment\ExtensionDownload\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\ExtensionDownload\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;

class URLHandler extends Handler {
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

		RequestBuilder::download_file( $extension->extension_identifier, $extension->path );
	}
}