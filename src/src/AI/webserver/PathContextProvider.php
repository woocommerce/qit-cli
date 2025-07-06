<?php

namespace QIT_AI_Webserver;

/**
 * PathContextProvider - Non-tool class that provides workspace context information
 * 
 * This class extracts the functionality previously in PathContextTool but as a regular
 * utility class that doesn't extend BaseTool, preventing it from being called by LLMs.
 */
class PathContextProvider {
	private string $workDir;
	private string $sutDir;

	public function __construct( string $workDirectory, string $sutDirectory = '' ) {
		$this->workDir = rtrim( $workDirectory, '/\\' );
		$this->sutDir = ltrim( $sutDirectory, '/' );
	}

	/**
	 * Get path context data - same functionality as PathContextTool::do()
	 * 
	 * @return array Context data with wp_root, sut, deps, dep_count, truncated
	 * @throws \RuntimeException if directories don't exist
	 */
	public function getPathContext(): array {
		$wpRoot  = $this->workDir;
		$sutPath = rtrim( $wpRoot . '/' . $this->sutDir, '/' );
		$sutSlug = basename( $this->sutDir );

		if ( ! file_exists( $wpRoot ) ) {
			throw new \RuntimeException( "WP_ROOT directory does not exist: $wpRoot" );
		}

		if ( ! file_exists( $sutPath ) || ! is_dir( $sutPath ) ) {
			throw new \RuntimeException( "SUT directory does not exist: $sutPath" );
		}

		$deps      = [];
		$totalDeps = 0;
		$truncated = false;

		/** helper: shallow listing */
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

		/** scan wp-content/(plugins|themes) */
		foreach ( [ 'plugins', 'themes' ] as $typeDir ) {
			$base = $wpRoot . '/wp-content/' . $typeDir;

			if ( ! file_exists( $base ) ) {
				throw new \RuntimeException( "Base directory does not exist: $base" );
			}

			foreach ( scandir( $base ) ?: [] as $slug ) {
				if ( $slug === '.' || $slug === '..' || $slug === $sutSlug ) {
					continue;
				}

				$totalDeps ++;
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
					'type' => $typeDir === 'plugins' ? 'plugin' : 'theme',
					'path' => $path,
					'ls'   => $ls1( $path ),
				];
			}
		}

		return [
			'wp_root'   => $wpRoot,
			'sut'       => $sutPath,
			'deps'      => $deps,
			'dep_count' => $totalDeps,
			'truncated' => $truncated,
		];
	}
}