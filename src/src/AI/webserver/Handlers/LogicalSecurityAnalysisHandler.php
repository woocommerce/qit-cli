<?php

namespace QIT_AI_Webserver\Handlers;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use QIT_AI_Webserver\Lib\ToolRegistry;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\Lib\FilePathResolver;
use QIT_AI_Webserver\NodeResponse;

/**
 * Logical Security Analysis Handler
 *
 * This class contains methods specifically for logical security analysis,
 * including vulnerability discovery and file analysis for security issues.
 */
class LogicalSecurityAnalysisHandler extends AbstractHandler {

	private string $coderModel = 'qwen2.5-coder:7b';
	private string $toolsModel = 'devstral:24b';

	/**
	 * Handle request based on input
	 *
	 * @param array $input Request input
	 */
	public function handle( array $input ): void {
		$jobId = $input['job_id'] ?? null;

		if ( ! $jobId ) {
			$this->log_error( "Missing job_id" );
			NodeResponse::error( 'Missing required job_id parameter', 400, [
				'job_id' => $jobId
			] );
		}

		// Check if this is a discovery or file analysis mode
		if ( isset( $input['config']['analysis_mode'] ) ) {
			if ( $input['config']['analysis_mode'] === 'logical_security_discovery' ) {
				$this->handleDiscovery( $input, $jobId );
			} elseif ( in_array( $input['config']['analysis_mode'], [ 'vulnerability_discovery', 'vulnerability_investigation' ] ) ) {
				$this->handleFileAnalysis( $input, $jobId );
			} else {
				NodeResponse::error( 'Invalid analysis_mode', 400, [
					'job_id'        => $jobId,
					'analysis_mode' => $input['config']['analysis_mode']
				] );
			}
		} else {
			// Default to discovery if no mode specified
			$this->handleDiscovery( $input, $jobId );
		}
	}

	/**
	 * Handle logical security discovery - explore codebase and return vulnerability findings
	 *
	 * @param array $input Request input
	 * @param string $jobId Job identifier
	 */
	public function handleDiscovery( array $input, string $jobId ): void {
		$this->log_info( "Starting logical security discovery", [
			'job_id'     => $jobId,
			'session_id' => $input['session_id'] ?? 'unknown'
		] );

		$maxIterations = $input['config']['max_iterations'] ?? 30;
		$sessionId     = $input['session_id'] ?? null;

		try {
			$workDir = ExtractPathResolver::resolve( $input );
			$this->log_info( "Work directory resolved for discovery", [ 'work_dir' => $workDir ] );
		} catch ( Exception $e ) {
			$this->log_error( "Path resolution failed for discovery", [
				'error'       => $e->getMessage(),
				'session_id'  => $sessionId,
				'diagnostics' => ExtractPathResolver::getDiagnosticMessage( $input )
			] );

			NodeResponse::error( $e->getMessage(), 400, [
				'job_id'      => $jobId,
				'session_id'  => $sessionId,
				'help'        => 'Ensure extract_path is provided from zip extraction step',
				'diagnostics' => ExtractPathResolver::getDiagnosticMessage( $input )
			] );
		}

		// Initialize tool registry
		$toolRegistry = new ToolRegistry( $workDir );

		// Define vulnerability patterns
		$searchPatterns = $this->getVulnerabilityPatterns();

		// Build system prompt
		$systemPrompt = $this->buildDiscoverySystemPrompt( $workDir, $searchPatterns );

		// Get initial directory listing
		$initialListing = $toolRegistry->execute_tool( 'list_files', [ 'directory' => '.' ] );

		// Start conversation
		$coderConversation = [
			[
				'role'    => 'system',
				'content' => $systemPrompt
			],
			[
				'role'    => 'system',
				'content' => "DIRECTORY CONTEXT:\n" . json_encode( $initialListing, JSON_PRETTY_PRINT ) .
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
		NodeResponse::mark( 'discovery_loop_start' );
		$allToolResults          = [];
		$orchestrationIterations = 0;

		while ( $orchestrationIterations < 5 ) {
			$orchestrationIterations ++;

			// Ask coder what to search for
			$coderRequest = [
				'model'    => $this->coderModel,
				'messages' => $coderConversation,
				'stream'   => false,
				'format'   => 'json'
			];

			$coderResponse = $this->callOllamaChat( $coderRequest );
			$searchPlan    = json_decode( $coderResponse['message']['content'] ?? '{}', true );

			if ( empty( $searchPlan['search_plan'] ) ) {
				break;
			}

			// Execute searches
			NodeResponse::mark( "search_iteration_$orchestrationIterations" );
			$searchResults  = $this->executeSearches( $searchPlan['search_plan'], $toolRegistry );
			$allToolResults = array_merge( $allToolResults, $searchResults );

			// Feed results back to coder
			$coderConversation[] = [
				'role'    => 'assistant',
				'content' => $coderResponse['message']['content']
			];

			$coderConversation[] = [
				'role'    => 'user',
				'content' => "Search results:\n" . json_encode( $searchResults, JSON_PRETTY_PRINT ) . "\n\n" .
				             "Analyze these results. Are there any security vulnerabilities? " .
				             "For each vulnerability found, provide: file, line, type, severity, and description. " .
				             "Also indicate if you need more searches. Respond with JSON."
			];
		}

		// Get final analysis
		NodeResponse::mark( 'final_analysis' );
		$finalAnalysis = $this->getFinalAnalysis( $coderConversation );

		$this->log_info( "Discovery completed, returning vulnerability data to Manager", [
			'vulnerabilities_found' => count( $finalAnalysis['vulnerabilities'] ?? [] ),
			'job_id'                => $jobId
		] );

		// Prepare response data for NodeResponse::toolPrompt
		$responseContent = json_encode( [
			'vulnerabilities'       => $finalAnalysis['vulnerabilities'] ?? [],
			'vulnerabilities_found' => count( $finalAnalysis['vulnerabilities'] ?? [] ),
			'summary'               => $finalAnalysis['summary'] ?? 'Discovery completed',
			'high_risk_count'       => count( array_filter(
				$finalAnalysis['vulnerabilities'] ?? [],
				fn( $v ) => in_array( $v['severity'] ?? '', [ 'critical', 'high' ] )
			) ),
			'analysis_metadata'     => [
				'total_files_analyzed' => count( $allToolResults ),
				'discovery_iterations' => $orchestrationIterations,
				'patterns_searched'    => array_keys( $searchPatterns )
			]
		] );

		// Use NodeResponse::toolPrompt for standardized response
		NodeResponse::toolPrompt(
			$responseContent,
			$allToolResults,
			$this->coderModel,
			[
				'job_id'     => $jobId,
				'session_id' => $sessionId,
				'iterations' => $orchestrationIterations
			]
		);
	}

	/**
	 * Handle file analysis
	 *
	 * @param array $input Request input
	 * @param string $jobId Job identifier
	 */
	public function handleFileAnalysis( array $input, string $jobId ): void {
		$this->log_info( "Starting file analysis for logical security", [
			'job_id'           => $jobId,
			'file_path'        => $input['file_path'] ?? 'unknown',
			'has_file_content' => isset( $input['file_content'] )
		] );

		if ( ! isset( $input['file_content'] ) ) {
			$this->log_error( "No file content provided for analysis" );
			NodeResponse::error( 'No file content provided', 400 );

			return;
		}

		// Initial vulnerability scan
		if ( $input['config']['analysis_mode'] === 'vulnerability_discovery' ) {
			$this->handleVulnerabilityDiscovery( $input );

			return;
		}

		// Deep investigation with tools
		if ( $input['config']['analysis_mode'] === 'vulnerability_investigation' ) {
			$this->handleVulnerabilityInvestigation( $input );

			return;
		}
	}

	/**
	 * Get vulnerability patterns
	 *
	 * @return array Patterns to search for
	 */
	private function getVulnerabilityPatterns(): array {
		return [
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
	}

	/**
	 * Build discovery system prompt
	 *
	 * @param string $workDir Work directory
	 * @param array $patterns Search patterns
	 *
	 * @return string System prompt
	 */
	private function buildDiscoverySystemPrompt( string $workDir, array $patterns ): string {
		$prompt = "You are a security researcher analyzing a WordPress plugin ONLY within: $workDir\n\n";

		$prompt .= "CRITICAL RESTRICTIONS:\n";
		$prompt .= "- You can ONLY access files within the base directory: $workDir\n";
		$prompt .= "- Do NOT attempt to access system files, parent directories, or other locations\n";
		$prompt .= "- All file paths must be relative to the base directory\n";
		$prompt .= "- This is an extracted plugin/theme - NOT a full WordPress installation\n";
		$prompt .= "- Do NOT assume WordPress paths like wp-content/plugins/ - use actual file structure\n\n";

		// Add actual directory structure
		$structure = $this->getDirectoryStructure( $workDir );
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
		$prompt .= "All file paths in your analysis must be relative to: $workDir\n";

		return $prompt;
	}

	/**
	 * Execute searches based on search plan
	 *
	 * @param array $searchPlan Search plan from AI
	 * @param ToolRegistry $toolRegistry Tool registry
	 *
	 * @return array Search results
	 */
	private function executeSearches( array $searchPlan, ToolRegistry $toolRegistry ): array {
		$searchResults = [];

		foreach ( $searchPlan as $search ) {
			$result = $toolRegistry->execute_tool( 'search_pattern', [
				'pattern'     => $search['pattern'],
				'max_results' => 50
			] );

			$searchResults[] = [
				'pattern' => $search['pattern'],
				'results' => $result
			];
		}

		return $searchResults;
	}

	/**
	 * Get final analysis from coder
	 *
	 * @param array $coderConversation Conversation history
	 *
	 * @return array Final analysis results
	 */
	private function getFinalAnalysis( array $coderConversation ): array {
		$finalPrompt = "Based on all your searches, provide a final list of discovered vulnerabilities. " .
		               "For each vulnerability include: type, severity (critical/high/medium/low), file, line, " .
		               "description, and potential impact. Respond with JSON: " .
		               '{"vulnerabilities": [...], "summary": "..."}';

		$coderConversation[] = [
			'role'    => 'user',
			'content' => $finalPrompt
		];

		$finalRequest = [
			'model'    => $this->coderModel,
			'messages' => $coderConversation,
			'stream'   => false,
			'format'   => 'json'
		];

		$finalResponse = $this->callOllamaChat( $finalRequest );

		return json_decode( $finalResponse['message']['content'] ?? '{}', true );
	}

	/**
	 * Handle vulnerability discovery mode
	 *
	 * @param array $input Request input
	 */
	private function handleVulnerabilityDiscovery( array $input ): void {
		$request = [
			'model'  => $this->coderModel,
			'prompt' => $input['prompt'],
			'stream' => false,
			'format' => 'json'
		];

		try {
			$response = $this->callOllamaGenerate( $request );
			$analysis = json_decode( $response['response'] ?? '{}', true );

			NodeResponse::success( [
				'potential_vulnerabilities' => $analysis['potential_vulnerabilities'] ?? [],
				'summary'                   => $analysis['summary'] ?? '',
				'file_analyzed'             => $input['file_path'] ?? null
			], [
				'model' => $this->coderModel
			] );

		} catch ( Exception $e ) {
			$this->log_error( "Initial vulnerability scan failed", [ 'error' => $e->getMessage() ] );
			NodeResponse::error( 'Analysis failed: ' . $e->getMessage(), 500 );
		}
	}

	/**
	 * Handle vulnerability investigation mode
	 *
	 * @param array $input Request input
	 */
	private function handleVulnerabilityInvestigation( array $input ): void {
		$potentialVulns = $input['dependencies']['initial_vulnerability_scan']['potential_vulnerabilities'] ?? [];

		if ( empty( $potentialVulns ) ) {
			NodeResponse::success( [
				'vulnerabilities' => [],
				'summary'         => 'No vulnerabilities to investigate'
			], [
				'model' => $this->coderModel
			] );

			return;
		}

		$this->log_info( "Starting vulnerability investigation", [
			'potential_vulnerabilities' => count( $potentialVulns ),
			'job_id'                    => $input['job_id'] ?? 'unknown'
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
		}

		// Initialize tool registry
		$toolRegistry = new ToolRegistry( $workDir );

		// Configuration for development - limit investigations per job
		$maxInvestigationsPerJob       = $input['config']['max_investigations_per_job'] ?? 1;
		$maxIterationsPerInvestigation = $input['config']['max_iterations_per_investigation'] ?? 5;

		$this->log_info( "Investigation limits", [
			'max_investigations_per_job'       => $maxInvestigationsPerJob,
			'max_iterations_per_investigation' => $maxIterationsPerInvestigation
		] );

		// Limit vulnerabilities to investigate based on configuration
		$vulnsToInvestigate = array_slice( $potentialVulns, 0, $maxInvestigationsPerJob );

		$investigationResults = [];
		$allToolCalls         = [];

		foreach ( $vulnsToInvestigate as $index => $vulnerability ) {
			$this->log_info( "Investigating vulnerability", [
				'index'         => $index + 1,
				'total'         => count( $vulnsToInvestigate ),
				'vulnerability' => $vulnerability['type'] ?? 'unknown',
				'file'          => $vulnerability['file'] ?? 'unknown'
			] );

			$investigationResult = $this->investigateVulnerability(
				$vulnerability,
				$workDir,
				$toolRegistry,
				$maxIterationsPerInvestigation
			);

			$investigationResults[] = $investigationResult;
			$allToolCalls           = array_merge( $allToolCalls, $investigationResult['tool_calls'] ?? [] );
		}

		// Compile final results
		$confirmedVulnerabilities = [];
		$investigationSummary     = [];

		foreach ( $investigationResults as $result ) {
			if ( $result['confirmed'] ?? false ) {
				$confirmedVulnerabilities[] = $result['vulnerability'];
			}
			$investigationSummary[] = $result['summary'] ?? 'Investigation completed';
		}

		$this->log_info( "Investigation completed", [
			'potential_vulnerabilities' => count( $potentialVulns ),
			'investigated'              => count( $vulnsToInvestigate ),
			'confirmed_vulnerabilities' => count( $confirmedVulnerabilities ),
			'total_tool_calls'          => count( $allToolCalls )
		] );

		// Use NodeResponse::toolPrompt for standardized response
		$responseContent = json_encode( [
			'vulnerabilities'           => $confirmedVulnerabilities,
			'vulnerabilities_confirmed' => count( $confirmedVulnerabilities ),
			'summary'                   => implode( "\n", $investigationSummary ),
			'investigation_metadata'    => [
				'potential_vulnerabilities'        => count( $potentialVulns ),
				'investigated'                     => count( $vulnsToInvestigate ),
				'confirmed'                        => count( $confirmedVulnerabilities ),
				'total_tool_calls'                 => count( $allToolCalls ),
				'max_investigations_per_job'       => $maxInvestigationsPerJob,
				'max_iterations_per_investigation' => $maxIterationsPerInvestigation
			]
		] );

		NodeResponse::toolPrompt(
			$responseContent,
			$allToolCalls,
			$this->coderModel,
			[
				'job_id'      => $input['job_id'] ?? null,
				'session_id'  => $input['session_id'] ?? null,
				'coder_model' => $this->coderModel,
				'tools_model' => $this->toolsModel
			]
		);
	}

	/**
	 * Investigate a single vulnerability using coder-tools loop
	 *
	 * @param array $vulnerability Vulnerability to investigate
	 * @param string $workDir Work directory
	 * @param ToolRegistry $toolRegistry Tool registry
	 * @param int $maxIterations Maximum iterations for investigation
	 *
	 * @return array Investigation result
	 */
	private function investigateVulnerability(
		array $vulnerability,
		string $workDir,
		ToolRegistry $toolRegistry,
		int $maxIterations
	): array {
		$this->log_info( "Starting vulnerability investigation loop", [
			'vulnerability'  => $vulnerability['type'] ?? 'unknown',
			'file'           => $vulnerability['file'] ?? 'unknown',
			'max_iterations' => $maxIterations
		] );

		// Build initial conversation for the coder model
		$coderConversation = [
			[
				'role'    => 'system',
				'content' => $this->buildVulnerabilityInvestigationPrompt( $vulnerability, $workDir )
			],
			[
				'role'    => 'user',
				'content' => $this->buildInitialInvestigationPrompt( $vulnerability )
			]
		];

		// Run the coder-tools orchestration loop
		$loopResult = $this->runVulnerabilityInvestigationLoop(
			$this->coderModel,
			$this->toolsModel,
			$coderConversation,
			$toolRegistry,
			$maxIterations
		);

		// Parse the final response to determine if vulnerability is confirmed
		$finalResponse = $loopResult['final_response'] ?? '';
		$confirmed     = $this->parseVulnerabilityConfirmation( $finalResponse );

		// Build enhanced vulnerability data if confirmed
		$enhancedVulnerability = $vulnerability;
		if ( $confirmed ) {
			$enhancedVulnerability = $this->enhanceVulnerabilityData( $vulnerability, $finalResponse, $loopResult['tool_calls'] );
		}

		return [
			'vulnerability'  => $enhancedVulnerability,
			'confirmed'      => $confirmed,
			'summary'        => $this->extractInvestigationSummary( $finalResponse ),
			'tool_calls'     => $loopResult['tool_calls'] ?? [],
			'iterations'     => $loopResult['iterations'] ?? 0,
			'execution_time' => $loopResult['execution_time'] ?? 0,
			'final_response' => $finalResponse
		];
	}

	/**
	 * Run vulnerability investigation loop between coder and tools models
	 *
	 * @param string $coderModel Coder model for reasoning
	 * @param string $toolsModel Tools model for execution
	 * @param array $coderConversation Coder conversation history
	 * @param ToolRegistry $toolRegistry Tool registry
	 * @param int $maxIterations Maximum iterations
	 *
	 * @return array Results
	 */
	private function runVulnerabilityInvestigationLoop(
		string $coderModel,
		string $toolsModel,
		array &$coderConversation,
		ToolRegistry $toolRegistry,
		int $maxIterations
	): array {
		$startTime    = microtime( true );
		$allToolCalls = [];
		$iterations   = 0;

		while ( $iterations < $maxIterations ) {
			$iterations ++;

			$this->log_info( "Investigation iteration $iterations" );

			// Step 1: Ask coder model what information it needs
			$coderRequest = [
				'model'    => $coderModel,
				'messages' => $coderConversation,
				'stream'   => false,
				'format'   => 'json' // Request structured response
			];

			try {
				$coderResponse = $this->callOllamaChat( $coderRequest );
			} catch ( Exception $e ) {
				$this->log_error( "Coder model failed during investigation", [ 'error' => $e->getMessage() ] );
				break;
			}

			// Parse coder's request for tool usage
			$coderMessage = $coderResponse['message'] ?? [];
			$coderContent = $coderMessage['content'] ?? '';

			// Try to parse as JSON first
			$toolRequests = json_decode( $coderContent, true );

			$this->log_info( "Coder model response parsing", [
				'content_length' => strlen( $coderContent ),
				'json_error' => json_last_error(),
				'json_error_msg' => json_last_error_msg(),
				'parsed_successfully' => json_last_error() === JSON_ERROR_NONE,
				'content_preview' => substr( $coderContent, 0, 200 )
			] );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				// If not JSON, check if it's a final response
				if ( $this->isInvestigationComplete( $coderContent ) ) {
					$this->log_info( "Investigation complete - coder provided final analysis" );
					$coderConversation[] = $coderMessage;
					break;
				}

				// Otherwise, prompt for structured response
				$coderConversation[] = $coderMessage;
				$coderConversation[] = [
					'role'    => 'user',
					'content' => 'Please provide your investigation requests in JSON format: {"tool_requests": [{"tool": "tool_name", "args": {...}}]} or {"investigation_complete": true, "confirmed": true/false, "analysis": "your detailed analysis"}'
				];
				continue;
			}

			// Add coder's response to conversation
			$coderConversation[] = $coderMessage;

			// Check if coder is done with investigation
			if ( isset( $toolRequests['investigation_complete'] ) ) {
				$this->log_info( "Investigation complete - coder finished analysis" );
				break;
			}

			if ( ! isset( $toolRequests['tool_requests'] ) || empty( $toolRequests['tool_requests'] ) ) {
				$this->log_info( "No tool requests from coder - investigation may be complete" );
				break;
			}

			$this->log_info( "Processing tool requests from coder", [
				'tool_requests_count' => count( $toolRequests['tool_requests'] ),
				'tool_requests' => $toolRequests['tool_requests']
			] );

			// Step 2: Execute tools using the tools model
			$toolResults = $this->executeToolsWithModel(
				$toolsModel,
				$toolRequests['tool_requests'],
				$toolRegistry
			);

			// Track all tool calls
			foreach ( $toolResults as $result ) {
				$allToolCalls[] = [
					'iteration' => $iterations,
					'tool'      => $result['tool'],
					'args'      => $result['args'],
					'result'    => $result['result']
				];
			}

			// Step 3: Feed results back to coder
			$toolResultsSummary  = $this->formatToolResults( $toolResults );
			$coderConversation[] = [
				'role'    => 'user',
				'content' => "Tool execution results:\n" . $toolResultsSummary . "\n\nBased on these results, what would you like to investigate next? Request more tools or provide your final vulnerability analysis?"
			];
		}

		// Extract final response
		$finalResponse = $this->extractFinalInvestigationResponse( $coderConversation );

		$executionTime = round( ( microtime( true ) - $startTime ) * 1000 );

		return [
			'final_response' => $finalResponse,
			'tool_calls'     => $allToolCalls,
			'iterations'     => $iterations,
			'execution_time' => $executionTime
		];
	}

	/**
	 * Execute tools using the tools model (adapted from PromptWithToolsHandler)
	 *
	 * @param string $toolsModel Model to use for tool execution
	 * @param array $toolRequests Tool requests from coder
	 * @param ToolRegistry $toolRegistry Tool registry
	 *
	 * @return array Tool results
	 */
	private function executeToolsWithModel(
		string $toolsModel,
		array $toolRequests,
		ToolRegistry $toolRegistry
	): array {
		$results = [];

		// Convert tool requests to Ollama tool format
		$tools = $this->getToolDefinitionsForRequests( $toolRequests );

		// Build messages for tool model
		$toolMessages = [
			[
				'role'    => 'system',
				'content' => 'You are a security analysis tool execution assistant. Execute the requested tools to help investigate potential vulnerabilities.'
			],
			[
				'role'    => 'user',
				'content' => 'Please execute these security investigation tools: ' . json_encode( $toolRequests )
			]
		];

		// Call tools model
		$toolRequest = [
			'model'    => $toolsModel,
			'messages' => $toolMessages,
			'tools'    => $tools,
			'stream'   => false
		];

		try {
			$toolResponse = $this->callOllamaChat( $toolRequest );

			// Extract tool calls from response
			$toolCalls = $toolResponse['message']['tool_calls'] ?? [];

			$this->log_info( "Tools model response received", [
				'tool_calls_count' => count( $toolCalls ),
				'has_tool_calls' => !empty( $toolCalls ),
				'response_keys' => array_keys( $toolResponse['message'] ?? [] )
			] );

			// Execute each tool call
			foreach ( $toolCalls as $toolCall ) {
				$toolName = $toolCall['function']['name'] ?? '';
				$toolArgs = $toolCall['function']['arguments'] ?? '{}';

				// Parse arguments if string
				if ( is_string( $toolArgs ) ) {
					$toolArgs = json_decode( $toolArgs, true ) ?? [];
				}

				$this->log_info( "Executing security investigation tool", [
					'tool' => $toolName,
					'args' => $toolArgs
				] );

				try {
					$result    = $toolRegistry->execute_tool( $toolName, $toolArgs );
					$results[] = [
						'tool'    => $toolName,
						'args'    => $toolArgs,
						'result'  => $result,
						'success' => ! isset( $result['error'] )
					];
				} catch ( Exception $e ) {
					$results[] = [
						'tool'    => $toolName,
						'args'    => $toolArgs,
						'result'  => [ 'error' => $e->getMessage() ],
						'success' => false
					];
				}
			}
		} catch ( Exception $e ) {
			$this->log_error( "Tools model failed during investigation", [ 'error' => $e->getMessage() ] );

			// Fallback: execute tools directly without model
			foreach ( $toolRequests as $request ) {
				$toolName = $request['tool'] ?? '';
				$toolArgs = $request['args'] ?? [];

				try {
					$result    = $toolRegistry->execute_tool( $toolName, $toolArgs );
					$results[] = [
						'tool'    => $toolName,
						'args'    => $toolArgs,
						'result'  => $result,
						'success' => ! isset( $result['error'] )
					];
				} catch ( Exception $e ) {
					$results[] = [
						'tool'    => $toolName,
						'args'    => $toolArgs,
						'result'  => [ 'error' => $e->getMessage() ],
						'success' => false
					];
				}
			}
		}

		return $results;
	}

	/**
	 * Get directory structure
	 *
	 * @param string $workDir Work directory
	 * @param int $maxDepth Maximum depth
	 *
	 * @return string Directory structure as string
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
	 * Build vulnerability investigation system prompt
	 *
	 * @param array $vulnerability Vulnerability to investigate
	 * @param string $workDir Work directory
	 *
	 * @return string System prompt
	 */
	private function buildVulnerabilityInvestigationPrompt( array $vulnerability, string $workDir ): string {
		$prompt = "You are a security researcher investigating a potential vulnerability in a WordPress plugin.\n\n";

		$prompt .= "WORK DIRECTORY: $workDir\n";
		$prompt .= "All file operations are relative to this directory.\n\n";

		$prompt .= "VULNERABILITY TO INVESTIGATE:\n";
		$prompt .= "- Type: " . ( $vulnerability['type'] ?? 'Unknown' ) . "\n";
		$prompt .= "- File: " . ( $vulnerability['file'] ?? 'Unknown' ) . "\n";
		$prompt .= "- Line: " . ( $vulnerability['line'] ?? 'Unknown' ) . "\n";
		$prompt .= "- Description: " . ( $vulnerability['description'] ?? 'No description' ) . "\n";
		$prompt .= "- Severity: " . ( $vulnerability['severity'] ?? 'Unknown' ) . "\n\n";

		$prompt .= "AVAILABLE TOOLS:\n";
		$prompt .= "- read_file: Read file contents\n";
		$prompt .= "- search_pattern: Search for patterns in files\n";
		$prompt .= "- list_files: List files in directories\n\n";

		$prompt .= "YOUR INVESTIGATION GOALS:\n";
		$prompt .= "1. Confirm if this is a real vulnerability\n";
		$prompt .= "2. Understand the attack vector and exploitability\n";
		$prompt .= "3. Identify the root cause and affected code\n";
		$prompt .= "4. Assess the actual impact and severity\n";
		$prompt .= "5. Look for similar patterns in the codebase\n\n";

		$prompt .= "INVESTIGATION APPROACH:\n";
		$prompt .= "- Use tools to gather information about the vulnerability\n";
		$prompt .= "- Look for call stacks, data flow, and entry points\n";
		$prompt .= "- Check for proper input validation and sanitization\n";
		$prompt .= "- Verify if the vulnerability is actually exploitable\n";
		$prompt .= "- Search for similar patterns that might indicate more vulnerabilities\n\n";

		$prompt .= "RESPONSE FORMAT:\n";
		$prompt .= "Request tools using JSON: {\"tool_requests\": [{\"tool\": \"tool_name\", \"args\": {...}}]}\n";
		$prompt .= "When investigation is complete: {\"investigation_complete\": true, \"confirmed\": true/false, \"analysis\": \"detailed analysis\"}\n\n";

		$prompt .= "Start by examining the reported vulnerability location and understanding the context.";

		return $prompt;
	}

	/**
	 * Build initial investigation prompt
	 *
	 * @param array $vulnerability Vulnerability to investigate
	 *
	 * @return string Initial prompt
	 */
	private function buildInitialInvestigationPrompt( array $vulnerability ): string {
		$prompt = "Please investigate this potential vulnerability:\n\n";
		$prompt .= "Type: " . ( $vulnerability['type'] ?? 'Unknown' ) . "\n";
		$prompt .= "File: " . ( $vulnerability['file'] ?? 'Unknown' ) . "\n";
		$prompt .= "Line: " . ( $vulnerability['line'] ?? 'Unknown' ) . "\n";
		$prompt .= "Description: " . ( $vulnerability['description'] ?? 'No description' ) . "\n\n";

		$prompt .= "Start by reading the file to understand the context and then investigate further. ";
		$prompt .= "What tools do you need to begin your investigation?";

		return $prompt;
	}

	/**
	 * Check if investigation is complete
	 *
	 * @param string $content Response content
	 *
	 * @return bool True if investigation is complete
	 */
	private function isInvestigationComplete( string $content ): bool {
		$indicators = [
			'investigation complete',
			'investigation_complete',
			'final analysis',
			'confirmed vulnerability',
			'not a vulnerability',
			'false positive',
			'investigation finished'
		];

		$lowerContent = strtolower( $content );
		foreach ( $indicators as $indicator ) {
			if ( strpos( $lowerContent, $indicator ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Parse vulnerability confirmation from response
	 *
	 * @param string $response Final response
	 *
	 * @return bool True if vulnerability is confirmed
	 */
	private function parseVulnerabilityConfirmation( string $response ): bool {
		$lowerResponse = strtolower( $response );

		// Look for explicit confirmation
		if ( strpos( $lowerResponse, '"confirmed": true' ) !== false ) {
			return true;
		}

		// Look for confirmation indicators
		$confirmationIndicators = [
			'confirmed vulnerability',
			'vulnerability confirmed',
			'this is a vulnerability',
			'exploitable',
			'security risk',
			'critical vulnerability',
			'high severity'
		];

		foreach ( $confirmationIndicators as $indicator ) {
			if ( strpos( $lowerResponse, $indicator ) !== false ) {
				return true;
			}
		}

		// Look for negation indicators
		$negationIndicators = [
			'"confirmed": false',
			'not a vulnerability',
			'false positive',
			'not exploitable',
			'properly sanitized',
			'no security risk'
		];

		foreach ( $negationIndicators as $indicator ) {
			if ( strpos( $lowerResponse, $indicator ) !== false ) {
				return false;
			}
		}

		// Default to false if unclear
		return false;
	}

	/**
	 * Enhance vulnerability data with investigation results
	 *
	 * @param array $vulnerability Original vulnerability
	 * @param string $finalResponse Final investigation response
	 * @param array $toolCalls Tool calls made during investigation
	 *
	 * @return array Enhanced vulnerability data
	 */
	private function enhanceVulnerabilityData( array $vulnerability, string $finalResponse, array $toolCalls ): array {
		$enhanced = $vulnerability;

		// Try to extract enhanced data from the final response
		$responseData = json_decode( $finalResponse, true );
		if ( is_array( $responseData ) ) {
			// Update severity if provided
			if ( isset( $responseData['severity'] ) ) {
				$enhanced['severity'] = $responseData['severity'];
			}

			// Update description with investigation findings
			if ( isset( $responseData['analysis'] ) ) {
				$enhanced['investigation_analysis'] = $responseData['analysis'];
			}

			// Add attack vector if identified
			if ( isset( $responseData['attack_vector'] ) ) {
				$enhanced['attack_vector'] = $responseData['attack_vector'];
			}

			// Add impact assessment
			if ( isset( $responseData['impact'] ) ) {
				$enhanced['impact'] = $responseData['impact'];
			}
		}

		// Add investigation metadata
		$enhanced['investigation_metadata'] = [
			'tool_calls_count'   => count( $toolCalls ),
			'tools_used'         => array_unique( array_column( $toolCalls, 'tool' ) ),
			'investigation_date' => date( 'Y-m-d H:i:s' )
		];

		return $enhanced;
	}

	/**
	 * Extract investigation summary from response
	 *
	 * @param string $response Final response
	 *
	 * @return string Investigation summary
	 */
	private function extractInvestigationSummary( string $response ): string {
		// Try to parse as JSON first
		$responseData = json_decode( $response, true );
		if ( is_array( $responseData ) && isset( $responseData['analysis'] ) ) {
			return $responseData['analysis'];
		}

		// If not JSON, return truncated response
		return strlen( $response ) > 500 ? substr( $response, 0, 500 ) . '...' : $response;
	}

	/**
	 * Extract final investigation response from conversation
	 *
	 * @param array $conversation Conversation history
	 *
	 * @return string Final response
	 */
	private function extractFinalInvestigationResponse( array $conversation ): string {
		// Get the last assistant message
		for ( $i = count( $conversation ) - 1; $i >= 0; $i -- ) {
			if ( $conversation[ $i ]['role'] === 'assistant' ) {
				return $conversation[ $i ]['content'] ?? '';
			}
		}

		return 'Investigation completed without final response';
	}

	/**
	 * Get tool definitions for requests (adapted from PromptWithToolsHandler)
	 *
	 * @param array $toolRequests Tool requests
	 *
	 * @return array Tool definitions
	 */
	private function getToolDefinitionsForRequests( array $toolRequests ): array {
		$allTools       = $this->getAllToolDefinitions();
		$requestedTools = [];

		foreach ( $toolRequests as $request ) {
			$toolName = $request['tool'] ?? '';
			if ( isset( $allTools[ $toolName ] ) ) {
				$requestedTools[] = $allTools[ $toolName ];
			}
		}

		return $requestedTools;
	}

	/**
	 * Get all tool definitions (adapted from PromptWithToolsHandler)
	 *
	 * @return array All tool definitions
	 */
	private function getAllToolDefinitions(): array {
		return [
			'read_file'      => [
				'type'     => 'function',
				'function' => [
					'name'        => 'read_file',
					'description' => 'Read the contents of a file',
					'parameters'  => [
						'type'       => 'object',
						'properties' => [
							'file_path' => [
								'type'        => 'string',
								'description' => 'Path to the file to read (relative to work directory)'
							]
						],
						'required'   => [ 'file_path' ]
					]
				]
			],
			'search_pattern' => [
				'type'     => 'function',
				'function' => [
					'name'        => 'search_pattern',
					'description' => 'Search for a pattern in files',
					'parameters'  => [
						'type'       => 'object',
						'properties' => [
							'pattern'     => [
								'type'        => 'string',
								'description' => 'Pattern to search for (regex supported)'
							],
							'max_results' => [
								'type'        => 'integer',
								'description' => 'Maximum number of results to return',
								'default'     => 20
							]
						],
						'required'   => [ 'pattern' ]
					]
				]
			],
			'list_files'     => [
				'type'     => 'function',
				'function' => [
					'name'        => 'list_files',
					'description' => 'List files in a directory',
					'parameters'  => [
						'type'       => 'object',
						'properties' => [
							'directory' => [
								'type'        => 'string',
								'description' => 'Directory to list (relative to work directory)',
								'default'     => '.'
							],
							'recursive' => [
								'type'        => 'boolean',
								'description' => 'Whether to list files recursively',
								'default'     => false
							]
						]
					]
				]
			]
		];
	}

	/**
	 * Format tool results for display (adapted from PromptWithToolsHandler)
	 *
	 * @param array $toolResults Tool results
	 *
	 * @return string Formatted results
	 */
	private function formatToolResults( array $toolResults ): string {
		$formatted = '';

		foreach ( $toolResults as $result ) {
			$toolName   = $result['tool'] ?? 'unknown';
			$success    = $result['success'] ?? false;
			$toolResult = $result['result'] ?? [];

			$formatted .= "=== $toolName ===\n";
			$formatted .= "Status: " . ( $success ? 'Success' : 'Failed' ) . "\n";

			if ( isset( $toolResult['error'] ) ) {
				$formatted .= "Error: " . $toolResult['error'] . "\n";
			} else {
				$formatted .= $this->formatToolResult( $toolResult );
			}

			$formatted .= "\n";
		}

		return $formatted;
	}

	/**
	 * Format individual tool result (adapted from PromptWithToolsHandler)
	 *
	 * @param array $result Tool result
	 *
	 * @return string Formatted result
	 */
	private function formatToolResult( array $result ): string {
		if ( isset( $result['content'] ) ) {
			// File content
			$content = $result['content'];

			return "Content:\n" . $this->truncateContent( $content, 2000 ) . "\n";
		}

		if ( isset( $result['matches'] ) ) {
			// Search results
			return $this->formatSearchResults( $result['matches'] );
		}

		if ( isset( $result['files'] ) ) {
			// File listing
			$files = $result['files'];

			return "Files (" . count( $files ) . "):\n" . implode( "\n", array_slice( $files, 0, 50 ) ) . "\n";
		}

		// Generic result
		return json_encode( $result, JSON_PRETTY_PRINT ) . "\n";
	}

	/**
	 * Format search results (adapted from PromptWithToolsHandler)
	 *
	 * @param array $results Search results
	 *
	 * @return string Formatted results
	 */
	private function formatSearchResults( array $results ): string {
		$formatted = "Matches (" . count( $results ) . "):\n";

		foreach ( array_slice( $results, 0, 20 ) as $match ) {
			$file    = $match['file'] ?? 'unknown';
			$line    = $match['line'] ?? 'unknown';
			$content = $match['content'] ?? '';

			$formatted .= "$file:$line: " . trim( $content ) . "\n";
		}

		return $formatted;
	}

	/**
	 * Truncate content to maximum length (adapted from PromptWithToolsHandler)
	 *
	 * @param string $content Content to truncate
	 * @param int $maxLength Maximum length
	 *
	 * @return string Truncated content
	 */
	private function truncateContent( string $content, int $maxLength = 1000 ): string {
		if ( strlen( $content ) <= $maxLength ) {
			return $content;
		}

		return substr( $content, 0, $maxLength ) . "\n... [truncated] ...";
	}
}
