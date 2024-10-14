<?php

namespace QIT_CLI\Tunnel;

use QIT_CLI\Environment\Docker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function QIT_CLI\is_mac;
use function QIT_CLI\is_wsl;

class TunnelRunner {
	/** @var Docker */
	protected $docker;

	/** @var OutputInterface */
	protected $output;

	/** @var 'docker'|'local'|'custom' $tunnel_type Tunnel runs in "docker" on Linux and in "local" on Mac. Initialized as "local" for convenience. */
	protected static $tunnel_type = 'local';

	public function __construct( Docker $docker, OutputInterface $output ) {
		$this->docker = $docker;
		$this->output = $output;
	}

	public function check_tunnel_support( string $tunnel_type ): void {
		// Determine the tunnel type based on the provided parameter or auto-detection.
		if ( $tunnel_type !== 'auto' ) {
			if ( ! in_array( $tunnel_type, [ 'docker', 'local', 'custom' ], true ) ) {
				throw new \InvalidArgumentException( 'Invalid tunnel type "' . $tunnel_type . '" specified. Allowed values are "docker", "local" or "custom".' );
			}
			static::$tunnel_type = $tunnel_type;
		} else {
			// Auto-detect tunnel type based on the operating system.
			if ( ! is_mac() && ! is_wsl() ) {
				// Assuming it's Linux.
				static::$tunnel_type = 'docker';
			} elseif ( is_mac() ) {
				static::$tunnel_type = 'local';
			} elseif ( is_wsl() ) {
				throw new \RuntimeException( 'The "--tunnel" option is not supported in WSL.' );
			}
		}

		if ( static::$tunnel_type === 'docker' ) {
			if ( is_mac() ) {
				throw new \RuntimeException( 'Docker tunnels are not supported on macOS, as it requires network mode host. Use local tunnel instead.' );
			}

			return;
		}

		if ( static::$tunnel_type === 'custom' ) {
			return;
		}

		// If the tunnel type is 'local', verify that "cloudflared" is installed.
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
			);
			// phpcs:enable WordPress.Security.EscapeOutput.HeredocOutputNotEscaped
		}
	}

	/**
	 * @link https://stackoverflow.com/a/76131126/2056484 For additional context.
	 *
	 * @return string The tunnel type to use.
	 */
	public static function get_tunnel_value( InputInterface $input ): string {
		$tunnel = $input->getParameterOption( '--tunnel', 'no_tunnel', true ) ?? 'auto';

		if ( ! in_array( $tunnel, [ 'auto', 'docker', 'local', 'custom', 'no_tunnel' ], true ) ) {
			return 'auto';
		}

		return $tunnel;
	}

	public function start_tunnel( string $local_url, string $env_id ): string {
		if ( static::$tunnel_type === 'custom' ) {
			$classes = get_declared_classes();
			foreach ( $classes as $class ) {
				if ( is_subclass_of( $class, CustomTunnel::class ) ) {
					return $class::connect_tunnel( $local_url, $env_id );
				}
			}

			throw new \RuntimeException( 'No custom tunnel found.' );
		}

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

	/**
	 * We cannot run a Docker tunnel on Mac due to network limitations.
	 *
	 * The easiest way to get Docker tunnel to work is with "network mode" set to "host",
	 * which is only available in Linux.
	 *
	 * We couldn't get around it with other methods with Docker, so we fallback to
	 * a local "cloudflared" binary running on the host.
	 *
	 * @returns string The tunnelled URL.
	 */
	public function start_tunnel_local( string $local_url, string $env_id, string $region ): string {
		$pid_file    = sys_get_temp_dir() . "/qit_env_tunnel_{$env_id}.pid";
		$output_file = sys_get_temp_dir() . "/qit_env_tunnel_{$env_id}.log";

		$command_parts = [
			'nohup',
			'cloudflared',
			'tunnel',
			'--no-autoupdate',
			'--pidfile',
			escapeshellarg( $pid_file ),
		];

		if ( $region !== '' ) {
			$command_parts[] = escapeshellarg( $region );
		}

		$command_parts[] = '--url';
		$command_parts[] = escapeshellarg( $local_url );

		$command = implode( ' ', $command_parts );

		/*
		 * We have a couple of requirements:
		 * - The tunnel process must continue running after this PHP script terminates.
		 * - We need to capture the output of the 'cloudflared' process to extract the generated domain URL.
		 *
		 * PHP struggles at running things on the background detached from the current process.
		 *
		 * To achieve this, we:
		 * - Use 'nohup' to prevent the process from terminating when the PHP script ends or the console is closed.
		 * - Redirect both stdout and stderr to the output file.
		 * - Run the command in the background.
		 *
		 * Then we keep reading from the output file until we find the tunnelled URL, or we bail.
		 *
		 * @link https://stackoverflow.com/a/3819422/2056484
		 * @link https://github.com/cocur/background-process/blob/master/src/BackgroundProcess.php
		 */
		$command .= ' > ' . escapeshellarg( $output_file ) . ' 2>&1 &';

		exec( $command );

		$start_time = time();
		$timeout    = 60; // seconds.
		$domain     = null;

		// Wait for the PID file to be created.
		while ( ! file_exists( $pid_file ) && ( time() - $start_time ) < $timeout ) {
			usleep( 100000 ); // 0.1 seconds.
		}

		if ( ! file_exists( $pid_file ) ) {
			throw new \RuntimeException( 'Timed out waiting for PID file creation.' );
		}

		$output = '';

		// Loop to get the domain from the output file.
		while ( ( time() - $start_time ) < $timeout ) {
			if ( file_exists( $output_file ) ) {
				$output = file_get_contents( $output_file );

				if ( preg_match( '#https://[a-zA-Z0-9\-]+\.trycloudflare\.com#', $output, $matches ) ) {
					$domain = $matches[0];
					break;
				}
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

		if ( file_exists( $output_file ) ) {
			unlink( $output_file );
		}

		$this->test_connection_cloudflare( $domain );

		return $domain;
	}

	/**
	 * Spins up a tunnel using Docker. Only works on Linux.
	 *
	 * @return string The tunnelled URL.
	 */
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

		$this->test_connection_cloudflare( $domain );

		return $domain;
	}

	protected function test_connection_cloudflare( string $site_url ): void {
		$max_retries = 1;
		$attempt     = 0;

		while ( $attempt <= $max_retries ) {
			try {
				CustomTunnel::test_connection( $site_url );

				return;
			} catch ( \Exception $e ) {
				$this->output->writeln( '<comment>The connection to the tunnel timed out. This is usually because the DNS hasn\'t propagated yet. If you are using a different DNS, consider switching to Cloudflare DNS (1.1.1.1) for better performance.</comment>' );

				if ( $attempt < $max_retries ) {
					$this->output->writeln( '<comment>Retrying connection test...</comment>' );
				} else {
					throw $e;
				}
			} finally {
				++$attempt;
			}
		}
	}

	public static function stop_tunnel( string $env_id ): void {
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
			$p = new Process( [ 'docker', 'rm', '-f', "qit_env_tunnel_$env_id" ] );
			$p->run();
		}
	}
}
