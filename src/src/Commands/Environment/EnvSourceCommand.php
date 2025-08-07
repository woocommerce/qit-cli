<?php

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\EnvironmentVars;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class EnvSourceCommand extends QITCommand {
	/** @var EnvironmentVars */
	private EnvironmentVars $environment_vars;
	
	/** @var Config */
	private Config $config;
	
	protected static $defaultName = 'env:source'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( EnvironmentVars $environment_vars, Config $config ) {
		$this->environment_vars = $environment_vars;
		$this->config = $config;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure();
		$this
			->addArgument( 'env_id', InputArgument::OPTIONAL, 'The environment ID to generate source file for. If not provided, uses the most recent environment.' )
			->setDescription( 'Output the path to a source-able file with environment variables for manual testing' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$env_id = $input->getArgument( 'env_id' );
		
		// If no env_id provided, try to find the most recent or current one
		if ( empty( $env_id ) ) {
			$env_id = $this->get_current_or_most_recent_env();
			
			if ( empty( $env_id ) ) {
				$output->writeln( '<error>No environment found. Start one with: qit env:up</error>' );
				return 1;
			}
		}
		
		// Load the environment info
		$env_info = $this->load_env_info( $env_id );
		
		if ( ! $env_info ) {
			$output->writeln( sprintf( '<error>Environment %s not found</error>', $env_id ) );
			return 1;
		}
		
		// Generate and save the source file
		$files = $this->environment_vars->save_environment_file( $env_info );
		
		// Output just the path (for sourcing)
		// This allows: source "$(qit env:source)"
		$output->write( $files['shell'] );
		
		return 0;
	}
	
	/**
	 * Get the current or most recent environment ID.
	 * 
	 * @return string|null The environment ID or null if none found.
	 */
	private function get_current_or_most_recent_env(): ?string {
		$env_dir = $this->environment_vars->get_env_directory();
		
		// Check if current.sh exists and is valid
		$current_link = $env_dir . '/current.sh';
		if ( is_link( $current_link ) && file_exists( $current_link ) ) {
			// Extract env_id from the symlink target
			$target = readlink( $current_link );
			if ( preg_match( '/([a-z0-9]+)\.sh$/', $target, $matches ) ) {
				return $matches[1];
			}
		}
		
		// Otherwise, find the most recently modified .sh file
		$files = glob( $env_dir . '/*.sh' );
		if ( empty( $files ) ) {
			return null;
		}
		
		// Sort by modification time (newest first)
		usort( $files, function( $a, $b ) {
			return filemtime( $b ) - filemtime( $a );
		});
		
		// Extract env_id from the newest file
		$newest = basename( $files[0], '.sh' );
		if ( $newest !== 'current' ) {
			return $newest;
		}
		
		return null;
	}
	
	/**
	 * Load environment info from stored data.
	 * 
	 * @param string $env_id The environment ID.
	 * @return E2EEnvInfo|null The environment info or null if not found.
	 */
	private function load_env_info( string $env_id ): ?E2EEnvInfo {
		// Check if environment info is stored
		$info_file = $this->config::get_qit_dir() . '/environments/' . $env_id . '.json';
		
		if ( file_exists( $info_file ) ) {
			$data = json_decode( file_get_contents( $info_file ), true );
			if ( $data ) {
				$env_info = new E2EEnvInfo();
				foreach ( $data as $key => $value ) {
					if ( property_exists( $env_info, $key ) ) {
						$env_info->$key = $value;
					}
				}
				// Ensure container names are set
				if ( empty( $env_info->php_container ) ) {
					$env_info->php_container = 'qit_env_php_' . $env_info->env_id;
				}
				if ( empty( $env_info->db_container ) ) {
					$env_info->db_container = 'qit_env_db_' . $env_info->env_id;
				}
				return $env_info;
			}
		}
		
		// Try to reconstruct from running containers
		// This is a fallback for environments started before this feature
		$containers = shell_exec( 'docker ps --format "{{.Names}}"' );
		if ( strpos( $containers, $env_id ) !== false ) {
			// Try to determine port from docker inspect
			$inspect = shell_exec( sprintf(
				'docker inspect %s_php_%s --format "{{(index .NetworkSettings.Ports \"80/tcp\" 0).HostPort}}"',
				'qit_env',
				$env_id
			));
			
			$port = trim( $inspect );
			if ( is_numeric( $port ) ) {
				$env_info = new E2EEnvInfo();
				$env_info->env_id = $env_id;
				$env_info->site_url = 'http://localhost:' . $port;
				$env_info->db_port = (int) $port + 1; // Assuming DB port is next
				$env_info->php_container = 'qit_env_php_' . $env_id;
				$env_info->db_container = 'qit_env_db_' . $env_id;
				
				// Save for next time
				$this->save_env_info( $env_info );
				
				return $env_info;
			}
		}
		
		return null;
	}
	
	/**
	 * Save environment info for future use.
	 * 
	 * @param E2EEnvInfo $env_info The environment info.
	 */
	private function save_env_info( E2EEnvInfo $env_info ): void {
		$info_file = $this->config::get_qit_dir() . '/environments/' . $env_info->env_id . '.json';
		file_put_contents( $info_file, json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}
}