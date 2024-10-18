<?php

namespace QIT_CLI\Tunnel;

use QIT_CLI\App;
use QIT_CLI\Spinner;
use Symfony\Component\Console\Helper\Helper;
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
	abstract public static function connect_tunnel( string $local_url, string $env_id ): string;

	/**
	 * Determines whether this tunnel can be used.
	 *
	 * @throws \RuntimeException If the tunnel is not supported.
	 */
	abstract public static function check_is_installed(): void;

	/**
	 * Checks if the tunnel is properly configured.
	 *
	 * @return bool
	 */
	abstract public static function is_configured(): bool;

	/**
	 * Optionally override this method if this tunnel has any other
	 * runtime requirements, for instance, to check if the tunnel
	 * is already in use, etc.
	 *
	 * @return void
	 * @throws \RuntimeException If the tunnel is not available.
	 */
	public static function check_is_available(): void {
		return; // phpcs:ignore Squiz.PHP.NonExecutableCode.ReturnNotRequired
	}

	public static function test_connection( string $tunnel_url, string $tunnel_type ): void {
		$output = App::make( OutputInterface::class );
		// Wait for the tunnel to become accessible.
		$start_time      = time();
		$timeout         = getenv( 'QIT_TUNNEL_TIMEOUT_SECONDS' ) ?: 300; // 5 min.
		$tunnel_ready    = false;
		$started_spinner = false;
		$spinner         = new Spinner( $output );
		$spinner->get_progress_bar()->setFormat( "%bar% (%elapsed:6s%) %message%" );

		$tunnels_that_works_best_on_cloudflare_dns = [
			TunnelRunner::$tunnel_map['cloudflared-docker'],
			TunnelRunner::$tunnel_map['cloudflared-binary'],
		];

		while ( ( time() - $start_time ) < $timeout ) {
			$elapsed         = time() - $start_time;
			$spinner_message = 'Waiting for tunnel to be ready... (Trying to access ' . $tunnel_url . '/qit-ping)';

			if ( $elapsed > 30 && in_array( $tunnel_type, $tunnels_that_works_best_on_cloudflare_dns, true ) ) {
				$spinner_message = $spinner_message . sprintf( "\n<comment>The tunnel connection is taking longer than expected. This delay might be due to DNS propagation delays. For optimal performance, consider using Cloudflare DNS (if not already) or a persistent tunnel. The timeout is: %s</comment>", Helper::formatTime( $timeout ) );
			}

			$spinner->set_message( $spinner_message );

			if ( ! $started_spinner ) {
				$started_spinner = true;
				$spinner->start();
			}

			$ch = curl_init( $tunnel_url . '/qit-ping' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
			curl_setopt( $ch, CURLOPT_DNS_SERVERS, '1.1.1.1,8.8.8.8' );

			$response   = curl_exec( $ch );
			$http_code  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			$curl_error = curl_error( $ch );
			curl_close( $ch );

			// Advance the spinner.
			$spinner->advance();

			if ( $response === 'qit-pong' && $http_code === 200 ) {
				$tunnel_ready = true;
				break;
			}

			if ( $output->isVerbose() ) {
				$output->writeln( sprintf( 'Tunnel test failed. HTTP code: %s. Response: %s Error: %s', $http_code, $response, $curl_error ) );
			}

			if ( $http_code === 429 ) {
				sleep( 10 );
			} else {
				sleep( 1 );
			}
		}

		if ( ! $tunnel_ready ) {
			$spinner->fail();
			throw new \RuntimeException( 'Timed out waiting for the tunnel to become accessible.' );
		}

		$spinner->finish();

		$output->writeln( '' );
	}
}
