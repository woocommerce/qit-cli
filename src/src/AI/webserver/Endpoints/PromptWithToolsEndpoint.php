<?php

namespace QIT_AI_Webserver\Endpoints;

use LLPhant\Chat\Message;
use LLPhant\Chat\ChatResponse;
use LLPhant\Chat\FunctionInfo\ToolCall;
use LLPhant\Evaluation\Output\JSONFormatEvaluator;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\Lib\ToolPathGuard;
use QIT_AI_Webserver\NodeResponse;
use QIT_AI_Webserver\ToolRegistry;

class PromptWithToolsEndpoint extends AbstractEndpoint {
	/* ------------------------------------------------------------------ */
	public function get_route(): string {
		return '/prompt-with-tools';
	}

	/* ------------------------------------------------------------------ */
	public function handle( array $input ): void {
		/* ─────────────── 0.  helpers ────────────── */
		$dbg     = [];
		$log     = function ( string $stage, $data = null ) use ( &$dbg ) {
			$dbg[] = [ 'ts_ms' => (int) ( microtime( true ) * 1000 ), 'stage' => $stage, 'data' => $data ];
			file_put_contents( '/tmp/debug-prompt.log', json_encode( $dbg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
		};
		$logTool = function ( string $event, array $payload ) use ( $log ) {
			$log( $event, $payload );
		};

		/* ─────────────── 1.  validate ───────────── */
		foreach ( [ 'messages', 'model' ] as $k ) {
			if ( ! isset( $input[ $k ] ) ) {
				$log( 'error', "Missing {$k}" );
				NodeResponse::error( "Missing required parameter: {$k}", 400 );
			}
		}
		$raw           = $input['messages'];
		$model         = (string) $input['model'];
		$tools         = $input['available_tools'] ?? [ 'read_file', 'search_pattern', 'list_files' ];
		$format        = $input['format'] ?? null;
		$maxIterations = (int) ( $input['max_iterations'] ?? 10 );
		$minToolCalls  = (int) ( $input['min_tool_calls'] ?? 2 );
		$log( 'validated', compact( 'model', 'tools', 'format', 'minToolCalls' ) );

		$supportsNativeTools = str_contains( $model, 'mistral' )
		                       || str_contains( $model, 'gpt' )
		                       || ( $input['force_native'] ?? false );
		$log( 'capabilities', [ 'native_tools' => $supportsNativeTools ] );

		/* ─────────────── 2.  boot LLM ───────────── */
		$this->providerConfig['model'] = $model;
		$this->initializeLLM();
		$this->llm->ensureInitialized();
		$chat = $this->llm->getChat();
		$chat->setModelOption( 'think', false );

		/* ─────────────── 3.  register tools ─────── */
		$workDir   = ExtractPathResolver::resolve( $input );
		$registry  = new ToolRegistry( $workDir );
		$pathGuard = new ToolPathGuard( $workDir );
		foreach ( $tools as $t ) {
			if ( $tool = $registry->getTool( $t ) ) {
				$chat->addTool( $tool->getFunctionInfo() );
			}
		}

		/* ─────────────── 4.  seed conversation ──── */
		$system = '';
		$conv   = [];
		foreach ( $raw as $m ) {
			if ( $m['role'] === 'system' ) {
				$system .= $m['content'] . "\n";
			} else {
				$conv[] = Message::{$m['role']}( $m['content'] );
			}
		}

		$system .= "\nWhen the investigation is complete, say **done** and "
		           . "give a short summary.\n"
		           . "You must execute at least {$minToolCalls} tool calls.";
		if ( $supportsNativeTools ) {
			$system .= "\nUse function calls natively; do NOT embed JSON blocks.";
		} else {
			$system .= "\nYour model cannot call functions natively; "
			           . "therefore embed the JSON object for the tool you need.";
		}
		$chat->setSystemMessage( trim( $system ) );

		/* ───────────── helper: unwrap ChatResponse ─ */
		$unwrap = function ( mixed $resp ): array {
			return $resp instanceof ChatResponse
				? [ $resp->getContent(), $resp->getToolCalls() ]
				: [ (string) $resp, [] ];
		};

		/* ───────────── 5.  main loop ─────────────── */
		$it         = 0;
		$successful = 0;
		$summary    = '';
		$calls      = [];

		while ( ++ $it <= $maxIterations ) {

			/* ask next step */
			$conv[] = $ask = Message::user(
				"🧠 Reason about the next step. "
				. ( $supportsNativeTools
					? "If you need a tool, call it."
					: "If you need a tool, output ONLY its JSON object." )
			);
			$log( 'prompt', $ask->content );

			[ $rawOut, $nativeCalls ] = $unwrap( $chat->generateChat( $conv ) );
			$thought = trim( $rawOut );
			$conv[]  = Message::assistant( $thought );
			$log( 'response', $thought );

			/* finished? */
			if ( preg_match( '/\b(done|finished)\b/i', $thought ) ) {
				if ( $successful >= $minToolCalls ) {
					$summary = $thought;
					$log( 'summary', $summary );
					break;
				}
				$conv[] = Message::assistant(
					"❌ Only {$successful} tool calls executed; need {$minToolCalls}. Continue."
				);
				continue;
			}

			/* ───────── 5a  native function‑calls ───────── */
			if ( $nativeCalls !== [] ) {
				foreach ( $nativeCalls as $tc ) {
					$toolName = $tc->name;
					if ( ! in_array( $toolName, $tools, true ) ) {
						continue;
					}

					$args = is_string( $tc->arguments )
						? ( json_decode( $tc->arguments, true ) ?: [] )
						: (array) $tc->arguments;

					if ( in_array( $toolName, [ 'list_files', 'search_pattern' ], true )
					     && isset( $args['path'] ) && ! isset( $args['directory'] ) ) {
						$args['directory'] = $args['path'];
					}
					try {
						if ( in_array( $toolName, [ 'read_file', 'search_pattern', 'list_files' ], true ) ) {
							$key          = $toolName === 'read_file' ? 'path' : 'directory';
							$args[ $key ] = $pathGuard->normalise( $args[ $key ] ?? '.' );
						}
					} catch ( \RuntimeException $e ) {
						continue;
					}

					/* execute & log */
					$logTool( 'tool_call', [ 'id' => $tc->id, 'name' => $toolName, 'args' => $args ] );
					$result = $registry->getTool( $toolName )->execute( $args );
					$logTool( 'tool_result', [ 'id' => $tc->id, 'result' => $result ] );

					$conv[] = Message::assistantAskingTools( [
						new ToolCall( $tc->id, $toolName, json_encode( $args, JSON_UNESCAPED_UNICODE ) ),
					] );
					$conv[] = Message::toolResult( json_encode( $result, JSON_UNESCAPED_UNICODE ), $tc->id );

					$successful ++;
					$calls[] = [ 'toolName' => $toolName, 'args' => $args, 'result' => $result ];
				}
				$conv[] = Message::user( "✅ Interpret results, then next step or **done**." );
				continue;
			}

			/* ───────── 5b  legacy JSON‑inside‑content path ─────────
			   (now ALWAYS allowed – even for native‑capable models)   */
			if ( preg_match( '/\{.*\}/s', $thought, $m )
			     && ( $call = json_decode( $m[0], true ) )
			     && is_array( $call ) && isset( $call['name'] ) ) {

				$toolName = $call['name'];
				$args     = $call['arguments'] ?? [];

				if ( ! in_array( $toolName, $tools, true ) ) {
					$conv[] = Message::assistant( "❌ Unknown tool `{$toolName}`." );
					continue;
				}

				if ( in_array( $toolName, [ 'list_files', 'search_pattern' ], true )
				     && isset( $args['path'] ) && ! isset( $args['directory'] ) ) {
					$args['directory'] = $args['path'];
				}
				try {
					if ( in_array( $toolName, [ 'read_file', 'search_pattern', 'list_files' ], true ) ) {
						$key          = $toolName === 'read_file' ? 'path' : 'directory';
						$args[ $key ] = $pathGuard->normalise( $args[ $key ] ?? '.' );
					}
				} catch ( \RuntimeException $e ) {
					continue;
				}

				/* execute & log */
				$id = uniqid( 'call_', true );
				$logTool( 'tool_call', [ 'id' => $id, 'name' => $toolName, 'args' => $args ] );
				$result = $registry->getTool( $toolName )->execute( $args );
				$logTool( 'tool_result', [ 'id' => $id, 'result' => $result ] );

				$conv[] = Message::assistantAskingTools( [
					new ToolCall( $id, $toolName, json_encode( $args, JSON_UNESCAPED_UNICODE ) ),
				] );
				$conv[] = Message::toolResult( json_encode( $result, JSON_UNESCAPED_UNICODE ), $id );

				$successful ++;
				$calls[] = [ 'toolName' => $toolName, 'args' => $args, 'result' => $result ];
				$conv[]  = Message::user( "✅ Interpret results, then next step or **done**." );
				continue;
			}

			/* nothing actionable produced – re‑prompt automatically */
			$conv[] = Message::assistant( "❌ You produced no usable tool call. Think again." );
		}

		if ( $summary === '' ) {
			$log( 'error', 'loop ended w/o summary' );
			NodeResponse::error( 'Model never produced summary.', 500 );
		}

		/* ───────────── 6.  final JSON ───────────── */
		$conv[] = Message::assistant( $summary );
		$conv[] = Message::user( "🟢 Reply ONLY with the final JSON object." );
		$chat->setModelOption( 'tool_choice', 'none' );
		$chat->setModelOption( 'format', $format );

		$jsonEval = new JSONFormatEvaluator();

		for ( $attempt = 0; $attempt < 2; $attempt ++ ) {
			$log( 'prompt', '[final JSON request]' );
			[ $finRaw ] = $unwrap( $chat->generateChat( $conv ) );
			$log( 'response', $finRaw );

			$answer = preg_replace( '/```json|```/i', '', $finRaw );
			if ( $jsonEval->evaluateText( $answer )->getResults()['score'] ?? 0 ) {
				$log( 'done', [ 'iterations' => $it, 'json_bytes' => strlen( $answer ) ] );
				NodeResponse::toolPrompt( $answer, $calls, $model, [
					'iterations'     => $it,
					'job_id'         => $input['job_id'] ?? null,
					'session_id'     => $input['session_id'] ?? null,
					'execution_time' => (int) ( ( microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'] ) * 1000 ),
				] );

				return;
			}
			$conv[] = Message::assistant( "❌ Invalid JSON. Output ONLY the object." );
		}

		$log( 'error', 'final JSON invalid after retry' );
		NodeResponse::error( 'Model failed to produce valid JSON.', 500 );
	}

	/* ------------------------------------------------------------------ */
	private static function exampleFor( string $tool ): string {
		return match ( $tool ) {
			'read_file' => '{"name":"read_file","arguments":{"path":"includes/utils.php"}}',
			'list_files' => '{"name":"list_files","arguments":{"directory":"."}}',
			'search_pattern' => '{"name":"search_pattern","arguments":{"pattern":"foo","directory":"."}}',
			default => sprintf( '{"name":"%s","arguments":{}}', $tool ),
		};
	}
}
