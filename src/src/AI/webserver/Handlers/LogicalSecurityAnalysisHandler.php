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

		// TODO: Implement tool-based investigation
		// This would use tools to investigate each potential vulnerability
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
}
