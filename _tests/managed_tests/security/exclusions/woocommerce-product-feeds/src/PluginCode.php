<?php
/**
 * This file is in the plugin's src directory and should be scanned.
 * Any security issues here should be flagged.
 */

namespace MyPlugin;

class PluginCode {
	// This AWS access key ID should be flagged by Gitleaks
	private $aws_access_key_id = 'AKIA234567ABCDEF2345';
	
	public function processData() {
		// Regular plugin code
		return true;
	}
}