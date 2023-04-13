<?php

/*
 * Plugin name: Activation - Plugin A
 * Description: This test mimicks a PHP 8.1 plugin, asserting that it flags what we expect to be flagged in an Activation Test in PHP 8.1.
 */

namespace SUT;

add_action( 'init', static function () {
	// PHP 8.1 added support for read-only properties, so this syntax should not cause a fatal.
	class ReadOnlyUser {
		public readonly string $name;
	}

	class NullLogger {
	}

	class BarUser implements \Serializable { // Deprecation notice. Serializable interface is deprecated in PHP 8.1.
		public NullLogger $logger;

		// PHP 8.1 added support for "new" in __construct.
		public function __construct( $logger = new NullLogger ) {
			$this->logger = $logger;
		}

		public function serialize() {
			return [];
		}

		public function unserialize( $data ) {
			return null;
		}
	}

	$user = new BarUser();

	add_action( 'wp', static function () use ( $user ) {
		if ( ! $user->logger instanceof NullLogger ) {
			trigger_error( 'This should be a NullLogger.', E_USER_ERROR );
		}

		$foo = strlen( null ); // Deprecation notice. Null where a string is expected.
	} );
} );