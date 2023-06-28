<?php
if ( ! class_exists( 'WC_Abstract_Privacy' ) ) {
	return;
}

class WC_Advanced_Notifications_Privacy extends WC_Abstract_Privacy {
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		parent::__construct( __( 'Advanced Notifications', 'woocommerce-advanced-notifications' ) );

		$this->add_exporter( 'woocommerce-advanced-notifications', __( 'WooCommerce Advanced Notifications Data', 'woocommerce-advanced-notifications' ), array( $this, 'advanced_notifications_exporter' ) );

		$this->add_eraser( 'woocommerce-advanced-notifications', __( 'WooCommerce Advanced Notifications Data', 'woocommerce-advanced-notifications' ), array( $this, 'advanced_notifications_eraser' ) );
	}

	/**
	 * Returns a list of Advanced Notifications.
	 *
	 * @param string  $email_address
	 * @param int     $page
	 *
	 * @return array
	 */
	protected function get_notifications( $email_address, $page ) {
		global $wpdb;

		$limit = 10;

		$notifications = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}advanced_notifications WHERE recipient_email = %s LIMIT %d, %d",
			$email_address,
			( $page - 1 ) * $limit,
			$limit
		) );

		return $notifications;
	}

	/**
	 * Gets the message of the privacy to display.
	 *
	 */
	public function get_privacy_message() {
		return wpautop( sprintf( __( 'By using this extension, you may be storing personal data or sharing data with an external service. <a href="%s" target="_blank">Learn more about how this works, including what you may want to include in your privacy policy.</a>', 'woocommerce-advanced-notifications' ), 'https://docs.woocommerce.com/document/marketplace-privacy/#woocommerce-advanced-notifications' ) );
	}

	/**
	 * Handle exporting data for Advanced Notifications.
	 *
	 * @param string $email_address E-mail address to export.
	 * @param int    $page          Pagination of data.
	 *
	 * @return array
	 */
	public function advanced_notifications_exporter( $email_address, $page = 1 ) {
		$done           = false;
		$data_to_export = array();
		$notifications  = $this->get_notifications( $email_address, (int) $page );

		$done = true;

		if ( 0 < count( $notifications ) ) {
			foreach ( $notifications as $notification ) {
				$data_to_export[] = array(
					'group_id'    => 'woocommerce_advanced_notifications',
					'group_label' => __( 'Advanced Notifications', 'woocommerce-advanced-notifications' ),
					'item_id'     => 'advanced-notification-' . $notification->notification_id,
					'data'        => array(
						array(
							'name'  => __( 'Recipient name', 'woocommerce-advanced-notifications' ),
							'value' => $notification->recipient_name,
						),
						array(
							'name'  => __( 'Recipient address', 'woocommerce-advanced-notifications' ),
							'value' => $notification->recipient_address,
						),
						array(
							'name'  => __( 'Recipient phone', 'woocommerce-advanced-notifications' ),
							'value' => $notification->recipient_phone,
						),
						array(
							'name'  => __( 'Recipient website', 'woocommerce-advanced-notifications' ),
							'value' => $notification->recipient_website,
						),
					),
				);
			}

			$done = 10 > count( $notifications );
		}

		return array(
			'data' => $data_to_export,
			'done' => $done,
		);
	}

	/**
	 * Finds and erases notifications data by email address.
	 *
	 * @since 3.4.0
	 * @param string $email_address The user email address.
	 * @param int    $page  Page.
	 * @return array An array of personal data in name value pairs
	 */
	public function advanced_notifications_eraser( $email_address, $page ) {
		global $wpdb;

		$notifications  = $this->get_notifications( $email_address, 1 );
		$items_removed  = false;
		$items_retained = false;
		$messages       = array();

		foreach ( $notifications as $notification ) {
			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->prefix}advanced_notifications WHERE notification_id = %d",
				$notification->notification_id
			) );

			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->prefix}advanced_notification_triggers WHERE notification_id = %d",
				$notification->notification_id
			) );

			$items_removed = true;
		}

		if ( $items_removed ) {
			$messages[] = __( 'Advanced Notifications Data Erased.', 'woocommerce-advanced-notifications' );
		}

		// Tell core if we have more notifications to work on still
		$done = count( $notifications ) < 10;

		return array(
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => $done,
		);
	}
}

new WC_Advanced_Notifications_Privacy();
