<?php

namespace integration\tests\Commands;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test extension resolution from various sources:
 * - CLI parameters (slug, paths, URLs)
 * - Config files (simple strings and complex objects)
 * - Different source types (wporg, wccom, local, url)
 */
class ExtensionResolutionTest extends TestCase {

	/**
	 * Test 1: Simple slug resolution from CLI
	 * Should auto-resolve from WordPress.org
	 */
	public function test_cli_slug_resolution(): void {
		$proc = qit( [
			'env:up',
			'--plugin', 'hello-dolly',  // Well-known WordPress.org plugin
			'--plugin', 'query-monitor', // Another WPORG plugin
			'--json',
		], return_process: true );

		$output = $proc->getOutput();
		$this->assertEquals( 0, $proc->getExitCode(), 'Should resolve slugs from WordPress.org. Output: ' . $output );
		
		$json = json_decode( $output, true );
		$this->assertIsArray( $json );
		$this->assertArrayHasKey( 'plugins', $json );
		
		// Check that plugins were resolved
		$plugin_slugs = array_column( $json['plugins'], 'slug' );
		$this->assertContains( 'hello-dolly', $plugin_slugs );
		$this->assertContains( 'query-monitor', $plugin_slugs );
	}

	/**
	 * Test 2: Local directory paths from CLI
	 */
	public function test_cli_local_directory_paths(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		$pluginDir = $tempDir . '/my-local-plugin';
		
		try {
			// Create a test plugin directory
			mkdir( $pluginDir, 0755, true );
			file_put_contents( $pluginDir . '/my-plugin.php', '<?php
/**
 * Plugin Name: My Local Plugin
 * Version: 1.0.0
 */
' );

			// Test absolute path
			$proc = qit( [
				'env:up',
				'--plugin', $pluginDir,
				'--json',
			], return_process: true );

			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'Should accept absolute directory path. Output: ' . $output );
			
			// Test relative path
			$originalCwd = getcwd();
			chdir( $tempDir );
			
			$proc = qit( [
				'env:up',
				'--plugin', './my-local-plugin',
				'--json',
			], return_process: true );
			
			chdir( $originalCwd );
			
			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'Should accept relative directory path. Output: ' . $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test 3: ZIP file paths from CLI
	 */
	public function test_cli_zip_file_paths(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Use existing test plugin zip
			$zipPath = __DIR__ . '/../../data/plugins/cli-plugin.zip';
			$this->assertFileExists( $zipPath, 'Test plugin zip should exist' );
			
			$proc = qit( [
				'env:up',
				'--plugin', realpath( $zipPath ),
				'--json',
			], return_process: true );

			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'Should accept zip file path. Output: ' . $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test 4: URLs from CLI
	 */
	public function test_cli_url_resolution(): void {
		$proc = qit( [
			'env:up',
			'--plugin', 'https://downloads.wordpress.org/plugin/hello-dolly.1.7.3.zip',
			'--json',
		], return_process: true );

		$output = $proc->getOutput();
		$this->assertEquals( 0, $proc->getExitCode(), 'Should accept plugin URL. Output: ' . $output );
		
		$json = json_decode( $output, true );
		$this->assertIsArray( $json );
		$this->assertArrayHasKey( 'plugins', $json );
	}

	/**
	 * Test 5: Config file with simple strings
	 */
	public function test_config_simple_strings(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create local plugin for testing
			$localPlugin = $tempDir . '/local-plugin';
			mkdir( $localPlugin, 0755, true );
			file_put_contents( $localPlugin . '/plugin.php', '<?php /* Plugin Name: Local */' );
			
			// Create config with various string formats
			$config = [
				'environments' => [
					'default' => [
						'plugins' => [
							'hello-dolly',                                              // Slug
							realpath( $localPlugin ),                                   // Absolute path
							'https://downloads.wordpress.org/plugin/akismet.5.3.3.zip', // URL
						],
						'themes' => [
							'twentytwentyfour', // WordPress.org theme
						]
					]
				]
			];
			
			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
			
			$proc = qit( [
				'env:up',
				'--config', $configPath,
				'--json',
			], return_process: true );

			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'Should process config with mixed string formats. Output: ' . $output );
			
			$json = json_decode( $output, true );
			$this->assertCount( 3, $json['plugins'], 'Should have 3 plugins' );
			$this->assertCount( 1, $json['themes'], 'Should have 1 theme' );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test 6: Config file with complex objects
	 */
	public function test_config_complex_objects(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create local plugin
			$localPlugin = $tempDir . '/complex-plugin';
			mkdir( $localPlugin, 0755, true );
			file_put_contents( $localPlugin . '/plugin.php', '<?php /* Plugin Name: Complex */' );
			
			// Create config with complex object formats
			$config = [
				'environments' => [
					'default' => [
						'plugins' => [
							// WPORG with specific version
							[
								'slug' => 'hello-dolly',
								'from' => 'wporg',
								'version' => '1.7.2'
							],
							// Local directory
							[
								'slug' => 'complex-plugin',
								'from' => 'local',
								'path' => realpath( $localPlugin )
							],
							// URL source - slug must match the directory inside the ZIP
							[
								'slug' => 'akismet',
								'from' => 'url',
								'url' => 'https://downloads.wordpress.org/plugin/akismet.5.3.3.zip'
							],
						]
					]
				]
			];
			
			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
			
			$proc = qit( [
				'env:up',
				'--config', $configPath,
				'--json',
			], return_process: true );

			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'Should process config with complex objects. Output: ' . $output );
			
			$json = json_decode( $output, true );
			$this->assertCount( 3, $json['plugins'], 'Should have 3 plugins' );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test 7: CLI overrides config
	 */
	public function test_cli_overrides_config(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Config specifies one plugin
			$config = [
				'environments' => [
					'default' => [
						'plugins' => [
							'hello-dolly'
						]
					]
				]
			];
			
			$configPath = $tempDir . '/qit.json';
			file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
			
			// CLI adds another plugin
			$proc = qit( [
				'env:up',
				'--config', $configPath,
				'--plugin', 'query-monitor',
				'--json',
			], return_process: true );

			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'CLI should override config. Output: ' . $output );
			
			$json = json_decode( $output, true );
			$plugin_slugs = array_column( $json['plugins'], 'slug' );
			$this->assertContains( 'hello-dolly', $plugin_slugs, 'Should have config plugin' );
			$this->assertContains( 'query-monitor', $plugin_slugs, 'Should have CLI plugin' );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test 8: Error handling for invalid paths
	 */
	public function test_error_handling_invalid_paths(): void {
		// Non-existent directory
		$proc = qit( [
			'env:up',
			'--plugin', '/tmp/does-not-exist-' . uniqid(),
			'--json',
		], return_process: true );

		$this->assertNotEquals( 0, $proc->getExitCode(), 'Should fail for non-existent path' );
		$this->assertStringContainsString( 'Unrecognized plugin identifier', $proc->getOutput() . $proc->getErrorOutput() );
		
		// Invalid URL
		$proc = qit( [
			'env:up',
			'--plugin', 'https://example.com/fake-plugin.zip',
			'--json',
		], return_process: true );

		$this->assertNotEquals( 0, $proc->getExitCode(), 'Should fail for invalid URL' );
		
		// Non-existent slug
		$proc = qit( [
			'env:up',
			'--plugin', 'this-plugin-definitely-does-not-exist-xyz123',
			'--json',
		], return_process: true );

		$this->assertNotEquals( 0, $proc->getExitCode(), 'Should fail for non-existent slug' );
		$this->assertStringContainsString( 'Not found in WPORG, WCCOM', $proc->getOutput() . $proc->getErrorOutput() );
	}

	/**
	 * Test 9: Symlinks
	 */
	public function test_symlink_resolution(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create actual plugin directory
			$actualPlugin = $tempDir . '/actual-plugin';
			mkdir( $actualPlugin, 0755, true );
			file_put_contents( $actualPlugin . '/plugin.php', '<?php /* Plugin Name: Symlinked */' );
			
			// Create symlink
			$symlink = $tempDir . '/symlinked-plugin';
			symlink( $actualPlugin, $symlink );
			
			$proc = qit( [
				'env:up',
				'--plugin', $symlink,
				'--json',
			], return_process: true );

			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'Should follow symlinks. Output: ' . $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}

	/**
	 * Test 10: Mixed sources in single command
	 */
	public function test_mixed_sources_single_command(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create local plugin
			$localPlugin = $tempDir . '/mixed-test';
			mkdir( $localPlugin, 0755, true );
			file_put_contents( $localPlugin . '/plugin.php', '<?php /* Plugin Name: Mixed */' );
			
			$proc = qit( [
				'env:up',
				'--plugin', 'hello-dolly',                                           // WPORG slug
				'--plugin', $localPlugin,                                            // Local path
				'--plugin', 'https://downloads.wordpress.org/plugin/akismet.5.3.3.zip', // URL
				'--theme', 'twentytwentyfour',                                       // WPORG theme
				'--json',
			], return_process: true );

			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'Should handle mixed sources. Output: ' . $output );
			
			$json = json_decode( $output, true );
			$this->assertCount( 3, $json['plugins'], 'Should have 3 plugins from different sources' );
			$this->assertCount( 1, $json['themes'], 'Should have 1 theme' );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}
	
	/**
	 * Test 11: Explicit slug with @ separator
	 */
	public function test_explicit_slug_with_at_separator(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create a plugin with a complex name
			$pluginDir = $tempDir . '/my-awesome-plugin-v2.3.4-final';
			mkdir( $pluginDir, 0755, true );
			file_put_contents( $pluginDir . '/plugin.php', '<?php /* Plugin Name: My Awesome Plugin */' );
			
			// Use explicit slug
			$proc = qit( [
				'env:up',
				'--plugin', 'my-awesome-plugin@' . $pluginDir,
				'--json',
			], return_process: true );

			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'Should accept explicit slug. Output: ' . $output );
			
			$json = json_decode( $output, true );
			$this->assertCount( 1, $json['plugins'] );
			$this->assertEquals( 'my-awesome-plugin', $json['plugins'][0]['slug'] );
			
			// Test with URL
			$proc = qit( [
				'env:up', 
				'--plugin', 'hello-dolly@https://downloads.wordpress.org/plugin/hello-dolly.1.7.3.zip',
				'--json',
			], return_process: true );
			
			$output = $proc->getOutput();
			$this->assertEquals( 0, $proc->getExitCode(), 'Should accept explicit slug with URL. Output: ' . $output );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}
	
	/**
	 * Test 12: Invalid inferred slugs cause errors
	 */
	public function test_invalid_inferred_slugs(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();

		try {
			mkdir( $tempDir, 0755, true );

			// Create a plugin with invalid slug name (contains dots that won't be stripped)
			// Version stripping removes patterns like -v2.3.4 or -1.2.3, so use dots in the base name
			$pluginDir = $tempDir . '/my.plugin.name';
			mkdir( $pluginDir, 0755, true );
			file_put_contents( $pluginDir . '/plugin.php', '<?php /* Plugin Name: My Plugin */' );

			// This should fail because the inferred slug contains dots (not a version pattern)
			$proc = qit( [
				'env:up',
				'--plugin', $pluginDir,
			], return_process: true, capture_stderr_separately: true );

			$output = $proc->getOutput() . $proc->getErrorOutput();

			// Should fail with invalid slug error
			$this->assertNotEquals( 0, $proc->getExitCode() );
			$this->assertStringContainsString( 'is not valid', $output );
			$this->assertStringContainsString( 'Please use explicit format: slug@path', $output );

		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}
	
	/**
	 * Test 13: Valid slug inference with warning
	 */
	public function test_valid_slug_inference_warning(): void {
		$tempDir = sys_get_temp_dir() . '/qit-test-' . uniqid();
		
		try {
			mkdir( $tempDir, 0755, true );
			
			// Create a plugin with valid slug name
			$pluginDir = $tempDir . '/my-awesome-plugin';
			mkdir( $pluginDir, 0755, true );
			file_put_contents( $pluginDir . '/plugin.php', '<?php /* Plugin Name: My Awesome Plugin */' );
			
			// This should work but show a warning
			$proc = qit( [
				'env:up',
				'--plugin', $pluginDir,
			], return_process: true, capture_stderr_separately: true );
			
			$stderr = $proc->getErrorOutput();
			
			// Should succeed
			$this->assertEquals( 0, $proc->getExitCode(), 'Should succeed with valid slug. Output: ' . $proc->getOutput() );
			
			// Should show warning about inferred slug
			$this->assertStringContainsString( 'Warning: Inferred slug "my-awesome-plugin" from path', $stderr );
			$this->assertStringContainsString( '<correct-slug>@' . $pluginDir, $stderr );
			
		} finally {
			if ( is_dir( $tempDir ) ) {
				exec( 'rm -rf ' . escapeshellarg( $tempDir ) );
			}
		}
	}
}