<?php
/**
 * QIT AI – Prompt-With-Tools endpoint (simplified)
 *
 * - minimal loop
 * - no helper validation
 * - always adds one extra “format-enforced” round if $format is given
 */

namespace QIT_AI_Webserver\Endpoints;

use Exception;
use QIT_AI_Webserver\Lib\ToolRegistry;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\NodeResponse;

class PromptWithToolsEndpoint extends AbstractEndpoint {
	/* ------------------------------------------------------------- */
	private array $currentInput = [];

	public function get_route(): string {
		return '/prompt-with-tools';
	}

	/* ------------------------------------------------------------- */
	public function handle( array $input ): void {

		$this->currentInput = $input;
		if ( ! isset( $input['messages'], $input['model'] ) ) {
			$missing = array_diff( [ 'messages', 'model' ], array_keys( $input ) );
			NodeResponse::error( 'Missing required parameters: ' . implode( ', ', $missing ), 400 );
		}

		try {
			/* ------- params ------- */
			$messages       = $input['messages'];
			$model          = $input['model'];
			$jobId          = $input['job_id'] ?? null;
			$maxIterations  = $input['max_iterations'] ?? 10;
			$availableTools = $input['available_tools'] ?? [ 'read_file', 'search_pattern', 'list_files' ];
			$sessionId      = $input['session_id'] ?? null;
			$format         = $input['format'] ?? [];

			/* ------- work dir & tools ------- */
			$workDir      = ExtractPathResolver::resolve( $input );
			$toolRegistry = new ToolRegistry( $workDir );
			$tools        = $this->getToolDefinitions( $availableTools );

			/* ------- run loop ------- */
			$result = $this->runToolCallingLoop(
				$model,
				$messages,
				$tools,
				$toolRegistry,
				$maxIterations,
				$format
			);

			$this->stopOllamaModel( $model );

			NodeResponse::toolPrompt(
				$result['final_response'],
				$result['all_tool_calls'],
				$model,
				[
					'job_id'         => $jobId,
					'iterations'     => $result['iterations'],
					'session_id'     => $sessionId,
					'execution_time' => $result['execution_time']
				]
			);

		} catch ( Exception $e ) {
			if ( isset( $model ) ) {
				$this->stopOllamaModel( $model );
			}
			$this->handleError( $e, [ 'job_type' => 'prompt_with_tools' ] );
		}
	}

	/* ============================================================= */
	private function runToolCallingLoop(
		string $model,
		array $messages,
		array $tools,
		ToolRegistry $toolRegistry,
		int $maxIterations = 10,
		?array $format = null
	): array {

		$conversation  = $messages;
		$allToolCalls  = [];
		$distinctTools = [];
		$iterations    = 0;
		$startMs       = microtime( true );

		while ( $iterations < $maxIterations ) {
			$iterations ++;

			/* ---- ask ---- */
			$req       = [
				'model'    => $model,
				'messages' => $conversation,
				'tools'    => $tools,
				'stream'   => false,
			];
			$resp      = $this->callOllamaChat( $req, $this->currentInput );
			$assistant = $resp['message'] ?? [];
			$content   = trim( $assistant['content'] ?? '' );
			$calls     = $assistant['tool_calls'] ?? [];

			/* ---- tool phase ---- */
			if ( $calls ) {
				foreach ( $calls as $call ) {
					$tool = $call['function']['name'] ?? '';
					// --- get arguments no-matter how they come ---
					$rawArgs = $call['function']['arguments'] ?? [];

					/**
					 * • If Ollama passed an array, keep it.
					 * • If it’s a string, decode it.
					 * • Anything else → empty array.
					 */
					if ( is_array( $rawArgs ) ) {
						$args = $rawArgs;
					} elseif ( is_string( $rawArgs ) ) {
						$args = json_decode( $rawArgs, true ) ?: [];
					} else {
						$args = [];
					}

					$result = $toolRegistry->execute_tool( $tool, $args );

					$allToolCalls[]         = compact( 'tool', 'args', 'result' );
					$distinctTools[ $tool ] = true;
					$conversation[]         = $assistant;          // request
					$conversation[]         = [
						'role'    => 'tool',
						'content' => json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ),
					];
				}
				continue; // next iteration
			}

			/* ---- conclusion phase ---- */
			if ( $format ) {
				// One extra round that enforces JSON schema
				$conversation[] = $assistant;
				$conversation[] = [
					'role'    => 'system',
					'content' => 'Return ONLY the JSON object that matches the agreed schema.',
				];

				$jsonReq = [
					'model'    => $model,
					'messages' => $conversation,
					'format'   => $format,
					'stream'   => false,
				];
				$resp    = $this->callOllamaChat( $jsonReq, $this->currentInput );
				$content = trim( ( $resp['message']['content'] ?? '' ) );
			}

			break; // done
		}

		return [
			'final_response'       => $content,
			'all_tool_calls'       => $allToolCalls,
			'iterations'           => $iterations,
			'execution_time'       => (int) round( ( microtime( true ) - $startMs ) * 1000 ),
			'distinct_tools_count' => count( $distinctTools ),
			'last_prompt'          => $conversation,
		];
	}

	/* ============================================================= */
	private function getToolDefinitions( array $available ): array {
		$defs = $this->getAllToolDefinitions();
		$out  = [];

		foreach ( $available as $name ) {
			if ( isset( $defs[ $name ] ) ) {
				$out[] = [ 'type' => 'function', 'function' => $defs[ $name ] ];
			}
		}

		return $out;
	}

	private function getAllToolDefinitions(): array {
		return [
			'read_file'      => [
				'name'        => 'read_file',
				'description' => 'Read contents of a file',
				'parameters'  => [
					'type'       => 'object',
					'properties' => [
						'path'       => [ 'type' => 'string', 'description' => 'Relative path' ],
						'start_line' => [ 'type' => 'integer', 'default' => 1 ],
						'end_line'   => [ 'type' => 'integer', 'default' => - 1 ],
					],
					'required'   => [ 'path' ],
				],
			],
			'search_pattern' => [
				'name'        => 'search_pattern',
				'description' => 'Search for a pattern in PHP files',
				'parameters'  => [
					'type'       => 'object',
					'properties' => [
						'pattern'     => [ 'type' => 'string' ],
						'max_results' => [ 'type' => 'integer', 'default' => 50 ],
						'directory'   => [ 'type' => 'string', 'default' => '' ],
						'is_regex'    => [ 'type' => 'boolean', 'default' => true ],
					],
					'required'   => [ 'pattern' ],
				],
			],
			'list_files'     => [
				'name'        => 'list_files',
				'description' => 'List files/directories',
				'parameters'  => [
					'type'       => 'object',
					'properties' => [
						'directory' => [ 'type' => 'string', 'default' => '.' ],
					],
				],
			],
		];
	}
}
