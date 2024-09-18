<?php

/*
 * Plugin name: Activation - Plugin A
 */

// Register a custom page in the admin menu.
add_action( 'admin_menu', function () {
	add_menu_page( 'Plugin A', 'Plugin A', 'manage_options', 'plugin-a', function () {
		// Generate a notice.
		trigger_error( 'Notice in custom page.', E_USER_NOTICE );
		// Generate an warning.
		trigger_error( 'Warning in custom page.', E_USER_WARNING );
		// Generate a legit notice without trigger_error.
		$foo                = [];
		$undefined_variable = $foo['bar'];
		// Generate a legit warning without trigger_error.
		$undefined_variable = $undefined_variable + 1;

		echo '<h1>Plugin A</h1>';
		echo '<script>console.log("Console Log in custom page.");</script>';
		echo '<script>console.warn("Console Warning in custom page.");</script>';
		echo '<script>console.error("Console Error in custom page.");</script>';
		// Throw uncaught exception in JS.
		echo '<script>throw new Error("Uncaught Error in custom page.");</script>';
	} );

	add_menu_page( 'Plugin B', 'Plugin B', 'manage_options', 'plugin-b', function () {
		call_to_an_undefined_function();
	} );
} );