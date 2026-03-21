<?php

namespace QIT_CLI\BreakingChanges\Models;

class HookDiffResult {
	/** @var HookInfo[] Hooks present in old but not in new */
	public array $removed;

	/** @var HookInfo[] Hooks present in new but not in old */
	public array $added;

	/**
	 * @param HookInfo[] $removed
	 * @param HookInfo[] $added
	 */
	public function __construct( array $removed = [], array $added = [] ) {
		$this->removed = $removed;
		$this->added   = $added;
	}

	public function has_removals(): bool {
		return count( $this->removed ) > 0;
	}
}
