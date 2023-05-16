<?php

/*
 * Plugin name: Activation - Assert that we can run a PHP 7.4 test on WordPress 6.2.1.
 */

add_action( 'wp', static function () {
	if ( is_cart() ) {
		call_to_undefined_function();
	}

	trigger_error( 'Warning on all requests', E_USER_WARNING );
} );

add_action( 'init', static function() {
	trigger_error( 'Notice on all requests' );
} );