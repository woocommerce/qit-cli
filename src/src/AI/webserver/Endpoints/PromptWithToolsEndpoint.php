<?php

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\Message;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\ToolRegistry;
use QIT_AI_Webserver\NodeResponse;

/**
 * Prompt-With-Tools Endpoint
 *
 * Implements a full multi-turn function-calling loop following LLPhant's
 * documented pattern (see GH issues #219, #251).
 */
class PromptWithToolsEndpoint extends AbstractEndpoint {

	public function get_route(): string {
		return '/prompt-with-tools';
	}

	public function handle( array $input ): void {
		try {
			// ---------- 1. Validate & extract parameters ----------
			if ( ! isset( $input['messages'], $input['model'] ) ) {
				$missing = array_diff( [ 'messages', 'model' ], array_keys( $input ) );
				NodeResponse::error(
					'Missing required parameters: ' . implode( ',', $missing ),
					400
				);
			}

			$rawMessages    = $input['messages'];
			$model          = $input['model'];
			$availableTools = $input['available_tools'] ??
			                  [ 'read_file', 'search_pattern', 'list_files' ];
			$maxIterations  = $input['max_iterations'] ?? 10;
			$jobId          = $input['job_id'] ?? null;
			$sessionId      = $input['session_id'] ?? null;
			$format         = $input['format'] ?? null;

			// ---------- 2. Initialise LLM ----------
			$this->providerConfig['model'] = $model;
			if ( $format ) {
				$this->providerConfig['format'] = $format;
			}
			$this->initializeLLM();                 // sets $this->llm
			$this->llm->ensureInitialized();        // creates chat client
			$chat = $this->llm->getChat();

			// ---------- 3. Prepare workdir & tools ----------
			$workDir      = ExtractPathResolver::resolve( $input );
			$toolRegistry = new ToolRegistry( $workDir );

			foreach ( $availableTools as $toolName ) {
				if ( $tool = $toolRegistry->getTool( $toolName ) ) {
					$chat->addTool( $tool->getFunctionInfo() );
				}
			}

			// ---------- 4. Convert input messages to LLPhant objects ----------
			$conversation  = [];   // array<Message>
			$systemMessage = '';

			foreach ( $rawMessages as $m ) {
				switch ( $m['role'] ) {
					case 'system':
						$systemMessage .= $m['content'] . "\n";
						break;
					case 'user':
						$conversation[] = Message::user( $m['content'] );
						break;
					case 'assistant':
						$conversation[] = Message::assistant( $m['content'] );
						break;
					case 'tool':
						// Tool results need the tool_call_id if available
						$toolCallId     = $m['tool_call_id'] ?? null;
						$conversation[] = Message::toolResult( $m['content'], $toolCallId );
						break;
					default:
						$this->log_warning( "Unknown message role: {$m['role']}" );
				}
			}

			if ( trim( $systemMessage ) !== '' ) {
				$chat->setSystemMessage( trim( $systemMessage ) );
			}

			// Ensure we have at least one message
			if ( empty( $conversation ) ) {
				NodeResponse::error( 'No valid messages provided', 400 );
			}

			// ---------- 5. Main tool-calling loop ----------
			$iterations   = 0;
			$allToolCalls = [];
			$startMs      = microtime( true );
			$finalContent = '';

			while ( $iterations < $maxIterations ) {
				$iterations ++;

				try {
					$answer = $chat->generateChatOrReturnFunctionCalled( $conversation );
				} catch ( Exception $e ) {
					$this->log_error( "LLM call failed: " . $e->getMessage() );
					throw new Exception( "Failed to generate response: " . $e->getMessage() );
				}

				// --- 5.a  Tool branch ---
				if ( is_array( $answer ) ) {
					/** @var FunctionInfo[] $answer */
					foreach ( $answer as $functionInfo ) {
						$args = json_decode( $functionInfo->jsonArgs, true ) ?? [];

						try {
							$result = $toolRegistry->execute_tool( $functionInfo->name, $args );
						} catch ( Exception $e ) {
							$this->log_error(
								"Tool execution failed for {$functionInfo->name}: " . $e->getMessage()
							);
							$result = [
								'error'   => true,
								'message' => "Tool execution failed: " . $e->getMessage()
							];
						}

						$allToolCalls[] = [
							'toolName' => $functionInfo->name,
							'args'     => $args,
							'result'   => $result,
						];

						// Append assistant tool call + tool result, preserving IDs
						$conversation[] = Message::assistantAskingTools( [ $functionInfo ] );
						$conversation[] = Message::toolResult(
							json_encode(
								$result,
								JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
							),
							$functionInfo->getToolCallId()
						);
					}
					continue;   // loop again with updated context
				}

				// --- 5.b  Final text branch ---
				$finalContent = is_string( $answer ) ? trim( $answer ) : '';
				break;
			}

			// Check if we hit the iteration limit
			if ( $iterations >= $maxIterations && is_array( $answer ) ) {
				$this->log_warning( "Hit max iterations limit ({$maxIterations})" );
				$finalContent = "Maximum iteration limit reached. The task may be incomplete.";
			}

			// ---------- 6. Respond ----------
			NodeResponse::toolPrompt(
				$finalContent,
				$allToolCalls,
				$model,
				[
					'job_id'         => $jobId,
					'iterations'     => $iterations,
					'session_id'     => $sessionId,
					'execution_time' => (int) round(
						( microtime( true ) - $startMs ) * 1000
					),
				]
			);

		} catch ( Exception $e ) {
			$this->handleError( $e, [
				'job_type' => 'prompt_with_tools',
				'job_id'   => $jobId ?? null
			] );
		}
	}
}