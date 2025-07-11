<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Sends results to Manager callback URLs
 */
class CallbackSender {
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

        $request = OutboundRequest::callback($callback_url, $data, 'task-callback-request-success');
        $result = $request->send();

        return $result['success'];
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

        $request = OutboundRequest::callback($callback_url, $data, 'task-callback-request-error');
        $result = $request->send();

        return $result['success'];
    }
}
