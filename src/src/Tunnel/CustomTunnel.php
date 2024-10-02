<?php

namespace QIT_CLI\Tunnel;

abstract class CustomTunnel {
	/**
	 * Connects to a custom tunnel.
	 *
	 * @param string $site_url The local URL to be tunnelled.
	 * @param string $env_id The environment ID.
	 *
	 * @return string The tunnelled URL.
	 */
	abstract public static function connect_tunnel( string $site_url, string $env_id ): string;

	public static function test_connection( string $tunnel_url ) {
		echo "Waiting for tunnel to be ready... ";
		// Wait for the tunnel to become accessible.
		$start_time   = time();
		$timeout      = getenv( 'QIT_TUNNEL_TIMEOUT_SECONDS' ) ?: 60; // seconds.
		$tunnel_ready = false;

		while ( ( time() - $start_time ) < $timeout ) {
			// Try accessing the /ping endpoint.
			$ch = curl_init( $tunnel_url . '/qit-ping' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); // We need the response body.
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 ); // Timeout for the request.
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true ); // Enable SSL verification.
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
			$response   = curl_exec( $ch );
			$http_code  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			$curl_error = curl_error( $ch );
			curl_close( $ch );

			if ( $response === 'qit-pong' && $http_code === 200 ) {
				$tunnel_ready = true;
				break;
			}

			usleep( 500000 ); // Wait for 0.5 seconds before retrying.
		}

		if ( ! $tunnel_ready ) {
			throw new \RuntimeException( 'Timed out waiting for the tunnel to become accessible.' );
		}

		echo "Tunnel is ready!\n";
	}
}