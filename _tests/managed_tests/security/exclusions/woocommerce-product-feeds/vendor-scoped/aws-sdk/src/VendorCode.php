<?php
/**
 * This file simulates vendor code with security issues that should be ignored
 * because it's in the vendor-scoped directory.
 * 
 * Note: For vendor dependencies, we rely on public CVSS databases to check for
 * known vulnerabilities instead of scanning the code directly.
 */

namespace AWS\SDK;

class VendorCode {
	// This AWS access key ID should be ignored by Gitleaks
	private $aws_access_key_id = 'AKIA234567ABCDEF2345';
	
	public function dangerousFileInclusion() {
		// This file inclusion should be ignored by Semgrep
		if ( isset( $_GET['file'] ) ) {
			include $_GET['file'];
			require_once $_GET['file'];
		}
	}
	
	public function xssVulnerability() {
		// This XSS should be ignored
		if ( isset( $_GET['data'] ) ) {
			echo "Vendor data: " . $_GET['data'];
		}
	}
	
	public function sqlInjection() {
		global $wpdb;
		// This SQL injection should be ignored
		if ( isset( $_GET['id'] ) ) {
			$id = $_GET['id'];
			$query = "SELECT * FROM {$wpdb->posts} WHERE ID = $id";
			$wpdb->query( $query );
		}
	}
	
	public function unsafeRedirect() {
		// This unsafe redirect should be ignored
		if ( isset( $_GET['url'] ) ) {
			wp_redirect( $_GET['url'] );
		}
	}
}