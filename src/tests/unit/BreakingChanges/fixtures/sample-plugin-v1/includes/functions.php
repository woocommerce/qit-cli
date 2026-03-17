<?php

namespace SamplePlugin;

function sample_plugin_get_version(): string {
	return SAMPLE_PLUGIN_VERSION;
}

function sample_plugin_deprecated_function(): void {
	// This function will be removed in v2.
}

function sample_plugin_helper( $data ) {
	return apply_filters( 'sample_plugin_helper_result', $data );
}
