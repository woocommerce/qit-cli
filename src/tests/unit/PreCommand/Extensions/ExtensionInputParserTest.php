<?php

namespace unit\PreCommand\Extensions;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Extensions\ExtensionInputParser;

class ExtensionInputParserTest extends TestCase {
	
	/**
	 * Test parsing of pure slugs.
	 */
	public function test_parse_slug(): void {
		$slugs = [
			'woocommerce',
			'hello-dolly',
			'my_plugin',
			'plugin-123',
			'UPPERCASE-PLUGIN',
		];
		
		foreach ( $slugs as $slug ) {
			$extension = ExtensionInputParser::parse( $slug, 'plugin' );
			
			$this->assertEquals( $slug, $extension->slug );
			$this->assertEquals( 'plugin', $extension->type );
			$this->assertNull( $extension->from ); // Should not be set for slugs
			$this->assertEquals( 'stable', $extension->version );
		}
	}
	
	/**
	 * Test parsing with explicit slug using @ separator.
	 */
	public function test_parse_explicit_slug(): void {
		$tempDir = sys_get_temp_dir() . '/parser-test-explicit-' . uniqid();
		mkdir( $tempDir, 0755, true );
		
		try {
			// Create test directory
			$testDir = $tempDir . '/my-plugin-v2.3.4-final';
			mkdir( $testDir, 0755, true );
			
			// Test with local path and explicit slug
			$extension = ExtensionInputParser::parse( 'my-correct-slug@' . $testDir, 'plugin' );
			$this->assertEquals( 'my-correct-slug', $extension->slug );
			$this->assertEquals( 'local', $extension->from );
			$this->assertEquals( realpath( $testDir ), $extension->source );
			$this->assertStringContainsString( 'explicit slug', $extension->added_automatically );
			
			// Test with URL and explicit slug
			$extension = ExtensionInputParser::parse( 
				'woocommerce@https://example.com/woo-custom-build.zip', 
				'plugin' 
			);
			$this->assertEquals( 'woocommerce', $extension->slug );
			$this->assertEquals( 'url', $extension->from );
			$this->assertEquals( 'https://example.com/woo-custom-build.zip', $extension->source );
			$this->assertStringContainsString( 'explicit slug', $extension->added_automatically );
			
			// Test with ZIP file
			$zipFile = $tempDir . '/plugin-package.zip';
			touch( $zipFile );
			$extension = ExtensionInputParser::parse( 'actual-plugin-name@' . $zipFile, 'plugin' );
			$this->assertEquals( 'actual-plugin-name', $extension->slug );
			$this->assertEquals( 'local', $extension->from );
			
		} finally {
			exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
		}
	}
	
	/**
	 * Test error cases for @ separator.
	 */
	public function test_parse_explicit_slug_errors(): void {
		// Invalid slug before @ - won't be treated as slug@source format
		try {
			ExtensionInputParser::parse( 'invalid slug!@/path/to/plugin', 'plugin' );
			$this->fail( 'Expected exception for invalid input' );
		} catch ( \InvalidArgumentException $e ) {
			// Since "invalid slug!" is not a valid slug, the whole string is treated as one input
			$this->assertStringContainsString( 'Unrecognized plugin identifier', $e->getMessage() );
		}
		
		// Empty source after @
		try {
			ExtensionInputParser::parse( 'my-plugin@', 'plugin' );
			$this->fail( 'Expected exception for empty source' );
		} catch ( \InvalidArgumentException $e ) {
			$this->assertStringContainsString( 'Missing source after @', $e->getMessage() );
		}
		
		// Non-existent path with explicit slug
		try {
			ExtensionInputParser::parse( 'my-plugin@/does/not/exist', 'plugin' );
			$this->fail( 'Expected exception for non-existent path' );
		} catch ( \InvalidArgumentException $e ) {
			$this->assertStringContainsString( 'Unrecognized source', $e->getMessage() );
		}
	}
	
	/**
	 * Test parsing of local directory paths.
	 */
	public function test_parse_local_directory(): void {
		$tempDir = sys_get_temp_dir() . '/parser-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		
		try {
			// Create test directories - only valid slugs
			$dirs = [
				'simple-plugin' => 'simple-plugin',
				'my-plugin-master' => 'my-plugin-master',
				'plugin_underscore' => 'plugin_underscore',
				'UPPERCASE-PLUGIN' => 'UPPERCASE-PLUGIN',
			];
			
			foreach ( $dirs as $dirname => $expected_slug ) {
				$dir = $tempDir . '/' . $dirname;
				mkdir( $dir );
				
				$extension = ExtensionInputParser::parse( $dir, 'plugin' );
				
				$this->assertEquals( $expected_slug, $extension->slug, "Failed for directory: $dirname" );
				$this->assertEquals( 'local', $extension->from );
				$this->assertEquals( realpath( $dir ), $extension->source );
				$this->assertEquals( realpath( $dir ), $extension->directory );
			}
			
			// Test relative path
			$originalCwd = getcwd();
			chdir( $tempDir );
			
			$extension = ExtensionInputParser::parse( './simple-plugin', 'plugin' );
			$this->assertEquals( 'simple-plugin', $extension->slug );
			$this->assertEquals( 'local', $extension->from );
			
			chdir( $originalCwd );
			
		} finally {
			exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
		}
	}
	
	/**
	 * Test that invalid inferred slugs cause errors.
	 */
	public function test_invalid_inferred_slugs(): void {
		$tempDir = sys_get_temp_dir() . '/parser-test-invalid-' . uniqid();
		mkdir( $tempDir, 0755, true );
		
		try {
			// Directories with invalid slug names
			$invalidDirs = [
				'plugin-1.2.3',      // Contains dots
				'plugin(1)',         // Contains parentheses
				'my plugin',         // Contains space
				'plugin@version',    // Contains @
				'plugin!',           // Contains exclamation
			];
			
			foreach ( $invalidDirs as $dirname ) {
				$dir = $tempDir . '/' . $dirname;
				mkdir( $dir );
				
				try {
					ExtensionInputParser::parse( $dir, 'plugin' );
					$this->fail( "Expected exception for invalid slug from directory: $dirname" );
				} catch ( \InvalidArgumentException $e ) {
					$this->assertStringContainsString( 'is not valid', $e->getMessage() );
					$this->assertStringContainsString( 'Please use explicit format: slug@path', $e->getMessage() );
				}
			}
			
		} finally {
			exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
		}
	}
	
	/**
	 * Test parsing of ZIP file paths.
	 */
	public function test_parse_zip_files(): void {
		$tempDir = sys_get_temp_dir() . '/parser-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		
		try {
			$zips = [
				'plugin.zip' => 'plugin',
				'woocommerce.zip' => 'woocommerce',
				'my-awesome-plugin.zip' => 'my-awesome-plugin',
				'plugin-master.zip' => 'plugin-master',
				'test_plugin.zip' => 'test_plugin',
			];
			
			foreach ( $zips as $filename => $expected_slug ) {
				$file = $tempDir . '/' . $filename;
				touch( $file );
				
				$extension = ExtensionInputParser::parse( $file, 'plugin' );
				
				$this->assertEquals( $expected_slug, $extension->slug, "Failed for zip: $filename" );
				$this->assertEquals( 'local', $extension->from );
				$this->assertEquals( realpath( $file ), $extension->source );
				$this->assertNull( $extension->directory ); // Should not be set for zips
			}
			
		} finally {
			exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
		}
	}
	
	/**
	 * Test parsing of URLs.
	 */
	public function test_parse_urls(): void {
		$urls = [
			'https://downloads.wordpress.org/plugin/woocommerce.zip' => 'woocommerce',
			'https://example.com/my-plugin.zip' => 'my-plugin',
			'http://site.com/path/to/plugin-beta.zip' => 'plugin-beta',
			'https://github.com/user/repo/releases/plugin-master.zip' => 'plugin-master',
		];
		
		foreach ( $urls as $url => $expected_slug ) {
			$extension = ExtensionInputParser::parse( $url, 'theme' );
			
			$this->assertEquals( $expected_slug, $extension->slug, "Failed for URL: $url" );
			$this->assertEquals( 'url', $extension->from );
			$this->assertEquals( $url, $extension->source );
			$this->assertEquals( 'theme', $extension->type );
		}
		
		// Test URLs that would produce invalid slugs
		try {
			ExtensionInputParser::parse( 'https://example.com/plugin-1.2.3.zip', 'plugin' );
			$this->fail( 'Expected exception for URL with invalid slug' );
		} catch ( \InvalidArgumentException $e ) {
			$this->assertStringContainsString( 'is not valid', $e->getMessage() );
		}
		
		// Test URL without filename
		try {
			ExtensionInputParser::parse( 'https://example.com/', 'plugin' );
			$this->fail( 'Expected exception for URL without filename' );
		} catch ( \InvalidArgumentException $e ) {
			$this->assertStringContainsString( 'no filename in path', $e->getMessage() );
		}
	}
	
	/**
	 * Test invalid inputs.
	 */
	public function test_invalid_inputs(): void {
		$invalid_inputs = [
			'../../../etc/passwd', // Path that doesn't exist
			'not a valid slug!',   // Contains invalid characters
			'C:\\Windows\\System', // Windows path that doesn't exist
			'file:///etc/passwd',  // Unsupported scheme
			'ssh://git@github.com/repo', // Unsupported scheme
			'',                    // Empty string
			' ',                   // Whitespace only
		];
		
		foreach ( $invalid_inputs as $input ) {
			try {
				ExtensionInputParser::parse( $input, 'plugin' );
				$this->fail( "Expected exception for invalid input: '$input'" );
			} catch ( \InvalidArgumentException $e ) {
				// Different error messages for different cases
				if ( empty( trim( $input ) ) ) {
					$this->assertStringContainsString( 'Empty plugin identifier', $e->getMessage() );
				} else {
					$this->assertStringContainsString( 'Unrecognized plugin identifier', $e->getMessage() );
				}
			}
		}
	}
	
	
	/**
	 * Test that inferred slugs are marked differently from explicit slugs.
	 */
	public function test_slug_inference_metadata(): void {
		$tempDir = sys_get_temp_dir() . '/parser-test-metadata-' . uniqid();
		mkdir( $tempDir, 0755, true );
		
		try {
			// Test local path with inferred slug
			$pluginDir = $tempDir . '/my-plugin';
			mkdir( $pluginDir );
			
			$extension = ExtensionInputParser::parse( $pluginDir, 'plugin' );
			$this->assertEquals( 'my-plugin', $extension->slug );
			$this->assertStringContainsString( 'local path', $extension->added_automatically );
			$this->assertStringNotContainsString( 'explicit slug', $extension->added_automatically );
			
			// Test URL with inferred slug
			$extension = ExtensionInputParser::parse( 'https://example.com/plugin-name.zip', 'plugin' );
			$this->assertEquals( 'plugin-name', $extension->slug );
			$this->assertStringContainsString( 'URL', $extension->added_automatically );
			$this->assertStringNotContainsString( 'explicit slug', $extension->added_automatically );
			
			// Test with explicit slug
			$extension = ExtensionInputParser::parse( 'my-explicit-slug@' . $pluginDir, 'plugin' );
			$this->assertEquals( 'my-explicit-slug', $extension->slug );
			$this->assertStringContainsString( 'explicit slug', $extension->added_automatically );
			
		} finally {
			exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
		}
	}
}