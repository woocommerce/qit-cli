<?php

namespace QIT_CLI\Environment\CustomTests;

use QIT_CLI\Commands\Tags\UploadTestTagsCommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
		$custom_tests    = $this->get_custom_tests_info( $extensions );
		$printed_warning = false;

		foreach ( $extensions as $extension ) {
			if ( $extension->action === Extension::ACTIONS['activate'] ) {
				continue;
			}

			foreach ( $extension->test_tags as $k => $test_tag ) {
				$original_path = null;

				// Check if local test directory/file conflicts with remote test tag.
				if ( file_exists( $test_tag ) ) {
					if ( ! empty( $custom_tests[ $extension->slug ]['tests'][ $test_type ][ $test_tag ] ) ) {
						$this->output->writeln( sprintf(
							'Conflict detected: Test tag "%s" exists both locally and remotely. The remote test tag will be used for the extension "%s".',
							$test_tag,
							$extension->slug
						) );
					}
				}

				if ( isset( $custom_tests[ $extension->slug ]['tests'][ $test_type ][ $test_tag ] ) ) {
					$custom_test_url       = $custom_tests[ $extension->slug ]['tests'][ $test_type ][ $test_tag ];
					$custom_test_file_name = md5( $custom_test_url ) . '.zip';
					$custom_test_file_path = "$cache_dir/tests/$test_type/$custom_test_file_name";

					// If connected to Local manager, let the developer know.
					if ( Config::get_current_manager_backend() === 'local' && $printed_warning === false ) {
						$printed_warning = true;
						$io              = new SymfonyStyle( new ArrayInput( [] ), $this->output );
						$io->warning( 'You are connected to the Local manager. Custom tests will be downloaded from the local QIT instance and might be outdated!' );
					}

					if ( ! file_exists( $custom_test_file_path ) ) {
						RequestBuilder::download_file( $custom_test_url, $custom_test_file_path );
					}

					$zip_file           = $custom_test_file_path;
					$processed_test_tag = $test_tag;
				} elseif ( file_exists( $test_tag ) ) {
					// Local test directory or file.
					if ( is_dir( $test_tag ) ) {
						$original_path = $test_tag;
						$zip_file      = tempnam( sys_get_temp_dir(), 'qit_' ) . '.zip';
						$this->zipper->zip_directory( $test_tag, $zip_file, UploadTestTagsCommand::get_exclude_files() );
					} else {
						$zip_file = $test_tag;
					}

					$processed_test_tag = $k > 0 ? "local-$k" : 'local';
				} else {
					$this->output->writeln( sprintf( 'No test tag "%s" found for extension "%s".', $test_tag, $extension->slug ) );
					continue;
				}

				$path_in_host          = "{$env_info->temporary_env}/tests/$test_type/{$extension->slug}/$processed_test_tag";
				$path_in_php_container = "/qit/tests/$test_type/{$extension->slug}/$processed_test_tag";

				$this->zipper->extract_zip( $zip_file, $path_in_host );

				$env_info->volumes[ $path_in_php_container ] = $path_in_host;

				if ( $env_info instanceof E2EEnvInfo ) {
					$env_info->tests[] = [
						'slug'                  => $extension->slug,
						'test_tag'              => $processed_test_tag,
						'type'                  => $extension->type,
						'action'                => $extension->action,
						'path_in_php_container' => $path_in_php_container,
						'path_in_host'          => $path_in_host,
						'path_in_host_original' => $original_path ?? '',
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
	 * Fetch custom test info from the Manager endpoint, sending a friendly JSON structure:
	 * {
	 *   "extensions": [
	 *     { "slug": "<slug1>", "tags": ["tagA","tagB"] },
	 *     { "slug": "<slug2>", "tags": ["tagC"] }
	 *   ]
	 * }
	 *
	 * @param array<Extension> $extensions
	 *
	 * @return array<string, array{
	 *     slug: string,
	 *     tests: array<string, array<string,string>>
	 * }> e.g. [ "slug" => ["slug"=>"slug","tests"=>[...]], ...]
	 */
	protected function get_custom_tests_info( array $extensions ): array {
		$payload = [ 'extensions' => [] ];

		foreach ( $extensions as $ext ) {
			if ( ! isset( $ext->wccom_id ) || empty( $ext->wccom_id ) ) {
				continue;
			}
			$payload['extensions'][] = [
				'slug' => $ext->slug,
				'tags' => array_values( $ext->test_tags ),
			];
		}

		if ( empty( $payload['extensions'] ) ) {
			return [];
		}

		$start = microtime( true );

		$response = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/custom-test-download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( $payload )
			->request();

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( sprintf(
				'Fetched custom test checksums for %d extensions from QIT in %f seconds.',
				count( $extensions ),
				microtime( true ) - $start
			) );
		}

		$download_urls = json_decode( $response, true );

		// Handle errors from QIT Manager.
		if ( ! is_array( $download_urls ) ) {
			throw new \RuntimeException( 'No valid JSON response from QIT Manager.' );
		}

		if ( isset( $download_urls['code'] ) && $download_urls['code'] === 'rest_forbidden' && ! empty( $download_urls['message'] ) ) {
			// The manager returned permission errors.
			throw new \RuntimeException( $download_urls['message'] );
		}

		if ( empty( $download_urls['urls'] ) || ! is_array( $download_urls['urls'] ) ) {
			// If no tests found and no error code, just return empty array.
			return [];
		}

		return $download_urls['urls'];
	}
}
