<?php
/**
 * Plugin Name: Test Valid Plugin
 * Plugin URI: https://example.com/test-valid-plugin
 * Description: A valid plugin fixture for testing artifact validation
 * Version: 1.0.0
 * Author: QIT Tests
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined("ABSPATH")) {
    exit;
}

// Simple plugin functionality
function test_valid_plugin_init() {
    // Plugin initialization code
}
add_action("init", "test_valid_plugin_init");
