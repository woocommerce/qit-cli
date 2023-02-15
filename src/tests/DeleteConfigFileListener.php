<?php

class DeleteConfigFileListener implements \PHPUnit\Runner\AfterLastTestHook {
	public function executeAfterLastTest(): void {
		try {
			\QIT_CLI\App::make( \QIT_CLI\Environment::class )->remove_environment( 'tests' );
		} catch ( Exception $e ) {
			echo "\nFailed to remove environment: " . $e->getMessage() . "\n";
		}
	}
}
