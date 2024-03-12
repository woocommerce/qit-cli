<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class ExtensionDownloader {
	/** @var OutputInterface $output */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * @param EnvInfo           $env_info
	 * @param string            $cache_dir
	 * @param array<string|int> $plugins Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
	 * @param array<string|int> $themes Accepts paths, Woo.com slugs/product IDs, WordPress.org slugs or GitHub URLs.
	 *
	 * @return void
	 */
	public function download( EnvInfo $env_info, string $cache_dir, array $plugins = [], array $themes = [] ): void {
		$plugins_by_type = $this->group_by_downloadable( $plugins );
		$themes_by_type  = $this->group_by_downloadable( $themes );
	}

	protected function group_by_downloadable( array $extensions ): array {
		$grouped = $this->group_by_type( $extensions );

		// Keep only 'slug' and 'id' types as they are considered downloadable.
		return [
			'slug' => $grouped['slug'] ?? [],
			'id'   => $grouped['id'] ?? [],
		];
	}

	/**
	 * @param array<string|int> $extensions
	 *
	 * @return array<string, array<string>>
	 */
	protected function group_by_type( array $extensions ): array {
		$grouped = [
			'path' => [],
			'slug' => [],
			'id'   => [],
			'url'  => [],
		];

		foreach ( $extensions as $extension ) {
			$type = $this->detect_type( $extension );

			$grouped[ $type ][] = $extension;
		}

		return $grouped;
	}

	/**
	 * @param string|int $extension
	 *
	 * @return string
	 */
	protected function detect_type( $extension ): string {
		if ( is_numeric( $extension ) ) {
			return 'id';
		}

		if ( preg_match( '#^https?://#i', $extension ) ) {
			return 'url';
		}

		if ( file_exists( $extension ) ) {
			return 'path';
		}

		return 'slug';
	}

	public function download_plugins( array $plugins, EnvInfo $env_info ) {
		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-download-url' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'woo_ids'     => $extensions,
					'test_status' => $input->getOption( 'test_status' ),
					'test_types'  => $input->getOption( 'test_types' ),
					'page'        => $input->getOption( 'page' ),
					'per_page'    => $input->getOption( 'per_page' ),
				] )
				->request();
		} catch ( \Exception $e ) {
			return [ false, '' ];
		}
	}
}
