<?php

/*
 * Plugin name: Security - Plugin A
 */

add_action( 'init', static function() {
	if ( isset( $_POST['foo'] ) ) {
		$foo = $_POST['foo']; // Detected usage of a non-sanitized input variable: $_POST['foo']
		$bar = $_POST['bar']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		echo "Unescaped output! $foo"; // All output should be run through an escaping function
		echo "Unescaped output! $bar"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		wp_set_auth_cookie( 1 ); // Detected usage of a potentially unsafe function.
		wp_set_current_user( 1 ); // Detected usage of a potentially unsafe function.

		wp_safe_redirect( $_GET['foo'] ); // Should not be flagged.

		wp_redirect( $_GET['foo'] ); // Should be flagged by WordPress.Security.SafeRedirect.wp_redirect_wp_redirect.

		// Should not be flagged neither by PHPCS nor by SemGrep's audit.php.wp.security.unsafe-wp-redirect.
		wp_redirect( $_GET['bar'] ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
	}
} );

add_filter( 'determine_user', 'callable' ); // Risky filter warning.