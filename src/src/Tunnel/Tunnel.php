<?php

namespace QIT_CLI\Tunnel;

use QIT_CLI\App;
use QIT_CLI\IO\Output;
use QIT_CLI\Spinner;
use QIT_CLI\Tunnel\Tunnels\CloudflaredDockerTunnel;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Tunnel {
	/**
	 * Connects the tunnel.
	 *
	 * @param string $local_url
	 * @param string $env_id
	 *
	 * @return string The public URL of the tunnel.
	 */
	public static abstract function connect_tunnel( string $local_url, string $env_id ): string;

	/**
	 * Determines whether this tunnel can be used.
	 *
	 * @return bool
	 */
	public static abstract function is_supported(): bool;

	/**
	 * Checks if the tunnel is properly configured.
	 *
	 * @return bool
	 */
	public static abstract function is_configured(): bool;

	/**
	 * Optionally override this method if this tunnel has any other
	 * runtime requirements, for instance, to check if the tunnel
	 * is already in use, etc.
	 *
	 * @return bool
	 */
	public static function is_available(): bool {
		return true;
	}

	public static function test_connection( string $tunnel_url, string $tunnel_type ): void {
		$output = App::make( OutputInterface::class );
		// Wait for the tunnel to become accessible.
		$start_time   = time();
		$timeout      = getenv( 'QIT_TUNNEL_TIMEOUT_SECONDS' ) ?: 300; // 5 min.
		$tunnel_ready = false;
		$waited       = 0;
		$spinner      = new Spinner( $output );
		$format_time  = static function ( $t ) {
			return sprintf( "%02d:%02d", ( $t / 60 ) % 60, $t % 60 );
		};

		$tunnels_that_works_best_on_cloudflare_dns = [
			TunnelRunner::$tunnel_map['cloudflared-docker'],
			TunnelRunner::$tunnel_map['cloudflared-binary'],
		];

		while ( ( time() - $start_time ) < $timeout ) {
			$spinner_message = sprintf( '(%s/%s) Waiting for tunnel to be ready... (Trying to access ' . $tunnel_url . '/qit-ping)', $format_time( $waited ), $format_time( $timeout ) );

			if ( $waited > 30 && in_array( $tunnel_type, $tunnels_that_works_best_on_cloudflare_dns, true ) ) {
				$spinner_message = $spinner_message . "\n<comment>The tunnel connection is taking longer than expected. This delay might be due to DNS propagation delays. For optimal performance, consider using Cloudflare DNS (if not already) or a persistent tunnel.</comment>";
			}

			$spinner->setMessage( $spinner_message );

			if ( $waited === 0 ) {
				$spinner->start();
			}

			$ch = curl_init( $tunnel_url . '/qit-ping' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
			$response   = curl_exec( $ch );
			$http_code  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			$curl_error = curl_error( $ch );
			curl_close( $ch );

			if ( $response === 'qit-pong' && $http_code === 200 ) {
				$tunnel_ready = true;
				break;
			}

			// Advance the spinner.
			$spinner->advance();

			sleep( 1 );
			$waited += 1;
		}

		if ( ! $tunnel_ready ) {
			$spinner->fail();
			throw new \RuntimeException( 'Timed out waiting for the tunnel to become accessible.' );
		}

		$spinner->finish();
	}
}