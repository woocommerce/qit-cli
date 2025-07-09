<?php
// src/AI/webserver/Persistence/SleekTaskRepository.php
namespace QIT_AI_Webserver\Persistence;

use Exception;
use SleekDB\SleekDB;

class SleekTaskRepository {

    private $store;          // SleekDB instance
    private $lockHandle;     // resource for flock

    public function __construct( string $basePath ) {
        $this->store = SleekDB::store("tasks", $basePath . '/sleekdb'); // e.g. QIT_NODE_DIR . 'db'
    }

    /* ---------- queue helpers ---------- */

    /** create new task in pending state */
    public function create( string $taskId, string $type, array $payload ): void {
        $this->store->insert([
            "task_id" => $taskId,
            "type"    => $type,
            "status"  => "pending",
            "data"    => $payload,
            "created_at"  => time(),
            "updated_at"  => time()
        ]);
    }

    /** atomically reserve the oldest pending task (returns doc or null) */
    public function reserveNextPending(): ?array {
        $this->acquireLock();
        try {
            // oldest pending
            $task = $this->store
                ->where("status", "=", "pending")
                ->orderBy("_id", "asc")
                ->limit(1)
                ->fetch()[0] ?? null;

            if (!$task) {
                return null;
            }

            // mark running
            $task["status"] = "running";
            $task["updated_at"] = time();
            $this->store->update($task);

            return $task;
        } finally {
            $this->releaseLock();
        }
    }

    public function markFinished( string $taskId, $result = null ): void {
        $this->updateById($taskId, [
            "status"     => "finished",
            "result"     => $result,
            "updated_at" => time()
        ]);
    }

    public function get( string $taskId ): ?array {
        return $this->store->where("task_id", "=", $taskId)->fetch()[0] ?? null;
    }

    /* ---------- internal helpers ---------- */

    private function updateById( string $taskId, array $patch ): void {
        $docs = $this->store->where("task_id", "=", $taskId)->fetch();
        if (!$docs) {
            throw new Exception("Unknown task_id $taskId");
        }
        $doc = array_merge($docs[0], $patch);
        $this->store->update($doc);
    }

    /* ---------- flock‑based mutex ---------- */

    private function acquireLock(): void {
        $lockFile = $this->store->getStorePath() . '/.tasks.lock';
        $this->lockHandle = fopen($lockFile, 'c');
        if (!flock($this->lockHandle, LOCK_EX)) {
            throw new Exception("Could not acquire task lock");
        }
    }

    private function releaseLock(): void {
        if ($this->lockHandle) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
            $this->lockHandle = null;
        }
    }
}
