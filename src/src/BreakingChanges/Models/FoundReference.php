<?php

namespace QIT_CLI\BreakingChanges\Models;

class FoundReference {
	/** @var string The symbol or hook name that was referenced */
	public string $name;

	/** @var string Reference type: class_usage, static_call, function_call, constant_access, hook_registration */
	public string $type;

	/** @var string File path relative to plugin root */
	public string $file;

	/** @var int Line number */
	public int $line;

	/** @var string Short code context snippet */
	public string $context;

	public function __construct(
		string $name,
		string $type,
		string $file,
		int $line,
		string $context = ''
	) {
		$this->name    = $name;
		$this->type    = $type;
		$this->file    = $file;
		$this->line    = $line;
		$this->context = $context;
	}
}
