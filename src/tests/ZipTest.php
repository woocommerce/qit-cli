<?php

namespace QIT_CLI_Tests;

class ZipTest extends QITTestCase {
	public function test_validate_zip() {
		$zip = new \QIT_CLI\Zip();
		$zip->validate_zip( __DIR__ . '/fixtures/valid.zip' );

		$this->expectOutputString( 'Zip file is valid.' );
	}
}
