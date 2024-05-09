<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Upload;
use QIT_CLI_Tests\ZipBuilderHelper as ZipBuilder;
use RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\NullOutput;
use ZipArchive;

class UploadTest extends QITTestCase {
	use MatchesSnapshots;
	use InjectRequestBuilderMockTrait;

	protected $to_delete = [];

	public function setUp(): void {
		global $_test_post_bodies;
		$_test_post_bodies = [];

		// Pre-create the entrypoint file as we use it on various tests.
		file_put_contents(__DIR__ . '/plugin-entrypoint.php', '<?php Plugin Name: Foo');
		$this->to_delete[] = __DIR__ . '/plugin-entrypoint.php';

		parent::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();

		foreach ( $this->to_delete as $file ) {
			@unlink( $file );
		}

		$this->to_delete = [];
	}

	/**
	 * @param Upload $upload The Upload instance to get the requests.
	 *
	 * @return array An array with the requests and the size of the chunks sent.
	 */
	protected function get_requests_from_upload( Upload $upload ): array {
		$content_size_sent = 0;

		$data = [];

		foreach ( $upload->_requests as $r ) {
			$normalized_request = $r->to_array();

			if ( isset( $normalized_request['post_body']['chunk'] ) ) {
				$content_size_sent += strlen( $normalized_request['post_body']['chunk'] );

				// It's impossible to do snapshot testing on zips, as they change every time they are created.
				$normalized_request['post_body']['chunk'] = 'REMOVED_FOR_SNAPSHOT_TEST';
			}

			if ( isset( $normalized_request['post_body']['md5_sum'] ) ) {
				// It's impossible to do snapshot testing on zips, as they change every time they are created.
				$normalized_request['post_body']['md5_sum'] = 'REMOVED_FOR_SNAPSHOT_TEST';
			}

			$data[] = $normalized_request;
		}

		return [ $content_size_sent, $data ];
	}

	protected function list_zip_contents( string $zip_file_path, bool $acceptable ) {
		$zip_archive = new ZipArchive();

		if ( ! $zip_archive->open( $zip_file_path ) ) {
			throw new RuntimeException( 'Could not open ZIP file.' );
		}

		$index = [ 'contents' => [] ];

		for ( $i = 0; $i < $zip_archive->numFiles; $i ++ ) {
			$index['contents'][] = $zip_archive->getNameIndex( $i );
		}

		$zip_archive->close();

		return $index;
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

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'This is not a valid ZIP file.' );
		$upload->upload_build( 123, 'foo', __DIR__ . '/foo.zip', new NullOutput() );
	}

	/**
	 * @param string $zip_file Path to zip file.
	 * @param int $content_size_sent Size of contents uploaded.
	 *
	 * @return void
	 */
	protected function assert_sent_chunks_matches_original_file( string $zip_file, int $content_size_sent ) {
		$original_zip_file_size = strlen( base64_encode( file_get_contents( $zip_file ) ) );

		// Allow uploaded data to vary up to 4kb compared to local zip, to accommodate dynamic ZipArchive metadata.
		$variance = 4096;

		$this->assertTrue( $content_size_sent >= $original_zip_file_size - $variance && $content_size_sent <= $original_zip_file_size + $variance );
	}

	public function test_upload_reads_whole_file() {
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		$zip_file = 'foo.zip';
		$sut_slug = 'foo';

		$zip = ( new ZipBuilder( $zip_file ) )
			->with_file( __DIR__ . '/plugin-entrypoint.php', "$sut_slug/plugin-entrypoint.php" )
			->with_file( __DIR__ . '/data/cat.jpg', "$sut_slug/cat.jpg" )
			->build();

		$this->to_delete[] = __DIR__ . "/$zip_file";

		$this->assertMatchesSnapshot( $this->list_zip_contents( $zip, true ) );

		$upload->upload_build( 123, $sut_slug, __DIR__ . "/$zip_file", new NullOutput() );

		[ $content_size_sent, $data ] =  $this->get_requests_from_upload( $upload );

		$this->assertMatchesJsonSnapshot( json_encode( $data ) );
		$this->assert_sent_chunks_matches_original_file( __DIR__ . "/$zip_file", $content_size_sent );
	}

	public function test_upload_reads_whole_file_in_chunks() {
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		// 128kb chunks
		App::setVar( 'UPLOAD_CHUNK_KB', 128 );

		$zip_file = 'foo.zip';
		$sut_slug = 'foo';

		$zip = ( new ZipBuilder( $zip_file ) )
			->with_file( __DIR__ . '/plugin-entrypoint.php', "$sut_slug/plugin-entrypoint.php" )
			->with_file( __DIR__ . '/data/cat.jpg', "$sut_slug/cat.jpg" )
			->build();

		$this->to_delete[] = __DIR__ . "/$zip_file";

		$this->assertMatchesSnapshot( $this->list_zip_contents( $zip, true ) );

		$upload->upload_build( 123, $sut_slug, __DIR__ . "/$zip_file", new NullOutput() );

		[ $content_size_sent, $data ] =  $this->get_requests_from_upload( $upload );

		$this->assertMatchesJsonSnapshot( json_encode( $data ) );
		$this->assert_sent_chunks_matches_original_file( __DIR__ . "/$zip_file", $content_size_sent );

		App::offsetUnset( 'UPLOAD_CHUNK_KB' );
	}

	public function test_uploaded_chunks_can_be_rehydrated_to_source() {
		global $_test_post_bodies;
		$this->inject_request_builder_mock();
		$upload = App::make( Upload::class );

		// 128kb chunks
		App::setVar( 'UPLOAD_CHUNK_KB', 128 );

		$zip_file = 'foo.zip';
		$sut_slug = 'foo';

		$zip = ( new ZipBuilder( $zip_file ) )
			->with_file( __DIR__ . '/plugin-entrypoint.php', "$sut_slug/plugin-entrypoint.php" )
			->with_file( __DIR__ . '/data/cat.jpg', "$sut_slug/cat.jpg" )
			->build();

		$this->to_delete[] = __DIR__ . "/$zip_file";

		$this->assertMatchesSnapshot( $this->list_zip_contents( $zip, true ) );

		$upload->upload_build( 123, $sut_slug, __DIR__ . "/$zip_file", new NullOutput() );

		// Hydrate the chunks and validate it's the same as the source.
		$file_string = '';

		foreach ( $_test_post_bodies as $post_body ) {
			$file_string .= base64_decode( $post_body['chunk'] );
		}

		$this->assertEquals( $file_string, file_get_contents( __DIR__ . '/foo.zip' ) );

		App::offsetUnset( 'UPLOAD_CHUNK_KB' );
	}
}
