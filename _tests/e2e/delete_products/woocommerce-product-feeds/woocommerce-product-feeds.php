<?php

/*
 * Plugin name: E2E - Delete products after creation. This should cause some failures on E2E tests.
 */

// Delete the post after it's created.
add_action( 'save_post_product', static function ( $post_id ) {
	add_action( 'shutdown', static function () use ( $post_id ) {
		wp_delete_post( $post_id, true );
	} );
} );