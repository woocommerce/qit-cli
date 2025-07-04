<?php
namespace QIT_AI_Webserver\Lib;

class PromptContext {
    /** Return the path‑contract & root listing as a ready‑to‑append string. */
    public static function forWorkspace(string $root): string {
        $ctxFile = $root.'/.ctx.json';
        if (!is_file($ctxFile)) {
            // Try parent directory if not found in current directory
            $parentCtxFile = dirname($root).'/.ctx.json';
            if (!is_file($parentCtxFile)) {
                return '';                   // extraction step not run?  silently ignore
            }
            $ctxFile = $parentCtxFile;
        }
        $ctx   = json_decode(file_get_contents($ctxFile), true) ?? [];
        $roots = $ctx['roots'] ?? [];
        $rootList = implode("\n", array_map(fn($r)=>"  • {$r}", $roots));

        return <<<TXT
──────────────── Path Contract v3 ───────────────
• All tool paths are **relative to "."** (workspace root).
• Never use leading "/", never use "..", always use forward slashes.
• Use <tool_call>{"name":"list_files","arguments":{"directory":"."}}</tool_call>
  to see the root listing at any time.

Workspace roots you can start from:
{$rootList}
───────────────────────────────────────────────

TXT;
    }
}
