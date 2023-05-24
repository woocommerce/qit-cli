<?php

/*
 * Plugin name: API - Trigger failure when New Product Editor is not enabled.
 */

 add_action( 'woocommerce_admin_get_feature_config', static function( $features ) {
	if ( 
		array_key_exists( 'new-product-management-experience', $features ) &&
		$features['new-product-management-experience'] === true
	) {
		trigger_error( "New Product Editor is enabled as expected." );
	}
}, 99 );