<?php

class DeleteConfigFileListener implements \PHPUnit\Runner\AfterLastTestHook {
	public function executeAfterLastTest(): void {
		if ( file_exists( __DIR__ . '/.test_config' ) ) {
			$unlinked = unlink( __DIR__ . '/.test_config' );

			if ( ! $unlinked ) {
				echo "Could not delete test config file.\n";
				die( 1 );
			}
		}
	}
}
