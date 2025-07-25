<?php

namespace QIT_CLI\Tests\Unit\Commands\TestPackages;

use QIT_CLI\App;
use QIT_CLI\Commands\TestPackages\PackageDownloadCommand;
use QIT_CLI\TestPackageDownloader;
use QIT_CLI_Tests\QITTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use function QIT_CLI\get_manager_url;

class PackageDownloadCommandTest extends QITTestCase {
	private PackageDownloadCommand $command;
	private CommandTester $command_tester;
	private TestPackageDownloader $downloader;

	public function setUp(): void {
		parent::setUp();

		$this->downloader = App::make( TestPackageDownloader::class );
		$this->command = new PackageDownloadCommand( $this->downloader );

		$application = new Application();
		$application->add( $this->command );

		$this->command_tester = new CommandTester( $this->command );
	}

	public function test_download_single_package_success(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Create proper ZIP content
		$zip_content = $this->createMinimalPluginZip( 'test-package', '1.0.0' );

		// Mock the download URLs API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => hash( 'sha256', $zip_content ),
					'size' => strlen( $zip_content ),
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', $zip_content );

		// Execute command with --no-install to avoid dependency issues
		$exit_code = $this->command_tester->execute( [
'packages' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
			'--no-install' => true,
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '[1/1] vendor/test-package:1.0.0', $output );
		$this->assertStringContainsString( '✓ Ready at', $output );
		$this->assertStringContainsString( 'All 1 package(s) downloaded successfully!', $output );

		// Verify extracted directory was created (since extract is default)
		$expected_dir = $temp_dir . '/vendor-test-package-1.0.0';
		$this->assertDirectoryExists( $expected_dir );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_multiple_packages_sequential(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Create proper ZIP content
		$zip_content1 = $this->createMinimalPluginZip( 'package1', '1.0.0' );
		$zip_content2 = $this->createMinimalPluginZip( 'package2', '2.0.0' );

		// Mock the download URLs API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
'urls' => [
				'vendor/package1:1.0.0' => [
					'url' => 'https://example.com/package1.zip',
					'checksum' => hash( 'sha256', $zip_content1 ),
					'size' => strlen( $zip_content1 ),
					'version' => '1.0.0',
				],
				'vendor/package2:2.0.0' => [
					'url' => 'https://example.com/package2.zip',
					'checksum' => hash( 'sha256', $zip_content2 ),
					'size' => strlen( $zip_content2 ),
					'version' => '2.0.0',
				],
			],
		] ) );

		// Mock the actual file downloads
		App::setVar( 'mock_https://example.com/package1.zip', $zip_content1 );
		App::setVar( 'mock_https://example.com/package2.zip', $zip_content2 );

		// Execute command with --no-install to avoid dependency issues
		$exit_code = $this->command_tester->execute( [
'packages' => [ 'vendor/package1:1.0.0', 'vendor/package2:2.0.0' ],
			'--output-dir' => $temp_dir,
			'--no-install' => true,
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '[1/2] vendor/package1:1.0.0', $output );
		$this->assertStringContainsString( '[2/2] vendor/package2:2.0.0', $output );
		$this->assertStringContainsString( 'All 2 package(s) downloaded successfully!', $output );

		// Verify extracted directories were created
		$this->assertDirectoryExists( $temp_dir . '/vendor-package1-1.0.0' );
		$this->assertDirectoryExists( $temp_dir . '/vendor-package2-2.0.0' );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_with_no_extract(): void {
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

		// Execute command with --no-extract
		$exit_code = $this->command_tester->execute( [
'packages' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
			'--no-extract' => true,
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✓ Downloaded', $output );

		// Verify zip file was created but not extracted
		$expected_file = $temp_dir . '/vendor-test-package-1.0.0.zip';
		$this->assertFileExists( $expected_file );
		$this->assertEquals( 'test-content', file_get_contents( $expected_file ) );

		// Verify no extracted directory
		$expected_dir = $temp_dir . '/vendor-test-package-1.0.0';
		$this->assertFalse( is_dir( $expected_dir ) );

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
					'checksum' => 'wrong_checksum',
					'size' => 1024,
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', 'test-content' );

		// Execute command
		$exit_code = $this->command_tester->execute( [
'packages' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
		] );

		// Assert failure
		$this->assertEquals( 2, $exit_code ); // Total failure
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✗ Failed', $output );
		$this->assertStringContainsString( 'Checksum verification failed', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_download_with_verify_disabled(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Create proper ZIP content
		$zip_content = $this->createMinimalPluginZip( 'test-package', '1.0.0' );

		// Mock the download URLs API response with wrong checksum
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => 'wrong_checksum',
					'size' => strlen( $zip_content ),
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', $zip_content );

		// Execute command with --verify=false and --no-install
		$exit_code = $this->command_tester->execute( [
'packages' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
			'--verify' => false,
			'--no-install' => true,
		] );

		// Assert success despite wrong checksum
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✓ Ready at', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_invalid_package_identifier(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Execute command with invalid package identifier
		$exit_code = $this->command_tester->execute( [
'packages' => [ 'invalid-format' ],
			'--output-dir' => $temp_dir,
		] );

		// Assert failure
		$this->assertEquals( 1, $exit_code ); // Failure
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( 'Invalid package identifier format', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_package_not_found(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Mock empty API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
'urls' => [],
		] ) );

		// Execute command
		$exit_code = $this->command_tester->execute( [
'packages' => [ 'vendor/nonexistent:1.0.0' ],
			'--output-dir' => $temp_dir,
		] );

		// Assert failure
		$this->assertEquals( 2, $exit_code ); // Total failure
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( '✗ Failed', $output );
		$this->assertStringContainsString( 'Package not found or access denied', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_json_output_format(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_download_' );
		mkdir( $temp_dir, 0755, true );

		// Create proper ZIP content
		$zip_content = $this->createMinimalPluginZip( 'test-package', '1.0.0' );

		// Mock the download URLs API response
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-package-download-urls' ), json_encode( [
'urls' => [
				'vendor/test-package:1.0.0' => [
					'url' => 'https://example.com/test-package.zip',
					'checksum' => hash( 'sha256', $zip_content ),
					'size' => strlen( $zip_content ),
					'version' => '1.0.0',
				],
			],
		] ) );

		// Mock the actual file download
		App::setVar( 'mock_https://example.com/test-package.zip', $zip_content );

		// Execute command with JSON format and --no-install
		$exit_code = $this->command_tester->execute( [
'packages' => [ 'vendor/test-package:1.0.0' ],
			'--output-dir' => $temp_dir,
			'--format' => 'json',
			'--no-install' => true,
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		
		// Should contain JSON output
		$this->assertStringContainsString( '"success": true', $output );
		$this->assertStringContainsString( '"requested": 1', $output );
		$this->assertStringContainsString( '"successful": 1', $output );
		$this->assertStringContainsString( '"failed": 0', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

}
