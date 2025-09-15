<?php

namespace QIT_CLI\Tests\Integration;

trait QitHttpMockHelper {
	protected function allRequests(): array {
		$dir = getenv('QIT_MOCK_DIR');
		$file = "$dir/_requests.json";
		return is_file($file) ? json_decode(file_get_contents($file), true) : [];
	}

	protected function requestByUrl(string $url): ?array {
		foreach ($this->allRequests() as $req) {
			if ($req['url'] === $url) {
				return $req;
			}
		}
		return null;
	}
}

class MockHelper {
	private static $dir;

	public static function setup() {
		self::$dir = sys_get_temp_dir() . '/qit-mock-' . uniqid();
		mkdir( self::$dir );
	}

	public static function mock( $url, $response ) {
		// Use sha1 to match the RequestBuilder implementation
		file_put_contents( self::$dir . '/' . sha1( $url ) . '.json', json_encode( $response ) );
	}

	public static function env() {
		return [ 'QIT_MOCK_DIR' => self::$dir ];
	}

	// Get the last request made (any URL) - now returns the entry format
	public static function lastRequest() {
		$file = self::$dir . '/last_request.json';
		if ( ! file_exists( $file ) ) {
			return null;
		}
		return json_decode( file_get_contents( $file ), true );
	}

	// Get all requests chronologically
	public static function allRequests(): array {
		$file = self::$dir . '/_requests.json';
		return is_file($file) ? json_decode(file_get_contents($file), true) : [];
	}

	// Get request for a specific URL
	public static function requestByUrl( $url ) {
		foreach (self::allRequests() as $req) {
			if ($req['url'] === $url) {
				return $req;
			}
		}
		return null;
	}

	public static function cleanup() {
		if ( isset( self::$dir ) && is_dir( self::$dir ) ) {
			$files = glob( self::$dir . '/*' );
			if ( $files ) {
				array_map( 'unlink', $files );
			}
			rmdir( self::$dir );
		}
	}
}
