<?php

namespace integration\tests\PreCommand;

/**
 * Integration tests for TinyPreCommand end-to-end scenarios.
 * 
 * These tests use qit_precommand() to test the complete CLI → TinyPreCommand flow
 * without spinning up Docker or downloading anything.
 */
class TinyPreCommandIntegrationTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Test env-only command (env:up) with CLI args and qit.json configuration.
	 */
	public function test_env_only_command(): void {
		$qitJson = <<<'JSON'
{
  "environments": {
    "default": {
      "php": "7.4",
      "wp": "5.9",
      "woo": "stable"
    }
  }
}
JSON;

		$output = \qit_precommand([
			'env:up',
			'--php', '8.1',
			'--wp', '6.0',
			'--json'
		], $qitJson);

		$result = json_decode($output, true);
		$this->assertIsArray($result, 'Output should be valid JSON');
		$this->assertArrayHasKey('resolved_config', $result, 'Should contain resolved_config');
		$this->assertArrayHasKey('env_config', $result, 'Should contain env_config');

		// Verify that CLI args override config values
		$envConfig = $result['env_config'];
		$this->assertEquals('8.1', $envConfig['php'] ?? null, 'CLI --php should override config');
		$this->assertEquals('6.0', $envConfig['wp'] ?? null, 'CLI --wp should override config');
	}

	/**
	 * Test profile-only command (run:security) with test-specific configuration.
	 */
	public function test_profile_only_command(): void {
		$qitJson = <<<'JSON'
{
  "test_types": {
    "security": {
      "default": {
        "php": "8.0",
        "wp": "latest",
        "phpstan_level": 5
      }
    }
  }
}
JSON;

		$output = \qit_precommand([
			'run:security',
			'woocommerce',
			'--phpstan_level', '8',
			'--json'
		], $qitJson);

		$result = json_decode($output, true);
		$this->assertIsArray($result, 'Output should be valid JSON');
		$this->assertArrayHasKey('resolved_config', $result, 'Should contain resolved_config');
		$this->assertArrayHasKey('test_config', $result, 'Should contain test_config');

		// Verify that CLI args override test profile config
		$testConfig = $result['test_config'];
		if (isset($testConfig['phpstan_level'])) {
			$this->assertEquals(8, $testConfig['phpstan_level'], 'CLI --phpstan_level should override config and be integer');
		}
	}

	/**
	 * Test combined command (run:e2e) that uses both environment and test configuration.
	 */
	public function test_combined_command(): void {
		$qitJson = <<<'JSON'
{
  "environments": {
    "default": {
      "php": "7.4",
      "wp": "5.9"
    }
  },
  "test_types": {
    "e2e": {
      "default": {
        "php": "8.0",
        "wp": "6.0",
        "plugins": ["woocommerce"]
      }
    }
  }
}
JSON;

		$output = \qit_precommand([
			'run:e2e',
			'test-plugin',
			'--php', '8.1',
			'--plugin', 'jetpack',
			'--json'
		], $qitJson);

		$result = json_decode($output, true);
		$this->assertIsArray($result, 'Output should be valid JSON');
		$this->assertArrayHasKey('resolved_config', $result, 'Should contain resolved_config');

		// Verify that CLI args have highest precedence
		$testConfig = $result['test_config'] ?? [];
		$this->assertEquals('8.1', $testConfig['php'] ?? null, 'CLI --php should override all config');
		
		// Verify array merging for plugins
		if (isset($testConfig['plugins'])) {
			$pluginSlugs = array_column($testConfig['plugins'], 'slug');
			$this->assertContains('jetpack', $pluginSlugs, 'CLI --plugin should be included');
		}
	}

	/**
	 * Test early return functionality with QIT_SELF_TEST=precommand.
	 */
	public function test_early_return(): void {
		$qitJson = <<<'JSON'
{
  "environments": {
    "default": {
      "php": "8.0",
      "wp": "6.0"
    }
  }
}
JSON;

		// The qit_precommand() helper automatically sets QIT_SELF_TEST=precommand
		$output = \qit_precommand([
			'env:up',
			'--php', '8.1',
			'--json'
		], $qitJson);

		$result = json_decode($output, true);
		$this->assertIsArray($result, 'Early return should produce valid JSON');
		$this->assertArrayHasKey('resolved_config', $result, 'Should contain resolved_config');
		$this->assertArrayHasKey('env_config', $result, 'Should contain env_config');
		$this->assertArrayHasKey('test_config', $result, 'Should contain test_config');

		// Verify the structure matches what TinyPreCommand::handleEarlyReturn() produces
		$this->assertIsArray($result['resolved_config'], 'resolved_config should be an array/object');
		$this->assertIsArray($result['env_config'], 'env_config should be an array');
		$this->assertIsArray($result['test_config'], 'test_config should be an array');
	}

	/**
	 * Test malformed config error path - should handle gracefully.
	 */
	public function test_malformed_config_error_path(): void {
		// Invalid JSON that should cause parsing to fail
		$invalidJson = '{ "environments": { "default": { "php": "8.0" } '; // Missing closing braces

		// This should not throw an exception, but handle the error gracefully
		$output = \qit_precommand([
			'env:up',
			'--php', '8.1',
			'--json'
		], $invalidJson, 0); // Expect success exit code

		$result = json_decode($output, true);
		$this->assertIsArray($result, 'Should handle malformed config gracefully');
		
		// Even with malformed config, CLI args should still work
		$envConfig = $result['env_config'] ?? [];
		$this->assertEquals('8.1', $envConfig['php'] ?? null, 'CLI args should work even with malformed config');
	}
}