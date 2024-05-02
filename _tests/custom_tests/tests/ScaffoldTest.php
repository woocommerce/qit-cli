<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class ScaffoldTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;
	use SnapshotHelpers;

	public function test_scaffold_e2e() {
		$scaffolded_dir = $this->scaffold_test();
		$this->assertDirectoryExists( $scaffolded_dir );

		$normalize_path = function ( $path ) {
			// "/tmp/qit_scaffolded_e2e-662bcfc1a99ed/bootstrap/bootstrap.sh" => "/tmp/qit_scaffolded_e2e-NORMALIZED_ID/bootstrap/bootstrap.sh"
			$path = preg_replace( '/\/tmp\/qit_scaffolded_e2e-\w+/', '/tmp/qit_scaffolded_e2e-NORMALIZED_ID', $path );
			$path = str_replace( sys_get_temp_dir(), '/tmp-normalized', $path );

			return $path;
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