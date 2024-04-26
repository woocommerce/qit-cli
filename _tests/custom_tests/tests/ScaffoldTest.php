<?php

class ScaffoldTest extends \PHPUnit\Framework\TestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	public function test_scaffold_e2e() {
		$scaffolded_dir = sys_get_temp_dir() . '/qit_scaffolded_e2e-' . uniqid();
		$output         = qit( [ 'scaffold:e2e', $scaffolded_dir ] );
		$this->assertDirectoryExists( $scaffolded_dir );

		// Snapshot the directory files and contents.
		$files     = [];
		$directory = new RecursiveDirectoryIterator( $scaffolded_dir );
		/** @var SplFileInfo $file $file */
		foreach ( new RecursiveIteratorIterator( $directory ) as $file ) {
			if ( $file->getBasename() === '.' || $file->getBasename() === '..' ) {
				continue;
			}

			if ( $file->isFile() ) {
				$files[ $file->getPathname() ] = file_get_contents( $file->getPathname() );
			} else {
				$files[ $file->getPathname() ] = '';
			}
		}
		$this->assertMatchesSnapshot( $files );
	}
}