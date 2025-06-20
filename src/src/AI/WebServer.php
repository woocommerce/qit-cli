<?php

namespace QIT_CLI\AI;

use Symfony\Component\Process\Process;

class WebServer {
	private ?Process $process = null;
	private int $port = 8000;
	private string $webroot;
	private string $node_token;

	protected Ollama $ollama;

	public function __construct( Ollama $ollama ) {
		$this->ollama = $ollama;
	}

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
		$node_token     = $this->node_token;
		$ollama_api_url = $this->ollama->get_api_base_url();

		$router = <<<PHP
<?php
header('Content-Type: application/json');

// Error logging function
function log_error(\$message) {
    error_log('[QIT Node Router] ' . \$message);
}

// Route handling
\$uri = \$_SERVER['REQUEST_URI'];
\$method = \$_SERVER['REQUEST_METHOD'];

log_error("Received \$method request to \$uri");

// Only accept POST requests
if (\$method !== 'POST') {
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

if (!isset(\$headers['X-Node-Token']) || \$headers['X-Node-Token'] !== '$node_token') {
    log_error("Invalid token provided");
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Rate limiting
\$rate_limit_key = 'analyze_code_' . md5(\$headers['X-Node-Token'] ?? '');
\$rate_limit_file = sys_get_temp_dir() . '/' . \$rate_limit_key;

if (file_exists(\$rate_limit_file)) {
    \$last_request = filemtime(\$rate_limit_file);
    if (time() - \$last_request < 1) { // 1 second between requests
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded']);
        exit;
    }
}
touch(\$rate_limit_file);

// Get JSON input
\$input = json_decode(file_get_contents('php://input'), true);
log_error("Received input: " . json_encode(\$input));

// Route to appropriate handler
switch (\$uri) {
    case '/process':
        handle_ai_process(\$input, '$ollama_api_url');
        break;

    case '/analyze-code':
        handle_code_analysis(\$input);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
}

// AI Processing Handler
function handle_ai_process(\$input, \$ollama_api_url) {
    if (!isset(\$input['prompt']) || !isset(\$input['model'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing prompt or model']);
        exit;
    }

    try {
        // Track processing time
        \$start_time = microtime(true);

        \$ollama_request = [
            'model' => \$input['model'] ?? 'llama3.2',
            'prompt' => \$input['prompt'],
            'stream' => false,
            'system' => '/no_think', // Disable thinking for models that support it
        ];

        // Add format if schema is provided
        if (isset(\$input['schema']) && is_array(\$input['schema'])) {
            \$ollama_request['format'] = \$input['schema'];
        }

        \$ch = curl_init(\$ollama_api_url . '/api/generate');
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

        echo json_encode([
            'response' => trim(\$ollama_response['response']),
            'model' => \$ollama_response['model'] ?? (\$input['model'] ?? 'llama3.2'),
            'timestamp' => time(),
            'processing_time_ms' => \$processing_time_ms,
            'tokens_generated' => \$ollama_response['eval_count'] ?? null,
            'tokens_per_second' => \$tokens_per_second,
            'prompt_eval_tokens' => \$prompt_eval_tokens,
            'prompt_eval_duration_ms' => \$prompt_eval_duration,
            'total_duration_ms' => isset(\$ollama_response['total_duration']) ? round(\$ollama_response['total_duration'] / 1000000) : \$processing_time_ms
        ]);

    } catch (\Exception \$e) {
        log_error('Processing error: ' . \$e->getMessage());

        // Report error back to manager
        \$error_report = [
            'error_type' => get_class(\$e),
            'error_message' => \$e->getMessage(),
            'error_time' => date('Y-m-d H:i:s'),
            'job_type' => \$input['type'] ?? 'unknown'
        ];

        // Store error for next heartbeat
        file_put_contents(
            sys_get_temp_dir() . '/qit-node-last-error.json', 
            json_encode(\$error_report)
        );

        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to process request',
            'message' => \$e->getMessage(),
            'error_details' => \$error_report
        ]);
    }
}

// Code Analysis Handler
function handle_code_analysis(\$input) {
    log_error('Starting code analysis handler');

    if (!isset(\$input['zip_url']) || !isset(\$input['file']) || !isset(\$input['line'])) {
        log_error('Missing required parameters');

        // Store this error for heartbeat
        \$error_report = [
            'error_type' => 'ValidationError',
            'error_message' => 'Missing required parameters: zip_url, file, line',
            'error_time' => date('Y-m-d H:i:s'),
            'job_type' => 'code_analysis',
            'request_data' => \$input
        ];

        file_put_contents(
            sys_get_temp_dir() . '/qit-node-last-error.json', 
            json_encode(\$error_report)
        );

        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters: zip_url, file, line']);
        exit;
    }

    try {
        \$session_id = \$input['session_id'] ?? md5(\$input['zip_url']);
        \$cache_dir = sys_get_temp_dir() . '/qit-code-analysis';
        \$work_dir = \$cache_dir . '/' . \$session_id;

        log_error("Session ID: \$session_id");
        log_error("Work dir: \$work_dir");

        // Create cache directory
        if (!is_dir(\$cache_dir)) {
            mkdir(\$cache_dir, 0777, true);
        }

        // Download and extract if not already cached
        if (!is_dir(\$work_dir) || !file_exists(\$work_dir . '/.analyzed')) {
            log_error("Preparing codebase from: " . \$input['zip_url']);
            prepare_codebase(\$input['zip_url'], \$work_dir);
        } else {
            log_error("Using cached codebase");
        }

        // Find symbol at location
        log_error("Finding symbol at " . \$input['file'] . ":" . \$input['line']);
        \$symbol = find_symbol_at_location(\$work_dir, \$input['file'], \$input['line']);

        if (!\$symbol) {
            throw new \Exception("Could not find symbol at {\$input['file']}:{\$input['line']}");
        }

        log_error("Found symbol: \$symbol");

        // Run psalm to find references
        log_error("Finding references for symbol: \$symbol");
        \$references = find_references(\$work_dir, \$symbol);
        log_error("Found " . count(\$references) . " references");

        // Extract relevant file contents
        \$file_contents = extract_file_contents(\$work_dir, \$references);

        // Build execution context
        \$execution_context = build_execution_context(\$work_dir, \$references);

        \$response = [
            'success' => true,
            'context' => [
                'symbol' => \$symbol,
                'references' => \$references,
                'file_contents' => \$file_contents,
                'execution_context' => \$execution_context
            ]
        ];

        log_error("Sending response with " . count(\$references) . " references");
        echo json_encode(\$response);

    } catch (\Exception \$e) {
        log_error('Processing error: ' . \$e->getMessage());
        log_error('Stack trace: ' . \$e->getTraceAsString());

        // Report error back to manager with more details
        \$error_report = [
            'error_type' => get_class(\$e),
            'error_message' => \$e->getMessage(),
            'error_time' => date('Y-m-d H:i:s'),
            'job_type' => 'code_analysis',
            'request_data' => [
                'zip_url' => \$input['zip_url'] ?? 'not provided',
                'file' => \$input['file'] ?? 'not provided',
                'line' => \$input['line'] ?? 'not provided'
            ],
            'work_dir_exists' => isset(\$work_dir) ? is_dir(\$work_dir) : false,
            'symbol_found' => isset(\$symbol) ? \$symbol : 'not found'
        ];

        // Store error for next heartbeat
        file_put_contents(
            sys_get_temp_dir() . '/qit-node-last-error.json', 
            json_encode(\$error_report)
        );

        http_response_code(500);
        echo json_encode([
            'error' => 'Analysis failed',
            'message' => \$e->getMessage(),
            'error_details' => \$error_report
        ]);
    }
}

function prepare_codebase(\$zip_url, \$work_dir) {
    log_error("prepare_codebase: Starting download from \$zip_url");

    if (!is_dir(\$work_dir)) {
        mkdir(\$work_dir, 0777, true);
    }

    // Add validation for the zip URL
    if (!filter_var(\$zip_url, FILTER_VALIDATE_URL)) {
        throw new \Exception('Invalid ZIP URL provided: ' . \$zip_url);
    }

    // Test if URL is accessible
    \$headers = @get_headers(\$zip_url);
    if (\$headers === false) {
        throw new \Exception('Cannot access ZIP URL: ' . \$zip_url);
    }

    \$status_line = \$headers[0];
    if (strpos(\$status_line, '200') === false && strpos(\$status_line, '302') === false) {
        throw new \Exception('ZIP URL returned non-200 status: ' . \$status_line);
    }

    // Check file size before downloading
    \$ch = curl_init(\$zip_url);
    curl_setopt(\$ch, CURLOPT_NOBODY, true);
    curl_setopt(\$ch, CURLOPT_HEADER, true);
    curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(\$ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec(\$ch);
    \$size = curl_getinfo(\$ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    curl_close(\$ch);

    log_error("File size check: \$size bytes, HTTP code: \$http_code");

    // Limit to 500MB
    if (\$size > 500 * 1024 * 1024) {
        throw new \Exception('File too large: ' . \$size . ' bytes');
    }

    // Download zip file
    \$zip_path = \$work_dir . '/plugin.zip';
    \$ch = curl_init(\$zip_url);
    \$fp = fopen(\$zip_path, 'wb');

    curl_setopt(\$ch, CURLOPT_FILE, \$fp);
    curl_setopt(\$ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt(\$ch, CURLOPT_TIMEOUT, 300);

    curl_exec(\$ch);
    \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
    \$error = curl_error(\$ch);

    curl_close(\$ch);
    fclose(\$fp);

    if (\$http_code !== 200) {
        log_error("Download failed: HTTP \$http_code, error: \$error");
        throw new \Exception("Failed to download file: HTTP \$http_code");
    }

    log_error("Download complete, extracting...");

    // Extract using Docker for safety
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

    \$process = proc_open(\$cmd, \$descriptorspec, \$pipes);

    if (is_resource(\$process)) {
        fclose(\$pipes[0]);
        \$stdout = stream_get_contents(\$pipes[1]);
        \$stderr = stream_get_contents(\$pipes[2]);
        fclose(\$pipes[1]);
        fclose(\$pipes[2]);
        \$return_code = proc_close(\$process);

        if (\$return_code !== 0) {
            log_error("Extraction failed: \$stderr");
            throw new \Exception('Failed to extract zip: ' . \$stderr);
        }
    }

    log_error("Extraction complete");

    // Create psalm config if needed
    create_psalm_config(\$work_dir);

    // Mark as analyzed
    touch(\$work_dir . '/.analyzed');
}

function find_symbol_at_location(\$work_dir, \$file, \$line) {
    // Sanitize the file path
    \$file = str_replace(['../', '..\\\\', "\\0"], '', \$file);
    \$file = ltrim(\$file, '/\\\\');

    \$filepath = realpath(\$work_dir . '/' . \$file);

    log_error("find_symbol_at_location: Looking for \$file at line \$line");
    log_error("Resolved path: \$filepath");

    // Ensure the resolved path is within work_dir
    if (\$filepath === false || strpos(\$filepath, realpath(\$work_dir)) !== 0) {
        log_error("Path validation failed");
        return null;
    }

    if (!file_exists(\$filepath)) {
        log_error("File does not exist: \$filepath");
        return null;
    }

    \$content = file_get_contents(\$filepath);
    \$tokens = token_get_all(\$content);

    \$namespace = '';
    \$class = '';
    \$current_line = 1;

    // Track current context
    for (\$i = 0; \$i < count(\$tokens); \$i++) {
        if (is_array(\$tokens[\$i])) {
            \$current_line = \$tokens[\$i][2] ?? \$current_line;

            // Found namespace
            if (\$tokens[\$i][0] === T_NAMESPACE) {
                \$namespace = '';
                for (\$j = \$i + 1; \$j < count(\$tokens); \$j++) {
                    if (\$tokens[\$j] === ';') break;
                    if (is_array(\$tokens[\$j]) && \$tokens[\$j][0] === T_STRING) {
                        \$namespace .= \$tokens[\$j][1];
                    } elseif (is_array(\$tokens[\$j]) && \$tokens[\$j][0] === T_NS_SEPARATOR) {
                        \$namespace .= '\\\\';
                    }
                }
            }

            // Found class
            if (\$tokens[\$i][0] === T_CLASS) {
                for (\$j = \$i + 1; \$j < count(\$tokens); \$j++) {
                    if (is_array(\$tokens[\$j]) && \$tokens[\$j][0] === T_STRING) {
                        \$class = \$tokens[\$j][1];
                        break;
                    }
                }
            }

            // Found function
            if (\$tokens[\$i][0] === T_FUNCTION) {
                \$function = '';
                for (\$j = \$i + 1; \$j < count(\$tokens); \$j++) {
                    if (is_array(\$tokens[\$j]) && \$tokens[\$j][0] === T_STRING) {
                        \$function = \$tokens[\$j][1];
                        break;
                    }
                }

                // Find function boundaries
                \$func_start = \$current_line;
                \$brace_count = 0;
                \$in_function = false;

                for (\$j = \$i; \$j < count(\$tokens); \$j++) {
                    if (\$tokens[\$j] === '{') {
                        \$in_function = true;
                        \$brace_count++;
                    } elseif (\$tokens[\$j] === '}' && \$in_function) {
                        \$brace_count--;
                        if (\$brace_count === 0) {
                            \$func_end = is_array(\$tokens[\$j]) ? \$tokens[\$j][2] : \$current_line;

                            // Check if target line is within this function
                            if (\$line >= \$func_start && \$line <= \$func_end) {
                                if (\$class) {
                                    \$symbol = ltrim(\$namespace . '\\\\' . \$class . '::' . \$function, '\\\\');
                                } else {
                                    \$symbol = ltrim(\$namespace . '\\\\' . \$function, '\\\\');
                                }
                                log_error("Found symbol: \$symbol");
                                return \$symbol;
                            }
                            break;
                        }
                    }
                }
            }
        }
    }

    log_error("No symbol found at line \$line");
    return null;
}

function find_references(\$work_dir, \$symbol) {
    \$descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];

    \$cmd = [
        'docker', 'run', '--rm',
        '-v', \$work_dir . ':/app',
        '-w', '/app',
        'ghcr.io/vimeo/psalm:latest',
        '--find-references-to=' . \$symbol,
        '--no-cache'
    ];

    log_error("Running psalm command: " . implode(' ', \$cmd));

    \$process = proc_open(\$cmd, \$descriptorspec, \$pipes);
    \$output = [];
    \$return_code = 1;

    if (is_resource(\$process)) {
        fclose(\$pipes[0]);
        \$stdout = stream_get_contents(\$pipes[1]);
        \$stderr = stream_get_contents(\$pipes[2]);
        fclose(\$pipes[1]);
        fclose(\$pipes[2]);
        \$return_code = proc_close(\$process);

        log_error("Psalm stdout: " . \$stdout);
        log_error("Psalm stderr: " . \$stderr);

        if (\$stdout) {
            \$output = explode("\\n", \$stdout);
        }
    }

    // Parse psalm output
    \$references = [];
    foreach (\$output as \$line) {
        if (preg_match('/^(.+?):(\d+):(\d+)\s+-\s+(.+)$/', \$line, \$matches)) {
            \$references[] = [
                'file' => \$matches[1],
                'line' => (int)\$matches[2],
                'column' => (int)\$matches[3],
                'type' => trim(\$matches[4])
            ];
        }
    }

    log_error("Found " . count(\$references) . " references");
    return \$references;
}

function extract_file_contents(\$work_dir, \$references) {
    \$contents = [];
    \$processed = [];

    foreach (\$references as \$ref) {
        if (in_array(\$ref['file'], \$processed)) continue;

        \$filepath = \$work_dir . '/' . \$ref['file'];
        if (file_exists(\$filepath)) {
            \$lines = file(\$filepath, FILE_IGNORE_NEW_LINES);
            \$start = max(0, \$ref['line'] - 10);
            \$end = min(count(\$lines), \$ref['line'] + 10);

            \$contents[\$ref['file']] = [
                'excerpt' => array_slice(\$lines, \$start, \$end - \$start),
                'start_line' => \$start + 1,
                'highlight_line' => \$ref['line']
            ];

            \$processed[] = \$ref['file'];
        }
    }

    return \$contents;
}

function build_execution_context(\$work_dir, \$references) {
    \$context = [
        'has_public_access' => false,
        'wordpress_hooks' => [],
        'call_chain' => []
    ];

    foreach (\$references as \$ref) {
        // Build call chain first
        \$call_info = \$ref['file'] . ':' . \$ref['line'];
        if (!empty(\$ref['type'])) {
            \$call_info .= ' (' . \$ref['type'] . ')';
        }
        \$context['call_chain'][] = \$call_info;

        // Now analyze the file for WordPress patterns
        \$file_path = \$work_dir . '/' . \$ref['file'];
        if (file_exists(\$file_path)) {
            \$file_content = file_get_contents(\$file_path);

            // Check for WordPress hooks in the file
            if (preg_match_all('/add_action\s*\\(\s*[\'"]([^\'"]+)[\'"]/', \$file_content, \$matches)) {
                foreach (\$matches[1] as \$hook) {
                    if (strpos(\$hook, 'wp_ajax_nopriv_') === 0 || 
                        in_array(\$hook, ['init', 'rest_api_init', 'wp_loaded', 'template_redirect'])) {
                        \$context['has_public_access'] = true;
                    }
                    \$context['wordpress_hooks'][] = [
                        'type' => 'action',
                        'hook' => \$hook,
                        'location' => \$ref['file'] . ':' . \$ref['line']
                    ];
                }
            }

            if (preg_match_all('/add_filter\s*\\(\s*[\'"]([^\'"]+)[\'"]/', \$file_content, \$matches)) {
                foreach (\$matches[1] as \$hook) {
                    \$context['wordpress_hooks'][] = [
                        'type' => 'filter',
                        'hook' => \$hook,
                        'location' => \$ref['file'] . ':' . \$ref['line']
                    ];
                }
            }
        }
    }

    return \$context;
}

function create_psalm_config(\$work_dir) {
    \$psalm_xml = \$work_dir . '/psalm.xml';
    if (!file_exists(\$psalm_xml)) {
        file_put_contents(\$psalm_xml, '<?xml version="1.0"?>
<psalm errorLevel="8" resolveFromConfigFile="true">
    <projectFiles>
        <directory name="." />
        <ignoreFiles>
            <directory name="vendor" />
            <directory name="node_modules" />
        </ignoreFiles>
    </projectFiles>
</psalm>');
    }
}

// Cleanup old sessions periodically (1% chance)
if (mt_rand(1, 100) === 1) {
    cleanup_old_sessions();
}

function cleanup_old_sessions() {
    \$cache_dir = sys_get_temp_dir() . '/qit-code-analysis';
    if (!is_dir(\$cache_dir)) return;

    \$now = time();
    foreach (scandir(\$cache_dir) as \$dir) {
        if (\$dir === '.' || \$dir === '..') continue;

        \$session_dir = \$cache_dir . '/' . \$dir;
        \$real_path = realpath(\$session_dir);

        // Verify it's really inside cache_dir
        if (\$real_path === false || strpos(\$real_path, realpath(\$cache_dir)) !== 0) {
            continue;
        }

        if (is_dir(\$real_path)) {
            \$mtime = filemtime(\$real_path);
            if (\$now - \$mtime > 3600) { // 1 hour old
                // Use PHP's recursive directory removal instead of exec
                remove_directory_safely(\$real_path);
            }
        }
    }
}

function remove_directory_safely(\$dir) {
    if (!is_dir(\$dir)) return;

    \$files = array_diff(scandir(\$dir), ['.', '..']);
    foreach (\$files as \$file) {
        \$path = \$dir . '/' . \$file;
        is_dir(\$path) ? remove_directory_safely(\$path) : unlink(\$path);
    }
    rmdir(\$dir);
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
