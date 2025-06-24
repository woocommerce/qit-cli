<?php
/**
 * AI Processing with Tools Handler
 */

use QIT_CLI\AI\WebServer\ToolRegistry;

function handle_ai_with_tools( $input, $ollama_api_url ) {
	// Model configuration
	$coder_model = $input['coder_model'] ?? 'qwen2.5-coder:32b';
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

	// Initialize tool registry with work directory
	$work_dir = '';

	// First, try to get the extraction path from dependencies (from zip_extraction stage)
	if ( isset( $input['dependencies']['extract_codebase'] ) ) {
		$extraction_data = $input['dependencies']['extract_codebase'];

		// Handle both string and array formats
		if ( is_string( $extraction_data ) ) {
			$decoded = json_decode( $extraction_data, true );
			if ( json_last_error() === JSON_ERROR_NONE && isset( $decoded['extract_path'] ) ) {
				$work_dir = $decoded['extract_path'];
				log_info( "Using extraction path from dependencies", [ 'work_dir' => $work_dir ] );
			}
		} elseif ( is_array( $extraction_data ) && isset( $extraction_data['extract_path'] ) ) {
			$work_dir = $extraction_data['extract_path'];
			log_info( "Using extraction path from dependencies", [ 'work_dir' => $work_dir ] );
		}
	}

	// Fallback to session_id-based path construction if no extraction path found
	if ( empty( $work_dir ) && $session_id ) {
		// Check if session_id is already a full path
		if ( strpos( $session_id, '/' ) === 0 ) {
			$work_dir = $session_id;
		} else {
			$cache_dir = sys_get_temp_dir() . '/qit-code-analysis';
			$work_dir  = $cache_dir . '/' . $session_id;
		}

		// Ensure the codebase is ready
		if ( ! is_dir( $work_dir ) || ! file_exists( $work_dir . '/.analyzed' ) ) {
			if ( $zip_url ) {
				log_info( "Preparing codebase for tool analysis" );
				prepare_codebase( $zip_url, $work_dir );
			}
		}
	}

	// Validate work directory exists
	if ( ! is_dir( $work_dir ) ) {
		log_error( "Work directory does not exist", [
			'work_dir'   => $work_dir,
			'session_id' => $session_id
		] );

		echo json_encode( [
			'response'   => json_encode( [
				'error'      => 'Work directory not found',
				'work_dir'   => $work_dir,
				'session_id' => $session_id
			] ),
			'model'      => $coder_model,
			'iterations' => 0,
			'tool_calls' => [],
			'error'      => 'Work directory setup failed'
		] );

		return;
	}

	log_info( "Initializing tool registry", [
		'work_dir' => $work_dir,
		'exists'   => is_dir( $work_dir ),
		'readable' => is_readable( $work_dir )
	] );

	$tool_registry = new ToolRegistry( $work_dir );

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
	echo json_encode( [
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
	] );
}

function build_coder_investigation_prompt( $work_dir, $static_context, $wp_call_graph_data ) {
	$prompt = "You are a WordPress security expert analyzing code at: $work_dir\n\n";

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
	$prompt .= "3. Request specific tool calls to gather that information\n\n";

	$prompt .= "Available tools:\n";
	$prompt .= "- read_file: Read specific lines from a file\n";
	$prompt .= "- search_pattern: Search for patterns in the codebase\n";
	$prompt .= "- list_files: List files in a directory\n\n";

	$prompt .= "When requesting investigations, be specific. For example:\n";
	$prompt .= '{"investigations": [';
	$prompt .= '  {"tool": "read_file", "args": {"path": "admin.php", "start_line": 45, "end_line": 55}},';
	$prompt .= '  {"tool": "search_pattern", "args": {"pattern": "wp_ajax_nopriv_"}}';
	$prompt .= ']}\n';

	return $prompt;
}

function build_enhanced_system_prompt_with_wp_call_graph( $work_dir, $static_context ) {
	log_info( "Building enhanced system prompt with wp-call-graph data", [
		'work_dir'           => $work_dir,
		'has_static_context' => ! empty( $static_context ),
		'context_size'       => $static_context ? strlen( json_encode( $static_context ) ) : 0
	] );

	$prompt = "You are analyzing a WordPress security vulnerability at: $work_dir\n\n";

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
