<?php

namespace QIT_CLI\Tunnel;

use Symfony\Component\Process\Process;

class CloudflaredLocalTunnel extends Tunnel {
	public function get_id(): string {
		return 'cloudflared_local';
	}

	/**
	 * Try to execute "cloudfared" version and check if it's available.
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		$process = new Process( [ 'cloudflared', '--version' ] );
		$process->run();

		return $process->isSuccessful();
	}

	public function is_configured(): bool {
		// TODO: Implement is_configured() method.
	}

	public function start_tunnel( string $local_url, string $env_id, string $network_name ): string {
		// TODO: Implement start_tunnel() method.
	}

	public function stop_tunnel( string $env_id ): void {
		// TODO: Implement stop_tunnel() method.
	}
}