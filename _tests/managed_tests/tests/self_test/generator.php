<?php

$data = array();

// Create an array of test results
$test_result_1 = array(
	"test_run_id"                     => 1393482,
	"run_id"                          => 1393482,
	"test_type"                       => "woo-e2e",
	"test_type_display"               => "Woo E2E",
	"wordpress_version"               => "6.7.1",
	"woocommerce_version"             => "9.5.0-beta.2",
	"php_version"                     => "8.2",
	"max_php_version"                 => "",
	"min_php_version"                 => "",
	"additional_woo_plugins"          => array(),
	"additional_wp_plugins"           => array(),
	"test_log"                        => "",
	"status"                          => "success",
	"test_result_aws_url"             => "",
	"test_result_aws_expiration"      => 0,
	"is_development"                  => true,
	"send_notifications"              => false,
	"woo_extension"                   => array(
		"id"   => 18619,
		"host" => "wccom",
		"name" => "Google Product Feed",
		"type" => "plugin",
	),
	"client"                          => "qit_cli",
	"event"                           => "cli_development_extension_test",
	"optional_features"               => array(
		"hpos"               => false,
		"new_product_editor" => false,
	),
	"test_results_manager_url"        => "https://stagingcompatibilitydashboard.wpcomstaging.com?qit_results=1393482.jJlxNJrQ0oesOndi32F0aPATbqlvdyPFTaMWsUvnZ5y3T3weSA2XU3x6pBLabvMg",
	"test_results_manager_expiration" => 1733505761,
	"test_summary"                    => "427 total, 382 passed, 0 failed, 45 skipped.",
	"debug_log"                       => json_encode( [
		"b2222e813b8a81aa3cc5969e1a2d6fe9" => [
			"count"       => 1,
			"message"     => "PHP Fatal error: Uncaught Automattic\\WooCommerce\\StoreApi\\Exceptions\\RouteException: The quantity of \"Product 1732902946548\" cannot be changed in 
/var/www/html/wp-content/plugins/woocommerce/src/StoreApi/Utilities/CartController.php:238\nStack trace:\n#0 /var/www/html/wp-content/plugins/woocommerce/src/StoreApi/Utilities/CartController.php(52): 
Automattic\\WooCommerce\\StoreApi\\Utilities\\CartController->set_cart_item_quantity(\"776ebcd59621e30...\", 1)\n#1 /var/www/html/wp-content/plugins/woocommerce/src/StoreApi/Routes/V1/AbstractCartRoute.php(180): 
Automattic\\WooCommerce\\StoreApi\\Utilities\\CartController->normalize_cart()\n#2 /var/www/html/wp-content/plugins/woocommerce/src/StoreApi/Routes/V1/AbstractCartRoute.php(114): 
Automattic\\WooCommerce\\StoreApi\\Routes\\V1\\AbstractCartRoute->load_cart_session(Object(WP_REST_Request))\n#3 /var/www/html/wp-includes/rest-api/class-wp-rest-server.php(1292): 
Automattic\\WooCommerce\\StoreApi\\Routes\\V1\\AbstractCartRoute->get_response(Object(WP_REST_Request))\n#4 /var/www/html/wp-includes/rest-api/class-wp-rest-server.php(1125): 
WP_REST_Server->respond_to_request(Object(WP_REST_Request), \"\/wc\/store\/v1\/cart\/set_cart_item_quantity\", Array, NULL)\n#5 /var/www/html/wp-includes/rest-api/class-wp-rest-server.php(439): 
WP_REST_Server->dispatch(Object(WP_REST_Request))\n#6 /var/www/html/wp-includes/rest-api.php(449): WP_REST_Server->serve_request(\"\/wc\/store\/v1\/cart\/set_cart_item_quantity\")\n#7 
/var/www/html/wp-includes/class-wp-hook.php(324): rest_api_loaded(Object(WP))\n#8 /var/www/html/wp-includes/class-wp-hook.php(348): WP_Hook->apply_filters(NULL, Array)\n#9 /var/www/html/wp-includes/plugin.php(565): 
WP_Hook->do_action(Array)\n#10 /var/www/html/wp-includes/class-wp.php(418): do_action_ref_array(\"parse_request\", Array)\n#11 /var/www/html/wp-includes/class-wp.php(813): WP->parse_request(\"\")\n#12 
/var/www/html/wp-includes/functions.php(1336): WP->main(\"\")\n#13 /var/www/html/wp-blog-header.php(16): wp()\n#14 /var/www/html/index.php(17): require(/var/www/html/wordpress/wp-load.php)\n#15 {main}",
			"stack_trace" => "#0 /var/www/html/wp-content/plugins/woocommerce/src/StoreApi/Utilities/CartController.php(52): Automattic\\WooCommerce\\StoreApi\\Utilities\\CartController->set_cart_item_quantity(\"776ebcd59621e30...\", 
1)\n#1 /var/www/html/wp-content/plugins/woocommerce/src/StoreApi/Routes/V1/AbstractCartRoute.php(180): Automattic\\WooCommerce\\StoreApi\\Utilities\\CartController->normalize_cart()\n#2 
/var/www/html/wp-content/plugins/woocommerce/src/StoreApi/Routes/V1/AbstractCartRoute.php(114): Automattic\\WooCommerce\\StoreApi\\Routes\\V1\\AbstractCartRoute->load_cart_session(Object(WP_REST_Request))\n#3 
/var/www/html/wp-includes/rest-api/class-wp-rest-server.php(1292): Automattic\\WooCommerce\\StoreApi\\Routes\\V1\\AbstractCartRoute->get_response(Object(WP_REST_Request))\n#4 
/var/www/html/wp-includes/rest-api/class-wp-rest-server.php(1125): WP_REST_Server->respond_to_request(Object(WP_REST_Request), \"\/wc\/store\/v1\/cart\/set_cart_item_quantity\", Array, NULL)\n#5 
/var/www/html/wp-includes/rest-api/class-wp-rest-server.php(439): WP_REST_Server->dispatch(Object(WP_REST_Request))\n#6 /var/www/html/wp-includes/rest-api.php(449): 
WP_REST_Server->serve_request(\"\/wc\/store\/v1\/cart\/set_cart_item_quantity\")\n#7 /var/www/html/wp-includes/class-wp-hook.php(324): rest_api_loaded(Object(WP))\n#8 /var/www/html/wp-includes/class-wp-hook.php(348): 
WP_Hook->apply_filters(NULL, Array)\n#9 /var/www/html/wp-includes/plugin.php(565): WP_Hook->do_action(Array)\n#10 /var/www/html/wp-includes/class-wp.php(418): do_action_ref_array(\"parse_request\", Array)\n#11 
/var/www/html/wp-includes/class-wp.php(813): WP->parse_request(\"\")\n#12 /var/www/html/wp-includes/functions.php(1336): WP->main(\"\")\n#13 /var/www/html/wp-blog-header.php(16): wp()\n#14 /var/www/html/index.php(17): 
require(/var/www/html/wordpress/wp-load.php)\n#15 {main}",
		],
	] ),
	"version"                         => "Undefined",
	"update_complete"                 => true,
	"ai_suggestion_status"            => "none",
	"malware_whitelist_paths"         => array(),
	"workflow_id"                     => "12088625093",
	"runner"                          => "public",
	"test_media"                      => array(),
	"test_result_json_extracted"      => "{EXTRACTED}",
);

$test_result_2 = array(
	"test_result_json" => array(
		"numFailedTestSuites"  => 0,
		"numPassedTestSuites"  => 86,
		"numPendingTestSuites" => 22,
		"numTotalTestSuites"   => 108,
		"numFailedTests"       => 0,
		"numPassedTests"       => 382,
		"numPendingTests"      => 45,
		"numTotalTests"        => 427,
		"testResults"          => array(
			array(
				"file"        => "activate-and-setup/core-profiler.spec.js",
				"status"      => "passed",
				"has_pending" => true,
				"tests"       => array(
					array(
						"title"  => "Can complete the core profiler skipping extension install",
						"status" => "passed",
					),
					array(
						"title"  => "Can complete the core profiler installing default extensions",
						"status" => "pending",
					),
				),
			),
			array(
				"file"        => "activate-and-setup/store-owner-can-skip-core-profiler.spec.js",
				"status"      => "passed",
				"has_pending" => false,
				"tests"       => array(
					array(
						"title"  => "Can click skip guided setup",
						"status" => "passed",
					),
					array(
						"title"  => "Can connect to WooCommerce.com",
						"status" => "passed",
					),
				),
			),
		),
	),
);

$data[] = $test_result_1;
$data[] = $test_result_2;

$json = json_encode( $data, JSON_PRETTY_PRINT );

// Simulate reading the JSON from a file
$json_string = $json;

// Decode the JSON string
$decoded_json = json_decode( $json_string, true );

if ( json_last_error() !== JSON_ERROR_NONE ) {
	echo "Error decoding JSON: " . json_last_error_msg() . "\n";
}

// Access the 'debug_log' field from the first test result
$debug_log = $decoded_json[0]['debug_log'];

// Validate and normalize the 'debug_log' field
function validate( $value ) {
	if ( empty( $value ) ) {
		return true;
	}

	// Check if $value is a JSON string
	if ( is_string( $value ) ) {

		$decoded_value = json_decode( $value, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			$value = $decoded_value;
		} else {
			// If it's not valid JSON, return false
			echo "json_decode error: " . json_last_error_msg() . "\n";
			return false;
		}
	}

	// Now, $value should be an array
	if ( is_array( $value ) ) {
		// Encode the array to JSON with proper options
		$value = json_encode( $value );
		if ( $value === false ) {
			echo "json_encode error: " . json_last_error_msg() . "\n";
			return false;
		}
	} else {
		// If $value is neither an array nor a valid JSON string
		echo "Value is neither an array nor a valid JSON string.\n";
		return false;
	}

	// Validate the JSON
	$is_valid = json_decode( $value ) !== null && json_last_error() === JSON_ERROR_NONE;

	return $is_valid;
}

$result = validate( $debug_log );

echo "Validation result: ";
var_export( $result );
echo "\n";