<?php
/**  QIT – dynamic tool‑calling endpoint (v3‑b, resilient parser)  */

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
	/* 1.  HTTP route                                                     */
	/* ------------------------------------------------------------------ */
	public function get_route(): string {
		return '/prompt-with-tools';
	}

	/* ------------------------------------------------------------------ */
	/* 2.  Model ↔ dialect map                                            */
	/* ------------------------------------------------------------------ */
	private const MODEL_DIALECT_MAP = [
		'qwen2.5-coder:7b'            => Dialect::QWEN,
		'qwen2.5-coder:32b'           => Dialect::QWEN,
		'hhao/qwen2.5-coder-tools:7b' => Dialect::QWEN,
		'mistral-small3.2:24b'        => Dialect::MISTRAL,
		// 'gpt-4o'                   => Dialect::OPENAI,
		// 'llama-3-70b-instruct'     => Dialect::LLAMA,
	];

	/* -------- Context‑safety thresholds (character length) ---------------- */
	private const MAX_ASSISTANT_TOKENS   = 16384;
	private const MAX_TOOL_RESULT_TOKENS = 16384;

	/* ------------------------------------------------------------------ */
	/* 3.  Helpers                                                        */
	/* ------------------------------------------------------------------ */
	private function needsPathNormalisation( string $tool, ToolRegistry $reg ): bool {
		$spec  = FunctionFormatter::formatOneFunctionToOpenAI(
			$reg->getTool( $tool )->getFunctionInfo()
		);
		$props = $spec['parameters']['properties'] ?? [];

		return isset( $props['path'] ) || isset( $props['directory'] );
	}

	/* ------------------------------------------------------------------ */
	/* 4.  Main handler                                                   */
	/* ------------------------------------------------------------------ */
	public function handle( array $input ): void {

		/* 4.0  logger ---------------------------------------------------- */
		$dbg     = [];
		$log     = function ( string $stage, $data = null ) use ( &$dbg ) {
			$dbg[] = [
				'ts_ms' => (int) ( microtime( true ) * 1000 ),
				'stage' => $stage,
				'data'  => $data,
			];
			file_put_contents(
				'/tmp/debug-prompt.log',
				json_encode( $dbg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE )
			);
		};
		$logTool = fn( string $ev, array $p ) => $log( $ev, $p );

		/* 4.1  validate -------------------------------------------------- */
		foreach ( [ 'messages', 'model' ] as $k ) {
			if ( ! isset( $input[ $k ] ) ) {
				NodeResponse::error( "Missing required parameter: {$k}", 400 );
			}
		}
		$raw           = $input['messages'];
		$model         = (string) $input['model'];
		$format        = $input['format'] ?? null;
		$maxIterations = (int) ( $input['max_iterations'] ?? 12 );
		$minToolCalls  = (int) ( $input['min_tool_calls'] ?? 2 );
		$log( 'validated', compact( 'model', 'format', 'minToolCalls' ) );

		if ( ! isset( self::MODEL_DIALECT_MAP[ $model ] ) ) {
			NodeResponse::error( "Model '{$model}' not mapped.", 400 );
		}
		$dialect = self::MODEL_DIALECT_MAP[ $model ];
		$log( 'dialect', $dialect );

		/* 4.3  boot LLM -------------------------------------------------- */
		$this->providerConfig['model'] = $model;
		$this->initializeLLM();
		$this->llm->ensureInitialized();
		$chat = $this->llm->getChat();
		$chat->setModelOption( 'think', false );

		/* 4.4  register tools ------------------------------------------- */
		$workDir   = ExtractPathResolver::resolve( $input );
		$registry  = new ToolRegistry( $workDir );
		$pathGuard = new ToolPathGuard( $workDir );
		foreach ( $registry->getTools() as $t ) {
			$chat->addTool( $t->getFunctionInfo() );
		}

		/* 4.5  build conversation --------------------------------------- */
		$system = '';
		$conv   = [];
		foreach ( $raw as $m ) {
			if ( $m['role'] === 'system' ) {
				$system .= $m['content'] . "\n";
			} else {
				$conv[] = Message::{$m['role']}( $m['content'] );
			}
		}

		$system .= <<<SCRIPT
Run a **structured vulnerability investigation** with tools.

You must find:
- The name of the function that wraps the vulnerability
- Where this function is invoked in the codebase
- Whether this function is hooked to any WordPress action or filter (hooks)
- Whether these hooks are user-driven (tainted)
- If there are other forms of user input that can trigger the vulnerability
- The evidence for your findings (file, line range, code snippet)
- Any other relevant information you deem relevant for context awareness
- Then, you should look inward for the function and the logic around the vulnerability
- Understand the intended behavior of the developer
- Understand the business rules
- Understand what the code do
- Understand what the code should do
- Understand what the code should not do
- Understand the security implications of the code
- Any other relevant information you deem relevant for context awareness
SCRIPT;

		if ( ! Dialect::supportsNative( $dialect ) ) {
			$system .= "\n" . Dialect::callInstruction( $dialect );
		}
		Dialect::injectTools( $dialect, $registry, $system, $conv );

		$chat->setSystemMessage( trim( $system ) );

		/* 4.6  unwrap helper -------------------------------------------- */
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

			return [ $raw ?? '', $calls ?? [] ];
		};

		/* 4.7  main loop ------------------------------------------------- */
		$iter    = $successful = 0;
		$summary = '';
		$calls   = [];

		while ( ++ $iter <= $maxIterations ) {

			$example             = '<tool_call>{"name":"list_files","arguments":{"directory":"."}}</tool_call>';
			$remaining           = $minToolCalls - $successful;
			$quota_unmet_message = "🧠 Decide next step → emit ONE <tool_call>…</tool_call> "
			                       . "(you have executed $successful, need $remaining more).\n"
			                       . "Format example → {$example}";

			$quota_met_message = "🧠 Decide next step → either emit ONE <tool_call>…</tool_call> "
			                     . "or reply **done**.\n"
			                     . "Format example → {$example}";
			$conv[]            = Message::user( $remaining > 0 ? $quota_unmet_message : $quota_met_message );

			$log( 'prompt', json_encode( $conv ) );

			[ $rawOut, $nativeCalls ] = $unwrap( $chat->generateChat( $conv ) );

			/* ⚖️  Oversize guard – assistant reply --------------------- */
			if ( strlen( $rawOut ) > self::MAX_ASSISTANT_TOKENS ) {
				$rawOut = trim(
					$this->llm->getChat()->generateChat( [
						Message::system(
							"Summarise the following assistant reply to roughly 25 % of its original " .
							"token count (never >900 tokens). Preserve **all** file paths, line numbers, " .
							"function/class names and any code blocks verbatim. Respond with *only* the " .
							"summary."
						),
						Message::user( $rawOut ),
					] )
				);
			}

			$log( 'response_raw', $rawOut );
			$toolCalls = Dialect::parseToolCalls( $dialect, $rawOut, $nativeCalls );

			/*  🚑  fallback parser:  toolName { json‑args } ---------------- */
			if ( empty( $toolCalls ) ) {
				foreach ( preg_split( '/\R+/', $rawOut ) as $line ) {
					if ( preg_match( '/^\s*([a-zA-Z_][\w-]*)\s+(\{.*\})\s*$/s', trim( $line ), $m ) ) {
						$args = json_decode( $m[2], true );
						if ( is_array( $args ) ) {
							$toolCalls[] = [ $m[1], $args, uniqid( 'rx_', true ) ];
						}
					}
				}
				if ( $toolCalls ) {
					$log( 'parsed_tool_calls_fallback', $toolCalls );
				}
			}

			$thought = trim( $rawOut );
			if ( $thought !== '' && empty( $toolCalls ) ) {
				$conv[] = Message::assistant( $thought );
			}

			/* done? */
			if ( preg_match( '/\b(done|finished)\b/i', $thought ) ) {
				if ( $successful >= $minToolCalls ) {
					$summary = $thought;
					break;
				}
				continue;
			}

			/* execute tool(s) */
			if ( $toolCalls ) {
				foreach ( $toolCalls as [$name, $args, $id] ) {
					$log( 'tool_execute', [ 'id' => $id, 'name' => $name, 'args' => $args ] );
					$toolResult = $this->executeTool(
						$dialect, $name, $args, $id,
						$registry, $pathGuard,
						$conv, $logTool,
						$successful, $calls
					);
					$result     = $toolResult['result'];
					$uniqueId   = $toolResult['id'];
					$log( 'tool_executed', [ 'id' => $id, 'unique_id' => $uniqueId, 'result' => $result ] );

					$conv[] = Dialect::toolResultMessage( $dialect, json_encode( $result ), $uniqueId );
				}
				continue;
			}

			/* nothing actionable */
			$conv[] = Message::assistant( "❌ No usable tool call. Think again." );
		}

		if ( $summary === '' ) {
			$log( 'error', 'loop ended w/o summary' );
			NodeResponse::error( 'Model never produced summary.', 500 );
		}

		/* 4.8  final JSON ------------------------------------------------ */
		$conv[] = Message::assistant( $summary );
		$conv[] = Message::user( "🟢 Reply ONLY with the final JSON object." );
		$chat->setModelOption( 'tool_choice', 'none' );
		$chat->setModelOption( 'format', $format );

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

			/* evidence & schema check identical to previous version -------- */
			if ( ! isset( $decoded['evidence'][0] ) ) {
				$conv[] = Message::assistant( "❌ Provide at least one evidence block." );
				continue;
			}

			$log( 'done', [ 'iterations' => $iter, 'json_bytes' => strlen( $answer ) ] );
			NodeResponse::toolPrompt(
				$answer,
				$calls,
				$model,
				[
					'iterations'     => $iter,
					'job_id'         => $input['job_id'] ?? null,
					'session_id'     => $input['session_id'] ?? null,
					'execution_time' => (int) ( ( microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'] ) * 1000 ),
				]
			);

			return;
		}

		$log( 'error', 'final JSON invalid after retry' );
		NodeResponse::error( 'Model failed to produce valid JSON.', 500 );
	}

	/* ------------------------------------------------------------------
	   5.  Execute a tool + logging                                       */
	/* ------------------------------------------------------------------ */
	private function executeTool(
		string $dialect,
		string $tool,
		array $args,
		string $id,
		ToolRegistry $reg,
		ToolPathGuard $guard,
		array &$conv,
		callable $logTool,
		int &$ok,
		array &$calls
	) {
		if ( $tool === 'list_files' && isset( $args['path'] ) && ! isset( $args['directory'] ) ) {
			$args['directory'] = $args['path'];
		}
		try {
			if ( $this->needsPathNormalisation( $tool, $reg ) ) {
				$key          = $tool === 'read_file' ? 'path' : 'directory';
				$args[ $key ] = $guard->normalise( $args[ $key ] ?? '.' );
			}
		} catch ( \RuntimeException ) {
			return [ 'result' => null, 'id' => $id ];
		}

		$logTool( 'tool_call', [ 'id' => $id, 'name' => $tool, 'args' => $args ] );
		$result = $reg->getTool( $tool )->execute( $args );
		$logTool( 'tool_result', [ 'id' => $id, 'result' => $result ] );

		/* ⚖️  Oversize guard – tool result ------------------------------ */
		$resultJson = json_encode( $result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( strlen( $resultJson ) > self::MAX_TOOL_RESULT_TOKENS ) {
			$result = [
				'__summary' => trim(
					$this->llm->getChat()->generateChat( [
						Message::system(
							"Summarise the following tool output to ≤20 % of its length (cap 600 tokens), " .
							"keeping every path, line number and code block intact. Return only the summary."
						),
						Message::user( $resultJson ),
					] )
				),
			];
		}

		// Generate a new unique ID for the ToolCall to avoid duplicate counting
		$uniqueId = uniqid( 'call_', true );
		$conv[]   = Message::assistantAskingTools( [
			new ToolCall( $uniqueId, $tool, json_encode( $args, JSON_UNESCAPED_UNICODE ) ),
		] );

		++ $ok;
		$calls[] = [ 'toolName' => $tool, 'args' => $args, 'result' => $result ];

		return [ 'result' => $result, 'id' => $uniqueId ];
	}
}
