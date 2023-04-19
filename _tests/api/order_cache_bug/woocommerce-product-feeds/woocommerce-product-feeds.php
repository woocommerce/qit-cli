<?php

/*
 * Plugin name: API - Order Cache Bug
 */

// Only trigger on API test requests.
if ( php_sapi_name() === 'cli' || ! isset( $_SERVER['PHP_AUTH_USER'] ) || ! isset( $_SERVER['PHP_AUTH_PW'] ) ) {
	return;
}

add_action(
	'woocommerce_new_order',
	function ( $order_id ) {
		// this makes the cache store a specific order class instance, but it's quickly replaced by a generic one
		// as we're in the middle of a save and this gets executed before the logic in WC_Abstract_Order.
		$order = wc_get_order( $order_id );
	}
);

add_action( 'init', static function() {
	$order = new \WC_Order();
	$order->save();

	$order = wc_get_order( $order->get_id() );

	$expected = \Automattic\WooCommerce\Admin\Overrides\Order::class;

	if ( get_class( $order ) !== $expected ) {
		if ( is_object( $order ) ) {
			$actual = get_class( $order );
		} else {
			$actual = gettype( $order );

			if ( is_scalar( $actual ) ) {
				$actual .= " $actual";
			}
		}
		trigger_error( sprintf( 'Expected $order to be %s. Got %s instead.', $expected, $actual ), E_USER_ERROR );
	} else {
		trigger_error( sprintf( '$order is %s as expected.', $expected ) );
	}
} );