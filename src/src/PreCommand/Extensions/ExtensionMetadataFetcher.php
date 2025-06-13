<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Extension;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

/**
 * Fetches metadata (version, download URLs) for extensions.
 */
class ExtensionMetadataFetcher {
	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	/** @var WPORGExtensionsList */
	protected $wporg_extensions_list;

	/** @var Cache */
	protected $cache;

	/** @var OutputInterface */
	protected $output;

	public function __construct(
		WooExtensionsList $woo_extensions_list,
		WPORGExtensionsList $wporg_extensions_list,
		Cache $cache,
		OutputInterface $output
	) {
		$this->woo_extensions_list   = $woo_extensions_list;
		$this->wporg_extensions_list = $wporg_extensions_list;
		$this->cache                 = $cache;
		$this->output                = $output;
	}

	/**
	 * Fetch metadata for multiple extensions.
	 * Groups by source type for efficiency.
	 *
	 * @param Extension[] $extensions
	 *
	 * @throws \RuntimeException
	 */
	public function fetch_metadata( array $extensions ): void {
		// Group extensions by source type
		$grouped = [];
		foreach ( $extensions as $extension ) {
			if ( empty( $extension->from ) ) {
				throw new \RuntimeException( "Extensions '{$extension->slug}' has no source type set" );
			}
			$grouped[ $extension->from ][] = $extension;
		}

		// Process each group
		foreach ( $grouped as $source_type => $group ) {
			switch ( $source_type ) {
				case 'wporg':
					$this->fetch_wporg_metadata( $group );
					break;
				case 'wccom':
					$this->fetch_wccom_metadata( $group );
					break;
				case 'local':
				case 'build':
					// Local sources don't need metadata fetching
					$this->process_local_metadata( $group );
					break;
				case 'url':
					// URL sources use the URL as-is
					$this->process_url_metadata( $group );
					break;
				default:
					throw new \RuntimeException( "Unknown source type: $source_type" );
			}
		}
	}

	/**
	 * Fetch metadata for WPORG extensions.
	 */
	protected function fetch_wporg_metadata( array $extensions ): void {
		$start = microtime( true );

		foreach ( $extensions as $extension ) {
			try {
				if ( $extension->type === 'plugin' ) {
					$info = $this->wporg_extensions_list->get_plugin_download_info( $extension->slug );
				} else {
					$info = $this->wporg_extensions_list->get_theme_download_info( $extension->slug );
				}

				$extension->source  = $info['url'];
				$extension->version = $info['version'];

				file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionMetadataFetcher: WPORG metadata for '{$extension->slug}': version={$extension->version}, url={$extension->source}\n", FILE_APPEND );
			} catch ( \Exception $e ) {
				throw new \RuntimeException( "Failed to fetch WPORG metadata for '{$extension->slug}': " . $e->getMessage() );
			}
		}

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf(
				'Fetched metadata for %d WordPress.org extensions in %f seconds.',
				count( $extensions ),
				microtime( true ) - $start
			) );
		}
	}

	/**
	 * Fetch metadata for WCCOM extensions using bulk API.
	 */
	protected function fetch_wccom_metadata( array $extensions ): void {
		if ( empty( $extensions ) ) {
			return;
		}

		$start     = microtime( true );
		$slugs     = array_map( fn( $ext ) => $ext->slug, $extensions );
		$types_map = [];
		foreach ( $extensions as $ext ) {
			$types_map[ $ext->slug ] = $ext->type;
		}

		try {
			$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'sut_slug'   => App::getVar( 'QIT_SUT_SLUG', '' ),
					'extensions' => implode( ',', $slugs ),
					'types'      => $types_map,
					'from'       => 'wccom',
				] )
				->request();

			$data = json_decode( $response, true );

			if ( ! is_array( $data ) || ! isset( $data['urls'] ) ) {
				throw new \RuntimeException( 'Invalid response from WCCOM API' );
			}

			foreach ( $extensions as $extension ) {
				if ( isset( $data['urls'][ $extension->slug ] ) ) {
					$info               = $data['urls'][ $extension->slug ];
					$extension->slug    = $info['slug']; // May be different from requested
					$extension->version = $info['version'];
					$extension->source  = $info['url'];

					file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionMetadataFetcher: WCCOM metadata for '{$extension->slug}': version={$extension->version}\n", FILE_APPEND );
				} else {
					// Fallback for extensions not found
					$extension->version = 'stable';
					$extension->source  = '';
				}
			}
		} catch ( \Exception $e ) {
			throw new \RuntimeException( 'Failed to fetch WCCOM metadata: ' . $e->getMessage() );
		}

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf(
				'Fetched metadata for %d WooCommerce.com extensions in %f seconds.',
				count( $extensions ),
				microtime( true ) - $start
			) );
		}
	}

	/**
	 * Process local extensions metadata.
	 */
	protected function process_local_metadata( array $extensions ): void {
		foreach ( $extensions as $extension ) {
			// Local extensions don't have remote versions
			$extension->version = 'local';

			// Source is already set for local files
			if ( $extension->from === 'local' && empty( $extension->source ) && ! empty( $extension->directory ) ) {
				$extension->source = $extension->directory;
			}

			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionMetadataFetcher: Local metadata for '{$extension->slug}': type={$extension->from}\n", FILE_APPEND );
		}
	}

	/**
	 * Process URL extensions metadata.
	 */
	protected function process_url_metadata( array $extensions ): void {
		foreach ( $extensions as $extension ) {
			// URL extensions use the URL as source
			if ( empty( $extension->source ) ) {
				throw new \RuntimeException( "URL extension '{$extension->slug}' has no source URL" );
			}

			// Version is not determined for URL sources
			$extension->version = 'url';

			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionMetadataFetcher: URL metadata for '{$extension->slug}': url={$extension->source}\n", FILE_APPEND );
		}
	}
}
