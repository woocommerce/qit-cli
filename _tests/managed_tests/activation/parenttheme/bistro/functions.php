<?php

// Dont't flag requests with $_GET['nonce'] as they can't be snapshotted, as they always change.
if ( isset( $_GET['nonce'] ) ) {
	return;
}

add_action( 'wp', static function () {
	if ( is_cart() ) {
		call_to_undefined_function();
	}

	trigger_error( 'Warning on all requests - Parent Theme', E_USER_WARNING );
} );

add_action( 'init', static function() {
	trigger_error( 'Notice on all requests - Parent Theme' );
} );