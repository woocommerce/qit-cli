<?php

namespace QIT_CLI\PreCommand\Download\Extensions;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\PreCommand\Download\Extensions\Handlers\WCCOMDownloadHandler;
use QIT_CLI\PreCommand\Download\Extensions\Handlers\LocalDownloadHandler;
use QIT_CLI\PreCommand\Download\Extensions\Handlers\WPOrgDownloadHandler;
use QIT_CLI\PreCommand\Download\Extensions\Handlers\UrlDownloadHandler;

class ExtensionDownloader {
	/** @var array<string, WCCOMDownloadHandler|LocalDownloadHandler|WPOrgDownloadHandler|UrlDownloadHandler> */
	protected $handlers = [];

	public function __construct(
		WCCOMDownloadHandler $wccom_handler,
		LocalDownloadHandler $local_handler,
		WPOrgDownloadHandler $wporg_handler,
		UrlDownloadHandler $url_handler
	) {
		$this->handlers = [
			'wccom'     => $wccom_handler,
			'wporg'     => $wporg_handler,
			'directory' => $local_handler,
			'zip'       => $local_handler,
			'url'       => $url_handler,
			'build'     => $local_handler,
		];
	}

	public static function is_valid_plugin_slug( string $slug ): bool {
		return preg_match( '/^[a-z0-9_]+([-\.][a-z0-9_]+)*$/', $slug );
	}

	public function download( EnvInfo $env_info, string $cache_dir, array $plugins, array $themes ): void {
		$extensions = array_merge( $plugins, $themes );

		foreach ( $extensions as $extension ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionDownloader: Processing extension '{$extension->slug}' with source_type: {$extension->from}\n", FILE_APPEND );

			// Skip download if downloaded_source is set for non-remote sources
			if ( in_array( $extension->from, [ 'directory', 'zip', 'build' ], true ) && ! empty( $extension->downloaded_source ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionDownloader: Skipping download for '{$extension->slug}' as downloaded_source is set: {$extension->downloaded_source}\n", FILE_APPEND );
				continue;
			}

			if ( ! isset( $this->handlers[ $extension->from ] ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionDownloader: No handler for source_type '{$extension->from}' for '{$extension->slug}'\n", FILE_APPEND );
				throw new \RuntimeException( "No download handler for source_type '{$extension->from}' for '{$extension->slug}'." );
			}

			$handler = $this->handlers[ $extension->from ];
			try {
				$handler->populate_extension_versions( [ $extension ] );
				$handler->maybe_download_extensions( [ $extension ], $cache_dir );
				if ( empty( $extension->downloaded_source ) ) {
					file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionDownloader: Download failed for '{$extension->slug}'\n", FILE_APPEND );
					throw new \RuntimeException( "Download failed for '{$extension->slug}'." );
				}
				file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionDownloader: Successfully downloaded '{$extension->slug}' to {$extension->downloaded_source}\n", FILE_APPEND );
			} catch ( \Exception $e ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionDownloader: Failed to download '{$extension->slug}': {$e->getMessage()}\n", FILE_APPEND );
				throw new \RuntimeException( "Download failed for '{$extension->slug}': {$e->getMessage()}" );
			}
		}
	}
}