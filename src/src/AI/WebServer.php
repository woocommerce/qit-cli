<?php

namespace QIT_CLI\AI;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class WebServer {
	private ?Process $process = null;
	private int $port         = 8000;
	private string $webroot;
	private string $node_token;
	private ?\QIT_CLI\Logging\Logger $logger = null;
	private bool $use_local_mode;
	private string $provider      = 'lmstudio';
	private array $providerConfig = [];
	private array $runtimeConfig  = [];
	private string $routerTemplate;
	private bool $bindLocalhostOnly     = false;
	private ?string $nodeToken          = null;
	private ?string $customLogFile      = null;
	private array $environmentVariables = [];

	public function __construct( bool $use_local_mode = true ) {
		$this->use_local_mode = $use_local_mode;
	}

	public static function is_ai_enabled(): bool {
		return false;
	}

	/**
	 * Set the logger instance.
	 *
	 * @param \QIT_CLI\Logging\Logger $logger The logger instance.
	 */
	public function setLogger( \QIT_CLI\Logging\Logger $logger ): void {
		$this->logger        = $logger;
		$this->customLogFile = $logger->get_log_file();
	}

	/**
	 * Set a node token to use instead of generating a random one.
	 *
	 * @param string $t The token to use.
	 */
	public function setNodeToken( string $t ): void {
		$this->nodeToken = $t;
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

	/**
	 * Set an environment variable for the web server process.
	 *
	 * @param string $name The name of the environment variable.
	 * @param string $value The value of the environment variable.
	 */
	public function setEnvironmentVariable( string $name, string $value ): void {
		$this->environmentVariables[ $name ] = $value;
	}

	public function start(): string {
		/* ───────────────────── 1. Validate caller contract ─────────────────── */
		$required = [
			'nodeToken'      => $this->nodeToken,
			'routerTemplate' => $this->routerTemplate,
			'ai_dir'         => $this->runtimeConfig['ai_dir'] ?? null,
			'tmp_base'       => $this->runtimeConfig['tmp_base'] ?? null,
		];

		$missing = array_keys(
			array_filter( $required, static fn( $v ) => $v === null || $v === '' )
		);

		if ( $missing ) {
			throw new \RuntimeException(
				'WebServer mis‑configuration: missing ' . implode( ', ', $missing )
			);
		}

		/*
		───────────────────── 2. Set guaranteed values ────────────────────── */
		// we *know* $this->nodeToken is present, so no silent fallback:
		$this->node_token = $this->nodeToken;

		if ( $this->logger ) {
			$this->logger->info( 'Starting webserver', [
				'mode' => $this->use_local_mode ? 'local' : 'temp',
			] );
		}

		// Find an available port
		$this->port = $this->findAvailablePort();
		if ( $this->logger ) {
			$this->logger->debug( 'Found available port', [ 'port' => $this->port ] );
		}
		if ( $this->logger ) {
			$this->logger->debug( 'Using provided node token', [
				'token_prefix' => substr( $this->node_token, 0, 8 ) . '...',
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

		// No placeholder replacement or temp router file creation needed anymore
		// Just use the router template directly
		$router_path = $this->webroot . '/' . $this->routerTemplate;

		// Configure open_basedir restrictions for security
		$allowed = [
			// treat as *directories* by adding the trailing slash
			$this->runtimeConfig['tmp_base'] . '/',  // /tmp/qit-node/ (parent, not child)
			$this->runtimeConfig['ai_dir'] . '/', // AI directory
		];

		if ( $this->use_local_mode ) {
			$allowed[] = rtrim( __DIR__, '/' ) . '/'; // Allow access to the project directory
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
				'localhost_only' => $this->bindLocalhostOnly,
			] );
		}

		$env = [
			// everything the routers must know
			'QIT_NODE_TOKEN'   => $this->node_token,
			'QIT_LOG_FILE'     => $this->logger->get_log_file(),
			'QIT_NODE_DIR'     => $this->runtimeConfig['tmp_base'],
			'QIT_AI_DIR'       => $this->runtimeConfig['ai_dir'],
			'QIT_PROVIDER'     => $this->provider,
			'QIT_PROVIDER_CFG' => json_encode( $this->providerConfig ),
		];

		// Add custom environment variables
		if ( ! empty( $this->environmentVariables ) ) {
			$env = array_merge( $env, $this->environmentVariables );
			if ( $this->logger ) {
				$this->logger->debug('Added custom environment variables', [
					'variables' => array_keys( $this->environmentVariables ),
				]);
			}
		}

		$this->process = new Process(
			[
				'php',
				'-d',
				'open_basedir=' . $openBasedir,
				'-d',
				'variables_order=EGPCS',
				'-S',
				$host,
				'-t',
				$this->webroot,
				// router file (no placeholders any more)
				$this->webroot . '/' . $this->routerTemplate,
			],
			null,   // cwd
			$env
		);

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
					'error_output' => $error_output,
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
		// Get base temp directory from runtime config (already validated)
		$base = $this->runtimeConfig['tmp_base'];
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
					'base'    => $base,
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

		// No need to replace placeholders anymore, as we're using environment variables

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
				'destination' => $this->webroot,
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

	// Placeholder replacement and temp router file creation methods removed
	// as they are no longer needed with environment variables

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
				'mode' => $this->use_local_mode ? 'local' : 'temp',
			] );
		}

		if ( $this->process && $this->process->isRunning() ) {
			if ( $this->logger ) {
				$this->logger->debug( 'Terminating webserver process' );
			}
			$this->process->stop();
		} elseif ( $this->logger ) {
			$this->logger->debug( 'No running process to stop' );
		}

		// No need to clean up temporary router files anymore, as we're using environment variables
		if ( $this->use_local_mode ) {
			if ( $this->logger ) {
				$this->logger->info( 'Webserver stopped (local mode)' );
			}

			return;
		}

		if ( $this->logger ) {
			$this->logger->info( 'Skipping explicit tmp cleanup; relying on OS tmp purge' );
		}
	}
}
