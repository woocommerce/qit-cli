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

	/** @var Process */
	protected static $tunnel_process;

	public function __construct( Docker $docker, OutputInterface $output ) {
		$this->docker = $docker;
		$this->output = $output;
	}

	public function start_tunnel( string $local_url ): string {
		// Determine the region, defaulting to 'us' if not set.
		$region = getenv( 'QIT_TUNNEL_REGION' ) ?: 'us';

		// Build the Docker command to start the Cloudflare Tunnel.
		$docker_command = [
			$this->docker->find_docker(),
			'run',
			'--net=host',
			'--name=qit_tunnel',
			'cloudflare/cloudflared:latest',
			'tunnel',
			'--no-autoupdate',
			"--region=$region",
			"--url=$local_url",
		];

		// Initialize the Symfony Process with the Docker command.
		$process = new Process( $docker_command );
		$process->setTty( false );
		$process->setTimeout( 120 );      // Total timeout for the process.
		$process->setIdleTimeout( 120 );  // Idle timeout for the process.

		// If verbose output is enabled, print the command that will run.
		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( $process->getCommandLine() );
		}

		// Variable to store the captured domain.
		$domain     = null;
		$start_time = time();

		// Start the process with a callback to handle real-time output.
		$process->start( function ( $type, $buffer ) use ( &$domain ) {
			// Write the output buffer to the console.
			$this->output->write( $buffer );

			// Use regex to match the domain pattern in the output.
			// Example pattern: https://cancer-bundle-connector-attractions.trycloudflare.com
			if ( preg_match( '#https://[a-zA-Z0-9\-]+\.trycloudflare\.com#', $buffer, $matches ) ) {
				$domain = $matches[0];
			}
		} );

		// Store the process in a static property for potential later use.
		static::$tunnel_process = $process;

		/*
		 * Register a shutdown function to stop the tunnel if not in test mode.
		 * This ensures the tunnel is cleaned up when the script terminates.
		 */
		if ( getenv( 'QIT_UP_AND_TEST' ) !== '1' ) {
			register_shutdown_function( [ __CLASS__, 'stop_tunnel' ] );
		}

		// Loop for up to 60 seconds to wait for the domain to be captured.
		while ( time() - $start_time < 60 ) {
			// If the domain has been captured, return the domain without stopping the process.
			if ( $domain !== null ) {
				return $domain;
			}

			// If the process has exited before capturing the domain, throw an exception.
			if ( ! $process->isRunning() ) {
				$process_output = $process->getOutput() . $process->getErrorOutput();
				$this->output->writeln( $process_output );
				throw new \RuntimeException( 'Tunnel process terminated before domain was found.' );
			}

			// Sleep for 0.5 seconds before checking again.
			usleep( 500000 ); // 500,000 microseconds = 0.5 seconds.
		}

		// If the loop completes without finding the domain, stop the process and throw a timeout exception.
		$process->stop( 1 ); // Send SIGTERM and wait 1 second.
		throw new \RuntimeException( 'Timed out waiting for tunnel domain.' );
	}

	public static function stop_tunnel(): void {
		if ( static::$tunnel_process instanceof Process ) {
			if ( static::$tunnel_process->isRunning() ) {
				static::$tunnel_process->stop();
			}
		}

		$p = new Process( [ 'docker', 'rm', '-f', 'qit_tunnel' ] );
		$p->run();
	}

	public function remove_exited_tunnel_docker_container_if_it_exists(): void {

	}

	/**
	 * Checks if the Cloudflare Tunnel is running.
	 * Removes any exited containers named 'qit_tunnel' without prompting.
	 *
	 * @return bool True if a tunnel is running, false otherwise.
	 *
	 * @throws \RuntimeException If there is an error listing or removing Docker containers.
	 */
	public function is_tunnel_running(): bool {
		// Build the Docker command to list all containers named 'qit_tunnel', including exited ones.
		$docker_command = [
			$this->docker->find_docker(),
			'ps',
			'-a', // Include all containers, not just running ones.
			'--filter',
			'name=qit_tunnel',
			'--format',
			'{{.Names}} {{.Status}}', // Format to include both Name and Status.
		];

		// Initialize the Symfony Process with the Docker command.
		$process = new Process( $docker_command );
		$process->setTty( false );
		$process->setTimeout( 30 );      // Total timeout for the process.
		$process->setIdleTimeout( 30 );  // Idle timeout for the process.

		// Run the process and capture the output.
		$process->run();

		// Check if the Docker command executed successfully.
		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'Failed to list Docker containers: ' . $process->getErrorOutput() );
		}

		// Get the output and split it into lines.
		$output = trim( $process->getOutput() );
		if ( empty( $output ) ) {
			// No containers named 'qit_tunnel' found.
			return false;
		}

		// Split the output into individual lines.
		$lines = explode( "\n", $output );

		// Iterate through each line to process the containers.
		foreach ( $lines as $line ) {
			// Each line contains 'Name Status'
			// Example: 'qit_tunnel Up 5 minutes'
			// Example: 'qit_tunnel Exited (0) 2 minutes ago'

			// Split the line into name and status.
			$parts = explode( ' ', $line, 2 );
			if ( count( $parts ) < 2 ) {
				// Malformed line; skip processing.
				continue;
			}

			list( $name, $status ) = $parts;

			if ( $name !== 'qit_tunnel' ) {
				// Not the tunnel container; skip.
				continue;
			}

			if ( stripos( $status, 'Up' ) === 0 ) {
				// Container is running.
				return true;
			} elseif ( stripos( $status, 'Exited' ) === 0 ) {
				// Container has exited; remove it without prompting.
				$removeCommand = [
					$this->docker->find_docker(),
					'rm',
					'-f', // Force removal of the container.
					$name,
				];

				// Initialize the Symfony Process with the Docker remove command.
				$removeProcess = new Process( $removeCommand );
				$removeProcess->setTty( false );
				$removeProcess->setTimeout( 30 );      // Total timeout for the process.
				$removeProcess->setIdleTimeout( 30 );  // Idle timeout for the process.

				// Run the remove process.
				$removeProcess->run();

				// Check if the removal was successful.
				if ( ! $removeProcess->isSuccessful() ) {
					throw new \RuntimeException( 'Failed to remove exited container "' . $name . '": ' . $removeProcess->getErrorOutput() );
				}

				// Optionally, log the removal.
				$this->output->writeln( 'Removed exited container: ' . $name . '.' );
			}
		}

		// After processing all containers, if none are running, return false.
		return false;
	}
}
