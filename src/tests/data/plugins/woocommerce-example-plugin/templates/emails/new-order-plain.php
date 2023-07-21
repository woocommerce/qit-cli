<?php
/**
 * New order email (plain)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

printf( __( 'Hi %s,', 'woocommerce-example-plugin' ), esc_html( $recipient_name ) );

echo "\n\n";

/* translators: 1: first name 2: last name */
printf( __( 'You have received an order from %s %s:', 'woocommerce-example-plugin' ), version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_first_name : $order->get_billing_first_name(), version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->billing_last_name : $order->get_billing_last_name() );

echo "\n\n";

echo "============================================================\n";

printf( __( 'Order: %s', 'woocommerce-example-plugin' ), esc_html( $order->get_order_number() ) );

echo "\n";

printf( '%s', version_compare( WC_VERSION, '3.0.0', '<' ) ? date_i18n( __( 'jS F Y', 'woocommerce-example-plugin' ), strtotime( $order->order_date ) ) : wc_format_datetime( $order->get_date_created() ) );

echo "\n";

echo "============================================================";

$displayed_total = 0;

foreach ( $order->get_items() as $item_id => $item ) {

	if ( is_callable( array( $item, 'get_product' ) ) ) {
		$_product = $item->get_product();
	} else {
		$_product = $order->get_product_from_item( $item );
	}

	$display = false;

	$product_id = $_product->is_type( 'variation' ) && version_compare( WC_VERSION, '3.0', '>=' ) ? $_product->get_parent_id() : $_product->get_id();

	if ( $triggers['all'] || in_array( $product_id, $triggers['product_ids'] ) || in_array( $_product->get_shipping_class_id(), $triggers['shipping_classes'] ) )
		$display = true;

	if ( ! $display ) {

		$cats = wp_get_post_terms( $product_id, 'product_cat', array( "fields" => "ids" ) );

		if ( sizeof( array_intersect( $cats, $triggers['product_cats'] ) ) > 0 ) {
			$display = true;
		}

	}

	if ( ! $display && ! empty( $show_all_items ) ) {
		$display = true;
	}

	if ( ! $display ) {
		continue;
	}

	$displayed_total += $order->get_line_total( $item, true );

	$item_meta = version_compare( WC_VERSION, '3.0', '<' ) ? new WC_Order_Item_Meta( $item ) : new WC_Order_Item_Product( $item_id );

	// Product name
	echo "\n" . apply_filters( 'woocommerce_order_product_title', $item['name'], $_product );

	// SKU
	echo $_product->get_sku() ? ' (#' . $_product->get_sku() . ')' : '';

	if ( $show_prices )
		echo " (" . $order->get_line_subtotal( $item ) . ")";

	echo " X " . $item['qty'];

	// allow other plugins to add additional product information here
	do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

	// Variation
	if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
		echo $item_meta->meta ? ( "\n --> " . str_replace( "\n", '', $item_meta->display( true, true ) ) ) : '';
	} else {
		echo strip_tags( wc_display_item_meta( $item, array(
			'before'    => "\n- ",
			'separator' => "\n- ",
			'after'     => "",
			'echo'      => false,
			'autop'     => false,
		) ) );
	}

	// File URLs
	if ( $show_download_links ) {
		version_compare( WC_VERSION, '3.0.0', '<' ) ? $order->display_item_downloads( $item ) : wc_display_item_downloads( $item );
	}

	// allow other plugins to add additional product information here
	do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );

	echo "\n";

}

echo "============================================================\n";

if ( $show_totals ) {

	$totals = $order->get_order_item_totals();
	if ( $totals ) {
		foreach ( $totals as $total ) {
			echo $total['label'] . ' ';
			echo preg_replace( "/&#?[a-z0-9]{2,8};/i", "", $total['value'] );
			echo "\n";
		}
	} else {
		// Only show the total for displayed items
		echo __( 'Total', 'woocommerce-example-plugin' ) . ': ';
		echo $displayed_total;
		echo "\n";
	}

}

if ( $order->get_customer_note() ) {
	echo __( 'Note', 'woocommerce-example-plugin' ) . ': ';
	echo $order->get_customer_note();
	echo "\n";
}

echo "\n\n";

/**
* @hooked WC_Emails::customer_details() Shows customer details
* @hooked WC_Emails::email_address() Shows email address
*/
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n\n";

echo "Regards,\n" . $blogname;
