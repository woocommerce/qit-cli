<?php

namespace QIT_CLI;

use QIT_CLI\Exceptions\EncryptionException;

class Crypto {
	public static function using_encryption() {
		return getenv( 'QIT_KEY' ) !== false;
	}

	public function encrypt( string $plain_text ): string {
		$key = $this->check_requirements();

		$cipher          = "AES-128-CBC";
		$ivlen           = openssl_cipher_iv_length( $cipher );
		$iv              = openssl_random_pseudo_bytes( $ivlen );
		$cipher_text_raw = openssl_encrypt( $plain_text, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv );
		$hmac            = hash_hmac( 'sha256', $cipher_text_raw, $key, $as_binary = true );
		$cipher_text     = base64_encode( $iv . $hmac . $cipher_text_raw );

		return $cipher_text;
	}

	public function decrypt( string $cipher_text ): string {
		$key = $this->check_requirements();

		$cipher         = "AES-128-CBC";
		$c              = base64_decode( $cipher_text );
		$ivlen          = openssl_cipher_iv_length( $cipher );
		$iv             = substr( $c, 0, $ivlen );
		$hmac           = substr( $c, $ivlen, $sha2len = 32 );
		$ciphertext_raw = substr( $c, $ivlen + $sha2len );
		$plain_text     = openssl_decrypt( $ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv );

		return $plain_text;
	}

	protected function check_requirements(): string {
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			throw EncryptionException::openssl_not_available();
		}

		$key = getenv( 'QIT_KEY' );

		if ( empty( $key ) ) {
			throw EncryptionException::key_not_set();
		}

		$minimum_key_length = 4;

		if ( strlen( $key ) < $minimum_key_length ) {
			throw EncryptionException::weak_key( 4 );
		}

		return $key;
	}
}