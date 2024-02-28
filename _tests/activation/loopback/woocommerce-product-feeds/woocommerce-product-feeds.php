<?php

/*
 * Plugin name: Activation - Plugin A
 */

add_action( 'wp', static function () {
	trigger_error( 'Site URL: ' . get_site_url() );
	$response = wp_remote_get( get_site_url() . '/wp-json/' );

	if ( is_wp_error( $response ) ) {
		trigger_error( 'Loopback error: ' . $response->get_error_message() );
	} else {
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $data['name'] ) ) {
			trigger_error( 'Site name from wp-json: ' . $data['name'] );
		} else {
			trigger_error( 'Site name not found in wp-json response' );
		}
	}
} );
