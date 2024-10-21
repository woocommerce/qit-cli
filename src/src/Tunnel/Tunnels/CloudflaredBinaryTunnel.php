<?php

namespace QIT_CLI\Tunnel\Tunnels;

use QIT_CLI\App;
use QIT_CLI\Tunnel\Tunnel;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\is_mac;
use function QIT_CLI\is_wsl;

class CloudflaredBinaryTunnel extends Tunnel {
	public static function connect_tunnel( string $local_url, string $env_id ): string {
		$output = App::make( OutputInterface::class );

		$pid_file    = sys_get_temp_dir() . "/qit_env_tunnel_{$env_id}.pid";
		$output_file = sys_get_temp_dir() . "/qit_env_tunnel_{$env_id}.log";

		$region = '';

		if ( ! empty( getenv( 'QIT_TUNNEL_REGION' ) ) ) {
			// Validate $region is a-z.
			if ( ! preg_match( '/^[a-z]+$/', getenv( 'QIT_TUNNEL_REGION' ) ) ) {
				throw new \InvalidArgumentException( 'Invalid region specified.' );
			}
			$region = '--region=' . getenv( 'QIT_TUNNEL_REGION' );
		}

		$pid_file_escaped  = escapeshellarg( $pid_file );
		$local_url_escaped = escapeshellarg( $local_url );

		$command_parts = [
			'nohup',
			'cloudflared',
			'tunnel',
			'--no-autoupdate',
			'--pidfile',
			$pid_file_escaped,
		];

		if ( $region !== '' ) {
			$command_parts[] = $region;
		}

		$command_parts[] = '--url';
		$command_parts[] = $local_url_escaped;

		$command = implode( ' ', $command_parts );

		$command .= ' > ' . escapeshellarg( $output_file ) . ' 2>&1 &';

		exec( $command );

		$start_time = time();
		$timeout    = 60; // seconds.
		$domain     = null;

		// Wait for the PID file to be created.
		while ( ! file_exists( $pid_file ) && ( time() - $start_time ) < $timeout ) {
			usleep( 500000 ); // 0.5 seconds.
		}

		if ( ! file_exists( $pid_file ) ) {
			throw new \RuntimeException( 'Timed out waiting for PID file creation.' );
		}

		$output_content = '';

		// Loop to get the domain from the output file.
		while ( ( time() - $start_time ) < $timeout ) {
			if ( file_exists( $output_file ) ) {
				$output_content = file_get_contents( $output_file );

				if ( preg_match( '#https://[a-zA-Z0-9\-]+\.trycloudflare\.com#', $output_content, $matches ) ) {
					$domain = $matches[0];
					break;
				}
			}

			usleep( 500000 ); // 0.5 seconds.
		}

		if ( $output && $output->isVeryVerbose() ) {
			$output->writeln( $output_content );
		}

		if ( $domain === null ) {
			TunnelRunner::stop_tunnel( $env_id );
			throw new \RuntimeException( 'Timed out waiting for tunnel domain.' );
		}

		if ( file_exists( $output_file ) ) {
			unlink( $output_file );
		}

		self::test_connection( $domain, TunnelRunner::$tunnel_map['cloudflared-binary'] );

		return $domain;
	}

	public static function check_is_installed(): void {
		if ( is_wsl() ) {
			throw new \RuntimeException( 'Cloudflared binary tunnel is not supported on Windows Subsystem for Linux (WSL).' );
		}

		// Verify that "cloudflared" is installed.
		exec( 'which cloudflared', $output, $return_var );

		if ( $return_var !== 0 ) {
			$output           = App::make( OutputInterface::class );
			$installation_url = 'https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/';

			if ( is_mac() ) {
				$output->writeln( '<error>Cloudflared is not installed.</error>' );
				$output->writeln( 'Please install it using Homebrew by running the following command:' );
				$output->writeln( '<info>brew install cloudflared</info>' );
				$output->writeln( "For detailed installation instructions, visit: $installation_url" );
			} else {
				$output->writeln( '<error>Cloudflared is not installed.</error>' );
				$output->writeln( 'Please install it using the appropriate package manager for your operating system.' );
				$output->writeln( "For detailed installation instructions, visit: $installation_url" );
			}
		}
	}

	public static function is_configured(): bool {
		// No additional configuration required.
		return true;
	}
}
