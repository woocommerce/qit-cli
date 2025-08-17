<?php

namespace QIT\IntegrationTests\Fixtures;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test that verifies external HTTP requests are properly blocked in the e2e environment.
 * This ensures tests run faster and more deterministically without external API dependencies.
 */
class ExternalRequestBlockingTest extends TestCase {
	
	/**
	 * Test that the blocking mu-plugin exists in the environment.
	 */
	public function test_blocking_mu_plugin_exists_in_environment(): void {
		// Start an environment
		$output = qit( [ 'env:up', '--plugin=woocommerce' ] );
		
		// Extract environment ID
		preg_match( '/Environment ready: (qitenv[a-z0-9]+)/', $output, $matches );
		$this->assertNotEmpty( $matches, 'Should find environment ID in output' );
		
		$env_id = $matches[1];
		$php_container = 'qit_env_php_' . $env_id;
		
		try {
			// Check if the blocking mu-plugin exists
			exec( "docker exec $php_container ls /var/www/html/wp-content/mu-plugins/ 2>&1", $mu_plugins_output, $return_code );
			
			if ( $return_code === 0 ) {
				$mu_plugins = implode( "\n", $mu_plugins_output );
				
				// Check for our blocking plugins
				$this->assertStringContainsString( 
					'disable-external-requests.php', 
					$mu_plugins, 
					'The disable-external-requests.php mu-plugin should be present in the environment' 
				);
				
				$this->assertStringContainsString( 
					'network-debug.php', 
					$mu_plugins, 
					'The network-debug.php mu-plugin should be present in the environment' 
				);
				
				echo "\n=== MU-Plugins in Environment ===\n";
				echo $mu_plugins . "\n";
				echo "=================================\n";
			} else {
				$this->fail( 'Could not list mu-plugins: ' . implode( "\n", $mu_plugins_output ) );
			}
		} finally {
			// Clean up environment using docker directly to avoid env:down issues
			exec( "docker stop $php_container qit_env_nginx_{$env_id} qit_env_db_{$env_id} 2>/dev/null" );
			exec( "docker rm $php_container qit_env_nginx_{$env_id} qit_env_db_{$env_id} 2>/dev/null" );
		}
	}
	
	/**
	 * Test that external requests are properly blocked.
	 * This test runs a simple e2e test and then checks the network log to verify
	 * that WordPress update/feed requests are blocked.
	 */
	public function test_external_requests_are_blocked(): void {
		// Run a simple test that will trigger some WordPress initialization
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . __DIR__ . '/../../fixtures/test-packages/regular-test-package-one'
		] );
		
		// Check that the test ran successfully
		$this->assertStringContainsString( 'Environment ready', $output, 'Environment should start successfully' );
		
		// Parse the environment ID from output
		preg_match( '/Environment ready: (qitenv[a-z0-9]+)/', $output, $matches );
		if ( ! empty( $matches[1] ) ) {
			$env_id = $matches[1];
			
			// Look for the network log in the temporary environment
			$config_dir = getenv( 'QIT_CONFIG_DIR' ) ?: getenv( 'QIT_HOME' );
			if ( ! $config_dir ) {
				// Try to find it from the output path patterns
				$config_dir = __DIR__ . '/../../tmp/tmp_qit_config-qit_custom_tests_*';
				$dirs = glob( $config_dir );
				if ( ! empty( $dirs ) ) {
					$config_dir = $dirs[0];
				}
			}
			
			if ( $config_dir ) {
				$network_log_pattern = $config_dir . '/temporary-envs/*' . $env_id . '*/network.log';
				$network_logs = glob( $network_log_pattern );
				
				if ( ! empty( $network_logs ) ) {
					$network_log_content = file_get_contents( $network_logs[0] );
					
					// Extract all external request URLs
					preg_match_all( '/REQUEST: (https?:\/\/[^\s]+)/', $network_log_content, $url_matches );
					$external_urls = array_filter( $url_matches[1] ?? [], function( $url ) {
						return ! str_starts_with( $url, 'http://localhost' );
					} );
					
					// Check that WordPress feeds are NOT present
					$blocked_patterns = [
						'wordpress.org/news/feed',
						'planet.wordpress.org/feed',
						'api.wordpress.org/events/',
					];
					
					$found_blocked = [];
					foreach ( $blocked_patterns as $pattern ) {
						foreach ( $external_urls as $url ) {
							if ( strpos( $url, $pattern ) !== false ) {
								$found_blocked[] = $pattern;
							}
						}
					}
					
					$this->assertEmpty( 
						$found_blocked, 
						'These URLs should be blocked but were found: ' . implode( ', ', $found_blocked )
					);
					
					// Count external requests - should be minimal (allowing for WooCommerce-specific requests)
					$external_request_count = count( $external_urls );
					
					echo "\n=== External Request Blocking Test Results ===\n";
					echo "Total external requests: $external_request_count\n";
					if ( $external_request_count > 0 ) {
						echo "External requests found (some may be from WooCommerce):\n";
						foreach ( array_unique( $external_urls ) as $url ) {
							// Truncate long URLs for readability
							$display_url = parse_url( $url, PHP_URL_HOST ) . parse_url( $url, PHP_URL_PATH );
							if ( strlen( $display_url ) > 60 ) {
								$display_url = substr( $display_url, 0, 60 ) . '...';
							}
							echo "  - $display_url\n";
						}
					}
					echo "WordPress feed/event requests blocked: " . ( empty( $found_blocked ) ? '✓ Yes' : '✗ No' ) . "\n";
					echo "=== End Results ===\n";
				} else {
					$this->markTestSkipped( 'Network log not found - network logging may not be captured for this test type' );
				}
			} else {
				$this->markTestSkipped( 'Could not determine config directory' );
			}
		} else {
			$this->markTestIncomplete( 'Could not determine environment ID from output' );
		}
	}
}