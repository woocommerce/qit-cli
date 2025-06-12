<?php

/*
 * Plugin name: Security - Plugin A
 */

add_action( 'init', static function() {
	if ( isset( $_POST['foo'] ) ) {
		$foo = $_POST['foo'];
		$bar = $_POST['bar']; 

		echo "Unescaped output! $foo";
		echo "Unescaped output! $bar"; // nosemgrep
	}
} );

add_filter( 'determine_user', 'callable' ); // Risky filter warning.