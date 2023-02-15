<?php

namespace QIT_CLI_Tests;

use Exception;
use QIT_CLI\App;
use QIT_CLI\RequestBuilder;
use QIT_CLI\Upload;
use RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\NullOutput;
use ZipArchive;

class UploadTest extends QITTestCase {
	use MatchesSnapshots;

	protected $to_delete = [];

	public function setUp(): void {
		global $_test_post_bodies;
		$_test_post_bodies = [];
		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();

		foreach ( $this->to_delete as $file ) {
			@unlink( $file );
		}

		$this->to_delete = [];
	}

	protected function inject_request_builder_mock() {
		$mock = new class() extends RequestBuilder {
			public function request(): string {
				global $_test_post_bodies;
				$_test_post_bodies[] = $this->post_body;

				return json_encode( [ 'upload_id' => 123 ] );
			}
		};

		App::singleton( RequestBuilder::class, $mock );
	}

	protected function create_file( string $name, string $contents ) {
		file_put_contents( __DIR__ . '/' . $name, $contents );

		/*
		 * Zip archives stores the FileMTime as metadata,
		 * which interferes with snapshot testing.
		 * Set a predictable FileMTime to normalize the created
		 * zip files for snapshot testing.
		 */
		touch( __DIR__ . '/' . $name, 1669652766 );

		$this->to_delete[] = __DIR__ . '/' . $name;
	}

	/**
	 * @param Upload $upload The Upload instance to get the requests.
	 *
	 * @return array All requests this Upload instance sent out, normalized for snapshot testing.
	 */
	protected function get_requests_from_upload( Upload $upload ): array {
		$data = [];

		foreach ( $upload->_requests as $r ) {
			$normalized_request = $r->to_array();

			if ( isset( $normalized_request['post_body']['chunk'] ) ) {
				/*
				 * The ZIP algorithm includes bytes that stores the
				 * file modification and creation times. This makes
				 * it impossible to do snapshot tests of the chunks
				 * of a zip file, as each time you create them, they
				 * will be slightly different.
				 */
				$normalized_request['post_body']['chunk'] = 'Contents removed for snapshot tests.';
			}

			if ( isset( $normalized_request['post_body']['expected_size'] ) ) {
				// Due to the same reason above, the expected size varies a little, which breaks snapshot testing.
				$normalized_request['post_body']['expected_size'] = 123;
			}

			if ( isset( $normalized_request['post_body']['md5_sum'] ) ) {
				$normalized_request['post_body']['md5_sum'] = 'Normalized MD5';
			}

			$data[] = $normalized_request;
		}

		return $data;
	}

	public function test_upload_file_not_exists() {
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		$this->expectException( RuntimeException::class );
		$upload->upload_build( 123, 'foo', __DIR__ . '/foo.zip', new NullOutput() );
	}

	public function test_upload_file_not_zip() {
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		file_put_contents( __DIR__ . '/foo.zip', 'a' );
		$this->to_delete[] = __DIR__ . '/foo.zip';

		$this->expectException( RuntimeException::class );
		$upload->upload_build( 123, 'foo', __DIR__ . '/foo.zip', new NullOutput() );
	}

	public function test_upload_fails_if_no_parent_dir() {
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		file_put_contents( __DIR__ . '/foo.txt', 'a' );
		$zip = new ZipArchive();
		$zip->open( __DIR__ . '/foo.zip', ZipArchive::CREATE );
		$zip->addFile( __DIR__ . '/data/cat.jpg', 'cat.jpg' );
		$zip->close();

		$this->to_delete[] = __DIR__ . '/foo.zip';
		$this->to_delete[] = __DIR__ . '/foo.txt';

		$this->expectException( Exception::class );
		$upload->upload_build( 123, 'foo', __DIR__ . '/foo.zip', new NullOutput() );
	}

	public function test_upload_file_exists() {
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		file_put_contents( __DIR__ . '/foo.txt', 'a' );
		$zip = new ZipArchive();
		$zip->open( __DIR__ . '/foo.zip', ZipArchive::CREATE );
		$zip->addEmptyDir('foo');
		$zip->addFile( __DIR__ . '/data/cat.jpg', 'foo/cat.jpg' );
		$zip->close();

		$this->to_delete[] = __DIR__ . '/foo.zip';
		$this->to_delete[] = __DIR__ . '/foo.txt';

		$upload->upload_build( 123, 'foo', __DIR__ . '/foo.zip', new NullOutput() );

		// This test passes if we don't throw an exception.
		$this->assertTrue( true );
	}

	public function test_upload_reads_whole_file() {
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		$this->create_file( 'foo.txt', 'a' );

		$zip = new ZipArchive();
		$zip->open( __DIR__ . '/foo.zip', ZipArchive::CREATE );
		$zip->addEmptyDir('foo');
		$zip->addFile( __DIR__ . '/data/cat.jpg', 'foo/cat.jpg' );
		$zip->close();

		$this->to_delete[] = __DIR__ . '/foo.zip';

		$upload->upload_build( 123, 'foo', __DIR__ . '/foo.zip', new NullOutput() );

		$this->assertMatchesJsonSnapshot( $this->get_requests_from_upload( $upload ) );
	}

	public function test_upload_reads_whole_file_in_chunks() {
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		// 128kb chunks
		App::setVar( 'UPLOAD_CHUNK_KB', 128 );

		touch( __DIR__ . '/data/cat.jpg', 1669652766 );

		$zip = new ZipArchive();
		$zip->open( __DIR__ . '/foo.zip', ZipArchive::CREATE );
		$zip->addEmptyDir('foo');
		$zip->addFile( __DIR__ . '/data/cat.jpg', 'foo/cat.jpg' );
		$zip->close();

		$this->to_delete[] = __DIR__ . '/foo.zip';

		$upload->upload_build( 123, 'foo', __DIR__ . '/foo.zip', new NullOutput() );

		$this->assertMatchesJsonSnapshot( json_encode( $this->get_requests_from_upload( $upload ) ) );

		App::offsetUnset( 'UPLOAD_CHUNK_KB' );
	}

	public function test_uploaded_chunks_can_be_rehydrated_to_source() {
		global $_test_post_bodies;
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		// 128kb chunks
		App::setVar( 'UPLOAD_CHUNK_KB', 128 );

		touch( __DIR__ . '/data/cat.jpg', 1669652766 );

		$zip = new ZipArchive();
		$zip->open( __DIR__ . '/foo.zip', ZipArchive::CREATE );
		$zip->addEmptyDir('foo');
		$zip->addFile( __DIR__ . '/data/cat.jpg', 'foo/cat.jpg' );
		$zip->close();

		$this->to_delete[] = __DIR__ . '/foo.zip';

		$upload->upload_build( 123, 'foo', __DIR__ . '/foo.zip', new NullOutput() );

		// Hydrate the chunks and validate it's the same as the source.
		$file_string = '';

		foreach ( $_test_post_bodies as $post_body ) {
			$file_string .= base64_decode( $post_body['chunk'] );
		}

		$this->assertEquals( $file_string, file_get_contents( __DIR__ . '/foo.zip' ) );

		App::offsetUnset( 'UPLOAD_CHUNK_KB' );
	}
}
