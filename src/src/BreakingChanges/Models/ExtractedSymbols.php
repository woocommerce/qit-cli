<?php

namespace QIT_CLI\BreakingChanges\Models;

class ExtractedSymbols {
	/** @var array<string, SymbolInfo> Keyed by FQN */
	public array $classes = [];

	/** @var array<string, SymbolInfo> Keyed by Class::method */
	public array $methods = [];

	/** @var array<string, SymbolInfo> Keyed by FQN */
	public array $functions = [];

	/** @var array<string, SymbolInfo> Keyed by FQN */
	public array $constants = [];

	/** @var array<string, HookInfo> Keyed by hook name */
	public array $hooks = [];

	/** @var string[] Parse or extraction warnings */
	public array $warnings = [];

	/** @var int Count of dynamic hooks encountered */
	public int $dynamic_hook_count = 0;

	public function add_class( SymbolInfo $symbol ): void {
		$this->classes[ $symbol->get_key() ] = $symbol;
	}

	public function add_method( SymbolInfo $symbol ): void {
		$this->methods[ $symbol->get_key() ] = $symbol;
	}

	public function add_function( SymbolInfo $symbol ): void {
		$this->functions[ $symbol->get_key() ] = $symbol;
	}

	public function add_constant( SymbolInfo $symbol ): void {
		$this->constants[ $symbol->get_key() ] = $symbol;
	}

	public function add_hook( HookInfo $hook ): void {
		$this->hooks[ $hook->name ] = $hook;
	}

	public function add_warning( string $warning ): void {
		$this->warnings[] = $warning;
	}

	/**
	 * Merge another ExtractedSymbols into this one.
	 */
	public function merge( ExtractedSymbols $other ): void {
		$this->classes             = array_merge( $this->classes, $other->classes );
		$this->methods             = array_merge( $this->methods, $other->methods );
		$this->functions           = array_merge( $this->functions, $other->functions );
		$this->constants           = array_merge( $this->constants, $other->constants );
		$this->hooks               = array_merge( $this->hooks, $other->hooks );
		$this->warnings            = array_merge( $this->warnings, $other->warnings );
		$this->dynamic_hook_count += $other->dynamic_hook_count;
	}
}
