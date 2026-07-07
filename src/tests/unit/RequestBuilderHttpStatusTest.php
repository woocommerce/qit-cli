<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\RequestBuilder;

/**
 * Coverage for QIT-997: download_file() must reject HTTP error responses instead
 * of writing the error body (e.g. a 404 "Not found" page) to disk, where it would
 * later fail as a misleading "invalid zip".
 */
class RequestBuilderHttpStatusTest extends QITTestCase {
	/** @var string[] */
	private $tmp_files = [];

	public function tearDown(): void {
		foreach ( $this->tmp_files as $f ) {
			if ( file_exists( $f ) ) {
				unlink( $f );
			}
		}
		parent::tearDown();
	}

	private function error_message( int $http_code, string $url, string $file_path ): ?string {
		$ref = new \ReflectionMethod( RequestBuilder::class, 'download_http_error_message' );
		$ref->setAccessible( true );

		return $ref->invoke( null, $http_code, $url, $file_path );
	}

	private function tmp_file_with( string $contents ): string {
		$path = tempnam( sys_get_temp_dir(), 'qit-dl-' );
		file_put_contents( $path, $contents );
		$this->tmp_files[] = $path;

		return $path;
	}

	public function test_successful_status_returns_null(): void {
		$path = $this->tmp_file_with( 'PK...zip bytes...' );
		$this->assertNull( $this->error_message( 200, 'https://example.com/x.zip', $path ) );
	}

	public function test_404_returns_message_with_status_url_and_body_snippet(): void {
		$url     = 'https://downloads.wordpress.org/plugin/woocommerce.11.0.0-dev.zip?token=secret';
		$path    = $this->tmp_file_with( "Not found" );
		$message = $this->error_message( 404, $url, $path );

		$this->assertNotNull( $message );
		$this->assertStringContainsString( 'HTTP 404', $message );
		$this->assertStringContainsString( 'https://downloads.wordpress.org/plugin/woocommerce.11.0.0-dev.zip', $message );
		$this->assertStringNotContainsString( 'token=secret', $message );
		$this->assertStringContainsString( 'Not found', $message );
	}

	public function test_5xx_without_readable_body_omits_snippet(): void {
		$message = $this->error_message( 503, 'https://example.com/x.zip', '/no/such/file/here' );

		$this->assertNotNull( $message );
		$this->assertStringContainsString( 'HTTP 503', $message );
		$this->assertStringNotContainsString( 'response body starts with', $message );
	}

	public function test_download_file_deletes_http_error_body(): void {
		$url  = 'https://downloads.wordpress.org/plugin/woocommerce.11.0.0-dev.zip?token=secret';
		$path = $this->tmp_file_with( '' );
		unlink( $path );

		App::setVar( 'mock_' . $url, [
			'status' => 404,
			'body'   => 'Not found',
		] );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'HTTP 404' );

		try {
			RequestBuilder::download_file( $url, $path );
		} finally {
			$this->assertFileNotExists( $path );
		}
	}

	public function test_download_file_deletes_empty_success_response(): void {
		$url  = 'https://example.com/empty.zip?signature=secret';
		$path = $this->tmp_file_with( '' );
		unlink( $path );

		App::setVar( 'mock_' . $url, [
			'status' => 200,
			'body'   => '',
		] );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'empty response' );

		try {
			RequestBuilder::download_file( $url, $path );
		} finally {
			$this->assertFileNotExists( $path );
		}
	}

	public function test_download_file_accepts_successful_mock_response(): void {
		$url  = 'https://example.com/plugin.zip';
		$path = $this->tmp_file_with( '' );
		unlink( $path );
		$this->tmp_files[] = $path;

		App::setVar( 'mock_' . $url, [
			'status' => 200,
			'body'   => 'zip bytes',
		] );

		RequestBuilder::download_file( $url, $path );

		$this->assertSame( 'zip bytes', file_get_contents( $path ) );
	}
}
