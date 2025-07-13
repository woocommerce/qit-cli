<?php
namespace QIT_AI_Webserver\Lib;

/**
 * Lightweight in‑memory store for step findings.
 * Each array pushed in may contain any keys you like,
 * but the examples below assume at least:
 *   id, step, kind, summary, [file,line,extra…]
 */
class FactStore {
	public const KIND_STEP_SUMMARY = 'step_summary';   // <-- NEW

	public static array $facts = [];  // cleared per request

	public static function add( array $fact ): void {
		if ( ! isset( $fact['id'] ) ) {
			$fact['id'] = uniqid( 'fact_', true );
		}
		self::$facts[] = $fact;
	}

	/** Browse by step/kind with newest‑first order */
	public static function list(
		?int $step = null,
		?string $kind = null,
		int $limit = 20
	): array {

		$matches = array_filter(self::$facts, function ( $f ) use ( $step, $kind ) {
			if ( $step !== null && ( $f['step'] ?? null ) !== $step ) {
				return false;
			}
			if ( $kind !== null && ( $f['kind'] ?? null ) !== $kind ) {
				return false;
			}
			return true;
		});
		usort( $matches, fn( $a, $b ) => ( $b['step'] ?? 0 ) <=> ( $a['step'] ?? 0 ) );

		return array_slice( $matches, 0, $limit );
	}

	/** Naive substring search – replace with embedding search when ready */
	public static function search( string $query, int $k = 5 ): array {
		$q      = mb_strtolower( $query );
		$scored = [];
		foreach ( self::$facts as $f ) {
			$text = mb_strtolower( $f['summary'] ?? json_encode( $f ) );
			if ( ( $pos = mb_strpos( $text, $q ) ) !== false ) {
				// lower position == better score
				$scored[] = [
					'score' => 1_000 - $pos,
					'fact'  => $f,
				];
			}
		}
		usort( $scored, fn( $a, $b ) => $b['score'] <=> $a['score'] );

		return array_slice( array_column( $scored, 'fact' ), 0, $k );
	}
}
