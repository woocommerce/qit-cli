<?php

namespace QIT_CLI\Tunnel\Tunnels;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Tunnel\Tunnel;
use QIT_CLI\Tunnel\TunnelRunner;
use function QIT_CLI\is_wsl;

class JurassicTubeTunnel extends Tunnel {
	public static function connect_tunnel( string $local_url, string $env_id ): string {
		// When the environment is destroyed, we will try to kill the process with this PID.
		$pid_file = sys_get_temp_dir() . "/qit_env_tunnel_{$env_id}.pid";

		// Load configuration from cache.
		$configs = App::make( Cache::class )->get( 'tunnel_configs' );
		$config  = $configs['jurassictube'] ?? null;

		if ( ! isset( $config['username'], $config['subdomain'] ) ) {
			throw new \InvalidArgumentException( 'JurassicTube username and subdomain must be configured. Run "qit tunnel:setup jurassictube" to configure.' );
		}

		$jurassic_tube_user      = $config['username'];
		$jurassic_tube_subdomain = $config['subdomain'];

		// Extract host and port from $site_url.
		$parsed_url = parse_url( $local_url );

		if ( ! isset( $parsed_url['host'] ) ) {
			throw new \InvalidArgumentException( "Invalid site URL provided." );
		}

		$host = $parsed_url['host'];
		$port = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';

		$host_and_port = $host . $port;

		// Construct the JurassicTube command.
		$command_parts = [
			'nohup',
			'jurassictube',
			'-u',
			escapeshellarg( $jurassic_tube_user ),
			'-s',
			escapeshellarg( $jurassic_tube_subdomain ),
			'-h',
			escapeshellarg( $host_and_port ),
		];

		$command = implode( ' ', $command_parts ) . ' > /dev/null 2>&1 & echo $!';

		// Start the process and capture the PID.
		$output_exec = [];
		exec( $command, $output_exec );

		if ( empty( $output_exec ) ) {
			throw new \RuntimeException( 'Failed to start JurassicTube process.' );
		}

		$pid = (int) $output_exec[0];

		// Save the PID to the pid file.
		file_put_contents( $pid_file, $pid );

		// Construct the tunnel URL.
		$tunnel_url = "https://{$jurassic_tube_subdomain}.jurassic.tube/";

		self::test_connection( $tunnel_url, TunnelRunner::$tunnel_map['jurassictube'] );

		return $tunnel_url;
	}

	public static function is_supported(): bool {
		if ( is_wsl() ) {
			return false;
		}

		// Verify that "jurassictube" is installed.
		exec( "which jurassictube", $output, $return_var );

		return $return_var === 0;
	}

	public static function is_configured(): bool {
		$configs = App::make( Cache::class )->get( 'tunnel_configs' );
		$config  = $configs['jurassictube'] ?? null;

		return isset( $config['username'], $config['subdomain'] );
	}
}