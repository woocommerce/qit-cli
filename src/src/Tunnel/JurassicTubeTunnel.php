<?php

namespace QIT_CLI\Tunnel;

use QIT_CLI\Cache;
use Symfony\Component\Process\Process;

class JurassicTubeTunnel extends Tunnel {
	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	public function get_id(): string {
		return 'jurassic_tube_local';
	}

	public function is_available(): bool {
		$process = new Process( [ 'jurassictube' ] );
		$process->run();

		return strpos( $process->getOutput(), 'Usage: jurassictube' ) !== false;
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

	public function configure_tunnel() {

	}

	public function add_domain( string $domain ): void {
		/*
		 *
		 */
	}

	public function list_domains(): array {

	}

	public function remove_domain( string $domain ): void {

	}

}