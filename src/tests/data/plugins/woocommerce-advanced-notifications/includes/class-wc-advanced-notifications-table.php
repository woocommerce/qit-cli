<?php
/**
 * WC_Advanced_Notifications_Table class.
 *
 * @extends WP_List_Table
 */
class WC_Advanced_Notifications_Table extends WP_List_Table {

	/**
	 * __construct function.
	 */
	function __construct() {
		parent::__construct(
			array(
				'singular' => 'email',
				'plural'   => 'emails',
				'ajax'     => false,
			)
		);
	}

	/**
	 * column_default function.
	 *
	 * @access public
	 * @param mixed $item
	 * @param mixed $column_name
	 * @return void
	 */
	function column_default( $item, $column_name ) {
		global $woocommerce, $wpdb;

		switch ( $column_name ) {
			case 'recipient_name':
				$return = '<strong>' . $item->recipient_name . '</strong>';

				$formatted_mails = array();
				$emails          = array_map( 'trim', explode( ',', $item->recipient_email ) );
				foreach ( $emails as $email ) {
					$formatted_mails[] = '<a href="mailto:' . $email . '">' . $email . '</a>';
				}
				$return .= ' <' . implode( ', ', $formatted_mails ) . '>';

				$return = wpautop( $return );

				$return .= '
				<div class="row-actions">
					<span class="edit"><a href="' . admin_url( 'admin.php?page=advanced-notifications&edit=' . $item->notification_id ) . '">' . __( 'Edit', 'woocommerce-advanced-notifications' ) . '</a> | </span><span class="trash"><a class="submitdelete" href="' . wp_nonce_url( admin_url( 'admin.php?page=advanced-notifications&delete=' . $item->notification_id ), 'delete_notification' ) . '">' . __( 'Delete', 'woocommerce-advanced-notifications' ) . '</a></span>
				</div>';

				return $return;
				break;
			case 'recipient_details':
				$return  = $item->recipient_website ? '<p><a href="' . $item->recipient_website . '" rel="nofollow">' . $item->recipient_website . '</a></p>' : '';
				$return .= $item->recipient_phone ? '<p>' . $item->recipient_phone . '</p>' : '';
				$return .= $item->recipient_address ? '<p>' . nl2br( $item->recipient_address ) . '</p>' : '';
				return $return ? $return : '-';
				break;
			case 'notification_type':
				$formatted_types = array();
				$types           = maybe_unserialize( $item->notification_type );
				foreach ( $types as $type ) {
					switch ( $type ) {
						case 'low_stock':
							$formatted_types[] = __( 'Low stock', 'woocommerce-advanced-notifications' );
							break;
						case 'out_of_stock':
							$formatted_types[] = __( 'Out of stock', 'woocommerce-advanced-notifications' );
							break;
						case 'backorders':
							$formatted_types[] = __( 'Backorders', 'woocommerce-advanced-notifications' );
							break;
						case 'purchases':
							$formatted_types[] = __( 'Purchases', 'woocommerce-advanced-notifications' );
							break;
						case 'refunds':
							$formatted_types[] = __( 'Refunds', 'woocommerce-advanced-notifications' );
							break;
					}
				}
				return sizeof( $formatted_types ) ? '<p>' . implode( ', ', $formatted_types ) . '</p>' : '-';
				break;
			case 'notification_plain_text':
			case 'notification_prices':
			case 'notification_totals':
				if ( $item->$column_name == 1 ) {
					return '&#10004;';
				} else {
					return '-';
				}
				break;
			case 'notification_sent_count':
				return sprintf( _n( '1 sent', '%s sent', $item->notification_sent_count, 'woocommerce-advanced-notifications' ), $item->notification_sent_count );
				break;
			case 'notification_triggers':
				$return     = '';
				$categories = array();
				$classes    = array();
				$products   = array();

				$triggers = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}advanced_notification_triggers WHERE notification_id = " . absint( $item->notification_id ) . ';' );

				foreach ( $triggers as $trigger ) {

					if ( '0' === $trigger->object_id ) {
						return '<strong> ' . __( 'All Purchases', 'woocommerce-advanced-notifications' ) . '</strong>';
					} elseif ( 'product_cat' === $trigger->object_type ) {
						$term = get_term( $trigger->object_id, $trigger->object_type );

						if ( ! $term ) {
							continue;
						}

						$categories[] = $term->name;
					} elseif ( 'product_shipping_class' === $trigger->object_type ) {
						$term = get_term( $trigger->object_id, $trigger->object_type );

						if ( ! $term ) {
							continue;
						}

						$classes[] = $term->name;
					} elseif ( 'product' === $trigger->object_type ) {
						if ( get_the_title( $trigger->object_id ) ) {
							$products[] = '<a href="' . admin_url( 'post.php?post=' . $trigger->object_id . '&action=edit' ) . '">' . get_the_title( $trigger->object_id ) . '</a>';
						}
					}
				}

				if ( sizeof( $categories ) > 0 ) {
					$return .= '<p><strong>' . __( 'Categories:', 'woocommerce-advanced-notifications' ) . '</strong> ' . implode( ', ', $categories ) . '</p>';
				}

				if ( sizeof( $classes ) > 0 ) {
					$return .= '<p><strong>' . __( 'Classes:', 'woocommerce-advanced-notifications' ) . '</strong> ' . implode( ', ', $classes ) . '</p>';
				}

				if ( sizeof( $products ) > 0 ) {
					$return .= '<p><strong>' . __( 'Products:', 'woocommerce-advanced-notifications' ) . '</strong> ' . implode( ', ', $products ) . '</p>';
				}

				if ( ! $return ) {
					$return = '-';
				}

				return $return;
				break;
		}
	}

	/**
	 * column_cb function.
	 *
	 * @access public
	 * @param mixed $item
	 * @return void
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="notification_id[]" value="%s" />',
			/*$2%s*/ $item->notification_id
		);
	}

	/**
	 * get_columns function.
	 *
	 * @access public
	 * @return void
	 */
	function get_columns() {
		$columns = array(
			'cb'                      => '<input type="checkbox" />',
			'recipient_name'          => __( 'Recipient', 'woocommerce-advanced-notifications' ),
			'notification_type'       => __( 'Notification Types', 'woocommerce-advanced-notifications' ),
			'notification_triggers'   => __( 'Triggers', 'woocommerce-advanced-notifications' ),
			'notification_plain_text' => __( 'Plain text?', 'woocommerce-advanced-notifications' ),
			'notification_prices'     => __( 'Prices?', 'woocommerce-advanced-notifications' ),
			'notification_totals'     => __( 'Totals?', 'woocommerce-advanced-notifications' ),
			'notification_sent_count' => __( 'Sent count', 'woocommerce-advanced-notifications' ),
		);
		return $columns;
	}

	 /**
	  * Get bulk actions
	  */
	function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'woocommerce-advanced-notifications' ),
		);
		return $actions;
	}

	/**
	 * Process bulk actions
	 */
	function process_bulk_action() {
		global $wpdb;

		if ( ! empty( $_POST ) ) {
			check_admin_referer( 'bulk-emails' );
		}

		if ( ! $this->current_action() ) {
			return;
		}

		$emails = array_map( 'intval', $_POST['notification_id'] );

		if ( $emails ) {

			if ( 'delete' === $this->current_action() ) {

				foreach ( $emails as $email_id ) {

					$email_id = absint( $email_id );

					$wpdb->query( 
						$wpdb->prepare( 
							"DELETE FROM {$wpdb->prefix}advanced_notifications WHERE notification_id = %d;",
							$email_id
						)
					);

					$wpdb->query( 
						$wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}advanced_notification_triggers WHERE notification_id = %d;",
							$email_id
						)
					);

				}
			}

			echo '<div class="updated"><p>' . esc_html__( 'Notifications updated', 'woocommerce-advanced-notifications' ) . '</p></div>';
		}
	}

	/**
	 * Uses the screen options to figure out how many notifications to show per page
	 */
	function per_page() {
		$screen = get_current_screen();
		$user   = get_current_user_id();
		$option = $screen->get_option( 'per_page', 'option' );

		$per_page = get_user_meta( $user, $option, true );

		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		return $per_page;
	}

	/**
	 * prepare_items function.
	 *
	 * @access public
	 * @return void
	 */
	function prepare_items() {
		global $wpdb;

		/**
		 * Init column headers
		 */
		$this->_column_headers = array( $this->get_columns(), get_hidden_columns( 'woocommerce_page_advanced-notifications' ), array() );

		/**
		 * Process bulk actions
		 */
		$this->process_bulk_action();

		/**
		 * Figure out how many items to show per page
		 */
		$per_page = $this->per_page();

		/**
		 * Get emails
		 */
		$count = $wpdb->get_var( "SELECT COUNT(notification_id) FROM {$wpdb->prefix}advanced_notifications;" );

		$this->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}advanced_notifications LIMIT %d,%d", ( $per_page * ( $this->get_pagenum() - 1 ) ), $per_page ) );

		/**
		 * Handle pagination
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);
	}

}
