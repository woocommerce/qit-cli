<?php

namespace QIT_CLI\Tunnel;

use QIT_CLI\Environment\Docker;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function QIT_CLI\is_mac;
use function QIT_CLI\is_wsl;

class TunnelRunner {
	/** @var Docker */
	protected $docker;

	/** @var OutputInterface */
	protected $output;

	/** @var 'docker'|'local' $tunnel_type Tunnel runs in "docker" on Linux and in "local" on Mac. Initialized as "local" for convenience. */
	protected static $tunnel_type = 'local';

	public function __construct( Docker $docker, OutputInterface $output ) {
		$this->docker = $docker;
		$this->output = $output;
	}

	public function check_tunnel_support(): void {
		if ( ! is_mac() && ! is_wsl() ) {
			// Linux.
			static::$tunnel_type = 'docker';

			return;
		} elseif ( is_mac() ) {
			static::$tunnel_type = 'local';
		} elseif ( is_wsl() ) {
			throw new \RuntimeException( 'The "--tunnel" option is not supported in WSL.' );
		}

		// Check if "cloudflared" is installed.
		$process = new Process( [ 'cloudflared', '--version' ] );
		$process->run();

		if ( ! $process->isSuccessful() ) {
			$install_command = '';
			$os_name         = '';

			if ( is_mac() ) {
				$install_command = 'brew install cloudflared';
				$os_name         = 'macOS';
			} else {
				$install_command = 'Refer to the Cloudflare documentation for installation instructions.';
				$os_name         = 'your operating system';
			}

			// phpcs:disable WordPress.Security.EscapeOutput.HeredocOutputNotEscaped
			throw new \RuntimeException( <<<NOTICE
The "cloudflared" binary is required to use tunnels on {$os_name}.

You can install it by running the following command in your terminal:

    <comment>{$install_command}</comment>
    
After installation, re-run your command. No additional configuration is needed.

For detailed installation instructions and more information, visit the https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/

NOTICE
			// phpcs:enable WordPress.Security.EscapeOutput.HeredocOutputNotEscaped
			);
		}
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

		if ( static::$tunnel_type === 'docker' ) {
			return $this->start_tunnel_docker( $local_url, $env_id, $region );
		} else {
			return $this->start_tunnel_local( $local_url, $env_id, $region );
		}
	}

	public function start_tunnel_local( string $local_url, string $env_id, string $region ): string {
		$pid_file = sys_get_temp_dir() . "/qit_env_tunnel_{$env_id}.pid";

		$command = [
			'cloudflared',
			'tunnel',
			'--no-autoupdate',
			'--pidfile',
			$pid_file,
		];
		if ( $region !== '' ) {
			$command[] = $region;
		}
		$command[] = '--url';
		$command[] = $local_url;

		$process = new Process( $command );
		$process->setTimeout( null );
		$process->start();

		$start_time = time();
		$timeout    = 60; // seconds.
		$domain     = null;
		$output     = '';

		// Wait for the PID file to be created.
		while ( ! file_exists( $pid_file ) && ( time() - $start_time ) < $timeout ) {
			if ( ! $process->isRunning() ) {
				throw new \RuntimeException( 'cloudflared process terminated unexpectedly.' );
			}
			usleep( 100000 ); // 0.1 seconds.
		}

		if ( ! file_exists( $pid_file ) ) {
			throw new \RuntimeException( 'Timed out waiting for PID file creation.' );
		}

		// Loop to get the domain from the process output.
		while ( ( time() - $start_time ) < $timeout ) {
			// Get the incremental output.
			$output .= $process->getIncrementalOutput() . $process->getIncrementalErrorOutput();

			if ( preg_match( '#https://[a-zA-Z0-9\-]+\.trycloudflare\.com#', $output, $matches ) ) {
				$domain = $matches[0];
				break;
			}

			if ( ! $process->isRunning() ) {
				// Clean up PID file.
				if ( file_exists( $pid_file ) ) { // @phpstan-ignore-line
					unlink( $pid_file );
				}
				throw new \RuntimeException( 'cloudflared process terminated unexpectedly.' );
			}

			usleep( 500000 ); // 0.5 seconds.
		}

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( $output );
		}

		if ( $domain === null ) {
			static::stop_tunnel( $env_id );
			throw new \RuntimeException( 'Timed out waiting for tunnel domain.' );
		}

		return $domain;
	}

	public function start_tunnel_docker( string $local_url, string $env_id, string $region ): string {
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

	public static function stop_tunnel( string $env_id, bool $force_local = false ): void {
		if ( ! $force_local && static::$tunnel_type === 'docker' ) {
			$p = new Process( [ 'docker', 'rm', '-f', "qit_env_tunnel_$env_id" ] );
			$p->run();
		} else {
			// Retrieve the PID from the pidfile.
			$pid_file = sys_get_temp_dir() . "/qit_env_tunnel_{$env_id}.pid";
			if ( file_exists( $pid_file ) ) {
				$pid = trim( file_get_contents( $pid_file ) );
				if ( $pid ) {
					// Unix/Linux/MacOS.
					$kill_command = [ 'kill', $pid ];
					$kill_process = new Process( $kill_command );
					$kill_process->run();
				}
				unlink( $pid_file );
			} else {
				throw new \RuntimeException( 'PID file not found. Unable to stop the tunnel.' );
			}
		}
	}
}
