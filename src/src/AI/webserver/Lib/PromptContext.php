<?php
namespace QIT_AI_Webserver\Lib;

class PromptContext {
	/** Return the path‑contract & root listing as a ready‑to‑append string. */
	public static function for_workspace( string $root ): string {
		$ctx_file = $root . '/.ctx.json';
		if ( ! is_file( $ctx_file ) ) {
			// Try parent directory if not found in current directory
			$parent_ctx_file = dirname( $root ) . '/.ctx.json';
			if ( ! is_file( $parent_ctx_file ) ) {
				return '';                   // extraction step not run?  silently ignore
			}
			$ctx_file = $parent_ctx_file;
		}
		$ctx       = json_decode( file_get_contents( $ctx_file ), true ) ?? [];
		$roots     = $ctx['roots'] ?? [];
		$root_list = implode( "\n", array_map( fn( $r )=>"  • {$r}", $roots ) );

		return <<<TXT
──────────────── Path Contract v3 ───────────────
• All tool paths are **relative to "."** (workspace root).
• Never use leading "/", never use "..", always use forward slashes.
• Use <tool_call>{"name":"list_files","arguments":{"directory":"."}}</tool_call>
  to see the root listing at any time.

──────────────── Current Roots ─────────────────
{$root_list}

TXT;
	}
}
