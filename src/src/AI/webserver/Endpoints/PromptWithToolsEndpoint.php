<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use QIT_AI_Webserver\Lib\ToolRegistry;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\NodeResponse;

/**
 * Prompt With Tools Endpoint
 *
 * This endpoint implements a single-model approach for AI analysis with tools.
 * The model handles both reasoning and tool execution.
 */
class PromptWithToolsEndpoint extends AbstractEndpoint {
	/**
	 * Get the route for this endpoint
	 *
	 * @return string The route path
	 */
	public function get_route(): string {
		return '/prompt-with-tools';
	}

	/**
	 * Handle AI request with tools using single model approach
	 *
	 * @param array $input Request input data
	 *
	 * @return void Outputs JSON response
	 */
	public function handle( array $input ): void {
		$this->log_info( "Processing prompt with tools request" );

		// Validate input
		if ( ! isset( $input['prompt'] ) || ! isset( $input['model'] ) ) {
			$this->log_error( "Missing required parameters", [
				'missing' => ! isset( $input['prompt'] ) ? 'prompt' : 'model',
				'uri'     => $_SERVER['REQUEST_URI'] ?? 'unknown'
			] );
			NodeResponse::error( 'Missing prompt or model parameter', 400, [
				'job_id' => $input['job_id'] ?? null
			] );
		}

		try {
			// Extract parameters
			$userPrompt     = $input['prompt'];
			$model          = $input['model'];
			$jobId          = $input['job_id'] ?? null;
			$maxIterations  = $input['max_iterations'] ?? 10;
			$availableTools = $input['available_tools'] ?? [ 'read_file', 'search_pattern', 'list_files' ];
			$sessionId      = $input['session_id'] ?? null;

			// Resolve work directory using ExtractPathResolver
			NodeResponse::mark( 'path_resolution' );
			try {
				$workDir = ExtractPathResolver::resolve( $input );
				$this->log_info( "Work directory resolved", [ 'work_dir' => $workDir ] );
			} catch ( Exception $e ) {
				$this->log_error( "Path resolution failed", [
					'error'       => $e->getMessage(),
					'diagnostics' => ExtractPathResolver::getDiagnosticMessage( $input )
				] );

				NodeResponse::error( $e->getMessage(), 400, [
					'job_id'      => $jobId,
					'help'        => 'Ensure extract_path is provided from zip extraction step',
					'diagnostics' => ExtractPathResolver::getDiagnosticMessage( $input )
				] );
			}

			// Initialize tool registry
			NodeResponse::mark( 'tool_registry_init' );
			$toolRegistry = new ToolRegistry( $workDir );

			// Build system prompt for model
			$systemPrompt = $this->buildSystemPrompt( $workDir, $availableTools );

			// Initialize conversation
			$conversation = [
				[
					'role'    => 'system',
					'content' => $systemPrompt
				],
				[
					'role'    => 'user',
					'content' => $userPrompt
				]
			];

			// Execute orchestration loop
			NodeResponse::mark( 'orchestration_loop' );
			$result = $this->runOrchestrationLoop(
				$model,
				$conversation,
				$toolRegistry,
				$maxIterations
			);

			$this->log_info( "Orchestrated request completed successfully", [
				'iterations' => $result['iterations'],
				'tool_calls' => count( $result['tool_calls'] )
			] );

			// Use NodeResponse::toolPrompt for standardized response
			NodeResponse::toolPrompt(
				$result['final_response'],
				$result['tool_calls'],
				$model, // Primary model used
				[
					'job_id'         => $jobId,
					'model'          => $model,
					'iterations'     => $result['iterations'],
					'session_id'     => $sessionId,
					'execution_time' => $result['execution_time'] ?? null
				]
			);

		} catch ( Exception $e ) {
			$this->handleError( $e, [
				'job_id'   => $jobId ?? 'unknown',
				'job_type' => 'prompt_with_tools'
			] );
		}
	}

	/**
	 * Run orchestration loop using single model for both reasoning and tool execution
	 *
	 * @param string $model Model for both reasoning and tool execution
	 * @param array $conversation Conversation history
	 * @param ToolRegistry $toolRegistry Tool registry
	 * @param int $maxIterations Maximum iterations
	 *
	 * @return array Results
	 */
	private function runOrchestrationLoop(
		string $model,
		array &$conversation,
		ToolRegistry $toolRegistry,
		int $maxIterations
	): array {
		$startTime    = microtime( true );
		$allToolCalls = [];
		$iterations   = 0;

		while ( $iterations < $maxIterations ) {
			$iterations ++;

			$this->log_info( "Orchestration iteration $iterations" );

			// Step 1: Ask model what information it needs
			$modelRequest = [
				'model'    => $model,
				'messages' => $conversation,
				'stream'   => false,
				'format'   => 'json' // Request structured response
			];

			try {
				$modelResponse = $this->callOllamaChat( $modelRequest );
			} catch ( Exception $e ) {
				$this->log_error( "Model failed", [ 'error' => $e->getMessage() ] );
				break;
			}

			// Parse model's request for tool usage
			$modelMessage = $modelResponse['message'] ?? [];
			$modelContent = $modelMessage['content'] ?? '';

			// Try to parse as JSON first
			$toolRequests = json_decode( $modelContent, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				// If not JSON, check if it's a final response
				if ( $this->isFinaResponse( $modelContent ) ) {
					$this->log_info( "Model provided final response" );
					$conversation[] = $modelMessage;
					break;
				}

				// Otherwise, prompt for structured response
				$conversation[] = $modelMessage;
				$conversation[] = [
					'role'    => 'user',
					'content' => 'Please provide your tool requests in JSON format: {"tool_requests": [{"tool": "tool_name", "args": {...}}]} or {"final_response": "your analysis"}'
				];
				continue;
			}

			// Add model's response to conversation
			$conversation[] = $modelMessage;

			// Check if model wants to use tools or is done
			if ( isset( $toolRequests['final_response'] ) ) {
				$this->log_info( "Model provided final response" );
				break;
			}

			if ( ! isset( $toolRequests['tool_requests'] ) || empty( $toolRequests['tool_requests'] ) ) {
				$this->log_info( "No tool requests from model" );
				break;
			}

			// Step 2: Execute tools using the same model
			$toolResults = $this->executeToolsWithModel(
				$model,
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

			// Step 3: Feed results back to model
			$toolResultsSummary = $this->formatToolResults( $toolResults );
			$conversation[]     = [
				'role'    => 'user',
				'content' => "Tool execution results:\n" . $toolResultsSummary . "\n\nBased on these results, what would you like to do next? Request more tools or provide your final analysis?"
			];
		}

		// Extract final response
		$finalResponse = $this->extractFinalResponse( $conversation );

		$executionTime = round( ( microtime( true ) - $startTime ) * 1000 );

		return [
			'final_response' => $finalResponse,
			'tool_calls'     => $allToolCalls,
			'iterations'     => $iterations,
			'execution_time' => $executionTime
		];
	}

	/**
	 * Execute tools using the model
	 *
	 * @param string $model Model to use for tool execution
	 * @param array $toolRequests Tool requests from model
	 * @param ToolRegistry $toolRegistry Tool registry
	 *
	 * @return array Tool results
	 */
	private function executeToolsWithModel(
		string $model,
		array $toolRequests,
		ToolRegistry $toolRegistry
	): array {
		$results = [];

		// Convert tool requests to Ollama tool format
		$tools = $this->getToolDefinitionsForRequests( $toolRequests );

		// Build messages for model
		$toolMessages = [
			[
				'role'    => 'system',
				'content' => 'You are a tool execution assistant. Execute the requested tools and return the results.'
			],
			[
				'role'    => 'user',
				'content' => 'Please execute these tools: ' . json_encode( $toolRequests )
			]
		];

		// Call model for tool execution
		$toolRequest = [
			'model'    => $model,
			'messages' => $toolMessages,
			'tools'    => $tools,
			'stream'   => false
		];

		try {
			$toolResponse = $this->callOllamaChat( $toolRequest );

			// Extract tool calls from response
			$toolCalls = $toolResponse['message']['tool_calls'] ?? [];

			// Execute each tool call
			foreach ( $toolCalls as $toolCall ) {
				$toolName = $toolCall['function']['name'] ?? '';
				$toolArgs = $toolCall['function']['arguments'] ?? '{}';

				// Parse arguments if string
				if ( is_string( $toolArgs ) ) {
					$toolArgs = json_decode( $toolArgs, true ) ?? [];
				}

				$this->log_info( "Executing tool via registry", [
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
			$this->log_error( "Model failed during tool execution", [ 'error' => $e->getMessage() ] );

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
	 * Build system prompt for model
	 *
	 * @param string $workDir Work directory
	 * @param array $availableTools Available tools
	 *
	 * @return string System prompt
	 */
	private function buildSystemPrompt( string $workDir, array $availableTools ): string {
		$prompt = "You are an AI code analyst examining a WordPress plugin/theme.\n\n";
		$prompt .= "WORK DIRECTORY: $workDir\n";
		$prompt .= "All file operations are relative to this directory.\n\n";

		$prompt .= "You have access to the following tools:\n";

		$toolDescriptions = [
			'read_file'        => 'Read file contents with optional line range',
			'search_pattern'   => 'Search for patterns in PHP files',
			'list_files'       => 'List files in a directory',
			'get_file_info'    => 'Get file metadata',
			'analyze_function' => 'Analyze a specific function'
		];

		foreach ( $availableTools as $tool ) {
			if ( isset( $toolDescriptions[ $tool ] ) ) {
				$prompt .= "- $tool: " . $toolDescriptions[ $tool ] . "\n";
			}
		}

		$prompt .= "\nTo gather information, respond with JSON in this format:\n";
		$prompt .= '{"tool_requests": [{"tool": "tool_name", "args": {...}}]}' . "\n\n";

		$prompt .= "When you have enough information, provide your final analysis with:\n";
		$prompt .= '{"final_response": "Your complete analysis here"}' . "\n\n";

		$prompt .= "Be systematic and thorough. Start by understanding the code structure, then dive into specifics.\n";

		return $prompt;
	}

	/**
	 * Get tool definitions for requested tools
	 *
	 * @param array $toolRequests Tool requests
	 *
	 * @return array Tool definitions
	 */
	private function getToolDefinitionsForRequests( array $toolRequests ): array {
		$allTools = $this->getAllToolDefinitions();
		$tools    = [];

		foreach ( $toolRequests as $request ) {
			$toolName = $request['tool'] ?? '';
			if ( isset( $allTools[ $toolName ] ) ) {
				$tools[] = [
					'type'     => 'function',
					'function' => $allTools[ $toolName ]
				];
			}
		}

		return $tools;
	}

	/**
	 * Get all tool definitions
	 *
	 * @return array Tool definitions
	 */
	private function getAllToolDefinitions(): array {
		return [
			'read_file'      => [
				'name'        => 'read_file',
				'description' => 'Read contents of a file with optional line range',
				'parameters'  => [
					'type'       => 'object',
					'properties' => [
						'path'       => [
							'type'        => 'string',
							'description' => 'File path relative to the work directory'
						],
						'start_line' => [
							'type'        => 'integer',
							'description' => 'Starting line number (optional)'
						],
						'end_line'   => [
							'type'        => 'integer',
							'description' => 'Ending line number (optional)'
						]
					],
					'required'   => [ 'path' ]
				]
			],
			'search_pattern' => [
				'name'        => 'search_pattern',
				'description' => 'Search for a pattern in all PHP files',
				'parameters'  => [
					'type'       => 'object',
					'properties' => [
						'pattern'     => [
							'type'        => 'string',
							'description' => 'Pattern to search for (regex or string)'
						],
						'max_results' => [
							'type'        => 'integer',
							'description' => 'Maximum number of results to return',
							'default'     => 50
						]
					],
					'required'   => [ 'pattern' ]
				]
			],
			'list_files'     => [
				'name'        => 'list_files',
				'description' => 'List files in a directory',
				'parameters'  => [
					'type'       => 'object',
					'properties' => [
						'directory' => [
							'type'        => 'string',
							'description' => 'Directory path relative to work directory (default: root)',
							'default'     => '.'
						]
					],
					'required'   => []
				]
			]
		];
	}

	/**
	 * Format tool results for coder model
	 *
	 * @param array $toolResults Tool execution results
	 *
	 * @return string Formatted results
	 */
	private function formatToolResults( array $toolResults ): string {
		$formatted = "=== TOOL EXECUTION RESULTS ===\n\n";

		foreach ( $toolResults as $i => $result ) {
			$formatted .= sprintf( "[%d] Tool: %s\n", $i + 1, $result['tool'] );
			$formatted .= sprintf( "    Args: %s\n", json_encode( $result['args'] ) );

			if ( $result['success'] ) {
				$formatted .= "    Status: SUCCESS\n";
				$formatted .= "    Result:\n";
				$formatted .= $this->formatToolResult( $result['result'] );
			} else {
				$formatted .= "    Status: FAILED\n";
				$formatted .= "    Error: " . ( $result['result']['error'] ?? 'Unknown error' ) . "\n";
			}
			$formatted .= "\n";
		}

		return $formatted;
	}

	/**
	 * Format individual tool result
	 *
	 * @param array $result Tool result
	 *
	 * @return string Formatted result
	 */
	private function formatToolResult( array $result ): string {
		// Format based on known result types
		if ( isset( $result['content'] ) ) {
			// File read result
			$lines = substr_count( $result['content'], "\n" ) + 1;

			return sprintf( "    File: %s (%d lines)\n    Content preview:\n%s\n",
				$result['path'] ?? 'unknown',
				$lines,
				$this->truncateContent( $result['content'], 500 )
			);
		}

		if ( isset( $result['results'] ) && isset( $result['pattern'] ) ) {
			// Search result
			return sprintf( "    Pattern: %s\n    Matches: %d%s\n%s",
				$result['pattern'],
				$result['count'] ?? count( $result['results'] ),
				$result['truncated'] ? ' (truncated)' : '',
				$this->formatSearchResults( $result['results'] )
			);
		}

		if ( isset( $result['files'] ) && isset( $result['directories'] ) ) {
			// Directory listing
			return sprintf( "    Directory: %s\n    Files: %d, Directories: %d\n",
				$result['directory'] ?? '.',
				$result['total_files'] ?? 0,
				$result['total_directories'] ?? 0
			);
		}

		// Default: JSON encode
		return "    " . json_encode( $result, JSON_PRETTY_PRINT ) . "\n";
	}

	/**
	 * Format search results
	 *
	 * @param array $results Search results
	 *
	 * @return string Formatted results
	 */
	private function formatSearchResults( array $results ): string {
		$formatted = '';
		$shown     = 0;

		foreach ( $results as $match ) {
			if ( $shown >= 5 ) {
				$formatted .= sprintf( "    ... and %d more matches\n", count( $results ) - $shown );
				break;
			}

			$formatted .= sprintf( "    - %s:%d: %s\n",
				$match['file'] ?? 'unknown',
				$match['line'] ?? 0,
				$this->truncateContent( $match['content'] ?? '', 80 )
			);
			$shown ++;
		}

		return $formatted;
	}

	/**
	 * Truncate content for display
	 *
	 * @param string $content Content to truncate
	 * @param int $maxLength Maximum length
	 *
	 * @return string Truncated content
	 */
	private function truncateContent( string $content, int $maxLength ): string {
		if ( strlen( $content ) <= $maxLength ) {
			return $content;
		}

		return substr( $content, 0, $maxLength ) . '...';
	}

	/**
	 * Check if coder response is a final response
	 *
	 * @param string $content Response content
	 *
	 * @return bool True if final response
	 */
	private function isFinaResponse( string $content ): bool {
		// Check for common final response indicators
		$finalIndicators = [
			'final analysis',
			'conclusion',
			'summary',
			'based on my analysis',
			'in conclusion',
			'to summarize'
		];

		$lowerContent = strtolower( $content );
		foreach ( $finalIndicators as $indicator ) {
			if ( strpos( $lowerContent, $indicator ) !== false ) {
				return true;
			}
		}

		// Check if it's a substantial response without tool requests
		return strlen( $content ) > 200 && strpos( $content, 'tool' ) === false;
	}

	/**
	 * Extract final response from conversation
	 *
	 * @param array $conversation Conversation history
	 *
	 * @return string Final response
	 */
	private function extractFinalResponse( array $conversation ): string {
		// Work backwards to find the last substantial coder response
		for ( $i = count( $conversation ) - 1; $i >= 0; $i -- ) {
			$message = $conversation[ $i ];

			if ( $message['role'] === 'assistant' && isset( $message['content'] ) ) {
				$content = $message['content'];

				// Try to parse as JSON
				$parsed = json_decode( $content, true );
				if ( json_last_error() === JSON_ERROR_NONE && isset( $parsed['final_response'] ) ) {
					return $parsed['final_response'];
				}

				// If it's a substantial non-JSON response, use it
				if ( strlen( $content ) > 100 && $this->isFinaResponse( $content ) ) {
					return $content;
				}
			}
		}

		return 'Analysis incomplete - no final response generated.';
	}
}