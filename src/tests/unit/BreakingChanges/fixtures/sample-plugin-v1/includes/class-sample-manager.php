<?php

namespace SamplePlugin;

class SampleManager {
	public function initialize(): void {
		do_action( 'sample_plugin_init' );
	}

	public function get_items(): array {
		return apply_filters( 'sample_plugin_items', [] );
	}

	public function process_item( $item ): void {
		do_action( 'sample_plugin_before_process', $item );
		// Processing logic.
		do_action( 'sample_plugin_after_process', $item );
	}

	protected function internal_helper(): void {
		// Should not appear in public API.
	}

	private function private_method(): void {
		// Should not appear in public API.
	}
}
