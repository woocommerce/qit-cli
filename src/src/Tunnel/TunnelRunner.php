<?php

namespace QIT_CLI\Tunnel;

use QIT_CLI\App;
use QIT_CLI\Tunnel\Tunnels\CloudflaredBinaryTunnel;
use QIT_CLI\Tunnel\Tunnels\CloudflaredDockerTunnel;
use QIT_CLI\Tunnel\Tunnels\JurassicTubeTunnel;
use QIT_CLI\Tunnel\Tunnels\PersistentCloudFlareTunnel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function QIT_CLI\is_mac;
use function QIT_CLI\is_wsl;

class TunnelRunner {
	/** @var string $tunnel_type */
	protected $tunnel_type;

	/** @var string|Tunnel $tunnel_class */
	protected $tunnel_class;

	/** @var array<string, string|Tunnel> $tunnel_map */
	public static $tunnel_map = [
		'cloudflared-docker'     => CloudflaredDockerTunnel::class,
		'cloudflared-binary'     => CloudflaredBinaryTunnel::class,
		'cloudflared-persistent' => PersistentCloudflareTunnel::class,
		'jurassictube'           => JurassicTubeTunnel::class,
	];

	/**
	 * @link https://stackoverflow.com/a/76131126/2056484 For additional context.
	 *
	 * @return string The tunnel type to use.
	 */
	public static function get_tunnel_value( InputInterface $input ): string {
		// Get the value of the --tunnel option, defaulting to 'no_tunnel' if not specified.
		$tunnel = $input->getParameterOption( '--tunnel', 'no_tunnel', true );

		// Allowed tunnel values are the keys of $tunnel_map plus 'auto', 'custom', 'no_tunnel'.
		$allowed_tunnels = array_merge( array_keys( self::$tunnel_map ), [ 'auto', 'custom', 'no_tunnel' ] );

		if ( ! in_array( $tunnel, $allowed_tunnels, true ) ) {
			// If an invalid tunnel type is provided, default to 'auto'.
			$tunnel = 'auto';
		}

		return $tunnel;
	}

	public function check_tunnel_support( string $tunnel_type ): void {
		if ( $tunnel_type === 'no_tunnel' ) {
			$this->tunnel_type = 'none';

			return;
		}

		$cache = App::make( \QIT_CLI\Cache::class );

		if ( $tunnel_type === 'auto' ) {
			// Check if user has set a default tunnel.
			$default_tunnel = $cache->get( 'tunnel_default' );

			if ( $default_tunnel ) {
				$tunnel_type = $default_tunnel;

				// Tell which tunnel we are using.
				$output = App::make( OutputInterface::class );
				$output->writeln( '<info>Using tunneling method: ' . $tunnel_type . '.</info>' );
			} else {
				// Determine the default tunnel based on the OS.
				if ( ! is_mac() && ! is_wsl() ) {
					// Assuming Linux.
					$tunnel_type = 'cloudflared-docker';
				} elseif ( is_mac() ) {
					$tunnel_type = 'cloudflared-binary';
				} elseif ( is_wsl() ) {
					throw new \RuntimeException( 'Tunneling is not supported in WSL.' );
				}

				$output = App::make( OutputInterface::class );
				$output->writeln( '<info>No default tunneling method configured. Using default based on OS: ' . $tunnel_type . '.</info>' );
			}
		}

		if ( $tunnel_type === 'custom' ) {
			// Check if a custom tunnel is available.
			$custom_tunnel_class = $this->get_custom_tunnel_class();
			if ( $custom_tunnel_class !== null ) {
				$this->tunnel_type  = 'custom';
				$this->tunnel_class = $custom_tunnel_class;

				return;
			} else {
				throw new \RuntimeException( 'No custom tunnel class found.' );
			}
		}

		$tunnel_class = self::get_tunnel_class( $tunnel_type );
		if ( $tunnel_class === null ) {
			throw new \RuntimeException( 'Invalid tunnel type "' . $tunnel_type . '" specified.' );
		}

		// Check if the tunnel is supported, configured and available.
		$tunnel_class::check_is_installed();

		if ( ! $tunnel_class::is_configured() ) {
			throw new \RuntimeException( 'The tunneling method "' . $tunnel_type . '" is not configured properly. Please run "qit tunnel:setup".' );
		}

		$tunnel_class::check_is_available();

		$this->tunnel_type  = $tunnel_type;
		$this->tunnel_class = $tunnel_class;
	}

	/**
	 * @param string $tunnel_type
	 *
	 * @return string|null|Tunnel
	 */
	public static function get_tunnel_class( string $tunnel_type ) {
		return self::$tunnel_map[ $tunnel_type ] ?? null;
	}

	protected function get_custom_tunnel_class(): ?string {
		foreach ( get_declared_classes() as $class ) {
			if ( is_subclass_of( $class, CustomTunnel::class ) ) {
				return $class;
			}
		}

		return null;
	}

	public function start_tunnel( string $local_url, string $env_id ): string {
		if ( ! $this->tunnel_class ) {
			throw new \RuntimeException( 'No tunneling method available.' );
		}

		return $this->tunnel_class::connect_tunnel( $local_url, $env_id );
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
