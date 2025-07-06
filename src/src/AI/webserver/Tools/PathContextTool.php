<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

/**
 * Tool: get_path_context
 *
 * Return canonical macro variables that describe the workspace layout.
 * – $WP_ROOT  : absolute extraction directory
 * – $SUT      : absolute path to the plugin / theme under test
 * – deps[]    : other plugins / themes present for context
 *               Each dep contains first‑level entries (“ls”).
 */
class PathContextTool extends BaseTool {

	/** @var string relative path such as wp-content/plugins/foo-bar */
	private string $sutDir;

	public function __construct( string $workDirectory, string $sutDirectory = '' ) {
		parent::__construct( $workDirectory, $sutDirectory );
		$this->sutDir = ltrim( $sutDirectory, '/' );
	}

	/* ─────────────── metadata ─────────────── */
	public function getName(): string {
		return 'get_path_context';
	}

	public function getDescription(): string {
		return 'Return $WP_ROOT, $SUT and dependency map.';
	}

	public function getFunctionInfo(): FunctionInfo {
		/* --------------------------------------------------------------
		 * Add ONE **optional, ignored** parameter ("_").
		 *   – keeps runtime signature backward‑compatible
		 *   – forces FunctionFormatter to emit a valid schema
		 * --------------------------------------------------------------*/
		$params = [
			new Parameter(
				'_',                    // name
				'boolean',              // type (anything primitive works)
				'Ignore me – internal placeholder',
			)
		];

		return new FunctionInfo(
			$this->getName(),
			[ $this, 'get_path_context' ],
			$this->getDescription(),
			$params,   // <-- 1 optional param
			[]         // nothing is required
		);
	}

	// keep signature compatible – the arg defaults to null / unused
	public function get_path_context( ?bool $_ = null ): string {
		return json_encode( $this->execute( [] ), JSON_UNESCAPED_SLASHES );
	}

	/* ─────────────── core ─────────────── */
	protected function do( array $params ) {
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
