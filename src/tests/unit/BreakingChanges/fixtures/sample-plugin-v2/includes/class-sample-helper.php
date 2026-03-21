<?php

namespace SamplePlugin;

class SampleHelper {
	public function format_output( string $text ): string {
		return apply_filters( 'sample_plugin_format_output', $text );
	}

	// deprecated_method removed in v2.

	public function sanitize_output( string $text ): string {
		return apply_filters( 'sample_plugin_sanitize_output', $text );
	}
}
