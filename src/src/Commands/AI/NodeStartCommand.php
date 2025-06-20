<?php

namespace QIT_CLI\Commands\AI;

use QIT_CLI\AI\Ollama;
use QIT_CLI\AI\WebServer;
use QIT_CLI\Auth;
use QIT_CLI\Cache;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class NodeStartCommand extends QITCommand {
	protected static $defaultName = 'node:start';

	protected Ollama $ollama;
	protected TunnelRunner $tunnel_runner;
	protected WebServer $webserver;
	protected Cache $cache;
	protected Auth $auth;

	private ?string $node_id = null;
	private ?string $node_token = null;
	private ?string $env_id = null;
	private ?string $client_id = null;  // Add this
	private ?string $tunnel_url = null; // Add this
	private bool $heartbeat_running = true;

	public function __construct(
		Ollama $ollama,
		TunnelRunner $tunnel_runner,
		WebServer $webserver,
		Cache $cache,
		Auth $auth
	) {
		parent::__construct( self::getDefaultName() );
		$this->ollama        = $ollama;
		$this->tunnel_runner = $tunnel_runner;
		$this->webserver     = $webserver;
		$this->cache         = $cache;
		$this->auth          = $auth;
	}

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Start an AI processing node' )
		     ->setHelp( 'This command starts a local AI processing node that contributes to the QIT network.' )
		     ->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunneling. Optionally specify the tunnel method to use. Valid options: ' . implode( ', ', array_keys( TunnelRunner::$tunnel_map ) ), 'cloudflared-docker' )
		     ->addOption( 'name', null, InputOption::VALUE_OPTIONAL, 'A friendly name for this node (e.g., "Office PC", "Gaming Rig")' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		if ( ! $this->ollama->is_available() ) {
			$output->writeln( '<error>Ollama CLI is not available. Please install it first: https://ollama.ai</error>' );

			return self::FAILURE;
		}

		// Ensure Ollama API is running
		$output->write( 'Checking Ollama API... ' );
		try {
			if ( $this->ollama->ensure_api_running() ) {
				$output->writeln( '<info>✓</info>' );
			} else {
				$output->writeln( '<error>✗</error>' );
				$output->writeln( '<error>Failed to start Ollama API server. Is Ollama installed correctly?</error>' );

				return self::FAILURE;
			}
		} catch ( \Exception $e ) {
			$output->writeln( '<error>✗</error>' );
			$output->writeln( '<error>' . $e->getMessage() . '</error>' );

			return self::FAILURE;
		}

		// Check authentication
		if ( ! $this->auth->get_manager_secret() && ! $this->auth->get_partner_auth() ) {
			$output->writeln( '<error>You must be authenticated to start a node. Run "qit connect" first.</error>' );

			return self::FAILURE;
		}

		// Generate environment ID
		$this->env_id = bin2hex( random_bytes( 8 ) );

		// Get node name
		$node_name = $this->getNodeName( $input );

		try {
			// Start webserver
			$webserver_url = $this->webserver->start();
			$node_token    = $this->webserver->get_node_token();
			$output->writeln( '<info>✓ Started local server on ' . $webserver_url . '</info>' );

			// Create tunnel
			$this->tunnel_runner->check_tunnel_support( $input->getOption( 'tunnel' ) );
			$this->tunnel_url = $this->tunnel_runner->start_tunnel( $webserver_url, $this->env_id ); // Store as class property
			$output->writeln( '<info>✓ Created secure tunnel: ' . $this->tunnel_url . '</info>' );

			// Get client ID
			$this->client_id = $this->cache->get( 'client_id' ); // Store as class property
			if ( ! $this->client_id ) {
				throw new \Exception( 'Client ID not found. This should have been generated during bootstrap.' );
			}

			// Register with Manager
			$output->writeln( 'Registering with QIT network...' );

			$response_json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/ai-nodes/register' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'tunnel_url'   => $this->tunnel_url,
					'client_id'    => $this->client_id,
					'endpoint'     => '/process',
					'node_token'   => $node_token,
					'capabilities' => [], // Empty for now
					'node_name'    => $node_name,
				] )
				->with_expected_status_codes( [ 200, 201 ] )
				->with_retry( 3 )
				->request();

			$response = json_decode( $response_json, true );

			if ( ! isset( $response['node_id'] ) ) {
				throw new \Exception( 'Invalid response from Manager: ' . $response_json );
			}

			$this->node_id    = $response['node_id'];
			$this->node_token = $node_token;

			// Store node credentials for other commands
			$this->cache->set( 'active_node_id', $this->node_id, 86400 ); // 24h
			$this->cache->set( 'active_node_token', $this->node_token, 86400 ); // 24h

			$output->writeln( '<info>✓ Registered with node ID: ' . $this->node_id . '</info>' );

			if ( $node_name ) {
				$output->writeln( '<info>✓ Node name: ' . $node_name . '</info>' );
			}

			$output->writeln( '' );
			$output->writeln( 'Ensuring required models are available...' );

			// Model preloading.
			$required_models = [ 'llama3.2', 'qwen3:8b' ]; // qwen2.5:3b is ~2GB, qwen3:8b is 5gb~.

			foreach ( $required_models as $model ) {
				$output->write( "Checking $model... " );

				try {
					if ( $this->ollama->ensure_model( $model, $output ) ) {
						$output->writeln( '<info>✓</info>' );
					} else {
						$output->writeln( '<error>✗ Failed to pull model</error>' );
					}
				} catch ( \Exception $e ) {
					$output->writeln( '<error>✗ ' . $e->getMessage() . '</error>' );
				}
			}

			$output->writeln( '' );
			$output->writeln( '<info>Node started successfully!</info>' );

			// Start heartbeat in background
			$this->startHeartbeat( $output );

			// Handle keeping the process running
			if ( extension_loaded( 'pcntl' ) ) {
				$output->writeln( '<comment>Press Ctrl+C to stop the node.</comment>' );

				pcntl_signal( SIGINT, function () use ( $output ) {
					$output->writeln( "\n<info>Shutting down node...</info>" );
					$this->heartbeat_running = false;
					$this->cleanup( $output );
					exit( 0 );
				} );

				// Keep the process running with heartbeat
				while ( $this->heartbeat_running ) {
					pcntl_signal_dispatch();
					$this->sendHeartbeat( $output );
					sleep( 60 ); // Sleep for 60 seconds between heartbeats
				}
			} else {
				// Fallback for Windows or systems without pcntl
				$output->writeln( '<comment>Press Enter to stop the node.</comment>' );

				// Start a simple heartbeat loop in a separate process if possible
				$heartbeat_pid = null;
				if ( function_exists( 'pcntl_fork' ) ) {
					$heartbeat_pid = pcntl_fork();
					if ( $heartbeat_pid === 0 ) {
						// Child process - run heartbeat loop
						while ( true ) {
							$this->sendHeartbeat( $output );
							sleep( 60 );
						}
						exit( 0 );
					}
				}

				fgets( STDIN );

				// Kill heartbeat process if it exists
				if ( $heartbeat_pid > 0 ) {
					if ( function_exists( 'posix_kill' ) ) {
						posix_kill( $heartbeat_pid, SIGTERM );
					} else {
						// Fallback - the process will be killed when parent exits anyway
						exec( "kill $heartbeat_pid 2>/dev/null" );
					}
				}

				$output->writeln( '<info>Shutting down node...</info>' );
				$this->cleanup( $output );
			}

		} catch ( \Exception $e ) {
			$output->writeln( '<error>Failed to start node: ' . $e->getMessage() . '</error>' );
			$this->cleanup( $output );

			return self::FAILURE;
		}

		return self::SUCCESS;
	}

	private function getNodeName( InputInterface $input ): ?string {
		// Check if user provided a name
		$name = $input->getOption( 'name' );
		if ( $name ) {
			// Sanitize and limit length
			$name = substr( trim( $name ), 0, 50 );
			// Cache it for future runs
			$this->cache->set( 'node_display_name', $name, 86400 * 30 ); // 30 days

			return $name;
		}

		// Try to get a cached name
		$cached_name = $this->cache->get( 'node_display_name' );
		if ( $cached_name ) {
			return $cached_name;
		}

		// No name provided or cached
		return null;
	}

	private function startHeartbeat( OutputInterface $output ): void {
		// Initial heartbeat happens during the main loop
		if ( $output->isVerbose() ) {
			$output->writeln( 'Heartbeat scheduled every 60 seconds...' );
		}
	}

	private function sendHeartbeat( OutputInterface $output ): void {
		if ( ! $this->node_id || ! $this->node_token ) {
			return;
		}

		try {
			// Collect health metrics
			$error_file = sys_get_temp_dir() . '/qit-node-last-error.json';
			$last_error = null;

			if ( file_exists( $error_file ) ) {
				$last_error = json_decode( file_get_contents( $error_file ), true );

				// Debug: show error in verbose mode
				if ( $output->isVerbose() && $last_error ) {
					$output->writeln( '<error>Last error: ' . $last_error['error_message'] . '</error>' );
				}

				// Clear the error after reading
				unlink( $error_file );
			}

			$heartbeat_data = [
				'node_token'  => $this->node_token,
				'last_error'  => $last_error,
				'system_info' => [
					'memory_usage' => memory_get_usage( true ),
					'cpu_load'     => sys_getloadavg()[0] ?? null,
				],
			];

			$response_json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/ai-nodes/' . $this->node_id . '/heartbeat' ) )
				->with_method( 'POST' )
				->with_post_body( $heartbeat_data )
				->with_expected_status_codes( [ 200, 201 ] )
				->request();

			if ( $output->isVeryVerbose() ) {
				$output->writeln( '[' . date( 'H:i:s' ) . '] Heartbeat sent successfully' );
				if ( $last_error ) {
					$output->writeln( '  - Reported error: ' . $last_error['error_message'] );
				}
			}
		} catch ( \Exception $e ) {
			if ( $output->isVerbose() ) {
				$output->writeln( '<warning>Heartbeat failed: ' . $e->getMessage() . '</warning>' );
			}
		}
	}

	private function cleanup( OutputInterface $output ): void {
		// Unregister from Manager
		if ( $this->node_id && $this->node_token ) {
			try {
				( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/ai-nodes/' . $this->node_id . '/unregister' ) )
					->with_method( 'POST' )
					->with_post_body( [
						'node_token' => $this->node_token, // Changed from node_secret
					] )
					->with_expected_status_codes( [ 200, 201 ] )
					->request();

				$output->writeln( '<info>✓ Unregistered from QIT network</info>' );
			} catch ( \Exception $e ) {
				$output->writeln( '<warning>Failed to unregister: ' . $e->getMessage() . '</warning>' );
			}
		}

		// Stop webserver
		$this->webserver->stop();

		// Stop tunnel
		if ( $this->env_id ) {
			$this->tunnel_runner->stop_tunnel( $this->env_id );
		}

		// Clear cached node info
		$this->cache->delete( 'active_node_id' );
		$this->cache->delete( 'active_node_token' );
	}
}
