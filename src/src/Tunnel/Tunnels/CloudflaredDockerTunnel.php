<?php

namespace QIT_CLI\Tunnel\Tunnels;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Tunnel\Tunnel;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_mac;
use function QIT_CLI\is_wsl;

class CloudflaredDockerTunnel extends Tunnel {
	public static function connect_tunnel( string $local_url, string $env_id ): string {
		$docker = App::make( Docker::class );
		$output = App::make( OutputInterface::class );

		$local_url_escaped     = escapeshellarg( $local_url );
		$docker_container_name = "qit_env_tunnel_$env_id";

		$region = '';

		if ( ! empty( getenv( 'QIT_TUNNEL_REGION' ) ) ) {
			// Validate $region is a-z.
			if ( ! preg_match( '/^[a-z]+$/', getenv( 'QIT_TUNNEL_REGION' ) ) ) {
				throw new \InvalidArgumentException( 'Invalid region specified.' );
			}
			$region = '--region=' . getenv( 'QIT_TUNNEL_REGION' );
		}

		// Start the Docker container in detached mode.
		$command = "{$docker->find_docker()} run -d --net=host --name={$docker_container_name} cloudflare/cloudflared:latest tunnel --no-autoupdate {$region} --url={$local_url_escaped}";
		exec( $command, $run_output, $run_return );

		if ( $run_return !== 0 ) {
			$error_output = implode( "\n", $run_output );
			throw new \RuntimeException( "Failed to start tunnel container: $error_output" );
		}

		// Initialize variables for capturing the domain.
		$domain     = null;
		$start_time = time();
		$timeout    = 60; // seconds.

		// Loop to check the container logs for the domain.
		while ( ( time() - $start_time ) < $timeout ) {
			exec( "{$docker->find_docker()} logs {$docker_container_name} 2>&1", $logs_output, $logs_return );

			$logs = implode( "\n", $logs_output );
			if ( preg_match( '#https://[a-zA-Z0-9\-]+\.trycloudflare\.com#', $logs, $matches ) ) {
				$domain = $matches[0];
				break;
			}

			usleep( 500000 ); // 0.5 seconds.
		}

		if ( $output && $output->isVeryVerbose() ) {
			$output->writeln( $logs );
		}

		if ( $domain === null ) {
			TunnelRunner::stop_tunnel( $env_id );
			throw new \RuntimeException( 'Timed out waiting for tunnel domain.' );
		}

		self::test_connection( $domain, TunnelRunner::$tunnel_map['cloudflared-docker'] );

		return $domain;
	}

	public static function is_supported(): bool {
		if ( is_wsl() || is_mac() ) {
			return false;
		}

		return true;
	}

	public static function is_configured(): bool {
		// No additional configuration required.
		return true;
	}
}