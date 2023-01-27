<?php

namespace QIT_CLI\Exceptions;

class EncryptionException extends \Exception {
	public static function openssl_not_available() {
		return new self( 'Encryption is not available. Please install/activate the OpenSSL PHP extension.' );
	}

	public static function key_not_set() {
		return new self( 'Encryption key is not set. Please set the QIT_KEY environment variable.' );
	}

	public static function weak_key( int $minimum_characters ) {
		return new self( 'Encryption key is too weak. Please set the QIT_KEY environment variable to a string with at least ' . $minimum_characters . ' characters.' );
	}
}
