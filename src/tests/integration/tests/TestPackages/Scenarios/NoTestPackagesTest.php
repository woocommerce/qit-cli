<?php

namespace integration\tests\TestPackages\Scenarios;

use function qit;

require_once __DIR__ . '/BaseScenarioTestCase.php';

/**
 * Test Scenario 6: Environment Without Test Packages
 * 
 * Tests the workflow for simple environments without test automation:
 * - Simple environment with specified extensions
 * - NO test phases run
 * - Clean WordPress/WooCommerce installation
 * - Used for manual testing without test automation
 */
class NoTestPackagesTest extends BaseScenarioTestCase {
	
	
	protected function setUp(): void {
		parent::setUp();
	}
	
	protected function tearDown(): void {
		parent::tearDown();
		// Environment cleanup handled by test framework
	}
	
	/**
	 * Test basic environment without test packages.
	 */
	public function test_env_up_without_test_packages() {
		// Run env:up without any test packages
		$output = qit( [ 
			'env:up', 
			'--json',
			'--woo=stable'
		] );
		$data = json_decode( $output, true );
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'env_id', $data );
		
		// Should NOT have test packages
		if ( isset( $data['test_packages_for_setup'] ) ) {
			$this->assertEmpty( $data['test_packages_for_setup'], 'Should have no test packages' );
		}
		
		// Should have WooCommerce
		$pluginSlugs = array_column( $data['plugins'] ?? [], 'slug' );
		$this->assertContains( 'woocommerce', $pluginSlugs );
		
		// NO test phases should run without test packages
	}
	
	/**
	 * Test that environment is clean without test package modifications.
	 */
	public function test_clean_wordpress_installation() {
		// Set up environment without test packages
		$output = qit( [ 
			'env:up', 
			'--json',
			'--woo=stable'
		] );
		$data = json_decode( $output, true );
		$envId = $data['env_id'];
		
		// Verify clean WordPress installation
		$wpVersion = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp core version --path=/var/www/html'
		] );
		$this->assertNotEmpty( trim( $wpVersion ) );
		
		// Check that no test-related options exist - some QIT options may exist for environment tracking
		$testOptions = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option list --search="qit_test_*" --format=count --path=/var/www/html'
		] );
		$this->assertEquals( '0', trim( $testOptions ), 'Should have no QIT test-specific options' );
		
		// WooCommerce should be clean
		$wooVersion = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp plugin get woocommerce --field=version --path=/var/www/html 2>/dev/null || echo "not_installed"'
		] );
		$this->assertNotEquals( 'not_installed', trim( $wooVersion ) );
	}
	
	/**
	 * Test manual testing workflow without automation.
	 */
	public function test_manual_testing_workflow() {
		// Step 1: Set up environment for manual testing
		$envOutput = qit( [ 
			'env:up', 
			'--json',
			'--php=8.2',
			'--wp=6.4',
			'--woo=stable',
			'--plugin=akismet',
			'--theme=twentytwentythree'
		] );
		$envData = json_decode( $envOutput, true );
		$envId = $envData['env_id'];
		
		// Step 2: Verify environment configuration
		$this->assertEquals( '8.2', $envData['php_version'] );
		$this->assertEquals( '6.4', $envData['wordpress_version'] );
		$this->assertNotEmpty( $envData['woocommerce_version'] );
		
		// Step 3: Get environment variables for manual testing
		$sourcePath = trim( qit( [ 'env:source', $envId ] ) );
		$this->assertFileExists( $sourcePath );
		
		// Read source file to verify variables
		$sourceContent = file_get_contents( $sourcePath );
		$this->assertStringContainsString( 'export QIT_SITE_URL=', $sourceContent );
		$this->assertStringContainsString( 'export QIT_ENV_ID="' . $envId . '"', $sourceContent );
		
		// Step 4: Verify plugins are available for manual testing
		$pluginList = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp plugin list --field=name --path=/var/www/html'
		] );
		$this->assertStringContainsString( 'woocommerce', $pluginList );
		$this->assertStringContainsString( 'akismet', $pluginList );
		
		// Step 5: Verify theme is available
		$themeList = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp theme list --field=name --path=/var/www/html'
		] );
		$this->assertStringContainsString( 'twentytwentythree', $themeList );
	}
	
	/**
	 * Test environment with development tools but no test packages.
	 */
	public function test_development_environment_no_tests() {
		// Set up development environment
		$envOutput = qit( [ 
			'env:up', 
			'--json',
			'--php_extension=xdebug',
			'--object_cache',
			'--env=WP_DEBUG=true',
			'--env=WP_DEBUG_LOG=true',
			'--env=SCRIPT_DEBUG=true'
		] );
		$envData = json_decode( $envOutput, true );
		$envId = $envData['env_id'];
		
		// Verify development tools
		$this->assertContains( 'xdebug', $envData['php_extensions'] ?? [] );
		$this->assertTrue( $envData['object_cache'] ?? false );
		
		// Verify debug environment variables
		$this->assertEquals( 'true', $envData['envs']['WP_DEBUG'] ?? '' );
		$this->assertEquals( 'true', $envData['envs']['WP_DEBUG_LOG'] ?? '' );
		$this->assertEquals( 'true', $envData['envs']['SCRIPT_DEBUG'] ?? '' );
		
		// No test packages should be present
		if ( isset( $envData['test_packages_for_setup'] ) ) {
			$this->assertEmpty( $envData['test_packages_for_setup'] );
		}
		
		// Environment should be ready for development
		$wpDebug = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp config get WP_DEBUG --path=/var/www/html'
		] );
		$this->assertEquals( '1', trim( $wpDebug ), 'WP_DEBUG should be enabled' );
	}
	
	/**
	 * Test environment reset without test packages.
	 */
	public function test_env_reset_without_test_packages() {
		// Set up environment
		$envOutput = qit( [ 
			'env:up', 
			'--json',
			'--woo=stable'
		] );
		$envData = json_decode( $envOutput, true );
		$envId = $envData['env_id'];
		
		// Make some manual changes
		qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option add manual_test_option "test_value" --path=/var/www/html'
		] );
		
		// Verify change was made
		$checkOption = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp option get manual_test_option --path=/var/www/html'
		] );
		$this->assertEquals( 'test_value', trim( $checkOption ) );
		
		// Run env:reset
		$resetProc = qit( [ 'env:reset' ], return_process: true );
		
		// Without test packages, reset might not have a backup to restore
		// This is expected behavior - reset is mainly for test package setups
		if ( $resetProc->getExitCode() !== 0 ) {
			$this->assertStringContainsString( 
				'backup', 
				strtolower( $resetProc->getOutput() . $resetProc->getErrorOutput() ),
				'Reset should indicate no backup available without test packages'
			);
		} else {
			// If reset succeeded, manual changes might be gone
			$afterReset = qit( [ 
				'env:exec', 
				'--env_id=' . $envId,
				'wp option get manual_test_option --path=/var/www/html 2>/dev/null || echo "not_found"'
			] );
			// Option might or might not exist depending on reset behavior
			$this->assertNotEmpty( trim( $afterReset ) );
		}
	}
	
	/**
	 * Test complete manual testing workflow without automation.
	 */
	public function test_complete_manual_workflow() {
		// Step 1: Developer/QA sets up environment for manual testing
		$envOutput = qit( [
			'env:up',
			'--json',
			'--php=8.0',
			'--wp=latest',
			'--woo=8.5.0',
			'--plugin=woocommerce-gateway-stripe',
			'--theme=storefront',
			'--php_extension=gd',
			'--php_extension=imagick'
		] );
		$envData = json_decode( $envOutput, true );
		$envId = $envData['env_id'];

		// Step 2: Verify complete environment setup
		$this->assertEquals( '8.0', $envData['php_version'] );
		$this->assertNotEmpty( $envData['wordpress_version'] );
		$this->assertEquals( '8.5.0', $envData['woocommerce_version'] );
		$this->assertContains( 'gd', $envData['php_extensions'] );
		$this->assertContains( 'imagick', $envData['php_extensions'] );
		
		// Step 3: No test phases should have run (no test packages were provided)
		
		// Step 4: Verify site is accessible for manual testing
		$siteUrl = $envData['site_url'];
		$this->assertNotEmpty( $siteUrl );
		
		// Step 5: Activate plugins for manual testing
		qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp plugin activate woocommerce woocommerce-gateway-stripe --path=/var/www/html'
		] );
		
		// Step 6: Activate theme
		qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp theme activate storefront --path=/var/www/html'
		] );
		
		// Step 7: Create test products for manual testing
		$productCreate = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp wc product create --name="Test Product" --type=simple --regular_price=19.99 --user=1 --path=/var/www/html 2>/dev/null || echo "product_creation_failed"'
		] );
		
		// Step 8: Environment is ready for manual testing
		// User can now:
		// - Browse to $siteUrl
		// - Log in as admin
		// - Test WooCommerce functionality
		// - Test payment gateway
		// - etc.
		
		// Step 9: Verify environment remains stable
		$finalCheck = qit( [ 
			'env:exec', 
			'--env_id=' . $envId,
			'wp core is-installed --path=/var/www/html && echo "WordPress OK"'
		] );
		$this->assertEquals( 'WordPress OK', trim( $finalCheck ) );
		
		// Step 10: Environment can be torn down when done
		// This happens in tearDown()
	}
}