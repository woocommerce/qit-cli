<?php

namespace QIT_CLI\Tests\Unit\Commands\TestPackages;

use QIT_CLI\App;
use QIT_CLI\Auth;
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
	private Auth $auth;

	public function setUp(): void {
		parent::setUp();

		$this->manifest_parser = $this->createMock( TestPackageManifestParser::class );
		$this->zipper = $this->createMock( Zipper::class );
		$this->woo_extensions_list = $this->createMock( WooExtensionsList::class );
		$this->auth = $this->createMock( Auth::class );

		$this->command = new PublishCommand( $this->manifest_parser, $this->zipper, $this->woo_extensions_list, $this->auth );

		$application = new Application();
		$application->add( $this->command );

		$this->command_tester = new CommandTester( $this->command );
	}

	public function test_publish_happy_path(): void {
		// Mock Auth to return a username-based user (not email)
		$this->auth->expects( $this->any() )
			->method( 'getAuthenticatedUser' )
			->willReturn( [
				'user' => 'testuser',
				'is_email_user' => false,
			] );

		// Mock WooExtensionsList to return a valid extension ID for 'vendor'
		// Called multiple times: in resolve_reference() and validate_reference_matches_manifest()
		$this->woo_extensions_list->expects( $this->exactly( 2 ) )
			->method( 'get_woo_extension_id_by_slug' )
			->with( 'vendor' )
			->willReturn( 123 );

		// Mock WooExtensionsList to return that user maintains the extension
		$this->woo_extensions_list->expects( $this->once() )
			->method( 'user_maintains' )
			->with( 'vendor' )
			->willReturn( true );

		// Create a temporary directory with manifest.json
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_' );
		mkdir( $temp_dir );
		
		$manifest_content = json_encode( [
			'$schema' => 'https://qit.woo.com/json-schema/test-package',
			'vendor' => 'vendor',
			'package' => 'test-package',
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
			'test' => [
				'phases' => [
					'run' => [ 'echo "run"' ],
				],
			],
		];
		$this->manifest_parser->expects( $this->once() )
			->method( 'parse' )
			->willReturn( new \QIT_CLI\PreCommand\Objects\TestPackageManifest( $manifest_data ) );

		// Execute command
		$exit_code = $this->command_tester->execute( [
			'path' => $temp_dir,
			'version' => '1.0.0',
			'--test-type' => 'e2e',
			'--extension' => 'vendor',
			'--package' => 'test-package',
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

	public function test_publish_with_invalid_version(): void {
		// Mock Auth to return a username-based user (not email)
		$this->auth->expects( $this->any() )
			->method( 'getAuthenticatedUser' )
			->willReturn( [
				'user' => 'testuser',
				'is_email_user' => false,
			] );

		// Mock WooExtensionsList to return that user maintains the extension
		$this->woo_extensions_list->expects( $this->once() )
			->method( 'user_maintains' )
			->with( 'vendor' )
			->willReturn( true );

		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_' );
		mkdir( $temp_dir );

		// Create a manifest.json file so we can reach the version validation
		$manifest_content = json_encode( [
			'$schema' => 'https://qit.woo.com/json-schema/test-package',
			'vendor' => 'vendor',
			'package' => 'test-package',
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

		// Note: manifest parser is not called because version validation fails first

		// Execute command with invalid version
		$exit_code = $this->command_tester->execute( [
			'path' => $temp_dir,
			'version' => 'invalid-version',
			'--extension' => 'vendor',
		] );

		// Assert failure
		$this->assertEquals( 1, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( 'Version must be in SemVer format', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_publish_with_nonexistent_path(): void {
		// Mock Auth to return a username-based user (not email)
		$this->auth->expects( $this->any() )
			->method( 'getAuthenticatedUser' )
			->willReturn( [
				'user' => 'testuser',
				'is_email_user' => false,
			] );

		// Mock WooExtensionsList to return that user maintains the extension
		$this->woo_extensions_list->expects( $this->once() )
			->method( 'user_maintains' )
			->with( 'vendor' )
			->willReturn( true );

		// Execute command with non-existent path
		$exit_code = $this->command_tester->execute( [
			'path' => '/nonexistent/path',
			'version' => '1.0.0',
			'--extension' => 'vendor',
		] );

		// Assert failure
		$this->assertEquals( 1, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( 'Path must be a directory or zip file', $output );
	}

	public function test_publish_with_skip_validate(): void {
		// Mock Auth to return a username-based user (not email)
		$this->auth->expects( $this->any() )
			->method( 'getAuthenticatedUser' )
			->willReturn( [
				'user' => 'testuser',
				'is_email_user' => false,
			] );

		// Mock WooExtensionsList to return a valid extension ID for 'vendor'
		$this->woo_extensions_list->expects( $this->once() )
			->method( 'get_woo_extension_id_by_slug' )
			->with( 'vendor' )
			->willReturn( 123 );

		// Mock WooExtensionsList to return that user maintains the extension
		$this->woo_extensions_list->expects( $this->once() )
			->method( 'user_maintains' )
			->with( 'vendor' )
			->willReturn( true );

		// Create a temporary directory with manifest.json
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_' );
		mkdir( $temp_dir );

		// Create a manifest.json file
		$manifest_content = json_encode( [
			'$schema' => 'https://qit.woo.com/json-schema/test-package',
			'vendor' => 'vendor',
			'package' => 'test-package',
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

		// Note: manifest parser is not called because --skip-validate bypasses validation

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


		// Execute command with --skip-validate
		$exit_code = $this->command_tester->execute( [
			'path' => $temp_dir,
			'version' => '1.0.0',
			'--skip-validate' => true,
			'--extension' => 'vendor',
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( 'Package published successfully!', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
	}

	public function test_publish_with_default_stable_version(): void {
		// Mock Auth to return a username-based user (not email)
		$this->auth->expects( $this->any() )
			->method( 'getAuthenticatedUser' )
			->willReturn( [
				'user' => 'testuser',
				'is_email_user' => false,
			] );

		// Mock WooExtensionsList to return a valid extension ID for 'vendor'
		// Called multiple times: in resolve_reference() and validate_reference_matches_manifest()
		$this->woo_extensions_list->expects( $this->exactly( 2 ) )
			->method( 'get_woo_extension_id_by_slug' )
			->with( 'vendor' )
			->willReturn( 123 );

		// Mock WooExtensionsList to return that user maintains the extension
		$this->woo_extensions_list->expects( $this->once() )
			->method( 'user_maintains' )
			->with( 'vendor' )
			->willReturn( true );

		// Create a temporary directory with manifest.json
		$temp_dir = sys_get_temp_dir() . '/' . uniqid( 'qit_test_' );
		mkdir( $temp_dir );
		
		$manifest_content = json_encode( [
			'$schema' => 'https://qit.woo.com/json-schema/test-package',
			'vendor' => 'vendor',
			'package' => 'test-package',
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
			'upload_id' => 'test-upload-stable',
			'checksum' => 'stable123def456',
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
			'version' => 'stable',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'run' => [ 'echo "run"' ],
				],
			],
		];
		$this->manifest_parser->expects( $this->once() )
			->method( 'parse' )
			->willReturn( new \QIT_CLI\PreCommand\Objects\TestPackageManifest( $manifest_data ) );

		// Execute command without version argument (should default to 'stable')
		$exit_code = $this->command_tester->execute( [
			'path' => $temp_dir,
			'--test-type' => 'e2e',
			'--extension' => 'vendor',
		] );

		// Assert success
		$this->assertEquals( 0, $exit_code );
		$output = $this->command_tester->getDisplay();
		$this->assertStringContainsString( 'Package published successfully!', $output );
		$this->assertStringContainsString( 'Upload ID: test-upload-stable', $output );
		$this->assertStringContainsString( 'vendor/test-package:stable', $output );

		// Clean up
		$this->recursive_rmdir( $temp_dir );
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