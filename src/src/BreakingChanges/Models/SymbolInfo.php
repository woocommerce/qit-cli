<?php

namespace QIT_CLI\BreakingChanges\Models;

class SymbolInfo {
	/** @var string Fully qualified name */
	public string $name;

	/** @var string One of: class, method, function, constant */
	public string $type;

	/** @var string File path relative to plugin root */
	public string $file;

	/** @var int Line number */
	public int $line;

	/** @var string Visibility: public, protected, private */
	public string $visibility;

	/** @var string|null Parent class FQN (for methods) */
	public ?string $parent_class;

	public function __construct(
		string $name,
		string $type,
		string $file,
		int $line,
		string $visibility = 'public',
		?string $parent_class = null
	) {
		$this->name         = $name;
		$this->type         = $type;
		$this->file         = $file;
		$this->line         = $line;
		$this->visibility   = $visibility;
		$this->parent_class = $parent_class;
	}

	/**
	 * Get a unique key for deduplication and comparison.
	 */
	public function get_key(): string {
		if ( $this->type === 'method' && $this->parent_class !== null ) {
			return $this->parent_class . '::' . $this->name;
		}

		return $this->name;
	}
}
