<?php

/*
 * Plugin name: Security - Nonce
 */

add_action( 'init', static function() {
	if ( isset( $_POST['foo'] ) ) { // Should be flagged by Nonce.Missing
		$foo = $_POST['foo']; // Should be flagged by Nonce.Missing and Unsanitized
		$bar = $_POST['bar']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing

		echo "Unescaped output! $foo"; // All output should be run through an escaping function
		echo "Unescaped output! $bar"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		wp_set_auth_cookie( 1 ); // Detected usage of a potentially unsafe function.
		wp_set_current_user( 1 ); // Detected usage of a potentially unsafe function.
	}

	if ( isset( $_GET['foo'] ) ) { // Should be flagged by Nonce.Recommended
		$foo = $_GET['foo']; // Should be flagged by Nonce.Recommended and Unsanitized
		$bar = $_GET['bar']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Recommended
	}
} );

add_filter( 'determine_user', 'callable' ); // Risky filter warning.