<?php
function post_request( $url ) {
	$attempts_429   = 3; // how many times to retry if 429.
	$attempts_other = 1; // how many times to retry for any other error.

	while ( true ) {
		$curl = curl_init();

		curl_setopt_array( $curl, [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_HTTPHEADER     => [ 'Content-Type: application/json' ],
		] );

		$response   = curl_exec( $curl );
		$http_code  = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		$curl_error = curl_error( $curl );

		curl_close( $curl );

		if ( $response === false ) {
			throw new RuntimeException( 'cURL error: ' . $curl_error );
		}

		// If HTTP status < 400, assume success
		if ( $http_code < 400 ) {
			return json_decode( $response, true );
		}

		// Handle 429 separately
		if ( $http_code === 429 ) {
			if ( $attempts_429 <= 0 ) {
				throw new RuntimeException( 'Received HTTP 429 too many times, giving up.' );
			}
			// Wait 10s and retry
			$attempts_429 --;
			sleep( 10 );
			continue;
		}

		// For any other 4xx/5xx, retry once after 15s.
		if ( $attempts_other <= 0 ) {
			throw new RuntimeException( "HTTP error $http_code, giving up." );
		}
		$attempts_other --;
		sleep( 15 );
	}
}

try {
	$sync_url = 'https://qit.woo.com/wp-json/cd/v1/cli/sync';

	// Fetch sync data from API
	$response = post_request( $sync_url );

	// 1) Override the 'extensions' array
	$response['extensions'] = [
		[ 'id' => 123, 'slug' => 'foo-extension' ],
		[ 'id' => 456, 'slug' => 'bar-extension' ],
		[ 'id' => 789, 'slug' => 'baz-extension' ],
		[ 'id' => 1234, 'slug' => 'qit-beaver' ],
		[ 'id' => 12345, 'slug' => 'qit-cat' ],
		[ 'id' => 12346, 'slug' => 'qit-dog' ],
	];

	// Normalize data.
	if ( isset( $response['latest_cli_version'] ) ) {
		$response['latest_cli_version'] = '0.8.2';
	}
	if ( isset( $response['minimum_cli_version'] ) ) {
		$response['minimum_cli_version'] = '0.3.2';
	}
	if ( isset( $response['playwright_version'] ) ) {
		$response['playwright_version'] = '1.49.1';
	}
	if ( isset( $response['enforce_latest'] ) ) {
		$response['enforce_latest'] = true;
	}
	if ( isset( $response['versions']['wordpress'] ) ) {
		$response['versions']['wordpress']['stable'] = '6.7.1';
		$response['versions']['wordpress']['rc']     = '6.7.1';
	}
	if ( isset( $response['versions']['woocommerce'] ) ) {
		$response['versions']['woocommerce']['stable'] = '9.6.0';
		$response['versions']['woocommerce']['rc']     = '9.7.0-beta.1';
	}
	if ( isset( $response['environments']['e2e'] ) ) {
		$response['environments']['e2e']['url']          = 'https://qit.woo.com/wp-content/uploads/environments/c1a78950ca0eb8a23f8706955681bf02.zip';
		$response['environments']['e2e']['checksum']     = 'c1a78950ca0eb8a23f8706955681bf02';
		$response['environments']['e2e']['zip_checksum'] = 19091;
	}
	if ( isset( $response['schemas'] ) && is_array( $response['schemas'] ) ) {
		$normalize_enums = function ( &$node ) use ( &$normalize_enums ) {
			if ( ! is_array( $node ) ) {
				return;
			}
			foreach ( array_keys( $node ) as $key ) {
				// If we find "wordpress_version" or "woocommerce_version" and they have an enum, overwrite it
				if ( $key === 'wordpress_version'
				     && isset( $node[ $key ]['enum'] )
				     && is_array( $node[ $key ]['enum'] )
				) {
					$node[ $key ]['enum'] = [
						'6.4.5',
						'6.5.5',
						'6.6.2',
						'6.7.1',
						'stable',
						'rc',
					];
				}
				if ( $key === 'woocommerce_version'
				     && isset( $node[ $key ]['enum'] )
				     && is_array( $node[ $key ]['enum'] )
				) {
					$node[ $key ]['enum'] = [
						'9.7.0-beta.1',
						'9.5.0',
						'9.5.1',
						'9.5.2',
						'9.6.0',
						'stable',
						'rc',
					];
				}
				// Recurse deeper on each child
				$normalize_enums( $node[ $key ] );
			}
		};
		$normalize_enums( $response['schemas'] );
	}

	$file_path = __DIR__ . '/sync.json';
	if ( ! is_writable( dirname( $file_path ) ) ) {
		throw new RuntimeException( 'Cannot write to directory: ' . dirname( $file_path ) );
	}

	file_put_contents( $file_path, json_encode( $response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
} catch ( Exception $e ) {
	die( "Error: " . $e->getMessage() . "\n" );
}