<?php
/**
 * Network status checker for QIT network requirements tests.
 * 
 * This script runs in web context (not WP-CLI) to accurately test
 * whether network restrictions are in effect.
 */

// Bootstrap WordPress
require_once '/var/www/html/wp-load.php';

// Test network connectivity
$response = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.0/hello-dolly.json', array(
	'timeout' => 5,
	'sslverify' => false, // Don't verify SSL in test context
) );

// Determine network status
$network_status = is_wp_error( $response ) ? 'NO_NETWORK' : 'NETWORK';

// Write result to file
file_put_contents( '/tmp/network-status.txt', $network_status );

// Also output for debugging
echo "Network status: $network_status\n";

if ( is_wp_error( $response ) ) {
	echo "Error: " . $response->get_error_message() . "\n";
}

exit(0);