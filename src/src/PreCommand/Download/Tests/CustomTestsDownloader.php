<?php

namespace QIT_CLI\PreCommand\Download\Tests;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Extension;
use QIT_CLI\IO\Output;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class CustomTestsDownloader {
	/** @var OutputInterface */
	protected $output;

	/** @var Zipper */
	protected $zipper;

	public function __construct( OutputInterface $output, Zipper $zipper ) {
		$this->output = $output;
		$this->zipper = $zipper;
	}

	/**
	 * Downloads custom tests specified in the bootstrap configuration.
	 *
	 * @param EnvInfo $env_info The environment configuration.
	 * @param string $cache_dir The cache directory.
	 * @param array<Extension> $plugins List of plugins (for validation).
	 * @param array<Extension> $themes List of themes (for validation).
	 *
	 * @return void
	 */
	public function download( EnvInfo $env_info, string $cache_dir, array $plugins = [], array $themes = [] ): void {
		$output = App::make( Output::class );

		if ( empty( $env_info->bootstrap ) || ! is_array( $env_info->bootstrap ) ) {
			if ( $output->isVerbose() ) {
				$output->writeln( "No bootstrap tests specified." );
			}

			return;
		}

		$tests_to_download = [];
		$extension_slugs   = array_merge(
			array_map( fn( $p ) => $p->slug, $plugins ),
			array_map( fn( $t ) => $t->slug, $themes )
		);

		// Parse bootstrap entries
		foreach ( $env_info->bootstrap as $index => $bootstrap_item ) {
			if ( ! is_array( $bootstrap_item ) || ! isset( $bootstrap_item['slug'], $bootstrap_item['test_package'] ) || ! is_string( $bootstrap_item['slug'] ) || ! is_string( $bootstrap_item['test_package'] ) ) {
				$output->writeln( "<error>Invalid bootstrap item at index $index: " . json_encode( $bootstrap_item ) . "</error>" );
				continue;
			}

			$slug         = $bootstrap_item['slug'];
			$test_package = $bootstrap_item['test_package'];
			$tag          = isset( $bootstrap_item['tag'] ) && is_string( $bootstrap_item['tag'] ) ? $bootstrap_item['tag'] : 'default';

			// Validate slug exists in plugins or themes
			if ( ! in_array( $slug, $extension_slugs, true ) ) {
				$output->writeln( "<warning>Skipping test for '$slug/$test_package:$tag' at index $index as the extension is not included.</warning>" );
				continue;
			}

			$tests_to_download[] = [
				'slug'         => $slug,
				'test_package' => $test_package,
				'tag'          => $tag,
			];
		}

		if ( empty( $tests_to_download ) ) {
			if ( $output->isVerbose() ) {
				$output->writeln( "No valid bootstrap tests to download." );
			}

			return;
		}

		$output->writeln( '<info>Downloading custom tests...</info>' );

		$start = microtime( true );

		// Fetch download URLs from QIT Manager
		$tests_string = implode( ',', array_map( fn( $t ) => "{$t['slug']}/{$t['test_package']}:{$t['tag']}", $tests_to_download ) );
		$response     = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/test-download-urls' ) )
			->with_method( 'POST' )
			->with_post_body( [
				'tests' => $tests_string,
			] )
			->request();

		$download_urls = json_decode( $response, true );

		if ( ! is_array( $download_urls ) ) {
			throw new \RuntimeException( 'No valid JSON response from QIT Manager for test downloads.' );
		}

		if ( isset( $download_urls['code'], $download_urls['message'] ) ) {
			throw new \RuntimeException( $download_urls['message'] );
		}

		if ( empty( $download_urls['urls'] ) || ! is_array( $download_urls['urls'] ) ) {
			throw new \RuntimeException( 'No test download URLs received from QIT Manager.' );
		}

		$urls = $download_urls['urls'];

		foreach ( $tests_to_download as $test ) {
			$key = "{$test['slug']}/{$test['test_package']}:{$test['tag']}";
			if ( ! array_key_exists( $key, $urls ) ) {
				$output->writeln( "<error>No download URL found for test '$key'.</error>" );
				continue;
			}

			$test_url   = $urls[ $key ]['url'];
			$cache_file = "$cache_dir/tests/{$test['slug']}-{$test['test_package']}-{$test['tag']}.zip";

			if ( file_exists( $cache_file ) ) {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Using cached test {$test['slug']}/{$test['test_package']}:{$test['tag']}." );
				}
			} else {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( "Cache miss on test {$test['slug']}/{$test['test_package']}:{$test['tag']}." );
				}

				if ( ! file_exists( dirname( $cache_file ) ) ) {
					if ( ! mkdir( dirname( $cache_file ), 0755, true ) ) {
						throw new \RuntimeException( "Could not create test cache directory: " . dirname( $cache_file ) );
					}
				}

				RequestBuilder::download_file( $test_url, $cache_file );

				try {
					$this->zipper->validate_zip( $cache_file );
				} catch ( \Exception $exception ) {
					unlink( $cache_file );
					throw new \RuntimeException( "Could not download test zip for '$key': {$exception->getMessage()}" );
				}
			}

			// Extract test to temporary environment
			$test_dir = "{$env_info->temporary_env}/tests/{$test['slug']}/{$test['test_package']}";
			$this->zipper->extract_zip( $cache_file, $test_dir );
		}

		if ( $output->isVerbose() ) {
			$output->writeln( sprintf(
				'Downloaded %d custom tests in %f seconds.',
				count( $tests_to_download ),
				microtime( true ) - $start
			) );
		}
	}
}