<?php

namespace integration\tests\PreCommand;

use QIT\IntegrationTests\Traits\ScaffoldHelpers;

/**
 * Tests the precedence rules for configuration settings in the QIT CLI:
 * 1. CLI flags have highest priority
 * 2. qit.json configuration has medium priority
 * 3. Default values have lowest priority
 */
class CommandInputPriorityTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;

	/**
	 * Test that CLI flags override qit.json configuration.
	 */
	public function test_cli_overrides_config() {
		// Create a qit.json with specific environment configuration
		$qitJson = <<<'JSON'
{
  "environments": {
    "default": {
      "wp": "6.0",
      "php": "7.4",
      "woo": "7.0"
    }
  }
}
JSON;

		// CLI flags should override qit.json values
		$output = qit_precommand( [
			'env:up',
			'--wp=6.1',
			'--php=8.0',
			'--woo=7.1',
			'--json',
		],
			$qitJson
		);

		$env = json_decode( $output, true );
		$this->assertIsArray( $env, "JSON decoding failed" );
		$this->assertSame( '6.1', $env['env_info']['wp'], "WP version should be CLI override '6.1'." );
		$this->assertSame( '8.0', $env['env_info']['php'], "PHP version should be CLI override '8.0'." );
		$this->assertSame( '7.1', $env['env_info']['woo'] ?? null, "WooCommerce version should be CLI override '7.1'." );
	}

	/**
	 * Test that qit.json configuration overrides defaults when no CLI flags are provided.
	 */
	public function test_config_overrides_defaults() {
		// Create a qit.json with specific environment configuration
		$qitJson = <<<'JSON'
{
  "environments": {
    "default": {
      "wp": "5.9",
      "php": "8.1",
      "woo": "6.9"
    }
  }
}
JSON;

		// No CLI flags, so qit.json values should be used
		$output = qit_precommand( [
			'env:up',
			'--json',
		],
			$qitJson
		);

		$env = json_decode( $output, true );
		$this->assertIsArray( $env, "JSON decoding failed" );
		$this->assertSame( '5.9', $env['env_info']['wp'], "WP version should be from qit.json '5.9'." );
		$this->assertSame( '8.1', $env['env_info']['php'], "PHP version should be from qit.json '8.1'." );
		$this->assertSame( '6.9', $env['env_info']['woo'] ?? null, "WooCommerce version should be from qit.json '6.9'." );
	}

	/**
	 * Test that defaults are used when no CLI flags or qit.json configuration is provided.
	 */
	public function test_defaults_only() {
		// No qit.json, no CLI flags, so default values should be used
		$output = qit_precommand( [
			'env:up',
			'--json',
		],
			null
		);

		$env = json_decode( $output, true );
		$this->assertIsArray( $env, "JSON decoding failed" );
		$this->assertSame( 'stable', $env['env_info']['wp'], "WP version should be default 'stable'." );
		$this->assertSame( '8.2', $env['env_info']['php'], "PHP version should be the schema default '8.2'." );
		$this->assertSame(
			'',
			$env['env_info']['woo'],
			'WooCommerce should not be installed unless requested.'
		);
		$this->assertNotContains(
			'woocommerce',
			array_column( $env['env_info']['plugins'], 'slug' ),
			'WooCommerce should not be installed if not requested.'
		);
	}
}
