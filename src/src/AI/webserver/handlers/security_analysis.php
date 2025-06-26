<?php

/**
 * General Security Analysis Handler
 * 
 * This file contains functions for general security analysis,
 * including AI tools handling, wp-call-graph integration, and enhanced prompts.
 */

require_once __DIR__ . '/../NodeResponse.php';
require_once __DIR__ . '/../lib/ExtractPathResolver.php';

use QIT_CLI\AI\WebServer\ToolRegistry;
use QIT_CLI\AI\WebServer\ExtractPathResolver;

/**
 * Handle AI with tools for general security analysis
 */
function handle_general_security_analysis( $input, $ollama_api_url ) {
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

		// Call the discovery handler from logical security analysis
		require_once __DIR__ . '/logical_security_analysis.php';
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

		// Call the file analysis handler from logical security analysis
		require_once __DIR__ . '/logical_security_analysis.php';
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

		if ( empty( $investigation_plan['investigations'] ) ) {
			log_info( "No more investigations requested, moving to final analysis" );
			$investigation_complete = true;
			continue;
		}

		// Step 2: Execute the investigations using tools model
		$investigation_results = [];
		foreach ( $investigation_plan['investigations'] as $investigation ) {
			$tool_name = $investigation['tool'] ?? '';
			$tool_args = $investigation['args'] ?? [];

			log_info( "Executing investigation", [
				'tool' => $tool_name,
				'args' => $tool_args
			] );

			try {
				$result = $tool_registry->execute_tool( $tool_name, $tool_args );
				$investigation_results[] = [
					'tool'   => $tool_name,
					'args'   => $tool_args,
					'result' => $result
				];
				$all_tool_results[] = $result;
			} catch ( Exception $e ) {
				log_error( "Tool execution failed", [
					'tool'  => $tool_name,
					'error' => $e->getMessage()
				] );
				$investigation_results[] = [
					'tool'   => $tool_name,
					'args'   => $tool_args,
					'error'  => $e->getMessage()
				];
			}
		}

		// Step 3: Feed results back to coder
		$coder_conversation[] = [
			'role'    => 'assistant',
			'content' => $coder_response['message']['content']
		];

		$coder_conversation[] = [
			'role'    => 'user',
			'content' => "Investigation results:\n" . json_encode( $investigation_results, JSON_PRETTY_PRINT ) . "\n\n" .
			             "Based on these results, do you need more information? If so, request more investigations. " .
			             "If you have enough information, respond with 'ANALYSIS_COMPLETE' and provide your security analysis."
		];
	}

	// Step 4: Get final analysis from coder
	$final_prompt = "Based on all the investigations, provide your final security analysis in JSON format. " .
	                "Include: vulnerability_confirmed (true/false), severity (critical/high/medium/low), " .
	                "exploitability, entry_points, and detailed_analysis.";

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
			'error'   => 'Analysis failed',
			'message' => $e->getMessage()
		] );
	}

	// Validate and enhance results with wp-call-graph data if available
	if ( $wp_call_graph_data && ! empty( $all_tool_results ) ) {
		$final_analysis = enhance_ai_results_with_wp_call_graph( $final_analysis, $wp_call_graph_data );
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

/**
 * Get directory structure (utility function used by general security analysis)
 */
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

/**
 * Build coder investigation prompt
 */
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

/**
 * Build enhanced system prompt with wp-call-graph data
 */
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

		$prompt .= "=== STATIC ANALYSIS RESULTS ===\n";

		// Add wp-call-graph results if available
		if ( isset( $static_context['wp_call_graph'] ) ) {
			$wp_call_graph = $static_context['wp_call_graph'];
			$prompt .= "\n--- WP-CALL-GRAPH ANALYSIS ---\n";

			if ( isset( $wp_call_graph['result'] ) ) {
				$result = $wp_call_graph['result'];
				$prompt .= sprintf( "Target Function: %s\n", $result['symbol'] ?? 'unknown' );

				if ( ! empty( $result['trace'] ) ) {
					$prompt .= "\nDiscovered Entry Points:\n";
					foreach ( $result['trace'] as $trace ) {
						$hook_type = determine_entry_type( $trace['hook_name'] ?? '' );
						$prompt    .= sprintf(
							"- %s (%s) at %s:%d\n",
							$trace['hook_name'] ?? 'unknown',
							$hook_type,
							basename( $trace['file'] ?? 'unknown' ),
							$trace['line'] ?? 0
						);
					}
				}

				if ( isset( $result['analysis'] ) ) {
					$prompt .= "\nCall Graph Analysis:\n";
					$prompt .= $result['analysis'] . "\n";
				}
			}
		}

		// Add other static analysis results
		foreach ( $static_context as $key => $value ) {
			if ( $key === 'wp_call_graph' ) {
				continue; // Already handled above
			}

			$prompt .= "\n--- " . strtoupper( str_replace( '_', ' ', $key ) ) . " ---\n";
			if ( is_array( $value ) ) {
				$prompt .= json_encode( $value, JSON_PRETTY_PRINT ) . "\n";
			} else {
				$prompt .= $value . "\n";
			}
		}

		$prompt .= "\n=== END STATIC ANALYSIS ===\n\n";
	}

	$prompt .= "ANALYSIS INSTRUCTIONS:\n";
	$prompt .= "1. Review the static analysis results above\n";
	$prompt .= "2. Use the available tools to investigate the security issue\n";
	$prompt .= "3. Focus on determining if the vulnerability is exploitable\n";
	$prompt .= "4. Check for authentication/authorization requirements\n";
	$prompt .= "5. Identify all possible entry points\n";
	$prompt .= "6. Assess the potential impact\n\n";

	$prompt .= "Available tools:\n";
	$prompt .= "- read_file: Read specific lines from a file\n";
	$prompt .= "- search_pattern: Search for patterns in the codebase\n";
	$prompt .= "- list_files: List files in a directory\n\n";

	$prompt .= "IMPORTANT REMINDERS:\n";
	$prompt .= "- All file paths must be relative to: $work_dir\n";
	$prompt .= "- Only analyze files from the directory structure shown above\n";
	$prompt .= "- Consider WordPress security functions and best practices\n";
	$prompt .= "- Provide specific evidence for your conclusions\n";

	return $prompt;
}

/**
 * Validate if AI used wp-call-graph data appropriately
 */
function validate_ai_used_wp_call_graph( $tool_calls, $wp_call_graph_data ) {
	if ( empty( $wp_call_graph_data ) || empty( $tool_calls ) ) {
		return false;
	}

	// Check if AI investigated the files mentioned in wp-call-graph
	$wp_files = [];
	if ( isset( $wp_call_graph_data['result']['trace'] ) ) {
		foreach ( $wp_call_graph_data['result']['trace'] as $trace ) {
			if ( isset( $trace['file'] ) ) {
				$wp_files[] = basename( $trace['file'] );
			}
		}
	}

	// Check if any tool calls referenced these files
	foreach ( $tool_calls as $call ) {
		if ( isset( $call['args']['path'] ) ) {
			$called_file = basename( $call['args']['path'] );
			if ( in_array( $called_file, $wp_files ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Enhance AI results with wp-call-graph context
 */
function enhance_ai_results_with_wp_call_graph( $ai_results, $wp_call_graph_data ) {
	$results = json_decode( $ai_results, true );
	if ( ! $results ) {
		return $ai_results;
	}

	// Add wp-call-graph context to the results
	$results['wp_call_graph_context'] = [
		'target_function' => $wp_call_graph_data['result']['symbol'] ?? 'unknown',
		'entry_points'    => $wp_call_graph_data['result']['trace'] ?? [],
		'analysis'        => $wp_call_graph_data['result']['analysis'] ?? ''
	];

	return json_encode( $results, JSON_PRETTY_PRINT );
}

/**
 * Determine entry type from hook name
 */
function determine_entry_type( $hook_name ) {
	if ( strpos( $hook_name, 'wp_ajax_nopriv_' ) === 0 ) {
		return 'Public AJAX';
	} elseif ( strpos( $hook_name, 'wp_ajax_' ) === 0 ) {
		return 'Authenticated AJAX';
	} elseif ( strpos( $hook_name, 'admin_' ) === 0 ) {
		return 'Admin Hook';
	} elseif ( strpos( $hook_name, 'wp_' ) === 0 ) {
		return 'WordPress Hook';
	} else {
		return 'Custom Hook';
	}
}

/**
 * Build enhanced system prompt (general version)
 */
function build_enhanced_system_prompt( $work_dir, $static_context ) {
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

	$prompt .= "ANALYSIS GOAL:\n";
	$prompt .= "Determine if the reported security issue is exploitable and assess its impact.\n\n";

	$prompt .= "FOCUS AREAS:\n";
	$prompt .= "1. Entry points - How can attackers reach the vulnerable code?\n";
	$prompt .= "2. Authentication - What permissions are required?\n";
	$prompt .= "3. Input validation - Is user input properly sanitized?\n";
	$prompt .= "4. Impact assessment - What damage could be done?\n\n";

	$prompt .= "GOAL: Determine if unauthenticated users can reach the vulnerable code.\n";

	return $prompt;
}
