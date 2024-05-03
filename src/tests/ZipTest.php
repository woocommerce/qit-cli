<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Zip;

use QIT_CLI_Tests\ZipBuilderHelper as ZipBuilder;

class ZipTest extends QITTestCase {
	use InjectRequestBuilderMockTrait;

	protected $to_delete = [];

	public function setUp(): void {
		file_put_contents( __DIR__ . '/plugin-entrypoint.php', '<?php Plugin Name: Foo' );
		$this->to_delete[] = __DIR__ . '/plugin-entrypoint.php';

		parent::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();

		foreach ( $this->to_delete as $file ) {
			@unlink( $file );
		}

		$this->to_delete = [];
	}

	public function test_valid_zip() {
		$this->inject_request_builder_mock();

		$file_name = 'valid-zip.zip';
		$slug      = 'valid-zip';

		$zip      = App::make( Zip::class );
		$zip_file = ( new ZipBuilder( $file_name ) )
			->with_file( __DIR__ . '/plugin-entrypoint.php', $slug . '/plugin-entrypoint.php' )
			->build();

		try {
			$zip->validate_zip( $zip_file );
		} catch ( \Exception $e ) {
			$this->fail( 'The zip file should be valid.' );
		}

		$this->assertTrue( true );
	}
}
