<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use QIT_AI_Webserver\Lib\ToolRegistry;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\NodeResponse;

/**
 * Prompt With Tools Endpoint
 *
 * Implements proper multi-turn tool calling with Ollama
 */
class PromptWithToolsEndpoint extends AbstractEndpoint {
	private array $currentInput = [];

	/**
	 * Get the route for this endpoint
	 *
	 * @return string The route path
	 */
	public function get_route(): string {
		return '/prompt-with-tools';
	}

	/**
	 * Handle AI request with tools
	 *
	 * @param array $input Request input data
	 *
	 * @return void Outputs JSON response
	 */
	public function handle( array $input ): void {
		$this->log_info( "Processing prompt with tools request" );

		$this->currentInput = $input;

		// Validate input - messages and model are required
		if ( ! isset( $input['messages'] ) || ! isset( $input['model'] ) ) {
			$missing = [];
			if ( ! isset( $input['messages'] ) ) $missing[] = 'messages';
			if ( ! isset( $input['model'] ) ) $missing[] = 'model';

			$this->log_error( "Missing required parameters", [
				'missing' => $missing,
				'uri'     => $_SERVER['REQUEST_URI'] ?? 'unknown'
			] );
			NodeResponse::error( 'Missing required parameters: ' . implode(', ', $missing), 400, [
				'job_id' => $input['job_id'] ?? null
			] );
		}

		try {
			// Extract parameters
			$messages       = $input['messages'];
			$model          = $input['model'];
			$jobId          = $input['job_id'] ?? null;
			$maxIterations  = $input['max_iterations'] ?? 30;
			$availableTools = $input['available_tools'] ?? [ 'read_file', 'search_pattern', 'list_files' ];
			$sessionId      = $input['session_id'] ?? null;

			// Resolve work directory
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

			// Get tool definitions
			$tools = $this->getToolDefinitions( $availableTools );

			// Use the provided messages directly

			// Run the tool-calling loop
			NodeResponse::mark( 'tool_calling_loop' );
			$result = $this->runToolCallingLoop(
				$model,
				$messages,
				$tools,
				$toolRegistry,
				$maxIterations
			);

			$this->log_info( "Tool calling completed", [
				'iterations'  => $result['iterations'],
				'total_tools' => count( $result['all_tool_calls'] )
			] );

			// Return the final response
			NodeResponse::toolPrompt(
				$result['final_response'],
				$result['all_tool_calls'],
				$model,
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
	 * Run the tool-calling loop
	 *
	 * @param string $model Model name
	 * @param array $messages Initial messages
	 * @param array $tools Tool definitions
	 * @param ToolRegistry $toolRegistry Tool registry
	 * @param int $maxIterations Maximum iterations
	 *
	 * @return array Results with final response and all tool calls
	 */
	private function runToolCallingLoop(
		string $model,
		array $messages,
		array $tools,
		ToolRegistry $toolRegistry,
		int $maxIterations
	): array {
		$startTime       = microtime( true );
		$allToolCalls    = [];
		$iterations      = 0;
		$completionToken = 'INVESTIGATION_COMPLETE';

		while ( $iterations < $maxIterations ) {
			$iterations ++;

			$this->log_info( "Tool calling iteration $iterations" );

			// Call the model
			$modelRequest = [
				'model'    => $model,
				'messages' => $messages,
				'tools'    => $tools,
				'stream'   => false
			];

			try {
				$modelResponse = $this->callOllamaChat( $modelRequest, $this->currentInput );
			} catch ( Exception $e ) {
				$this->log_error( "Model call failed", [ 'error' => $e->getMessage() ] );
				break;
			}

			// Get the model's response
			$message   = $modelResponse['message'] ?? [];
			$content   = $message['content'] ?? '';
			$toolCalls = $message['tool_calls'] ?? [];

			// Add the assistant's message to history
			$messages[] = [
				'role'       => 'assistant',
				'content'    => $content,
				'tool_calls' => $toolCalls
			];

			// Execute any tool calls
			if ( ! empty( $toolCalls ) ) {
				foreach ( $toolCalls as $toolCall ) {
					$toolName = $toolCall['function']['name'] ?? '';
					$toolArgs = $toolCall['function']['arguments'] ?? '{}';

					if ( is_string( $toolArgs ) ) {
						$toolArgs = json_decode( $toolArgs, true ) ?? [];
					}

					$this->log_info( "Executing tool", [
						'tool' => $toolName,
						'args' => $toolArgs
					] );

					$result = $toolRegistry->execute_tool( $toolName, $toolArgs );

					$allToolCalls[] = [
						'iteration' => $iterations,
						'tool'      => $toolName,
						'args'      => $toolArgs,
						'result'    => $result,
						'success'   => ! isset( $result['error'] )
					];

					$messages[] = [
						'role'         => 'tool',
						'content'      => json_encode( $result ),
						'tool_call_id' => $toolCall['id'] ?? uniqid()
					];
				}
			}

			// Check if investigation is complete
			if ( strpos( $content, $completionToken ) !== false ) {
				$this->log_info( "Found completion token, investigation complete" );
				break;
			}

			// If no completion token, prompt to continue
			$this->log_info( "No completion token found, prompting to continue" );

			$messages[] = [
				'role'    => 'user',
				'content' => 'Continue with the next step of the investigation. When you have completed ALL investigation steps and are ready to provide your final analysis, include the text "' . $completionToken . '" in your response.'
			];
		}

		// Extract final response (remove the token)
		$finalResponse = $this->extractFinalResponse( $messages, $completionToken );
		$executionTime = round( ( microtime( true ) - $startTime ) * 1000 );

		return [
			'final_response' => $finalResponse,
			'all_tool_calls' => $allToolCalls,
			'iterations'     => $iterations,
			'execution_time' => $executionTime
		];
	}

	/**
	 * Extract final response and clean up completion token
	 */
	private function extractFinalResponse( array $messages, string $completionToken ): string {
		$fullResponse = [];

		foreach ( $messages as $message ) {
			if ( $message['role'] === 'assistant' && ! empty( $message['content'] ) ) {
				$content = str_replace( $completionToken, '', $message['content'] );
				$content = trim( $content );

				if ( ! empty( $content ) ) {
					$fullResponse[] = $content;
				}
			}
		}

		return implode( "\n\n", $fullResponse );
	}

	/**
	 * Get tool definitions for available tools
	 *
	 * @param array $availableTools List of available tool names
	 *
	 * @return array Tool definitions for Ollama
	 */
	private function getToolDefinitions( array $availableTools ): array {
		$allTools = $this->getAllToolDefinitions();
		$tools    = [];

		foreach ( $availableTools as $toolName ) {
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
	 * Get all tool definitions matching ToolRegistry implementation
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
							'description' => 'Starting line number (optional, 1-based)'
						],
						'end_line'   => [
							'type'        => 'integer',
							'description' => 'Ending line number (optional, inclusive)'
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
							'description' => 'Pattern to search for (regex or literal string)'
						],
						'max_results' => [
							'type'        => 'integer',
							'description' => 'Maximum number of results to return',
							'default'     => 50
						],
						'directory'   => [
							'type'        => 'string',
							'description' => 'Directory to search in (optional, relative to work directory)',
							'default'     => ''
						],
						'is_regex'    => [
							'type'        => 'boolean',
							'description' => 'Whether to treat pattern as regex (default: true)',
							'default'     => true
						]
					],
					'required'   => [ 'pattern' ]
				]
			],
			'list_files'     => [
				'name'        => 'list_files',
				'description' => 'List files and directories in a directory',
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
}
