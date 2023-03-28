<?php

/*
 * Plugin name: Security - Plugin A
 */

add_action( 'init', static function() {
	if ( isset( $_POST['foo'] ) ) {
		$foo = $_POST['foo']; // Detected usage of a non-sanitized input variable: $_POST['foo']
		$bar = $_POST['bar']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$baz = $_POST['baz'];

		echo "Unescaped output! $foo"; // All output should be run through an escaping function
		echo "Unescaped output! $bar"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "Unescaped output! $baz";
	}
} );