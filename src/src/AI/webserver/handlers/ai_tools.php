<?php
/**
 * AI Processing with Tools Handler
 */

use QIT_CLI\AI\WebServer\ToolRegistry;

function handle_ai_with_tools( $input, $ollama_api_url ) {
	$model           = $input['model'] ?? 'qwen3:8b';
	$messages        = $input['messages'];
	$available_tools = $input['tools'] ?? [];
	$max_iterations  = $input['max_iterations'] ?? 10;
	$session_id      = $input['session_id'] ?? null;
	$zip_url         = $input['zip_url'] ?? null;
	$static_context  = $input['static_context'] ?? null; // wp-call-graph results

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
			'data_type' => gettype( $wp_call_graph_data ),
			'has_result' => isset( $wp_call_graph_data['result'] ),
			'traces_count' => isset( $wp_call_graph_data['result']['trace'] ) ? count( $wp_call_graph_data['result']['trace'] ) : 0
		] );
	} else {
		log_info( "No wp-call-graph data found in input" );
	}

	log_info( "Starting tool-enabled AI processing", [
		'model'              => $model,
		'tools_count'        => count( $available_tools ),
		'max_iterations'     => $max_iterations,
		'session_id'         => $session_id,
		'has_static_context' => ! empty( $static_context ),
		'input_keys'         => array_keys( $input )
	] );

	// Log wp-call-graph context if available
	if ( $static_context ) {
		log_info( "wp-call-graph static context provided", [
			'has_symbol_info'    => isset( $static_context['symbol_info'] ),
			'has_wp_call_graph'  => isset( $static_context['wp_call_graph'] ),
			'has_call_graph'     => isset( $static_context['call_graph'] ),
			'has_execution_ctx'  => isset( $static_context['execution_context'] ),
			'callers_count'      => count( $static_context['execution_context']['callers'] ?? [] ),
			'context_keys'       => array_keys( $static_context ),
			'context_size'       => strlen( json_encode( $static_context ) )
		] );

		// Log detailed wp-call-graph context analysis
		if ( isset( $static_context['symbol_info'] ) ) {
			log_debug( "wp-call-graph symbol info details", [
				'symbol' => $static_context['symbol_info']['symbol'] ?? 'unknown',
				'type' => $static_context['symbol_info']['type'] ?? 'unknown',
				'file' => $static_context['symbol_info']['file'] ?? 'unknown',
				'line_start' => $static_context['symbol_info']['line_start'] ?? 'unknown',
				'line_end' => $static_context['symbol_info']['line_end'] ?? 'unknown'
			] );
		}

		if ( isset( $static_context['execution_context'] ) ) {
			$exec_ctx = $static_context['execution_context'];
			log_debug( "wp-call-graph execution context details", [
				'symbol' => $exec_ctx['symbol'] ?? 'unknown',
				'callers_count' => count( $exec_ctx['callers'] ?? [] ),
				'wordpress_hooks_count' => count( $exec_ctx['wordpress_hooks'] ?? [] ),
				'ajax_handlers_count' => count( $exec_ctx['ajax_handlers'] ?? [] ),
				'has_public_access' => $exec_ctx['has_public_access'] ?? false,
				'execution_context_keys' => array_keys( $exec_ctx )
			] );

			// Log individual callers
			if ( !empty( $exec_ctx['callers'] ) ) {
				foreach ( $exec_ctx['callers'] as $index => $caller ) {
					log_debug( "wp-call-graph caller details", [
						'caller_index' => $index,
						'function' => $caller['function'] ?? 'unknown',
						'location' => $caller['location'] ?? 'unknown',
						'snippet_length' => strlen( $caller['snippet'] ?? '' ),
						'caller_keys' => array_keys( $caller )
					] );
				}
			}
		}

		if ( isset( $static_context['wp_call_graph'] ) ) {
			$wp_result = $static_context['wp_call_graph'];
			log_debug( "wp-call-graph result details", [
				'success' => $wp_result['success'] ?? false,
				'execution_time' => $wp_result['execution_time'] ?? 'unknown',
				'has_result' => isset( $wp_result['result'] ),
				'result_keys' => isset( $wp_result['result'] ) ? array_keys( $wp_result['result'] ) : [],
				'traces_count' => isset( $wp_result['result']['trace'] ) ? count( $wp_result['result']['trace'] ) : 0
			] );
		}
	}

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

	$tool_registry = new ToolRegistry( $work_dir );

	// Convert tools to Ollama format
	$ollama_tools = array_map( function ( $tool ) {
		return [
			'type'     => 'function',
			'function' => $tool
		];
	}, $available_tools );

	// Build initial conversation with enhanced guidance including wp-call-graph results
	$system_message = [
		'role'    => 'system',
		'content' => build_enhanced_system_prompt_with_wp_call_graph( $work_dir, $static_context )
	];

	$conversation   = array_merge( [ $system_message ], $messages );
	$all_tool_calls = [];

	for ( $iteration = 0; $iteration < $max_iterations; $iteration ++ ) {
		log_info( "Tool iteration $iteration", [
			'model'               => $model,
			'conversation_length' => count( $conversation ),
			'total_tool_calls'    => count( $all_tool_calls )
		] );

		// Call Ollama with tools
		$ollama_request = [
			'model'    => $model,
			'messages' => $conversation,
			'tools'    => $ollama_tools,
			'stream'   => false
		];

		log_info( "Tool-enabled AI request details", [
			'model'          => $model,
			'messages_count' => count( $conversation ),
			'tools_count'    => count( $ollama_tools )
		] );

		try {
			$response = call_ollama( $ollama_api_url . '/api/chat', $ollama_request );
		} catch ( Exception $e ) {
			log_error( "Ollama call failed", [ 'error' => $e->getMessage() ] );
			$final_response = [
				'response'   => 'Analysis partially completed. Error: ' . $e->getMessage(),
				'model'      => $model,
				'iterations' => $iteration,
				'tool_calls' => $all_tool_calls,
				'error'      => $e->getMessage(),
				'timestamp'  => time()
			];
			echo json_encode( $final_response );

			return;
		}

		// Check if AI wants to make tool calls
		if ( ! isset( $response['message']['tool_calls'] ) || empty( $response['message']['tool_calls'] ) ) {
			// AI didn't use tools - check if it's ready to conclude
			$ai_response = $response['message']['content'] ?? '';

			// If we have a substantial response and enough exploration
			if ( strlen( $ai_response ) > 200 && ( $iteration >= 3 || count( $all_tool_calls ) >= 5 ) ) {
				log_info( "AI completed exploration", [
					'iteration'        => $iteration,
					'total_tool_calls' => count( $all_tool_calls ),
					'response_length'  => strlen( $ai_response )
				] );

				// Ask for structured output
				$conversation[] = [
					'role'    => 'assistant',
					'content' => $ai_response
				];

				$conversation[] = [
					'role'    => 'user',
					'content' => 'Based on your analysis and the wp-call-graph analysis results, provide a JSON response with these exact fields: ' .
					             '{ "vulnerable_code": "description", "entry_points": [{"type": "ajax|hook|rest", "name": "hook_name", "location": "file:line", "is_public": boolean}], ' .
					             '"call_graph": [{"from": "function", "to": "function", "via": "method"}], ' .
					             '"exploitable": boolean, "has_public_access": boolean, "summary": "brief conclusion", ' .
					             '"wp_call_graph_findings": "summary of relevant wp-call-graph findings" }'
				];

				// One more call to get structured response
				$final_request = [
					'model'    => $model,
					'messages' => $conversation,
					'stream'   => false,
					'format'   => 'json'
				];

				try {
					$final_response = call_ollama( $ollama_api_url . '/api/chat', $final_request );
					echo json_encode( [
						'response'       => $final_response['message']['content'] ?? '{}',
						'model'          => $model,
						'iterations'     => $iteration + 1,
						'tool_calls'     => $all_tool_calls,
						'wp_call_graph_context'  => $static_context,
						'timestamp'      => time()
					] );

					return;
				} catch ( Exception $e ) {
					log_error( "Failed to get structured response", [ 'error' => $e->getMessage() ] );
				}
			}
		}

		// Add assistant response to conversation
		$assistant_message = [
			'role'    => 'assistant',
			'content' => $response['message']['content'] ?? ''
		];
		if ( isset( $response['message']['tool_calls'] ) ) {
			$assistant_message['tool_calls'] = $response['message']['tool_calls'];
		}
		$conversation[] = $assistant_message;

		// Execute tool calls
		$tool_results = [];
		if ( ! isset( $response['message']['tool_calls'] ) || empty( $response['message']['tool_calls'] ) ) {
			// Don't end iteration immediately - allow AI to continue for a few iterations
			// This prevents premature termination when AI responds with content but no tool calls
			$min_iterations = 2; // Allow at least 2 iterations before considering ending
			$min_tool_calls = 3; // Or require at least 3 tool calls before allowing early exit

			if ( $iteration >= $min_iterations && count( $all_tool_calls ) >= $min_tool_calls ) {
				log_info( "No tool calls in response, ending iteration after sufficient exploration", [
					'iteration' => $iteration,
					'total_tool_calls' => count( $all_tool_calls ),
					'has_content' => ! empty( $response['message']['content'] ?? '' ),
					'min_iterations' => $min_iterations,
					'min_tool_calls' => $min_tool_calls
				] );
				break;
			} else {
				log_info( "No tool calls in response, but continuing iteration for more exploration", [
					'iteration' => $iteration,
					'total_tool_calls' => count( $all_tool_calls ),
					'has_content' => ! empty( $response['message']['content'] ?? '' ),
					'min_iterations' => $min_iterations,
					'min_tool_calls' => $min_tool_calls,
					'will_continue' => true
				] );

				// Add the assistant response to conversation and continue
				$assistant_message = [
					'role'    => 'assistant',
					'content' => $response['message']['content'] ?? ''
				];
				$conversation[] = $assistant_message;

				// Continue to next iteration
				continue;
			}
		}

		foreach ( $response['message']['tool_calls'] as $tool_call ) {
			$function_name = $tool_call['function']['name'];
			$arguments     = is_string( $tool_call['function']['arguments'] )
				? json_decode( $tool_call['function']['arguments'], true ) ?? []
				: $tool_call['function']['arguments'] ?? [];

			log_info( "Executing tool: $function_name", [
				'args'      => $arguments,
				'iteration' => $iteration
			] );

			$result = $tool_registry->execute_tool( $function_name, $arguments );

			log_debug( "Tool execution completed", [
				'tool'    => $function_name,
				'success' => ! isset( $result['error'] ),
				'error'   => $result['error'] ?? null
			] );

			$conversation[] = [
				'role'         => 'tool',
				'content'      => json_encode( $result ),
				'tool_call_id' => $tool_call['id'] ?? uniqid()
			];

			$tool_call_info   = [
				'tool'      => $function_name,
				'args'      => $arguments,
				'result'    => $result,
				'iteration' => $iteration
			];
			$tool_results[]   = $tool_call_info;
			$all_tool_calls[] = $tool_call_info;
		}

		log_info( "Executed " . count( $tool_results ) . " tools in iteration $iteration" );
	}

	// Max iterations reached
	log_warning( "Max iterations reached", [
		'max_iterations'   => $max_iterations,
		'total_tool_calls' => count( $all_tool_calls )
	] );

	$final_response = [
		'response'      => json_encode( [
			'error'           => 'Max iterations reached',
			'partial_results' => $all_tool_calls
		] ),
		'model'         => $model,
		'iterations'    => $max_iterations,
		'tool_calls'    => $all_tool_calls,
		'wp_call_graph_context' => $static_context,
		'timestamp'     => time()
	];

	echo json_encode( $final_response );
}

function build_enhanced_system_prompt_with_wp_call_graph( $work_dir, $static_context ) {
	log_info( "Building enhanced system prompt with wp-call-graph data", [
		'work_dir' => $work_dir,
		'has_static_context' => !empty( $static_context ),
		'context_size' => $static_context ? strlen( json_encode( $static_context ) ) : 0
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
				'symbol' => $symbol['symbol'] ?? 'unknown',
				'type' => $symbol['type'] ?? 'unknown',
				'file' => $symbol['file'] ?? 'unknown',
				'line_range' => ($symbol['line_start'] ?? 'unknown') . '-' . ($symbol['line_end'] ?? 'unknown')
			] );

			$prompt .= "Target Symbol: {$symbol['symbol']} ({$symbol['type']})\n";
			$prompt .= "Location: {$symbol['file']}:{$symbol['line_start']}-{$symbol['line_end']}\n\n";
		}

		// Add execution context
		if ( isset( $static_context['execution_context'] ) ) {
			$exec_ctx = $static_context['execution_context'];

			log_debug( "wp-call-graph prompt building: adding execution context", [
				'symbol' => $exec_ctx['symbol'] ?? 'unknown',
				'callers_count' => count( $exec_ctx['callers'] ?? [] ),
				'wordpress_hooks_count' => count( $exec_ctx['wordpress_hooks'] ?? [] ),
				'ajax_handlers_count' => count( $exec_ctx['ajax_handlers'] ?? [] ),
				'has_public_access' => $exec_ctx['has_public_access'] ?? false
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
						'caller_index' => $index,
						'function' => $caller['function'] ?? 'unknown',
						'location' => $caller['location'] ?? 'unknown',
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
						'hook_name' => $hook['hook'] ?? 'unknown',
						'hook_type' => $hook['type'] ?? 'unknown',
						'location' => $hook['location'] ?? 'unknown',
						'is_public' => $hook['public'] ?? false
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
						'action' => $handler['action'] ?? 'unknown',
						'location' => $handler['location'] ?? 'unknown',
						'is_public' => $handler['public'] ?? false
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
		}

		// Add wp-call-graph results
		if ( isset( $static_context['wp_call_graph'] ) && ! empty( $static_context['wp_call_graph']['result'] ) ) {
			$wp_result = $static_context['wp_call_graph']['result'];

			log_debug( "wp-call-graph prompt building: adding wp-call-graph analysis results", [
				'symbol' => $wp_result['symbol'] ?? 'unknown',
				'has_trace' => !empty( $wp_result['trace'] ),
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
						'type' => $trace['type'] ?? 'unknown',
						'hook_name' => $trace['hook_name'] ?? 'unknown',
						'file' => basename( $trace['file'] ?? 'unknown' ),
						'line' => $trace['line'] ?? 'unknown'
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
		'prompt_length' => strlen( $prompt ),
		'prompt_lines' => substr_count( $prompt, "\n" ),
		'contains_wp_call_graph_results' => strpos( $prompt, 'WP-CALL-GRAPH ANALYSIS RESULTS' ) !== false,
		'contains_trace_info' => strpos( $prompt, 'Trace Information:' ) !== false,
		'contains_public_access_warning' => strpos( $prompt, 'PUBLIC ACCESS DETECTED' ) !== false
	] );

	return $prompt;
}

/**
 * Validate that AI actually used wp-call-graph data
 */
function validate_ai_used_wp_call_graph($tool_calls, $wp_call_graph_data) {
	if (!$wp_call_graph_data || empty($wp_call_graph_data['result']['trace'])) {
		return ['valid' => true]; // No wp-call-graph data to use
	}

	// Check if AI searched for critical patterns
	$expected_searches = [];
	foreach ($wp_call_graph_data['result']['trace'] as $trace) {
		if (strpos($trace['hook_name'], 'wp_ajax_') !== false) {
			$action = str_replace('wp_ajax_', '', $trace['hook_name']);
			$expected_searches[] = "wp_ajax_nopriv_$action";
		}
	}

	$searched_patterns = [];
	foreach ($tool_calls as $call) {
		if ($call['tool'] === 'search_pattern') {
			$searched_patterns[] = $call['args']['pattern'];
		}
	}

	// Verify critical searches were performed
	$missing_searches = array_diff($expected_searches, $searched_patterns);
	if (!empty($missing_searches)) {
		return [
			'valid' => false,
			'reason' => 'Failed to search for critical patterns: ' . implode(', ', $missing_searches)
		];
	}

	return ['valid' => true];
}

/**
 * Enhance AI results with wp-call-graph data if AI missed findings
 */
function enhance_ai_results_with_wp_call_graph($ai_results, $wp_call_graph_data) {
	$enhanced = json_decode($ai_results, true);

	// If AI missed wp-call-graph findings, add them
	if (empty($enhanced['entry_points']) && !empty($wp_call_graph_data['result']['trace'])) {
		$enhanced['entry_points'] = [];
		foreach ($wp_call_graph_data['result']['trace'] as $trace) {
			$enhanced['entry_points'][] = [
				'type' => determine_entry_type($trace['hook_name']),
				'name' => $trace['hook_name'],
				'location' => sprintf('%s:%d', basename($trace['file']), $trace['line']),
				'from_wp_call_graph' => true
			];
		}
	}

	return json_encode($enhanced);
}

/**
 * Determine entry point type from hook name
 */
function determine_entry_type($hook_name) {
	if (strpos($hook_name, 'wp_ajax_') === 0) {
		return 'ajax';
	} elseif (strpos($hook_name, 'rest_') === 0) {
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
