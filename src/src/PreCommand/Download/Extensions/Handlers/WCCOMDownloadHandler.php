<?php

namespace QIT_CLI\PreCommand\Download\Extensions\Handlers;

use QIT_CLI\App;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class WCCOMDownloadHandler extends Handler {
	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	public function __construct( OutputInterface $output, WooExtensionsList $woo_extensions_list ) {
		parent::__construct( $output );
		$this->woo_extensions_list = $woo_extensions_list;
	}

	public function populate_extension_versions( array $extensions ): void {
		$output = App::make( Output::class );

		$extensions_to_download = array_filter(
			$extensions,
			static function ( Extension $ext ) {
				return ! file_exists( $ext->downloaded_source ?? '' );
			}
		);

		if ( empty( $extensions_to_download ) ) {
			return;
		}

		$slugs             = array_map(
			static function ( Extension $ext ) {
				return $ext->slug;
			},
			$extensions_to_download
		);
		$extensions_string = implode( ',', $slugs );

		$types_map = [];
		foreach ( $extensions_to_download as $ext ) {
			$types_map[ $ext->slug ] = $ext->type;
		}

		$start = microtime( true );

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'sut_slug'   => App::getVar( 'QIT_SUT_SLUG', '' ),
				'extensions' => $extensions_string,
				'types'      => $types_map,
				'from'       => 'wccom',
			] )
			->request();

		file_put_contents( '/tmp/qit/qit_debug.log', 'WCCOMDownloadHandler Response: ' . print_r( $response, true ) . "\n", FILE_APPEND );

		if ( $output->isVerbose() ) {
			$output->writeln( sprintf(
				'Fetched versions for %d WooCommerce.com extensions in %f seconds.',
				count( $extensions_to_download ),
				microtime( true ) - $start
			) );
		}

		$download_urls = json_decode( $response, true );

		if ( ! is_array( $download_urls ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "WCCOMDownloadHandler: Invalid JSON response\n", FILE_APPEND );
			throw new \RuntimeException( 'No valid JSON response from QIT Manager.' );
		}

		if ( isset( $download_urls['code'], $download_urls['message'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "WCCOMDownloadHandler: Error: {$download_urls['message']}\n", FILE_APPEND );
			throw new \RuntimeException( $download_urls['message'] );
		}

		// Handle empty or missing URLs gracefully
		if ( empty( $download_urls['urls'] ) || ! is_array( $download_urls['urls'] ) ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "WCCOMDownloadHandler: No download URLs received\n", FILE_APPEND );
			foreach ( $extensions_to_download as $ext ) {
				$ext->source  = '';
				$ext->version = 'stable';
			}

			return;
		}

		$urls = $download_urls['urls'];

		foreach ( $extensions_to_download as $ext ) {
			$original_slug = $ext->slug;
			if ( ! array_key_exists( $original_slug, $urls ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "WCCOMDownloadHandler: No download URL for '$original_slug'\n", FILE_APPEND );
				$ext->source  = '';
				$ext->version = 'stable';
				continue;
			}

			$ext->slug     = $urls[ $original_slug ]['slug'];
			$ext->version  = $urls[ $original_slug ]['version'];
			$ext->source   = $urls[ $original_slug ]['url'];
			$ext->wccom_id = $this->woo_extensions_list->get_woo_extension_id_by_slug( $ext->slug );
		}
	}

	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		$output = App::make( Output::class );

		foreach ( $extensions as $ext ) {
			if ( ! empty( $ext->downloaded_source ) ) {
				continue;
			}

			if ( empty( $ext->source ) ) {
				file_put_contents( '/tmp/qit/qit_debug.log', "WCCOMDownloadHandler: No source URL for '{$ext->slug}', skipping download\n", FILE_APPEND );
				continue;
			}

			$cache_file = $this->make_cache_path(
				$cache_dir,
				$ext->type,
				$ext->slug,
				$ext->version,
				$ext->source
			);

			if ( $output->isVeryVerbose() ) {
				$output->writeln( "No cache validation for {$ext->type} {$ext->slug} (WCCOM)." );
			}

			RequestBuilder::download_file( $ext->source, $cache_file );

			try {
				App::make( Zipper::class )->validate_zip( $cache_file );
			} catch ( \Exception $exception ) {
				unlink( $cache_file );
				file_put_contents( '/tmp/qit/qit_debug.log', "WCCOMDownloadHandler: Failed to download zip for '{$ext->slug}': {$exception->getMessage()}\n", FILE_APPEND );
				throw new \RuntimeException(
					sprintf( 'Could not download zip file from URL %s.', $ext->source )
				);
			}

			$ext->downloaded_source = $cache_file;
		}
	}
}