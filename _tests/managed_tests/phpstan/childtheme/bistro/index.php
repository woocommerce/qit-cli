<?php

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