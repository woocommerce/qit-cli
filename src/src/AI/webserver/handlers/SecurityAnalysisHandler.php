<?php

namespace QIT_CLI\AI\WebServer\Handlers;

use Exception;
use QIT_CLI\AI\WebServer\ToolRegistry;
use QIT_CLI\AI\WebServer\ExtractPathResolver;
use QIT_CLI\AI\WebServer\NodeResponse;

/**
 * General Security Analysis Handler
 *
 * This class contains methods for general security analysis,
 * including AI tools handling, wp-call-graph integration, and enhanced prompts.
 */
class SecurityAnalysisHandler extends AbstractHandler {

	private string $coderModel = 'qwen2.5-coder:7b';
	private string $toolsModel = 'devstral:24b';
	private LogicalSecurityAnalysisHandler $logicalHandler;

	public function __construct( string $ollamaApiUrl ) {
		parent::__construct( $ollamaApiUrl );
		$this->logicalHandler = new LogicalSecurityAnalysisHandler( $ollamaApiUrl );
	}

	/**
	 * Handle general security analysis request
	 *
	 * @param array $input Request input
	 */
	public function handle( array $input ): void {
		// Check if this is a logical security discovery task
		if ( isset( $input['config']['analysis_mode'] ) &&
		     $input['config']['analysis_mode'] === 'logical_security_discovery' ) {

			$this->log_info( "Detected logical security discovery mode" );

			$jobId = $input['job_id'] ?? null;
			if ( ! $jobId ) {
				$this->log_error( "Missing job_id for discovery mode" );
				http_response_code( 400 );
				echo json_encode( [ 'error' => 'Missing required job_id parameter for discovery mode' ] );

				return;
			}

			$this->logicalHandler->handleDiscovery( $input, $jobId );

			return;
		}

		// Check if this is a file analysis task
		if ( isset( $input['task_phase'] ) && $input['task_phase'] === 'file_analysis' ) {
			$this->log_info( "Detected file analysis mode for logical security" );

			$jobId = $input['job_id'] ?? null;
			if ( ! $jobId ) {
				$this->log_error( "Missing job_id for file analysis mode" );
				http_response_code( 400 );
				echo json_encode( [ 'error' => 'Missing required job_id parameter for file analysis mode' ] );

				return;
			}

			$this->logicalHandler->handleFileAnalysis( $input, $jobId );

			return;
		}

		// Handle general security analysis with tools
		$this->handleGeneralSecurityAnalysis( $input );
	}

	/**
	 * Handle AI with tools for general security analysis
	 *
	 * @param array $input Request input
	 */
	private function handleGeneralSecurityAnalysis( array $input ): void {
		// Model configuration
		$coderModel = $input['coder_model'] ?? $this->coderModel;
		$toolsModel = $input['tools_model'] ?? $this->toolsModel;

		$messages       = $input['messages'];
		$availableTools = $input['tools'] ?? [];
		$maxIterations  = $input['max_iterations'] ?? 10;
		$sessionId      = $input['session_id'] ?? null;
		$zipUrl         = $input['zip_url'] ?? null;
		$staticContext  = $input['static_context'] ?? null;

		// Extract wp-call-graph results
		$wpCallGraphData = $this->extractWpCallGraphData( $input );

		$this->log_info( "Starting orchestrated AI analysis", [
			'coder_model'        => $coderModel,
			'tools_model'        => $toolsModel,
			'tools_count'        => count( $availableTools ),
			'max_iterations'     => $maxIterations,
			'session_id'         => $sessionId,
			'has_static_context' => ! empty( $staticContext ),
			'has_wp_call_graph'  => ! empty( $wpCallGraphData ),
			'input_keys'         => array_keys( $input )
		] );

		// Resolve work directory
		try {
			$workDir = ExtractPathResolver::resolve( $input );
			$this->log_info( "Work directory resolved", [ 'work_dir' => $workDir ] );
		} catch ( Exception $e ) {
			$this->log_error( "Path resolution failed", [
				'error'       => $e->getMessage(),
				'diagnostics' => ExtractPathResolver::getDiagnosticMessage( $input )
			] );

			http_response_code( 400 );
			echo json_encode( [
				'error'       => $e->getMessage(),
				'help'        => 'Ensure extract_path is provided from zip extraction step',
				'diagnostics' => ExtractPathResolver::getDiagnosticMessage( $input )
			] );

			return;
		}

		// Validate work directory
		if ( ! $this->validateWorkDirectory( $workDir ) ) {
			return;
		}

		// Initialize tool registry
		try {
			$toolRegistry = new ToolRegistry( $workDir );
		} catch ( Exception $e ) {
			$this->log_error( "Failed to initialize ToolRegistry", [
				'error'    => $e->getMessage(),
				'work_dir' => $workDir
			] );

			http_response_code( 500 );
			echo json_encode( [
				'error' => 'Failed to initialize tool registry: ' . $e->getMessage()
			] );

			return;
		}

		// Convert tools to Ollama format
		$ollamaTools = array_map( function ( $tool ) {
			return [
				'type'     => 'function',
				'function' => $tool
			];
		}, $availableTools );

		// Build initial context
		$coderSystemPrompt = $this->buildCoderInvestigationPrompt( $workDir, $staticContext, $wpCallGraphData );

		// Extract security context from messages
		$securityContext = $this->extractSecurityContext( $messages );

		$coderConversation = [
			[
				'role'    => 'system',
				'content' => $coderSystemPrompt
			],
			[
				'role'    => 'user',
				'content' => $securityContext . "\n\nAnalyze this security issue and tell me what specific information you need to investigate. " .
				             "Respond with a JSON object containing an 'investigations' array of specific tool calls you want me to make."
			]
		];

		// Run orchestration loop
		$results = $this->runOrchestrationLoop(
			$coderModel,
			$coderConversation,
			$toolRegistry,
			$wpCallGraphData
		);

		// Return the complete analysis
		$responseData = [
			'response'              => $results['final_analysis'],
			'model'                 => $coderModel,
			'iterations'            => $results['iterations'],
			'tool_calls'            => $results['tool_calls'],
			'wp_call_graph_context' => $staticContext,
			'orchestration'         => [
				'coder_model' => $coderModel,
				'tools_model' => $toolsModel,
				'loops'       => $results['iterations']
			],
			'timestamp'             => time()
		];

		echo json_encode( $responseData );
	}

	/**
	 * Extract wp-call-graph data from input
	 *
	 * @param array $input Request input
	 *
	 * @return array|null WP call graph data
	 */
	private function extractWpCallGraphData( array $input ): ?array {
		if ( isset( $input['context']['wp_call_graph'] ) ) {
			$this->log_info( "wp-call-graph data extracted from context" );

			return $input['context']['wp_call_graph'];
		}

		if ( isset( $input['dependencies']['wp_call_graph'] ) ) {
			$this->log_info( "wp-call-graph data extracted from dependencies" );

			return $input['dependencies']['wp_call_graph'];
		}

		if ( isset( $input['static_context']['wp_call_graph'] ) ) {
			$this->log_info( "wp-call-graph data extracted from static_context" );

			return $input['static_context']['wp_call_graph'];
		}

		$this->log_info( "No wp-call-graph data found in input" );

		return null;
	}

	/**
	 * Validate work directory
	 *
	 * @param string $workDir Work directory path
	 *
	 * @return bool True if valid
	 */
	private function validateWorkDirectory( string $workDir ): bool {
		if ( ! is_dir( $workDir ) ) {
			$this->log_error( "Work directory does not exist", [ 'work_dir' => $workDir ] );
			http_response_code( 400 );
			echo json_encode( [
				'error'    => 'Work directory does not exist',
				'work_dir' => $workDir
			] );

			return false;
		}

		$files    = scandir( $workDir );
		$phpFiles = array_filter( $files, function ( $file ) {
			return pathinfo( $file, PATHINFO_EXTENSION ) === 'php';
		} );

		$this->log_info( "Work directory contents", [
			'work_dir'     => $workDir,
			'total_files'  => count( $files ) - 2, // Exclude . and ..
			'php_files'    => count( $phpFiles ),
			'sample_files' => array_slice( $files, 2, 5 ) // Show first few files
		] );

		return true;
	}

	/**
	 * Extract security context from messages
	 *
	 * @param array $messages Messages array
	 *
	 * @return string Security context
	 */
	private function extractSecurityContext( array $messages ): string {
		foreach ( $messages as $msg ) {
			if ( isset( $msg['content'] ) && strpos( $msg['content'], 'SECURITY ISSUE' ) !== false ) {
				return $msg['content'];
			}
		}

		return '';
	}

	/**
	 * Run orchestration loop
	 *
	 * @param string $coderModel Coder model name
	 * @param array $coderConversation Conversation history
	 * @param ToolRegistry $toolRegistry Tool registry
	 * @param array|null $wpCallGraphData WP call graph data
	 *
	 * @return array Results
	 */
	private function runOrchestrationLoop(
		string $coderModel,
		array &$coderConversation,
		ToolRegistry $toolRegistry,
		?array $wpCallGraphData
	): array {
		$allToolResults          = [];
		$investigationComplete   = false;
		$orchestrationIterations = 0;
		$maxOrchestrationLoops   = 5;

		while ( ! $investigationComplete && $orchestrationIterations < $maxOrchestrationLoops ) {
			$orchestrationIterations ++;

			$this->log_info( "Orchestration iteration $orchestrationIterations" );

			// Ask coder what to investigate
			$coderRequest = [
				'model'    => $coderModel,
				'messages' => $coderConversation,
				'stream'   => false,
				'format'   => 'json'
			];

			try {
				$coderResponse = $this->callOllamaChat( $coderRequest );
			} catch ( Exception $e ) {
				$this->log_error( "Coder model failed", [ 'error' => $e->getMessage() ] );
				break;
			}

			// Parse investigation plan
			$investigationPlan = json_decode( $coderResponse['message']['content'] ?? '{}', true );

			if ( empty( $investigationPlan['investigations'] ) ) {
				$this->log_info( "No more investigations requested, moving to final analysis" );
				$investigationComplete = true;
				continue;
			}

			// Execute investigations
			$investigationResults = $this->executeInvestigations(
				$investigationPlan['investigations'],
				$toolRegistry
			);

			$allToolResults = array_merge( $allToolResults, $investigationResults );

			// Feed results back to coder
			$coderConversation[] = [
				'role'    => 'assistant',
				'content' => $coderResponse['message']['content']
			];

			$coderConversation[] = [
				'role'    => 'user',
				'content' => "Investigation results:\n" . json_encode( $investigationResults, JSON_PRETTY_PRINT ) . "\n\n" .
				             "Based on these results, do you need more information? If so, request more investigations. " .
				             "If you have enough information, respond with 'ANALYSIS_COMPLETE' and provide your security analysis."
			];
		}

		// Get final analysis
		$finalAnalysis = $this->getFinalAnalysis( $coderModel, $coderConversation );

		// Enhance results with wp-call-graph data if available
		if ( $wpCallGraphData && ! empty( $allToolResults ) ) {
			$finalAnalysis = $this->enhanceResultsWithWpCallGraph( $finalAnalysis, $wpCallGraphData );
		}

		return [
			'final_analysis' => $finalAnalysis,
			'iterations'     => $orchestrationIterations,
			'tool_calls'     => $allToolResults
		];
	}

	/**
	 * Execute investigations
	 *
	 * @param array $investigations Investigation requests
	 * @param ToolRegistry $toolRegistry Tool registry
	 *
	 * @return array Investigation results
	 */
	private function executeInvestigations( array $investigations, ToolRegistry $toolRegistry ): array {
		$results = [];

		foreach ( $investigations as $investigation ) {
			$toolName = $investigation['tool'] ?? '';
			$toolArgs = $investigation['args'] ?? [];

			$this->log_info( "Executing investigation", [
				'tool' => $toolName,
				'args' => $toolArgs
			] );

			try {
				$result    = $toolRegistry->execute_tool( $toolName, $toolArgs );
				$results[] = [
					'tool'   => $toolName,
					'args'   => $toolArgs,
					'result' => $result
				];
			} catch ( Exception $e ) {
				$this->log_error( "Tool execution failed", [
					'tool'  => $toolName,
					'error' => $e->getMessage()
				] );

				$results[] = [
					'tool'  => $toolName,
					'args'  => $toolArgs,
					'error' => $e->getMessage()
				];
			}
		}

		return $results;
	}

	/**
	 * Get final analysis from coder
	 *
	 * @param string $coderModel Model name
	 * @param array $coderConversation Conversation history
	 *
	 * @return string Final analysis
	 */
	private function getFinalAnalysis( string $coderModel, array &$coderConversation ): string {
		$finalPrompt = "Based on all the investigations, provide your final security analysis in JSON format. " .
		               "Include: vulnerability_confirmed (true/false), severity (critical/high/medium/low), " .
		               "exploitability, entry_points, and detailed_analysis.";

		$coderConversation[] = [
			'role'    => 'user',
			'content' => $finalPrompt
		];

		$finalRequest = [
			'model'    => $coderModel,
			'messages' => $coderConversation,
			'stream'   => false,
			'format'   => 'json'
		];

		try {
			$finalResponse = $this->callOllamaChat( $finalRequest );

			return $finalResponse['message']['content'] ?? '{}';
		} catch ( Exception $e ) {
			$this->log_error( "Final analysis failed", [ 'error' => $e->getMessage() ] );

			return json_encode( [
				'error'   => 'Analysis failed',
				'message' => $e->getMessage()
			] );
		}
	}

	/**
	 * Build coder investigation prompt
	 *
	 * @param string $workDir Work directory
	 * @param array|null $staticContext Static context
	 * @param array|null $wpCallGraphData WP call graph data
	 *
	 * @return string System prompt
	 */
	private function buildCoderInvestigationPrompt( string $workDir, ?array $staticContext, ?array $wpCallGraphData ): string {
		$prompt = "You are a WordPress security expert analyzing code ONLY within: $workDir\n\n";

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

		$prompt .= "SECURITY ISSUE CONTEXT:\n";

		if ( $wpCallGraphData ) {
			$prompt .= "\n=== WP-CALL-GRAPH FINDINGS ===\n";
			$prompt .= sprintf( "Target Function: %s\n", $wpCallGraphData['symbol'] ?? 'unknown' );

			if ( ! empty( $wpCallGraphData['trace'] ) ) {
				$prompt .= "\nDiscovered Hooks:\n";
				foreach ( $wpCallGraphData['trace'] as $trace ) {
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
		$prompt .= "\nREMINDER: All paths must be relative to the base directory: $workDir\n";

		return $prompt;
	}

	/**
	 * Get directory structure
	 *
	 * @param string $workDir Work directory
	 * @param int $maxDepth Maximum depth
	 *
	 * @return string Directory structure
	 */
	private function getDirectoryStructure( string $workDir, int $maxDepth = 2 ): string {
		if ( ! is_dir( $workDir ) ) {
			return "Directory not accessible: $workDir\n";
		}

		$cmd = sprintf( 'find %s -maxdepth %d -type f -name "*.php" | head -20',
			escapeshellarg( $workDir ),
			$maxDepth
		);

		exec( $cmd, $output );

		$structure = "Files found:\n";
		foreach ( $output as $file ) {
			$relativePath = str_replace( $workDir . '/', '', $file );
			$structure    .= "- $relativePath\n";
		}

		return $structure;
	}

	/**
	 * Enhance AI results with wp-call-graph context
	 *
	 * @param string $aiResults AI analysis results
	 * @param array $wpCallGraphData WP call graph data
	 *
	 * @return string Enhanced results
	 */
	private function enhanceResultsWithWpCallGraph( string $aiResults, array $wpCallGraphData ): string {
		$results = json_decode( $aiResults, true );
		if ( ! $results ) {
			return $aiResults;
		}

		// Add wp-call-graph context to the results
		$results['wp_call_graph_context'] = [
			'target_function' => $wpCallGraphData['result']['symbol'] ?? 'unknown',
			'entry_points'    => $wpCallGraphData['result']['trace'] ?? [],
			'analysis'        => $wpCallGraphData['result']['analysis'] ?? ''
		];

		return json_encode( $results, JSON_PRETTY_PRINT );
	}
}