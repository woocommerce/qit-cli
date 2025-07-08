<?php
/**
 * Worker router – bound only to 127.0.0.1.
 * Claims one pending task, executes it, persists result.
 *
 * Placeholders: {{NODE_TOKEN}}, {{LOG_FILE}}, {{PROVIDER}}, {{PROVIDER_CONFIG}}, {{AI_DIR}}
 */
require_once __DIR__ . '/router.shared.inc.php';

use QIT_AI_Webserver\Persistence\TaskRepository;
global $method, $uri;
use QIT_AI_Webserver\Endpoints\{
    BasicPromptEndpoint,
    PromptWithToolsEndpoint,
    ZipExtractionEndpoint,
    FileReadingEndpoint,
    VulnerabilityScanEndpoint
};

$repo      = new TaskRepository(QIT_DB_PATH);
$endpoints = [
    'prompt'          => new BasicPromptEndpoint(),
    'prompt-tools'    => new PromptWithToolsEndpoint(),
    'extract-zip'     => new ZipExtractionEndpoint(),
    'file-read'       => new FileReadingEndpoint(),
    'vuln-scan'       => new VulnerabilityScanEndpoint(),
];

// --------------------------------------------------------------
// POST /run-one   – atomic claim + execution
// --------------------------------------------------------------
if ($method === 'POST' && $uri === '/run-one') {
    // 1) Atomically claim a pending task
    $pdo = $repo->getConnection();
    $pdo->beginTransaction();
    $stmt = $pdo->query("SELECT task_id,type,data FROM tasks WHERE status='pending' ORDER BY id ASC LIMIT 1");
    $row  = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        $pdo->commit();
        echo json_encode(['did_run' => false]);
        return;
    }

    $taskId = $row['task_id'];
    $pdo->exec("UPDATE tasks SET status='running',updated_at=strftime('%s','now') WHERE task_id=" . $pdo->quote($taskId));
    $pdo->commit();

    // 2) Run it
    try {
        $payload = json_decode($row['data'], true) ?? [];
        $type    = $row['type'];

        if (!isset($endpoints[$type])) {
            throw new \RuntimeException("Unsupported task type: $type");
        }

        /** @var \QIT_AI_Webserver\Endpoints\AbstractEndpoint $handler */
        $handler = $endpoints[$type];
        // endpoint returns JSON body directly; we capture output buffer
        ob_start();
        $handler->handle($payload);
        $resultJson = ob_get_clean();

        $repo->markFinished($taskId, $resultJson);
    } catch (\Throwable $e) {
        $repo->markFinished($taskId, ['error' => $e->getMessage()]);
    }

    echo json_encode(['did_run' => true, 'task_id' => $taskId]);
    return;
}

http_response_code(404);
echo json_encode(['error' => 'Route not found on Worker']);
