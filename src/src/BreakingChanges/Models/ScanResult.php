<?php

namespace QIT_CLI\BreakingChanges\Models;

class ScanResult {
	/** @var string Plugin slug that was scanned */
	public string $plugin_slug;

	/** @var FoundReference[] References to removed symbols/hooks */
	public array $references;

	/** @var string[] Warnings encountered during scanning */
	public array $warnings;

	/**
	 * @param string           $plugin_slug
	 * @param FoundReference[] $references
	 * @param string[]         $warnings
	 */
	public function __construct( string $plugin_slug, array $references = [], array $warnings = [] ) {
		$this->plugin_slug = $plugin_slug;
		$this->references  = $references;
		$this->warnings    = $warnings;
	}

	public function has_breaking_references(): bool {
		return count( $this->references ) > 0;
	}
}
