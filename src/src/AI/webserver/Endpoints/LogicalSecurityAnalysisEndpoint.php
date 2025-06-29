<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use QIT_AI_Webserver\Lib\ToolRegistry;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\Lib\FilePathResolver;
use QIT_AI_Webserver\NodeResponse;

/**
 * Logical Security Analysis Endpoint - Complete Refactored Version
 *
 * This class contains methods specifically for logical security analysis,
 * using Llama 3.2's native tool calling capabilities with improved investigation flow.
 */
class LogicalSecurityAnalysisEndpoint extends AbstractEndpoint {

	/**
	 * Get the route for this endpoint
	 *
	 * @return string The route path
	 */
	public function get_route(): string {
		return '/ai-analysis-with-tools';
	}

	/**
	 * Handle request based on input
	 *
	 * @param array $input Request input
	 */
	public function handle( array $input ): void {
		$jobId = $input['job_id'] ?? null;

		// Validate that model is provided in the input
		if ( ! isset( $input['model'] ) ) {
			$this->log_error( "Missing required model parameter", [
				'job_id' => $jobId,
				'uri'    => $_SERVER['REQUEST_URI'] ?? 'unknown'
			] );

			NodeResponse::error( 'Missing required model parameter', 400, [
				'job_id' => $jobId
			] );

			return;
		}

		$model = $input['model'];

		// Validate that options are provided in the input
		if ( ! isset( $input['options'] ) ) {
			$this->log_error( "Missing required options parameter", [
				'job_id' => $jobId,
				'uri'    => $_SERVER['REQUEST_URI'] ?? 'unknown'
			] );

			NodeResponse::error( 'Missing required options parameter', 400, [
				'job_id' => $jobId
			] );

			return;
		}

		$modelOptions = $input['options'];

		// Check if this is a file analysis mode
		if ( isset( $input['config']['analysis_mode'] ) ) {
			if ( $input['config']['analysis_mode'] === 'vulnerability_investigation' ) {
				$this->handleFileAnalysis( $input, $jobId, $model, $modelOptions );
			} else {
				// Handle other analysis modes as general security analysis
				$this->handleGeneralSecurityAnalysis( $input, $model, $modelOptions );
			}
		} elseif ( isset( $input['task_phase'] ) && $input['task_phase'] === 'file_analysis' ) {
			// Handle file analysis task
			if ( ! $jobId ) {
				$this->log_error( "Missing job_id for file analysis mode" );
				NodeResponse::error( 'Missing required job_id parameter for file analysis mode', 400, [
					'job_id' => $jobId
				] );

				return;
			}
			$this->handleFileAnalysis( $input, $jobId, $model, $modelOptions );
		} elseif ( isset( $input['messages'] ) && isset( $input['tools'] ) ) {
			// Handle general security analysis with tools
			$this->handleGeneralSecurityAnalysis( $input, $model, $modelOptions );
		} else {
			// No valid mode specified
			$this->log_error( "No valid analysis mode specified" );
			NodeResponse::error( 'No valid analysis mode specified', 400, [
				'job_id' => $jobId
			] );
		}
	}

	/**
	 * Handle file analysis
	 *
	 * @param array $input Request input
	 * @param string $jobId Job identifier
	 * @param string $model Model to use
	 * @param array $modelOptions Model options
	 */
	public function handleFileAnalysis( array $input, string $jobId, string $model, array $modelOptions ): void {
		$this->log_info( "Starting file analysis for logical security", [
			'job_id'           => $jobId,
			'file_path'        => $input['file_path'] ?? 'unknown',
			'has_file_content' => isset( $input['file_content'] ),
			'analysis_mode'    => $input['config']['analysis_mode'] ?? 'unknown'
		] );

		// Check if this is a tool-based investigation that doesn't require pre-loaded content
		if ( ! isset( $input['file_content'] ) ) {
			// Allow tool-based vulnerability investigation to proceed without file_content
			if ( $input['config']['analysis_mode'] === 'vulnerability_investigation' &&
			     isset( $input['config']['available_tools'] ) ) {
				$this->log_info( "Proceeding with tool-based vulnerability investigation", [
					'available_tools' => $input['config']['available_tools']
				] );
				$this->handleVulnerabilityInvestigation( $input, $model, $modelOptions );

				return;
			}

			// Only error for paradigms that require pre-loaded content
			$this->log_error( "No file content provided for analysis" );
			NodeResponse::error( 'No file content provided', 400 );

			return;
		}

		// Deep investigation with tools (can work with or without pre-loaded content)
		if ( $input['config']['analysis_mode'] === 'vulnerability_investigation' ) {
			$this->handleVulnerabilityInvestigation( $input, $model, $modelOptions );

			return;
		}
	}


	/**
	 * Handle vulnerability investigation mode - COMPLETELY REFACTORED
	 *
	 * @param array $input Request input
	 * @param string $model Model to use
	 * @param array $modelOptions Model options
	 */
	private function handleVulnerabilityInvestigation( array $input, string $model, array $modelOptions ): void {
		// DEBUG: Log input structure to diagnose the issue
		$this->log_info( "DEBUG: handleVulnerabilityInvestigation called", [
			'input_keys'         => array_keys( $input ),
			'has_vulnerability'  => isset( $input['vulnerability'] ),
			'vulnerability_data' => $input['vulnerability'] ?? 'NOT_SET',
			'job_id'             => $input['job_id'] ?? 'unknown'
		] );

		$vulnerability = $input['vulnerability'] ?? null;
		if ( ! $vulnerability ) {
			$this->log_error( "No vulnerability in input", [
				'input_keys' => array_keys( $input ),
				'full_input' => json_encode( $input, JSON_PRETTY_PRINT )
			] );
			NodeResponse::error( 'No vulnerability provided for investigation', 400 );

			return;
		}

		// Validate vulnerability has required fields
		if ( empty( $vulnerability['file'] ) ) {
			$this->log_error( "Vulnerability missing file path", [
				'vulnerability' => $vulnerability,
				'job_id'        => $input['job_id'] ?? 'unknown'
			] );
			NodeResponse::error( 'Vulnerability missing file path - cannot investigate without target file', 400, [
				'job_id'             => $input['job_id'] ?? null,
				'vulnerability_data' => $vulnerability,
				'help'               => 'Ensure vulnerability discovery phase includes file paths in vulnerability objects'
			] );

			return;
		}

		if ( empty( $vulnerability['line'] ) ) {
			$this->log_error( "Vulnerability missing line number", [
				'vulnerability' => $vulnerability,
				'job_id'        => $input['job_id'] ?? 'unknown'
			] );
			NodeResponse::error( 'Vulnerability missing line number - cannot investigate without target location', 400, [
				'job_id'             => $input['job_id'] ?? null,
				'vulnerability_data' => $vulnerability
			] );

			return;
		}

		if ( empty( $vulnerability['type'] ) ) {
			$this->log_error( "Vulnerability missing type", [
				'vulnerability' => $vulnerability,
				'job_id'        => $input['job_id'] ?? 'unknown'
			] );
			NodeResponse::error( 'Vulnerability missing type - cannot investigate without vulnerability type', 400, [
				'job_id'             => $input['job_id'] ?? null,
				'vulnerability_data' => $vulnerability
			] );

			return;
		}

		$this->log_info( "Starting structured vulnerability investigation with Llama 3.2", [
			'vulnerability_type' => $vulnerability['type'] ?? 'unknown',
			'vulnerability_file' => $vulnerability['file'] ?? 'unknown',
			'vulnerability_line' => $vulnerability['line'] ?? 'unknown',
			'job_id'             => $input['job_id'] ?? 'unknown',
			'model'              => $model
		] );

		try {
			$workDir = ExtractPathResolver::resolve( $input );
		} catch ( Exception $e ) {
			$this->log_error( "Path resolution failed for investigation", [
				'error' => $e->getMessage()
			] );

			NodeResponse::error( $e->getMessage(), 400, [
				'job_id' => $input['job_id'] ?? null,
				'help'   => 'Ensure extract_path is provided from zip extraction step'
			] );

			return;
		}

		// Initialize tool registry
		$toolRegistry = new ToolRegistry( $workDir );

		// Configuration
		$maxIterations = $input['config']['max_iterations_per_investigation'] ?? 15;

		// Run STRUCTURED investigation
		$investigationResult = $this->runStructuredInvestigation(
			$vulnerability,
			$workDir,
			$toolRegistry,
			$maxIterations,
			$model,
			$modelOptions
		);

		// Prepare response
		$responseContent = json_encode( [
			'vulnerability_confirmed' => $investigationResult['confirmed'] ?? false,
			'analysis'                => $investigationResult['analysis'] ?? '',
			'severity'                => $investigationResult['severity'] ?? $vulnerability['severity'],
			'exploitability'          => $investigationResult['exploitability'] ?? 'unknown',
			'impact'                  => $investigationResult['impact'] ?? 'unknown',
			'remediation'             => $investigationResult['remediation'] ?? '',
			'evidence'                => $investigationResult['evidence'] ?? [],
			'tool_calls_count'        => count( $investigationResult['tool_calls'] ?? [] ),
			'investigation_steps'     => $investigationResult['completed_steps'] ?? []
		] );

		NodeResponse::toolPrompt(
			$responseContent,
			$investigationResult['tool_calls'] ?? [],
			$model,
			[
				'job_id'         => $input['job_id'] ?? null,
				'session_id'     => $input['session_id'] ?? null,
				'iterations'     => $investigationResult['iterations'] ?? 0,
				'execution_time' => $investigationResult['execution_time'] ?? 0
			]
		);

		return;
	}

	/**
	 * Run STRUCTURED investigation with specific steps
	 *
	 * @param array $vulnerability Vulnerability to investigate
	 * @param string $workDir Work directory
	 * @param ToolRegistry $toolRegistry Tool registry
	 * @param int $maxIterations Maximum iterations
	 * @param string $model Model to use
	 * @param array $modelOptions Model options
	 *
	 * @return array Investigation result
	 */
	private function runStructuredInvestigation(
		array $vulnerability,
		string $workDir,
		ToolRegistry $toolRegistry,
		int $maxIterations,
		string $model,
		array $modelOptions
	): array {
		$startTime = microtime( true );
		$this->log_info( "Starting STRUCTURED vulnerability investigation", [
			'vulnerability'  => $vulnerability['type'] ?? 'unknown',
			'file'           => $vulnerability['file'] ?? 'unknown',
			'line'           => $vulnerability['line'] ?? 'unknown',
			'max_iterations' => $maxIterations
		] );

		// DEBUG LOGGING - Full vulnerability details and prompts
		$this->log_info( "INVESTIGATION STARTING - Full vulnerability details", [
			'vulnerability'         => json_encode( $vulnerability ),
			'initial_prompt'        => $this->buildInitialInvestigationPrompt( $vulnerability ),
			'system_prompt_preview' => substr( $this->buildStructuredSystemPrompt( $vulnerability, $workDir ), 0, 500 )
		] );

		// Track investigation progress
		$investigationState = [
			'read_target_file'       => false,
			'found_entry_points'     => false,
			'checked_authentication' => false,
			'traced_data_flow'       => false,
			'final_assessment'       => false
		];

		// Build initial conversation
		$messages = [
			[
				'role'    => 'system',
				'content' => $this->buildStructuredSystemPrompt( $vulnerability, $workDir )
			],
			[
				'role'    => 'user',
				'content' => $this->buildInitialInvestigationPrompt( $vulnerability )
			]
		];

		// Define available tools
		$tools = $this->getToolDefinitions();

		// Track all tool calls and iterations
		$allToolCalls = [];
		$iterations   = 0;

		// Investigation loop
		while ( $iterations < $maxIterations && ! $investigationState['final_assessment'] ) {
			$iterations ++;

			$this->log_info( "Investigation iteration $iterations/$maxIterations", [
				'completed_steps' => array_keys( array_filter( $investigationState ) )
			] );

			// Call Llama 3.2 with tools
			$request = [
				'model'    => $model,
				'messages' => $messages,
				'tools'    => $tools,
				'stream'   => false,
				'options'  => $modelOptions
			];

			try {
				$response = $this->callOllamaChat( $request );
			} catch ( Exception $e ) {
				$this->log_error( "Llama 3.2 call failed", [ 'error' => $e->getMessage() ] );
				break;
			}

			$message = $response['message'] ?? [];

			// Add assistant's response to conversation
			$messages[] = $message;

			// Check if model made tool calls
			if ( isset( $message['tool_calls'] ) && ! empty( $message['tool_calls'] ) ) {
				$this->log_info( "Model requested tool calls", [
					'count'     => count( $message['tool_calls'] ),
					'iteration' => $iterations
				] );

				// Execute tool calls
				$toolResults = $this->executeToolCalls(
					$message['tool_calls'],
					$toolRegistry,
					$allToolCalls,
					$iterations
				);

				// Add tool results to conversation
				$messages[] = [
					'role'    => 'tool',
					'content' => json_encode( $toolResults )
				];

				// Update investigation state based on tool calls
				$this->updateInvestigationState( $investigationState, $allToolCalls, $vulnerability );

				// Provide guided next steps
				$guidanceMessage = $this->getGuidedNextStep( $investigationState, $vulnerability, $iterations );
				if ( $guidanceMessage ) {
					$messages[] = [
						'role'    => 'user',
						'content' => $guidanceMessage
					];
				}

			} else {
				// No tool calls - check if investigation is complete
				$content = $message['content'] ?? '';

				// Force minimum investigation depth
				if ( $iterations < 3 || ! $investigationState['read_target_file'] ) {
					$messages[] = [
						'role'    => 'user',
						'content' => "You must read the vulnerable file first. Use the read_file tool to examine {$vulnerability['file']} around line {$vulnerability['line']}."
					];
					continue;
				}

				if ( $this->isInvestigationComplete( $content ) || $iterations >= $maxIterations - 1 ) {
					$investigationState['final_assessment'] = true;
					$this->log_info( "Investigation completed at iteration $iterations" );

					return $this->compileInvestigationResults(
						$content,
						$allToolCalls,
						$iterations,
						$startTime,
						$investigationState
					);
				}

				// Prompt for continuation
				$messages[] = [
					'role'    => 'user',
					'content' => "Continue your investigation. You've completed these steps: " .
					             implode( ', ', array_keys( array_filter( $investigationState ) ) ) .
					             ". What else do you need to check?"
				];
			}
		}

		// Max iterations reached or error occurred
		$finalContent = $this->getFinalAssessment( $messages );

		return $this->compileInvestigationResults(
			$finalContent,
			$allToolCalls,
			$iterations,
			$startTime,
			$investigationState
		);
	}

	/**
	 * Execute tool calls and track them
	 */
	private function executeToolCalls(
		array $toolCalls,
		ToolRegistry $toolRegistry,
		array &$allToolCalls,
		int $iteration
	): array {
		$toolResults = [];

		foreach ( $toolCalls as $toolCall ) {
			$toolName = $toolCall['function']['name'] ?? '';
			$toolArgs = $toolCall['function']['arguments'] ?? '{}';

			// Parse arguments if string
			if ( is_string( $toolArgs ) ) {
				$toolArgs = json_decode( $toolArgs, true ) ?? [];
			}

			$this->log_info( "Executing tool", [
				'tool' => $toolName,
				'args' => $toolArgs
			] );

			try {
				$result = $toolRegistry->execute_tool( $toolName, $toolArgs );

				$toolResults[] = [
					'tool_call_id' => $toolCall['id'] ?? uniqid(),
					'output'       => json_encode( $result )
				];

				$allToolCalls[] = [
					'iteration' => $iteration,
					'tool'      => $toolName,
					'args'      => $toolArgs,
					'result'    => $result,
					'success'   => ! isset( $result['error'] )
				];
			} catch ( Exception $e ) {
				$this->log_error( "Tool execution failed", [
					'tool'  => $toolName,
					'error' => $e->getMessage()
				] );

				$toolResults[] = [
					'tool_call_id' => $toolCall['id'] ?? uniqid(),
					'output'       => json_encode( [ 'error' => $e->getMessage() ] )
				];
			}
		}

		return $toolResults;
	}

	/**
	 * Update investigation state based on completed actions
	 */
	private function updateInvestigationState( array &$state, array $toolCalls, array $vulnerability ): void {
		foreach ( $toolCalls as $call ) {
			// Check if target file was read
			if ( $call['tool'] === 'read_file' &&
			     isset( $call['args']['path'] ) &&
			     strpos( $call['args']['path'], $vulnerability['file'] ) !== false ) {
				$state['read_target_file'] = true;
			}

			// Check if searching for entry points
			if ( $call['tool'] === 'search_pattern' ) {
				$pattern = strtolower( $call['args']['pattern'] ?? '' );
				if ( strpos( $pattern, 'add_action' ) !== false ||
				     strpos( $pattern, 'ajax' ) !== false ) {
					$state['found_entry_points'] = true;
				}
				if ( strpos( $pattern, 'verify_nonce' ) !== false ||
				     strpos( $pattern, 'current_user_can' ) !== false ) {
					$state['checked_authentication'] = true;
				}
			}
		}

		// Mark data flow as traced if we've done enough investigation
		if ( $state['read_target_file'] && $state['found_entry_points'] ) {
			$state['traced_data_flow'] = true;
		}
	}

	/**
	 * Get guided next step based on investigation state
	 */
	// Enhanced getGuidedNextStep method with more specific instructions:

	private function getGuidedNextStep( array $state, array $vulnerability, int $iteration ): ?string {
		// Extract function name more intelligently
		$funcName = $this->extractFunctionName( $vulnerability );

		// Step 1: Read the vulnerable file
		if ( ! $state['read_target_file'] ) {
			return sprintf(
				"Start by reading the vulnerable code. Use:\n" .
				"read_file with parameters:\n" .
				"- path: \"%s\"\n" .
				"- start_line: %d\n" .
				"- end_line: %d\n" .
				"This will show you the code around line %d where the %s vulnerability was found.",
				$vulnerability['file'],
				max( 1, $vulnerability['line'] - 30 ),
				$vulnerability['line'] + 30,
				$vulnerability['line'],
				$vulnerability['type']
			);
		}

		// Step 2: Find entry points
		if ( ! $state['found_entry_points'] && $iteration === 2 ) {
			$patterns = [];

			// For CSRF vulnerabilities, look for AJAX handlers
			if ( stripos( $vulnerability['type'], 'CSRF' ) !== false ) {
				$patterns[] = "- search_pattern with pattern: \"add_action.*wp_ajax.*{$funcName}\"";
				$patterns[] = "- search_pattern with pattern: \"wp_ajax_nopriv_\"";
			}

			// For any vulnerability, search for function calls
			$patterns[] = "- search_pattern with pattern: \"{$funcName}\\s*\\(\"";
			$patterns[] = "- search_pattern with pattern: \"->call_api\\s*\\(\" (if this is an API call)";

			return "Now find where this vulnerable code is called from. Try these searches:\n" .
			       implode( "\n", $patterns ) . "\n" .
			       "This will help identify all entry points to the vulnerable code.";
		}

		// Step 3: Check authentication
		if ( ! $state['checked_authentication'] && $iteration === 3 ) {
			return "Check for security measures. Use search_pattern with these patterns:\n" .
			       "- Pattern: \"wp_verify_nonce\" (for CSRF protection)\n" .
			       "- Pattern: \"current_user_can\" (for authorization)\n" .
			       "- Pattern: \"is_user_logged_in\" (for authentication)\n" .
			       "- Pattern: \"check_admin_referer\" (for admin CSRF)\n" .
			       "Search in the vulnerable function and any calling functions you found.";
		}

		// Step 4: Trace data flow
		if ( ! $state['traced_data_flow'] && $iteration === 4 ) {
			return "Trace how user input reaches the vulnerable code. Use search_pattern:\n" .
			       "- Pattern: \"\\\$_(?:POST|GET|REQUEST)\\s*\\[\" (to find user input)\n" .
			       "- Pattern: \"sanitize_\" (to check for input sanitization)\n" .
			       "- Pattern: \"esc_\" (to check for output escaping)\n" .
			       "Focus on the functions you've already identified.";
		}

		// Step 5: Final assessment
		if ( $iteration >= 5 && ! $state['final_assessment'] ) {
			$vulnType = $vulnerability['type'] ?? 'vulnerability';

			return "Based on your investigation, provide your FINAL ASSESSMENT:\n\n" .
			       "1. Is the {$vulnType} vulnerability confirmed? (Yes/No)\n" .
			       "2. Exploitability: How can it be exploited?\n" .
			       "3. Prerequisites: What access level is needed?\n" .
			       "4. Impact: What damage could be done?\n" .
			       "5. Severity: Critical/High/Medium/Low\n" .
			       "6. Remediation: Specific fix recommendation\n\n" .
			       "Base your assessment ONLY on the evidence you've gathered.";
		}

		return null;
	}

	/**
	 * Extract function name from vulnerability info
	 */
	private function extractFunctionName( array $vulnerability ): string {
		// Try multiple extraction strategies
		$candidates = [];

		// From code snippet
		if ( isset( $vulnerability['code_snippet'] ) ) {
			$snippet = is_array( $vulnerability['code_snippet'] )
				? implode( "\n", $vulnerability['code_snippet'] )
				: $vulnerability['code_snippet'];

			// Method call pattern: ->method_name(
			if ( preg_match( '/->(\w+)\s*\(/', $snippet, $matches ) ) {
				$candidates[] = $matches[1];
			}

			// Function call pattern: function_name(
			if ( preg_match( '/(\w+)\s*\(/', $snippet, $matches ) ) {
				$candidates[] = $matches[1];
			}

			// Function definition: function name(
			if ( preg_match( '/function\s+(\w+)/', $snippet, $matches ) ) {
				$candidates[] = $matches[1];
			}
		}

		// From description
		if ( isset( $vulnerability['description'] ) ) {
			if ( preg_match( '/function\s+(\w+)/i', $vulnerability['description'], $matches ) ) {
				$candidates[] = $matches[1];
			}
			if ( preg_match( '/method\s+(\w+)/i', $vulnerability['description'], $matches ) ) {
				$candidates[] = $matches[1];
			}
			if ( preg_match( '/(\w+)\s*\(/', $vulnerability['description'], $matches ) ) {
				$candidates[] = $matches[1];
			}
		}

		// Return the most specific/longest candidate
		if ( ! empty( $candidates ) ) {
			usort( $candidates, function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			} );

			return $candidates[0];
		}

		return 'vulnerable_function';
	}

	/**
	 * Build structured system prompt
	 */
	// Replace buildStructuredSystemPrompt method:

	private function buildStructuredSystemPrompt( array $vulnerability, string $workDir ): string {
		$prompt = "You are a WordPress security expert conducting a STRUCTURED investigation of a specific vulnerability.\n\n";

		$prompt .= "WORK DIRECTORY: $workDir\n";
		$prompt .= "All file operations are relative to this directory.\n\n";

		$prompt .= "SPECIFIC VULNERABILITY TO INVESTIGATE:\n";
		$prompt .= "- Type: " . ( $vulnerability['type'] ?? 'Unknown' ) . "\n";
		$prompt .= "- File: " . ( $vulnerability['file'] ?? 'Unknown' ) . "\n";
		$prompt .= "- Line: " . ( $vulnerability['line'] ?? 'Unknown' ) . "\n";
		$prompt .= "- Description: " . ( $vulnerability['description'] ?? 'No description' ) . "\n\n";

		$prompt .= "INVESTIGATION METHODOLOGY:\n";
		$prompt .= "Follow these steps IN ORDER:\n\n";

		$prompt .= "1. READ THE VULNERABLE CODE:\n";
		$prompt .= "   - Use read_file to examine the specific file and line\n";
		$prompt .= "   - Read at least 30 lines before and after the vulnerability\n";
		$prompt .= "   - Identify the function name and understand what it does\n\n";

		$prompt .= "2. FIND ENTRY POINTS:\n";
		$prompt .= "   - Search for where this function is called using search_pattern\n";
		$prompt .= "   - For AJAX handlers: search_pattern with pattern 'add_action.*wp_ajax.*function_name'\n";
		$prompt .= "   - For direct calls: search_pattern with pattern 'function_name\\s*\\('\n";
		$prompt .= "   - Check if it's accessible via public AJAX (wp_ajax_nopriv_)\n\n";

		$prompt .= "3. CHECK AUTHENTICATION:\n";
		$prompt .= "   - Search for security checks in the vulnerable function\n";
		$prompt .= "   - Use search_pattern with patterns like:\n";
		$prompt .= "     * 'wp_verify_nonce' - for CSRF protection\n";
		$prompt .= "     * 'current_user_can' - for capability checks\n";
		$prompt .= "     * 'is_user_logged_in' - for authentication\n";
		$prompt .= "     * 'check_admin_referer' - for admin CSRF\n\n";

		$prompt .= "4. TRACE DATA FLOW:\n";
		$prompt .= "   - Search for user input: search_pattern with '\\\$_(GET|POST|REQUEST)'\n";
		$prompt .= "   - Track how data flows from input to the vulnerable point\n";
		$prompt .= "   - Check if input is sanitized/validated\n\n";

		$prompt .= "5. ASSESS EXPLOITABILITY:\n";
		$prompt .= "   - Can an attacker reach this code?\n";
		$prompt .= "   - What privileges are required?\n";
		$prompt .= "   - What would be the impact of exploitation?\n\n";

		$prompt .= "EXAMPLE SEARCH PATTERNS:\n";
		$prompt .= "- For AJAX: 'add_action\\s*\\(\\s*['\"]wp_ajax_'\n";
		$prompt .= "- For SQL: '\\\$wpdb->(?:query|prepare|get_results)'\n";
		$prompt .= "- For file ops: '(?:include|require|fopen|file_get_contents)\\s*\\('\n";
		$prompt .= "- For user input: '\\\$_(?:GET|POST|REQUEST|COOKIE|SERVER)\\s*\\['\n\n";

		$prompt .= "CRITICAL: Focus ONLY on the reported vulnerability. Do NOT investigate other issues.\n";
		$prompt .= "Use regex patterns in search_pattern for flexible matching.\n";

		return $prompt;
	}

	/**
	 * Build initial investigation prompt
	 */
	private function buildInitialInvestigationPrompt( array $vulnerability ): string {
		$file = $vulnerability['file'] ?? '';
		$line = $vulnerability['line'] ?? 0;
		$type = $vulnerability['type'] ?? 'Unknown';

		// Validate required fields for investigation
		if ( empty( $file ) || $file === 'Unknown' ) {
			return "ERROR: Cannot investigate vulnerability - file path is missing or invalid. " .
			       "The vulnerability data is incomplete. Required fields: file, line, type. " .
			       "Current vulnerability data: " . json_encode( $vulnerability );
		}

		if ( empty( $line ) || $line === 'Unknown' || ! is_numeric( $line ) ) {
			return "ERROR: Cannot investigate vulnerability - line number is missing or invalid. " .
			       "The vulnerability data is incomplete. Line must be a valid number. " .
			       "Current vulnerability data: " . json_encode( $vulnerability );
		}

		$prompt = "BEGIN INVESTIGATION of the {$type} vulnerability.\n\n";

		$prompt .= "FIRST REQUIRED ACTION:\n";
		$prompt .= "Read the file '{$file}' focusing on line {$line}.\n";
		$prompt .= "Use the read_file tool with these parameters:\n";
		$prompt .= "- path: {$file}\n";
		$prompt .= "- start_line: " . max( 1, $line - 30 ) . "\n";
		$prompt .= "- end_line: " . ( $line + 30 ) . "\n\n";

		$prompt .= "This is mandatory - you cannot investigate without first reading the vulnerable code.\n";
		$prompt .= "Execute the read_file tool now.";

		return $prompt;
	}

	/**
	 * Compile investigation results
	 */
	private function compileInvestigationResults(
		string $content,
		array $toolCalls,
		int $iterations,
		float $startTime,
		array $investigationState
	): array {
		$lowerContent = strtolower( $content );

		// Determine if vulnerability is confirmed
		$confirmed = false;
		if ( strpos( $lowerContent, 'confirmed' ) !== false && strpos( $lowerContent, 'not confirmed' ) === false ) {
			$confirmed = true;
		} elseif ( strpos( $lowerContent, 'exploitable' ) !== false && strpos( $lowerContent, 'not exploitable' ) === false ) {
			$confirmed = true;
		}

		// Extract severity
		$severity = 'unknown';
		if ( preg_match( '/severity:\s*(critical|high|medium|low)/i', $content, $matches ) ) {
			$severity = strtolower( $matches[1] );
		}

		// Extract evidence from tool calls
		$evidence = $this->extractEvidenceFromToolCalls( $toolCalls );

		return [
			'confirmed'       => $confirmed,
			'analysis'        => $content,
			'severity'        => $severity,
			'exploitability'  => $this->extractExploitability( $content ),
			'impact'          => $this->extractImpact( $content ),
			'remediation'     => $this->extractRemediation( $content ),
			'evidence'        => $evidence,
			'tool_calls'      => $toolCalls,
			'iterations'      => $iterations,
			'execution_time'  => round( ( microtime( true ) - $startTime ) * 1000 ),
			'completed_steps' => array_keys( array_filter( $investigationState ) )
		];
	}

	/**
	 * Extract evidence from tool calls
	 */
	private function extractEvidenceFromToolCalls( array $toolCalls ): array {
		$evidence = [];

		foreach ( $toolCalls as $call ) {
			if ( ! $call['success'] ) {
				continue;
			}

			$result = $call['result'] ?? [];

			// Evidence from file reads
			if ( $call['tool'] === 'read_file' && isset( $result['content'] ) ) {
				// Look for security-relevant code
				if ( preg_match( '/wp_verify_nonce\s*\([^)]*\)/', $result['content'], $matches ) ) {
					$evidence[] = "Found nonce verification: " . trim( $matches[0] );
				}
				if ( preg_match( '/current_user_can\s*\([^)]*\)/', $result['content'], $matches ) ) {
					$evidence[] = "Found capability check: " . trim( $matches[0] );
				}
			}

			// Evidence from searches
			if ( $call['tool'] === 'search_pattern' && isset( $result['results'] ) ) {
				foreach ( $result['results'] as $match ) {
					if ( isset( $match['content'] ) ) {
						$evidence[] = sprintf(
							"Found in %s:%d: %s",
							$match['file'] ?? 'unknown',
							$match['line'] ?? 0,
							trim( $match['content'] )
						);
					}
				}
			}
		}

		return array_unique( $evidence );
	}

	/**
	 * Check if investigation is complete
	 */
	private function isInvestigationComplete( string $content ): bool {
		$lowerContent = strtolower( $content );

		// Look for completion indicators
		$completionIndicators = [
			'investigation complete',
			'final assessment',
			'conclusion:',
			'vulnerability confirmed',
			'vulnerability not confirmed',
			'not exploitable',
			'is exploitable',
			'can be exploited',
			'cannot be exploited',
			'based on my investigation',
			'after analyzing'
		];

		foreach ( $completionIndicators as $indicator ) {
			if ( strpos( $lowerContent, $indicator ) !== false ) {
				// Also check that the response is substantial
				if ( strlen( $content ) > 200 ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get final assessment from conversation
	 */
	private function getFinalAssessment( array $messages ): string {
		// Work backwards to find the last substantial assistant message
		for ( $i = count( $messages ) - 1; $i >= 0; $i -- ) {
			$message = $messages[ $i ];

			if ( $message['role'] === 'assistant' && isset( $message['content'] ) ) {
				$content = $message['content'];

				// Return if it's a substantial response
				if ( strlen( $content ) > 100 ) {
					return $content;
				}
			}
		}

		return 'Investigation incomplete - no final assessment provided.';
	}

	/**
	 * Extract exploitability from content
	 */
	private function extractExploitability( string $content ): string {
		if ( preg_match( '/exploitability:\s*(.+?)(?:\n|$)/i', $content, $matches ) ) {
			return trim( $matches[1] );
		}

		if ( stripos( $content, 'easily exploitable' ) !== false ) {
			return 'Easily exploitable';
		} elseif ( stripos( $content, 'difficult to exploit' ) !== false ) {
			return 'Difficult to exploit';
		} elseif ( stripos( $content, 'not exploitable' ) !== false ) {
			return 'Not exploitable';
		}

		return 'Unknown';
	}

	/**
	 * Extract impact from content
	 */
	private function extractImpact( string $content ): string {
		if ( preg_match( '/impact:\s*(.+?)(?:\n|$)/i', $content, $matches ) ) {
			return trim( $matches[1] );
		}

		// Look for common impact descriptions
		$impactPatterns = [
			'remote code execution' => 'Remote code execution possible',
			'sql injection'         => 'Database compromise possible',
			'xss'                   => 'Cross-site scripting attacks possible',
			'authentication bypass' => 'Authentication bypass possible',
			'privilege escalation'  => 'Privilege escalation possible',
			'csrf'                  => 'Cross-site request forgery possible',
			'unauthorized access'   => 'Unauthorized access to functionality'
		];

		$lowerContent = strtolower( $content );
		foreach ( $impactPatterns as $pattern => $impact ) {
			if ( strpos( $lowerContent, $pattern ) !== false ) {
				return $impact;
			}
		}

		return 'Unknown impact';
	}

	/**
	 * Extract remediation from content
	 */
	private function extractRemediation( string $content ): string {
		if ( preg_match( '/remediation:\s*(.+?)(?:\n|$)/i', $content, $matches ) ) {
			return trim( $matches[1] );
		}

		if ( preg_match( '/fix:\s*(.+?)(?:\n|$)/i', $content, $matches ) ) {
			return trim( $matches[1] );
		}

		// Look for common remediation patterns
		if ( stripos( $content, 'add nonce verification' ) !== false ) {
			return 'Add proper nonce verification using wp_verify_nonce()';
		}
		if ( stripos( $content, 'check capabilities' ) !== false ) {
			return 'Add capability checks using current_user_can()';
		}
		if ( stripos( $content, 'sanitize input' ) !== false ) {
			return 'Properly sanitize and validate all user input';
		}

		return 'No specific remediation provided';
	}

	/**
	 * Get tool definitions for Llama 3.2
	 */
	// Tool definitions for LogicalSecurityAnalysisEndpoint:

	private function getToolDefinitions(): array {
		return [
			[
				'type'     => 'function',
				'function' => [
					'name'        => 'read_file',
					'description' => 'Read the contents of a PHP file to examine code. Use this to inspect suspicious functions and their implementation.',
					'parameters'  => [
						'type'       => 'object',
						'properties' => [
							'path'       => [
								'type'        => 'string',
								'description' => 'File path relative to plugin root (e.g., "classes/FortisApi.php")'
							],
							'start_line' => [
								'type'        => 'integer',
								'description' => 'Starting line number (1-based, inclusive)'
							],
							'end_line'   => [
								'type'        => 'integer',
								'description' => 'Ending line number (1-based, inclusive)'
							]
						],
						'required'   => [ 'path' ]
					]
				]
			],
			[
				'type'     => 'function',
				'function' => [
					'name'        => 'search_pattern',
					'description' => 'Search for code patterns across all PHP files. Supports regex patterns. Use this to find function calls, security checks, and entry points.',
					'parameters'  => [
						'type'       => 'object',
						'properties' => [
							'pattern'     => [
								'type'        => 'string',
								'description' => 'Regex pattern to search for. Examples: "wp_verify_nonce", "add_action.*wp_ajax_", "\\$_(GET|POST|REQUEST)"'
							],
							'max_results' => [
								'type'        => 'integer',
								'description' => 'Maximum results to return (default: 50)'
							],
							'is_regex'    => [
								'type'        => 'boolean',
								'description' => 'Whether pattern is regex (true) or literal string (false). Default: true'
							]
						],
						'required'   => [ 'pattern' ]
					]
				]
			],
			[
				'type'     => 'function',
				'function' => [
					'name'        => 'list_files',
					'description' => 'List all files and directories in a given path. Use this to explore the plugin structure.',
					'parameters'  => [
						'type'       => 'object',
						'properties' => [
							'directory' => [
								'type'        => 'string',
								'description' => 'Directory path relative to plugin root (use "." for root)'
							]
						],
						'required'   => []
					]
				]
			]
		];
	}

	/**
	 * Get directory structure
	 */
	private function getDirectoryStructure( string $workDir, int $maxDepth = 3 ): string {
		$structure = '';
		$iterator  = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $workDir, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);
		$iterator->setMaxDepth( $maxDepth );

		foreach ( $iterator as $file ) {
			$depth     = $iterator->getDepth();
			$indent    = str_repeat( '  ', $depth );
			$structure .= $indent . basename( $file ) . ( $file->isDir() ? '/' : '' ) . "\n";
		}

		return $structure;
	}


	/**
	 * Handle general security analysis with tools (simplified version)
	 *
	 * @param array $input Request input
	 * @param string $model Model to use
	 * @param array $modelOptions Model options
	 */
	private function handleGeneralSecurityAnalysis( array $input, string $model, array $modelOptions ): void {
		$this->log_info( "Starting simplified general security analysis", [
			'input_keys'   => array_keys( $input ),
			'has_messages' => isset( $input['messages'] ),
			'has_tools'    => isset( $input['tools'] ),
			'job_id'       => $input['job_id'] ?? 'none'
		] );

		$messages       = $input['messages'] ?? [];
		$availableTools = $input['tools'] ?? [];
		$maxIterations  = $input['max_iterations'] ?? 10;

		// Resolve work directory if needed
		$workDir = null;
		try {
			$workDir = ExtractPathResolver::resolve( $input );
			$this->log_info( "Work directory resolved for general analysis", [ 'work_dir' => $workDir ] );
		} catch ( Exception $e ) {
			$this->log_warning( "Could not resolve work directory for general analysis", [
				'error' => $e->getMessage()
			] );
		}

		// Initialize tool registry if we have a work directory
		$toolRegistry = null;
		if ( $workDir ) {
			try {
				$toolRegistry = new ToolRegistry( $workDir );
			} catch ( Exception $e ) {
				$this->log_warning( "Could not initialize tool registry", [
					'error'    => $e->getMessage(),
					'work_dir' => $workDir
				] );
			}
		}

		// Convert tools to Ollama format
		$ollamaTools = array_map( function ( $tool ) {
			return [
				'type'     => 'function',
				'function' => $tool
			];
		}, $availableTools );

		// Simple analysis loop
		$iterations   = 0;
		$allToolCalls = [];

		while ( $iterations < $maxIterations ) {
			$iterations ++;

			$this->log_info( "General analysis iteration $iterations/$maxIterations" );

			// Call model with tools
			$request = [
				'model'    => $model,
				'messages' => $messages,
				'tools'    => $ollamaTools,
				'stream'   => false,
				'options'  => $modelOptions
			];

			try {
				$response = $this->callOllamaChat( $request );
			} catch ( Exception $e ) {
				$this->log_error( "Model call failed in general analysis", [ 'error' => $e->getMessage() ] );
				break;
			}

			$message    = $response['message'] ?? [];
			$messages[] = $message;

			// Check if model made tool calls
			if ( isset( $message['tool_calls'] ) && ! empty( $message['tool_calls'] ) && $toolRegistry ) {
				$this->log_info( "Executing tool calls in general analysis", [
					'count' => count( $message['tool_calls'] )
				] );

				$toolResults = $this->executeToolCalls(
					$message['tool_calls'],
					$toolRegistry,
					$allToolCalls,
					$iterations
				);

				// Add tool results to conversation
				$messages[] = [
					'role'    => 'tool',
					'content' => json_encode( $toolResults )
				];
			} else {
				// No tool calls, analysis is complete
				break;
			}
		}

		// Get final response content
		$finalContent = '';
		for ( $i = count( $messages ) - 1; $i >= 0; $i -- ) {
			if ( $messages[ $i ]['role'] === 'assistant' && isset( $messages[ $i ]['content'] ) ) {
				$finalContent = $messages[ $i ]['content'];
				break;
			}
		}

		// Return response
		NodeResponse::toolPrompt(
			$finalContent,
			$allToolCalls,
			$model,
			[
				'job_id'        => $input['job_id'] ?? null,
				'session_id'    => $input['session_id'] ?? null,
				'iterations'    => $iterations,
				'analysis_type' => 'general_security'
			]
		);
	}

}
