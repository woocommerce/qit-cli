<?php
/**  QIT – dynamic tool‑calling endpoint (v3‑b, resilient parser)  */

namespace QIT_AI_Webserver\Endpoints;

use LLPhant\Chat\Message;
use LLPhant\Chat\FunctionInfo\FunctionFormatter;
use LLPhant\Chat\FunctionInfo\ToolCall;
use QIT_AI_Webserver\Lib\ExtractPathResolver;
use QIT_AI_Webserver\Lib\PromptContext;
use QIT_AI_Webserver\Lib\ToolPathGuard;
use QIT_AI_Webserver\NodeResponse;
use QIT_AI_Webserver\ToolRegistry;

class PromptWithToolsEndpoint extends AbstractEndpoint {

	/* ------------------------------------------------------------------ */
	/* 1.  HTTP route                                                     */
	/* ------------------------------------------------------------------ */
	public function get_route(): string {
		return '/prompt-with-tools';
	}


	/* -------- Context‑safety thresholds (character length) ---------------- */
	private const MAX_ASSISTANT_TOKENS = 16384;
	private const MAX_TOOL_RESULT_TOKENS = 16384;

	/* -------- Deduplication properties ------------------------------------ */
	// generic hash cache:  tool|json(args) => true
	private array $callHashes = [];

	// read_file coverage:  path => [ [start,end], … ]
	private array $readCoverage = [];

	/* ------------------------------------------------------------------ */
	/* 3.  Helpers                                                        */
	/* ------------------------------------------------------------------ */
	private function needsPathNormalisation( string $tool, ToolRegistry $reg ): bool {
		$toolObj = $reg->getTool( $tool );
		if ( ! $toolObj ) {
			// Unknown tool – nothing to normalise
			return false;
		}

		$spec  = FunctionFormatter::formatOneFunctionToOpenAI(
			$toolObj->getFunctionInfo()
		);
		$props = $spec['parameters']['properties'] ?? [];

		return isset( $props['path'] ) || isset( $props['directory'] );
	}

	private function hashCall( string $tool, array $args ): string {
		return md5( $tool . '|' . json_encode( $args ) );
	}

	private function rangeCovered( string $path, int $start, int $end ): bool {
		if ( ! isset( $this->readCoverage[ $path ] ) ) {
			return false;
		}
		foreach ( $this->readCoverage[ $path ] as [$s, $e] ) {
			if ( $start >= $s && $end <= $e ) {
				return true;
			}   // fully inside
		}

		return false;
	}

	private function rememberRange( string $path, int $start, int $end ): void {
		$this->readCoverage[ $path ][] = [ $start, $end ];
		// TODO: coalesce overlaps if heavy usage is expected
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
			$debugDir = rtrim(sys_get_temp_dir(), '/\\') . '/qit-node/debug';
			if (!is_dir($debugDir)) {
				mkdir($debugDir, 0700, true);
			}
			file_put_contents(
				$debugDir . '/debug-prompt.log',
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
		$format        = $input['format'] ?? null;
		$maxIterations = (int) ( $input['max_iterations'] ?? 12 );
		$minToolCalls  = (int) ( $input['min_tool_calls'] ?? 2 );

		$log( 'validated', compact( 'format', 'minToolCalls' ) );

		/* 4.3  boot LLM -------------------------------------------------- */
		$chat = $this->chat;
		$chat->setModelOption( 'think', false );

		/* 4.4  register tools ------------------------------------------- */
		$workDir   = ExtractPathResolver::resolve( $input );
		$registry  = new ToolRegistry( $workDir );
		$pathGuard = new ToolPathGuard( $workDir );
		foreach ( $registry->getTools() as $t ) {
			$chat->addTool( $t->getFunctionInfo() );
		}

		/* 4.5  build conversation --------------------------------------- */
		$pathCtx = PromptContext::forWorkspace( $workDir );
		$system  = $pathCtx;
		$conv    = [];
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

		// Tools are already registered with chat->addTool() above

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

			// Use native tool calls from LLPhant
			$toolCalls = [];
			if ( $nativeCalls ) {
				foreach ( $nativeCalls as $call ) {
					$toolCalls[] = [ $call->name, (array) $call->arguments, $call->id ?? uniqid( 'call_', true ) ];
				}
			}

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

				// Use native tool calls from LLPhant
				$toolCalls = [];
				if ( $nativeCalls ) {
					foreach ( $nativeCalls as $call ) {
						$toolCalls[] = [ $call->name, (array) $call->arguments, $call->id ?? uniqid( 'call_', true ) ];
					}
				}

			/* 🩹 Simplified repair rule ------------------------------------ */
			if ( empty( $toolCalls ) && $this->hasToolName( $rawOut, $registry ) ) {
				$toolCalls = $this->repairToolCalls( $rawOut );
				if ( $toolCalls ) {
					$log( 'parsed_tool_calls_repaired', $toolCalls );
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
					$toolResult = $this->executeTool(
						$name, $args, $id,
						$registry, $pathGuard,
						$conv, $logTool,
						$successful, $calls
					);
					$result     = $toolResult['result'];
					$uniqueId   = $toolResult['id'];

					$conv[] = Message::toolResult( json_encode( $result ), $uniqueId );
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
				\QIT_AI_Webserver\Lib\LLPhantBootstrap::getModel(),
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

		// --- duplicate guard -----------------------------------------------
		$hash = $this->hashCall( $tool, $args );
		if ( isset( $this->callHashes[ $hash ] ) ) {
			// Tell the LLM we skipped and avoid counting success
			$conv[] = Message::assistant( "🔄 Duplicate $tool call skipped." );

			return [ 'result' => [ '__note' => 'duplicate-skip' ], 'id' => $id ];
		}

		// Special handling for read_file sub-ranges
		if ( $tool === 'read_file' && isset( $args['path'], $args['start_line'], $args['end_line'] ) ) {
			$path  = $args['path'];
			$start = (int) $args['start_line'];
			$end   = (int) $args['end_line'];
			if ( $this->rangeCovered( $path, $start, $end ) ) {
				$conv[] = Message::assistant(
					"🔄 read_file `$path` lines $start-$end already provided. Skipping."
				);

				return [ 'result' => [ '__note' => 'duplicate-skip' ], 'id' => $id ];
			}
		}

		$logTool( 'tool_call', [ 'id' => $id, 'name' => $tool, 'args' => $args ] );

		$toolObj = $reg->getTool( $tool );
		if ( ! $toolObj ) {
			$conv[] = Message::assistant( "⚠️ Unknown tool '$tool' – skipping." );

			return [ 'result' => [ '__note' => 'unknown-tool' ], 'id' => $id ];
		}

		$result = $toolObj->execute( $args );

		// Handle tool errors
		if ( ! $result['success'] ) {
			$conv[] = Message::assistant( "⚠️ Tool '$tool' failed: " . ( $result['error'] ?? 'Unknown error' ) );

			return [ 'result' => [ '__note' => 'tool-error', 'error' => $result['error'] ], 'id' => $id ];
		}

		// Extract the actual data for the LLM
		$actualResult = $result['data'];
		$logTool( 'tool_result', [ 'id' => $id, 'result' => $actualResult ] );

		$this->callHashes[ $hash ] = true;
		if ( $tool === 'read_file' && isset( $path, $start, $end ) ) {
			$this->rememberRange( $path, $start, $end );
			// (Optional) brief ledger for model
			$ranges = array_map( fn( $r ) => "{$r[0]}-{$r[1]}", $this->readCoverage[ $path ] );
			$conv[] = Message::assistant(
				"📚 Coverage for `$path`: [" . implode( ', ', $ranges ) . "]"
			);
		}

		/* ⚖️  Oversize guard – tool result ------------------------------ */
		$resultJson = json_encode( $actualResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( strlen( $resultJson ) > self::MAX_TOOL_RESULT_TOKENS ) {
			$actualResult = [
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
		$calls[] = [ 'tool' => $tool, 'args' => $args, 'result' => $actualResult ];

		return [ 'result' => $actualResult, 'id' => $uniqueId ];
	}

	/** Return true if $assistantRaw contains any registered tool name. */
	private function hasToolName( string $assistantRaw, ToolRegistry $registry ): bool {
		$text = strtolower( $assistantRaw );
		foreach ( $registry->getTools() as $tool ) {
			$name = strtolower( $tool->getName() );
			// whole‑word match to avoid false positives like "path"
			if ( preg_match( '/\b' . preg_quote( $name, '/' ) . '\b/', $text ) ) {
				return true;
			}
		}

		return false;
	}

	/** ------------------------------------------------------------------
	 *  Try to “repair” a malformed tool request with a micro‑prompt.
	 *  Return the parsed calls in the usual [ [name,args,id], … ] format,
	 *  or an empty array if nothing usable is produced.
	 *  ------------------------------------------------------------------ */
	private function repairToolCalls(
		string $assistantRaw
	): array {
		// 1 · spin up a **fresh, stateless** Chat object so that we
		//    don’t pollute the main conversation with extra tokens.
		$llm     = clone $this->llm;
		$chatFix = $llm->getChat();
		$chatFix->setModelOption( 'think', false );
		$chatFix->setModelOption( 'tool_choice', 'none' );   // we want *text*, not calls

		// 2 · Explain what we need, once, very explicitly
		$callSyntax = "If you need a tool, output ONLY its raw JSON object.";
		$sys        = <<<SYS
Convert the user text into **one** valid tool call.
{$callSyntax}
• Root‑relative paths only (e.g., "includes/utils.php", not "/includes/utils.php").
• Return exactly the <tool_call> … </tool_call> wrapper, nothing else.
SYS;

		// 3 · Do the round‑trip with the malformed text
		$resp = $chatFix->generateChat( [
			Message::system( $sys ),
			Message::user( $assistantRaw ),
		] );

		// 4 · Parse the result with the normal dialect parser
		$content = method_exists( $resp, 'getContent' ) ? $resp->getContent() : (string) $resp;
		$native  = method_exists( $resp, 'getToolCalls' ) ? $resp->getToolCalls() : [];

		// Use native tool calls if available
		$toolCalls = [];
		if ( $native ) {
			foreach ( $native as $call ) {
				$toolCalls[] = [ $call->name, (array) $call->arguments, $call->id ?? uniqid( 'call_', true ) ];
			}
		}
		return $toolCalls;
	}

}
