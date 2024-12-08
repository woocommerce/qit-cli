<?php

// This script creates a new scenario: "scenario-additional-plugin-multiple-test-tags"
// Run it from the parent directory of "rune2e-scenarios", similar to the previous example.

$scenario_dir = __DIR__ . '/rune2e-scenarios/scenario-additional-plugin-multiple-test-tags';

// Create scenario directory if it doesn't exist.
if (!file_exists($scenario_dir)) {
	if (!mkdir($scenario_dir, 0755, true)) {
		fwrite(STDERR, "Failed to create directory: $scenario_dir\n");
		exit(1);
	}
}

// Create qit.yml with multiple plugins.
$qit_yml = <<<YML
plugins:
  woocommerce-amazon-s3-storage:
    source: ./woocommerce-amazon-s3-storage
    test_tags:
      - default
  woocommerce-progressive-discounts:
    source: ./woocommerce-progressive-discounts
    test_tags:
      - already-defined
YML;

if (file_put_contents($scenario_dir . '/qit.yml', $qit_yml) === false) {
	fwrite(STDERR, "Failed to create qit.yml in $scenario_dir\n");
	exit(1);
}

// Create the SUT plugin directory.
$plugin_dir_sut = $scenario_dir . '/woocommerce-amazon-s3-storage';
if (!file_exists($plugin_dir_sut)) {
	if (!mkdir($plugin_dir_sut, 0755, true)) {
		fwrite(STDERR, "Failed to create directory: $plugin_dir_sut\n");
		exit(1);
	}
}

$plugin_file_sut = <<<PHP
<?php
/*
Plugin Name: WooCommerce Amazon S3 Storage
Description: A dummy plugin to test multiple test tags scenario for SUT.
Version: 1.0.0
Author: QIT Test
*/
PHP;

if (file_put_contents($plugin_dir_sut . '/woocommerce-amazon-s3-storage.php', $plugin_file_sut) === false) {
	fwrite(STDERR, "Failed to create plugin file in $plugin_dir_sut\n");
	exit(1);
}

// Create a tests directory and a dummy test file.
$tests_dir_sut = $plugin_dir_sut . '/tests';
if (!file_exists($tests_dir_sut)) {
	mkdir($tests_dir_sut, 0755, true);
	file_put_contents($tests_dir_sut . '/example.spec.js', '// Example SUT E2E test file');
}

// Create the additional plugin directory.
$plugin_dir_additional = $scenario_dir . '/woocommerce-progressive-discounts';
if (!file_exists($plugin_dir_additional)) {
	if (!mkdir($plugin_dir_additional, 0755, true)) {
		fwrite(STDERR, "Failed to create directory: $plugin_dir_additional\n");
		exit(1);
	}
}

$plugin_file_additional = <<<PHP
<?php
/*
Plugin Name: WooCommerce Progressive Discounts
Description: A dummy plugin to test multiple test tags scenario for additional plugin.
Version: 1.0.0
Author: QIT Test
*/
PHP;

if (file_put_contents($plugin_dir_additional . '/woocommerce-progressive-discounts.php', $plugin_file_additional) === false) {
	fwrite(STDERR, "Failed to create plugin file in $plugin_dir_additional\n");
	exit(1);
}

// Create a tests directory and a dummy test file for the additional plugin.
$tests_dir_additional = $plugin_dir_additional . '/tests';
if (!file_exists($tests_dir_additional)) {
	mkdir($tests_dir_additional, 0755, true);
	file_put_contents($tests_dir_additional . '/example.spec.js', '// Example Additional Plugin E2E test file');
}

echo "Scenario 'scenario-additional-plugin-multiple-test-tags' created successfully.\n";
