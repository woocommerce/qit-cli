<?php

/*
 * Plugin name: PHPStan - Main
 */

class Foo {

}

function example_return_void(): void {
	// no-op.
}

function example_missing_return_statement() {
	return 'foo';
}

add_action( 'init', static function() {
	// Flagged at Level 0:
	$bar = new Bar; // Undefined class. (Flagged)
	$baz = example_return_void(); // Result (void) is used. (Flagged)

	// Not flagged at level 0:
	$foo = new Foo;
	$foo->bar = 'baz'; // Access to non-existing property. // (Not Flagged)
	$bax = example_missing_return_statement(); // (Not Flagged)
} );


// WP CLI Stubs.
WP_CLI::add_command( 'my-wpcli-test', function () {
	// This command just checks if WP_CLI stubs are recognized by PHPStan.
	WP_CLI::log( 'Running WP CLI test command...' );
	WP_CLI::success( 'PHPStan recognized WP CLI stubs!' );
	WP_CLI::someNonExistingMethod( 'PHPStan recognized WP CLI stubs!' );
} );
