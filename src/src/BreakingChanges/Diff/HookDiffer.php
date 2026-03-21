<?php

namespace QIT_CLI\BreakingChanges\Diff;

use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Models\HookDiffResult;

class HookDiffer {
	/**
	 * Compare hooks between old and new versions.
	 * Dynamic hooks are skipped since their names cannot be statically determined.
	 */
	public function diff( ExtractedSymbols $old, ExtractedSymbols $new ): HookDiffResult {
		$removed = [];
		$added   = [];

		// Find removed hooks (in old but not in new).
		foreach ( $old->hooks as $name => $hook ) {
			if ( $hook->is_dynamic ) {
				continue;
			}
			if ( ! isset( $new->hooks[ $name ] ) ) {
				$removed[] = $hook;
			}
		}

		// Find added hooks (in new but not in old).
		foreach ( $new->hooks as $name => $hook ) {
			if ( $hook->is_dynamic ) {
				continue;
			}
			if ( ! isset( $old->hooks[ $name ] ) ) {
				$added[] = $hook;
			}
		}

		return new HookDiffResult( $removed, $added );
	}
}
