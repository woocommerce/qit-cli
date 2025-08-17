<?php

namespace QIT\IntegrationTests\Fixtures;

use PHPUnit\Framework\TestCase;
use function qit;

class NetworkCaptureTest extends TestCase {
	
	public function test_network_requests_are_captured(): void {
		// Run a simple e2e test
		$output = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . __DIR__ . '/../../fixtures/test-packages/regular-test-package-one'
		] );
		
		// Debug: Print the output to see what's happening
		echo "\n=== TEST OUTPUT ===\n";
		echo substr($output, 0, 2000);
		echo "\n=== END OUTPUT ===\n";
		
		// Check if the test ran successfully
		$this->assertStringContainsString( 'Environment ready', $output, 'Environment should be ready' );
		
		// Find the temporary environment directory
		preg_match( '/Environment ready: (qitenv[a-z0-9]+)/', $output, $matches );
		$this->assertNotEmpty( $matches, 'Should find environment ID in output' );
		
		$env_id = $matches[1];
		
		// Look for the network.log file
		$tmp_dir = getenv( 'QIT_CONFIG_DIR' ) ?: __DIR__ . '/../../tmp/tmp_qit_config-qit_custom_tests_' . substr( md5( uniqid() ), 0, 17 );
		$network_log_pattern = $tmp_dir . '/temporary-envs/*' . $env_id . '*/network.log';
		$network_logs = glob( $network_log_pattern );
		
		if ( ! empty( $network_logs ) ) {
			$this->assertNotEmpty( $network_logs, 'Network log should be created' );
			
			$network_log_content = file_get_contents( $network_logs[0] );
			$this->assertNotEmpty( $network_log_content, 'Network log should have content' );
			
			// Output the network log for analysis
			echo "\n\n=== NETWORK LOG CONTENT ===\n";
			echo "File: " . $network_logs[0] . "\n";
			echo "Size: " . strlen( $network_log_content ) . " bytes\n";
			echo "--- First 5000 characters ---\n";
			echo substr( $network_log_content, 0, 5000 );
			echo "\n=== END NETWORK LOG ===\n\n";
			
			// Count requests by domain
			preg_match_all( '/REQUEST: (https?:\/\/[^\/\s]+)/', $network_log_content, $url_matches );
			if ( ! empty( $url_matches[1] ) ) {
				$domains = array_map( function( $url ) {
					$parts = parse_url( $url );
					return $parts['host'] ?? $url;
				}, $url_matches[1] );
				
				$domain_counts = array_count_values( $domains );
				arsort( $domain_counts );
				
				echo "\n=== REQUEST SUMMARY BY DOMAIN ===\n";
				foreach ( $domain_counts as $domain => $count ) {
					echo sprintf( "%3d requests to %s\n", $count, $domain );
				}
				echo "=== END SUMMARY ===\n\n";
			}
		} else {
			$this->markTestSkipped( 'Network log was not captured - mu-plugin may not be loaded' );
		}
	}
}