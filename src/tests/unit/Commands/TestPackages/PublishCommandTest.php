<?php

namespace QIT_CLI\Tests\Unit\Commands\TestPackages;

use QIT_CLI\App;
use QIT_CLI\Commands\TestPackages\PublishCommand;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI_Tests\QITTestCase;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use function QIT_CLI\get_manager_url;

class PublishCommandTest extends QITTestCase {
	private PublishCommand $command;
	private CommandTester $command_tester;
	private TestPackageManifestParser $manifest_parser;
	private Zipper $zipper;
	private WooExtensionsList $woo_extensions_list;

	public function setUp(): void {
		parent::setUp();

		$this->manifest_parser = $this->createMock( TestPackageManifestParser::class );
		$this->zipper = $this->createMock( Zipper::class );
		$this->woo_extensions_list = $this->createMock( WooExtensionsList::class );

		$this->command = new PublishCommand( $this->manifest_parser, $this->zipper, $this->woo_extensions_list );

		$application = new Application();
		$application->add( $this->command );

		$this->command_tester = new CommandTester( $this->command );
	}

	public function test_publish_happy_path(): void {
		// Mock WooExtensionsList to return a valid extension ID for 'vendor'
		// Called twice: once in resolve_reference() and once in validate_reference_matches_manifest()
		$this->woo_extensions_list->expects( $this->exactly( 2 ) )
			->method( 'get_woo_extension_id_by_slug' )
			->with( 'vendor' )
			->willReturn( 123 );

		// Create a temporary directory with manifest.json
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_' );
		mkdir( $temp_dir );
		
		$manifest_content = json_encode( [
			'$schema' => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'e2e',
			'lifecycle' => [
				'global' => [
					'setup' => [ 'echo "Global setup"' ],
				],
				'test' => [
					'run' => [ 'echo "Test run"' ],
				],
			],
		] );
		
		file_put_contents( $temp_dir . '/manifest.json', $manifest_content );

		// Mock HTTP response for successful upload
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-packages' ), json_encode( [
			'upload_id' => 'test-upload-123',
			'checksum' => 'abc123def456',
		] ) );

		// Mock zipper methods
		$this->zipper->expects( $this->once() )
			->method( 'zip_directory' )
			->willReturnCallback( function( $source, $destination ) {
				// Create a dummy zip file
				touch( $destination );
				return true;
			} );

		$this->zipper->expects( $this->once() )
			->method( 'extract_zip' )
			->willReturnCallback( function( $zip_path, $extract_to ) {
				// Create manifest.json in extract directory
				mkdir( $extract_to, 0755, true );
				file_put_contents( $extract_to . '/manifest.json', json_encode( [
					'$schema' => 'https://qit.woo.com/json-schema/test-package',
					'test_type' => 'e2e',
					'lifecycle' => [
						'global' => [ 'setup' => [ 'echo "setup"' ] ],
						'test' => [ 'run' => [ 'echo "run"' ] ],
					],
				] ) );
				return true;
			} );

		// Mock manifest parser
		$manifest_data = [
			'vendor' => 'vendor',
			'package' => 'test-package',
			'version' => '1.0.0',
			'test_type' => 'e2e',
			'lifecycle' => [
				'global' => [ 'setup' => [ 'echo "setup"' ] ],
				'test' => [ 'run' => [ 'echo "run"' ] ],
			],
		];
		$this->manifest_parser->expects( $this->once() )
			->method( 'parse' )
			->willReturn( new \QIT_CLI\PreCommand\Objects\TestPackageManifest( $manifest_data ) );

		// Execute command
		$exit_code = $this->command_tester->execute( [
			'reference' => 'vendor/test-package:1.0.0',
			'path' => $temp_dir,
			'--test-type' => 'e2e',
		] );

		// Debug output
		$output = $this->command_tester->getDisplay();
		if ( $exit_code !== 0 ) {
			echo "Exit code: $exit_code\n";
			echo "Output: $output\n";
		}

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$this->assertStringContainsString( 'Package published successfully!', $output );
		$this->assertStringContainsString( 'Upload ID: test-upload-123', $output );
		$this->assertStringContainsString( 'Checksum: abc123def456', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_publish_with_invalid_reference(): void {
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_' );
		mkdir( $temp_dir );

		// Execute command with invalid reference
		$exit_code = $this->command_tester->execute( [
			'reference' => 'invalid-reference',
			'path' => $temp_dir,
		] );

		// Assert failure
		$this->assertEquals( 1, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( 'Invalid reference format', $output );

		// Clean up
		rmdir( $temp_dir );
	}

	public function test_publish_with_nonexistent_path(): void {
		// Execute command with non-existent path
		$exit_code = $this->command_tester->execute( [
			'reference' => 'vendor/test-package:1.0.0',
			'path' => '/nonexistent/path',
		] );

		// Assert failure
		$this->assertEquals( 1, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( 'Path must be a directory or zip file', $output );
	}

	public function test_publish_with_skip_validate(): void {
		// Mock WooExtensionsList to return a valid extension ID for 'vendor'
		$this->woo_extensions_list->expects( $this->once() )
			->method( 'get_woo_extension_id_by_slug' )
			->with( 'vendor' )
			->willReturn( 123 );

		// Create a temporary directory without manifest.json
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_' );
		mkdir( $temp_dir );

		// Mock HTTP response for successful upload
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/test-packages' ), json_encode( [
			'upload_id' => 'test-upload-456',
		] ) );

		// Mock zipper methods
		$this->zipper->expects( $this->once() )
			->method( 'zip_directory' )
			->willReturnCallback( function( $source, $destination ) {
				touch( $destination );
				return true;
			} );

		// Manifest parser should not be called when skipping validation
		$this->manifest_parser->expects( $this->never() )
			->method( 'parse' );

		// Execute command with --skip-validate
		$exit_code = $this->command_tester->execute( [
			'reference' => 'vendor/test-package:1.0.0',
			'path' => $temp_dir,
			'--skip-validate' => true,
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( 'Package published successfully!', $output );

		// Clean up
		rmdir( $temp_dir );
	}

	/**
	 * Recursively remove directory
	 */
	private function recursive_rmdir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			if ( is_dir( $path ) ) {
				$this->recursive_rmdir( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}
}