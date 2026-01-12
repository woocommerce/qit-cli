<?php

/*
 * Plugin name: Validation - Main Plugin
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * Tested up to: 5.8
 * WC requires at least: 5.8
 * WC tested up to: 5.8
 */

// Declare feature compatibility for testing compatible/incompatible detection.
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		// Declare compatible with HPOS.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		// Declare incompatible with cart/checkout blocks.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
	}
} );