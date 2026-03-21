<?php

namespace QIT_CLI\BreakingChanges\Models;

class HookInfo {
	/** @var string Hook name */
	public string $name;

	/** @var string One of: action, filter */
	public string $type;

	/** @var string File path relative to plugin root */
	public string $file;

	/** @var int Line number */
	public int $line;

	/** @var bool Whether the hook name is dynamically constructed */
	public bool $is_dynamic;

	/** @var int Number of arguments passed to the hook */
	public int $arg_count;

	public function __construct(
		string $name,
		string $type,
		string $file,
		int $line,
		bool $is_dynamic = false,
		int $arg_count = 0
	) {
		$this->name       = $name;
		$this->type       = $type;
		$this->file       = $file;
		$this->line       = $line;
		$this->is_dynamic = $is_dynamic;
		$this->arg_count  = $arg_count;
	}
}
