<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Pushes task events to the Manager
 */
class TaskEventPusher {
    private string $node_id;
    private string $node_token;
    private string $manager_url;
    private int $max_retries = 5;

    /**
     * Constructor
     * 
     * @param string $node_id Node ID
     * @param string $node_token Node token
     * @param string $manager_url Manager URL
     */
    public function __construct(string $node_id, string $node_token, string $manager_url) {
        $this->node_id = $node_id;
        $this->node_token = $node_token;
        $this->manager_url = rtrim($manager_url, '/');
    }

    /**
     * Push an event to the Manager
     * 
     * @param string $task_id Task ID
     * @param string $state Task state (queued, in_progress, succeeded, failed, heartbeat)
     * @param int|null $progress Progress percentage (0-100)
     * @param array|null $payload Additional data
     * @return bool Success
     */
    public function pushEvent(string $task_id, string $state, ?int $progress = null, ?array $payload = null): bool {
        $endpoint = "{$this->manager_url}/wp-json/cd/v1/ai-nodes/{$this->node_id}/tasks/{$task_id}/event";
        $idempotency_key = $this->generateIdempotencyKey();

        $data = [
            'state' => $state,
            'timestamp' => gmdate('c'), // RFC 3339 format
        ];

        if ($progress !== null) {
            $data['progress'] = $progress;
        }

        if ($payload !== null) {
            $data['payload'] = $payload;
        }

        // Log outbound request to the manager
        log_info('Outbound request to manager', [
            'endpoint' => $endpoint,
            'method' => 'POST',
            'task_id' => $task_id,
            'state' => $state,
            'idempotency_key' => $idempotency_key
        ]);

        // Add detailed logging of payload for succeeded events
        if ($state === 'succeeded' && $payload !== null) {
            log_info('Task succeeded event payload', [
                'task_id' => $task_id,
                'has_result' => isset($payload['result']),
                'result_type' => isset($payload['result']) ? gettype($payload['result']) : 'none',
                'result_keys' => isset($payload['result']) && is_array($payload['result']) ? 
                    array_keys($payload['result']) : []
            ]);
        }

        return $this->sendWithRetry($endpoint, $data, $idempotency_key);
    }

    /**
     * Send a request with exponential backoff retry
     * 
     * @param string $url Endpoint URL
     * @param array $data Request data
     * @param string $idempotency_key Idempotency key
     * @return bool Success
     */
    private function sendWithRetry(string $url, array $data, string $idempotency_key): bool {
        $attempt = 0;

        while ($attempt < $this->max_retries) {
            try {
                $ch = curl_init($url);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->node_token,
                    'Idempotency-Key: ' . $idempotency_key,
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);

                $response = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);

                curl_close($ch);

                // Log the response from the manager
                log_info('Response from manager', [
                    'url' => $url,
                    'status_code' => $status_code,
                    'response_size' => strlen($response ?? ''),
                    'attempt' => $attempt + 1,
                    'error' => $error ?: null
                ]);

                if ($status_code >= 200 && $status_code < 300) {
                    return true;
                }

                // If it's a duplicate (already processed), that's fine
                if ($status_code === 200 && $response && strpos($response, 'duplicate') !== false) {
                    return true;
                }

                // If it's a client error (except 429), don't retry
                if ($status_code >= 400 && $status_code < 500 && $status_code !== 429) {
                    log_info("Task event push failed with client error", [
                        'url' => $url,
                        'status_code' => $status_code,
                        'response' => $response ?: null,
                        'error' => $error ?: null
                    ]);
                    error_log("Task event push failed with client error: HTTP $status_code - " . ($response ?: $error));
                    return false;
                }
            } catch (\Throwable $e) {
                log_info("Task event push failed with exception", [
                    'url' => $url,
                    'exception' => $e->getMessage(),
                    'attempt' => $attempt + 1
                ]);
                error_log("Task event push failed with exception: " . $e->getMessage());
            }

            // Exponential backoff with jitter
            $backoff = pow(2, $attempt) + rand(0, 1000) / 1000;
            usleep($backoff * 1000000); // Convert to microseconds

            $attempt++;
        }

        log_info("Task event push failed after all retries", [
            'url' => $url,
            'max_retries' => $this->max_retries,
            'data_size' => strlen(json_encode($data))
        ]);
        error_log("Task event push failed after {$this->max_retries} attempts");
        return false;
    }

    /**
     * Generate a UUID v4 for idempotency key
     * 
     * @return string UUID v4
     */
    private function generateIdempotencyKey(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
