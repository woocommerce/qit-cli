<?php

namespace SamplePlugin;

class SampleManager {
	public function initialize(): void {
		do_action( 'sample_plugin_initialized' ); // Renamed from sample_plugin_init.
	}

	public function get_items(): array {
		return apply_filters( 'sample_plugin_items', [] );
	}

	// process_item removed; before/after hooks removed.

	public function batch_process( array $items ): void {
		do_action( 'sample_plugin_before_batch', $items );
		// New batch processing logic.
		do_action( 'sample_plugin_after_batch', $items );
	}

	protected function internal_helper(): void {
		// Still protected, should not appear.
	}
}
