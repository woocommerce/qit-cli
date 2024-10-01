<?php

namespace QIT_CLI\Commands\Tunnel;

use QIT_CLI\Cache;
use QIT_CLI\Tunnel\JurassicTubeTunnel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TunnelDomainsCommand extends Command {
	protected static $defaultName = 'tunnel:domain'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var JurassicTubeTunnel */
	protected $jurassic_tube_tunnel;

	/** @var Cache */
	protected $cache;

	public function __construct(
		JurassicTubeTunnel $jurassic_tube_tunnel,
		Cache $cache
	) {
		parent::__construct();
		$this->jurassic_tube_tunnel      = $jurassic_tube_tunnel;
		$this->cache                     = $cache;
	}

	protected function configure() {
		$this
			->addArgument( 'action', InputArgument::REQUIRED, 'Action to perform: add, list or remove.' )
			->addArgument( 'domain', InputArgument::OPTIONAL, 'Domain to add or remove.' )
			->setDescription('Manage your local list of domains for Jurassic Tube.')
			->setHelp( <<<'HELP'
The <info>%command.name%</info> command allows you to manage your local list of domains associated with Jurassic Tube.

<info>Usage:</info>
  <info>qit-cli %command.name%</info> add <domain>      Add a domain to your local list.
  <info>qit-cli %command.name%</info> list              List all domains in your local list.
  <info>qit-cli %command.name%</info> remove <domain>   Remove a domain from your local list.
  
  <info>Important Notes:</info>
  - This command does not register new domains to Jurassic Tube directly.
  - Ensure that the domains you manage with this command are already registered with Jurassic Tube.
HELP
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$action = $input->getArgument( 'action' );
		$domain = $input->getArgument( 'domain' );

		switch ( $action ) {
			case 'add':
				$this->jurassic_tube_tunnel->add_domain( $domain );
				break;
			case 'list':
				$this->jurassic_tube_tunnel->list_domains();
				break;
			case 'remove':
				$this->jurassic_tube_tunnel->remove_domain( $domain );
				break;
			default:
				$output->writeln( 'Invalid action.' );
				break;
		}

		return Command::SUCCESS;
	}
}