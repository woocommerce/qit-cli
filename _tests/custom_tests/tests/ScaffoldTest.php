<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use Spatie\Snapshots\MatchesSnapshots;

class ScaffoldTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;
	use MatchesSnapshots;

	public function test_scaffold_e2e() {
		$scaffolded_dir = $this->scaffold_test();
		$this->assertDirectoryExists( $scaffolded_dir );

		$normalize_path = function ( $path ) {
			// "/tmp/qit_scaffolded_e2e-662bcfc1a99ed/bootstrap/bootstrap.sh" => "/tmp/qit_scaffolded_e2e-NORMALIZED_ID/bootstrap/bootstrap.sh"
			return preg_replace( '/\/tmp\/qit_scaffolded_e2e-\w+/', '/tmp/qit_scaffolded_e2e-NORMALIZED_ID', $path );
		};

		// Snapshot the directory files and contents.
		$files     = [];
		$directory = new RecursiveDirectoryIterator( $scaffolded_dir );
		/** @var SplFileInfo $file $file */
		foreach ( new RecursiveIteratorIterator( $directory ) as $file ) {
			if ( $file->getBasename() === '.' || $file->getBasename() === '..' ) {
				continue;
			}

			if ( $file->isFile() ) {
				$files[ $normalize_path( $file->getPathname() ) ] = file_get_contents( $file->getPathname() );
			} else {
				$files[ $normalize_path( $file->getPathname() ) ] = '';
			}
		}
		$this->assertMatchesSnapshot( $files );
	}
}