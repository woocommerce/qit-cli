<?php
/**
 *  SimpleToolDialectAdapter.php  – single class, five helpers.
 *  NEW in this version:
 *    • demoCalls(): one-shot example for *each* tool
 *    • tighter call‑instructions for Qwen (repeat each turn)
 */

namespace QIT_AI_Webserver\Lib;

use LLPhant\Chat\Message;
use LLPhant\Chat\FunctionInfo\FunctionFormatter;
use QIT_AI_Webserver\ToolRegistry;

final class SimpleToolDialectAdapter {
	/* ------------------------------------------------------------------ */
	/* 1. Dialect detection                                               */
	/* ------------------------------------------------------------------ */
	public const OPENAI = 'openai_native';
	public const MISTRAL = 'mistral_tags';
	public const LLAMA = 'llama_json';
	public const QWEN = 'qwen_xml';
	public const LEGACY = 'legacy_inline';

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

	public static function supportsNative( string $dialect ): bool {
		return $dialect === self::OPENAI;
	}

	public static function callInstruction( string $dialect ): string {
		return match ( $dialect ) {
			self::QWEN =>
				// hard, repetitive guard – this is what nudges Qwen
				"If you need a tool, output *exactly one* " .
				"<tool_call>{\"name\":\"…\",\"arguments\":{…}}</tool_call>. " .
				"NO other text, no markdown.",
			self::MISTRAL =>
			"If you need a tool, output a [TOOL_CALLS] JSON array.",
			default =>
			"If you need a tool, output ONLY its raw JSON object.",
		};
	}

	/* ------------------------------------------------------------------ */
	/* 2. Prompt‑time injection of tool specs (unchanged)                 */
	/* ------------------------------------------------------------------ */

	public static function injectTools(
		string $dialect,
		ToolRegistry $registry,
		string &$system,
		array &$conv
	): void {
		/* Build once – OpenAI format */
		$specs = [];
		foreach ( $registry->getTools() as $tool ) {
			$specs[] = [
				'type'     => 'function',
				'function' =>
					FunctionFormatter::formatOneFunctionToOpenAI( $tool->getFunctionInfo() ),
			];
		}

		switch ( $dialect ) {
			case self::OPENAI:
				/* already handled by LLPhant addTool() */
				break;

			case self::MISTRAL:
				$system = "[AVAILABLE_TOOLS]" .
				          json_encode( $specs, JSON_UNESCAPED_UNICODE ) .
				          "[/AVAILABLE_TOOLS]\n" . $system;
				break;

			case self::LLAMA:
				$block = "\n\nGiven these functions, answer with a JSON object only:\n";
				foreach ( $specs as $s ) {
					$block .= json_encode( $s['function'], JSON_UNESCAPED_UNICODE ) . "\n";
				}
				$system .= $block;
				break;

			case self::QWEN:
				$xml = "<tools>\n";
				foreach ( $specs as $s ) {
					$xml .= json_encode( $s['function'], JSON_UNESCAPED_UNICODE ) . "\n";
				}
				$xml    .= "</tools>\n";
				$system .= "\n# Tools\n" . $xml;
				break;

			default: /* nothing */
		}
	}

	/* ------------------------------------------------------------------ */
	/* 3.  Parse tool‑calls (identical to previous version – omitted)      */
	/* ------------------------------------------------------------------ */
	/* … keep the previous parseToolCalls() here … */

	/* ------------------------------------------------------------------ */
	/* 4.  Build tool‑result messages (unchanged)                          */
	/* ------------------------------------------------------------------ */
	/* … keep the previous toolResultMessage() here … */

	/* ------------------------------------------------------------------ */
	/* 5.  NEW — generate demo calls for every tool                        */
	/* ------------------------------------------------------------------ */

	/** Return list of demo pairs: [ [assistantCall, fakeResultJSON], … ] */
	public static function demoCalls( string $dialect, ToolRegistry $registry ): array {
		$out = [];

		foreach ( $registry->getTools() as $tool ) {
			$name = $tool->getName();
			[ $args, $fakeResult ] = self::sample( $name );

			// some tools (very large output) → truncate fake result
			$fakeJson = json_encode( $fakeResult, JSON_UNESCAPED_UNICODE );

			// wrap the *call* per dialect
			$assistant = match ( $dialect ) {
				self::QWEN =>
					"<tool_call>" .
					json_encode( [ 'name' => $name, 'arguments' => $args ], JSON_UNESCAPED_UNICODE ) .
					"</tool_call>",
				self::MISTRAL =>
					"[TOOL_CALLS]{$name}[CALL_ID]demo_" . uniqid() .
					"[ARGS]" . json_encode( $args, JSON_UNESCAPED_UNICODE ),
				self::LLAMA,
				self::LEGACY =>
				json_encode( [ 'name' => $name, 'parameters' => $args ], JSON_UNESCAPED_UNICODE ),
				default => '',      // OPENAI: not needed
			};

			if ( $assistant !== '' ) {
				$out[] = [ $assistant, $fakeJson ];
			}
		}

		return $out;
	}

	/* helper – minimal yet valid args + stub result for each tool */
	private static function sample( string $tool ): array {
		return match ( $tool ) {
			'list_files' =>
			[
				[ 'directory' => '.' ],
				[ 'success' => true, 'data' => [ 'files' => [ 'plugin.php' ], 'directories' => [ 'includes' ] ], 'truncated' => false, 'error' => null, 'debug' => [] ]
			],
			'read_file' =>
			[
				[ 'path' => 'README.md', 'start_line' => 1, 'end_line' => 10 ],
				[ 'success' => true, 'data' => [ 'content' => "Sample\nLines\n…", 'path' => 'README.md' ], 'truncated' => false, 'error' => null, 'debug' => [] ]
			],
			'search_strings' =>
			[
				[ 'needles' => [ 'todo' ], 'directory' => '.', 'file_types' => [ 'php' ], 'max_results' => 3 ],
				[ 'success' => true, 'data' => [ 'results' => [] ], 'truncated' => false, 'error' => null, 'debug' => [] ]
			],
			'find_hooks' =>
			[
				[ 'type' => 'both', 'directory' => '.', 'max_results' => 3 ],
				[ 'success' => true, 'data' => [ 'results' => [] ], 'truncated' => false, 'error' => null, 'debug' => [] ]
			],
			default =>
			[ [], [] ],
		};
	}

	/* ------------------------------------------------------------------ */
	/* 3.  Parse tool‑calls (identical to previous version)               */
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
	}

	/* ------------------------------------------------------------------ */
	/* 4.  Build tool‑result messages (unchanged)                          */
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
