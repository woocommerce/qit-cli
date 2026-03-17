<?php

namespace TargetPlugin;

use SamplePlugin\SampleContract;
use SamplePlugin\SampleManager;

// References a removed class (SampleContract was removed in v2).
class MyImplementation implements SampleContract {
	public function execute(): void {
		// Calls a removed method (process_item was removed in v2).
		$manager = new SampleManager();
		$manager->process_item( 'test' );
	}

	public function get_name(): string {
		return 'my-implementation';
	}
}

// Calls a removed function.
$version = \SamplePlugin\sample_plugin_deprecated_function();

// Registers on removed hooks.
add_action( 'sample_plugin_init', function () {
	// This hook was renamed to sample_plugin_initialized in v2.
} );

add_action( 'sample_plugin_before_process', function ( $item ) {
	// This hook was removed in v2.
} );

add_filter( 'sample_plugin_items', function ( $items ) {
	// This hook still exists in v2 — should NOT be flagged.
	return $items;
} );
