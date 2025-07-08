<?php
/**
 * Listener router – exposed (and tunnelled) HTTP interface.
 * Accepts jobs from the Manager, stores them in SQLite, returns 202.
 *
 * Placeholders replaced by WebServer::replacePlaceholders():
 *  {{NODE_TOKEN}}, {{LOG_FILE}}, {{AI_DIR}}
 */
require_once __DIR__ . '/router.shared.inc.php';   // shared bootstrap

use QIT_AI_Webserver\Persistence\TaskRepository;

global $method, $uri;

$repo = new TaskRepository(QIT_DB_PATH);

switch ("$method $uri") {

    // ------------------------------------------------------------------
    // POST /process  — enqueue a task
    // ------------------------------------------------------------------
    case 'POST /process':
        $task = $input ?? [];
        $taskId = $task['job_id'] ?? bin2hex(random_bytes(8));
        $type   = $task['type']   ?? 'prompt';

        $repo->create($taskId, $type, $task);

        http_response_code(202);
        echo json_encode(['accepted' => true, 'task_id' => $taskId]);
        break;

    // ------------------------------------------------------------------
    // GET /status/{task_id}
    // ------------------------------------------------------------------
    default:
        if (preg_match('#^/status/([a-f0-9]+)$#', $uri, $m) && $method === 'GET') {
            $status = $repo->get($m[1]);
            if (!$status) {
                http_response_code(404);
                echo json_encode(['error' => 'Unknown task id']);
            } else {
                echo json_encode($status);
            }
            break;
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found on Listener']);
}
