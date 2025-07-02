<?php

namespace QIT_CLI\Commands\AI;

use QIT_CLI\AI\WebServer;
use QIT_CLI\Auth;
use QIT_CLI\Cache;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Exceptions\NetworkErrorException;
use QIT_CLI\Logging\Logger;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class NodeStartCommand extends QITCommand {
	protected static $defaultName = 'node:start';

	protected TunnelRunner $tunnel_runner;
	protected WebServer $webserver;
	protected Cache $cache;
	protected Auth $auth;

	private ?string $node_id = null;
	private ?string $node_token = null;
	private ?string $env_id = null;
	private ?string $client_id = null;
	private ?string $tunnel_url = null;
	private bool $heartbeat_running = true;
	private Logger $logger;

	public function __construct(
		TunnelRunner $tunnel_runner,
		Cache $cache,
		Auth $auth
	) {
		parent::__construct( self::getDefaultName() );
		$this->tunnel_runner = $tunnel_runner;
		$this->webserver = new WebServer( true );
		$this->cache         = $cache;
		$this->auth          = $auth;
	}

	protected function configure(): void {
		parent::configure();

		$this->setDescription( 'Start an AI processing node' )
		     ->setHelp( 'This command starts a local AI processing node that contributes to the QIT network.' )
		     ->addOption( 'tunnel', null, InputOption::VALUE_OPTIONAL, 'Enable tunneling. Optionally specify the tunnel method to use. Valid options: ' . implode( ', ', array_keys( TunnelRunner::$tunnel_map ) ), 'cloudflared-docker' )
		     ->addOption( 'name', null, InputOption::VALUE_OPTIONAL, 'A friendly name for this node (e.g., "Office PC", "Gaming Rig")' )
		     ->addOption( 'provider', null, InputOption::VALUE_OPTIONAL, 'LLM provider (ollama, openai, anthropic)', 'ollama' )
		     ->addOption( 'api-key', null, InputOption::VALUE_OPTIONAL, 'API key for cloud providers' )
		     ->addOption( 'model', null, InputOption::VALUE_OPTIONAL, 'Default model to use' )
		     ->addOption( 'ollama-url', null, InputOption::VALUE_OPTIONAL, 'Ollama API URL', 'http://localhost:11434' );
	}

	protected function doExecute( InputInterface $input, OutputInterface $output ): int {
		// Initialize logger with a file in the system's temporary directory
		$log_file     = sys_get_temp_dir() . '/qit-node.log';
		$this->logger = new Logger( $log_file, Logger::DEBUG );
		ini_set( 'log_errors', 1 );
		ini_set( 'error_log', sys_get_temp_dir() . '/qit-node.log' );
		ini_set( 'display_errors', 0 );

		// Print the log file path
		$output->writeln( '<info>Log file: ' . $log_file . '</info>' );

		// Log startup
		$this->logger->info( 'Starting QIT Node', [
			'php_version' => PHP_VERSION,
			'os'          => PHP_OS,
		] );

		// Pass logger to WebServer
		$this->webserver->setLogger( $this->logger );

		// Get provider configuration
		$provider = $input->getOption( 'provider' );
		$providerConfig = [];

		switch ( $provider ) {
			case 'ollama':
				$providerConfig['url'] = $input->getOption( 'ollama-url' );
				if ( $input->getOption( 'model' ) ) {
					$providerConfig['model'] = $input->getOption( 'model' );
				}
				break;

			case 'openai':
			case 'anthropic':
				if ( ! $input->getOption( 'api-key' ) ) {
					$output->writeln( '<error>API key is required for ' . $provider . '</error>' );
					return self::FAILURE;
				}
				$providerConfig['api_key'] = $input->getOption( 'api-key' );
				if ( $input->getOption( 'model' ) ) {
					$providerConfig['model'] = $input->getOption( 'model' );
				}
				break;

			default:
				$output->writeln( '<error>Unsupported provider: ' . $provider . '</error>' );
				return self::FAILURE;
		}

		// Pass provider config to webserver
		$this->webserver->setProviderConfig( $provider, $providerConfig );

		// Only check Ollama if using Ollama provider
		if ( $provider === 'ollama' ) {
			// Check if Ollama is available through WebServer
			if ( ! $this->webserver->isOllamaAvailable() ) {
				$this->logger->error( 'Ollama CLI is not available' );
				$output->writeln( '<error>Ollama CLI is not available. Please install it first: https://ollama.ai</error>' );

				return self::FAILURE;
			}

			// Ensure Ollama API is running through WebServer
			$output->write( 'Checking Ollama API... ' );
			try {
				if ( $this->webserver->ensureOllamaApiRunning() ) {
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
		} else {
			$output->writeln( '<info>Using ' . $provider . ' provider</info>' );
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

			// Model preloading through WebServer
			$required_models = [ 'llama3.2', 'qwen3:8b' ]; // qwen2.5:3b is ~2GB, qwen3:8b is 5gb~.

			foreach ( $required_models as $model ) {
				$output->write( "Checking $model... " );

				try {
					if ( $this->webserver->ensureModel( $model, $output ) ) {
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
			$this->logger->warning( 'Skipping heartbeat - node_id or node_token not set' );

			return;
		}

		try {
			$this->logger->info( 'Preparing to send heartbeat', [
				'node_id' => $this->node_id,
				'time'    => date( 'Y-m-d H:i:s' )
			] );

			// Collect health metrics
			$error_file = sys_get_temp_dir() . '/qit-node-last-error.json';
			$last_error = null;

			// Get system metrics for logging
			$memory_usage = memory_get_usage( true );
			$cpu_load     = sys_getloadavg()[0] ?? null;

			$this->logger->debug( 'Collected system metrics', [
				'memory_usage'    => $memory_usage,
				'memory_usage_mb' => round( $memory_usage / 1024 / 1024, 2 ) . ' MB',
				'cpu_load'        => $cpu_load
			] );

			if ( file_exists( $error_file ) ) {
				$error_content = file_get_contents( $error_file );
				$last_error    = json_decode( $error_content, true );

				if ( json_last_error() !== JSON_ERROR_NONE ) {
					$this->logger->warning( 'Failed to parse error file JSON', [
						'error'   => json_last_error_msg(),
						'file'    => $error_file,
						'content' => substr( $error_content, 0, 200 ) . '...'
					] );
				} else {
					$this->logger->info( 'Found error to report in heartbeat', [
						'error_type'    => $last_error['error_type'] ?? 'unknown',
						'error_message' => $last_error['error_message'] ?? 'unknown',
						'job_id'        => $last_error['job_id'] ?? 'not provided',
						'job_type'      => $last_error['job_type'] ?? 'unknown'
					] );
				}

				// Debug: show error in verbose mode
				if ( $output->isVerbose() && $last_error ) {
					$output->writeln( '<error>Last error: ' . ( $last_error['error_message'] ?? 'Unknown error' ) . '</error>' );
					if ( ! empty( $last_error['job_id'] ) ) {
						$output->writeln( '  - Job ID: ' . $last_error['job_id'] );
					}
				}

				// Clear the error after reading
				unlink( $error_file );
				$this->logger->debug( 'Cleared error file after reading' );
			} else {
				$this->logger->debug( 'No error file found for heartbeat' );
			}

			$heartbeat_data = [
				'node_token'  => $this->node_token,
				'last_error'  => $last_error,
				'system_info' => [
					'memory_usage' => $memory_usage,
					'cpu_load'     => $cpu_load,
				],
			];

			$this->logger->debug( 'Sending heartbeat request', [
				'endpoint'   => get_manager_url() . '/wp-json/cd/v1/ai-nodes/' . $this->node_id . '/heartbeat',
				'has_error'  => $last_error !== null ? 'yes' : 'no',
				'has_job_id' => ! empty( $last_error['job_id'] ) ? 'yes' : 'no'
			] );

			$start_time = microtime( true );

			try {
				$response_json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/ai-nodes/' . $this->node_id . '/heartbeat' ) )
					->with_method( 'POST' )
					->with_post_body( $heartbeat_data )
					->with_expected_status_codes( [ 200, 201 ] )
					->request();

				$request_time = microtime( true ) - $start_time;
				$response     = json_decode( $response_json, true );

				$this->logger->info( 'Heartbeat sent successfully', [
					'response_time_ms' => round( $request_time * 1000, 2 ),
					'next_heartbeat'   => $response['next_heartbeat'] ?? 60,
					'status'           => $response['status'] ?? 'unknown'
				] );

				if ( $output->isVeryVerbose() ) {
					$output->writeln( '[' . date( 'H:i:s' ) . '] Heartbeat sent successfully' );
					if ( $last_error ) {
						$output->writeln( '  - Reported error: ' . ( $last_error['error_message'] ?? 'Unknown' ) );
						if ( ! empty( $last_error['job_id'] ) ) {
							$output->writeln( '  - Job error updated for: ' . $last_error['job_id'] );
						}
					}
				}
			} catch ( NetworkErrorException $e ) {
				// This is what RequestBuilder throws
				$this->logger->error( 'Heartbeat request failed', [
					'error'    => $e->getMessage(),
					'code'     => $e->getCode(),
					'endpoint' => get_manager_url() . '/wp-json/cd/v1/ai-nodes/' . $this->node_id . '/heartbeat'
				] );

				if ( $output->isVerbose() ) {
					$output->writeln( '<warning>Heartbeat failed: ' . $e->getMessage() . '</warning>' );
				}
			}

		} catch ( \Exception $e ) {
			$this->logger->error( 'Unexpected heartbeat error', [
				'error' => $e->getMessage(),
				'class' => get_class( $e ),
				'trace' => $e->getTraceAsString()
			] );

			if ( $output->isVerbose() ) {
				$output->writeln( '<warning>Heartbeat failed unexpectedly: ' . $e->getMessage() . '</warning>' );
			}
		}
	}

	private function cleanup( OutputInterface $output ): void {
		$this->logger->info( 'Starting node cleanup process' );

		// Unregister from Manager
		if ( $this->node_id && $this->node_token ) {
			$this->logger->info( 'Unregistering node from QIT network', [
				'node_id' => $this->node_id
			] );

			try {
				$start_time = microtime( true );
				( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/ai-nodes/' . $this->node_id . '/unregister' ) )
					->with_method( 'POST' )
					->with_post_body( [
						'node_token' => $this->node_token,
					] )
					->with_expected_status_codes( [ 200, 201 ] )
					->request();
				$request_time = microtime( true ) - $start_time;

				$this->logger->info( 'Node unregistered successfully', [
					'response_time_ms' => round( $request_time * 1000, 2 )
				] );

				$output->writeln( '<info>✓ Unregistered from QIT network</info>' );
			} catch ( \Exception $e ) {
				$this->logger->error( 'Failed to unregister node', [
					'error' => $e->getMessage(),
					'trace' => $e->getTraceAsString()
				] );

				$output->writeln( '<warning>Failed to unregister: ' . $e->getMessage() . '</warning>' );
			}
		} else {
			$this->logger->warning( 'Skipping unregister - node_id or node_token not set' );
		}

		// Stop webserver
		$this->logger->info( 'Stopping webserver' );
		$this->webserver->stop();
		$this->logger->debug( 'Webserver stopped' );

		// Stop tunnel
		if ( $this->env_id ) {
			$this->logger->info( 'Stopping tunnel', [ 'env_id' => $this->env_id ] );
			$this->tunnel_runner->stop_tunnel( $this->env_id );
			$this->logger->debug( 'Tunnel stopped' );
		} else {
			$this->logger->debug( 'No tunnel to stop (env_id not set)' );
		}

		// Clear cached node info
		$this->logger->debug( 'Clearing cached node info' );
		$this->cache->delete( 'active_node_id' );
		$this->cache->delete( 'active_node_token' );

		$this->logger->info( 'Node cleanup completed' );
	}
}
