<?php

/*
 * Plugin name: Security - Plugin A
 * Version: 0.1-test-version
 */

add_action( 'init', static function() {
	if ( isset( $_POST['foo'] ) ) {
		$foo = $_POST['foo']; // Detected usage of a non-sanitized input variable: $_POST['foo']
		$bar = $_POST['bar']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		echo "Unescaped output! $foo"; // All output should be run through an escaping function
		echo "Unescaped output! $bar"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		wp_set_auth_cookie( 1 ); // Detected usage of a potentially unsafe function.
		wp_set_current_user( 1 ); // Detected usage of a potentially unsafe function.
	}
} );

add_filter( 'determine_user', 'callable' ); // Risky filter warning.