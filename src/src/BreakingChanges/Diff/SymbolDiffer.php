<?php

namespace QIT_CLI\BreakingChanges\Diff;

use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Models\SymbolDiffResult;

class SymbolDiffer {
	/**
	 * Compare symbols between old and new versions.
	 */
	public function diff( ExtractedSymbols $old, ExtractedSymbols $new ): SymbolDiffResult {
		$removed = [];
		$added   = [];

		// Compare each symbol category.
		foreach ( [ 'classes', 'methods', 'functions', 'constants' ] as $category ) {
			$old_symbols = $old->$category;
			$new_symbols = $new->$category;

			// Find removed symbols (in old but not in new).
			foreach ( $old_symbols as $key => $symbol ) {
				if ( ! isset( $new_symbols[ $key ] ) ) {
					$removed[] = $symbol;
				}
			}

			// Find added symbols (in new but not in old).
			foreach ( $new_symbols as $key => $symbol ) {
				if ( ! isset( $old_symbols[ $key ] ) ) {
					$added[] = $symbol;
				}
			}
		}

		return new SymbolDiffResult( $removed, $added );
	}
}
