<?php

/**
 * Logical Security Analysis Handler
 * 
 * This file contains functions specifically for logical security analysis,
 * including vulnerability discovery and file analysis for security issues.
 */

use QIT_CLI\AI\WebServer\ToolRegistry;
use QIT_CLI\AI\WebServer\ExtractPathResolver;

/**
 * Handle logical security discovery - explore codebase and return vulnerability findings
 */
function handle_logical_security_discovery( $input, $ollama_api_url, $job_id ) {
	log_info( "Starting logical security discovery", [
		'job_id'     => $job_id,
		'session_id' => $input['session_id'] ?? 'unknown'
	] );

	// Set up the discovery
	$coder_model    = 'qwen2.5-coder:7b';
	$tools_model    = 'devstral:24b';
	$max_iterations = $input['config']['max_iterations'] ?? 30;

	// Use centralized path resolution for discovery
	$session_id = $input['session_id'] ?? null;

	try {
		$work_dir = ExtractPathResolver::resolve($input);
		log_info("Work directory resolved for discovery", ['work_dir' => $work_dir]);
	} catch (Exception $e) {
		log_error("Path resolution failed for discovery", [
			'error' => $e->getMessage(),
			'session_id' => $session_id,
			'diagnostics' => ExtractPathResolver::getDiagnosticMessage($input)
		]);
		http_response_code(400);
		echo json_encode([
			'error' => $e->getMessage(),
			'help' => 'Ensure extract_path is provided from zip extraction step',
			'diagnostics' => ExtractPathResolver::getDiagnosticMessage($input)
		]);
		return;
	}

	// Initialize tool registry
	$tool_registry = new ToolRegistry( $work_dir );

	// Define vulnerability patterns to search for
	$search_patterns = [
		'ajax_handlers'       => [
			'pattern'     => 'add_action\s*\(\s*[\'"]wp_ajax_',
			'description' => 'AJAX handlers that could be entry points'
		],
		'nopriv_ajax'         => [
			'pattern'     => 'add_action\s*\(\s*[\'"]wp_ajax_nopriv_',
			'description' => 'Public AJAX handlers without authentication'
		],
		'direct_input'        => [
			'pattern'     => '\$_(GET|POST|REQUEST)\s*\[',
			'description' => 'Direct user input access'
		],
		'sql_queries'         => [
			'pattern'     => '\$wpdb->(query|prepare|get_results|get_var)',
			'description' => 'Database queries that might have SQL injection'
		],
		'file_operations'     => [
			'pattern'     => '(include|require|fopen|file_get_contents|file_put_contents)\s*\(',
			'description' => 'File operations that might have path traversal'
		],
		'dangerous_functions' => [
			'pattern'     => '(eval|exec|system|shell_exec|passthru)\s*\(',
			'description' => 'Dangerous functions that could lead to RCE'
		],
	];

	// Build system prompt for coder
	$system_prompt = build_discovery_system_prompt( $work_dir, $search_patterns );

	// Get initial directory listing to provide context
	$initial_listing = $tool_registry->execute_tool( 'list_files', [ 'directory' => '.' ] );

	// Start conversation with coder model
	$coder_conversation = [
		[
			'role'    => 'system',
			'content' => $system_prompt
		],
		[
			'role'    => 'system',
			'content' => "DIRECTORY CONTEXT:\n" . json_encode( $initial_listing, JSON_PRETTY_PRINT ) .
			             "\nThis is the complete file structure you have access to. Work within these files only."
		],
		[
			'role'    => 'user',
			'content' => "Analyze this WordPress plugin for security vulnerabilities. Start by creating a search plan. " .
			             "What patterns do you want to search for first? Respond with JSON: " .
			             '{"search_plan": [{"pattern": "...", "reason": "..."}], "reasoning": "..."}'
		]
	];

	// Discovery loop
	$discovered_vulnerabilities = [];
	$orchestration_iterations   = 0;
	$all_tool_results           = [];

	while ( $orchestration_iterations < 5 ) { // Limit discovery loops
		$orchestration_iterations ++;

		// Ask coder what to search for
		$coder_request = [
			'model'    => $coder_model,
			'messages' => $coder_conversation,
			'stream'   => false,
			'format'   => 'json'
		];

		$coder_response = call_ollama( $ollama_api_url . '/api/chat', $coder_request );
		$search_plan    = json_decode( $coder_response['message']['content'] ?? '{}', true );

		if ( empty( $search_plan['search_plan'] ) ) {
			break; // No more searches needed
		}

		// Execute searches using tools model
		$search_results = [];
		foreach ( $search_plan['search_plan'] as $search ) {
			// Use tools model to execute search
			$tool_messages = [
				[
					'role'    => 'system',
					'content' => "Execute the requested search operation."
				],
				[
					'role'    => 'user',
					'content' => "Search for pattern: {$search['pattern']}"
				]
			];

			// Execute search (simplified - you'd use the actual tools execution)
			$result = $tool_registry->execute_tool( 'search_pattern', [
				'pattern'     => $search['pattern'],
				'max_results' => 50
			] );

			$search_results[]   = [
				'pattern' => $search['pattern'],
				'results' => $result
			];
			$all_tool_results[] = $result;
		}

		// Feed results back to coder
		$coder_conversation[] = [
			'role'    => 'assistant',
			'content' => $coder_response['message']['content']
		];

		$coder_conversation[] = [
			'role'    => 'user',
			'content' => "Search results:\n" . json_encode( $search_results, JSON_PRETTY_PRINT ) . "\n\n" .
			             "Analyze these results. Are there any security vulnerabilities? " .
			             "For each vulnerability found, provide: file, line, type, severity, and description. " .
			             "Also indicate if you need more searches. Respond with JSON."
		];
	}

	// Get final analysis from coder
	$final_prompt = "Based on all your searches, provide a final list of discovered vulnerabilities. " .
	                "For each vulnerability include: type, severity (critical/high/medium/low), file, line, " .
	                "description, and potential impact. Respond with JSON: " .
	                '{"vulnerabilities": [...], "summary": "..."}';

	$coder_conversation[] = [
		'role'    => 'user',
		'content' => $final_prompt
	];

	$final_request = [
		'model'    => $coder_model,
		'messages' => $coder_conversation,
		'stream'   => false,
		'format'   => 'json'
	];

	$final_response = call_ollama( $ollama_api_url . '/api/chat', $final_request );
	$final_analysis = json_decode( $final_response['message']['content'] ?? '{}', true );

	// Log discovery completion - Manager will create tasks from the returned data
	log_info( "Discovery completed, returning vulnerability data to Manager", [
		'vulnerabilities_found' => count( $final_analysis['vulnerabilities'] ?? [] ),
		'job_id'                => $job_id
	] );

	// Return discovery summary with vulnerability data for Manager to process
	$response_data = [
		'response'   => json_encode( [
			'vulnerabilities'       => $final_analysis['vulnerabilities'] ?? [],
			'vulnerabilities_found' => count( $final_analysis['vulnerabilities'] ?? [] ),
			'summary'               => $final_analysis['summary'] ?? 'Discovery completed',
			'high_risk_count'       => count( array_filter(
				$final_analysis['vulnerabilities'] ?? [],
				fn( $v ) => in_array( $v['severity'] ?? '', [ 'critical', 'high' ] )
			) ),
			'analysis_metadata'     => [
				'total_files_analyzed' => count( $all_tool_results ),
				'discovery_iterations' => $orchestration_iterations,
				'patterns_searched'    => array_keys( $search_patterns )
			]
		] ),
		'model'      => $coder_model,
		'iterations' => $orchestration_iterations,
		'tool_calls' => $all_tool_results,
		'timestamp'  => time()
	];

	echo json_encode( $response_data );
}

/**
 * Build system prompt for discovery
 */
function build_discovery_system_prompt( $work_dir, $patterns ) {
	$prompt = "You are a security researcher analyzing a WordPress plugin ONLY within: $work_dir\n\n";

	$prompt .= "CRITICAL RESTRICTIONS:\n";
	$prompt .= "- You can ONLY access files within the base directory: $work_dir\n";
	$prompt .= "- Do NOT attempt to access system files, parent directories, or other locations\n";
	$prompt .= "- All file paths must be relative to the base directory\n";
	$prompt .= "- This is an extracted plugin/theme - NOT a full WordPress installation\n";
	$prompt .= "- Do NOT assume WordPress paths like wp-content/plugins/ - use actual file structure\n\n";

	// Add actual directory structure
	$structure = get_logical_directory_structure( $work_dir );
	$prompt    .= "ACTUAL DIRECTORY STRUCTURE:\n";
	$prompt    .= $structure . "\n";
	$prompt    .= "IMPORTANT: This is the complete directory structure. Work within these files only.\n\n";

	$prompt .= "Your goal is to discover security vulnerabilities by searching for dangerous patterns.\n\n";

	$prompt .= "Available patterns to search:\n";
	foreach ( $patterns as $key => $pattern ) {
		$prompt .= "- {$key}: {$pattern['description']}\n";
	}

	$prompt .= "\nFocus on finding:\n";
	$prompt .= "- SQL injection vulnerabilities\n";
	$prompt .= "- Cross-site scripting (XSS)\n";
	$prompt .= "- Remote code execution\n";
	$prompt .= "- Path traversal\n";
	$prompt .= "- Authentication bypass\n";
	$prompt .= "- CSRF vulnerabilities\n\n";

	$prompt .= "For each potential vulnerability, verify it's exploitable by checking:\n";
	$prompt .= "- Is user input involved?\n";
	$prompt .= "- Is the input properly sanitized?\n";
	$prompt .= "- Can it be reached from a public entry point?\n\n";

	$prompt .= "REMEMBER: Only analyze files from the directory structure shown above.\n";
	$prompt .= "All file paths in your analysis must be relative to: $work_dir\n";

	return $prompt;
}

/**
 * Updated handle_file_analysis function
 */
function handle_file_analysis( $input, $ollama_api_url, $job_id ) {
	log_info( "Starting file analysis for logical security", [
		'job_id'           => $job_id,
		'file_path'        => $input['file_path'] ?? 'unknown',
		'has_file_content' => isset( $input['file_content'] )
	] );

	// Check if we have file content
	if ( ! isset( $input['file_content'] ) ) {
		log_error( "No file content provided for analysis" );
		NodeResponse::error( 'No file content provided', 400 );

		return;
	}

	$coder_model = 'qwen2.5-coder:7b';

	// If this is initial vulnerability scan
	if ( $input['config']['analysis_mode'] === 'vulnerability_discovery' ) {
		// Simple analysis - no tools needed
		$request = [
			'model'  => $coder_model,
			'prompt' => $input['prompt'], // Prompt is built by AIStepProcessor
			'stream' => false,
			'format' => 'json'
		];

		try {
			$response = call_ollama( $ollama_api_url . '/api/generate', $request );

			$analysis = json_decode( $response['response'] ?? '{}', true );

			NodeResponse::success( [
				'potential_vulnerabilities' => $analysis['potential_vulnerabilities'] ?? [],
				'summary'                   => $analysis['summary'] ?? '',
				'file_analyzed'             => $input['file_path'] ?? null
			], [
				'model' => $coder_model
			] );

		} catch ( Exception $e ) {
			log_error( "Initial vulnerability scan failed", [ 'error' => $e->getMessage() ] );
			NodeResponse::error( 'Analysis failed: ' . $e->getMessage(), 500 );
		}

		return;
	}

	// If this is deep investigation with tools
	if ( $input['config']['analysis_mode'] === 'vulnerability_investigation' ) {
		// Get potential vulnerabilities from dependencies
		$potential_vulns = $input['dependencies']['initial_vulnerability_scan']['potential_vulnerabilities'] ?? [];

		if ( empty( $potential_vulns ) ) {
			NodeResponse::success( [
				'vulnerabilities' => [],
				'summary'         => 'No vulnerabilities to investigate'
			], [
				'model' => $coder_model
			] );

			return;
		}

		// Now use tools to investigate each potential vulnerability
		// ... (existing tool-based investigation logic) ...
	}
}

/**
 * Build system prompt for file analysis
 */
function build_file_analysis_system_prompt( $work_dir, $file_path ) {
	$prompt = "You are a WordPress security expert analyzing a specific PHP file for vulnerabilities.\n\n";

	$prompt .= "CONTEXT:\n";
	$prompt .= "- Base directory: $work_dir\n";
	$prompt .= "- Analyzing file: $file_path\n";
	$prompt .= "- This is part of a WordPress plugin/theme\n\n";

	$prompt .= "SECURITY VULNERABILITIES TO LOOK FOR:\n";
	$prompt .= "1. **SQL Injection**: Direct use of user input in database queries\n";
	$prompt .= "2. **XSS**: Unescaped output of user-controlled data\n";
	$prompt .= "3. **CSRF**: Missing nonce verification on state-changing operations\n";
	$prompt .= "4. **File Upload/Inclusion**: Unsafe file operations\n";
	$prompt .= "5. **Authentication Bypass**: Missing or weak permission checks\n";
	$prompt .= "6. **Remote Code Execution**: eval(), system(), or similar with user input\n";
	$prompt .= "7. **Path Traversal**: File access with user-controlled paths\n";
	$prompt .= "8. **Insecure Deserialization**: unserialize() with user data\n";
	$prompt .= "9. **Information Disclosure**: Exposure of sensitive data\n";
	$prompt .= "10. **Race Conditions**: Time-of-check to time-of-use issues\n\n";

	$prompt .= "ANALYSIS APPROACH:\n";
	$prompt .= "1. First scan for obvious entry points (form handlers, AJAX, hooks)\n";
	$prompt .= "2. Trace user input through the code\n";
	$prompt .= "3. Check for security measures (sanitization, escaping, validation)\n";
	$prompt .= "4. Verify authentication and authorization checks\n";
	$prompt .= "5. Look for dangerous function calls\n\n";

	$prompt .= "IMPORTANT:\n";
	$prompt .= "- Focus on ACTUAL exploitable vulnerabilities, not theoretical issues\n";
	$prompt .= "- Consider WordPress security functions (wp_verify_nonce, esc_html, etc.)\n";
	$prompt .= "- Check if vulnerabilities can be reached from public context\n";
	$prompt .= "- Provide specific line numbers and code snippets\n";
	$prompt .= "- Rate confidence (0-100) based on exploitability\n";

	return $prompt;
}

/**
 * Get directory structure (utility function used by logical security analysis)
 */
function get_logical_directory_structure( $work_dir, $max_depth = 3 ) {
	$structure = '';
	$iterator  = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $work_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::SELF_FIRST
	);
	$iterator->setMaxDepth( $max_depth );

	foreach ( $iterator as $file ) {
		$depth      = $iterator->getDepth();
		$indent     = str_repeat( '  ', $depth );
		$structure .= $indent . basename( $file ) . ( $file->isDir() ? '/' : '' ) . "\n";
	}

	return $structure;
}

/**
 * Search for pattern within a specific file
 *
 * @param string $file_path Relative path to the file (will be resolved using FilePathResolver)
 * @param string $pattern Pattern to search for
 * @param string $work_dir Base directory for path resolution
 *
 * @return array Array of matches with line numbers and context
 */
function search_in_file( $file_path, $pattern, $work_dir = '' ) {
	// Use FilePathResolver for consistent path handling
	require_once __DIR__ . '/../lib/FilePathResolver.php';
	$resolver = new QIT_CLI\AI\WebServer\FilePathResolver( $work_dir );

	try {
		$content = $resolver->readFile( $file_path );
		$lines   = explode( "\n", $content );
		$matches = [];

		foreach ( $lines as $line_num => $line ) {
			if ( preg_match( '/' . preg_quote( $pattern, '/' ) . '/i', $line ) ) {
				$matches[] = [
					'line'    => $line_num + 1,
					'content' => trim( $line ),
					'context' => array_slice( $lines, max( 0, $line_num - 1 ), 3 )
				];
			}
		}

		return $matches;
	} catch ( Exception $e ) {
		return [
			'error'   => 'File not found: ' . $file_path,
			'message' => $e->getMessage()
		];
	}
}
