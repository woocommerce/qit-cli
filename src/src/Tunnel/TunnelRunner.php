<?php

namespace QIT_CLI\Tunnel;

use QIT_CLI\Environment\Docker;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TunnelRunner {
	/** @var Docker */
	protected $docker;

	/** @var OutputInterface */
	protected $output;

	public function __construct( Docker $docker, OutputInterface $output ) {
		$this->docker = $docker;
		$this->output = $output;
	}

	public function start_tunnel( string $local_url, string $env_id ): string {
		if ( ! empty( getenv( 'QIT_TUNNEL_REGION' ) ) ) {
			// Validate $region is a-z.
			if ( ! preg_match( '/^[a-z]+$/', getenv( 'QIT_TUNNEL_REGION' ) ) ) {
				throw new \InvalidArgumentException( 'Invalid region specified.' );
			}
			$region = '--region=' . getenv( 'QIT_TUNNEL_REGION' );
		} else {
			// If not defined, Cloudflare will allocate the fastest region to the requester.
			// Some payment gateways might require "us" region, so we allow them to override it if needed.
			$region = '';
		}

		$local_url             = escapeshellarg( $local_url );
		$docker_container_name = "qit_env_tunnel_$env_id";

		// Start the Docker container in detached mode.
		exec( "{$this->docker->find_docker()} run -d --net=host --name=$docker_container_name cloudflare/cloudflared:latest tunnel --no-autoupdate $region --url=$local_url", $run_output, $run_return );

		if ( $run_return !== 0 ) {
			$error_output = implode( "\n", $run_output );
			throw new \RuntimeException( "Failed to start tunnel container: $error_output" );
		}

		// Initialize variables for capturing the domain.
		$domain      = null;
		$start_time  = time();
		$timeout     = 60; // seconds.
		$logs_output = [];

		// Loop to check the container logs for the domain.
		while ( ( time() - $start_time ) < $timeout ) {
			exec( "{$this->docker->find_docker()} logs $docker_container_name 2>&1", $logs_output, $logs_return );

			$logs = implode( "\n", $logs_output );
			if ( preg_match( '#https://[a-zA-Z0-9\-]+\.trycloudflare\.com#', $logs, $matches ) ) {
				$domain = $matches[0];
				break;
			}

			usleep( 500000 ); // 0.5 seconds
		}

		if ( $this->output->isVeryVerbose() ) {
			foreach ( $logs_output as $log ) {
				$this->output->writeln( $log );
			}
		}

		if ( $domain === null ) {
			static::stop_tunnel( $env_id );
			throw new \RuntimeException( 'Timed out waiting for tunnel domain.' );
		}

		return $domain;
	}

	public static function stop_tunnel( string $env_id ): void {
		$p = new Process( [ 'docker', 'rm', '-f', "qit_env_tunnel_$env_id" ] );
		$p->run();
	}
}
