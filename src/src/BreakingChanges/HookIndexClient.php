<?php

namespace QIT_CLI\BreakingChanges;

use QIT_CLI\Cache;
use QIT_CLI\RequestBuilder;
use function QIT_CLI\get_manager_url;

class HookIndexClient {
	private Cache $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Query the hook index for plugins referencing the given hook names.
	 *
	 * @param string[] $hook_names
	 * @return array<string, array<array<string, mixed>>> Grouped by hook name.
	 */
	public function query_references( array $hook_names ): array {
		if ( empty( $hook_names ) ) {
			return [];
		}

		$url      = get_manager_url() . '/wp-json/cd/v1/hook-references';
		$response = ( new RequestBuilder( $url ) )
			->with_method( 'POST' )
			->with_post_body( [ 'hook_names' => $hook_names ] )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) || ! isset( $data['references'] ) ) {
			return [];
		}

		return $data['references'];
	}

	/**
	 * Get the hook index status.
	 *
	 * @return array{indexed_plugins: int, total_definitions: int, total_references: int, last_updated: ?string}
	 */
	public function get_status(): array {
		$url      = get_manager_url() . '/wp-json/cd/v1/hook-index-status';
		$response = ( new RequestBuilder( $url ) )
			->with_method( 'GET' )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) ) {
			return [
				'indexed_plugins'   => 0,
				'total_definitions' => 0,
				'total_references'  => 0,
				'last_updated'      => null,
			];
		}

		return $data;
	}

	/**
	 * Check if the hook index is available and populated.
	 */
	public function is_available(): bool {
		try {
			$status = $this->get_status();
			return $status['total_definitions'] > 0;
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
