<?php

namespace QIT_CLI\AI;

use Symfony\Component\Process\Process;

class WebServer {
	private ?Process $process = null;
	private int $port = 8000;
	private string $webroot;
	private string $node_token;

	public function start(): string {
		// Find an available port
		$this->port       = $this->findAvailablePort();
		$this->node_token = bin2hex( random_bytes( 32 ) );

		// Create the web server directory with safety checks
		$temp_dir = sys_get_temp_dir();
		if ( empty( $temp_dir ) || $temp_dir === '/' ) {
			throw new \RuntimeException( 'Invalid temp directory' );
		}

		$this->webroot = $temp_dir . '/qit-node-' . uniqid();

		// Ensure we're creating in a safe location
		if ( strpos( $this->webroot, $temp_dir ) !== 0 ) {
			throw new \RuntimeException( 'Webroot must be in temp directory' );
		}

		mkdir( $this->webroot, 0777, true );

		// Create the router script that handles AI requests
		$this->createRouterScript();

		// Start the PHP built-in server in the background
		$this->process = new Process( [
			'php',
			'-S',
			"localhost:{$this->port}",
			'-t',
			$this->webroot,
			$this->webroot . '/router.php'
		] );

		$this->process->start();

		// Give it a moment to start
		usleep( 500000 ); // 0.5 seconds

		// Check if it started successfully
		if ( ! $this->process->isRunning() ) {
			throw new \RuntimeException( 'Failed to start web server: ' . $this->process->getErrorOutput() );
		}

		return "http://localhost:{$this->port}";
	}

	public function get_node_token(): string {
		return $this->node_token;
	}

	private function createRouterScript(): void {
		$node_token = $this->node_token;

		$router = <<<PHP
<?php
header('Content-Type: application/json');

// Only accept POST requests to /process
if (\$_SERVER['REQUEST_METHOD'] !== 'POST' || \$_SERVER['REQUEST_URI'] !== '/process') {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

// Validate node token
\$headers = [];
foreach (\$_SERVER as \$name => \$value) {
    if (substr(\$name, 0, 5) == 'HTTP_') {
        \$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr(\$name, 5)))))] = \$value;
    }
}

if (!isset(\$headers['X-Node-Token']) || \$headers['X-Node-Token'] !== '$node_token') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get JSON input
\$input = json_decode(file_get_contents('php://input'), true);

if (!isset(\$input['prompt']) || !isset(\$input['model'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing prompt or model']);
    exit;
}

try {
    // Use Ollama API
    \$ollama_request = [
        'model' => \$input['model'] ?? 'llama3.2',
        'prompt' => \$input['prompt'],
        'stream' => false
    ];
    
    \$ch = curl_init('http://localhost:11434/api/generate');
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_POST, true);
    curl_setopt(\$ch, CURLOPT_POSTFIELDS, json_encode(\$ollama_request));
    curl_setopt(\$ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
    
    \$response = curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    \$error = curl_error(\$ch);
    curl_close(\$ch);
    
    if (\$response === false) {
        throw new \Exception('Ollama API error: ' . \$error);
    }
    
    if (\$http_code !== 200) {
        throw new \Exception('Ollama API returned status ' . \$http_code);
    }
    
    \$ollama_response = json_decode(\$response, true);
    
    if (!isset(\$ollama_response['response'])) {
        throw new \Exception('Invalid response from Ollama');
    }
    
    echo json_encode([
        'response' => trim(\$ollama_response['response']),
        'model' => \$ollama_response['model'] ?? (\$input['model'] ?? 'llama3.2'),
        'timestamp' => time()
    ]);
    
} catch (\Exception \$e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to process request',
        'message' => \$e->getMessage()
    ]);
}
PHP;

		file_put_contents( $this->webroot . '/router.php', $router );

		// Also create a simple index.php for the root
		file_put_contents( $this->webroot . '/index.php', '<?php echo json_encode(["status" => "QIT Node Active", "endpoint" => "/process"]);' );
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
		if ( $this->process && $this->process->isRunning() ) {
			$this->process->stop();
		}

		// Clean up webroot with safety checks
		if ( isset( $this->webroot ) && is_dir( $this->webroot ) ) {
			// Safety checks before deletion
			$temp_dir           = rtrim( sys_get_temp_dir(), '/' );
			$webroot_normalized = rtrim( $this->webroot, '/' );

			// Ensure we're only deleting our temporary directory
			if ( strpos( $webroot_normalized, $temp_dir ) !== 0 ) {
				return; // Don't delete if not in temp dir
			}

			// Check it's exactly one level deep in temp dir
			$relative_path = substr( $webroot_normalized, strlen( $temp_dir ) + 1 );
			if ( strpos( $relative_path, '/' ) !== false ) {
				return; // Too deep or not in expected location
			}

			// Ensure it contains our marker file
			if ( ! file_exists( $this->webroot . '/router.php' ) ) {
				return; // Don't delete if our router isn't there
			}

			// Additional safety: ensure path contains 'qit-node-'
			if ( strpos( $this->webroot, 'qit-node-' ) === false ) {
				return;
			}

			$this->recursiveRemove( $this->webroot );
		}
	}

	private function recursiveRemove( string $dir ): void {
		// Normalize the path (remove trailing slashes)
		$dir = rtrim( $dir, '/' );

		// Safety check: never delete root or home directories
		if ( in_array( $dir, [ '/', '', $_SERVER['HOME'] ?? '', '/tmp', sys_get_temp_dir() ], true ) ) {
			throw new \RuntimeException( "Refusing to delete protected directory: $dir" );
		}

		// Must have at least 2 directory separators for paths like /tmp/qit-node-123
		if ( substr_count( $dir, '/' ) < 2 ) {
			throw new \RuntimeException( "Directory path too shallow, refusing to delete: $dir" );
		}

		// Must be in temp directory
		$temp_dir = rtrim( sys_get_temp_dir(), '/' );
		if ( strpos( $dir, $temp_dir ) !== 0 ) {
			throw new \RuntimeException( "Can only delete directories in temp folder" );
		}

		// Must contain our qit-node- prefix (only check the root directory name)
		if ( strpos( $dir, 'qit-node-' ) === false ) {
			throw new \RuntimeException( "Can only delete qit-node directories" );
		}

		if ( is_dir( $dir ) ) {
			$objects = scandir( $dir );
			foreach ( $objects as $object ) {
				if ( $object != "." && $object != ".." ) {
					$full_path = $dir . "/" . $object;
					if ( is_dir( $full_path ) ) {
						$this->recursiveRemove( $full_path );
					} else {
						unlink( $full_path );
					}
				}
			}
			rmdir( $dir );
		}
	}
}