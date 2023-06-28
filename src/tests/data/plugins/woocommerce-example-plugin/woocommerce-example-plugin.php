<?php
/**
 * Plugin Name: WooCommerce Example Plugin
 * Plugin URI: https://woocommerce.com/products/example-plugin
 * Description:
 * Version:
 * Author:
 * Author URI:
 * Text Domain: woocommerce-example-plugin
 * Domain Path: /languages/
 * Tested up to: 6.1
 * WC requires at least: 3.0
 * WC tested up to: 7.3
 * Woo:
 *
 * Copyright: © 2023 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package woocommerce-example-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_EXAMPLE_PLUGIN_VERSION', '1.2.3' ); // WRCS: DEFINED_VERSION.
define( 'WC_EXAMPLE_PLUGIN_DB_VERSION', '1.2.3' ); // DB Version.
/**
 * Localisation
 */
load_plugin_textdomain( 'woocommerce-example-plugin', false, plugin_basename( __DIR__ ) . '/languages/' );

/**
 * Init example plugin function.
 */
function init_example_plugin() {
	if ( class_exists( 'WooCommerce' ) ) {
		woocommerce_example_plugin_db_check();

		include_once 'includes/class-wc-example-plugin.php';
		include_once 'includes/class-wc-example-plugin-privacy.php';
	} else {
		add_action( 'admin_notices', 'woocommerce_example_plugin_woocommerce_deactivated' );
	}
}
add_action( 'plugins_loaded', 'init_example_plugin' );

/**
 * WooCommerce Deactivated Notice.
 */
function woocommerce_example_plugin_woocommerce_deactivated() {
	/* translators: %s: WooCommerce link */
	echo '<div class="error"><p>' . sprintf( esc_html__( 'WooCommerce Example Plugin requires %s to be installed and active.', 'woocommerce-example-plugin' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p></div>';
}

function woocommerce_example_plugin_db_check() {
	if ( get_option( 'wc_example_plugin_db_version' ) != WC_EXAMPLE_PLUGIN_DB_VERSION ) {
        activate_example_plugin();
    }
}

/**
 * Declaring HPOS compatibility.
 */
function woocommerce_example_plugin_declare_hpos_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'woocommerce-example-plugin/woocommerce-example-plugin.php', true );
	}
}
add_action( 'before_woocommerce_init', 'woocommerce_example_plugin_declare_hpos_compatibility' );

/**
 * Activation
 */
register_activation_hook( __FILE__, 'activate_example_plugin' );

/**
 * Example plugin activation function.
 */
function activate_example_plugin() {
	global $wpdb;

	$wpdb->hide_errors();

	$collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) ) {
			$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$collate .= " COLLATE $wpdb->collate";
		}
	}

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	/**
	 * Table for plugin
	 */
	$sql = "
CREATE TABLE {$wpdb->prefix}example_plugin (
notification_id bigint(20) NOT NULL auto_increment,
recipient_name LONGTEXT NULL,
recipient_email LONGTEXT NULL,
recipient_address LONGTEXT NULL,
recipient_phone varchar(240) NULL,
recipient_website varchar(240) NULL,
notification_type varchar(240) NULL,
notification_plain_text int(1) NOT NULL,
notification_totals int(1) NOT NULL,
notification_prices int(1) NOT NULL,
notification_include_all_items int(1) NULL,
notification_sent_count bigint(20) NOT NULL default 0,
PRIMARY KEY  (notification_id)
) $collate;
";
	dbDelta( $sql );

	$sql = "
CREATE TABLE {$wpdb->prefix}example_notification_triggers (
notification_id bigint(20) NOT NULL,
object_id bigint(20) NOT NULL,
object_type varchar(200) NOT NULL,
PRIMARY KEY  (notification_id,object_id)
) $collate;
";
	dbDelta( $sql );

	update_option( 'wc_example_plugin_db_version', WC_EXAMPLE_PLUGIN_DB_VERSION );
}
