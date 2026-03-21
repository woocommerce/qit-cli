<?php

namespace SamplePlugin;

class SampleRegistry {
	public function register( string $key, $value ): void {
		do_action( 'sample_plugin_registered', $key, $value );
	}

	public function get( string $key ) {
		return apply_filters( 'sample_plugin_registry_get', null, $key );
	}
}
