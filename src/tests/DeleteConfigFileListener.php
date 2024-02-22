<?php

class DeleteConfigFileListener implements \PHPUnit\Runner\AfterLastTestHook {
	public function executeAfterLastTest(): void {
		try {
			\QIT_CLI\App::make( \QIT_CLI\ManagerBackend::class )->remove_manager_backend( 'tests' );
		} catch ( Exception $e ) {
			echo "\nFailed to remove environment: " . $e->getMessage() . "\n";
		}
	}
}
