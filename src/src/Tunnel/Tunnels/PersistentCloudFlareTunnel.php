<?php

namespace QIT_CLI\Tunnel\Tunnels;

use QIT_CLI\App;
use QIT_CLI\Tunnel\Tunnel;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function QIT_CLI\is_wsl;

class PersistentCloudFlareTunnel extends Tunnel {
	public static function connect_tunnel( string $local_url, string $env_id ): string {
		$cache  = App::make( \QIT_CLI\Cache::class );
		$output = App::make( OutputInterface::class );

		// When the environment is destroyed, we will try to kill the process with this PID.
		$pid_file = sys_get_temp_dir() . "/qit_env_tunnel_{$env_id}.pid";

		// Load configuration from cache.
		$configs = $cache->get( 'tunnel_configs' );
		$config  = $configs['cloudflared-persistent'] ?? null;

		if ( ! isset( $config['tunnel_name'], $config['tunnel_url'] ) ) {
			throw new \InvalidArgumentException( 'Cloudflare tunnel name and URL must be configured. Run "qit tunnel:setup" to configure.' );
		}

		$cloudflare_tunnel_name = $config['tunnel_name'];
		$cloudflare_tunnel_url  = $config['tunnel_url'];

		$pid_file_escaped    = escapeshellarg( $pid_file );
		$site_url_escaped    = escapeshellarg( $local_url );
		$tunnel_name_escaped = escapeshellarg( $cloudflare_tunnel_name );

		// Construct the command.
		$command = "nohup cloudflared tunnel --no-autoupdate --pidfile {$pid_file_escaped} --url {$site_url_escaped} run {$tunnel_name_escaped} > /dev/null 2>&1 &";

		exec( $command, $output_exec, $return_var );

		$start_time = time();
		$timeout    = 10; // seconds.

		// Wait for the PID file to be created.
		while ( ! file_exists( $pid_file ) && ( time() - $start_time ) < $timeout ) {
			usleep( 500000 ); // 0.5 seconds.
		}

		if ( ! file_exists( $pid_file ) ) {
			// Optionally log the output for debugging.
			if ( $output && ! empty( $output_exec ) ) {
				$output->writeln( implode( "\n", $output_exec ) );
			}

			throw new \RuntimeException( 'Timed out waiting for PID file creation.' );
		}

		self::test_connection( $cloudflare_tunnel_url, TunnelRunner::$tunnel_map['cloudflared-persistent'] );

		return $cloudflare_tunnel_url;
	}

	public static function is_supported(): bool {
		if ( is_wsl() ) {
			return false;
		}

		// Verify that "cloudflared" is installed.
		exec( "which cloudflared", $output, $return_var );

		return $return_var === 0;
	}

	public static function is_configured(): bool {
		$cache = App::make( \QIT_CLI\Cache::class );

		$configs = $cache->get( 'tunnel_configs' );
		$config  = $configs['cloudflared-persistent'] ?? null;

		return isset( $config['tunnel_name'], $config['tunnel_url'] );
	}

	private static function check_persistent_cloudflared_tunnel_exists( string $tunnel_name ): bool {
		$process = new Process( [ 'cloudflared', 'tunnel', 'info', $tunnel_name ] );
		$process->run();

		return $process->isSuccessful();
	}

	public static function check_is_available(): void {
		$cache  = App::make( \QIT_CLI\Cache::class );
		$output = App::make( OutputInterface::class );

		$configs = $cache->get( 'tunnel_configs' );
		$config  = $configs['cloudflared-persistent'] ?? null;

		$process = new Process( [ 'cloudflared', 'tunnel', 'info', '--output=json', $config['tunnel_name'] ] );
		$process->run();

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( sprintf( 'Running "cloudflared tunnel info %s" returned an error, which indicates the tunnel does not exist. Please setup the tunnel again', $config['tunnel_name'] ) );
		}

		$json = json_decode( $process->getOutput(), true );

		if ( empty( $json ) ) {
			// Print a warning in the output but does not throw an error, as we could not parse the output.
			$output->writeln( '<comment>Could not parse the output of "cloudflared tunnel info".</comment>' );

			return;
		}

		if ( ! empty( $json['conns'] ) ) {
			// The tunnel has active connections, so it's in use.
			throw new \RuntimeException( sprintf( 'The tunnel "%s" is already in use. Please stop the environment using this tunnel before proceeding.', $config['tunnel_name'] ) );
		}
	}
}