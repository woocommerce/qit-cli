<?php

class QITCustomTestsTest extends \PHPUnit\Framework\TestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	public function test_run_e2e_exists() {
		qit( [ 'run:e2e', '--help' ] );
		// If we got here, it means the command ran successfully.
		$this->assertTrue( true );
	}

	public function test_scaffold_e2e() {
		$scaffolded_dir = sys_get_temp_dir() . '/qit_scaffolded_e2e-' . uniqid();
		$output = qit( [ 'scaffold:e2e', $scaffolded_dir ] );
		$this->assertDirectoryExists( $scaffolded_dir );

		// Snapshot the directory files and contents.
		$files     = [];
		$directory = new RecursiveDirectoryIterator( $scaffolded_dir );
		foreach ( new RecursiveIteratorIterator( $directory ) as $file ) {
			$files[ $file->getPathname() ] = file_get_contents( $file->getPathname() );
		}
		$this->assertMatchesSnapshot( $files );
	}
}