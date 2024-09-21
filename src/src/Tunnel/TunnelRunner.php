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
		// Determine the region, defaulting to 'us' if not set.
		$region    = getenv( 'QIT_TUNNEL_REGION' ) ?: 'us';
		$local_url = escapeshellarg( $local_url );

		$docker_container_name = "qit_env_tunnel_$env_id";

		// Validate $region is a-z.
		if ( ! preg_match( '/^[a-z]+$/', $region ) ) {
			throw new \InvalidArgumentException( 'Invalid region specified.' );
		}

		// Start the Docker container in detached mode.
		exec( "{$this->docker->find_docker()} run -d --net=host --name=$docker_container_name cloudflare/cloudflared:latest tunnel --no-autoupdate --region=$region --url=$local_url", $runOutput, $runReturn );

		if ( $runReturn !== 0 ) {
			$errorOutput = implode( "\n", $runOutput );
			throw new \RuntimeException( "Failed to start tunnel container: $errorOutput" );
		}

		// Initialize variables for capturing the domain.
		$domain     = null;
		$start_time = time();
		$timeout    = 60; // seconds

		// Loop to check the container logs for the domain.
		while ( ( time() - $start_time ) < $timeout ) {
			exec( "{$this->docker->find_docker()} logs $docker_container_name 2>&1", $logsOutput, $logsReturn );

			$logs = implode( "\n", $logsOutput );
			if ( preg_match( '#https://[a-zA-Z0-9\-]+\.trycloudflare\.com#', $logs, $matches ) ) {
				$domain = $matches[0];
				break;
			}

			if ( $this->output->isVeryVerbose() ) {
				$this->output->writeln( $logs );
			}

			usleep( 500000 ); // 0.5 seconds
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
