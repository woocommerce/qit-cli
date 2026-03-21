<?php

namespace SamplePlugin;

class SampleHelper {
	public function format_output( string $text ): string {
		return apply_filters( 'sample_plugin_format_output', $text );
	}

	public function deprecated_method(): void {
		// This will be removed in v2.
	}
}
