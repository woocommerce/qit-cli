<?php

namespace QIT_CLI\BreakingChanges\Models;

class DiffResult {
	public SymbolDiffResult $symbols;
	public HookDiffResult $hooks;

	public function __construct( SymbolDiffResult $symbols, HookDiffResult $hooks ) {
		$this->symbols = $symbols;
		$this->hooks   = $hooks;
	}

	public function has_removals(): bool {
		return $this->symbols->has_removals() || $this->hooks->has_removals();
	}
}
