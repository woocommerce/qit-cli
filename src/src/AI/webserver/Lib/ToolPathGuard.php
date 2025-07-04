<?php
namespace QIT_AI_Webserver\Lib;

/**
 * Guard that converts a user‑supplied path to a *relative* path under $workDir
 * and rejects anything that violates Path‑Contract v3 (root‑relative).
 */
class ToolPathGuard {
    private string $workDir;                // canonical absolute path (no trailing "/")

    public function __construct(string $workDir) {
        $real = realpath($workDir);
        if ($real === false || !is_dir($real)) {
            throw new \RuntimeException("Invalid working directory: {$workDir}");
        }
        $this->workDir = rtrim(str_replace('\\', '/', $real), '/');
    }

    /**
     * @param string $path  The raw path coming from the LLM/tool‑call
     * @return string       Normalised *relative* path
     * @throws \RuntimeException on any contract violation
     */
    public function normalise(string $path): string
    {
        // ① Canonicalise separators, trim whitespace
        $path = str_replace('\\', '/', trim($path));

        // ② Fast contract checks (Path‑Contract v3)
        if ($path === '' || $path[0] === '/') {
            throw new \RuntimeException("Path must be root‑relative (no leading '/'): {$path}");
        }
        if (str_contains($path, '..')) {
            throw new \RuntimeException("Path must not contain '..' segments: {$path}");
        }
        if (!preg_match('#^[A-Za-z0-9_/\.\-]+$#', $path)) {
            throw new \RuntimeException("Path contains invalid characters: {$path}");
        }

        // ③ Build absolute candidate *inside* workspace
        $candidate = $this->workDir . '/' . $path;
        $real      = realpath($candidate) ?: $this->pseudoRealpath($candidate);

        // ④ Still inside workspace?
        if ($real !== $this->workDir && !str_starts_with($real, $this->workDir . '/')) {
            throw new \RuntimeException("Path escapes workspace: {$path}");
        }

        // ⑤ Return relative
        return ltrim(substr($real, strlen($this->workDir)), '/');
    }

    /** Fallback when file does not exist yet (string‑based realpath) */
    private function pseudoRealpath(string $path): string
    {
        $parts = [];
        foreach (explode('/', preg_replace('#/+#', '/', $path)) as $part) {
            if ($part === '' || $part === '.')  continue;
            if ($part === '..') { array_pop($parts); continue; }
            $parts[] = $part;
        }
        return '/' . implode('/', $parts);
    }
}