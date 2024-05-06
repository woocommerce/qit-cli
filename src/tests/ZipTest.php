<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Woo\ZipValidator;
use QIT_CLI_Tests\ZipBuilderHelper as ZipBuilder;

class ZipTest extends QITTestCase {
	use InjectRequestBuilderMockTrait;

	protected $to_delete = [];

	public function setUp(): void {
		file_put_contents( __DIR__ . '/plugin-entrypoint.php', '<?php Plugin Name: Foo' );
		$this->to_delete[] = __DIR__ . '/plugin-entrypoint.php';

		file_put_contents( __DIR__ . '/Thumbs.db', '' );
		$this->to_delete[] = __DIR__ . '/Thumbs.db';

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

		$zip      = App::make( ZipValidator::class );
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

	public function test_zip_with_invalid_file() {
		$this->inject_request_builder_mock();

		$file_name = 'zip-with-invalid-file.zip';
		$slug      = 'zip-with-invalid-file';

		$zip      = App::make( ZipValidator::class );
		$zip_file = ( new ZipBuilder( $file_name ) )
			->with_file( __DIR__ . '/plugin-entrypoint.php', $slug . '/plugin-entrypoint.php' )
			->with_file( __DIR__ . '/Thumbs.db', $slug . '/Thumbs.db' )
			->build();

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Invalid (zip-with-invalid-file/Thumbs.db) file/folder inside zip file' );

		$zip->validate_zip( $zip_file );
	}

	function test_zip_with_invalid_file_inside_folder() {
		$this->inject_request_builder_mock();

		$file_name = 'zip-with-invalid-file-inside-folder.zip';
		$slug      = 'zip-with-invalid-file-inside-folder';

		$zip      = App::make( ZipValidator::class );
		$zip_file = ( new ZipBuilder( $file_name ) )
			->with_file( __DIR__ . '/plugin-entrypoint.php', $slug . '/plugin-entrypoint.php' )
			->with_file( __DIR__ . '/Thumbs.db', $slug . '/foo/Thumbs.db' )
			->build();

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Invalid (zip-with-invalid-file-inside-folder/foo/Thumbs.db) file/folder inside zip file' );

		$zip->validate_zip( $zip_file );
	}

	function test_zip_with_invalid_file_inside_vendor_folder() {
		$this->inject_request_builder_mock();

		$file_name = 'zip-with-invalid-file-inside-vendor-folder.zip';
		$slug      = 'zip-with-invalid-file-inside-vendor-folder';

		$zip      = App::make( ZipValidator::class );
		$zip_file = ( new ZipBuilder( $file_name ) )
			->with_file( __DIR__ . '/plugin-entrypoint.php', $slug . '/plugin-entrypoint.php' )
			->with_file( __DIR__ . '/Thumbs.db', $slug . '/vendor/Thumbs.db' )
			->build();

		try{
			$zip->validate_zip( $zip_file );
		} catch (\Exception $e) {
			$this->fail('The zip file should be valid.');
		}

		$this->assertTrue( true );
	}
}
