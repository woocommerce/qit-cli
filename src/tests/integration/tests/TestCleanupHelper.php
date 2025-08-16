<?php

namespace QIT\IntegrationTests;

use QIT_CLI\RequestBuilder;
use function QIT_CLI\get_manager_url;

/**
 * Helper class for cleaning up test data from the Manager database.
 * 
 * All integration tests should use package names with the prefix "qit-integration-test-"
 * to make them easily identifiable and cleanable.
 */
class TestCleanupHelper {
	
	/**
	 * Standard prefix for all integration test packages.
	 * This makes it easy to identify and clean up test data.
	 * MUST match the hardcoded prefix in the Manager's CleanupTestPackagesEndpoint
	 */
	const TEST_PACKAGE_PREFIX = 'qit-integration-test-';
	
	/**
	 * Generate a unique test package name with the standard prefix.
	 * 
	 * @param string $namespace The namespace (e.g., 'woocommerce')
	 * @param string $base_name Optional base name for the package
	 * @return string Full package name like "woocommerce/qit-integration-test-{base}-{uniqid}"
	 */
	public static function generate_test_package_name( string $namespace, string $base_name = '' ): string {
		$unique_id = substr( uniqid(), 0, 8 );
		
		if ( $base_name ) {
			return sprintf( '%s/%s%s-%s', $namespace, self::TEST_PACKAGE_PREFIX, $base_name, $unique_id );
		}
		
		return sprintf( '%s/%s%s', $namespace, self::TEST_PACKAGE_PREFIX, $unique_id );
	}
	
	/**
	 * Clean up all test packages from the Manager database via API.
	 * This should be called in test setup or teardown to prevent pollution.
	 * 
	 * The Manager endpoint will only delete packages with the hardcoded
	 * "qit-integration-test-" prefix for security reasons.
	 * 
	 * @return int Number of packages cleaned up
	 */
	public static function cleanup_all_test_packages(): int {
		// Check we're in a test environment
		// We check if we're running under PHPUnit as a safety measure
		if ( ! defined( 'PHPUNIT_TESTSUITE' ) && ! getenv( 'QIT_SELF_TESTS' ) ) {
			// In test environment but don't trigger warning during setup
			return 0;
		}
		
		try {
			// Get the Manager secret from .env file if available
			$env_file = __DIR__ . '/.env';
			$manager_secret = null;
			
			if ( file_exists( $env_file ) ) {
				$env_contents = file_get_contents( $env_file );
				if ( preg_match( '/^QIT_CUSTOM_TESTS_SECRET="([^"]+)"/m', $env_contents, $matches ) ) {
					$manager_secret = $matches[1];
				}
			}
			
			// If no secret found in .env, skip cleanup (might be running in a different context)
			if ( ! $manager_secret ) {
				return 0;
			}
			
			// Build the request with proper authentication
			$request_builder = new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/cli/cleanup-integration-test-packages' );
			$request_builder->with_method( 'POST' );
			$request_builder->with_post_body( [
				'manager_secret' => $manager_secret,
			] );
			
			$response = $request_builder->request();
			$data = json_decode( $response, true );
			
			if ( ! is_array( $data ) ) {
				return 0;
			}
			
			return $data['deleted_count'] ?? 0;
			
		} catch ( \Exception $e ) {
			// Silently fail - cleanup is best effort
			return 0;
		}
	}
	
	/**
	 * Check if we're in a safe test environment.
	 * 
	 * @return bool
	 */
	public static function is_test_environment(): bool {
		// Check for test mode using environment variable or PHPUnit
		if ( ! defined( 'PHPUNIT_TESTSUITE' ) && ! getenv( 'QIT_SELF_TESTS' ) ) {
			return false;
		}
		
		// Check that we're not on production Manager
		$manager_url = getenv( 'QIT_MANAGER_URL' ) ?: get_manager_url();
		if ( strpos( $manager_url, 'qit.woo.com' ) !== false ) {
			return false;
		}
		
		return true;
	}
}