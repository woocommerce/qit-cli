<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Sends results to Manager callback URLs
 */
class CallbackSender {
    private int $max_retries = 3;
    private int $timeout = 30;

    /**
     * Send successful result to callback URL
     */
    public function sendCallback(
        string $callback_url,
        string $action_id,
        array $response,
        ?int $processing_time = null,
        array $tool_calls = [],
        array $metadata = []
    ): bool {
        $data = [
            'action_id' => $action_id,
            'response' => json_encode($response),
            'processing_time' => $processing_time,
            'tool_calls' => $tool_calls,
            'metadata' => $metadata
        ];

        return $this->sendWithRetry($callback_url, $data);
    }

    /**
     * Send error to callback URL
     */
    public function sendErrorCallback(
        string $callback_url,
        string $action_id,
        string $error_message
    ): bool {
        $data = [
            'action_id' => $action_id,
            'response' => json_encode(['error' => $error_message]),
            'processing_time' => 0,
            'tool_calls' => [],
            'metadata' => ['error' => true]
        ];

        return $this->sendWithRetry($callback_url, $data);
    }

    /**
     * Send POST request with retry logic
     */
    private function sendWithRetry(string $url, array $data): bool {
        $attempt = 0;
        
        while ($attempt < $this->max_retries) {
            try {
                $ch = curl_init($url);
                
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/x-www-form-urlencoded',
                    'X-Node-Token: ' . (getenv('QIT_NODE_TOKEN') ?: '')
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
                
                $response = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                
                curl_close($ch);
                
                log_info('Callback sent to Manager', [
                    'url' => $url,
                    'status_code' => $status_code,
                    'attempt' => $attempt + 1,
                    'action_id' => $data['action_id']
                ]);
                
                if ($status_code >= 200 && $status_code < 300) {
                    return true;
                }
                
                // Don't retry client errors (except 429)
                if ($status_code >= 400 && $status_code < 500 && $status_code !== 429) {
                    log_info("Callback failed with client error", [
                        'status_code' => $status_code,
                        'response' => $response
                    ]);
                    return false;
                }
                
            } catch (\Throwable $e) {
                log_info("Callback failed with exception", [
                    'exception' => $e->getMessage(),
                    'attempt' => $attempt + 1
                ]);
            }
            
            // Exponential backoff
            $backoff = pow(2, $attempt);
            sleep($backoff);
            $attempt++;
        }
        
        log_info("Callback failed after all retries", [
            'url' => $url,
            'max_retries' => $this->max_retries
        ]);
        return false;
    }
}