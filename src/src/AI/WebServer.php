<?php

namespace QIT_CLI\AI;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class WebServer {
	private ?Process $process = null;
	private int $port = 8000;
	private string $webroot;
	private string $node_token;
	private ?\QIT_CLI\Logging\Logger $logger = null;
	private bool $use_local_mode = false;
	private string $provider = 'lmstudio';
	private array $providerConfig = [];
	private array $runtimeConfig = [];
	private string $routerTemplate;
	private bool $bindLocalhostOnly = false;

	public function __construct( bool $use_local_mode = false ) {
		$this->use_local_mode = $use_local_mode;
	}

	/**
	 * Set the logger instance.
	 *
	 * @param \QIT_CLI\Logging\Logger $logger The logger instance.
	 */
	public function setLogger( \QIT_CLI\Logging\Logger $logger ): void {
		$this->logger = $logger;
	}

	public function setProviderConfig( string $provider, array $config ): void {
		$this->provider       = $provider;
		$this->providerConfig = $config;
	}

	/**
	 * Set the runtime configuration.
	 *
	 * @param array $config The runtime configuration.
	 */
	public function setRuntimeConfig( array $config ): void {
		$this->runtimeConfig = $config;
	}

	/**
	 * Set the router template to use.
	 *
	 * @param string $basename The basename of the router template file.
	 */
	public function setRouterTemplate( string $basename ): void {
		$this->routerTemplate = $basename;
	}

	/**
	 * Set whether to bind only to localhost.
	 */
	public function setBindLocalhostOnly(): void {
		$this->bindLocalhostOnly = true;
	}

	public function start(): string {
		if ( $this->logger ) {
			$this->logger->info( 'Starting webserver', [
				'mode' => $this->use_local_mode ? 'local' : 'temp'
			] );
		}

		// Find an available port
		$this->port = $this->findAvailablePort();
		if ( $this->logger ) {
			$this->logger->debug( 'Found available port', [ 'port' => $this->port ] );
		}

		// Generate node token
		$this->node_token = bin2hex( random_bytes( 32 ) );
		if ( $this->logger ) {
			$this->logger->debug( 'Generated node token', [
				'token_prefix' => substr( $this->node_token, 0, 8 ) . '...'
			] );
		}

		if ( $this->use_local_mode ) {
			// Use the source webserver directory directly
			$this->webroot = __DIR__ . '/webserver';
			if ( ! is_dir( $this->webroot ) ) {
				$error_msg = 'Webserver source directory not found: ' . $this->webroot;
				if ( $this->logger ) {
					$this->logger->error( $error_msg );
				}
				throw new \RuntimeException( $error_msg );
			}
			if ( $this->logger ) {
				$this->logger->info( 'Using local webserver directory', [ 'webroot' => $this->webroot ] );
			}
		} else {
			// Create temp directory and copy files
			$this->setupTempWebroot();
		}

		// For both modes, we need to handle placeholders
		// In local mode: create temp router file
		// In temp mode: modify the copied router file in place
		$routerTemplate = $this->routerTemplate;
		if ( $this->use_local_mode ) {
			$router_path = $this->createTempRouterFile( $routerTemplate );
		} else {
			// In temp mode, replace placeholders in the copied router file
			$this->replacePlaceholders( $routerTemplate );
			$router_path = $this->webroot . '/' . $routerTemplate;
		}

		// Configure open_basedir restrictions for security
		$allowed = [];

		if ( ! $this->use_local_mode ) {
			// In temp mode, restrict to the configured directories
			$allowed = [
				$this->runtimeConfig['tmp_base'] ?? rtrim( sys_get_temp_dir(), '/\\' ) . '/qit-node',  // /tmp/qit-node (parent, not child)
				$this->runtimeConfig['ai_dir'] ?? sys_get_temp_dir() . '/qit-node-ai', // Fallback if ai_dir not provided
			];
		} else {
			// In local mode, don't restrict
			$allowed = [
				dirname( __DIR__ ), // Allow access to the project directory
				sys_get_temp_dir(), // Allow access to temp directory
				getcwd(), // Allow access to the current working directory for relative includes
			];
		}

		$openBasedir = implode( PATH_SEPARATOR, $allowed );

		// Determine host binding based on bindLocalhostOnly flag
		$host = $this->bindLocalhostOnly ? "127.0.0.1:{$this->port}" : "0.0.0.0:{$this->port}";

		if ( $this->logger ) {
			$this->logger->info( 'Starting PHP built-in server', [
				'host'           => $host,
				'webroot'        => $this->webroot,
				'router'         => $router_path,
				'mode'           => $this->use_local_mode ? 'local' : 'temp',
				'open_basedir'   => $openBasedir,
				'localhost_only' => $this->bindLocalhostOnly
			] );
		}

		$this->process = new Process( [
			'php',
			'-d',
			'open_basedir=' . $openBasedir,
			'-d',
			'variables_order=EGPCS',
			'-S',
			$host,
			'-t',
			$this->webroot,
			$router_path
		] );

		$this->process->start();

		// Give it a moment to start
		if ( $this->logger ) {
			$this->logger->debug( 'Waiting for server to start' );
		}
		usleep( 500000 ); // 0.5 seconds

		// Check if it started successfully
		if ( ! $this->process->isRunning() ) {
			$error_output = $this->process->getErrorOutput();
			$error_msg    = 'Failed to start web server: ' . $error_output;
			if ( $this->logger ) {
				$this->logger->error( $error_msg, [
					'error_output' => $error_output
				] );
			}
			throw new \RuntimeException( $error_msg );
		}

		$server_url = "http://localhost:{$this->port}";
		if ( $this->logger ) {
			$this->logger->info( 'Webserver started successfully', [ 'url' => $server_url ] );
		}

		return $server_url;
	}

	/**
	 * Setup temporary webroot directory (for temp mode)
	 */
	private function setupTempWebroot(): void {
		// Get base temp directory from runtime config or use default
		$base = $this->runtimeConfig['tmp_base'] ?? rtrim( sys_get_temp_dir(), '/\\' ) . '/qit-node';
		if ( empty( $base ) || $base === '/' ) {
			$error_msg = 'Invalid temp base directory';
			if ( $this->logger ) {
				$this->logger->error( $error_msg, [ 'tmp_base' => $base ] );
			}
			throw new \RuntimeException( $error_msg );
		}

		// Create the base directory if it doesn't exist
		if ( ! is_dir( $base ) ) {
			mkdir( $base, 0700, true );
			if ( $this->logger ) {
				$this->logger->debug( 'Created base temp directory', [ 'base' => $base ] );
			}
		}

		// Create a unique run directory for this session
		$this->webroot = $base . '/run-' . bin2hex( random_bytes( 4 ) );
		if ( $this->logger ) {
			$this->logger->debug( 'Creating webroot directory', [ 'webroot' => $this->webroot ] );
		}

		// Ensure we're creating in a safe location
		if ( strpos( $this->webroot, $base ) !== 0 ) {
			$error_msg = 'Webroot must be in temp base directory';
			if ( $this->logger ) {
				$this->logger->error( $error_msg, [
					'webroot' => $this->webroot,
					'base'    => $base
				] );
			}
			throw new \RuntimeException( $error_msg );
		}

		mkdir( $this->webroot, 0700, true );
		if ( $this->logger ) {
			$this->logger->debug( 'Created webroot directory' );
		}

		// Create extracted-zips directory
		mkdir( $this->webroot . '/extracted-zips', 0700, true );
		if ( $this->logger ) {
			$this->logger->debug( 'Created extracted-zips directory' );
		}

		// Copy webserver files from source to temp directory
		if ( $this->logger ) {
			$this->logger->debug( 'Copying webserver files to temp directory' );
		}
		$this->copyWebserverFiles();

		// Replace placeholders in router template
		$this->replacePlaceholders($this->routerTemplate);

		if ( $this->logger ) {
			$this->logger->debug( 'Webserver files prepared' );
		}
	}

	/**
	 * Copy webserver files from source directory to temp directory
	 */
	private function copyWebserverFiles(): void {
		// Get the source webserver directory
		$source_dir = __DIR__ . '/webserver';

		if ( ! is_dir( $source_dir ) ) {
			$error_msg = 'Webserver source directory not found: ' . $source_dir;
			if ( $this->logger ) {
				$this->logger->error( $error_msg );
			}
			throw new \RuntimeException( $error_msg );
		}

		// Copy all files recursively
		$this->recursiveCopy( $source_dir, $this->webroot );

		if ( $this->logger ) {
			$this->logger->debug( 'Webserver files copied successfully', [
				'source'      => $source_dir,
				'destination' => $this->webroot
			] );
		}
	}

	/**
	 * Recursively copy directory contents
	 */
	private function recursiveCopy( string $source, string $dest ): void {
		// Create destination directory if it doesn't exist
		if ( ! is_dir( $dest ) ) {
			mkdir( $dest, 0777, true );
		}

		// Get all files and directories
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $source, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $item ) {
			$target = $dest . '/' . $iterator->getSubPathName();

			if ( $item->isDir() ) {
				if ( ! is_dir( $target ) ) {
					mkdir( $target, 0777, true );
				}
			} else {
				copy( $item->getPathname(), $target );
				// Make PHP files executable
				if ( pathinfo( $target, PATHINFO_EXTENSION ) === 'php' ) {
					chmod( $target, 0755 );
				}
			}
		}
	}

	/**
	 * Replace placeholders in the router file (for temp mode)
	 *
	 * @param string $template The router template file name
	 */
	private function replacePlaceholders( string $template ): void {
		$router_file = $this->webroot . '/' . $template;

		if ( ! file_exists( $router_file ) ) {
			throw new \RuntimeException( 'Router file not found: ' . $router_file );
		}

		// Read the router content
		$content = file_get_contents( $router_file );

		// Get AI directory from runtime config or use default
		$ai_dir = $this->runtimeConfig['ai_dir'] ?? sys_get_temp_dir() . '/qit-node-ai';

		// Ensure AI directory exists
		if ( ! is_dir( $ai_dir ) ) {
			mkdir( $ai_dir, 0700, true );
			if ( $this->logger ) {
				$this->logger->debug( 'Created AI directory', [ 'ai_dir' => $ai_dir ] );
			}
		}

		// Replace placeholders
		$replacements = [
			'{{NODE_TOKEN}}'      => $this->node_token,
			'{{LOG_FILE}}'        => $this->logger ? $this->logger->get_log_file() : sys_get_temp_dir() . '/qit-node.log',
			'{{PROVIDER}}'        => $this->provider,
			'{{PROVIDER_CONFIG}}' => json_encode( $this->providerConfig ),
			'{{AI_DIR}}'          => $ai_dir
		];

		foreach ( $replacements as $placeholder => $value ) {
			$content = str_replace( $placeholder, $value, $content );
		}

		// Write back the modified content
		file_put_contents( $router_file, $content );

		if ( $this->logger ) {
			$this->logger->debug( 'Replaced placeholders in ' . $template, [
				'placeholders' => array_keys( $replacements )
			] );
		}
	}

	/**
	 * Create a temporary router file for local mode
	 *
	 * @param string $template The router template file name
	 *
	 * @return string Path to the temporary router file
	 */
	private function createTempRouterFile( string $template ): string {
		$source_router = $this->webroot . '/' . $template;
		$temp_router   = $this->webroot . '/' . pathinfo( $template, PATHINFO_FILENAME ) . '.local.php'; // Save in same directory

		if ( ! file_exists( $source_router ) ) {
			throw new \RuntimeException( 'Router file not found: ' . $source_router );
		}

		// Read the router content
		$content = file_get_contents( $source_router );

		// Get AI directory from runtime config or use default
		$ai_dir = $this->runtimeConfig['ai_dir'] ?? sys_get_temp_dir() . '/qit-node-ai';

		// Ensure AI directory exists
		if ( ! is_dir( $ai_dir ) ) {
			mkdir( $ai_dir, 0700, true );
			if ( $this->logger ) {
				$this->logger->debug( 'Created AI directory', [ 'ai_dir' => $ai_dir ] );
			}
		}

		// Replace placeholders
		$replacements = [
			'{{NODE_TOKEN}}'      => $this->node_token,
			'{{LOG_FILE}}'        => $this->logger ? $this->logger->get_log_file() : sys_get_temp_dir() . '/qit-node.log',
			'{{PROVIDER}}'        => $this->provider,
			'{{PROVIDER_CONFIG}}' => json_encode( $this->providerConfig ),
			'{{AI_DIR}}'          => $ai_dir
		];

		foreach ( $replacements as $placeholder => $value ) {
			$content = str_replace( $placeholder, $value, $content );
		}

		// Write the modified content to local temp file
		file_put_contents( $temp_router, $content );
		chmod( $temp_router, 0755 );

		if ( $this->logger ) {
			$this->logger->debug( 'Created local router file', [
				'path'         => $temp_router,
				'placeholders' => array_keys( $replacements )
			] );
		}

		return $temp_router;
	}

	public function get_node_token(): string {
		return $this->node_token;
	}


	private function findAvailablePort(): int {
		// Let PHP find an available port by binding to port 0
		$tempServer = stream_socket_server( 'tcp://127.0.0.1:0', $errno, $errstr );
		if ( ! $tempServer ) {
			throw new \RuntimeException( "Failed to find available port: $errstr" );
		}

		$name = stream_socket_get_name( $tempServer, false );
		fclose( $tempServer );

		$parts = explode( ':', $name );

		return (int) $parts[1];
	}

	public function stop(): void {
		if ( $this->logger ) {
			$this->logger->info( 'Stopping webserver', [
				'mode' => $this->use_local_mode ? 'local' : 'temp'
			] );
		}

		if ( $this->process && $this->process->isRunning() ) {
			if ( $this->logger ) {
				$this->logger->debug( 'Terminating webserver process' );
			}
			$this->process->stop();
		} else if ( $this->logger ) {
			$this->logger->debug( 'No running process to stop' );
		}

		// Clean up temporary router file in local mode
		if ( $this->use_local_mode ) {
			$template_base = pathinfo( $this->routerTemplate, PATHINFO_FILENAME );
			$local_router  = $this->webroot . '/' . $template_base . '.local.php';
			if ( file_exists( $local_router ) ) {
				unlink( $local_router );
				if ( $this->logger ) {
					$this->logger->debug( 'Removed local router file', [ 'path' => $local_router ] );
				}
			}

			if ( $this->logger ) {
				$this->logger->info( 'Webserver stopped (local mode - cleaned up ' . $template_base . '.local.php)' );
			}

			return;
		}

		if ( $this->logger ) {
			$this->logger->info( 'Skipping explicit tmp cleanup; relying on OS tmp purge' );
		}

	}
}
