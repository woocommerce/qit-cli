<?php

namespace integration\tests\PreCommand;

use QIT\IntegrationTests\Traits\ScaffoldHelpers;

/**
 * Tests for the test package resolution functionality.
 * These tests verify that test packages from both CLI and configuration are properly merged.
 */
class TestPackageResolutionTest extends \PHPUnit\Framework\TestCase {
    use ScaffoldHelpers;

    /**
     * Test that test packages can be specified via CLI.
     */
    public function test_cli_test_packages() {
        $output = qit_precommand([
            'run:e2e',
            'woocommerce-amazon-s3-storage',
            $this->scaffold_test(),
            '--test-package',
            'woocommerce/checkout:stable',
            '--json',
        ]);

        $result = json_decode($output, true);
        $this->assertIsArray($result, "JSON decoding failed");
        $this->assertArrayHasKey('test_packages', $result);
        $this->assertIsArray($result['test_packages']);
        
        // Find the test package by slug
        $found = false;
        foreach ($result['test_packages'] as $package) {
            if ($package['slug'] === 'woocommerce/checkout') {
                $found = true;
                $this->assertEquals('stable', $package['version']);
                break;
            }
        }
        $this->assertTrue($found, "Test package 'woocommerce/checkout' not found in result");
    }

    /**
     * Test that multiple test packages can be specified via CLI.
     */
    public function test_multiple_cli_test_packages() {
        $output = qit_precommand([
            'run:e2e',
            'woocommerce-amazon-s3-storage',
            $this->scaffold_test(),
            '--test-package',
            'woocommerce/checkout:stable',
            '--test-package',
            'woocommerce/blocks:latest',
            '--json',
        ]);

        $result = json_decode($output, true);
        $this->assertIsArray($result, "JSON decoding failed");
        $this->assertArrayHasKey('test_packages', $result);
        $this->assertIsArray($result['test_packages']);
        
        // Find the test packages by slug
        $found_checkout = false;
        $found_blocks = false;
        foreach ($result['test_packages'] as $package) {
            if ($package['slug'] === 'woocommerce/checkout') {
                $found_checkout = true;
                $this->assertEquals('stable', $package['version']);
            } elseif ($package['slug'] === 'woocommerce/blocks') {
                $found_blocks = true;
                $this->assertEquals('latest', $package['version']);
            }
        }
        $this->assertTrue($found_checkout, "Test package 'woocommerce/checkout' not found in result");
        $this->assertTrue($found_blocks, "Test package 'woocommerce/blocks' not found in result");
    }

    /**
     * Test that test packages from configuration and CLI are properly merged.
     */
    public function test_merged_test_packages() {
        // Create a qit.json with test packages configuration
        $qitJson = <<<'JSON'
{
  "test_types": {
    "e2e": {
      "default": {
        "test_packages": ["woocommerce/checkout:v1.0", "woocommerce/admin:stable"]
      }
    }
  }
}
JSON;

        $output = qit_precommand([
            'run:e2e',
            'woocommerce-amazon-s3-storage',
            $this->scaffold_test(),
            '--test-package',
            'woocommerce/checkout:stable',  // This should override the one from config
            '--test-package',
            'woocommerce/blocks:latest',    // This is a new one
            '--json',
        ], $qitJson);

        $result = json_decode($output, true);
        $this->assertIsArray($result, "JSON decoding failed");
        $this->assertArrayHasKey('test_packages', $result);
        $this->assertIsArray($result['test_packages']);
        
        // Find the test packages by slug
        $found_checkout = false;
        $found_blocks = false;
        $found_admin = false;
        foreach ($result['test_packages'] as $package) {
            if ($package['slug'] === 'woocommerce/checkout') {
                $found_checkout = true;
                // CLI should override config
                $this->assertEquals('stable', $package['version']);
            } elseif ($package['slug'] === 'woocommerce/blocks') {
                $found_blocks = true;
                $this->assertEquals('latest', $package['version']);
            } elseif ($package['slug'] === 'woocommerce/admin') {
                $found_admin = true;
                $this->assertEquals('stable', $package['version']);
            }
        }
        $this->assertTrue($found_checkout, "Test package 'woocommerce/checkout' not found in result");
        $this->assertTrue($found_blocks, "Test package 'woocommerce/blocks' not found in result");
        $this->assertTrue($found_admin, "Test package 'woocommerce/admin' not found in result");
    }

    /**
     * Test that test packages with default version are properly handled.
     */
    public function test_default_version() {
        $output = qit_precommand([
            'run:e2e',
            'woocommerce-amazon-s3-storage',
            $this->scaffold_test(),
            '--test-package',
            'woocommerce/checkout',  // No version specified, should default to 'latest'
            '--json',
        ]);

        $result = json_decode($output, true);
        $this->assertIsArray($result, "JSON decoding failed");
        $this->assertArrayHasKey('test_packages', $result);
        $this->assertIsArray($result['test_packages']);
        
        // Find the test package by slug
        $found = false;
        foreach ($result['test_packages'] as $package) {
            if ($package['slug'] === 'woocommerce/checkout') {
                $found = true;
                $this->assertEquals('latest', $package['version']);
                break;
            }
        }
        $this->assertTrue($found, "Test package 'woocommerce/checkout' not found in result");
    }
}