<?php

/*
 * Plugin name: Activation - Plugin A
 * Description: This test mimicks a PHP 8.2 plugin, asserting that it flags what we expect to be flagged in an Activation Test in PHP 8.2.
 */

namespace SUT;

add_action( 'init', static function () {
	// PHP 8.2 added support for read-only classes, so this syntax should not cause a fatal.
	readonly class ReadOnlyUser {
		public string $name;
	}

	// PHP 8.2 added support for constants in traits.
	trait FooBar {
		const FOO = 'foo';
	}

	class BarUser {
		use FooBar;
	}

	$user = new BarUser();

	// Dynamic properties are deprecated and should be flagged.
	$user->bar = 'baz';

	add_action( 'wp', static function () use ( $user ) {
		// This syntax is supported in PHP 8.2.
		if ( BarUser::FOO !== 'foo' ) {
			trigger_error( 'This should be "foo".', E_USER_ERROR );
		}

		// utf8_encode is deprecated in PHP 8.2.
		utf8_encode( 'foo' );
	} );
} );