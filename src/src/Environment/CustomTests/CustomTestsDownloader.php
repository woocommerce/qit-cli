<?php

namespace QIT_CLI\Environment\CustomTests;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\ExtensionDownload\Extension;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use QIT_CLI\Environment\ExtensionDownload\Handlers\QITHandler;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class CustomTestsDownloader {
	/** @var OutputInterface $output */
	protected $output;

	/** @var Zipper $zipper */
	protected $zipper;

	/** @var ExtensionDownloader $extension_downloader */
	protected $extension_downloader;

	public function __construct(
		OutputInterface $output,
		Zipper $zipper,
		ExtensionDownloader $extension_downloader
	) {
		$this->output               = $output;
		$this->zipper               = $zipper;
		$this->extension_downloader = $extension_downloader;
	}

	/**
	 * @param EnvInfo           $env_info
	 * @param string            $cache_dir
	 * @param array<string|int> $plugins Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
	 * @param array<string|int> $themes Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
	 *
	 * @return void
	 */
	public function download( EnvInfo $env_info, string $cache_dir, array $plugins = [], array $themes = [], $test_type = 'e2e' ): void {
		$extensions = $this->extension_downloader->categorize_extensions( $plugins, $themes );

		$extensions_from_qit = array_filter( $extensions, function ( $e ) {
			return $e->handler === QITHandler::class;
		} );

		$this->maybe_create_cache_dir( $cache_dir, $test_type );

		$this->maybe_download_custom_tests( $env_info, $extensions_from_qit, $cache_dir, $test_type );
	}

	protected function maybe_download_custom_tests( EnvInfo $env_info, array $extensions, string $cache_dir, string $test_type ): void {
		$custom_tests = $this->get_custom_tests_info( $extensions );

		foreach ( $extensions as $extension ) {
			if ( array_key_exists( $extension->extension_identifier, $custom_tests ) ) {
				if ( array_key_exists( $test_type, $custom_tests[ $extension->extension_identifier ]['tests'] ) ) {
					$custom_test_url       = $custom_tests[ $extension->extension_identifier ]['tests'][ $test_type ];
					$custom_test_file_name = md5( $custom_test_url ) . '.zip';
					$custom_test_file_path = "$cache_dir/tests/$test_type/$custom_test_file_name";

					if ( ! file_exists( $custom_test_file_path ) ) {
						RequestBuilder::download_file( $custom_test_url, $custom_test_file_path );
					}

					$this->zipper->extract_zip( $custom_test_file_path, "{$env_info->temporary_env}/tests/$test_type/{$extension->extension_identifier}" );

					$env_info->volumes[ "/qit/tests/$test_type/{$extension->extension_identifier}" ] = "{$env_info->temporary_env}/tests/$test_type/{$extension->extension_identifier}";

					if ( $env_info instanceof E2EEnvInfo ) {
						$env_info->tests[ $extension->extension_identifier ] = [
							'extension'         => $extension->extension_identifier,
							'type'              => $extension->type,
							'path_in_container' => "/qit/tests/$test_type/{$extension->extension_identifier}",
							'path_in_host'      => "{$env_info->temporary_env}/tests/$test_type/{$extension->extension_identifier}",
						];
					}
				} else {
					$this->output->writeln( sprintf( 'No custom tests found for %s', $extension->extension_identifier ) );
				}
			} else {
				$this->output->writeln( sprintf( 'No custom tests found for %s', $extension->extension_identifier ) );
			}
		}
	}

	protected function maybe_create_cache_dir( string $cache_dir, string $test_type ) {
		$test_type_cache_dir = "$cache_dir/tests/$test_type";
		if ( ! file_exists( $test_type_cache_dir ) ) {
			if ( ! mkdir( $test_type_cache_dir, 0755, true ) ) {
				throw new \RuntimeException( "Could not create the custom tests directory: $test_type_cache_dir" );
			}
		}
	}

	protected function get_custom_tests_info( array $extensions ): array {
		$extensions_to_get_tests_for = implode( ',', array_map( function ( $v ) {
			return $v->extension_identifier;
		}, $extensions ) );

		if ( empty( $extensions_to_get_tests_for ) ) {
			return [];
		}

		$start    = microtime( true );
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'with_tests' => true,
				'extensions' => $extensions_to_get_tests_for,
			] )
			->request();
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf( 'Fetched custom test checksums for %d extensions from QIT in %f seconds.', count( $extensions ), microtime( true ) - $start ) );
		}

		/**
		 * Each key in the array represents a plugin identifier (e.g., 'plugin-foo').
		 *
		 * @var array<string, array{
		 *     url: string,
		 *     version: string,
		 *     slug: string,
		 *     tests: array{
		 *         e2e: string
		 *     }
		 * }> $download_urls
		 */
		$download_urls = json_decode( $response, true );

		if ( ! is_array( $download_urls ) || empty( $download_urls['urls'] ) || ! is_array( $download_urls['urls'] ) ) {
			throw new \RuntimeException( 'No download URLs received.' );
		}

		return $download_urls['urls'];
	}
}
