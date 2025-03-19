<?php
/**
 * Plugin Name: PHPStan - Custom Excludes/Bootstrap/IgnoreErrors Test
 *
 * Description: Demonstrates a plugin containing its own phpstan.neon that
 *              excludes certain folders and ignores certain errors.
 */

add_action( 'init', function () {
	// This constant is defined in the bootstrap file.
	$a = MY_PHPSTAN_BOOTSTRAP_CONSTANT;

	// This undefined function call would normally be flagged...
	// ...but we've explicitly told PHPStan to ignore "Function some_custom_wp_function not found"
	some_custom_wp_function();

	// Another random undefined function that we did *not* ignore, to confirm it is flagged:
	totally_unknown_function();
});
