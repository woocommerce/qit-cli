<?php

namespace QIT_CLI\Tunnel;

use QIT_CLI\App;
use QIT_CLI\Cache;

class TunnelRunner {
	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	public function start_tunnel( string $local_url, string $env_id, string $network_name ): string {
		return $this->get_tunnel()->start_tunnel( $local_url, $env_id, $network_name );
	}

	public function stop_tunnel( string $env_id ): void {
		$this->get_tunnel()->stop_tunnel( $env_id );
	}

	public function set_tunnel( string $tunnel_id ): void {
		$this->cache->set( 'tunnel', $tunnel_id, - 1 );
	}

	public function get_tunnel(): Tunnel {
		$tunnel_id = $this->cache->get( 'tunnel' );

		// If no tunnel is previously configured and the Cloudflared Docker tunnel is available, use it by default.
		// This will be mostly used in CI.
		if ( empty( $tunnel_id ) ) {
			if ( App::make( CloudflaredDockerTunnel::class )->is_available() ) {
				return App::make( CloudflaredDockerTunnel::class );
			}
		}

		switch ( $tunnel_id ) {
			case 'cloudflared_docker':
				return App::make( CloudflaredDockerTunnel::class );
			case 'cloudflared_local':
				return App::make( CloudflaredLocalTunnel::class );
			case 'jurassic_tube_local':
				return App::make( JurassicTubeTunnel::class );
			default:
				throw new \InvalidArgumentException( 'Invalid tunnel ID.' );
		}
	}
}