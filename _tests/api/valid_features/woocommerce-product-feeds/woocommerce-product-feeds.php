<?php

/*
 * Plugin name: API - Log that the  New Product Editor is enabled.
 */

 add_filter( 'woocommerce_admin_get_feature_config', static function( $features ) {
	if ( 
		array_key_exists( 'new-product-management-experience', $features ) &&
		$features['new-product-management-experience'] === true
	) {
		trigger_error( "New Product Editor is enabled as expected." );
	}

	return $features;
}, 99 );