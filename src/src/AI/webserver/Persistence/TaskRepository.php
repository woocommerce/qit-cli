<?php

namespace QIT_AI_Webserver\Persistence;

/**
 * Task Repository
 * 
 * Handles database operations for tasks in the AI webserver.
 * Initializes the database if it doesn't exist yet.
 */
class TaskRepository {
    /**
     * @var string The path to the SQLite database file
     */
    private string $dbPath;

    /**
     * @var \PDO|null The PDO connection instance
     */
    private ?\PDO $connection = null;

    /**
     * Constructor
     * 
     * @param string $dbPath The path to the SQLite database file
     */
    public function __construct(string $dbPath) {
        $this->dbPath = $dbPath;
        $this->initializeDatabase();
    }

    /**
     * Initialize the database if it doesn't exist yet
     */
    private function initializeDatabase(): void {
        $dbExists = file_exists($this->dbPath);

        // Create the directory if it doesn't exist
        $dbDir = dirname($this->dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0700, true);
        }

        // Connect to the database
        $this->connection = new \PDO('sqlite:' . $this->dbPath, null, null, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        // Important for cascading deletes etc.
        $this->connection->exec('PRAGMA foreign_keys = ON');
        // Handle short concurrent bursts gracefully
        $this->connection->exec('PRAGMA busy_timeout = 5000');

        // Create tables if they don't exist
        if (!$dbExists) {
            $this->createTables();
        }
    }

    /**
     * Create the necessary tables in the database
     */
    private function createTables(): void {
        // Create tasks table
        $this->connection->exec('
            CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                task_id TEXT NOT NULL UNIQUE,
                status TEXT NOT NULL,
                type TEXT NOT NULL,
                data TEXT,
                created_at INTEGER NOT NULL,
                updated_at INTEGER NOT NULL
            )
        ');

        // Create task_results table
        $this->connection->exec('
            CREATE TABLE IF NOT EXISTS task_results (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                task_id TEXT NOT NULL,
                result TEXT,
                created_at INTEGER NOT NULL,
                FOREIGN KEY (task_id) REFERENCES tasks(task_id) ON DELETE CASCADE
            )
        ');

        // Create indexes
        $this->connection->exec('CREATE INDEX IF NOT EXISTS idx_tasks_task_id ON tasks(task_id)');
        $this->connection->exec('CREATE INDEX IF NOT EXISTS idx_tasks_status ON tasks(status)');
        $this->connection->exec('CREATE INDEX IF NOT EXISTS idx_tasks_type ON tasks(type)');
        $this->connection->exec('CREATE INDEX IF NOT EXISTS idx_task_results_task_id ON task_results(task_id)');
    }

    /* ---------- Public API ---------- */

    /**
     * Create a new task
     * 
     * @param string $taskId The unique task ID
     * @param string $type The task type
     * @param array $payload The task payload data
     */
    public function create(string $taskId, string $type, array $payload): void
    {
        $now = time();
        $stmt = $this->connection->prepare(
            'INSERT INTO tasks (task_id, status, type, data, created_at, updated_at)
             VALUES (:task_id, :status, :type, :data, :created, :updated)'
        );
        $stmt->execute([
            ':task_id' => $taskId,
            ':status'  => 'pending',
            ':type'    => $type,
            ':data'    => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ':created' => $now,
            ':updated' => $now,
        ]);
    }

    /**
     * Mark a task as running
     * 
     * @param string $taskId The task ID
     */
    public function markRunning(string $taskId): void
    {
        $this->updateStatus($taskId, 'running');
    }

    /**
     * Mark a task as finished with optional result
     * 
     * @param string $taskId The task ID
     * @param array|string|null $result The task result
     */
    public function markFinished(string $taskId, $result = null): void
    {
        $this->connection->beginTransaction();
        try {
            $this->updateStatus($taskId, 'finished');

            $stmt = $this->connection->prepare(
                'INSERT INTO task_results (task_id, result, created_at)
                 VALUES (:task_id, :result, :created_at)'
            );
            $stmt->execute([
                ':task_id'   => $taskId,
                ':result'    => is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE),
                ':created_at'=> time(),
            ]);

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Get a task by ID
     * 
     * @param string $taskId The task ID
     * @return array|null The task data or null if not found
     */
    public function get(string $taskId): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM tasks WHERE task_id = :task_id LIMIT 1'
        );
        $stmt->execute([':task_id' => $taskId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /* ---------- Internals ---------- */

    /**
     * Update the status of a task
     * 
     * @param string $taskId The task ID
     * @param string $status The new status
     */
    private function updateStatus(string $taskId, string $status): void
    {
        $stmt = $this->connection->prepare(
            'UPDATE tasks SET status = :status, updated_at = :updated WHERE task_id = :task_id'
        );
        $stmt->execute([
            ':status'   => $status,
            ':updated'  => time(),
            ':task_id'  => $taskId,
        ]);
    }

    /**
     * Get the PDO connection instance
     * 
     * @return \PDO The PDO connection instance
     */
    public function getConnection(): \PDO {
        return $this->connection;
    }
}
