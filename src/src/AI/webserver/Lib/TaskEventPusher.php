<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Pushes task events to the Manager
 */
class TaskEventPusher {
    private string $node_id;
    private string $node_token;
    private string $manager_url;

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

        $request = OutboundRequest::taskEvent($endpoint, $data, 'task-event-push-request');
        $result = $request->send();

        return $result['success'];
    }

}
