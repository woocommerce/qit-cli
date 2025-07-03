<?php
/**  QIT – dynamic tool‑calling endpoint (v2, dialect‑aware)  */

namespace QIT_AI_Webserver\Endpoints;

use LLPhant\Chat\Message;
use LLPhant\Chat\FunctionInfo\FunctionFormatter;
use LLPhant\Chat\FunctionInfo\ToolCall;
use LLPhant\Evaluation\Output\JSONFormatEvaluator;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\Lib\ToolPathGuard;
use QIT_AI_Webserver\Lib\SimpleToolDialectAdapter as Dialect;
use QIT_AI_Webserver\NodeResponse;
use QIT_AI_Webserver\ToolRegistry;

class PromptWithToolsEndpoint extends AbstractEndpoint {

	/* ------------------------------------------------------------------ */
	/*  ❱❱ 1.  HTTP route                                                */
	/* ------------------------------------------------------------------ */
	public function get_route(): string {
		return '/prompt-with-tools';
	}

	/* ------------------------------------------------------------------ */
	/*  ❱❱ 2.  Map your models → dialects here (fill in as needed)       */
	/* ------------------------------------------------------------------ */
	private const MODEL_DIALECT_MAP = [
		// 'gpt-4o'              => Dialect::OPENAI,
		// 'llama-3-70b-instruct'=> Dialect::LLAMA,
		'qwen2.5-coder:7b'            => Dialect::QWEN,
		'hhao/qwen2.5-coder-tools:7b' => Dialect::QWEN,
		'mistral-small3.2:24b'        => Dialect::MISTRAL,
	];

	/* ------------------------------------------------------------------ */
	/*  ❱❱ 3.  Helpers                                                   */
	/* ------------------------------------------------------------------ */
	/** True iff the tool’s schema defines a 'path' or 'directory' field. */
	private function needsPathNormalisation( string $toolName, ToolRegistry $registry ): bool {
		$tool = $registry->getTool( $toolName );
		if ( ! $tool ) {
			return false;
		}
		$spec  = FunctionFormatter::formatOneFunctionToOpenAI( $tool->getFunctionInfo() );
		$props = $spec['parameters']['properties'] ?? [];

		return isset( $props['path'] ) || isset( $props['directory'] );
	}

	/* ------------------------------------------------------------------ */
	/*  ❱❱ 4.  Main handler                                              */
	/* ------------------------------------------------------------------ */
	public function handle( array $input ): void {

		/* — 4.0  Logging helper — */
		$dbg     = [];
		$log     = function ( string $stage, $data = null ) use ( &$dbg ) {
			$dbg[] = [ 'ts_ms' => (int) ( microtime( true ) * 1000 ), 'stage' => $stage, 'data' => $data ];
			file_put_contents(
				'/tmp/debug-prompt.log',
				json_encode( $dbg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE )
			);
		};
		$logTool = fn( string $ev, array $p ) => $log( $ev, $p );

		/* — 4.1  Validate input — */
		foreach ( [ 'messages', 'model' ] as $k ) {
			if ( ! isset( $input[ $k ] ) ) {
				NodeResponse::error( "Missing required parameter: {$k}", 400 );
			}
		}
		$raw           = $input['messages'];
		$model         = (string) $input['model'];
		$format        = $input['format'] ?? null;
		$maxIterations = (int) ( $input['max_iterations'] ?? 10 );
		$minToolCalls  = (int) ( $input['min_tool_calls'] ?? 2 );
		$log( 'validated', compact( 'model', 'format', 'minToolCalls' ) );

		/* — 4.2  Pick dialect (throw if unmapped) — */
		if ( ! isset( self::MODEL_DIALECT_MAP[ $model ] ) ) {
			NodeResponse::error( "Model '{$model}' is not mapped to a tool‑calling dialect.", 400 );
		}
		$dialect = self::MODEL_DIALECT_MAP[ $model ];
		$log( 'dialect', $dialect );

		/* — 4.3  Boot the LLM — */
		$this->providerConfig['model'] = $model;
		$this->initializeLLM();
		$this->llm->ensureInitialized();
		$chat = $this->llm->getChat();
		$chat->setModelOption( 'think', false );

		/* — 4.4  Register tools — */
		$workDir   = ExtractPathResolver::resolve( $input );
		$registry  = new ToolRegistry( $workDir );
		$pathGuard = new ToolPathGuard( $workDir );
		foreach ( $registry->getTools() as $t ) {
			$chat->addTool( $t->getFunctionInfo() );   // still required for OPENAI family
		}

		/* — 4.5  Build initial conversation — */
		$system = '';
		$conv   = [];
		foreach ( $raw as $m ) {
			if ( $m['role'] === 'system' ) {
				$system .= $m['content'] . "\n";
			} else {
				$conv[] = Message::{$m['role']}( $m['content'] );
			}
		}
		$system .= "\nWhen the investigation is complete, say **done** and give a short summary.\n";
		$system .= "You must execute at least {$minToolCalls} tool calls.";

		// If using a dialect that requires tool specs in the system message,
		if (!Dialect::supportsNative($dialect)) {
			$system .= "\n" . Dialect::callInstruction($dialect);
		}

		/*  ▼▼  ← you lost this line while editing  */
		Dialect::injectTools($dialect, $registry, $system, $conv);
		/*  ▲▲  keep it right here                  */

		if ($demo = Dialect::demoCall($dialect)) {
			[$assistantCall, $toolResp] = $demo;

			$conv[] = Message::assistant($assistantCall);
			$conv[] = Dialect::toolResultMessage(      // correct wrapper
				$dialect,
				$toolResp,
				uniqid('demo_', true)
			);
		}

		$chat->setSystemMessage( trim( $system ) );

		/* — 4.6  Helper to unwrap ChatResponse/ChatMessage — */
		$unwrap = function ( $resp ) use ( $log ): array {
			$raw = $calls = null;
			if ( is_object( $resp ) && method_exists( $resp, 'getContent' ) ) {
				$raw   = $resp->getContent();
				$calls = $resp->getToolCalls() ?? [];
			} elseif ( is_object( $resp ) && property_exists( $resp, 'content' ) ) {
				$raw   = $resp->content;
				$calls = $resp->tool_calls ?? $resp->function_calls ?? [];
			} elseif ( is_array( $resp ) && isset( $resp['content'] ) ) {
				$raw   = $resp['content'];
				$calls = $resp['tool_calls'] ?? [];
			} else {
				$raw = (string) $resp;
			}
			$log( 'raw_response_object', is_scalar( $resp ) ? $resp : json_encode( $resp, JSON_PARTIAL_OUTPUT_ON_ERROR ) );

			return [ $raw ?? '', $calls ?? [] ];
		};

		/* — 4.7  Main loop — */
		$iter         = 0;
		$successful   = 0;
		$summary      = '';
		$calls        = [];
		$noToolRounds = 0;
		$nativeOK     = Dialect::supportsNative( $dialect );

		while ( ++ $iter <= $maxIterations ) {

			$conv[] = $ask = Message::user(
				"🧠 Reason about the next step. " . Dialect::callInstruction( $dialect )
			);
			$log( 'prompt', $ask->content );

			[ $rawOut, $nativeCalls ] = $unwrap( $chat->generateChat( $conv ) );
			$log( 'response_raw', $rawOut );

			/* Parse tool calls (dialect aware) */
			$toolCalls = Dialect::parseToolCalls( $dialect, $rawOut, $nativeCalls );
			$log( 'parsed_tool_calls', $toolCalls );

			/* Show assistant thought if any text remains */
			$thought = trim( $rawOut );
			if ( $thought !== '' && empty( $toolCalls ) ) {
				$conv[] = Message::assistant( $thought );
			}

			/* Check for "done" */
			if ( preg_match( '/\b(done|finished)\b/i', $thought ) ) {
				if ( $successful >= $minToolCalls ) {
					$summary = $thought;
					break;
				}
				$conv[] = Message::assistant(
					"❌ Only {$successful} tool calls executed; need {$minToolCalls}. Continue."
				);
				continue;
			}

			/* Execute each detected tool call */
			if ( $toolCalls ) {
				foreach ( $toolCalls as [$name, $args, $id] ) {
					$log( 'tool_execute', [ 'id' => $id, 'name' => $name, 'args' => $args ] );
					$result = $this->executeTool(
						$dialect, $name, $args, $id,
						$registry, $pathGuard,
						$conv, $logTool,
						$successful, $calls
					);
					$log( 'tool_executed', [ 'id' => $id, 'result' => $result ] );
					/* Insert tool result message in dialect‑specific wrapper */
					$conv[] = Dialect::toolResultMessage( $dialect, json_encode( $result ), $id );
					// ask the model to reason on the fresh evidence
					$conv[] = Message::user( "✅ Result received – interpret it, then decide next step or **done**." );
				}
				continue;
			}

			/* Nothing actionable */
			$conv[] = Message::assistant( "❌ You produced no usable tool call. Think again." );
			if ( ++ $noToolRounds >= 3 ) {
				$conv[]       = Message::assistant( "❌ You must now issue a valid tool call." );
				$noToolRounds = 0;
			}
		}

		if ( $summary === '' ) {
			$log( 'error', 'loop ended w/o summary' );
			NodeResponse::error( 'Model never produced summary.', 500 );
		}

		/* — 4.8  Ask for the final JSON answer (unchanged from original) — */
		$conv[] = Message::assistant( $summary );
		$conv[] = Message::user( "🟢 Reply ONLY with the final JSON object." );
		$chat->setModelOption( 'tool_choice', 'none' );
		$chat->setModelOption( 'format', $format );

		$jsonEval = new JSONFormatEvaluator();

		for ( $attempt = 0; $attempt < 3; $attempt ++ ) {
			$log( 'prompt', '[final JSON request]' );
			[ $finRaw ] = $unwrap( $chat->generateChat( $conv ) );
			$log( 'response', $finRaw );

			$answer  = preg_replace( '/```json|```/i', '', $finRaw );
			$decoded = json_decode( $answer, true );
			if ( $decoded === null ) {
				$conv[] = Message::assistant( "❌ Invalid JSON. Output ONLY the object." );
				continue;
			}

			/* evidence verifier + schema checker …  (identical to your previous code) */
			if ( ! isset( $decoded['evidence'] ) || $decoded['evidence'] === [] ) {
				$conv[] = Message::assistant( "❌ Provide at least one evidence block." );
				continue;
			}
			$evidenceOk = true;
			$badIdx     = null;
			foreach ( ( $decoded['evidence'] ?? [] ) as $idx => $ev ) {
				if ( ! isset( $ev['file'], $ev['start_line'], $ev['end_line'], $ev['snippet'] ) ) {
					$evidenceOk = false;
					$badIdx     = $idx;
					break;
				}
				try {
					$src = $registry->getTool( 'read_file' )->execute( [
						'path'       => $pathGuard->normalise( $ev['file'] ),
						'start_line' => $ev['start_line'],
						'end_line'   => $ev['end_line'],
					] )['content'] ?? '';
					if (
						strpos(
							preg_replace( '/\s+/', '', $src ),
							preg_replace( '/\s+/', '', $ev['snippet'] )
						) === false
					) {
						$evidenceOk = false;
						$badIdx     = $idx;
						break;
					}
				} catch ( \Throwable $e ) {
					$evidenceOk = false;
					$badIdx     = $idx;
					break;
				}
			}
			if ( ! $evidenceOk ) {
				$conv[] = Message::assistant( "❌ Evidence block #{$badIdx} does not match cited lines. Fix it." );
				continue;
			}

			if ( ( $jsonEval->evaluateText( $answer )->getResults()['score'] ?? 0 ) !== 1 ) {
				$conv[] = Message::assistant( "❌ JSON does not match required schema. Only the object!" );
				continue;
			}

			$log( 'done', [ 'iterations' => $iter, 'json_bytes' => strlen( $answer ) ] );
			NodeResponse::toolPrompt( $answer, $calls, $model, [
				'iterations'     => $iter,
				'job_id'         => $input['job_id'] ?? null,
				'session_id'     => $input['session_id'] ?? null,
				'execution_time' => (int) ( ( microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'] ) * 1000 ),
			] );

			return;
		}

		$log( 'error', 'final JSON invalid after retry' );
		NodeResponse::error( 'Model failed to produce valid JSON.', 500 );
	}

	/* ------------------------------------------------------------------
	   ❱❱ 5.  Execute a tool + logging (returns result)                   */
	/* ------------------------------------------------------------------ */
	private function executeTool(
		string $dialect,
		string $toolName,
		array $args,
		string $id,
		ToolRegistry $registry,
		ToolPathGuard $pathGuard,
		array &$conv,
		callable $logTool,
		int &$successful,
		array &$calls
	) {
		/* Special‑case alias for list_files */
		if ( $toolName === 'list_files' && isset( $args['path'] ) && ! isset( $args['directory'] ) ) {
			$args['directory'] = $args['path'];
		}

		/* Path guard if required */
		try {
			if ( $this->needsPathNormalisation( $toolName, $registry ) ) {
				$key          = $toolName === 'read_file' ? 'path' : 'directory';
				$args[ $key ] = $pathGuard->normalise( $args[ $key ] ?? '.' );
			}
		} catch ( \RuntimeException $e ) {
			return null;
		}

		/* Execute & log */
		$logTool( 'tool_call', [ 'id' => $id, 'name' => $toolName, 'args' => $args ] );
		$result = $registry->getTool( $toolName )->execute( $args );
		$logTool( 'tool_result', [ 'id' => $id, 'result' => $result ] );

		/* Notify the LLM that a tool was called */
		$conv[] = Message::assistantAskingTools( [
			new ToolCall( $id, $toolName, json_encode( $args, JSON_UNESCAPED_UNICODE ) ),
		] );

		++ $successful;      // actually propagates because $successful is &‑passed
		$calls[] = [ 'toolName' => $toolName, 'args' => $args, 'result' => $result ];

		return $result;
	}
}
