<?php

namespace QITE2E;

use PHPUnit\Framework\TestCase;

class QITE2ETestCase extends TestCase {
	public function validate_and_normalize( string $file_path, ?callable $callback = null ): string {
		if ( ! file_exists( $file_path ) ) {
			$this->fail( 'Test result file not found at: ' . $file_path );
		}

		$json = json_decode( file_get_contents( $file_path ), true );

		if ( ! is_array( $json ) || empty( $json ) ) {
			$this->fail( 'Test result file is not a JSON: ' . $file_path );
		}

		$rules = [
			'run_id'                          => [
				'normalize' => 123456,
				'validate'  => static function ( $value ) {
					return preg_match( '/^\d+$/', $value );
				}
			],
			'wordpress_version'               => [
				'normalize' => '6.0.0-normalized',
				'validate'  => static function ( $value ) {
					return ! empty( $value ) && strlen( $value ) > 1 && strlen( $value ) < 60;
				}
			],
			'woocommerce_version'             => [
				'normalize' => '6.0.0-normalized',
				'validate'  => static function ( $value ) {
					return ! empty( $value ) && strlen( $value ) > 1 && strlen( $value ) < 60;
				}
			],
			'test_results_manager_url'        => [
				'normalize' => 'https://test-results-manager.com',
				'validate'  => static function ( $value ) {
					return filter_var( $value, FILTER_VALIDATE_URL );
				}
			],
			'test_results_manager_expiration' => [
				'normalize' => 1234567890,
				'validate'  => static function ( $value ) {
					return preg_match( '/^\d+$/', $value );
				}
			],
			'test_result_json' => [
				'normalize' => static function( $value ) {
					// Normalize timestamps such as [01-Mar-2023 10:55:12 UTC] to [TIMESTAMP]
					$value = preg_replace( '/\[\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\]/', '[TIMESTAMP]', $value );

					return $value;
				},
				'validate' => static function( $value ) {
					return ! is_null( json_decode( $value ) );
				}
			]
		];

		if ( ! is_null( $callback ) ) {
			$rules = $callback( $rules );
		}

		foreach ( $json as $k => &$v ) {
			// Check if the current key is in the processing rules.
			if ( array_key_exists( $k, $rules ) ) {
				// Validate the existing value.
				if ( $rules[ $k ]['validate']( $v ) ) {
					// Normalize for snapshot testing.
					if (is_callable($rules[ $k ]['normalize'])) {
						$v = $rules[ $k ]['normalize']( $v );
					} else {
						$v = $rules[ $k ]['normalize'];
					}
				} else {
					$this->fail( 'Invalid value for key: ' . $k );
				}
			}
		}

		return json_encode( $json, JSON_PRETTY_PRINT );
	}
}