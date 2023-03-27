<?php

/*
 * Plugin name: Activation - Plugin A
 */

add_action( 'wp', static function () {
	if ( is_cart() ) {
		call_to_undefined_function();
	}

	trigger_error( 'Warning on all requests', E_USER_WARNING );
} );

trigger_error( 'Notice on all requests' );