<?php

namespace QIT_AI_Webserver\Lib;

/**
 * File Path Contract
 * 
 * All file paths in the system follow these rules:
 * 1. ALWAYS relative to the extracted SUT directory
 * 2. NEVER include the extract_path prefix
 * 3. Use forward slashes (/) even on Windows
 * 4. No leading slash
 * 
 * Example:
 * - Actual file: /tmp/qit-code-analysis/abc123/fortis-for-woocommerce/classes/FortisApi.php
 * - Extract path: /tmp/qit-code-analysis/abc123/fortis-for-woocommerce
 * - Relative path: classes/FortisApi.php
 */

/**
 * File path resolver to ensure consistent path handling
 * Standalone copy for the node to avoid manager dependencies
 */
class FilePathResolver {
    private string $extractPath;
    
    public function __construct(string $extractPath) {
        $this->extractPath = rtrim($extractPath, '/\\');
    }
    
    /**
     * Convert a relative path to absolute
     */
    public function toAbsolute(string $relativePath): string {
        $relativePath = $this->normalize($relativePath);
        return $this->extractPath . '/' . $relativePath;
    }
    
    /**
     * Convert an absolute path to relative
     */
    public function toRelative(string $absolutePath): string {
        $absolutePath = $this->normalize($absolutePath);
        $extractPath = $this->normalize($this->extractPath);
        
        // Remove extract path prefix
        if (strpos($absolutePath, $extractPath) === 0) {
            $relative = substr($absolutePath, strlen($extractPath));
            return ltrim($relative, '/');
        }
        
        // Path is already relative
        return ltrim($absolutePath, '/');
    }
    
    /**
     * Normalize path separators and remove redundant parts
     */
    public function normalize(string $path): string {
        // Convert backslashes to forward slashes
        $path = str_replace('\\', '/', $path);
        
        // Remove duplicate slashes
        $path = preg_replace('#/+#', '/', $path);
        
        // Remove trailing slash
        return rtrim($path, '/');
    }
    
    /**
     * Check if a file exists (using relative path)
     */
    public function fileExists(string $relativePath): bool {
        return file_exists($this->toAbsolute($relativePath));
    }
    
    /**
     * Read file contents (using relative path)
     */
    public function readFile(string $relativePath): string {
        $absolutePath = $this->toAbsolute($relativePath);
        if (!file_exists($absolutePath)) {
            throw new \RuntimeException("File not found: $relativePath");
        }
        return file_get_contents($absolutePath);
    }
    
    /**
     * Get file info (using relative path)
     */
    public function getFileInfo(string $relativePath): array {
        $absolutePath = $this->toAbsolute($relativePath);
        if (!file_exists($absolutePath)) {
            throw new \RuntimeException("File not found: $relativePath");
        }
        
        $content = file_get_contents($absolutePath);
        return [
            'path' => $relativePath,
            'absolute_path' => $absolutePath,
            'size' => filesize($absolutePath),
            'lines' => substr_count($content, "\n") + 1,
            'extension' => pathinfo($relativePath, PATHINFO_EXTENSION)
        ];
    }
}