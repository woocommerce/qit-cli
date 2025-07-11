<?php

namespace QIT_AI_Webserver\Persistence;

use Exception;
use SleekDB\Store;

final class SleekTaskRepository {

	private Store $store;          // SleekDB store for “tasks”
	private string $storePath;      // …/sleekdb  (used by flock)
	private $lockHandle;            // resource|false

	public function __construct( string $basePath ) {
		$this->storePath = rtrim( $basePath, '/' ) . '/sleekdb';

		$this->store = new Store(
			'tasks',
			$this->storePath,
			[
				'timeout'     => false,          // silence old warning
				'auto_cache'  => false,
				'primary_key' => 'task_id',      // → we can use findById / updateById
			]
		);
	}

	/* ---- queue helpers -------------------------------------------------- */

	public function create( string $taskId, string $type, array $payload ): void {
		$this->store->insert( [
			'qit_task_id' => $taskId,
			'type'        => $type,
			'status'      => 'pending',
			'data'        => $payload,
			'created_at'  => time(),
			'updated_at'  => time(),
		] );
	}

 /** Atomically fetch + mark the oldest pending task */
	public function reserveNextPending(): ?array {
		$this->acquireLock();
		try {
			// First, check for stuck tasks (running for more than 30 minutes)
			$this->resetStuckTasks();

			$task = $this->store
				->createQueryBuilder()
				->where( [ 'status', '=', 'pending' ] )
				->orderBy( [ 'created_at' => 'asc' ] )
				->limit( 1 )
				->getQuery()
				->first();

			if ( ! $task ) {
				return null;
			}

			$task['status']     = 'running';
			$task['updated_at'] = time();
			$this->store->update( $task );

			return $task;
		} finally {
			$this->releaseLock();
		}
	}

	public function markFinished( string $taskId, $result = null ): void {
		$this->store->updateById( $taskId, [
			'status'     => 'finished',
			'result'     => $result,
			'updated_at' => time(),
		] );
	}

	public function get( string $taskId ): ?array {
		return $this->store->findById( $taskId );
	}

	/**
	 * Reset tasks that have been in the "running" state for too long (30 minutes)
	 */
	private function resetStuckTasks(): void {
		$thirtyMinutesAgo = time() - 1800; // 30 minutes in seconds

		$stuckTasks = $this->store
			->createQueryBuilder()
			->where( [ 'status', '=', 'running' ] )
			->where( [ 'updated_at', '<', $thirtyMinutesAgo ] )
			->getQuery()
			->fetch();

		foreach ( $stuckTasks as $task ) {
			$task['status'] = 'pending';
			$task['updated_at'] = time();
			$this->store->update( $task );
		}
	}

	/* ---- flock‑based mutex ---------------------------------------------- */

	private function acquireLock(): void {
		$lockFile         = $this->storePath . '/.tasks.lock';
		$this->lockHandle = fopen( $lockFile, 'c' );
		if ( ! flock( $this->lockHandle, LOCK_EX ) ) {
			throw new Exception( 'Could not acquire task lock' );
		}
	}

	private function releaseLock(): void {
		if ( $this->lockHandle ) {
			flock( $this->lockHandle, LOCK_UN );
			fclose( $this->lockHandle );
			$this->lockHandle = null;
		}
	}
}
