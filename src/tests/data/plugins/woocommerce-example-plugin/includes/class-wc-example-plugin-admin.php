<?php

/**
 * WC_Example_Plugin_Admin class.
 */
class WC_Example_Plugin_Admin {

	private $editing;
	private $editing_id;

	/**
	 * __construct function.
	 */
	function __construct() {
		// Admin menu
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_filter( 'set-screen-option', array( $this, 'set_screen_options' ), 10, 3 );
		add_filter( 'manage_woocommerce_page_example-plugin_columns', array( $this, 'pass_custom_columns' ) );

		// Meta
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'write_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'write_panel_save' ) );
	}

	/**
	 * Screen ids
	 */
	public function screen_ids( $ids ) {
		$wc_screen_id = strtolower( __( 'WooCommerce', 'woocommerce-example-plugin' ) );

		$ids[] = $wc_screen_id . '_page_example-plugin';

		return $ids;
	}

	/**
	 * admin_menu function.
	 */
	function admin_menu() {
		$page = add_submenu_page( 'woocommerce', __( 'Example Plugin', 'woocommerce-example-plugin' ), __( 'Plugin', 'woocommerce-example-plugin' ), 'manage_woocommerce', 'example-plugin', array( $this, 'admin_screen' ) );

		add_action( "load-$page", array( $this, 'screen_options' ) );
		if ( function_exists( 'woocommerce_admin_css' ) ) {
			add_action( 'admin_print_styles-'. $page, 'woocommerce_admin_css' );
		}
		add_action( 'admin_print_styles-'. $page, array( $this, 'admin_enqueue' ) );
	}

	/**
	 * Admin enqueue function.
	 */
	public function admin_enqueue() {
		wp_enqueue_style( 'plugin_css', plugins_url( 'assets/css/admin.css', __DIR__ ), array(), WC_EXAMPLE_PLUGIN_VERSION );
	}

	/**
	 * screen options for the example plugin page
	 */
	function screen_options() {
		add_screen_option( 'per_page', array( 'default' => 25, 'option' => 'woocommerce_example_plugin_per_page' ) );
	}

	/**
	 * Saves our custom screen options to the database
	 */
	function set_screen_options( $status, $option, $value ) {
		if ( 'woocommerce_example_plugin_per_page' === $option ) {
			return $value;
		}
		return $status;
	}

	/**
	 * Tells the screen options which columns we can hide
	 */
	public function pass_custom_columns( $columns ) {
		return array(
			'notification_type' => __( 'Notification Types', 'woocommerce-example-plugin' ),
			'notification_triggers' => __( 'Triggers', 'woocommerce-example-plugin' ),
			'notification_plain_text' => __( 'Plain text?', 'woocommerce-example-plugin' ),
			'notification_prices' => __( 'Prices?', 'woocommerce-example-plugin' ),
			'notification_totals' => __( 'Totals?', 'woocommerce-example-plugin' ),
			'notification_sent_count' => __( 'Sent count', 'woocommerce-example-plugin' ),
		);
	}

	/**
	 * write_panel function.
	 */
	function write_panel() {
		global $wpdb, $post;

		$plugin = $wpdb->get_results( "
			SELECT * FROM {$wpdb->prefix}example_plugin
		" );

		if ( ! $plugin ) {
			return;
		}

		echo '<div class="options_group">';

		$triggers = $wpdb->get_col( "SELECT notification_id FROM {$wpdb->prefix}example_notification_triggers WHERE object_id = " . absint( $post->ID ) . " AND object_type = 'product';" );
		?>
		<p class="form-field">
			<label><?php esc_html_e( 'Plugin', 'woocommerce-example-plugin' ); ?></label>
			<select id="notification_recipients" name="notification_recipients[]" multiple="multiple" style="width:300px;" data-placeholder="<?php esc_attr_e( 'Choose recipients for this product&hellip;', 'woocommerce-example-plugin' ); ?>" class="wc-enhanced-select">
				<?php
				foreach ( $plugin as $notification ) {
					echo '<option value="' . esc_attr( $notification->notification_id ) . '" ' . selected( in_array( $notification->notification_id, $triggers, true ), true, false ) . '>' . esc_html( $notification->recipient_name ) . '</option>';
				}
				?>
			</select>
		</p>
		<?php

		echo '</div>';
	}

	/**
	 * write_panel_save function.
	 *
	 * @param mixed $post_id
	 */
	function write_panel_save( $post_id ) {
		global $wpdb;

		$recipients = array( 0 );
		$triggers   = array();

		// Get new
		if ( isset( $_POST['notification_recipients'] ) ) {
			if ( is_array( $_POST['notification_recipients'] ) ) {
				foreach ( $_POST['notification_recipients'] as $recipient ) {
					$recipient    = absint( $recipient );
					$recipients[] = $recipient;
					$triggers[]   = "( {$recipient}, {$post_id}, 'product' )";
				}
			}
		}

		// Delete current triggers for this product
		$wpdb->query( "
			DELETE FROM {$wpdb->prefix}example_notification_triggers
			WHERE object_id = " . absint( $post_id ) . "
			AND object_type = 'product'
			AND object_id NOT IN ( " . implode( ',', $recipients ) . " )
		" );

		// Save new
		if ( sizeof( $triggers ) > 0 ) {
			$wpdb->query( "
				INSERT INTO {$wpdb->prefix}example_notification_triggers ( notification_id, object_id, object_type )
				VALUES " . implode( ',', $triggers ) . ";
			" );
		}

	}

	/**
	 * admin_screen function.
	 */
	function admin_screen() {
		global $wpdb;

		$admin = $this;

		if ( ! empty( $_GET['delete'] ) ) {

			check_admin_referer( 'delete_notification' );

			$delete = absint( $_GET['delete'] );

			$wpdb->query( "DELETE FROM {$wpdb->prefix}example_plugin WHERE notification_id = {$delete};" );
			$wpdb->query( "DELETE FROM {$wpdb->prefix}example_notification_triggers WHERE notification_id = {$delete};" );

			echo '<div class="updated fade"><p>' . __( 'Notification deleted successfully', 'woocommerce-example-plugin' ) . '</p></div>';

		} elseif ( ! empty( $_GET['add'] ) ) {

			if ( ! empty( $_POST['save_recipient'] ) ) {

				check_admin_referer( 'woocommerce_save_recipient' );

				$result = $this->add_recipient();

				if ( is_wp_error( $result ) ) {
					echo '<div class="error"><p>' . $result->get_error_message() . '</p></div>';
				} elseif ( $result ) {

					echo '<div class="updated fade"><p>' . __( 'Notification saved successfully', 'woocommerce-example-plugin' ) . '</p></div>';

				}

			}

			include_once( 'views/admin-screen-edit.php' );
			return;

		} elseif ( ! empty( $_GET['edit'] ) ) {

			$this->editing_id = absint( $_GET['edit'] );
			$this->editing = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}example_plugin WHERE notification_id = " . $this->editing_id . ";" );

			if ( ! empty( $_POST['save_recipient'] ) ) {

				check_admin_referer( 'woocommerce_save_recipient' );

				$result = $this->save_recipient();

				if ( is_wp_error( $result ) ) {
					echo '<div class="error"><p>' . $result->get_error_message() . '</p></div>';
				} elseif ( $result ) {

					echo '<div class="updated fade"><p>' . __( 'Notification saved successfully', 'woocommerce-example-plugin' ) . '</p></div>';

				}

			}

			include_once( 'views/admin-screen-edit.php' );
			return;
		}

		if ( ! empty( $_GET['success'] ) ) {
			echo '<div class="updated fade"><p>' . __( 'Notification saved successfully', 'woocommerce-example-plugin' ) . '</p></div>';
		}

		if ( ! empty( $_GET['deleted'] ) ) {
			echo '<div class="updated fade"><p>' . __( 'Notification deleted successfully', 'woocommerce-example-plugin' ) . '</p></div>';
		}

		if ( ! class_exists( 'WP_List_Table' ) ) {
			include_once( ABSPATH . 'wp-admin/views/class-wp-list-table.php' );
		}
		include_once( 'class-wc-example-plugin-table.php' );
		include_once( 'views/admin-screen.php' );
	}


	/**
	 * field_value function.
	 *
	 * @param string $name
	 */
	function field_value( $name ) {
		global $wpdb;

		$value = '';

		if ( isset( $this->editing->$name ) ) {

			$value = $this->editing->$name;

		} elseif ( $name == 'notification_triggers' ) {

			$value = $wpdb->get_col( "SELECT object_id FROM {$wpdb->prefix}example_notification_triggers WHERE notification_id = " . absint( $this->editing_id ) . ";" );

		}

		$value = maybe_unserialize( $value );

		if ( isset( $_POST[ $name ] ) ) {
			$value = $_POST[ $name ];
		}

		if ( is_array( $value ) ) {
			$value = array_map( 'trim', array_map( 'esc_attr', array_map( 'stripslashes', $value ) ) );
		} else {
			$value = trim( esc_attr( stripslashes( $value ) ) );
		}

		return $value;
	}

	/**
	 * add_recipient function.
	 */
	function add_recipient() {
		global $wpdb;

		$recipient_name          = sanitize_text_field( stripslashes( $_POST['recipient_name'] ) );
		$recipient_email         = sanitize_text_field( stripslashes( $_POST['recipient_email'] ) );
		$recipient_address       = sanitize_text_field( stripslashes( $_POST['recipient_address'] ) );
		$recipient_phone         = sanitize_text_field( stripslashes( $_POST['recipient_phone'] ) );
		$recipient_website       = sanitize_text_field( stripslashes( $_POST['recipient_website'] ) );
		$notification_type       = isset( $_POST['notification_type'] ) ? array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', (array) $_POST['notification_type'] ) ) ) : array();
		$notification_plain_text = ! empty( $_POST['notification_plain_text'] ) ? 1 : 0;
		$notification_totals     = ! empty( $_POST['notification_totals'] ) ? 1 : 0;
		$notification_prices     = ! empty( $_POST['notification_prices'] ) ? 1 : 0;
		$notification_all_items  = ! empty( $_POST['notification_include_all_items'] ) ? 1 : 0;

		// Validate
		if ( empty( $recipient_name ) ) {
			return new WP_Error( 'input', __( 'Recipient name is a required field', 'woocommerce-example-plugin' ) );
		}

		if ( empty( $recipient_email ) ) {
			return new WP_Error( 'input', __( 'Recipient email is a required field', 'woocommerce-example-plugin' ) );
		}

		$recipient_emails = array_map( 'trim', explode( ',', $recipient_email ) );

		foreach ( $recipient_emails as $email ) {
			if ( ! is_email( $email ) ) {
				return new WP_Error( 'input', __( 'A recipient email is invalid:', 'woocommerce-example-plugin' ) . ' ' . $email );
			}
		}

		// Insert recipient
		$result = $wpdb->insert(
			"{$wpdb->prefix}example_plugin",
			array(
				'recipient_name'                 => $recipient_name,
				'recipient_email'                => $recipient_email,
				'recipient_address'              => $recipient_address,
				'recipient_phone'                => $recipient_phone,
				'recipient_website'              => $recipient_website,
				'notification_plain_text'        => $notification_plain_text,
				'notification_type'              => serialize( $notification_type ),
				'notification_totals'            => $notification_totals,
				'notification_prices'            => $notification_prices,
				'notification_include_all_items' => $notification_all_items,
			),
			array(
				'%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d'
			)
		);

		$notification_id = $wpdb->insert_id;

		if ( $result && $notification_id ) {

			$triggers = array();

			// Store triggers
			$posted_triggers = isset( $_POST['notification_triggers'] ) ? array_filter( array_map( 'esc_attr', array_map( 'trim', (array) $_POST['notification_triggers'] ) ) ) : array();

			foreach ( $posted_triggers as $trigger ) {
				if ( $trigger == 'all' ) {

					$triggers[] = "( {$notification_id}, 0, '' )";

				} else {
					$trigger = explode( ':', $trigger );

					$term 	= esc_attr( $trigger[0] );
					$id 	= absint( $trigger[1] );

					$triggers[] = "( {$notification_id}, {$id}, '{$term}' )";
				}
			}

			if ( sizeof( $triggers ) > 0 ) {
				$wpdb->query( "
					INSERT INTO {$wpdb->prefix}example_notification_triggers ( notification_id, object_id, object_type )
					VALUES " . implode( ',', $triggers ) . ";
				" );
			}

			return true;
		}

		return false;
	}

	/**
	 * save_recipient function.
	 */
	function save_recipient() {
		global $wpdb;

		$recipient_name          = sanitize_text_field( stripslashes( $_POST['recipient_name'] ) );
		$recipient_email         = sanitize_text_field( stripslashes( $_POST['recipient_email'] ) );
		$recipient_address       = sanitize_text_field( stripslashes( $_POST['recipient_address'] ) );
		$recipient_phone         = sanitize_text_field( stripslashes( $_POST['recipient_phone'] ) );
		$recipient_website       = sanitize_text_field( stripslashes( $_POST['recipient_website'] ) );
		$notification_type       = isset( $_POST['notification_type'] ) ? array_filter( array_map( 'sanitize_text_field', array_map( 'stripslashes', (array) $_POST['notification_type'] ) ) ) : array();
		$notification_plain_text = ! empty( $_POST['notification_plain_text'] ) ? 1 : 0;
		$notification_totals	 = ! empty( $_POST['notification_totals'] ) ? 1 : 0;
		$notification_prices	 = ! empty( $_POST['notification_prices'] ) ? 1 : 0;
		$notification_all_items  = ! empty( $_POST['notification_include_all_items'] ) ? 1 : 0;

		// Validate
		if ( empty( $recipient_name ) ) {
			return new WP_Error( 'input', __( 'Recipient name is a required field', 'woocommerce-example-plugin' ) );
		}

		if ( empty( $recipient_email ) ) {
			return new WP_Error( 'input', __( 'Recipient email is a required field', 'woocommerce-example-plugin' ) );
		}

		$recipient_emails = array_map( 'trim', explode( ',', $recipient_email ) );

		foreach ( $recipient_emails as $email ) {
			if ( ! is_email( $email ) ) {
				return new WP_Error( 'input', __( 'A recipient email is invalid:', 'woocommerce-example-plugin' ) . ' ' . $email );
			}
		}

		// Insert recipient
		$wpdb->update(
			"{$wpdb->prefix}example_plugin",
			array(
				'recipient_name'                 => $recipient_name,
				'recipient_email'                => $recipient_email,
				'recipient_address'              => $recipient_address,
				'recipient_phone'                => $recipient_phone,
				'recipient_website'              => $recipient_website,
				'notification_plain_text'        => $notification_plain_text,
				'notification_type'              => serialize( $notification_type ),
				'notification_totals'            => $notification_totals,
				'notification_prices'            => $notification_prices,
				'notification_include_all_items' => $notification_all_items,
			),
			array( 'notification_id' => absint( $this->editing_id ) ),
			array(
				'%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d'
			),
			array( '%d' )
		);

		// Delete old triggers
		$wpdb->query( "
			DELETE FROM {$wpdb->prefix}example_notification_triggers
			WHERE notification_id = " . absint( $this->editing_id ) . "
			AND object_type != 'product';
		" );

		$triggers = array();

		// Store triggers
		$posted_triggers = isset( $_POST['notification_triggers'] ) ? array_filter( array_map( 'esc_attr', array_map( 'trim', (array) $_POST['notification_triggers'] ) ) ) : array();

		foreach ( $posted_triggers as $trigger ) {
			if ( $trigger == 'all' ) {

				$triggers[] = "( " . absint( $this->editing_id ) . ", 0, '' )";

			} else {
				$trigger = explode( ':', $trigger );

				$term 	= esc_attr( $trigger[0] );
				$id 	= absint( $trigger[1] );

				$triggers[] = "( " . absint( $this->editing_id ) . ", {$id}, '{$term}' )";
			}
		}

		if ( sizeof( $triggers ) > 0 ) {
			$wpdb->query( "
				INSERT INTO {$wpdb->prefix}example_notification_triggers ( notification_id, object_id, object_type )
				VALUES " . implode( ',', $triggers ) . ";
			" );
		}

		return true;
	}
}
