<?php

/*
 * Plugin name: E2E - Delete products after creation. This should cause some failures on E2E tests.
 */

add_action( 'save_post_product', static function ( $post_id ) {
	// Delete the post after it's created.
	wp_delete_post( $post_id, true );
} );