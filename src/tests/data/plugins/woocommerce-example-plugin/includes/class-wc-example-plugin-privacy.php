<?php
if ( ! class_exists( 'WC_Abstract_Privacy' ) ) {
	return;
}

class WC_Example_Plugin_Privacy extends WC_Abstract_Privacy {
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		parent::__construct( __( 'Example Plugin', 'woocommerce-example-plugin' ) );

		$this->add_exporter( 'woocommerce-example-plugin', __( 'WooCommerce Example Plugin Data', 'woocommerce-example-plugin' ), array( $this, 'example_plugin_exporter' ) );

		$this->add_eraser( 'woocommerce-example-plugin', __( 'WooCommerce Example Plugin Data', 'woocommerce-example-plugin' ), array( $this, 'example_plugin_eraser' ) );
	}

	/**
	 * Returns a list of Example Plugin.
	 *
	 * @param string  $email_address
	 * @param int     $page
	 *
	 * @return array
	 */
	protected function get_plugin( $email_address, $page ) {
		global $wpdb;

		$limit = 10;

		$plugin = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}example_plugin WHERE recipient_email = %s LIMIT %d, %d",
			$email_address,
			( $page - 1 ) * $limit,
			$limit
		) );

		return $plugin;
	}

	/**
	 * Gets the message of the privacy to display.
	 *
	 */
	public function get_privacy_message() {
		return wpautop( sprintf( __( 'By using this extension, you may be storing personal data or sharing data with an external service. <a href="%s" target="_blank">Learn more about how this works, including what you may want to include in your privacy policy.</a>', 'woocommerce-example-plugin' ), 'https://docs.woocommerce.com/document/marketplace-privacy/#woocommerce-example-plugin' ) );
	}

	/**
	 * Handle exporting data for Example Plugin.
	 *
	 * @param string $email_address E-mail address to export.
	 * @param int    $page          Pagination of data.
	 *
	 * @return array
	 */
	public function example_plugin_exporter( $email_address, $page = 1 ) {
		$done           = false;
		$data_to_export = array();
		$plugin  = $this->get_plugin( $email_address, (int) $page );

		$done = true;

		if ( 0 < count( $plugin ) ) {
			foreach ( $plugin as $notification ) {
				$data_to_export[] = array(
					'group_id'    => 'woocommerce_example_plugin',
					'group_label' => __( 'Example Plugin', 'woocommerce-example-plugin' ),
					'item_id'     => 'example-notification-' . $notification->notification_id,
					'data'        => array(
						array(
							'name'  => __( 'Recipient name', 'woocommerce-example-plugin' ),
							'value' => $notification->recipient_name,
						),
						array(
							'name'  => __( 'Recipient address', 'woocommerce-example-plugin' ),
							'value' => $notification->recipient_address,
						),
						array(
							'name'  => __( 'Recipient phone', 'woocommerce-example-plugin' ),
							'value' => $notification->recipient_phone,
						),
						array(
							'name'  => __( 'Recipient website', 'woocommerce-example-plugin' ),
							'value' => $notification->recipient_website,
						),
					),
				);
			}

			$done = 10 > count( $plugin );
		}

		return array(
			'data' => $data_to_export,
			'done' => $done,
		);
	}

	/**
	 * Finds and erases plugin data by email address.
	 *
	 * @since 3.4.0
	 * @param string $email_address The user email address.
	 * @param int    $page  Page.
	 * @return array An array of personal data in name value pairs
	 */
	public function example_plugin_eraser( $email_address, $page ) {
		global $wpdb;

		$plugin  = $this->get_plugin( $email_address, 1 );
		$items_removed  = false;
		$items_retained = false;
		$messages       = array();

		foreach ( $plugin as $notification ) {
			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->prefix}example_plugin WHERE notification_id = %d",
				$notification->notification_id
			) );

			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->prefix}example_notification_triggers WHERE notification_id = %d",
				$notification->notification_id
			) );

			$items_removed = true;
		}

		if ( $items_removed ) {
			$messages[] = __( 'Example Plugin Data Erased.', 'woocommerce-example-plugin' );
		}

		// Tell core if we have more plugin to work on still
		$done = count( $plugin ) < 10;

		return array(
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => $done,
		);
	}
}

new WC_Example_Plugin_Privacy();
