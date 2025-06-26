<?php
/**
 * AI Processing with Tools Handler
 */

require_once __DIR__ . '/../NodeResponse.php';
require_once __DIR__ . '/../lib/ExtractPathResolver.php';

use QIT_CLI\AI\WebServer\ToolRegistry;
use QIT_CLI\AI\WebServer\ExtractPathResolver;

function handle_ai_with_tools( $input, $ollama_api_url ) {
	// Check if this is a logical security discovery task
	if ( isset( $input['config']['analysis_mode'] ) &&
	     $input['config']['analysis_mode'] === 'logical_security_discovery' ) {

		log_info( "Detected logical security discovery mode" );

		// Extract job_id from input
		$job_id = $input['job_id'] ?? null;

		if ( ! $job_id ) {
			log_error( "Missing job_id for discovery mode" );
			http_response_code( 400 );
			echo json_encode( [ 'error' => 'Missing required job_id parameter for discovery mode' ] );

			return;
		}

		// Call the discovery handler
		return handle_logical_security_discovery( $input, $ollama_api_url, $job_id );
	}

	// Check if this is a file analysis task for logical security
	if ( isset( $input['task_phase'] ) && $input['task_phase'] === 'file_analysis' ) {
		log_info( "Detected file analysis mode for logical security" );

		// Extract job_id from input
		$job_id = $input['job_id'] ?? null;

		if ( ! $job_id ) {
			log_error( "Missing job_id for file analysis mode" );
			http_response_code( 400 );
			echo json_encode( [ 'error' => 'Missing required job_id parameter for file analysis mode' ] );

			return;
		}

		// Call the file analysis handler
		return handle_file_analysis( $input, $ollama_api_url, $job_id );
	}

	// Model configuration
	$coder_model = $input['coder_model'] ?? 'qwen2.5-coder:7b';
	$tools_model = $input['tools_model'] ?? 'devstral:24b';

	$messages        = $input['messages'];
	$available_tools = $input['tools'] ?? [];
	$max_iterations  = $input['max_iterations'] ?? 10;
	$session_id      = $input['session_id'] ?? null;
	$zip_url         = $input['zip_url'] ?? null;
	$static_context  = $input['static_context'] ?? null;

	// Extract wp-call-graph results from context and dependencies
	$wp_call_graph_data = null;
	if ( isset( $input['context']['wp_call_graph'] ) ) {
		$wp_call_graph_data = $input['context']['wp_call_graph'];
		log_info( "wp-call-graph data extracted from context" );
	} elseif ( isset( $input['dependencies']['wp_call_graph'] ) ) {
		$wp_call_graph_data = $input['dependencies']['wp_call_graph'];
		log_info( "wp-call-graph data extracted from dependencies" );
	} elseif ( isset( $static_context['wp_call_graph'] ) ) {
		$wp_call_graph_data = $static_context['wp_call_graph'];
		log_info( "wp-call-graph data extracted from static_context" );
	}

	// Log wp-call-graph extraction results
	if ( $wp_call_graph_data ) {
		log_info( "wp-call-graph data successfully extracted", [
			'data_type'    => gettype( $wp_call_graph_data ),
			'has_result'   => isset( $wp_call_graph_data['result'] ),
			'traces_count' => isset( $wp_call_graph_data['result']['trace'] ) ? count( $wp_call_graph_data['result']['trace'] ) : 0
		] );
	} else {
		log_info( "No wp-call-graph data found in input" );
	}

	log_info( "Starting orchestrated AI analysis", [
		'coder_model'        => $coder_model,
		'tools_model'        => $tools_model,
		'tools_count'        => count( $available_tools ),
		'max_iterations'     => $max_iterations,
		'session_id'         => $session_id,
		'has_static_context' => ! empty( $static_context ),
		'has_wp_call_graph'  => ! empty( $wp_call_graph_data ),
		'input_keys'         => array_keys( $input )
	] );

	// Single-line path resolution using centralized resolver
	try {
		$work_dir = ExtractPathResolver::resolve($input);
		log_info("Work directory resolved", ['work_dir' => $work_dir]);
	} catch (Exception $e) {
		log_error("Path resolution failed", [
			'error' => $e->getMessage(),
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

	// Double-check the directory exists and has files
	if ( is_dir( $work_dir ) ) {
		$files     = scandir( $work_dir );
		$php_files = array_filter( $files, function ( $file ) {
			return pathinfo( $file, PATHINFO_EXTENSION ) === 'php';
		} );

		log_info( "Work directory contents", [
			'work_dir'     => $work_dir,
			'total_files'  => count( $files ) - 2, // Exclude . and ..
			'php_files'    => count( $php_files ),
			'sample_files' => array_slice( $files, 2, 5 ) // Show first few files
		] );
	} else {
		log_error( "Work directory does not exist", [ 'work_dir' => $work_dir ] );
		http_response_code( 400 );
		echo json_encode( [
			'error'    => 'Work directory does not exist',
			'work_dir' => $work_dir
		] );

		return;
	}

	log_info( "Initializing tool registry", [
		'work_dir' => $work_dir,
		'exists'   => is_dir( $work_dir ),
		'readable' => is_readable( $work_dir )
	] );

	// Now we can safely create the ToolRegistry with a valid work directory
	try {
		$tool_registry = new ToolRegistry( $work_dir );
	} catch ( Exception $e ) {
		log_error( "Failed to initialize ToolRegistry", [
			'error'    => $e->getMessage(),
			'work_dir' => $work_dir
		] );

		http_response_code( 500 );
		echo json_encode( [
			'error' => 'Failed to initialize tool registry: ' . $e->getMessage()
		] );

		return;
	}

	// Convert tools to Ollama format
	$ollama_tools = array_map( function ( $tool ) {
		return [
			'type'     => 'function',
			'function' => $tool
		];
	}, $available_tools );

	// Build initial context for qwen coder
	$coder_system_prompt = build_coder_investigation_prompt( $work_dir, $static_context, $wp_call_graph_data );

	// Extract security issue details from messages
	$security_context = '';
	foreach ( $messages as $msg ) {
		if ( isset( $msg['content'] ) && strpos( $msg['content'], 'SECURITY ISSUE' ) !== false ) {
			$security_context = $msg['content'];
			break;
		}
	}

	$coder_conversation = [
		[
			'role'    => 'system',
			'content' => $coder_system_prompt
		],
		[
			'role'    => 'user',
			'content' => $security_context . "\n\nAnalyze this security issue and tell me what specific information you need to investigate. " .
			             "Respond with a JSON object containing an 'investigations' array of specific tool calls you want me to make."
		]
	];

	$all_tool_results         = [];
	$investigation_complete   = false;
	$orchestration_iterations = 0;
	$max_orchestration_loops  = 5; // Prevent infinite loops

	while ( ! $investigation_complete && $orchestration_iterations < $max_orchestration_loops ) {
		$orchestration_iterations ++;

		log_info( "Orchestration iteration $orchestration_iterations" );

		// Step 1: Ask qwen coder what to investigate
		$coder_request = [
			'model'    => $coder_model,
			'messages' => $coder_conversation,
			'stream'   => false,
			'format'   => 'json' // We want structured investigation requests
		];

		try {
			$coder_response = call_ollama( $ollama_api_url . '/api/chat', $coder_request );
		} catch ( Exception $e ) {
			log_error( "Coder model failed", [ 'error' => $e->getMessage() ] );
			break;
		}

		// Parse what qwen wants to investigate
		$investigation_plan = json_decode( $coder_response['message']['content'] ?? '{}', true );

		log_info( "Coder investigation plan", [
			'has_investigations'  => isset( $investigation_plan['investigations'] ),
			'investigation_count' => count( $investigation_plan['investigations'] ?? [] ),
			'is_complete'         => $investigation_plan['complete'] ?? false
		] );

		// Check if qwen thinks it has enough information
		if ( empty( $investigation_plan['investigations'] ) || ( $investigation_plan['complete'] ?? false ) ) {
			$investigation_complete = true;
			break;
		}

		// Step 2: Execute investigations using devstral
		$tool_results = [];

		foreach ( $investigation_plan['investigations'] as $investigation ) {
			log_info( "Executing investigation", [
				'tool' => $investigation['tool'] ?? 'unknown',
				'args' => $investigation['args'] ?? []
			] );

			// Build a focused prompt for devstral
			$devstral_messages = [
				[
					'role'    => 'system',
					'content' => "You are a code analysis assistant. Execute the requested tool and return the results."
				],
				[
					'role'    => 'user',
					'content' => sprintf(
						"Use the %s tool with these arguments: %s",
						$investigation['tool'],
						json_encode( $investigation['args'] )
					)
				]
			];

			// Call devstral with tools
			$devstral_request = [
				'model'    => $tools_model,
				'messages' => $devstral_messages,
				'tools'    => $ollama_tools,
				'stream'   => false
			];

			try {
				$devstral_response = call_ollama( $ollama_api_url . '/api/chat', $devstral_request );

				// Execute the tool calls devstral wants to make
				if ( isset( $devstral_response['message']['tool_calls'] ) ) {
					foreach ( $devstral_response['message']['tool_calls'] as $tool_call ) {
						$function_name = $tool_call['function']['name'];
						$arguments     = is_string( $tool_call['function']['arguments'] )
							? json_decode( $tool_call['function']['arguments'], true ) ?? []
							: $tool_call['function']['arguments'] ?? [];

						$result = $tool_registry->execute_tool( $function_name, $arguments );

						$tool_results[] = [
							'tool'         => $function_name,
							'args'         => $arguments,
							'result'       => $result,
							'requested_by' => 'coder_investigation'
						];

						$all_tool_results[] = $tool_results[ count( $tool_results ) - 1 ];
					}
				}
			} catch ( Exception $e ) {
				log_error( "Devstral execution failed", [ 'error' => $e->getMessage() ] );
				$tool_results[] = [
					'tool'  => $investigation['tool'],
					'error' => $e->getMessage()
				];
			}
		}

		// Step 3: Feed results back to qwen coder
		$results_summary = "Here are the investigation results:\n\n";
		foreach ( $tool_results as $result ) {
			$results_summary .= sprintf(
				"Tool: %s\nArgs: %s\nResult: %s\n\n",
				$result['tool'],
				json_encode( $result['args'] ?? [] ),
				json_encode( $result['result'] ?? $result['error'] ?? 'No result' )
			);
		}

		$coder_conversation[] = [
			'role'    => 'assistant',
			'content' => $coder_response['message']['content']
		];

		$coder_conversation[] = [
			'role'    => 'user',
			'content' => $results_summary . "\n\nBased on these results, what else do you need to investigate? " .
			             "Or are you ready to provide your final analysis? " .
			             "Respond with JSON: {\"investigations\": [...], \"complete\": true/false, \"analysis\": \"...\" }"
		];
	}

	// Step 4: Get final analysis from qwen
	log_info( "Getting final analysis from coder model" );

	$final_prompt = "Based on all the investigations, provide your final security analysis in JSON format with these fields:\n" .
	                "- vulnerable_code: description of the vulnerability\n" .
	                "- entry_points: array of entry points found\n" .
	                "- exploitable: boolean\n" .
	                "- has_public_access: boolean\n" .
	                "- susceptible_to_privilege_escalation: boolean\n" .
	                "- summary: your conclusion\n" .
	                "- confidence: your confidence level (0-100)";

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

	try {
		$final_response = call_ollama( $ollama_api_url . '/api/chat', $final_request );
		$final_analysis = $final_response['message']['content'] ?? '{}';
	} catch ( Exception $e ) {
		log_error( "Final analysis failed", [ 'error' => $e->getMessage() ] );
		$final_analysis = json_encode( [
			'error'           => 'Analysis failed: ' . $e->getMessage(),
			'partial_results' => $all_tool_results
		] );
	}

	// Return the complete analysis
	$response_data = [
		'response'              => $final_analysis,
		'model'                 => $coder_model,
		'iterations'            => $orchestration_iterations,
		'tool_calls'            => $all_tool_results,
		'wp_call_graph_context' => $static_context,
		'orchestration'         => [
			'coder_model' => $coder_model,
			'tools_model' => $tools_model,
			'loops'       => $orchestration_iterations
		],
		'timestamp'             => time()
	];

	echo json_encode( $response_data );
}

function get_directory_structure( $work_dir, $max_depth = 2 ) {
	if ( ! is_dir( $work_dir ) ) {
		return "Directory not accessible: $work_dir\n";
	}

	$cmd = sprintf( 'find %s -maxdepth %d -type f -name "*.php" | head -20',
		escapeshellarg( $work_dir ),
		$max_depth
	);

	exec( $cmd, $output );

	$structure = "Files found:\n";
	foreach ( $output as $file ) {
		$relative_path = str_replace( $work_dir . '/', '', $file );
		$structure     .= "- $relative_path\n";
	}

	return $structure;
}

function build_coder_investigation_prompt( $work_dir, $static_context, $wp_call_graph_data ) {
	$prompt = "You are a WordPress security expert analyzing code ONLY within: $work_dir\n\n";

	$prompt .= "CRITICAL RESTRICTIONS:\n";
	$prompt .= "- You can ONLY access files within the base directory: $work_dir\n";
	$prompt .= "- Do NOT attempt to access system files, parent directories, or other locations\n";
	$prompt .= "- All file paths must be relative to the base directory\n";
	$prompt .= "- This is an extracted plugin/theme - NOT a full WordPress installation\n";
	$prompt .= "- Do NOT assume WordPress paths like wp-content/plugins/ - use actual file structure\n\n";

	// Add actual directory structure
	$structure = get_directory_structure( $work_dir );
	$prompt    .= "ACTUAL DIRECTORY STRUCTURE:\n";
	$prompt    .= $structure . "\n";
	$prompt    .= "IMPORTANT: This is the complete directory structure. Work within these files only.\n\n";

	$prompt .= "SECURITY ISSUE CONTEXT:\n";
	// Add the security issue details from the original messages

	if ( $wp_call_graph_data ) {
		$prompt .= "\n=== WP-CALL-GRAPH FINDINGS ===\n";
		$prompt .= sprintf( "Target Function: %s\n", $wp_call_graph_data['symbol'] ?? 'unknown' );

		if ( ! empty( $wp_call_graph_data['trace'] ) ) {
			$prompt .= "\nDiscovered Hooks:\n";
			foreach ( $wp_call_graph_data['trace'] as $trace ) {
				$prompt .= sprintf(
					"- %s at %s:%d\n",
					$trace['hook_name'] ?? 'unknown',
					basename( $trace['file'] ?? 'unknown' ),
					$trace['line'] ?? 0
				);
			}
		}
	}

	$prompt .= "\nYOUR TASK:\n";
	$prompt .= "1. Analyze the security issue and wp-call-graph findings\n";
	$prompt .= "2. Determine what specific information you need to investigate\n";
	$prompt .= "3. Request specific tool calls to gather that information\n";
	$prompt .= "4. REMEMBER: Only use files from the directory structure shown above\n\n";

	$prompt .= "Available tools:\n";
	$prompt .= "- read_file: Read specific lines from a file (path relative to base directory)\n";
	$prompt .= "- search_pattern: Search for patterns in the codebase\n";
	$prompt .= "- list_files: List files in a directory (relative to base directory)\n\n";

	$prompt .= "When requesting investigations, be specific. For example:\n";
	$prompt .= '{"investigations": [';
	$prompt .= '  {"tool": "read_file", "args": {"path": "admin.php", "start_line": 45, "end_line": 55}},';
	$prompt .= '  {"tool": "search_pattern", "args": {"pattern": "wp_ajax_nopriv_"}}';
	$prompt .= ']}\n';
	$prompt .= "\nREMINDER: All paths must be relative to the base directory: $work_dir\n";

	return $prompt;
}

function build_enhanced_system_prompt_with_wp_call_graph( $work_dir, $static_context ) {
	log_info( "Building enhanced system prompt with wp-call-graph data", [
		'work_dir'           => $work_dir,
		'has_static_context' => ! empty( $static_context ),
		'context_size'       => $static_context ? strlen( json_encode( $static_context ) ) : 0
	] );

	$prompt = "You are analyzing a WordPress security vulnerability ONLY within: $work_dir\n\n";

	$prompt .= "CRITICAL RESTRICTIONS:\n";
	$prompt .= "- You can ONLY access files within the base directory: $work_dir\n";
	$prompt .= "- Do NOT attempt to access system files, parent directories, or other locations\n";
	$prompt .= "- All file paths must be relative to the base directory\n";
	$prompt .= "- This is an extracted plugin/theme - NOT a full WordPress installation\n";
	$prompt .= "- Do NOT assume WordPress paths like wp-content/plugins/ - use actual file structure\n\n";

	// Add actual directory structure
	$structure = get_directory_structure( $work_dir );
	$prompt    .= "ACTUAL DIRECTORY STRUCTURE:\n";
	$prompt    .= $structure . "\n";
	$prompt    .= "IMPORTANT: This is the complete directory structure. Work within these files only.\n\n";

	if ( $static_context ) {
		log_debug( "wp-call-graph prompt building: adding analysis results section", [
			'static_context_keys' => array_keys( $static_context )
		] );

		$prompt .= "=== WP-CALL-GRAPH ANALYSIS RESULTS ===\n\n";

		// Add symbol information
		if ( isset( $static_context['symbol_info'] ) ) {
			$symbol = $static_context['symbol_info'];
			log_debug( "wp-call-graph prompt building: adding symbol info", [
				'symbol'     => $symbol['symbol'] ?? 'unknown',
				'type'       => $symbol['type'] ?? 'unknown',
				'file'       => $symbol['file'] ?? 'unknown',
				'line_range' => ( $symbol['line_start'] ?? 'unknown' ) . '-' . ( $symbol['line_end'] ?? 'unknown' )
			] );

			$prompt .= "Target Symbol: {$symbol['symbol']} ({$symbol['type']})\n";
			$prompt .= "Location: {$symbol['file']}:{$symbol['line_start']}-{$symbol['line_end']}\n\n";
		}

		// Add execution context
		if ( isset( $static_context['execution_context'] ) ) {
			$exec_ctx = $static_context['execution_context'];

			log_debug( "wp-call-graph prompt building: adding execution context", [
				'symbol'                              => $exec_ctx['symbol'] ?? 'unknown',
				'callers_count'                       => count( $exec_ctx['callers'] ?? [] ),
				'wordpress_hooks_count'               => count( $exec_ctx['wordpress_hooks'] ?? [] ),
				'ajax_handlers_count'                 => count( $exec_ctx['ajax_handlers'] ?? [] ),
				'has_public_access'                   => $exec_ctx['has_public_access'] ?? false,
				'susceptible_to_privilege_escalation' => $exec_ctx['susceptible_to_privilege_escalation'] ?? false
			] );

			$prompt .= "Symbol: " . ( $exec_ctx['symbol'] ?? 'Unknown' ) . "\n";

			// Add direct callers
			if ( ! empty( $exec_ctx['callers'] ) ) {
				log_debug( "wp-call-graph prompt building: adding direct callers", [
					'callers_count' => count( $exec_ctx['callers'] )
				] );

				$prompt .= "\nDirect Callers Found by wp-call-graph:\n";
				foreach ( $exec_ctx['callers'] as $index => $caller ) {
					log_debug( "wp-call-graph prompt building: processing caller", [
						'caller_index'   => $index,
						'function'       => $caller['function'] ?? 'unknown',
						'location'       => $caller['location'] ?? 'unknown',
						'snippet_length' => strlen( $caller['snippet'] ?? '' )
					] );

					$prompt .= sprintf(
						"  - %s at %s (snippet: %s)\n",
						$caller['function'] ?? 'unknown',
						$caller['location'] ?? 'unknown',
						substr( $caller['snippet'] ?? '', 0, 80 )
					);
				}
			}

			// Add WordPress-specific findings
			if ( ! empty( $exec_ctx['wordpress_hooks'] ) ) {
				log_debug( "wp-call-graph prompt building: adding WordPress hooks", [
					'hooks_count' => count( $exec_ctx['wordpress_hooks'] )
				] );

				$prompt .= "\nWordPress Hooks Detected:\n";
				foreach ( $exec_ctx['wordpress_hooks'] as $index => $hook ) {
					log_debug( "wp-call-graph prompt building: processing WordPress hook", [
						'hook_index' => $index,
						'hook_name'  => $hook['hook'] ?? 'unknown',
						'hook_type'  => $hook['type'] ?? 'unknown',
						'location'   => $hook['location'] ?? 'unknown',
						'is_public'  => $hook['public'] ?? false
					] );

					$prompt .= sprintf(
						"  - %s (%s) at %s%s\n",
						$hook['hook'],
						$hook['type'],
						$hook['location'],
						$hook['public'] ? ' [PUBLIC ACCESS]' : ''
					);
				}
			}

			if ( ! empty( $exec_ctx['ajax_handlers'] ) ) {
				log_debug( "wp-call-graph prompt building: adding AJAX handlers", [
					'ajax_handlers_count' => count( $exec_ctx['ajax_handlers'] )
				] );

				$prompt .= "\nAJAX Handlers Detected:\n";
				foreach ( $exec_ctx['ajax_handlers'] as $index => $handler ) {
					log_debug( "wp-call-graph prompt building: processing AJAX handler", [
						'handler_index' => $index,
						'action'        => $handler['action'] ?? 'unknown',
						'location'      => $handler['location'] ?? 'unknown',
						'is_public'     => $handler['public'] ?? false
					] );

					$prompt .= sprintf(
						"  - %s at %s%s\n",
						$handler['action'],
						$handler['location'],
						$handler['public'] ? ' [NO AUTH REQUIRED - PUBLIC!]' : ' [Auth Required]'
					);
				}
			}

			// Add public access warning
			if ( $exec_ctx['has_public_access'] ) {
				log_debug( "wp-call-graph prompt building: adding public access warning" );
				$prompt .= "\n⚠️ PUBLIC ACCESS DETECTED - This code can be reached without authentication!\n";
			}

			// Add privilege escalation warning
			if ( $exec_ctx['susceptible_to_privilege_escalation'] ) {
				log_debug( "wp-call-graph prompt building: adding privilege escalation warning" );
				$prompt .= "\n🚨 PRIVILEGE ESCALATION RISK - This vulnerability could lead to privilege escalation!\n";
			}
		}

		// Add wp-call-graph results
		if ( isset( $static_context['wp_call_graph'] ) && ! empty( $static_context['wp_call_graph']['result'] ) ) {
			$wp_result = $static_context['wp_call_graph']['result'];

			log_debug( "wp-call-graph prompt building: adding wp-call-graph analysis results", [
				'symbol'      => $wp_result['symbol'] ?? 'unknown',
				'has_trace'   => ! empty( $wp_result['trace'] ),
				'trace_count' => count( $wp_result['trace'] ?? [] ),
				'result_keys' => array_keys( $wp_result )
			] );

			$prompt .= "\nWP-Call-Graph Analysis:\n";
			$prompt .= "Symbol: " . ( $wp_result['symbol'] ?? 'Unknown' ) . "\n";

			if ( ! empty( $wp_result['trace'] ) ) {
				log_debug( "wp-call-graph prompt building: processing trace information", [
					'trace_entries' => count( $wp_result['trace'] )
				] );

				$prompt .= "Trace Information:\n";
				foreach ( $wp_result['trace'] as $index => $trace ) {
					log_debug( "wp-call-graph prompt building: processing trace entry", [
						'trace_index' => $index,
						'type'        => $trace['type'] ?? 'unknown',
						'hook_name'   => $trace['hook_name'] ?? 'unknown',
						'file'        => basename( $trace['file'] ?? 'unknown' ),
						'line'        => $trace['line'] ?? 'unknown'
					] );

					$prompt .= sprintf(
						"  - Type: %s, Hook: %s, File: %s:%s\n",
						$trace['type'] ?? 'Unknown',
						$trace['hook_name'] ?? 'Unknown',
						basename( $trace['file'] ?? 'Unknown' ),
						$trace['line'] ?? 'Unknown'
					);
				}
			}
		}

		$prompt .= "\n=== END WP-CALL-GRAPH RESULTS ===\n\n";

		log_debug( "wp-call-graph prompt building: completed wp-call-graph results section" );
	} else {
		log_debug( "wp-call-graph prompt building: no static context provided, skipping wp-call-graph results" );
	}

	$prompt .= <<<'PROMPT'
YOUR TASK: Build a complete call graph from WordPress entry points to the vulnerable code.

STEP-BY-STEP APPROACH:
1. Review the wp-call-graph analysis results above - they show how the vulnerable function is connected to WordPress hooks
2. For each function that calls the vulnerable code:
   - Search for where it's registered as a WordPress hook
   - Use pattern: "add_action.*function_name" or "add_filter.*function_name"
   - Check if it's called directly in any files

3. Identify WordPress entry points:
   - wp_ajax_nopriv_* = PUBLIC (no authentication required!)
   - wp_ajax_* = Requires authentication
   - init, wp_loaded, template_redirect = Public hooks
   - admin_init = Admin only
   - REST API: register_rest_route with no permission_callback

4. Build the complete path:
   - Entry Point (hook/ajax/rest) -> intermediate functions -> vulnerable function

IMPORTANT PATTERNS TO SEARCH:
- add_action( 'wp_ajax_
- add_action( 'wp_ajax_nopriv_
- add_action( 'init'
- add_action( 'wp_loaded'
- add_action( 'template_redirect'
- register_rest_route(
- add_filter(

Use the wp-call-graph results as your starting point - they've already identified direct callers.
Now trace backward from those callers to find how they're invoked.

GOAL: Determine if unauthenticated users can reach the vulnerable code.
PROMPT;

	log_info( "wp-call-graph enhanced system prompt building completed", [
		'prompt_length'                  => strlen( $prompt ),
		'prompt_lines'                   => substr_count( $prompt, "\n" ),
		'contains_wp_call_graph_results' => strpos( $prompt, 'WP-CALL-GRAPH ANALYSIS RESULTS' ) !== false,
		'contains_trace_info'            => strpos( $prompt, 'Trace Information:' ) !== false,
		'contains_public_access_warning' => strpos( $prompt, 'PUBLIC ACCESS DETECTED' ) !== false
	] );

	return $prompt;
}

/**
 * Validate that AI actually used wp-call-graph data
 */
function validate_ai_used_wp_call_graph( $tool_calls, $wp_call_graph_data ) {
	if ( ! $wp_call_graph_data || empty( $wp_call_graph_data['result']['trace'] ) ) {
		return [ 'valid' => true ]; // No wp-call-graph data to use
	}

	// Check if AI searched for critical patterns
	$expected_searches = [];
	foreach ( $wp_call_graph_data['result']['trace'] as $trace ) {
		if ( strpos( $trace['hook_name'], 'wp_ajax_' ) !== false ) {
			$action              = str_replace( 'wp_ajax_', '', $trace['hook_name'] );
			$expected_searches[] = "wp_ajax_nopriv_$action";
		}
	}

	$searched_patterns = [];
	foreach ( $tool_calls as $call ) {
		if ( $call['tool'] === 'search_pattern' ) {
			$searched_patterns[] = $call['args']['pattern'];
		}
	}

	// Verify critical searches were performed
	$missing_searches = array_diff( $expected_searches, $searched_patterns );
	if ( ! empty( $missing_searches ) ) {
		return [
			'valid'  => false,
			'reason' => 'Failed to search for critical patterns: ' . implode( ', ', $missing_searches )
		];
	}

	return [ 'valid' => true ];
}

/**
 * Enhance AI results with wp-call-graph data if AI missed findings
 */
function enhance_ai_results_with_wp_call_graph( $ai_results, $wp_call_graph_data ) {
	$enhanced = json_decode( $ai_results, true );

	// If AI missed wp-call-graph findings, add them
	if ( empty( $enhanced['entry_points'] ) && ! empty( $wp_call_graph_data['result']['trace'] ) ) {
		$enhanced['entry_points'] = [];
		foreach ( $wp_call_graph_data['result']['trace'] as $trace ) {
			$enhanced['entry_points'][] = [
				'type'               => determine_entry_type( $trace['hook_name'] ),
				'name'               => $trace['hook_name'],
				'location'           => sprintf( '%s:%d', basename( $trace['file'] ), $trace['line'] ),
				'from_wp_call_graph' => true
			];
		}
	}

	return json_encode( $enhanced );
}

/**
 * Determine entry point type from hook name
 */
function determine_entry_type( $hook_name ) {
	if ( strpos( $hook_name, 'wp_ajax_' ) === 0 ) {
		return 'ajax';
	} elseif ( strpos( $hook_name, 'rest_' ) === 0 ) {
		return 'rest';
	} else {
		return 'hook';
	}
}

function build_enhanced_system_prompt( $work_dir, $static_context ) {
	$prompt = "You are analyzing a WordPress security vulnerability at: $work_dir\n\n";

	if ( $static_context ) {
		$prompt .= "STATIC ANALYSIS RESULTS:\n";
		$prompt .= "Symbol: " . ( $static_context['symbol'] ?? 'Unknown' ) . "\n";
		$prompt .= "Direct Callers:\n";
		foreach ( $static_context['callers'] ?? [] as $caller ) {
			$prompt .= "  - {$caller['function']} at {$caller['location']}\n";
		}
		$prompt .= "\n";
	}

	$prompt .= <<<'PROMPT'
YOUR TASK: Build a complete call graph from WordPress entry points to the vulnerable code.

STEP-BY-STEP APPROACH:
1. Start with the static call graph provided above
2. For each function that calls the vulnerable code:
   - Search for where it's registered as a WordPress hook
   - Use pattern: "add_action.*function_name" or "add_filter.*function_name"
   - Check if it's called directly in any files

3. Identify WordPress entry points:
   - wp_ajax_nopriv_* = PUBLIC (no authentication required!)
   - wp_ajax_* = Requires authentication
   - init, wp_loaded, template_redirect = Public hooks
   - admin_init = Admin only
   - REST API: register_rest_route with no permission_callback

4. Build the complete path:
   - Entry Point (hook/ajax/rest) -> intermediate functions -> vulnerable function

IMPORTANT PATTERNS TO SEARCH:
- add_action( 'wp_ajax_
- add_action( 'wp_ajax_nopriv_
- add_action( 'init'
- add_action( 'wp_loaded'
- add_action( 'template_redirect'
- register_rest_route(
- add_filter(

For each function in the call chain, search for how it's invoked.

GOAL: Determine if unauthenticated users can reach the vulnerable code.
PROMPT;

	return $prompt;
}

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
	$structure = get_directory_structure( $work_dir );
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
