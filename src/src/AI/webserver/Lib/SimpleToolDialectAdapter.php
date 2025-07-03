<?php
/**  SimpleToolDialectAdapter.php
 *   One class → five static helpers to make every model family “just work”.
 *
 *   Usage inside PromptWithToolsEndpoint:
 *     $dialect = SimpleToolDialectAdapter::detect($model);
 *     SimpleToolDialectAdapter::injectTools($dialect, $registry, $system, $conv);
 *     …
 *     // after the assistant replies:
 *     $toolCalls = SimpleToolDialectAdapter::parseToolCalls($dialect, $rawOut, $nativeCalls);
 *     …
 *     // when you have a $result for call $id:
 *     $conv[] = SimpleToolDialectAdapter::toolResultMessage($dialect, json_encode($result), $id);
 */

namespace QIT_AI_Webserver\Lib;

use LLPhant\Chat\Message;
use LLPhant\Chat\FunctionInfo\FunctionFormatter;
use QIT_AI_Webserver\ToolRegistry;

final class SimpleToolDialectAdapter {
	/* ------------------------------------------------------------------ */
	/* 1. Dialect detection                                               */
	/* ------------------------------------------------------------------ */

	public const OPENAI = 'openai_native';   // GPT‑4(o), Claude‑3, mistral‑large‑latest …
	public const MISTRAL = 'mistral_tags';    // mistral‑*‑instruct checkpoints
	public const LLAMA = 'llama_json';      // llama‑*, codellama‑*, deepseek‑coder‑*
	public const QWEN = 'qwen_xml';        // qwen‑*, qwen2.5‑coder‑*
	public const LEGACY = 'legacy_inline';   // anything else

	/** Return one of the constants above. */
	public static function detect( string $model ): string {
		$m = strtolower( $model );

		return match ( true ) {
			str_contains( $m, 'gpt-' ),
			str_contains( $m, 'claude' ),
			str_contains( $m, 'mistral-large' ) => self::OPENAI,

			str_contains( $m, 'mistral' ) => self::MISTRAL,
			str_contains( $m, 'llama' ),
			str_contains( $m, 'codellama' ),
			str_contains( $m, 'deepseek' ) => self::LLAMA,
			str_contains( $m, 'qwen' ) => self::QWEN,
			default => self::LEGACY,
		};
	}

	/** Whether the model family returns a `tool_calls` array natively. */
	public static function supportsNative( string $dialect ): bool {
		return $dialect === self::OPENAI;
	}

	/* Return the user‑prompt line that asks the model to think or call.  */
	public static function callInstruction( string $dialect ): string {
		return match ( $dialect ) {
			self::QWEN => // tell Qwen to use its XML wrapper
			"If you need a tool, output the <tool_call> JSON exactly as instructed, with no back‑ticks, and nothing else.",
			self::MISTRAL =>
			"If you need a tool, output a [TOOL_CALLS] JSON array.",
			default =>
			"If you need a tool, output ONLY its raw JSON object.",   // legacy
		};
	}

	/* ------------------------------------------------------------------ */
	/* 2. Add tool specifications to the prompt                           */
	/* ------------------------------------------------------------------ */

	/**
	 * Mutates $system / $conv so that the model sees the list of tools
	 * in the format it understands.
	 *
	 * @param ToolRegistry $registry All registered tools
	 * @param string &$system The system message you are building
	 * @param array  &$conv The conversation array (LLPhant messages)
	 */
	public static function injectTools(
		string $dialect,
		ToolRegistry $registry,
		string &$system,
		array &$conv
	): void {
		/* Build the OpenAI‑style JSON spec once; most dialects reuse it. */
		$specs = [];
		foreach ( $registry->getTools() as $tool ) {
			$fn      = FunctionFormatter::formatOneFunctionToOpenAI( $tool->getFunctionInfo() );
			$specs[] = [ 'type' => 'function', 'function' => $fn ];
		}

		switch ( $dialect ) {
			case self::OPENAI:
				/* The OpenAI / Anthropic SDK takes $chat->addTool() — already done
				   in your endpoint. Nothing else required. */
				break;

			case self::MISTRAL:
				/* Add [AVAILABLE_TOOLS]… block **before** the first user turn.   */
				$system = "[AVAILABLE_TOOLS]" . json_encode( $specs, JSON_UNESCAPED_UNICODE )
				          . "[/AVAILABLE_TOOLS]\n" . $system;
				break;

			case self::LLAMA:
				/* Your existing `llama.jinja` template already expects the tool
				   block inside the system message.                                */
				$block = "\n\nGiven the following functions, please respond with a JSON …\n";
				foreach ( $specs as $s ) {
					$block .= json_encode( $s, JSON_UNESCAPED_UNICODE ) . "\n";
				}
				$system .= $block;
				break;

			case self::QWEN:
				$xml = "<tools>\n";
				foreach ( $specs as $s ) {
					$xml .= json_encode( $s['function'], JSON_UNESCAPED_UNICODE ) . "\n";
				}
				$xml    .= "</tools>\n";
				$system .= "\n# Tools\nYou may call one or more functions …\n" . $xml;
				break;

			default:
				/* nothing                                     */
				break;
		}
	}

	/* ------------------------------------------------------------------ */
	/* 3. Parse the assistant’s reply and pull out tool calls             */
	/* ------------------------------------------------------------------ */

	/**
	 * @param string $assistantRaw `Message::assistant()->content`
	 * @param array $nativeCalls `$resp->getToolCalls()` from LLPhant (may be [])
	 *
	 * @return array [ [name,args,id], … ]
	 */
	public static function parseToolCalls(
		string $dialect,
		string $assistantRaw,
		array $nativeCalls = []
	): array {
		/* OPENAI family: the SDK already parsed them. */
		if ( $dialect === self::OPENAI && $nativeCalls ) {
			return array_map(
				fn( $c ) => [ $c->name, (array) $c->arguments, $c->id ?? uniqid( 'call_', true ) ],
				$nativeCalls
			);
		}

		/* Mistral style: [TOOL_CALLS]name[CALL_ID]xyz[ARGS]{…}      */
		if ( $dialect === self::MISTRAL && str_contains( $assistantRaw, '[TOOL_CALLS]' ) ) {
			preg_match_all(
				'/\[TOOL_CALLS](.+?)\[CALL_ID](.+?)\[ARGS](\{.*?})(?=\[TOOL_CALLS]|$)/s',
				$assistantRaw,
				$m,
				PREG_SET_ORDER
			);
			$out = [];
			foreach ( $m as $hit ) {
				$out[] = [
					trim( $hit[1] ),
					json_decode( $hit[3], true ) ?? [],
					trim( $hit[2] ),
				];
			}

			return $out;
		}

		/* Llama & legacy: single JSON object on its own */
		if ( in_array( $dialect, [ self::LLAMA, self::LEGACY ], true )
		     && preg_match( '/\{.*"name"\s*:\s*".+?".*"parameters"\s*:\s*\{.*\}\s*\}/s', $assistantRaw, $m )
		) {
			$call = json_decode( $m[0], true );

			return [
				[
					$call['name'] ?? '',
					$call['parameters'] ?? [],
					uniqid( 'call_', true ),
				],
			];
		}

		/* Qwen: <tool_call>{"name":…, "arguments":{…}}</tool_call>  */
		if ( $dialect === self::QWEN && str_contains( $assistantRaw, '<tool_call>' ) ) {
			preg_match_all( '/<tool_call>\s*(\{.*?\})\s*<\/tool_call>/s', $assistantRaw, $all );
			$out = [];
			foreach ( $all[1] ?? [] as $json ) {
				$call  = json_decode( $json, true );
				$out[] = [
					$call['name'] ?? '',
					$call['arguments'] ?? [],
					uniqid( 'call_', true ),
				];
			}

			return $out;
		}

		/* ------------------------------------------------------------------
		   UNIVERSAL FALLBACK – extract every top‑level JSON object that
		   contains a "name" key. Works even when prose surrounds the object.
		-------------------------------------------------------------------*/
		$calls = [];
		$depth = 0;
		$start = null;
		for ( $i = 0, $len = strlen( $assistantRaw ); $i < $len; $i ++ ) {
			$ch = $assistantRaw[ $i ];
			if ( $ch === '{' ) {
				if ( $depth === 0 ) {
					$start = $i;
				}
				$depth ++;
			} elseif ( $ch === '}' ) {
				$depth --;
				if ( $depth === 0 && $start !== null ) {
					$json = substr( $assistantRaw, $start, $i - $start + 1 );
					$obj  = json_decode( $json, true );
					if ( is_array( $obj ) && isset( $obj['name'] ) ) {
						$calls[] = [
							$obj['name'],
							$obj['arguments'] ?? $obj['parameters'] ?? [],
							uniqid( 'call_', true ),
						];
					}
					$start = null;
				}
			}
		}

		return $calls;

		return [];   // no calls detected
	}

	/* ------------------------------------------------------------------ *
	 * 5.  One‑shot demo for the model                                    *
	 * ------------------------------------------------------------------ */

	/**
	 * Return an array with two strings:
	 *   [ assistant‑formatted tool call , matching minimal tool_response ]
	 * or `null` if the model family does not need a primer.
	 *
	 * The demo is deliberately tiny (calls `list_files` on project root)
	 * and uses the exact wrapper syntax each dialect expects.
	 */
	public static function demoCall( string $dialect ): ?array {
		/* The real files listed do not matter – they are never executed. */
		$fakeResponse = json_encode( [ 'files' => [ 'index.php', 'readme.txt' ] ] );

		return match ( $dialect ) {
			/* Qwen requires the <tool_call> wrapper. */
			self::QWEN => [
				'<tool_call>{"name":"list_files","arguments":{"directory":"."}}</tool_call>',
				$fakeResponse
			],

			/* Mistral‑style tags */
			self::MISTRAL => [
				'[TOOL_CALLS]list_files[CALL_ID]demo_1[ARGS]{"directory":"."}',
				$fakeResponse
			],

			/* Llama‑JSON or any legacy model that only sees a raw object */
			self::LLAMA,
			self::LEGACY => [
				'{"name":"list_files","parameters":{"directory":"."}}',
				$fakeResponse
			],

			/* OpenAI‑native models already get structured calls from the SDK. */
			default => null,
		};
	}


	/* ------------------------------------------------------------------ */
	/* 4. Build the tool‑result message that goes back into the convo     */
	/* ------------------------------------------------------------------ */

	/**
	 * Return an LLPhant Message object containing the tool result,
	 * wrapped exactly how the model expects to see it.
	 */
	public static function toolResultMessage(
		string $dialect,
		string $jsonResult,
		string $callId
	): Message {
		return match ( $dialect ) {
			self::MISTRAL => Message::assistant(
				"[TOOL_RESULTS] " . json_encode( [ [ 'content' => $jsonResult ] ] ) . " [/TOOL_RESULTS]"
			),

			self::QWEN =>
				// Send as a proper 'tool' role message; LLPhant will wrap it into
				// <tool_response> ... </tool_response> for the model.
			Message::toolResult( $jsonResult, $callId ),

			/* Llama and OpenAI just need a normal tool‑role message */
			default => Message::toolResult( $jsonResult, $callId ),
		};
	}
}
