<?php

namespace QIT_CLI\BreakingChanges\Models;

class SymbolDiffResult {
	/** @var SymbolInfo[] Symbols present in old but not in new */
	public array $removed;

	/** @var SymbolInfo[] Symbols present in new but not in old */
	public array $added;

	/**
	 * @param SymbolInfo[] $removed
	 * @param SymbolInfo[] $added
	 */
	public function __construct( array $removed = [], array $added = [] ) {
		$this->removed = $removed;
		$this->added   = $added;
	}

	public function has_removals(): bool {
		return count( $this->removed ) > 0;
	}
}
