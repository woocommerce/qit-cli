<?php

namespace QIT_CLI\Commands\Tunnel;

use QIT_CLI\Cache;
use QIT_CLI\Tunnel\CloudflaredDockerTunnel;
use QIT_CLI\Tunnel\CloudflaredLocalTunnel;
use QIT_CLI\Tunnel\JurassicTubeTunnel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TunnelCommand extends Command {
	protected static $defaultName = 'tunnel'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var CloudflaredDockerTunnel */
	protected $cloudflared_docker_tunnel;

	/** @var CloudflaredLocalTunnel */
	protected $cloudflared_local_tunnel;

	/** @var JurassicTubeTunnel */
	protected $jurassic_tube_tunnel;

	/** @var Cache */
	protected $cache;

	public function __construct(
		CloudflaredDockerTunnel $cloudflared_docker_tunnel,
		CloudflaredLocalTunnel $cloudflared_local_tunnel,
		JurassicTubeTunnel $jurassic_tube_tunnel,
		Cache $cache
	) {
		parent::__construct();
		$this->cloudflared_docker_tunnel = $cloudflared_docker_tunnel;
		$this->cloudflared_local_tunnel  = $cloudflared_local_tunnel;
		$this->jurassic_tube_tunnel      = $jurassic_tube_tunnel;
		$this->cache                     = $cache;
	}

	protected function configure() {
		$this
			->setDescription( 'Setup a tunnel to expose your local environment to the internet.' )
			->setHelp( <<<'HELP'
The <info>%command.name%</info> command allows you to setup a tunnel to expose your local environment to the internet.

<info>Usage:</info>
  <info>qit-cli %command.name%</info>

<info>Supported Tunnels:</info>
  <info>1. Cloudflared Docker Tunnel</info>
     Zero-configuration tunnel that uses Docker and the Cloudflare's network to expose your local environment to the internet.
  <info>2. Cloudflared Local Tunnel</info>
     Same as before, but without Docker. This tunnel requires you to have Cloudflared installed on your machine.
  <info>3. Jurassic Tube Tunnel</info>
     Uses Automattic's Jurassic Tube Tunnel service to expose your local environment to the internet. This tunnel requires you to have Jurassic Tube installed on your machine.
HELP
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$current_tunnel = $this->cache->get( 'tunnel' );

		return Command::SUCCESS;
	}
}