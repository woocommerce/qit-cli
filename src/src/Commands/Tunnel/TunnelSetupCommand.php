<?php

namespace QIT_CLI\Commands\Tunnel;

use Symfony\Component\Console\Command\Command;
use QIT_CLI\Cache;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Process;

class TunnelSetupCommand extends Command {
	protected static $defaultName = 'tunnel:setup';

	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		parent::__construct();
		$this->cache = $cache;
	}

	protected function configure() {
		$this
			->setDescription( 'Configure tunneling methods for QIT CLI.' )
			->addArgument( 'method', InputArgument::OPTIONAL, 'The tunneling method to configure.' )
			->addOption( 'name', 'N', InputOption::VALUE_OPTIONAL, 'Persistent Cloudflared - Tunnel name.' )
			->addOption( 'url', 'u', InputOption::VALUE_OPTIONAL, 'Persistent Cloudflared - Tunnel URL.' )
			->addOption( 'username', 'U', InputOption::VALUE_OPTIONAL, 'JurassicTube - Username.' )
			->addOption( 'subdomain', 's', InputOption::VALUE_OPTIONAL, 'JurassicTube - Subdomain.' )
			->addOption( 'reset', 'r', InputOption::VALUE_NONE, 'Reset the tunneling configuration to defaults.' )
			->setHelp( <<<'TXT'
The <info>tunnel:configure</info> command allows you to configure tunneling methods for QIT CLI.

Usage:

  Interactive Mode:
    qit tunnel:configure <method>

  Programmatic Mode:
    qit tunnel:configure <method> [options]

Available methods:
  - cloudflared-docker: Docker-based Cloudflared tunnel (Linux only).
  - cloudflared-binary: Uses local Cloudflared binary (Mac/Linux).
  - cloudflared-persistent: Requires prior configuration.
  - jurassictube: For Automattic employees (requires prior configuration).

Options:
  --name (-n)       Tunnel name (for persistent Cloudflared).
  --url (-u)        Tunnel URL (for persistent Cloudflared).
  --username (-U)   JurassicTube Username.
  --subdomain (-s)  JurassicTube Subdomain.
  --reset (-r)      Reset the tunneling configuration to defaults.

Examples:

  Configure Persistent Cloudflared Tunnel:
    qit tunnel:configure cloudflared-persistent --name=my-tunnel --url=https://my-tunnel.example.com

  Configure JurassicTube Tunnel:
    qit tunnel:configure jurassictube --username=your-username --subdomain=your-subdomain

  Reset Configuration:
    qit tunnel:configure --reset
TXT
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( $input->getOption( 'reset' ) ) {
			$this->cache->delete( 'tunnel_configs' );
			$this->cache->delete( 'tunnel_default' );
			$output->writeln( '<info>Tunneling configurations have been reset to defaults.</info>' );

			return Command::SUCCESS;
		}

		$method = $input->getArgument( 'method' );

		$valid_methods = array_keys( TunnelRunner::$tunnel_map );

		$helper = $this->getHelper( 'question' );

		// If method is not provided or invalid, prompt the user.
		if ( ! $method || ! in_array( $method, $valid_methods, true ) ) {
			$method_descriptions = [
				'cloudflared-docker'     => 'Cloudflared Docker Tunnel (Linux only)',
				'cloudflared-binary'     => 'Cloudflared Local Binary Tunnel (Mac/Linux)',
				'cloudflared-persistent' => 'Persistent Cloudflared Tunnel',
				'jurassictube'           => 'JurassicTube Tunnel (Automattic employees only)',
			];

			$question = new ChoiceQuestion(
				'Select the tunneling method you wish to configure:',
				$method_descriptions,
				'cloudflared-docker' // default to first option.
			);
			$question->setErrorMessage( 'Method %s is invalid.' );

			$method = $helper->ask( $input, $output, $question );
		}

		$config = [ 'method' => $method ];

		// Use the is_usable method to check usability
		$tunnel_class = TunnelRunner::get_tunnel_class( $method );
		if ( $tunnel_class && ! $tunnel_class::is_supported() ) {
			$output->writeln( '<error>The selected tunneling method is not usable on this system.</error>' );
			return Command::FAILURE;
		}

		// Check if the relevant binaries are installed.
		switch ( $method ) {
			case 'cloudflared-binary':
			case 'cloudflared-persistent':
				if ( ! $this->check_binary_exists( 'cloudflared' ) ) {
					$output->writeln( '<error>Cloudflared binary not found. Please install it first.</error>' );

					if ( is_mac() ) {
						$output->writeln( '<info>Install Cloudflared binary on Mac using Homebrew:</info>' );
						$output->writeln( '<info>brew install cloudflared</info>' );
					} else {
						$output->writeln( '<info>Download Cloudflared binary from:</info>' );
						$output->writeln( '<info>https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/</info>' );
					}

					return Command::FAILURE;
				}
				break;
			case 'jurassictube':
				if ( ! $this->check_binary_exists( 'jurassictube' ) ) {
					$output->writeln( '<error>JurassicTube binary not found. Please install it first.</error>' );

					return Command::FAILURE;
				}
				break;
		}

		// Gather additional information based on the selected method.
		switch ( $method ) {
			case 'cloudflared-docker':
			case 'cloudflared-binary':
				$output->writeln( '<info>No additional configuration needed for ' . $method . '.</info>' );
				break;
			case 'cloudflared-persistent':
				// Retrieve options or prompt for them.
				$name = $input->getOption( 'name' );
				$url  = $input->getOption( 'url' );

				if ( ! $name ) {
					$question = new Question( '- Tunnel Name: ' );
					$name     = $helper->ask( $input, $output, $question );
				}

				$output->write( '<info>Running "cloudflared tunnel info ' . $name . '" to check if the tunnel exists...</info>' );

				if ( $this->check_persistent_cloudflared_tunnel_exists( $name ) ) {
					$output->writeln( '<bg=green;fg=white> OK! </>' );
				} else {
					$output->writeln( '<error>Tunnel "' . $name . '" does not exist. Please create it first in Cloudflare. Check QIT Tunnel documentation for details.</error>' );

					return Command::FAILURE;
				}

				if ( ! $url ) {
					$question = new Question( '- Tunnel URL (e.g., https://my-tunnel.example.com): ' );
					$url      = $helper->ask( $input, $output, $question );
				}

				if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
					$output->writeln( '<error>The Tunnel URL is not valid. It must start with "https://" and be a valid URL.</error>' );

					return Command::FAILURE;
				}

				$config['tunnel_name'] = $name;
				$config['tunnel_url']  = $url;
				break;

			case 'jurassictube':
				// Retrieve options or prompt for them.
				$username  = $input->getOption( 'username' );
				$subdomain = $input->getOption( 'subdomain' );

				if ( ! $username ) {
					$question = new Question( '- JurassicTube Username: ' );
					$username = $helper->ask( $input, $output, $question );
				}

				if ( ! $subdomain ) {
					$question  = new Question( '- Subdomain (e.g., your-subdomain): ' );
					$subdomain = $helper->ask( $input, $output, $question );
				}

				// Validate subdomain (e.g., only allow alphanumeric and hyphens).
				if ( ! preg_match( '/^[a-zA-Z0-9\-]+$/', $subdomain ) ) {
					$output->writeln( '<error>Invalid subdomain. Only alphanumeric characters and hyphens are allowed.</error>' );

					return Command::FAILURE;
				}

				$config['username']   = $username;
				$config['subdomain']  = $subdomain;
				$config['tunnel_url'] = 'https://' . $subdomain . '.jurassic.tube';

				$output->writeln( '<info>Your tunnel URL will be: ' . $config['tunnel_url'] . '</info>' );
				break;

			default:
				$output->writeln( '<error>Unsupported tunneling method selected.</error>' );

				return Command::FAILURE;
		}

		// Save the configuration.
		$configs = $this->cache->get( 'tunnel_configs' ) ?? [];
		$configs[ $method ] = $config;
		$this->cache->set( 'tunnel_configs', $configs, -1 );

		// Ask the user if they want to set this tunnel as default
		$set_as_default = false;
		if ( getenv( 'CI' ) !== false ) {
			// If running in CI, set as default automatically
			$set_as_default = true;
		} else {
			$question = new ChoiceQuestion(
				'Do you want to set this tunneling method as your default? (yes/no)',
				[ 'yes', 'no' ],
				0
			);
			$answer = $helper->ask( $input, $output, $question );
			if ( $answer === 'yes' ) {
				$set_as_default = true;
			}
		}

		if ( $set_as_default ) {
			$this->cache->set( 'tunnel_default', $method, -1 );
			$output->writeln( '<info>Default tunneling method set to: ' . $method . '</info>' );
		}

		$output->writeln( '<info>Configuration successful! Your ' . $method . ' tunnel is now set up.</info>' );

		return Command::SUCCESS;
	}

	private function check_persistent_cloudflared_tunnel_exists( string $tunnel_name ): bool {
		$process = new Process( [ 'cloudflared', 'tunnel', 'info', $tunnel_name ] );
		$process->run();

		return $process->isSuccessful();
	}

	private function check_binary_exists( string $binary ): bool {
		exec( "which $binary", $output, $return_var );

		return $return_var === 0;
	}
}
