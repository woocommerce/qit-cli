<?php

class DeleteConfigFileListener implements \PHPUnit\Runner\AfterLastTestHook {
	public function executeAfterLastTest(): void {
		\QIT_CLI\App::make( \QIT_CLI\Environment::class )->unset_environment( 'tests' );
	}
}
