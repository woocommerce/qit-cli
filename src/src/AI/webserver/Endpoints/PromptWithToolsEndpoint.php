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
		$benchmarkStart = microtime( true );
		$this->log_info( "BENCHMARK: PromptWithTools request started" );
		$this->log_info( "Processing prompt with tools request" );
		$this->currentInput = $input;
		// Validate input - messages and model are required
		if ( ! isset( $input['messages'] ) || ! isset( $input['model'] ) ) {
			$missing = [];
			if ( ! isset( $input['messages'] ) ) {
				$missing[] = 'messages';
			}
			if ( ! isset( $input['model'] ) ) {
				$missing[] = 'model';
			}
			$this->log_error( "Missing required parameters", [
				'missing' => $missing,
				'uri'     => $_SERVER['REQUEST_URI'] ?? 'unknown'
			] );
			NodeResponse::error( 'Missing required parameters: ' . implode( ', ', $missing ), 400, [
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
			$format         = $input['format'] ?? null;
			// Resolve work directory
			$pathResolutionStart = microtime( true );
			NodeResponse::mark( 'path_resolution' );
			$this->log_info( "BENCHMARK: Path resolution started" );
			try {
				$workDir            = ExtractPathResolver::resolve( $input );
				$pathResolutionTime = round( ( microtime( true ) - $pathResolutionStart ) * 1000, 2 );
				$this->log_info( "BENCHMARK: Path resolution completed in {$pathResolutionTime}ms" );
				$this->log_info( "Work directory resolved", [ 'work_dir' => $workDir ] );
			} catch ( Exception $e ) {
				$pathResolutionTime = round( ( microtime( true ) - $pathResolutionStart ) * 1000, 2 );
				$this->log_info( "BENCHMARK: Path resolution failed after {$pathResolutionTime}ms" );
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
			$toolRegistryStart = microtime( true );
			NodeResponse::mark( 'tool_registry_init' );
			$this->log_info( "BENCHMARK: Tool registry initialization started" );
			$toolRegistry = new ToolRegistry( $workDir );
			// Get tool definitions
			$tools            = $this->getToolDefinitions( $availableTools );
			$toolRegistryTime = round( ( microtime( true ) - $toolRegistryStart ) * 1000, 2 );
			$this->log_info( "BENCHMARK: Tool registry initialization completed in {$toolRegistryTime}ms" );
			// Use the provided messages directly
			// Run the tool-calling loop
			$toolCallingStart = microtime( true );
			NodeResponse::mark( 'tool_calling_loop' );
			$this->log_info( "BENCHMARK: Tool calling loop started" );
			$result          = $this->runToolCallingLoop(
				$model,
				$messages,
				$tools,
				$toolRegistry,
				$maxIterations,
				$format
			);
			$toolCallingTime = round( ( microtime( true ) - $toolCallingStart ) * 1000, 2 );
			$this->log_info( "BENCHMARK: Tool calling loop completed in {$toolCallingTime}ms" );
			$this->log_info( "Tool calling completed", [
				'iterations'  => $result['iterations'],
				'total_tools' => count( $result['all_tool_calls'] )
			] );
			// Return the final response
			$totalExecutionTime = round( ( microtime( true ) - $benchmarkStart ) * 1000, 2 );
			$this->log_info( "BENCHMARK: Total PromptWithTools execution completed in {$totalExecutionTime}ms" );

			// Stop the model after the entire request is complete (per-request stopping)
			$this->stopOllamaModel( $model );

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
			// Stop the model even on error (per-request stopping)
			if ( isset( $model ) ) {
				$this->stopOllamaModel( $model );
			}

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
	 * @param array|null $format Optional response format schema
	 *
	 * @return array Results with final response and all tool calls
	 */
	private function runToolCallingLoop(
		string $model,
		array $messages,
		array $tools,
		ToolRegistry $toolRegistry,
		int $maxIterations,
		?array $format = null
	): array {
		$startTime       = microtime( true );
		$allToolCalls    = [];
		$iterations      = 0;
		$completionToken = 'INVESTIGATION_COMPLETE';

		$this->log_info( "BENCHMARK: Tool calling loop started with max iterations: $maxIterations" );

		// Add initial system message about iterations and format
		array_unshift( $messages, [
			'role'    => 'system',
			'content' => "You have a maximum of $maxIterations iterations to complete your investigation. " .
			             "Each iteration allows you to make tool calls and gather information. " .
			             "You will be informed of remaining iterations. " .
			             "On your FINAL iteration, you MUST provide your complete analysis" .
			             ( $format ? " in the specified JSON format" : "" ) .
			             " and include '$completionToken' in your response."
		] );

		while ( $iterations < $maxIterations ) {
			$iterations ++;
			$remainingIterations = $maxIterations - $iterations;
			$isLastIteration     = ( $remainingIterations === 0 );

			$this->log_info( "Tool calling iteration $iterations of $maxIterations (remaining: $remainingIterations)" );

			// Prepare base model request
			$modelRequest = [
				'model'    => $model,
				'messages' => $messages,
				'stream'   => false,
				'raw'      => true,
				'think'    => false,
			];

			// Handle last iteration differently
			if ( $isLastIteration ) {
				$this->log_info( "Last iteration - enforcing final response" . ( $format ? " with format" : "" ) );

				// Don't include tools on the last iteration
				if ( $format ) {
					$modelRequest['format'] = $format;
					unset( $modelRequest['tools'] );

					// Add a strong system message about the format requirement
					$formatInstruction = [
						'role'    => 'system',
						'content' => "⚠️ FINAL ITERATION - NO MORE TOOLS AVAILABLE ⚠️\n" .
						             "This is your LAST opportunity to respond. You cannot make any more tool calls.\n" .
						             "You MUST provide your complete analysis NOW in the following JSON format:\n" .
						             json_encode( $format, JSON_PRETTY_PRINT ) . "\n\n" .
						             "Include '$completionToken' in your response to confirm completion."
					];

					// Insert this right before the final call
					$modelRequest['messages'] = array_merge( $messages, [ $formatInstruction ] );
				} else {
					// No format required, but still the last iteration
					unset( $modelRequest['tools'] );

					$finalInstruction = [
						'role'    => 'system',
						'content' => "⚠️ FINAL ITERATION - NO MORE TOOLS AVAILABLE ⚠️\n" .
						             "This is your LAST opportunity to respond. You cannot make any more tool calls.\n" .
						             "You MUST provide your complete analysis NOW.\n" .
						             "Include '$completionToken' in your response to confirm completion."
					];

					$modelRequest['messages'] = array_merge( $messages, [ $finalInstruction ] );
				}
			} else {
				// Not the last iteration - include tools, no format
				$modelRequest['tools'] = $tools;

				// Add iteration info to help the model plan
				$iterationInfo = [
					'role'    => 'system',
					'content' => "Iteration $iterations of $maxIterations. You have $remainingIterations more iterations after this one." .
					             ( $remainingIterations === 1 ? " The NEXT iteration will be your LAST." : "" )
				];

				$modelRequest['messages'] = array_merge( [ $iterationInfo ], $messages );
			}

			// Call the model
			$modelCallStart = microtime( true );
			$this->log_info( "BENCHMARK: Model call started (iteration $iterations)" );

			try {
				$modelResponse = $this->callOllamaChat( $modelRequest, $this->currentInput );
				$modelCallTime = round( ( microtime( true ) - $modelCallStart ) * 1000, 2 );
				$this->log_info( "BENCHMARK: Model call completed in {$modelCallTime}ms (iteration $iterations)" );
			} catch ( Exception $e ) {
				$modelCallTime = round( ( microtime( true ) - $modelCallStart ) * 1000, 2 );
				$this->log_info( "BENCHMARK: Model call failed after {$modelCallTime}ms (iteration $iterations)" );
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

			// Execute tool calls only if not the last iteration
			if ( ! empty( $toolCalls ) && ! $isLastIteration ) {
				foreach ( $toolCalls as $toolCall ) {
					$toolName = $toolCall['function']['name'] ?? '';
					$toolArgs = $toolCall['function']['arguments'] ?? '{}';

					if ( is_string( $toolArgs ) ) {
						$toolArgs = json_decode( $toolArgs, true ) ?? [];
					}

					$toolExecutionStart = microtime( true );
					$this->log_info( "BENCHMARK: Tool execution started - {$toolName} (iteration $iterations)" );
					$this->log_info( "Executing tool", [
						'tool' => $toolName,
						'args' => $toolArgs
					] );

					$result            = $toolRegistry->execute_tool( $toolName, $toolArgs );
					$toolExecutionTime = round( ( microtime( true ) - $toolExecutionStart ) * 1000, 2 );
					$success           = ! isset( $result['error'] );
					$status            = $success ? 'completed' : 'failed';

					$this->log_info( "BENCHMARK: Tool execution {$status} - {$toolName} in {$toolExecutionTime}ms (iteration $iterations)" );

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
			} elseif ( ! empty( $toolCalls ) && $isLastIteration ) {
				$this->log_warning( "Tool calls attempted on last iteration - ignoring", [
					'attempted_tools' => array_map( function ( $tc ) {
						return $tc['function']['name'] ?? 'unknown';
					}, $toolCalls )
				] );
			}

			// Check if investigation is complete or if this is the last iteration
			if ( strpos( $content, $completionToken ) !== false || $isLastIteration ) {
				if ( $isLastIteration && strpos( $content, $completionToken ) === false ) {
					$this->log_warning( "Last iteration reached without completion token" );
				} else {
					$this->log_info( "Found completion token, investigation complete" );
				}
				break;
			}

			// If not complete and not last iteration, prompt to continue
			$this->log_info( "No completion token found, prompting to continue" );

			// Craft continuation message based on remaining iterations
			if ( $remainingIterations === 1 ) {
				// Next iteration will be the last
				$continueContent = "⚠️ FINAL ITERATION WARNING ⚠️\n\n" .
				                   "You have only 1 iteration remaining. The next response will be your LAST.\n" .
				                   "After your next response:\n" .
				                   "- You will NOT be able to use any tools\n" .
				                   "- You MUST provide your complete final analysis\n";

				if ( $format ) {
					$continueContent .= "- You MUST format your response as JSON according to the schema that will be provided\n";
				}

				$continueContent .= "- You MUST include '$completionToken' in your response\n\n" .
				                    "Make any final tool calls you need NOW, then be prepared to give your complete analysis.";

				$messages[] = [
					'role'    => 'user',
					'content' => $continueContent
				];
			} elseif ( $remainingIterations <= 3 ) {
				// Getting close to the limit
				$messages[] = [
					'role'    => 'user',
					'content' => "Continue with the next step. You have $remainingIterations iterations remaining. " .
					             "Consider wrapping up your investigation soon. " .
					             "When ready to provide your final analysis, include '$completionToken' in your response."
				];
			} else {
				// Still have plenty of iterations
				$messages[] = [
					'role'    => 'user',
					'content' => "Continue with the next step. You have $remainingIterations iterations remaining. " .
					             "When you have completed ALL investigation steps and are ready to provide your final analysis, " .
					             "include '$completionToken' in your response."
				];
			}
		}

		// Extract final response (remove the token)
		$finalResponse = $this->extractFinalResponse( $messages, $completionToken );

		// If we hit max iterations without a proper response, ensure we have something
		if ( empty( trim( $finalResponse ) ) ) {
			$finalResponse = "Investigation reached maximum iteration limit ($maxIterations). " .
			                 "Based on the information gathered, here is the analysis:\n\n" .
			                 "[The model did not provide a final analysis. Please review the tool calls above for the information that was collected.]";
			$this->log_warning( "No final response extracted, using fallback message" );
		}

		$executionTime = round( ( microtime( true ) - $startTime ) * 1000 );

		$this->log_info( "BENCHMARK: Tool calling loop completed - {$iterations} iterations, " .
		                 count( $allToolCalls ) . " tool calls, {$executionTime}ms total" );

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
						'path' => [
							'type'        => 'string',
							'description' => 'File path relative to the work directory'
						],
						//'start_line' => [
						//	'type'        => 'integer',
						//	'description' => 'Starting line number (optional, 1-based)'
						//],
						//'end_line'   => [
						//	'type'        => 'integer',
						//	'description' => 'Ending line number (optional, inclusive)'
						//]
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
