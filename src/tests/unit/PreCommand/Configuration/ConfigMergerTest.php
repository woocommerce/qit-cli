<?php

namespace QIT_CLI\Tests\Unit\PreCommand\Configuration;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Configuration\ConfigMerger;

class ConfigMergerTest extends TestCase {

	private ConfigMerger $merger;

	protected function setUp(): void {
		$this->merger = new ConfigMerger();
	}

	public function test_cli_params_override_config_values(): void {
		$cli_params = [ 'php' => '8.1' ];
		$config_values = [ 'php' => '7.4' ];
		$command_defaults = [ 'php' => '8.0' ];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		$this->assertEquals( '8.1', $result['php'], 'CLI params should override config values' );
	}

	public function test_config_values_override_command_defaults(): void {
		$cli_params = [];
		$config_values = [ 'php' => '7.4' ];
		$command_defaults = [ 'php' => '8.0' ];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		$this->assertEquals( '7.4', $result['php'], 'Config values should override command defaults' );
	}

	public function test_command_defaults_used_when_no_overrides(): void {
		$cli_params = [];
		$config_values = [];
		$command_defaults = [ 'php' => '8.0' ];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		$this->assertEquals( '8.0', $result['php'], 'Command defaults should be used when no overrides' );
	}

	public function test_null_values_are_filtered_out(): void {
		$cli_params = [ 'nonexistent' => null ];
		$config_values = [ 'wp' => null ];
		$command_defaults = [ 'php' => '8.0', 'wp' => '6.0', 'woo' => null ];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		// 'php' should be present because it has a non-null default and no null override
		$this->assertEquals( '8.0', $result['php'], 'Non-null defaults should be preserved' );
		// 'wp' should be present because it has a non-null default, even though config is null
		$this->assertEquals( '6.0', $result['wp'], 'Non-null defaults should override null config values' );
		// 'woo' should be filtered out because it's null in defaults
		$this->assertArrayNotHasKey( 'woo', $result, 'Null command defaults should be filtered out' );
		// 'nonexistent' should not be present because it's null and not in defaults
		$this->assertArrayNotHasKey( 'nonexistent', $result, 'Null CLI params not in defaults should be filtered out' );
	}

	public function test_null_cli_params_dont_override_config_values(): void {
		$cli_params = [ 'php' => null ];
		$config_values = [ 'php' => '7.4' ];
		$command_defaults = [ 'php' => '8.0' ];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		$this->assertEquals( '7.4', $result['php'], 'Null CLI params should not override config values' );
	}

	public function test_null_config_values_dont_override_defaults(): void {
		$cli_params = [];
		$config_values = [ 'php' => null ];
		$command_defaults = [ 'php' => '8.0' ];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		$this->assertEquals( '8.0', $result['php'], 'Null config values should not override defaults' );
	}

	public function test_complex_precedence_scenario(): void {
		$cli_params = [ 
			'php' => '8.1',      // CLI override
			'wp' => null,        // Null CLI (should not override)
			'woo' => 'latest'    // CLI override
		];
		$config_values = [ 
			'php' => '7.4',      // Should be overridden by CLI
			'wp' => '6.0',       // Should be used (CLI is null)
			'object_cache' => 'redis'  // Should be used (no CLI override)
		];
		$command_defaults = [ 
			'php' => '8.0',           // Should be overridden
			'wp' => '5.9',            // Should be overridden by config
			'woo' => 'stable',        // Should be overridden by CLI
			'object_cache' => 'none', // Should be overridden by config
			'theme' => 'twentytwentythree'  // Should be used (no overrides)
		];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		$this->assertEquals( '8.1', $result['php'], 'CLI should override config and defaults' );
		$this->assertEquals( '6.0', $result['wp'], 'Config should override defaults when CLI is null' );
		$this->assertEquals( 'latest', $result['woo'], 'CLI should override config and defaults' );
		$this->assertEquals( 'redis', $result['object_cache'], 'Config should override defaults' );
		$this->assertEquals( 'twentytwentythree', $result['theme'], 'Defaults should be used when no overrides' );
	}

	public function test_empty_arrays_return_empty_result(): void {
		$result = $this->merger->merge( [], [], [] );

		$this->assertEmpty( $result, 'Empty arrays should return empty result' );
	}

	public function test_array_values_are_preserved(): void {
		$cli_params = [ 'plugins' => [ 'woocommerce', 'jetpack' ] ];
		$config_values = [ 'themes' => [ 'storefront' ] ];
		$command_defaults = [ 'volumes' => [ '/tmp:/tmp' ] ];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		$this->assertEquals( [ 'woocommerce', 'jetpack' ], $result['plugins'], 'Array CLI params should be preserved' );
		$this->assertEquals( [ 'storefront' ], $result['themes'], 'Array config values should be preserved' );
		$this->assertEquals( [ '/tmp:/tmp' ], $result['volumes'], 'Array defaults should be preserved' );
	}

	public function test_boolean_values_are_preserved(): void {
		$cli_params = [ 'debug' => true ];
		$config_values = [ 'verbose' => false ];
		$command_defaults = [ 'quiet' => false ];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		$this->assertTrue( $result['debug'], 'Boolean CLI params should be preserved' );
		$this->assertFalse( $result['verbose'], 'Boolean config values should be preserved' );
		$this->assertFalse( $result['quiet'], 'Boolean defaults should be preserved' );
	}

	public function test_zero_and_empty_string_values_are_preserved(): void {
		$cli_params = [ 'timeout' => 0 ];
		$config_values = [ 'prefix' => '' ];
		$command_defaults = [ 'retries' => 0 ];

		$result = $this->merger->merge( $cli_params, $config_values, $command_defaults );

		$this->assertEquals( 0, $result['timeout'], 'Zero CLI params should be preserved' );
		$this->assertEquals( '', $result['prefix'], 'Empty string config values should be preserved' );
		$this->assertEquals( 0, $result['retries'], 'Zero defaults should be preserved' );
	}
}