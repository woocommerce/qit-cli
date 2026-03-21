<?php

namespace SamplePlugin;

function sample_plugin_get_version(): string {
	return SAMPLE_PLUGIN_VERSION;
}

// sample_plugin_deprecated_function removed in v2.

function sample_plugin_helper( $data ) {
	return apply_filters( 'sample_plugin_helper_result', $data );
}

function sample_plugin_new_utility( string $key ): string {
	return apply_filters( 'sample_plugin_utility_result', $key );
}
