<?php
/**
 * Standardized Node Response Handler
 * 
 * Provides consistent response formatting for the core Node APIs:
 * - Basic Prompt (simple AI inference)
 * - Tool Prompt (AI with tool execution)
 * - ZIP Extraction (file extraction utility)
 * 
 * Complex orchestration is handled by the Manager service.
 */

namespace QIT_AI_Webserver;

class NodeResponse {

    private static ?float $requestStartTime = null;
    private static array $performanceMarkers = [];

    /**
     * Initialize response tracking (call at request start)
     */
    public static function init(): void {
        self::$requestStartTime = microtime(true);
        self::$performanceMarkers = [];
    }

    /**
     * Mark a performance checkpoint
     * 
     * @param string $name Marker name
     * @param array $data Optional data to associate with marker
     */
    public static function mark(string $name, array $data = []): void {
        self::$performanceMarkers[] = [
            'name' => $name,
            'time' => microtime(true),
            'data' => $data
        ];
    }

    /**
     * Get performance statistics
     * 
     * @return array Performance data
     */
    private static function getPerformanceStats(): array {
        $endTime = microtime(true);
        $totalTime = self::$requestStartTime ? ($endTime - self::$requestStartTime) * 1000 : null;

        $stats = [
            'total_duration_ms' => $totalTime ? round($totalTime, 2) : null,
            'timestamp' => time(),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
        ];

        // Add markers if any
        if (!empty(self::$performanceMarkers)) {
            $markers = [];
            $lastTime = self::$requestStartTime;

            foreach (self::$performanceMarkers as $marker) {
                $duration = ($marker['time'] - $lastTime) * 1000;
                $markers[$marker['name']] = [
                    'duration_ms' => round($duration, 2),
                    'cumulative_ms' => round(($marker['time'] - self::$requestStartTime) * 1000, 2)
                ];

                if (!empty($marker['data'])) {
                    $markers[$marker['name']]['data'] = $marker['data'];
                }

                $lastTime = $marker['time'];
            }

            $stats['markers'] = $markers;
        }

        return $stats;
    }

    /**
     * Extract token statistics from Ollama response
     * 
     * @param array $ollamaResponse Raw Ollama response
     * @return array Token statistics
     */
    private static function extractTokenStats(array $ollamaResponse): array {
        $stats = [];

        if (isset($ollamaResponse['eval_count'])) {
            $stats['tokens_generated'] = $ollamaResponse['eval_count'];
        }

        if (isset($ollamaResponse['eval_duration']) && $ollamaResponse['eval_duration'] > 0 && isset($ollamaResponse['eval_count'])) {
            $evalSeconds = $ollamaResponse['eval_duration'] / 1000000000;
            $stats['tokens_per_second'] = round($ollamaResponse['eval_count'] / $evalSeconds, 2);
            $stats['generation_duration_ms'] = round($ollamaResponse['eval_duration'] / 1000000, 2);
        }

        if (isset($ollamaResponse['prompt_eval_count'])) {
            $stats['prompt_tokens'] = $ollamaResponse['prompt_eval_count'];
        }

        if (isset($ollamaResponse['prompt_eval_duration'])) {
            $stats['prompt_eval_duration_ms'] = round($ollamaResponse['prompt_eval_duration'] / 1000000, 2);
        }

        if (isset($ollamaResponse['total_duration'])) {
            $stats['total_duration_ms'] = round($ollamaResponse['total_duration'] / 1000000, 2);
        }

        return $stats;
    }

    /**
     * Basic prompt response (single AI inference)
     * Used by BasicPromptHandler
     * 
     * @param string $response AI response text
     * @param string $model Model used
     * @param array $ollamaResponse Raw Ollama response for token stats
     * @param array $additional Additional data (job_id, etc.)
     */
    public static function prompt(string $response, string $model, array $ollamaResponse = [], array $additional = []): void {
        header('Content-Type: application/json');

        $data = array_merge([
            'response' => $response,
            'model' => $model
        ], $additional);

        // Add token statistics if available
        $tokenStats = self::extractTokenStats($ollamaResponse);
        if (!empty($tokenStats)) {
            $data['token_stats'] = $tokenStats;
        }

        $response = [
            'status' => 'success',
            'type' => 'prompt',
            'data' => $data,
            'meta' => self::getPerformanceStats()
        ];

        echo json_encode($response);
        exit;
    }

    /**
     * Tool execution response (AI with tools)
     * Used by ToolPromptHandler
     * 
     * @param string $response Final AI response
     * @param array $toolCalls Tool execution records
     * @param string $model Model used
     * @param array $additional Additional data
     */
    public static function toolPrompt(string $response, array $toolCalls, string $model, array $additional = []): void {
        header('Content-Type: application/json');

        $data = array_merge([
            'response' => $response,
            'model' => $model,
            'tool_calls' => $toolCalls,
            'tool_count' => count($toolCalls)
        ], $additional);

        $response = [
            'status' => 'success',
            'type' => 'tool_prompt',
            'data' => $data,
            'meta' => self::getPerformanceStats()
        ];

        echo json_encode($response);
        exit;
    }

    /**
     * ZIP extraction response
     * Used by ZipExtractionHandler
     * 
     * @param string $extractPath Extraction directory path
     * @param array $stats Extraction statistics
     * @param string $sessionId Session identifier
     * @param array $additional Additional data
     */
    public static function extraction(string $extractPath, array $stats, string $sessionId, array $additional = []): void {
        header('Content-Type: application/json');

        $data = array_merge([
            'extract_path' => $extractPath,
            'session_id' => $sessionId,
            'stats' => $stats
        ], $additional);

        $response = [
            'status' => 'success',
            'type' => 'extraction',
            'data' => $data,
            'meta' => self::getPerformanceStats()
        ];

        echo json_encode($response);
        exit;
    }

    /**
     * Generic success response (for future extensions)
     * 
     * @param mixed $data Response data
     * @param string $type Response type identifier
     * @param array $meta Additional metadata
     */
    public static function success($data, string $type = 'generic', array $meta = []): void {
        header('Content-Type: application/json');

        $response = [
            'status' => 'success',
            'type' => $type,
            'data' => $data,
            'meta' => array_merge(self::getPerformanceStats(), $meta)
        ];

        echo json_encode($response);
        exit;
    }

    /**
     * Error response
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param array $details Error details
     */
    public static function error(string $message, int $code = 500, array $details = []): void {
        http_response_code($code);
        header('Content-Type: application/json');

        $errorData = [
            'message' => $message,
            'code' => $code
        ];

        if (!empty($details)) {
            $errorData['details'] = $details;
        }

        $response = [
            'status' => 'error',
            'type' => 'error',
            'error' => $errorData,
            'meta' => self::getPerformanceStats()
        ];

        echo json_encode($response);
        exit;
    }

    /**
     * Raw response for Manager-orchestrated responses
     * The Manager can use this to return pre-structured responses while still
     * getting performance metrics added.
     * 
     * @param array $managerResponse Response structure from Manager
     */
    public static function fromManager(array $managerResponse): void {
        header('Content-Type: application/json');

        // Manager provides the response structure, we just add performance meta
        $response = array_merge($managerResponse, [
            'meta' => array_merge(
                self::getPerformanceStats(),
                $managerResponse['meta'] ?? []
            )
        ]);

        // Ensure we have required fields
        if (!isset($response['status'])) {
            $response['status'] = 'success';
        }
        if (!isset($response['type'])) {
            $response['type'] = 'manager_orchestrated';
        }

        echo json_encode($response);
        exit;
    }
}
