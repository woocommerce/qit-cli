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
		if ( $this->use_local_mode ) {
			$router_path = $this->createTempRouterFile();
		} else {
			// In temp mode, replace placeholders in the copied router.php
			$this->replacePlaceholders();
			$router_path = $this->webroot . '/router.php';
		}

		// Start the PHP built-in server in the background
		if ( $this->logger ) {
			$this->logger->info( 'Starting PHP built-in server', [
				'host'    => "localhost:{$this->port}",
				'webroot' => $this->webroot,
				'router'  => $router_path,
				'mode'    => $this->use_local_mode ? 'local' : 'temp'
			] );
		}

		$this->process = new Process( [
			'php',
			'-S',
			"localhost:{$this->port}",
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
		// Create the web server directory with safety checks
		$temp_dir = sys_get_temp_dir();
		if ( empty( $temp_dir ) || $temp_dir === '/' ) {
			$error_msg = 'Invalid temp directory';
			if ( $this->logger ) {
				$this->logger->error( $error_msg, [ 'temp_dir' => $temp_dir ] );
			}
			throw new \RuntimeException( $error_msg );
		}

		$this->webroot = $temp_dir . '/qit-node-' . uniqid();
		if ( $this->logger ) {
			$this->logger->debug( 'Creating webroot directory', [ 'webroot' => $this->webroot ] );
		}

		// Ensure we're creating in a safe location
		if ( strpos( $this->webroot, $temp_dir ) !== 0 ) {
			$error_msg = 'Webroot must be in temp directory';
			if ( $this->logger ) {
				$this->logger->error( $error_msg, [
					'webroot'  => $this->webroot,
					'temp_dir' => $temp_dir
				] );
			}
			throw new \RuntimeException( $error_msg );
		}

		mkdir( $this->webroot, 0777, true );
		if ( $this->logger ) {
			$this->logger->debug( 'Created webroot directory' );
		}

		// Copy webserver files from source to temp directory
		if ( $this->logger ) {
			$this->logger->debug( 'Copying webserver files to temp directory' );
		}
		$this->copyWebserverFiles();

		// Replace placeholders in router.php
		$this->replacePlaceholders();

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
	 * Replace placeholders in the router.php file (for temp mode)
	 */
	private function replacePlaceholders(): void {
		$router_file = $this->webroot . '/router.php';

		if ( ! file_exists( $router_file ) ) {
			throw new \RuntimeException( 'Router file not found: ' . $router_file );
		}

		// Read the router content
		$content = file_get_contents( $router_file );

		// Replace placeholders
		$replacements = [
			'{{NODE_TOKEN}}'      => $this->node_token,
			'{{LOG_FILE}}'        => $this->logger ? $this->logger->get_log_file() : sys_get_temp_dir() . '/qit-node.log',
			'{{PROVIDER}}'        => $this->provider,
			'{{PROVIDER_CONFIG}}' => json_encode( $this->providerConfig )
		];

		foreach ( $replacements as $placeholder => $value ) {
			$content = str_replace( $placeholder, $value, $content );
		}

		// Write back the modified content
		file_put_contents( $router_file, $content );

		if ( $this->logger ) {
			$this->logger->debug( 'Replaced placeholders in router.php', [
				'placeholders' => array_keys( $replacements )
			] );
		}
	}

	/**
	 * Create a temporary router file for local mode
	 * @return string Path to the temporary router file
	 */
	private function createTempRouterFile(): string {
		$source_router = $this->webroot . '/router.php';
		$temp_router   = $this->webroot . '/router.local.php'; // Save in same directory

		if ( ! file_exists( $source_router ) ) {
			throw new \RuntimeException( 'Router file not found: ' . $source_router );
		}

		// Read the router content
		$content = file_get_contents( $source_router );

		// Replace placeholders
		$replacements = [
			'{{NODE_TOKEN}}'      => $this->node_token,
			'{{LOG_FILE}}'        => $this->logger ? $this->logger->get_log_file() : sys_get_temp_dir() . '/qit-node.log',
			'{{PROVIDER}}'        => $this->provider,
			'{{PROVIDER_CONFIG}}' => json_encode( $this->providerConfig )
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
			$local_router = $this->webroot . '/router.local.php';
			if ( file_exists( $local_router ) ) {
				unlink( $local_router );
				if ( $this->logger ) {
					$this->logger->debug( 'Removed local router file', [ 'path' => $local_router ] );
				}
			}

			if ( $this->logger ) {
				$this->logger->info( 'Webserver stopped (local mode - cleaned up router.local.php)' );
			}

			return;
		}

		// Clean up webroot with safety checks (only for temp mode)
		if ( isset( $this->webroot ) && is_dir( $this->webroot ) ) {
			if ( $this->logger ) {
				$this->logger->debug( 'Cleaning up webroot directory', [ 'path' => $this->webroot ] );
			}

			// Safety checks before deletion
			$temp_dir           = rtrim( sys_get_temp_dir(), '/' );
			$webroot_normalized = rtrim( $this->webroot, '/' );

			// Ensure we're only deleting our temporary directory
			if ( strpos( $webroot_normalized, $temp_dir ) !== 0 ) {
				if ( $this->logger ) {
					$this->logger->warning( 'Skipping webroot deletion - not in temp dir', [
						'webroot'  => $webroot_normalized,
						'temp_dir' => $temp_dir
					] );
				}

				return; // Don't delete if not in temp dir
			}

			// Check it's exactly one level deep in temp dir
			$relative_path = substr( $webroot_normalized, strlen( $temp_dir ) + 1 );
			if ( strpos( $relative_path, '/' ) !== false ) {
				if ( $this->logger ) {
					$this->logger->warning( 'Skipping webroot deletion - too deep or unexpected location', [
						'relative_path' => $relative_path
					] );
				}

				return; // Too deep or not in expected location
			}

			// Ensure it contains our marker file
			if ( ! file_exists( $this->webroot . '/router.php' ) ) {
				if ( $this->logger ) {
					$this->logger->warning( 'Skipping webroot deletion - router.php not found' );
				}

				return; // Don't delete if our router isn't there
			}

			// Additional safety: ensure path contains 'qit-node-'
			if ( strpos( $this->webroot, 'qit-node-' ) === false ) {
				if ( $this->logger ) {
					$this->logger->warning( 'Skipping webroot deletion - missing qit-node- prefix' );
				}

				return;
			}

			if ( $this->logger ) {
				$this->logger->info( 'Removing webroot directory', [ 'path' => $this->webroot ] );
			}
			$this->recursiveRemove( $this->webroot );

			if ( $this->logger ) {
				$this->logger->debug( 'Webroot directory removed' );
			}
		} else if ( $this->logger ) {
			$this->logger->debug( 'No webroot directory to clean up' );
		}

		if ( $this->logger ) {
			$this->logger->info( 'Webserver stopped successfully' );
		}
	}

	private function recursiveRemove( string $dir ): void {
		// Normalize the path (remove trailing slashes)
		$dir = rtrim( $dir, '/' );

		if ( $this->logger ) {
			$this->logger->debug( 'Recursively removing directory', [ 'dir' => $dir ] );
		}

		// Safety check: never delete root or home directories
		if ( in_array( $dir, [ '/', '', $_SERVER['HOME'] ?? '', '/tmp', sys_get_temp_dir() ], true ) ) {
			$error_msg = "Refusing to delete protected directory: $dir";
			if ( $this->logger ) {
				$this->logger->error( $error_msg );
			}
			throw new \RuntimeException( $error_msg );
		}

		// Must have at least 2 directory separators for paths like /tmp/qit-node-123
		if ( substr_count( $dir, '/' ) < 2 ) {
			$error_msg = "Directory path too shallow, refusing to delete: $dir";
			if ( $this->logger ) {
				$this->logger->error( $error_msg );
			}
			throw new \RuntimeException( $error_msg );
		}

		// Must be in temp directory
		$temp_dir = rtrim( sys_get_temp_dir(), '/' );
		if ( strpos( $dir, $temp_dir ) !== 0 ) {
			$error_msg = "Can only delete directories in temp folder";
			if ( $this->logger ) {
				$this->logger->error( $error_msg, [
					'dir'      => $dir,
					'temp_dir' => $temp_dir
				] );
			}
			throw new \RuntimeException( $error_msg );
		}

		// Must contain our qit-node- prefix (only check the root directory name)
		if ( strpos( $dir, 'qit-node-' ) === false ) {
			$error_msg = "Can only delete qit-node directories";
			if ( $this->logger ) {
				$this->logger->error( $error_msg, [ 'dir' => $dir ] );
			}
			throw new \RuntimeException( $error_msg );
		}

		if ( is_dir( $dir ) ) {
			$objects    = scandir( $dir );
			$file_count = 0;
			$dir_count  = 0;

			foreach ( $objects as $object ) {
				if ( $object != "." && $object != ".." ) {
					$full_path = $dir . "/" . $object;
					if ( is_dir( $full_path ) ) {
						$dir_count ++;
						$this->recursiveRemove( $full_path );
					} else {
						$file_count ++;
						unlink( $full_path );
					}
				}
			}

			if ( $this->logger ) {
				$this->logger->debug( 'Removing directory contents', [
					'dir'             => $dir,
					'files_removed'   => $file_count,
					'subdirs_removed' => $dir_count
				] );
			}

			rmdir( $dir );
		} else if ( $this->logger ) {
			$this->logger->warning( 'Attempted to remove non-directory', [ 'path' => $dir ] );
		}
	}
}
