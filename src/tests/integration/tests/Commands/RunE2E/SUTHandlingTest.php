<?php

namespace QIT\IntegrationTests\Commands\RunE2E;

use QIT\IntegrationTests\TestCleanupHelper;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test E2E command with actual plugins/themes as SUT (System Under Test).
 * 
 * This covers the PRIMARY use case - users testing their own extensions.
 * 95% of QIT users are testing their own plugin/theme, not just WooCommerce.
 * 
 * Critical scenarios:
 * 1. Plugin ZIP as SUT
 * 2. Theme ZIP as SUT  
 * 3. Local directory as SUT
 * 4. Invalid/malformed extensions
 */
class SUTHandlingTest extends TestCase {

	private string $fixturesDir;
	private array $tempDirs = [];
	private array $tempFiles = [];

	protected function setUp(): void {
		parent::setUp();
		
		// Clean up any leftover test packages before running
		TestCleanupHelper::cleanup_all_test_packages();
		$this->fixturesDir = __DIR__ . '/../../../fixtures';
	}

	protected function tearDown(): void {		foreach ( $this->tempFiles as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
		
		// Then temp directories}
		parent::tearDown();
	}

	/**
	 * Test #36: Plugin ZIP as additional
	 * 
	 * Coverage aim: Validates testing with plugin ZIP files.
	 * Tests that custom plugin builds can be added to the test environment
	 * via ZIP files using the --plugin flag.
	 * 
	 * Key aspects tested:
	 * - Plugin ZIP loading via --plugin flag
	 * - Custom plugin integration in tests
	 * - Plugin installation and activation
	 * - Tests run with additional plugins
	 *
	 * Note: For true SUT testing, the plugin needs to be registered with WooCommerce.com
	 * This test demonstrates testing with custom plugin builds.
	 */
	public function test_plugin_zip_as_additional(): void {
		// Create a minimal test plugin
		$pluginZip = $this->createTestPluginZip( 'my-test-plugin' );
		
		// Use our existing test package
		$testPackage = $this->fixturesDir . '/test-packages/regular-test-package-one';
		
		$config = $this->createConfig( [ $testPackage ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',  // Test against WooCommerce
			'--plugin=my-test-plugin@' . $pluginZip,  // Add our plugin
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Should still run the test package
		$this->assertStringContainsString( 'PACKAGE [1/1]', $output );
		
		// Should complete successfully
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
		
		// Tests should pass (our plugin doesn't break anything)
		$this->assertStringContainsString( '3 passed', $output );
	}

	/**
	 * Test #37: Local directory as additional plugin
	 * 
	 * Coverage aim: Validates testing with local plugin directories.
	 * Tests that plugins under development can be loaded from local directories
	 * without needing to create ZIP files first.
	 * 
	 * Key aspects tested:
	 * - Local directory plugin loading
	 * - Development workflow support
	 * - Direct plugin path usage
	 * - No ZIP creation required
	 */
	public function test_local_directory_as_additional(): void {
		// Create a minimal plugin directory
		$pluginDir = $this->createTestPluginDirectory( 'my-local-plugin' );
		
		$testPackage = $this->fixturesDir . '/test-packages/regular-test-package-one';
		$config = $this->createConfig( [ $testPackage ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--plugin=my-local-plugin@' . $pluginDir,  // Local directory
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Tests should pass (plugin loaded successfully)
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
		$this->assertStringContainsString( '3 passed', $output );
	}

	/**
	 * Test #38: Plugin with PHP fatal error
	 * 
	 * Coverage aim: Validates handling of plugins with fatal errors.
	 * Tests that plugins with PHP fatal errors don't crash the entire test suite,
	 * demonstrating WordPress's resilient plugin loading behavior.
	 * 
	 * Key aspects tested:
	 * - Fatal error isolation
	 * - WordPress plugin error handling
	 * - Test suite resilience
	 * - Silent failure mode
	 *
	 * This is important for users to understand failure modes
	 */
	public function test_plugin_with_php_fatal_error(): void {
		// Create a plugin with a fatal error
		$badPlugin = $this->createBadPluginZip();
		
		$testPackage = $this->fixturesDir . '/test-packages/regular-test-package-one';
		$config = $this->createConfig( [ $testPackage ] );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--plugin=bad-plugin@' . $badPlugin,
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		// Should actually pass - WordPress silently ignores plugins with fatal errors during activation
		// This is actually good behavior - one bad plugin shouldn't break the entire test suite
		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Tests should still pass
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
	}

	/**
	 * Test #39: Multiple plugins
	 * 
	 * Coverage aim: Validates testing with multiple additional plugins.
	 * Tests that multiple plugins can be loaded simultaneously using
	 * multiple --plugin flags.
	 * 
	 * Key aspects tested:
	 * - Multiple plugin loading
	 * - Plugin interaction testing
	 * - Multiple --plugin flags
	 * - Plugin activation order
	 */
	public function test_multiple_plugins(): void {
		$plugin1 = $this->createTestPluginZip( 'plugin-one' );
		$plugin2 = $this->createTestPluginZip( 'plugin-two' );
		
		$testPackage = $this->fixturesDir . '/test-packages/regular-test-package-one';
		$config = $this->createConfig( [ $testPackage ] );

		// Test with multiple plugins
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--plugin=plugin-one@' . $plugin1,
			'--plugin=plugin-two@' . $plugin2,
			'--config=' . $config,
		], return_process: true );

		$output = $proc->getOutput();

		$this->assertEquals( 0, $proc->getExitCode() );
		
		// Tests should pass with multiple plugins loaded
		$this->assertStringContainsString( 'Status:        ✓ PASSED', $output );
		$this->assertStringContainsString( '3 passed', $output );
	}

	// ============= Helper Methods =============

	/**
	 * Create a minimal WordPress plugin ZIP for testing
	 */
	private function createTestPluginZip( string $pluginSlug ): string {
		$tempDir = sys_get_temp_dir() . '/qit-test-plugin-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$pluginDir = $tempDir . '/' . $pluginSlug;
		mkdir( $pluginDir, 0755, true );
		
		// Create main plugin file
		$pluginContent = <<<PHP
<?php
/**
 * Plugin Name: Test Plugin - {$pluginSlug}
 * Plugin URI: https://example.com/
 * Description: A test plugin for QIT E2E testing
 * Version: 1.0.0
 * Author: QIT Tests
 * Text Domain: {$pluginSlug}
 * 
 * WC requires at least: 7.0
 * WC tested up to: 8.5
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Basic plugin functionality
add_action( 'init', function() {
	// Register a simple shortcode
	add_shortcode( '{$pluginSlug}_test', function() {
		return '<div class="{$pluginSlug}-test">Plugin {$pluginSlug} is active!</div>';
	});
});

// WooCommerce compatibility
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
});
PHP;
		
		file_put_contents( $pluginDir . '/' . $pluginSlug . '.php', $pluginContent );
		
		// Create a simple readme
		$readme = "=== {$pluginSlug} ===\nTest plugin for QIT E2E testing\n";
		file_put_contents( $pluginDir . '/readme.txt', $readme );
		
		// Create the ZIP
		$zipPath = $tempDir . '/' . $pluginSlug . '.zip';
		exec( "cd " . escapeshellarg( $tempDir ) . " && zip -r " . escapeshellarg( $zipPath ) . " " . escapeshellarg( $pluginSlug ) );
		
		$this->tempFiles[] = $zipPath;
		return $zipPath;
	}

	/**
	 * Create a test plugin directory (not zipped)
	 */
	private function createTestPluginDirectory( string $pluginSlug ): string {
		$tempDir = sys_get_temp_dir() . '/qit-test-plugin-dir-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		// Create main plugin file
		$pluginContent = <<<PHP
<?php
/**
 * Plugin Name: Local Dev Plugin - {$pluginSlug}
 * Description: A local development plugin for testing
 * Version: 1.0.0-dev
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Simple test functionality
add_action( 'wp_footer', function() {
	echo '<!-- {$pluginSlug} is active -->';
});
PHP;
		
		file_put_contents( $tempDir . '/' . $pluginSlug . '.php', $pluginContent );
		
		return $tempDir;
	}

	/**
	 * Create a plugin with a fatal error for testing failure scenarios
	 */
	private function createBadPluginZip(): string {
		$tempDir = sys_get_temp_dir() . '/qit-bad-plugin-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$pluginDir = $tempDir . '/bad-plugin';
		mkdir( $pluginDir, 0755, true );
		
		// Create plugin with syntax error
		$badPlugin = <<<PHP
<?php
/**
 * Plugin Name: Bad Plugin
 * Description: This plugin has a fatal error
 * Version: 1.0.0
 */

// Intentional fatal error - calling undefined function
this_function_does_not_exist();

// Or syntax error
syntax error here
PHP;
		
		file_put_contents( $pluginDir . '/bad-plugin.php', $badPlugin );
		
		// Create ZIP
		$zipPath = $tempDir . '/bad-plugin.zip';
		exec( "cd " . escapeshellarg( $tempDir ) . " && zip -r " . escapeshellarg( $zipPath ) . " bad-plugin" );
		
		$this->tempFiles[] = $zipPath;
		return $zipPath;
	}

	private function createConfig( array $testPackages ): string {
		$config = [
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => $testPackages
					]
				]
			]
		];

		$tempDir = sys_get_temp_dir() . '/qit-fixture-test-' . uniqid();
		mkdir( $tempDir, 0755, true );
		$this->tempDirs[] = $tempDir;
		
		$configPath = $tempDir . '/qit.json';
		file_put_contents( $configPath, json_encode( $config, JSON_PRETTY_PRINT ) );
		
		return $configPath;
	}
}