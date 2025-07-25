<?php

namespace QIT_CLI_Tests\PreCommand\Pipeline\Stages;

use QIT_CLI\App;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\PreCommand\Pipeline\PipelineContext;
use QIT_CLI\PreCommand\Pipeline\Stages\ResolveTestPackageStage;
use QIT_CLI\TestPackageDownloader;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use function QIT_CLI\get_manager_url;

class ResolveTestPackageStageTest extends TestCase {
	private ResolveTestPackageStage $stage;
	private TestPackageDownloader $downloader;
	private PipelineContext $context;
	private array $to_reset = [];
	
	protected function setUp(): void {
		parent::setUp();
		
		$this->downloader = App::make( TestPackageDownloader::class );
		$this->stage = new ResolveTestPackageStage( $this->downloader );
		
		// Create a mock command
		$command = $this->createMock( QITCommand::class );
		$input = new ArrayInput( [] );
		$output = new BufferedOutput();
		
		$this->context = new PipelineContext( $command, $input, $output );
		
		// Clean up any existing temp directories
		$temp_dir = sys_get_temp_dir() . '/qit-packages';
		if ( is_dir( $temp_dir ) ) {
			$this->recursiveRmdir( $temp_dir );
		}
	}
	
	protected function tearDown(): void {
		// Reset any mocked variables
		foreach ( $this->to_reset as $key ) {
			App::setVar( $key, null );
		}
		$this->to_reset = [];
		
		// Clean up temp directories
		$temp_dir = sys_get_temp_dir() . '/qit-packages';
		if ( is_dir( $temp_dir ) ) {
			$this->recursiveRmdir( $temp_dir );
		}
		
		parent::tearDown();
	}
	
	protected function mockTestPackageDownloadUrls( array $urls ): void {
		$key = sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' );
		App::setVar( $key, json_encode( [ 'urls' => $urls ] ) );
		$this->to_reset[] = $key;
	}
	
	protected function mockDownloadUrl( string $url, string $response ): void {
		$key = sprintf( 'mock_%s', $url );
		App::setVar( $key, $response );
		$this->to_reset[] = $key;
	}
	
	protected function recursiveRmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		
		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->recursiveRmdir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
	
	protected function createMockZip( string $content ): string {
		$zip = new \ZipArchive();
		$temp = tempnam( sys_get_temp_dir(), 'zip' );
		if ( $temp === false ) {
			$this->fail( "Failed to create temporary file for ZIP" );
		}
		
		if ( ! $zip->open( $temp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) ) {
			$this->fail( "Failed to create ZIP file at $temp" );
		}
		
		$zip->addFromString( 'test.txt', $content );
		$zip->close();
		
		$zipContent = file_get_contents( $temp );
		unlink( $temp );
		
		if ( $zipContent === false ) {
			$this->fail( "Failed to read ZIP content" );
		}
		
		return $zipContent;
	}
	
	public function test_process_with_no_package_identifier(): void {
		// No package argument or option provided
		$input = new ArrayInput( [] );
		$this->context->input = $input;
		
		$result = $this->stage->process( $this->context );
		
		// Should return context unchanged
		$this->assertSame( $this->context, $result );
		$this->assertNull( $result->get( 'static_test_package_path' ) );
		$this->assertNull( $result->get( 'static_test_package_id' ) );
	}
	
	public function test_process_with_package_argument(): void {
		$package_id = 'woocommerce/e2e:stable';
		$download_url = 'https://example.com/package.zip';
		$zip_content = $this->createMockZip( 'test content' );
		$checksum = hash( 'sha256', $zip_content );
		
		// Mock the API response
		$this->mockTestPackageDownloadUrls( [
			$package_id => [
				'url' => $download_url,
				'checksum' => $checksum,
				'version' => 'stable'
			]
		] );
		
		// Mock the download
		$this->mockDownloadUrl( $download_url, $zip_content );
		
		// Create input definition with package argument and verify option
		$definition = new InputDefinition( [
			new InputArgument( 'package', InputArgument::OPTIONAL ),
			new InputOption( 'verify', null, InputOption::VALUE_NEGATABLE, '', true ),
		] );
		
		// Create input with package argument
		$input = new ArrayInput( [ 'package' => $package_id ], $definition );
		$this->context->input = $input;
		
		$result = $this->stage->process( $this->context );
		
		// Verify results are stored in context
		$this->assertNotNull( $result->get( 'static_test_package_path' ) );
		$this->assertEquals( $package_id, $result->get( 'static_test_package_id' ) );
		
		// Verify package is added to test_packages array
		$test_packages = $result->get_test_packages();
		$this->assertArrayHasKey( $package_id, $test_packages );
		$this->assertEquals( 'static', $test_packages[ $package_id ]['type'] );
		$this->assertTrue( $test_packages[ $package_id ]['resolved'] );
		
		// Verify extracted directory exists
		$extracted_path = $result->get( 'static_test_package_path' );
		$this->assertDirectoryExists( $extracted_path );
		$this->assertFileExists( $extracted_path . '/test.txt' );
	}
	
	public function test_process_with_test_package_option_string(): void {
		$package_id = 'woocommerce/e2e:stable';
		$download_url = 'https://example.com/package.zip';
		$zip_content = $this->createMockZip( 'test content' );
		$checksum = hash( 'sha256', $zip_content );
		
		// Mock the API response
		$this->mockTestPackageDownloadUrls( [
			$package_id => [
				'url' => $download_url,
				'checksum' => $checksum,
				'version' => 'stable'
			]
		] );
		
		// Mock the download
		$this->mockDownloadUrl( $download_url, $zip_content );
		
		// Create input definition with test-package option
		$definition = new InputDefinition( [
			new InputOption( 'test-package', null, InputOption::VALUE_OPTIONAL ),
			new InputOption( 'verify', null, InputOption::VALUE_NEGATABLE, '', true ),
		] );
		
		// Create input with test-package option as string
		$input = new ArrayInput( [ '--test-package' => $package_id ], $definition );
		$this->context->input = $input;
		
		$result = $this->stage->process( $this->context );
		
		// Verify results are stored in context
		$this->assertNotNull( $result->get( 'static_test_package_path' ) );
		$this->assertEquals( $package_id, $result->get( 'static_test_package_id' ) );
	}
	
	public function test_process_with_test_package_option_array(): void {
		$package_id = 'woocommerce/e2e:stable';
		$download_url = 'https://example.com/package.zip';
		$zip_content = $this->createMockZip( 'test content' );
		$checksum = hash( 'sha256', $zip_content );
		
		// Mock the API response
		$this->mockTestPackageDownloadUrls( [
			$package_id => [
				'url' => $download_url,
				'checksum' => $checksum,
				'version' => 'stable'
			]
		] );
		
		// Mock the download
		$this->mockDownloadUrl( $download_url, $zip_content );
		
		// Create input definition with test-package option
		$definition = new InputDefinition( [
			new InputOption( 'test-package', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL ),
			new InputOption( 'verify', null, InputOption::VALUE_NEGATABLE, '', true ),
		] );
		
		// Create input with test-package option as array
		$input = new ArrayInput( [ '--test-package' => [ $package_id, 'other/package:1.0.0' ] ], $definition );
		$this->context->input = $input;
		
		$result = $this->stage->process( $this->context );
		
		// Should process the first package in the array
		$this->assertNotNull( $result->get( 'static_test_package_path' ) );
		$this->assertEquals( $package_id, $result->get( 'static_test_package_id' ) );
	}
	
	public function test_process_with_invalid_package_identifier(): void {
		// Create input definition with package argument
		$definition = new InputDefinition( [
			new InputArgument( 'package', InputArgument::OPTIONAL ),
			new InputOption( 'verify', null, InputOption::VALUE_NEGATABLE, '', true ),
		] );
		
		// Create input with invalid package identifier
		$input = new ArrayInput( [ 'package' => 'invalid-format' ], $definition );
		$this->context->input = $input;
		
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Invalid package identifier format: invalid-format' );
		$this->expectExceptionCode( 1 );
		
		$this->stage->process( $this->context );
	}
	
	public function test_process_with_package_not_found(): void {
		$package_id = 'nonexistent/package:1.0.0';
		
		// Mock empty API response
		$this->mockTestPackageDownloadUrls( [] );
		
		// Create input definition with package argument
		$definition = new InputDefinition( [
			new InputArgument( 'package', InputArgument::OPTIONAL ),
			new InputOption( 'verify', null, InputOption::VALUE_NEGATABLE, '', true ),
		] );
		
		// Create input with package argument
		$input = new ArrayInput( [ 'package' => $package_id ], $definition );
		$this->context->input = $input;
		
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( "Package not found: $package_id" );
		$this->expectExceptionCode( 1 );
		
		$this->stage->process( $this->context );
	}
	
	public function test_process_with_download_failure(): void {
		$package_id = 'woocommerce/e2e:stable';
		$download_url = 'https://example.com/package.zip';
		
		// Mock the API response
		$this->mockTestPackageDownloadUrls( [
			$package_id => [
				'url' => $download_url,
				'checksum' => 'abc123',
				'version' => 'stable'
			]
		] );
		
		// Don't mock the download URL to simulate failure
		
		// Create input definition with package argument
		$definition = new InputDefinition( [
			new InputArgument( 'package', InputArgument::OPTIONAL ),
			new InputOption( 'verify', null, InputOption::VALUE_NEGATABLE, '', true ),
		] );
		
		// Create input with package argument
		$input = new ArrayInput( [ 'package' => $package_id ], $definition );
		$this->context->input = $input;
		
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Download failed:' );
		$this->expectExceptionCode( 1 );
		
		$this->stage->process( $this->context );
	}
	
	public function test_process_with_checksum_verification_failure(): void {
		$package_id = 'woocommerce/e2e:stable';
		$download_url = 'https://example.com/package.zip';
		$wrong_checksum = 'wrong_checksum';
		
		// Mock the API response with wrong checksum
		$this->mockTestPackageDownloadUrls( [
			$package_id => [
				'url' => $download_url,
				'checksum' => $wrong_checksum,
				'version' => 'stable'
			]
		] );
		
		// Mock the download
		$this->mockDownloadUrl( $download_url, $this->createMockZip( 'test content' ) );
		
		// Create input definition with package argument
		$definition = new InputDefinition( [
			new InputArgument( 'package', InputArgument::OPTIONAL ),
			new InputOption( 'verify', null, InputOption::VALUE_NEGATABLE, '', true ),
		] );
		
		// Create input with package argument and verify enabled (default)
		$input = new ArrayInput( [ 'package' => $package_id ], $definition );
		$this->context->input = $input;
		
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( "Checksum verification failed for: $package_id" );
		$this->expectExceptionCode( 1 );
		
		$this->stage->process( $this->context );
	}
	
	public function test_process_with_checksum_verification_disabled(): void {
		$package_id = 'woocommerce/e2e:stable';
		$download_url = 'https://example.com/package.zip';
		$wrong_checksum = 'wrong_checksum';
		
		// Mock the API response with wrong checksum
		$this->mockTestPackageDownloadUrls( [
			$package_id => [
				'url' => $download_url,
				'checksum' => $wrong_checksum,
				'version' => 'stable'
			]
		] );
		
		// Mock the download
		$this->mockDownloadUrl( $download_url, $this->createMockZip( 'test content' ) );
		
		// Create input definition with package argument and verify option
		$definition = new InputDefinition( [
			new InputArgument( 'package', InputArgument::OPTIONAL ),
			new InputOption( 'verify', null, InputOption::VALUE_NEGATABLE, '', true ),
		] );
		
		// Create input with package argument and verify disabled
		$input = new ArrayInput( [ 'package' => $package_id, '--verify' => false ], $definition );
		$this->context->input = $input;
		
		$result = $this->stage->process( $this->context );
		
		// Should succeed despite wrong checksum
		$this->assertNotNull( $result->get( 'static_test_package_path' ) );
		$this->assertEquals( $package_id, $result->get( 'static_test_package_id' ) );
	}
	
	public function test_process_without_checksum_in_response(): void {
		$package_id = 'woocommerce/e2e:stable';
		$download_url = 'https://example.com/package.zip';
		
		// Mock the API response without checksum
		$this->mockTestPackageDownloadUrls( [
			$package_id => [
				'url' => $download_url,
				'version' => 'stable'
			]
		] );
		
		// Mock the download
		$this->mockDownloadUrl( $download_url, $this->createMockZip( 'test content' ) );
		
		// Create input definition with package argument
		$definition = new InputDefinition( [
			new InputArgument( 'package', InputArgument::OPTIONAL ),
			new InputOption( 'verify', null, InputOption::VALUE_NEGATABLE, '', true ),
		] );
		
		// Create input with package argument
		$input = new ArrayInput( [ 'package' => $package_id ], $definition );
		$this->context->input = $input;
		
		$result = $this->stage->process( $this->context );
		
		// Should succeed without checksum verification
		$this->assertNotNull( $result->get( 'static_test_package_path' ) );
		$this->assertEquals( $package_id, $result->get( 'static_test_package_id' ) );
	}
}