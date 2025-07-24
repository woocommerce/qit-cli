<?php

namespace QIT_CLI\Tests\Unit\Commands\TestPackages;

use QIT_CLI\App;
use QIT_CLI\Commands\TestPackages\PackageDownloadCommand;
use QIT_CLI_Tests\QITTestCase;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use function QIT_CLI\get_manager_url;

class PackageDownloadCommandTest extends QITTestCase {
	private PackageDownloadCommand $command;
	private CommandTester $command_tester;
	private Zipper $zipper;

	public function setUp(): void {
		parent::setUp();

		$this->zipper = $this->createMock( Zipper::class );
		$this->command = new PackageDownloadCommand( $this->zipper );

		$application = new Application();
		$application->add( $this->command );

		$this->command_tester = new CommandTester( $this->command );
	}

	public function test_download_single_package_success(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Mock the download URLs API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
			'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => hash( 'sha256', 'test-content' ),
					'size' => 1024,
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', 'test-content' );

		// Execute command
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '[1/1] vendor/test-package:1.0.0', $output );
		$this->assertStringContainsString( '✓ Downloaded', $output );
		$this->assertStringContainsString( 'Summary: 1 successful, 0 failed', $output );

		// Verify file was created
		$expected_file = $temp_dir . '/vendor-test-package__1.0.0.zip';
		$this->assertFileExists( $expected_file );
		$this->assertEquals( 'test-content', file_get_contents( $expected_file ) );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_multiple_packages_sequential(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Mock the download URLs API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
			'urls' => [
				'vendor/package1:1.0.0' => [
					'url' => 'https://example.com/package1.zip',
					'checksum' => hash( 'sha256', 'content1' ),
					'size' => 1024,
					'version' => '1.0.0',
				],
				'vendor/package2:2.0.0' => [
					'url' => 'https://example.com/package2.zip',
					'checksum' => hash( 'sha256', 'content2' ),
					'size' => 2048,
					'version' => '2.0.0',
				],
			],
		] ) );

		// Mock the actual file downloads
		App::setVar( 'mock_https://example.com/package1.zip', 'content1' );
		App::setVar( 'mock_https://example.com/package2.zip', 'content2' );

		// Execute command
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'vendor/package1:1.0.0', 'vendor/package2:2.0.0' ],
			'--output-dir' => $temp_dir,
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '[1/2] vendor/package1:1.0.0', $output );
		$this->assertStringContainsString( '[2/2] vendor/package2:2.0.0', $output );
		$this->assertStringContainsString( 'Summary: 2 successful, 0 failed', $output );

		// Verify files were created
		$this->assertFileExists( $temp_dir . '/vendor-package1__1.0.0.zip' );
		$this->assertFileExists( $temp_dir . '/vendor-package2__2.0.0.zip' );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_with_checksum_verification_failure(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Mock the download URLs API response with wrong checksum
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
			'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => 'wrong-checksum',
					'size' => 1024,
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', 'test-content' );

		// Execute command
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
			'--verify',
		] );

		// Assert failure
		$this->assertEquals( 2, $exit_code ); // Total failure exit code
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✗ Failed (Checksum verification failed)', $output );
		$this->assertStringContainsString( 'Summary: 0 successful, 1 failed', $output );

		// Verify file was not left behind after checksum failure
		$expected_file = $temp_dir . '/vendor-test-package__1.0.0.zip';
		$this->assertFileNotExists( $expected_file );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_with_no_verify_flag(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Mock the download URLs API response with wrong checksum
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
			'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => 'wrong-checksum',
					'size' => 1024,
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', 'test-content' );

		// Execute command with --no-verify
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
			'--verify' => false,
		] );

		// Assert success (checksum verification skipped)
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✓ Downloaded', $output );
		$this->assertStringContainsString( 'Summary: 1 successful, 0 failed', $output );

		// Verify file was created despite wrong checksum
		$expected_file = $temp_dir . '/vendor-test-package__1.0.0.zip';
		$this->assertFileExists( $expected_file );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_with_extract_flag(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Mock the download URLs API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
			'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => hash( 'sha256', 'test-content' ),
					'size' => 1024,
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', 'test-content' );

		// Mock zipper methods
		$this->zipper->expects( $this->once() )
			->method( 'validate_zip' );

		$this->zipper->expects( $this->once() )
			->method( 'extract_zip' )
			->willReturnCallback( function( $zip_path, $extract_to ) {
				mkdir( $extract_to, 0755, true );
				file_put_contents( $extract_to . '/test-file.txt', 'extracted content' );
				return true;
			} );

		// Execute command with --extract
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
			'--extract' => true,
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✓ Downloaded', $output );

		// Verify extraction directory was created
		$extract_dir = $temp_dir . '/vendor-test-package__1.0.0';
		$this->assertDirectoryExists( $extract_dir );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_package_not_found(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Mock the download URLs API response with empty URLs
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
			'urls' => [],
		] ) );

		// Execute command
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'vendor/nonexistent:1.0.0' ],
			'--output-dir' => $temp_dir,
		] );

		// Assert total failure (no packages found)
		$this->assertEquals( 2, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✗ Failed (Package not found or access denied)', $output );
		$this->assertStringContainsString( 'Summary: 0 successful, 1 failed', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_with_force_flag(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Create existing file
		$existing_file = $temp_dir . '/vendor-test-package__1.0.0.zip';
		file_put_contents( $existing_file, 'old-content' );

		// Mock the download URLs API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
			'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => hash( 'sha256', 'new-content' ),
					'size' => 1024,
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', 'new-content' );

		// Execute command with --force
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
			'--force' => true,
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✓ Downloaded', $output );

		// Verify file was overwritten
		$this->assertEquals( 'new-content', file_get_contents( $existing_file ) );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_without_force_flag_existing_file(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Create existing file
		$existing_file = $temp_dir . '/vendor-test-package__1.0.0.zip';
		file_put_contents( $existing_file, 'old-content' );

		// Mock the download URLs API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
			'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => hash( 'sha256', 'new-content' ),
					'size' => 1024,
					'version' => '1.0.0',
				],
			],
		] ) );

		// Execute command without --force
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
		] );

		// Assert total failure
		$this->assertEquals( 2, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✗ Failed (File already exists (use --force to overwrite))', $output );

		// Verify file was not overwritten
		$this->assertEquals( 'old-content', file_get_contents( $existing_file ) );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_with_json_format(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Mock the download URLs API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
			'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => hash( 'sha256', 'test-content' ),
					'size' => 1024,
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', 'test-content' );

		// Execute command with --format=json
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
			'--format' => 'json',
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		
		// Verify JSON output is present
		$this->assertStringContainsString( '"success": true', $output );
		$this->assertStringContainsString( '"successful": 1', $output );
		$this->assertStringContainsString( '"failed": 0', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_invalid_reference_format(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Execute command with invalid reference
		$exit_code = $this->command_tester->execute( [
			'references' => [ 'invalid-reference' ],
			'--output-dir' => $temp_dir,
		] );

		// Assert failure
		$this->assertEquals( 1, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( 'Invalid reference format: invalid-reference', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	private function recursive_rmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->recursive_rmdir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}