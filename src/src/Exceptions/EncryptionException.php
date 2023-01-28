<?php

namespace QIT_CLI\Exceptions;

class EncryptionException extends \Exception {
	public static function encrypt_error( \Exception  $e) {
		return new self( sprintf( 'Could not encrypt config file. Error: %s', $e->getMessage() ) );
	}
	public static function decrypt_error( \Exception  $e) {
		return new self( sprintf( 'Could not decrypt config file. Error: %s', $e->getMessage() ) );
	}
}
