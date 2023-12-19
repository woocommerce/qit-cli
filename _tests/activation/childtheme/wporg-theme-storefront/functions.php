<?php

add_action( 'wp', static function () {
	if ( is_cart() ) {
		call_to_undefined_function();
	}

	trigger_error( 'Warning on all requests - Child theme', E_USER_WARNING );
} );

add_action( 'init', static function() {
	trigger_error( 'Notice on all requests - Child theme' );
} );