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
	 * Get the process-specific prefix for test packages.
	 * This ensures each parallel process has its own namespace for packages.
	 * 
	 * @return string Process-specific prefix like "qit-integration-test-1-" or "qit-integration-test-serial-xyz-"
	 */
	public static function get_process_prefix(): string {
		$process_id = defined( 'QIT_TEST_PROCESS_ID' ) ? QIT_TEST_PROCESS_ID : 'unknown';
		return self::TEST_PACKAGE_PREFIX . $process_id . '-';
	}
	
	/**
	 * Generate a unique test package name with the standard prefix.
	 * Now includes the process ID to prevent conflicts in parallel execution.
	 * 
	 * @param string $namespace The namespace (e.g., 'woocommerce')
	 * @param string $base_name Optional base name for the package
	 * @return string Full package name like "woocommerce/qit-integration-test-{process}-{base}-{uniqid}"
	 */
	public static function generate_test_package_name( string $namespace, string $base_name = '' ): string {
		$unique_id = substr( uniqid(), 0, 8 );
		$process_prefix = self::get_process_prefix();
		
		if ( $base_name ) {
			return sprintf( '%s/%s%s-%s', $namespace, $process_prefix, $base_name, $unique_id );
		}
		
		return sprintf( '%s/%s%s', $namespace, $process_prefix, $unique_id );
	}
	
	/**
	 * Clean up process-specific test packages from the Manager database via API.
	 * This only cleans packages created by the current process, making it safe for parallel execution.
	 * 
	 * @return int Number of packages cleaned up
	 */
	public static function cleanup_process_packages(): int {
		// Get process-specific prefix
		$process_prefix = self::get_process_prefix();
		
		// Check we're in a test environment
		if ( ! defined( 'PHPUNIT_TESTSUITE' ) && ! getenv( 'QIT_SELF_TESTS' ) ) {
			return 0;
		}
		
		try {
			// Get the Manager secret from .env file if available
			$env_file = dirname( __DIR__ ) . '/.env';
			$manager_secret = null;
			
			if ( file_exists( $env_file ) ) {
				$env_contents = file_get_contents( $env_file );
				if ( preg_match( '/^QIT_CUSTOM_TESTS_SECRET="([^"]+)"/m', $env_contents, $matches ) ) {
					$manager_secret = $matches[1];
				}
			}
			
			// If no secret found in .env, skip cleanup
			if ( ! $manager_secret ) {
				return 0;
			}
			
			// Build the request with proper authentication
			// Note: This would need an updated Manager endpoint that accepts a prefix parameter
			// For now, we'll use the CLI to delete specific packages
			// This is a workaround until the Manager API supports prefix-based deletion
			
			// Validate QIT CLI path before using it
			if ( ! isset( $GLOBALS['qit-php'] ) ) {
				throw new \RuntimeException( 'GLOBALS[qit-php] is not set' );
			}
			$qit_cli_path = $GLOBALS['qit-php'];
			if ( ! is_file( $qit_cli_path ) ) {
				throw new \RuntimeException( 'QIT CLI path is not a valid file: ' . $qit_cli_path );
			}
			
			// List packages with our process prefix using CLI
			$list_output = shell_exec( sprintf( 
				'php %s package:list --json 2>/dev/null',
				escapeshellarg( $qit_cli_path )
			) );
			
			if ( ! $list_output ) {
				return 0;
			}
			
			$packages = json_decode( $list_output, true );
			if ( ! is_array( $packages ) ) {
				return 0;
			}
			
			$deleted_count = 0;
			foreach ( $packages as $package ) {
				// Validate package structure
				if ( ! isset( $package['name'] ) ) {
					throw new \RuntimeException( 'Package missing name field: ' . json_encode( $package ) );
				}
				
				$package_name = $package['name'];
				
				// Extract package part after namespace
				$parts = explode( '/', $package_name, 2 );
				if ( count( $parts ) !== 2 ) {
					// Skip packages without namespace (shouldn't happen but be defensive)
					continue;
				}
				
				$package_part = $parts[1];
				
				// Check if package starts with our process-specific prefix
				if ( strpos( $package_part, $process_prefix ) === 0 ) {
					// Validate QIT CLI path exists
					if ( ! isset( $GLOBALS['qit-php'] ) ) {
						throw new \RuntimeException( 'GLOBALS[qit-php] is not set' );
					}
					$qit_cli_path = $GLOBALS['qit-php'];
					if ( ! is_file( $qit_cli_path ) ) {
						throw new \RuntimeException( 'QIT CLI path is not a valid file: ' . $qit_cli_path );
					}
					
					// Delete this package
					shell_exec( sprintf(
						'php %s package:delete %s --yes 2>/dev/null',
						escapeshellarg( $qit_cli_path ),
						escapeshellarg( $package_name )
					) );
					$deleted_count++;
				}
			}
			
			return $deleted_count;
			
		} catch ( \Exception $e ) {
			// Silently fail - cleanup is best effort
			return 0;
		}
	}
	
	/**
	 * Clean up all test packages from the Manager database via API.
	 * WARNING: This will delete ALL test packages, not just process-specific ones.
	 * Use cleanup_process_packages() for parallel-safe cleanup.
	 * 
	 * This should be called in test setup or teardown to prevent pollution.
	 * 
	 * The Manager endpoint will only delete packages with the hardcoded
	 * "qit-integration-test-" prefix for security reasons.
	 * 
	 * @deprecated Use cleanup_process_packages() for parallel-safe cleanup
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
	 * Smart cleanup that automatically chooses the right strategy.
	 * - In parallel mode: only cleans process-specific packages
	 * - In serial mode: cleans all test packages
	 * 
	 * This is the recommended method to use in test setUp/tearDown.
	 * 
	 * @return int Number of packages cleaned up
	 */
	public static function cleanup_test_packages(): int {
		// In parallel mode, only clean our process's packages
		if ( getenv( 'TEST_TOKEN' ) ) {
			return self::cleanup_process_packages();
		}
		
		// In serial mode, clean all test packages
		return self::cleanup_all_test_packages();
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