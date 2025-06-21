<?php

namespace QIT_CLI\AI;

use Symfony\Component\Process\Process;

class WebServer {
	private ?Process $process = null;
	private int $port = 8000;
	private string $webroot;
	private string $node_token;
	private ?\QIT_CLI\Logging\Logger $logger = null;

	protected Ollama $ollama;

	public function __construct( Ollama $ollama ) {
		$this->ollama = $ollama;
	}

	/**
	 * Set the logger instance.
	 *
	 * @param \QIT_CLI\Logging\Logger $logger The logger instance.
	 */
	public function setLogger( \QIT_CLI\Logging\Logger $logger ): void {
		$this->logger = $logger;
	}

	public function start(): string {
		if ( $this->logger ) {
			$this->logger->info( 'Starting webserver' );
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

		// Create the router script that handles AI requests
		if ( $this->logger ) {
			$this->logger->debug( 'Creating router script' );
		}
		$this->createRouterScript();
		if ( $this->logger ) {
			$this->logger->debug( 'Router script created' );
		}

		// Start the PHP built-in server in the background
		if ( $this->logger ) {
			$this->logger->info( 'Starting PHP built-in server', [
				'host'    => "localhost:{$this->port}",
				'webroot' => $this->webroot
			] );
		}

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

	public function get_node_token(): string {
		return $this->node_token;
	}

	private function createRouterScript(): void {
		$node_token     = $this->node_token;
		$ollama_api_url = $this->ollama->get_api_base_url();
		$log_file       = $this->logger ? $this->logger->get_log_file() : sys_get_temp_dir() . '/qit-node.log';

		$router = <<<PHP
<?php
header('Content-Type: application/json');

// Configure PHP error logging
ini_set('log_errors', 1);
ini_set('error_log', '$log_file');
ini_set('display_errors', 0);

// Configure logging
\$router_log_file = '$log_file';

// Enhanced logging functions
function log_message(\$level, \$message, \$context = []) {
    \$timestamp = date('Y-m-d H:i:s');
    \$formatted_message = "[\$timestamp] [\$level] [Router] \$message";

    // Add context if available
    if (!empty(\$context)) {
        \$formatted_message .= " " . json_encode(\$context, JSON_UNESCAPED_SLASHES);
    }

    // Write to log file
    global \$router_log_file;
    file_put_contents(\$router_log_file, \$formatted_message . PHP_EOL, FILE_APPEND);

    // Also write to error_log for system logging
    error_log("[QIT Node Router] \$formatted_message");
}

function log_debug(\$message, \$context = []) {
    log_message('debug', \$message, \$context);
}

function log_info(\$message, \$context = []) {
    log_message('info', \$message, \$context);
}

function log_warning(\$message, \$context = []) {
    log_message('warning', \$message, \$context);
}

function log_error(\$message, \$context = []) {
    log_message('error', \$message, \$context);
}

// Function to ensure model is available
function ensure_model_available(\$model, \$ollama_api_url) {
    log_info("Checking if model is available", ['model' => \$model]);

    // Check if model exists by trying to show it
    \$ch = curl_init(\$ollama_api_url . '/api/show');
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_POST, true);
    curl_setopt(\$ch, CURLOPT_POSTFIELDS, json_encode(['model' => \$model]));
    curl_setopt(\$ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 10);

    \$response = curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    curl_close(\$ch);

    if (\$http_code === 200) {
        log_info("Model already exists", ['model' => \$model]);
        return true;
    }

    // Model doesn't exist, try to pull it
    log_info("Model not found, attempting to pull", ['model' => \$model]);

    \$ch = curl_init(\$ollama_api_url . '/api/pull');
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_POST, true);
    curl_setopt(\$ch, CURLOPT_POSTFIELDS, json_encode(['model' => \$model]));
    curl_setopt(\$ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 1800); // 30 minutes timeout for pulling

    \$response = curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    \$error = curl_error(\$ch);
    curl_close(\$ch);

    if (\$http_code !== 200) {
        log_error("Failed to pull model", [
            'model' => \$model,
            'http_code' => \$http_code,
            'error' => \$error,
            'response' => substr(\$response, 0, 500)
        ]);
        return false;
    }

    log_info("Model pulled successfully", ['model' => \$model]);
    return true;
}

// Route handling
\$uri = \$_SERVER['REQUEST_URI'];
\$method = \$_SERVER['REQUEST_METHOD'];

// Log request details
\$request_body = file_get_contents('php://input');
\$headers = [];
foreach (\$_SERVER as \$name => \$value) {
    if (substr(\$name, 0, 5) == 'HTTP_') {
        \$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr(\$name, 5)))))] = \$value;
    }
}

log_info("Received \$method request to \$uri", [
    'headers' => \$headers,
    'body_size' => strlen(\$request_body),
    'remote_addr' => \$_SERVER['REMOTE_ADDR'] ?? 'unknown',
]);

// Only accept POST requests
if (\$method !== 'POST') {
    log_warning("Method not allowed: \$method", ['uri' => \$uri]);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate node token for all routes
\$headers = [];
foreach (\$_SERVER as \$name => \$value) {
    if (substr(\$name, 0, 5) == 'HTTP_') {
        \$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr(\$name, 5)))))] = \$value;
    }
}

if (!isset(\$headers['X-Node-Token'])) {
    log_error("Missing token", ['uri' => \$uri, 'method' => \$method]);
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized - missing token']);
    exit;
}

if (\$headers['X-Node-Token'] !== '$node_token') {
    log_error("Invalid token provided", [
        'uri' => \$uri, 
        'method' => \$method,
        'provided_token' => substr(\$headers['X-Node-Token'], 0, 8) . '...'
    ]);
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized - invalid token']);
    exit;
}

// Rate limiting
\$rate_limit_key = 'analyze_code_' . md5(\$headers['X-Node-Token'] ?? '');
\$rate_limit_file = sys_get_temp_dir() . '/' . \$rate_limit_key;

if (file_exists(\$rate_limit_file)) {
    \$last_request = filemtime(\$rate_limit_file);
    \$time_since_last = time() - \$last_request;

    log_debug("Rate limit check", [
        'time_since_last_request' => \$time_since_last,
        'rate_limit_key' => \$rate_limit_key
    ]);

    if (\$time_since_last < 0.1) { // 1 request per second
        log_warning("Rate limit exceeded", [
            'uri' => \$uri,
            'method' => \$method,
            'time_since_last' => \$time_since_last
        ]);
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded']);
        exit;
    }
}
touch(\$rate_limit_file);

log_debug("Rate limit passed", ['uri' => \$uri]);

// Get JSON input
\$input = json_decode(file_get_contents('php://input'), true);

// Log input with appropriate level and sanitization
if (\$input) {
    // Create a sanitized copy for logging (remove potentially sensitive data)
    \$log_input = \$input;
    if (isset(\$log_input['prompt'])) {
        \$log_input['prompt'] = substr(\$log_input['prompt'], 0, 100) . (strlen(\$log_input['prompt']) > 100 ? '...' : '');
    }

    log_info("Received input for \$uri", \$log_input);
} else {
    log_warning("Invalid JSON input received", [
        'uri' => \$uri,
        'raw_size' => strlen(file_get_contents('php://input'))
    ]);
}

// Route to appropriate handler
log_info("Routing request", ['uri' => \$uri, 'method' => \$method]);

switch (\$uri) {
    case '/process':
        log_info("Handling AI process request");
        handle_ai_process(\$input, '$ollama_api_url');
        break;

    case '/analyze-code':
        log_info("Handling code analysis request");
        handle_code_analysis(\$input);
        break;

    default:
        log_warning("Route not found", ['uri' => \$uri]);
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
}

// AI Processing Handler
function handle_ai_process(\$input, \$ollama_api_url) {
    if (!isset(\$input['prompt']) || !isset(\$input['model'])) {
        log_error("Missing required parameters", [
            'missing' => !isset(\$input['prompt']) ? 'prompt' : 'model',
            'uri' => \$_SERVER['REQUEST_URI']
        ]);
        http_response_code(400);
        echo json_encode(['error' => 'Missing prompt or model']);
        exit;
    }

    try {
        // Ensure the model is available before processing
        \$model = \$input['model'] ?? 'llama3.2';
        if (!ensure_model_available(\$model, \$ollama_api_url)) {
            throw new \Exception('Failed to ensure model availability: ' . \$model);
        }

        // Track processing time
        \$start_time = microtime(true);
        log_info("Starting AI processing", [
            'model' => \$model,
            'job_id' => \$input['job_id'] ?? 'unknown',
            'prompt_length' => strlen(\$input['prompt']),
            'has_schema' => isset(\$input['schema']) ? 'yes' : 'no'
        ]);

        \$ollama_request = [
            'model' => \$model,
            'prompt' => \$input['prompt'],
            'stream' => false,
            'system' => '/no_think', // Disable thinking for models that support it
        ];

        // Add format if schema is provided
        if (isset(\$input['schema']) && is_array(\$input['schema'])) {
            \$ollama_request['format'] = \$input['schema'];
            log_debug("Using schema format", ['schema_keys' => array_keys(\$input['schema'])]);
        }

        log_debug("Sending request to Ollama API", [
            'url' => \$ollama_api_url . '/api/generate',
            'model' => \$ollama_request['model'],
            'system' => \$ollama_request['system']
        ]);

        \$ch = curl_init(\$ollama_api_url . '/api/generate');
        curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(\$ch, CURLOPT_POST, true);
        curl_setopt(\$ch, CURLOPT_POSTFIELDS, json_encode(\$ollama_request));
        curl_setopt(\$ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt(\$ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout

        \$response = curl_exec(\$ch);
        \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
        \$error = curl_error(\$ch);
        \$info = curl_getinfo(\$ch);
        curl_close(\$ch);

        log_debug("Ollama API response received", [
            'http_code' => \$http_code,
            'total_time' => \$info['total_time'],
            'size_download' => \$info['size_download'],
            'has_error' => !empty(\$error) ? 'yes' : 'no'
        ]);

        if (\$response === false) {
            log_error("Ollama API curl error", ['error' => \$error, 'info' => \$info]);
            throw new \Exception('Ollama API error: ' . \$error);
        }

        if (\$http_code !== 200) {
            log_error("Ollama API non-200 response", [
                'http_code' => \$http_code, 
                'response' => substr(\$response, 0, 500)
            ]);
            throw new \Exception('Ollama API returned status ' . \$http_code);
        }

        \$ollama_response = json_decode(\$response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_error("JSON decode error", ['error' => json_last_error_msg()]);
            throw new \Exception('Invalid JSON response from Ollama: ' . json_last_error_msg());
        }

        if (!isset(\$ollama_response['response'])) {
            log_error("Invalid Ollama response structure", [
                'keys' => array_keys(\$ollama_response),
                'response_excerpt' => substr(\$response, 0, 500)
            ]);
            throw new \Exception('Invalid response from Ollama');
        }

        // Calculate processing time
        \$end_time = microtime(true);
        \$processing_time_ms = round((\$end_time - \$start_time) * 1000);

        // Calculate tokens per second
        \$tokens_per_second = 0;
        if (isset(\$ollama_response['eval_count']) && isset(\$ollama_response['eval_duration']) && \$ollama_response['eval_duration'] > 0) {
            \$eval_seconds = \$ollama_response['eval_duration'] / 1000000000;
            \$tokens_per_second = round(\$ollama_response['eval_count'] / \$eval_seconds, 2);
        }

        // Also include prompt evaluation metrics if available
        \$prompt_eval_tokens = \$ollama_response['prompt_eval_count'] ?? null;
        \$prompt_eval_duration = null;
        if (isset(\$ollama_response['prompt_eval_duration']) && \$ollama_response['prompt_eval_duration'] > 0) {
            \$prompt_eval_duration = round(\$ollama_response['prompt_eval_duration'] / 1000000); // Convert to milliseconds
        }

        // Log performance metrics
        log_info("AI processing completed successfully", [
            'job_id' => \$input['job_id'] ?? 'unknown',
            'model' => \$ollama_response['model'] ?? (\$input['model'] ?? 'llama3.2'),
            'processing_time_ms' => \$processing_time_ms,
            'tokens_generated' => \$ollama_response['eval_count'] ?? 0,
            'tokens_per_second' => \$tokens_per_second,
            'response_length' => strlen(\$ollama_response['response']),
        ]);

        // Prepare response
        \$response_data = [
            'response' => trim(\$ollama_response['response']),
            'model' => \$ollama_response['model'] ?? (\$input['model'] ?? 'llama3.2'),
            'timestamp' => time(),
            'processing_time_ms' => \$processing_time_ms,
            'tokens_generated' => \$ollama_response['eval_count'] ?? null,
            'tokens_per_second' => \$tokens_per_second,
            'prompt_eval_tokens' => \$prompt_eval_tokens,
            'prompt_eval_duration_ms' => \$prompt_eval_duration,
            'total_duration_ms' => isset(\$ollama_response['total_duration']) ? round(\$ollama_response['total_duration'] / 1000000) : \$processing_time_ms
        ];

        log_debug("Sending response", [
            'response_size' => strlen(json_encode(\$response_data)),
            'job_id' => \$input['job_id'] ?? 'unknown'
        ]);

        echo json_encode(\$response_data);

    } catch (\Exception \$e) {
        // Get stack trace for detailed logging
        \$trace = \$e->getTraceAsString();

        log_error('Processing error: ' . \$e->getMessage(), [
            'exception' => get_class(\$e),
            'job_id' => \$input['job_id'] ?? 'unknown',
            'model' => \$input['model'] ?? 'unknown',
            'trace' => \$trace
        ]);

        // Report error back to manager
        \$error_report = [
            'job_id' => \$input['job_id'] ?? null, 
            'error_type' => get_class(\$e),
            'error_message' => \$e->getMessage(),
            'error_time' => date('Y-m-d H:i:s'),
            'job_type' => \$input['type'] ?? 'unknown'
        ];

        log_info("Storing error for next heartbeat", [
            'job_id' => \$input['job_id'] ?? 'unknown',
            'error_type' => get_class(\$e)
        ]);

        // Store error for next heartbeat
        file_put_contents(
            sys_get_temp_dir() . '/qit-node-last-error.json', 
            json_encode(\$error_report)
        );

        // Prepare error response
        \$error_response = [
            'error' => 'Failed to process request',
            'message' => \$e->getMessage(),
            'error_details' => \$error_report
        ];

        log_debug("Sending error response", [
            'status_code' => 500,
            'response_size' => strlen(json_encode(\$error_response))
        ]);

        http_response_code(500);
        echo json_encode(\$error_response);
    }
}

// Code Analysis Handler
function handle_code_analysis(\$input) {
    log_info('Starting code analysis handler');

    // Validate required parameters
    \$required_params = ['zip_url', 'file', 'line'];
    \$missing_params = [];

    foreach (\$required_params as \$param) {
        if (!isset(\$input[\$param])) {
            \$missing_params[] = \$param;
        }
    }

    if (!empty(\$missing_params)) {
        log_error('Missing required parameters for code analysis', [
            'missing' => \$missing_params,
            'provided' => array_keys(\$input)
        ]);

        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters: ' . implode(', ', \$missing_params)]);
        exit;
    }

    // Validate and sanitize inputs
    \$zip_url = \$input['zip_url'];
    \$file = \$input['file'];
    \$line = \$input['line'];

    // Validate file path - only allow PHP files with safe characters
    if (!preg_match('/^[a-zA-Z0-9\/_\-\.]+\.php\$/', \$file)) {
        log_error('Invalid file path format', ['file' => \$file]);
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file path format']);
        exit;
    }

    // Remove any directory traversal attempts
    \$file = str_replace('..', '', \$file);
    \$file = ltrim(\$file, '/');

    // Validate line number
    \$line = filter_var(\$line, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 1,
            'max_range' => 999999
        ]
    ]);

    if (\$line === false) {
        log_error('Invalid line number', ['line' => \$input['line']]);
        http_response_code(400);
        echo json_encode(['error' => 'Invalid line number']);
        exit;
    }

    // Validate ZIP URL
    if (!filter_var(\$zip_url, FILTER_VALIDATE_URL)) {
        log_error('Invalid ZIP URL format', ['url' => substr(\$zip_url, 0, 50) . '...']);
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ZIP URL format']);
        exit;
    }

    // Validate URL scheme (only allow HTTPS)
    \$url_parts = parse_url(\$zip_url);
    if (\$url_parts['scheme'] !== 'https') {
        log_error('Only HTTPS URLs are allowed', ['scheme' => \$url_parts['scheme']]);
        http_response_code(400);
        echo json_encode(['error' => 'Only HTTPS URLs are allowed']);
        exit;
    }

    log_info("Code analysis parameters validated", [
        'file' => \$file,
        'line' => \$line,
        'zip_url' => substr(\$zip_url, 0, 50) . '...'
    ]);

    try {
        // Generate secure session ID
        \$session_id = \$input['session_id'] ?? null;
        if (\$session_id && !preg_match('/^[a-zA-Z0-9_\-]{1,64}\$/', \$session_id)) {
            log_warning('Invalid session ID provided, generating new one');
            \$session_id = null;
        }

        if (!\$session_id) {
            \$session_id = 'qit_' . bin2hex(random_bytes(16));
        }

        \$cache_dir = sys_get_temp_dir() . '/qit-code-analysis';
        \$work_dir = \$cache_dir . '/' . \$session_id;

        // Validate work directory path
        \$real_cache_dir = realpath(\$cache_dir);
        if (\$real_cache_dir === false) {
            \$real_cache_dir = \$cache_dir; // Directory doesn't exist yet
        }

        // Ensure work_dir is within cache_dir
        if (file_exists(\$work_dir)) {
            \$real_work_dir = realpath(\$work_dir);
            if (\$real_work_dir === false || strpos(\$real_work_dir, \$real_cache_dir) !== 0) {
                log_error('Invalid work directory path', [
                    'work_dir' => \$work_dir,
                    'real_work_dir' => \$real_work_dir
                ]);
                throw new Exception('Invalid work directory');
            }
        }

        log_info("Code analysis session details", [
            'session_id' => \$session_id,
            'work_dir' => \$work_dir,
            'file' => \$file,
            'line' => \$line
        ]);

        // Create cache directory with restricted permissions
        if (!is_dir(\$cache_dir)) {
            log_debug("Creating cache directory", ['path' => \$cache_dir]);
            mkdir(\$cache_dir, 0700, true);
        }

        // Download and extract if not already cached
        if (!is_dir(\$work_dir) || !file_exists(\$work_dir . '/.analyzed')) {
            log_info("Preparing codebase from URL", [
                'zip_url' => substr(\$zip_url, 0, 50) . '...',
                'work_dir' => \$work_dir
            ]);

            // Create work directory with restricted permissions
            if (!is_dir(\$work_dir)) {
                mkdir(\$work_dir, 0700, true);
            }

            prepare_codebase(\$zip_url, \$work_dir);
        } else {
            log_info("Using cached codebase", [
                'work_dir' => \$work_dir,
                'last_modified' => date('Y-m-d H:i:s', filemtime(\$work_dir))
            ]);
        }

        // Validate that the requested file exists within work_dir
        \$full_file_path = \$work_dir . '/' . \$file;
        \$real_file_path = realpath(\$full_file_path);
        \$real_work_dir = realpath(\$work_dir);

        if (\$real_file_path === false || strpos(\$real_file_path, \$real_work_dir) !== 0) {
            log_error('File not found or outside work directory', [
                'file' => \$file,
                'full_path' => \$full_file_path
            ]);
            throw new Exception('File not found or invalid path');
        }

        // Step 1: Find symbol using Docker-based approach
        log_info("Finding symbol at location");
        \$symbol_info = find_symbol_with_docker_v2(\$work_dir, \$file, \$line);

        if (!isset(\$symbol_info['symbol']) || empty(\$symbol_info['symbol'])) {
            log_warning("No symbol found", [
                'file' => \$file,
                'line' => \$line
            ]);
            // Don't throw - continue with limited context
            \$symbol_info = ['symbol' => null, 'type' => 'unknown'];
        } else {
            log_info("Found symbol", ['symbol' => \$symbol_info['symbol']]);
        }

        // Step 2: Get comprehensive analysis from Psalm
        log_info("Running comprehensive Psalm analysis");
        \$psalm_analysis = run_comprehensive_psalm_analysis(\$work_dir);

        // Step 3: Extract execution context
        log_info("Extracting execution context");
        \$execution_context = extract_execution_context_from_analysis(
            \$psalm_analysis, 
            \$file, 
            \$line,
            \$symbol_info['symbol']
        );

        // Step 4: Find WordPress hooks and entry points
        log_info("Analyzing WordPress integration points");
        \$wordpress_context = analyze_wordpress_integration(\$work_dir, \$execution_context);

        // Prepare response - sanitize output
        \$response = [
            'success' => true,
            'context' => [
                'symbol' => htmlspecialchars(\$symbol_info['symbol'] ?? '', ENT_QUOTES, 'UTF-8'),
                'symbol_info' => array_map(function(\$value) {
                    return is_string(\$value) ? htmlspecialchars(\$value, ENT_QUOTES, 'UTF-8') : \$value;
                }, \$symbol_info),
                'execution_paths' => array_map(function(\$path) {
                    return [
                        'file' => htmlspecialchars(\$path['file'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'line' => (int)(\$path['line'] ?? 0),
                        'type' => htmlspecialchars(\$path['type'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'message' => htmlspecialchars(\$path['message'] ?? '', ENT_QUOTES, 'UTF-8')
                    ];
                }, \$execution_context['paths'] ?? []),
                'references' => array_map(function(\$ref) {
                    return [
                        'file' => htmlspecialchars(\$ref['file'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'line' => (int)(\$ref['line'] ?? 0),
                        'type' => htmlspecialchars(\$ref['type'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'message' => htmlspecialchars(\$ref['message'] ?? '', ENT_QUOTES, 'UTF-8')
                    ];
                }, \$execution_context['references'] ?? []),
                'wordpress_hooks' => array_map(function(\$hook) {
                    return [
                        'type' => htmlspecialchars(\$hook['type'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'name' => htmlspecialchars(\$hook['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'file' => htmlspecialchars(\$hook['file'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'line' => (int)(\$hook['line'] ?? 0),
                        'is_public' => (bool)(\$hook['is_public'] ?? false)
                    ];
                }, \$wordpress_context['hooks'] ?? []),
                'has_public_access' => (bool)(\$wordpress_context['has_public_access'] ?? false),
                'entry_points' => array_map(function(\$entry) {
                    return [
                        'type' => htmlspecialchars(\$entry['type'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'location' => htmlspecialchars(\$entry['location'] ?? '', ENT_QUOTES, 'UTF-8')
                    ];
                }, \$wordpress_context['entry_points'] ?? [])
            ]
        ];

        log_info("Sending code analysis response", [
            'has_symbol' => !empty(\$symbol_info['symbol']),
            'execution_paths_count' => count(\$execution_context['paths'] ?? []),
            'hooks_count' => count(\$wordpress_context['hooks'] ?? []),
            'response_size' => strlen(json_encode(\$response))
        ]);

        echo json_encode(\$response);

    } catch (\Exception \$e) {
        log_error('Code analysis error: ' . \$e->getMessage(), [
            'exception' => get_class(\$e),
            'file' => \$file ?? 'unknown',
            'line' => \$line ?? 'unknown'
        ]);

        // Store error for heartbeat (sanitize error message)
        \$error_report = [
            'job_id' => isset(\$input['job_id']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', \$input['job_id']) : null,
            'error_type' => get_class(\$e),
            'error_message' => substr(preg_replace('/[^\x20-\x7E]/', '', \$e->getMessage()), 0, 500),
            'error_time' => date('Y-m-d H:i:s'),
            'job_type' => 'code_analysis'
        ];

        file_put_contents(
            sys_get_temp_dir() . '/qit-node-last-error.json', 
            json_encode(\$error_report)
        );

        http_response_code(500);
        echo json_encode([
            'error' => 'Analysis failed',
            'message' => htmlspecialchars(substr(\$e->getMessage(), 0, 200), ENT_QUOTES, 'UTF-8')
        ]);
    }
}

function find_symbol_with_docker_v2(\$work_dir, \$file, \$line) {
    // Create a PHP script that will run in Docker
    \$script_content = <<<'SCRIPT'
<?php
\$file = \$argv[1];
\$line = (int)\$argv[2];

if (!file_exists(\$file)) {
    echo json_encode(['error' => 'File not found']);
    exit(1);
}

\$content = file_get_contents(\$file);
\$lines = explode("\\n", \$content);

// Get context around the target line
\$start = max(0, \$line - 50);
\$end = min(count(\$lines), \$line + 50);

// Look for function/class definitions before our line
\$current_class = null;
\$current_namespace = null;
\$closest_function = null;
\$closest_line = 0;

for (\$i = \$start; \$i < \$line; \$i++) {
    \$line_content = \$lines[\$i] ?? '';

    // Check for namespace
    if (preg_match('/^\\s*namespace\\s+([^;]+);/', \$line_content, \$m)) {
        \$current_namespace = trim(\$m[1]);
    }

    // Check for class
    if (preg_match('/^\\s*(?:abstract\\s+)?(?:final\\s+)?class\\s+(\\w+)/', \$line_content, \$m)) {
        \$current_class = \$m[1];
    }

    // Check for function/method
    if (preg_match('/^\\s*(?:public|protected|private|static|\\s)+function\\s+(\\w+)/', \$line_content, \$m)) {
        if (\$i + 1 <= \$line) {
            \$closest_function = \$m[1];
            \$closest_line = \$i + 1;
        }
    }
}

if (\$closest_function) {
    if (\$current_class) {
        \$symbol = (\$current_namespace ? \$current_namespace . '\\\\' : '') . \$current_class . '::' . \$closest_function;
    } else {
        \$symbol = (\$current_namespace ? \$current_namespace . '\\\\' : '') . \$closest_function;
    }

    echo json_encode([
        'symbol' => \$symbol,
        'type' => \$current_class ? 'method' : 'function',
        'class' => \$current_class,
        'function' => \$closest_function,
        'namespace' => \$current_namespace,
        'line' => \$closest_line
    ]);
} else {
    echo json_encode(['symbol' => null, 'error' => 'No function found']);
}
SCRIPT;

    // Write script to temp file
    \$script_path = \$work_dir . '/find_symbol.php';
    file_put_contents(\$script_path, \$script_content);

    // Run it in Docker
    \$cmd = [
        'docker', 'run', '--rm',
        '-v', \$work_dir . ':/work',
        '-w', '/work',
        'php:8.1-cli-alpine',
        'php', 'find_symbol.php',
        \$file,
        \$line
    ];

    \$result = run_docker_command(\$cmd);

    if (\$result['success'] && \$result['output']) {
        \$parsed = json_decode(\$result['output'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return \$parsed;
        }
    }

    // Fallback
    return ['symbol' => null, 'type' => 'unknown'];
}

function run_comprehensive_psalm_analysis(\$work_dir) {
    // Create comprehensive psalm.xml
    \$psalm_config = '<?xml version="1.0"?>
<psalm
    errorLevel="1"
    resolveFromConfigFile="true"
    findUnusedVariables="true"
    findUnusedCode="true"
    reportInfo="true"
>
    <projectFiles>
        <directory name="." />
        <ignoreFiles>
            <directory name="vendor" />
            <directory name="node_modules" />
        </ignoreFiles>
    </projectFiles>

    <issueHandlers>
        <UnusedVariable errorLevel="info" />
        <UnusedParam errorLevel="info" />
        <UnusedProperty errorLevel="info" />
        <UnusedClass errorLevel="info" />
        <UnusedMethod errorLevel="info" />
        <PossiblyUnusedMethod errorLevel="info" />
        <PossiblyUnusedParam errorLevel="info" />
        <PossiblyUnusedProperty errorLevel="info" />
    </issueHandlers>
</psalm>';

    file_put_contents(\$work_dir . '/psalm.xml', \$psalm_config);

    \$cmd = [
        'docker', 'run', '--rm',
        '-v', \$work_dir . ':/app',
        '-w', '/app',
        'ghcr.io/vimeo/psalm:latest',
        '--output-format=json',
        '--show-info=true',
        '--find-unused-code',
        '--no-cache',
        '--threads=1'
    ];

    \$result = run_docker_command(\$cmd);

    if (\$result['success'] && \$result['output']) {
        // Parse JSON lines format
        \$lines = explode("\\n", trim(\$result['output']));
        \$analysis = [];

        foreach (\$lines as \$line) {
            if (empty(\$line)) continue;
            \$item = json_decode(\$line, true);
            if (\$item) {
                \$analysis[] = \$item;
            }
        }

        return \$analysis;
    }

    return [];
}

function extract_execution_context_from_analysis(\$analysis, \$target_file, \$target_line, \$symbol) {
    \$context = [
        'paths' => [],
        'references' => []
    ];

    foreach (\$analysis as \$item) {
        if (!isset(\$item['file_name'])) continue;

        \$file = str_replace('/app/', '', \$item['file_name']);

        // Look for items related to our file/line
        if (\$file === \$target_file && 
            isset(\$item['line_from']) && 
            abs(\$item['line_from'] - \$target_line) < 10) {

            \$context['references'][] = [
                'file' => \$file,
                'line' => \$item['line_from'],
                'type' => \$item['type'] ?? 'unknown',
                'message' => \$item['message'] ?? ''
            ];
        }

        // Look for references to our symbol
        if (\$symbol && isset(\$item['message']) && strpos(\$item['message'], \$symbol) !== false) {
            \$context['paths'][] = [
                'file' => \$file,
                'line' => \$item['line_from'] ?? 0,
                'type' => \$item['type'] ?? 'reference',
                'message' => \$item['message']
            ];
        }
    }

    return \$context;
}

function analyze_wordpress_integration(\$work_dir, \$execution_context) {
    \$wp_context = [
        'hooks' => [],
        'has_public_access' => false,
        'entry_points' => []
    ];

    // Get unique files to analyze
    \$files_to_analyze = [];
    foreach (\$execution_context['paths'] ?? [] as \$path) {
        \$files_to_analyze[\$path['file']] = true;
    }
    foreach (\$execution_context['references'] ?? [] as \$ref) {
        \$files_to_analyze[\$ref['file']] = true;
    }

    // Analyze each file for WordPress patterns
    foreach (array_keys(\$files_to_analyze) as \$file) {
        \$file_path = \$work_dir . '/' . \$file;
        if (!file_exists(\$file_path)) continue;

        \$content = file_get_contents(\$file_path);

        // Find WordPress hooks
        if (preg_match_all('/add_(action|filter)\\s*\\(\\s*[\'"]([^\'"]+)[\'"]/', \$content, \$matches, PREG_OFFSET_CAPTURE)) {
            foreach (\$matches[2] as \$idx => \$match) {
                \$hook_name = \$match[0];
                \$hook_type = \$matches[1][\$idx][0];
                \$offset = \$match[1];

                // Calculate line number
                \$line = substr_count(substr(\$content, 0, \$offset), "\\n") + 1;

                \$is_public = is_public_wordpress_hook(\$hook_name);

                \$wp_context['hooks'][] = [
                    'type' => \$hook_type,
                    'name' => \$hook_name,
                    'file' => \$file,
                    'line' => \$line,
                    'is_public' => \$is_public
                ];

                if (\$is_public) {
                    \$wp_context['has_public_access'] = true;
                    \$wp_context['entry_points'][] = [
                        'type' => 'wordpress_hook',
                        'hook' => \$hook_name,
                        'location' => \$file . ':' . \$line
                    ];
                }
            }
        }

        // Find direct superglobal access
        if (preg_match_all('/\$_(GET|POST|REQUEST|COOKIE|SERVER)\\[/', \$content, \$matches, PREG_OFFSET_CAPTURE)) {
            foreach (\$matches[0] as \$idx => \$match) {
                \$offset = \$match[1];
                \$line = substr_count(substr(\$content, 0, \$offset), "\\n") + 1;

                \$wp_context['has_public_access'] = true;
                \$wp_context['entry_points'][] = [
                    'type' => 'superglobal',
                    'variable' => \$matches[0][\$idx][0],
                    'location' => \$file . ':' . \$line
                ];
            }
        }
    }

    return \$wp_context;
}

function is_public_wordpress_hook(\$hook_name) {
    \$public_hooks = [
        'init', 'wp_loaded', 'template_redirect', 'wp',
        'parse_request', 'send_headers', 'parse_query',
        'pre_get_posts', 'posts_selection', 'wp_enqueue_scripts',
        'wp_head', 'wp_footer', 'rest_api_init', 'wp_ajax_nopriv_'
    ];

    foreach (\$public_hooks as \$public_hook) {
        if (\$hook_name === \$public_hook || strpos(\$hook_name, \$public_hook) === 0) {
            return true;
        }
    }

    return false;
}

function run_docker_command(\$cmd) {
    \$descriptorspec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];

    log_debug("Running Docker command", ['cmd' => implode(' ', \$cmd)]);

    \$process = proc_open(\$cmd, \$descriptorspec, \$pipes);

    if (!is_resource(\$process)) {
        return ['success' => false, 'output' => '', 'error' => 'Failed to start process'];
    }

    fclose(\$pipes[0]);
    \$stdout = stream_get_contents(\$pipes[1]);
    \$stderr = stream_get_contents(\$pipes[2]);
    fclose(\$pipes[1]);
    fclose(\$pipes[2]);
    \$return_code = proc_close(\$process);

    log_debug("Docker command completed", [
        'return_code' => \$return_code,
        'stdout_length' => strlen(\$stdout),
        'stderr_length' => strlen(\$stderr)
    ]);

    return [
        'success' => \$return_code === 0,
        'output' => \$stdout,
        'error' => \$stderr,
        'return_code' => \$return_code
    ];
}

function prepare_codebase(\$zip_url, \$work_dir) {
    log_info("Preparing codebase", [
        'zip_url' => substr(\$zip_url, 0, 50) . '...',
        'work_dir' => \$work_dir
    ]);

    // Create work directory if it doesn't exist
    if (!is_dir(\$work_dir)) {
        log_debug("Creating work directory", ['path' => \$work_dir]);
        mkdir(\$work_dir, 0777, true);
    }

    // Add validation for the zip URL
    if (!filter_var(\$zip_url, FILTER_VALIDATE_URL)) {
        log_error("Invalid ZIP URL format", ['url' => substr(\$zip_url, 0, 50) . '...']);
        throw new \Exception('Invalid ZIP URL provided: ' . \$zip_url);
    }

    // Test if URL is accessible
    log_debug("Checking if ZIP URL is accessible");
    \$headers = @get_headers(\$zip_url);
    if (\$headers === false) {
        log_error("Cannot access ZIP URL", [
            'url' => substr(\$zip_url, 0, 50) . '...',
            'error' => error_get_last()['message'] ?? 'Unknown error'
        ]);
        throw new \Exception('Cannot access ZIP URL: ' . \$zip_url);
    }

    \$status_line = \$headers[0];
    log_debug("ZIP URL status", ['status' => \$status_line]);

    if (strpos(\$status_line, '200') === false && strpos(\$status_line, '302') === false) {
        log_error("ZIP URL returned non-200/302 status", ['status' => \$status_line]);
        throw new \Exception('ZIP URL returned non-200 status: ' . \$status_line);
    }

    // Check file size before downloading
    log_debug("Checking file size before downloading");
    \$ch = curl_init(\$zip_url);
    curl_setopt(\$ch, CURLOPT_NOBODY, true);
    curl_setopt(\$ch, CURLOPT_HEADER, true);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec(\$ch);
    \$size = curl_getinfo(\$ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    \$curl_error = curl_error(\$ch);
    curl_close(\$ch);

    log_info("File size check completed", [
        'size_bytes' => \$size,
        'size_mb' => round(\$size / (1024 * 1024), 2),
        'http_code' => \$http_code,
        'has_error' => !empty(\$curl_error) ? 'yes' : 'no'
    ]);

    // Limit to 500MB
    if (\$size > 500 * 1024 * 1024) {
        log_error("File too large", [
            'size_bytes' => \$size,
            'size_mb' => round(\$size / (1024 * 1024), 2),
            'max_size_mb' => 500
        ]);
        throw new \Exception('File too large: ' . \$size . ' bytes');
    }

    // Download zip file
    \$zip_path = \$work_dir . '/plugin.zip';
    log_info("Downloading zip file", [
        'destination' => \$zip_path,
        'expected_size' => round(\$size / (1024 * 1024), 2) . ' MB'
    ]);

    \$ch = curl_init(\$zip_url);
    \$fp = fopen(\$zip_path, 'wb');

    curl_setopt(\$ch, CURLOPT_FILE, \$fp);
    curl_setopt(\$ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 300);

    \$download_start = microtime(true);
    curl_exec(\$ch);
    \$download_time = microtime(true) - \$download_start;

    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    \$error = curl_error(\$ch);
    \$download_size = curl_getinfo(\$ch, CURLINFO_SIZE_DOWNLOAD);

    curl_close(\$ch);
    fclose(\$fp);

    if (\$http_code !== 200) {
        log_error("Download failed", [
            'http_code' => \$http_code,
            'error' => \$error,
            'download_size' => \$download_size
        ]);
        throw new \Exception("Failed to download file: HTTP \$http_code");
    }

    log_info("Download complete", [
        'size_bytes' => \$download_size,
        'size_mb' => round(\$download_size / (1024 * 1024), 2),
        'time_seconds' => round(\$download_time, 2),
        'speed_mbps' => round((\$download_size / 1024 / 1024) / \$download_time, 2)
    ]);

    log_info("Extracting zip file", ['zip_path' => \$zip_path]);

    // Extract using Docker for safety
    log_debug("Setting up Docker extraction process");
    \$descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];

    \$cmd = [
        'docker', 'run', '--rm',
        '-v', \$work_dir . ':/work',
        'alpine:latest',
        'sh', '-c', 'cd /work && unzip -o plugin.zip && rm plugin.zip'
    ];

    log_debug("Running Docker command", ['command' => implode(' ', \$cmd)]);

    \$extraction_start = microtime(true);
    \$process = proc_open(\$cmd, \$descriptorspec, \$pipes);

    if (is_resource(\$process)) {
        fclose(\$pipes[0]);
        \$stdout = stream_get_contents(\$pipes[1]);
        \$stderr = stream_get_contents(\$pipes[2]);
        fclose(\$pipes[1]);
        fclose(\$pipes[2]);
        \$return_code = proc_close(\$process);
        \$extraction_time = microtime(true) - \$extraction_start;

        log_debug("Docker extraction process completed", [
            'return_code' => \$return_code,
            'time_seconds' => round(\$extraction_time, 2),
            'stdout_length' => strlen(\$stdout),
            'stderr_length' => strlen(\$stderr)
        ]);

        if (\$return_code !== 0) {
            log_error("Extraction failed", [
                'return_code' => \$return_code,
                'stderr' => \$stderr,
                'stdout' => \$stdout
            ]);
            throw new \Exception('Failed to extract zip: ' . \$stderr);
        }

        // Count extracted files
        \$file_count = 0;
        \$dir_count = 0;
        if (preg_match_all('/inflating:\s+(.+)/', \$stdout, \$matches)) {
            \$file_count = count(\$matches[1]);
        }
        if (preg_match_all('/creating:\s+(.+)/', \$stdout, \$matches)) {
            \$dir_count = count(\$matches[1]);
        }

        log_info("Extraction complete", [
            'files_extracted' => \$file_count,
            'directories_created' => \$dir_count,
            'time_seconds' => round(\$extraction_time, 2)
        ]);
    } else {
        log_error("Failed to start Docker extraction process");
        throw new \Exception('Failed to start Docker extraction process');
    }

    // Create psalm config if needed
    log_debug("Creating Psalm config if needed");
    create_psalm_config(\$work_dir);

    // Mark as analyzed
    log_debug("Marking codebase as analyzed");
    touch(\$work_dir . '/.analyzed');

    log_info("Codebase preparation complete", [
        'work_dir' => \$work_dir,
        'total_time' => round(microtime(true) - \$extraction_start, 2)
    ]);
}

function create_psalm_config(\$work_dir) {
    \$psalm_xml = \$work_dir . '/psalm.xml';

    if (!file_exists(\$psalm_xml)) {
        log_debug("Creating Psalm config file", ['path' => \$psalm_xml]);

        \$config_content = '<?xml version="1.0"?>
<psalm errorLevel="8" resolveFromConfigFile="true">
    <projectFiles>
        <directory name="." />
        <ignoreFiles>
            <directory name="vendor" />
            <directory name="node_modules" />
        </ignoreFiles>
    </projectFiles>
</psalm>';

        file_put_contents(\$psalm_xml, \$config_content);
        log_debug("Psalm config file created successfully");
    } else {
        log_debug("Psalm config file already exists", ['path' => \$psalm_xml]);
    }
}

// Cleanup old sessions periodically (1% chance)
if (mt_rand(1, 100) === 1) {
    log_info("Running periodic cleanup of old sessions (1% chance)");
    cleanup_old_sessions();
}

function cleanup_old_sessions() {
    \$cache_dir = sys_get_temp_dir() . '/qit-code-analysis';

    if (!is_dir(\$cache_dir)) {
        log_debug("Cache directory does not exist, skipping cleanup", ['path' => \$cache_dir]);
        return;
    }

    log_debug("Starting cleanup of old sessions", ['cache_dir' => \$cache_dir]);

    \$now = time();
    \$dirs_scanned = 0;
    \$dirs_removed = 0;
    \$dirs_skipped = 0;
    \$dirs_invalid = 0;

    foreach (scandir(\$cache_dir) as \$dir) {
        if (\$dir === '.' || \$dir === '..') continue;

        \$dirs_scanned++;
        \$session_dir = \$cache_dir . '/' . \$dir;
        \$real_path = realpath(\$session_dir);

        // Verify it's really inside cache_dir
        if (\$real_path === false || strpos(\$real_path, realpath(\$cache_dir)) !== 0) {
            log_warning("Skipping directory outside cache_dir", ['dir' => \$session_dir]);
            \$dirs_invalid++;
            continue;
        }

        if (is_dir(\$real_path)) {
            \$mtime = filemtime(\$real_path);
            \$age_hours = round((\$now - \$mtime) / 3600, 1);

            if (\$now - \$mtime > 3600) { // 1 hour old
                log_info("Removing old session directory", [
                    'dir' => \$dir,
                    'age_hours' => \$age_hours
                ]);

                // Use PHP's recursive directory removal instead of exec
                remove_directory_safely(\$real_path);
                \$dirs_removed++;
            } else {
                log_debug("Skipping recent session directory", [
                    'dir' => \$dir,
                    'age_hours' => \$age_hours
                ]);
                \$dirs_skipped++;
            }
        }
    }

    log_info("Session cleanup completed", [
        'dirs_scanned' => \$dirs_scanned,
        'dirs_removed' => \$dirs_removed,
        'dirs_skipped' => \$dirs_skipped,
        'dirs_invalid' => \$dirs_invalid
    ]);
}

function remove_directory_safely(\$dir) {
    if (!is_dir(\$dir)) {
        log_debug("Not a directory, skipping removal", ['path' => \$dir]);
        return;
    }

    log_debug("Removing directory safely", ['dir' => \$dir]);

    \$files = array_diff(scandir(\$dir), ['.', '..']);
    \$file_count = 0;
    \$dir_count = 0;

    foreach (\$files as \$file) {
        \$path = \$dir . '/' . \$file;
        if (is_dir(\$path)) {
            \$dir_count++;
            remove_directory_safely(\$path);
        } else {
            \$file_count++;
            unlink(\$path);
        }
    }

    rmdir(\$dir);

    log_debug("Directory removed", [
        'dir' => \$dir,
        'files_removed' => \$file_count,
        'subdirs_removed' => \$dir_count
    ]);
}
PHP;

		file_put_contents( $this->webroot . '/router.php', $router );

		// Also create a simple index.php for the root
		file_put_contents( $this->webroot . '/index.php', '<?php echo json_encode(["status" => "QIT Node Active", "endpoints" => ["/process", "/analyze-code"]]);' );
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
			$this->logger->info( 'Stopping webserver' );
		}

		if ( $this->process && $this->process->isRunning() ) {
			if ( $this->logger ) {
				$this->logger->debug( 'Terminating webserver process' );
			}
			$this->process->stop();
		} else if ( $this->logger ) {
			$this->logger->debug( 'No running process to stop' );
		}

		// Clean up webroot with safety checks
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
