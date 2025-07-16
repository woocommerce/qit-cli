<?php

namespace QIT_AI_Webserver;

/**
 * PathContextProvider - Non-tool class that provides workspace context information
 *
 * This class extracts the functionality previously in PathContextTool but as a regular
 * utility class that doesn't extend BaseTool, preventing it from being called by LLMs.
 */
class PathContextProvider {
	private string $work_dir;
	private string $sut_dir;

	public function __construct( string $work_directory, string $sut_directory = '' ) {
		$this->work_dir = rtrim( $work_directory, '/\\' );
		$this->sut_dir  = ltrim( $sut_directory, '/' );
	}

	/**
	 * Get path context data - same functionality as PathContextTool::do()
	 *
	 * @return array<string, mixed> Context data with wp_root, sut, deps, dep_count, truncated.
	 * @throws \RuntimeException If directories don't exist.
	 */
	public function get_path_context(): array {
		$wp_root  = $this->work_dir;
		$sut_path = rtrim( $wp_root . '/' . $this->sut_dir, '/' );
		$sut_slug = basename( $this->sut_dir );

		if ( ! file_exists( $wp_root ) ) {
			throw new \RuntimeException( "WP_ROOT directory does not exist: $wp_root" );
		}

		if ( ! file_exists( $sut_path ) || ! is_dir( $sut_path ) ) {
			throw new \RuntimeException( "SUT directory does not exist: $sut_path" );
		}

		$deps       = [];
		$total_deps = 0;
		$truncated  = false;

		/** Helper: shallow listing */
		$ls1 = static function ( string $dir ): array {
			$items = @scandir( $dir );
			if ( $items === false ) {
				return [];
			}
			$entries = [];
			foreach ( $items as $i ) {
				if ( $i === '.' || $i === '..' ) {
					continue;
				}
				$entries[] = $i;
				if ( count( $entries ) >= 50 ) {
					$entries[] = '…';
					break;
				}
			}

			return $entries;
		};

		/** Scan wp-content/(plugins|themes) */
		foreach ( [ 'plugins', 'themes' ] as $type_dir ) {
			$base = $wp_root . '/wp-content/' . $type_dir;

			if ( ! file_exists( $base ) ) {
				throw new \RuntimeException( "Base directory does not exist: $base" );
			}

			foreach ( scandir( $base ) ?: [] as $slug ) {
				if ( $slug === '.' || $slug === '..' || $slug === $sut_slug ) {
					continue;
				}

				++$total_deps;
				if ( count( $deps ) >= 20 ) {   // hard cap to keep JSON small
					$truncated = true;
					continue;
				}

				$path = $base . '/' . $slug;
				if ( ! is_dir( $path ) ) {
					continue;
				}

				$deps[] = [
					'slug' => $slug,
					'type' => $type_dir === 'plugins' ? 'plugin' : 'theme',
					'path' => $path,
					'ls'   => $ls1( $path ),
				];
			}
		}

		return [
			'wp_root'   => $wp_root,
			'sut'       => $sut_path,
			'deps'      => $deps,
			'dep_count' => $total_deps,
			'truncated' => $truncated,
		];
	}
}
