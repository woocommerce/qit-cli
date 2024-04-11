<?php

namespace QIT_CLI\Environment\CustomTests;

use QIT_CLI\Commands\CustomTests\UploadCustomTestCommand;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
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
	 * @param EnvInfo          $env_info
	 * @param string           $cache_dir
	 * @param array<Extension> $plugins Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
	 * @param array<Extension> $themes Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
	 * @param string           $test_type The test type. Defaults to 'e2e'.
	 *
	 * @return void
	 */
	public function download( EnvInfo $env_info, string $cache_dir, array $plugins = [], array $themes = [], string $test_type = 'e2e' ): void {
		$extensions = $this->extension_downloader->categorize_extensions( $plugins, $themes );

		$this->maybe_create_cache_dir( $cache_dir, $test_type );

		$this->maybe_download_custom_tests( $env_info, $extensions, $cache_dir, $test_type );
	}

	/**
	 * @param EnvInfo          $env_info
	 * @param array<Extension> $extensions
	 * @param string           $cache_dir
	 * @param string           $test_type
	 *
	 * @return void
	 */
	protected function maybe_download_custom_tests( EnvInfo $env_info, array $extensions, string $cache_dir, string $test_type ): void {
		$custom_tests = $this->get_custom_tests_info( $extensions );

		foreach ( $extensions as $extension ) {
			if ( $extension->action === Extension::ACTIONS['activate'] ) {
				continue;
			}

			foreach ( $extension->test_tags as $k => $test_tag ) {
				if ( file_exists( $test_tag ) ) {
					if ( is_dir( $test_tag ) ) {
						$zip_file = tempnam( sys_get_temp_dir(), 'qit_' ) . '.zip';
						$this->zipper->zip_directory( $test_tag, $zip_file, UploadCustomTestCommand::get_exclude_files() );
					} else {
						$zip_file = $test_tag;
					}

					$processed_test_tag = $k > 0 ? "local-$k" : 'local';
				} elseif ( isset( $custom_tests[ $extension->slug ]['tests'][ $test_type ][ $test_tag ] ) ) { // @phpstan-ignore-line
					$custom_test_url       = $custom_tests[ $extension->slug ]['tests'][ $test_type ][ $test_tag ];
					$custom_test_file_name = md5( $custom_test_url ) . '.zip';
					$custom_test_file_path = "$cache_dir/tests/$test_type/$custom_test_file_name";

					if ( ! file_exists( $custom_test_file_path ) ) {
						RequestBuilder::download_file( $custom_test_url, $custom_test_file_path );
					}

					$zip_file           = $custom_test_file_path;
					$processed_test_tag = $test_tag;
				} else {
					$this->output->writeln( sprintf( 'No test tag "%s" found for extension "%s".', $test_tag, $extension->slug ) );
					continue;
				}

				$path_in_host                 = "{$env_info->temporary_env}/tests/$test_type/{$extension->slug}/$processed_test_tag";
				$path_in_php_container        = "/qit/tests/$test_type/{$extension->slug}/$processed_test_tag";
				$path_in_playwright_container = "/home/pwuser/{$extension->slug}/$processed_test_tag";

				$this->zipper->extract_zip( $zip_file, $path_in_host );

				$env_info->volumes[ $path_in_php_container ] = $path_in_host;

				if ( $env_info instanceof E2EEnvInfo ) {
					$env_info->tests[] = [
						'slug'                         => $extension->slug,
						'test_tag'                     => $processed_test_tag,
						'type'                         => $extension->type,
						'action'                       => $extension->action,
						'path_in_php_container'        => $path_in_php_container,
						'path_in_playwright_container' => $path_in_playwright_container,
						'path_in_host'                 => $path_in_host,
					];
				}
			}
		}
	}

	protected function maybe_create_cache_dir( string $cache_dir, string $test_type ): void {
		$test_type_cache_dir = "$cache_dir/tests/$test_type";
		if ( ! file_exists( $test_type_cache_dir ) ) {
			if ( ! mkdir( $test_type_cache_dir, 0755, true ) ) {
				throw new \RuntimeException( "Could not create the custom tests directory: $test_type_cache_dir" );
			}
		}
	}

	/**
	 * @param array<\QIT_CLI\Environment\Extension> $extensions
	 *
	 * @return array<string, array{
	 *     url: string,
	 *     version: string,
	 *     slug: string,
	 *     tests: array{
	 *          e2e: string
	 *      }
	 *   }> Each key in the array represents a plugin identifier (e.g., 'plugin-foo').
	 */
	protected function get_custom_tests_info( array $extensions ): array {
		$test_tags_to_fetch          = [];
		$extensions_to_get_tests_for = [];

		foreach ( $extensions as $ext ) {
			foreach ( $ext->test_tags as $test_tag ) {
				if ( ! file_exists( $test_tag ) ) {
					$test_tags_to_fetch[] = "{$ext->slug}:{$test_tag}";
					if ( ! in_array( $ext->slug, $extensions_to_get_tests_for, true ) ) {
						$extensions_to_get_tests_for[] = $ext->slug;
					}
				}
			}
		}

		if ( empty( $extensions_to_get_tests_for ) ) {
			return [];
		}

		$start    = microtime( true );
		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'extensions' => $extensions_to_get_tests_for,
				'test_tags'  => implode( ',', $test_tags_to_fetch ),
			] )
			->request();
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf( 'Fetched custom test checksums for %d extensions from QIT in %f seconds.', count( $extensions ), microtime( true ) - $start ) );
		}

		$download_urls = json_decode( $response, true );

		if ( ! is_array( $download_urls ) || empty( $download_urls['urls'] ) || ! is_array( $download_urls['urls'] ) ) {
			throw new \RuntimeException( 'No download URLs received.' );
		}

		return $download_urls['urls'];
	}
}
