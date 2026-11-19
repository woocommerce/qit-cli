<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Objects\PackageType;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;

/**
 * Critical test for TestPackageManifest value object.
 * Tests the anti-corruption layer that adapts JSON manifest formats.
 */
class TestPackageManifestTest extends TestCase {
	
	/**
	 * Test v2 package format (namespace/package in single field).
	 */
	public function test_v2_package_format(): void {
		$data = [
			'package' => 'woocommerce/checkout',
			'test' => [
				'phases' => [
					'globalSetup' => [],
					'setup' => [],
					'run' => ['echo "test"'],
					'teardown' => [],
					'globalTeardown' => []
				],
				'results' => []
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		
		$this->assertEquals( 'woocommerce/checkout', $manifest->get_package_id() );
		$this->assertEquals( 'woocommerce', $manifest->get_namespace() );
		$this->assertEquals( 'checkout', $manifest->get_package_name() );
	}
	
	/**
	 * Test v1 package format (separate namespace and package fields).
	 */
	public function test_v1_package_format(): void {
		$data = [
			'namespace' => 'woocommerce',
			'package' => 'checkout',
			'test' => [
				'phases' => [
					'globalSetup' => [],
					'setup' => [],
					'run' => ['phpunit'],
					'teardown' => [],
					'globalTeardown' => []
				],
				'results' => []
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		
		$this->assertEquals( 'woocommerce/checkout', $manifest->get_package_id() );
		$this->assertEquals( 'woocommerce', $manifest->get_namespace() );
		$this->assertEquals( 'checkout', $manifest->get_package_name() );
	}
	
	/**
	 * Test environment variable conversion to strings.
	 */
	public function test_env_var_stringification(): void {
		$data = [
			'package' => 'test/package',
			'test' => [
				'phases' => [
					'run' => ['test']
				],
				'results' => []
			],
			'envs' => [
				'DEBUG' => true,
				'VERBOSE' => false,
				'MAX_RETRIES' => 5,
				'TIMEOUT' => 30.5,
				'API_KEY' => 'secret123',
				'ZERO' => 0
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		$env_vars = $manifest->get_env();
		
		// All values should be strings
		$this->assertSame( 'true', $env_vars['DEBUG'] );
		$this->assertSame( 'false', $env_vars['VERBOSE'] );
		$this->assertSame( '5', $env_vars['MAX_RETRIES'] );
		$this->assertSame( '30.5', $env_vars['TIMEOUT'] );
		$this->assertSame( 'secret123', $env_vars['API_KEY'] );
		$this->assertSame( '0', $env_vars['ZERO'] );
	}
	
	/**
	 * Test container directory name generation.
	 */
	public function test_container_directory_name(): void {
		$data = [
			'namespace' => 'WooCommerce',
			'package' => 'My.Package_Name',
			'test' => [
				'phases' => [ 'run' => ['test'] ],
				'results' => []
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		
		// Should lowercase and preserve valid characters
		$this->assertEquals( 'woocommerce-my.package_name', $manifest->get_container_directory_name() );
		$this->assertEquals( 'woocommerce-my.package_name-1.0.0', $manifest->get_container_directory_name( '1.0.0' ) );
		$this->assertEquals( '/qit/packages/woocommerce-my.package_name', $manifest->get_container_path() );
	}
	
	/**
	 * Test subpackage detection.
	 */
	public function test_subpackage_detection(): void {
		// Parent package
		$parent_data = [
			'package' => 'woocommerce/checkout',
			'test' => [
				'phases' => [ 'run' => ['test'] ],
				'results' => []
			],
			'subpackages' => [
				'smoke' => [
					'name' => 'smoke',
					'test_type' => 'e2e'
				]
			]
		];
		
		$parent = new TestPackageManifest( $parent_data );
		$this->assertTrue( $parent->has_subpackages() );
		$this->assertFalse( $parent->is_subpackage() );
		$this->assertNotNull( $parent->get_subpackage( 'smoke' ) );
		
		// Subpackage
		$subpackage_data = [
			'package' => 'woocommerce/checkout',
			'parent_package' => 'woocommerce/checkout',
			'test' => [
				'phases' => [ 'run' => ['smoke test'] ],
				'results' => []
			]
		];
		
		$subpackage = new TestPackageManifest( $subpackage_data );
		$this->assertTrue( $subpackage->is_subpackage() );
		$this->assertEquals( 'woocommerce/checkout', $subpackage->get_parent_package() );
	}
	
	/**
	 * Build a parent manifest with subpackages for create_subpackage_manifest tests.
	 */
	private function get_parent_manifest_with_subpackages(): TestPackageManifest {
		return new TestPackageManifest( [
			'package' => 'woocommerce/e2e-suite',
			'description' => 'Parent test suite',
			'test_dir' => './tests',
			'test_type' => 'e2e',
			'test' => [
				'phases' => [
					'globalSetup' => ['./bootstrap/global-setup.sh'],
					'setup' => ['npm ci'],
					'run' => ['npx playwright test'],
					'teardown' => ['./cleanup.sh'],
					'globalTeardown' => ['./global-teardown.sh'],
				],
				'results' => ['ctrf-json' => './results/ctrf.json'],
			],
			'requires' => ['plugins' => ['woocommerce']],
			'mu_plugins' => ['./mu/helper.php'],
			'envs' => ['FOO' => 'bar'],
			'timeout' => 900,
			'retry' => ['times' => 1, 'delay' => 5],
			'subpackages' => [
				'woocommerce/checkout' => [
					'description' => 'Checkout tests only',
					'tags' => ['checkout'],
					'test' => ['phases' => ['run' => ['npx playwright test tests/checkout.spec.js']]],
				],
				'woocommerce/cart' => [
					'requires' => ['plugins' => ['woocommerce', 'woocommerce-gateway-stripe']],
					'test' => ['phases' => ['run' => ['npx playwright test tests/cart.spec.js']]],
				],
				'woocommerce/no-run-phase' => [
					'description' => 'Missing test.phases entirely',
				],
			],
		] );
	}

	/**
	 * Test create_subpackage_manifest inherits everything except the run phase.
	 */
	public function test_create_subpackage_manifest_inherits_parent_config(): void {
		$parent = $this->get_parent_manifest_with_subpackages();

		$subpackage = $parent->create_subpackage_manifest( 'woocommerce/checkout' );

		$this->assertEquals( 'woocommerce/checkout', $subpackage->get_package_id() );
		$this->assertEquals( 'woocommerce/e2e-suite', $subpackage->get_parent_package() );
		$this->assertTrue( $subpackage->is_subpackage() );

		// Only the run phase is overridden.
		$phases = $subpackage->get_phases();
		$this->assertEquals( ['npx playwright test tests/checkout.spec.js'], $phases['run'] );
		$this->assertEquals( ['./bootstrap/global-setup.sh'], $phases['globalSetup'] );
		$this->assertEquals( ['npm ci'], $phases['setup'] );
		$this->assertEquals( ['./cleanup.sh'], $phases['teardown'] );
		$this->assertEquals( ['./global-teardown.sh'], $phases['globalTeardown'] );

		// Everything else is inherited from the parent.
		$this->assertEquals( ['ctrf-json' => './results/ctrf.json'], $subpackage->get_test_results() );
		$this->assertEquals( ['plugins' => ['woocommerce']], $subpackage->get_requires() );
		$this->assertEquals( ['./mu/helper.php'], $subpackage->get_mu_plugins() );
		$this->assertEquals( ['FOO' => 'bar'], $subpackage->get_env() );
		$this->assertEquals( 900, $subpackage->get_timeout() );
		$this->assertEquals( ['times' => 1, 'delay' => 5], $subpackage->get_retry() );
		$this->assertEquals( './tests', $subpackage->get_test_dir() );
		$this->assertEquals( 'e2e', $subpackage->get_test_type() );

		// Metadata overrides from the subpackage config are applied.
		$this->assertEquals( 'Checkout tests only', $subpackage->get_description() );
		$this->assertEquals( ['checkout'], $subpackage->get_tags() );
	}

	/**
	 * Test create_subpackage_manifest merges subpackage requires overrides.
	 */
	public function test_create_subpackage_manifest_merges_requires_override(): void {
		$parent = $this->get_parent_manifest_with_subpackages();

		$subpackage = $parent->create_subpackage_manifest( 'woocommerce/cart' );

		$this->assertEquals(
			['plugins' => ['woocommerce', 'woocommerce-gateway-stripe']],
			$subpackage->get_requires()
		);
	}

	/**
	 * Test create_subpackage_manifest throws for an unknown subpackage ID.
	 */
	public function test_create_subpackage_manifest_throws_for_unknown_id(): void {
		$parent = $this->get_parent_manifest_with_subpackages();

		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( "Subpackage 'woocommerce/nonexistent' not found in package 'woocommerce/e2e-suite'" );

		$parent->create_subpackage_manifest( 'woocommerce/nonexistent' );
	}

	/**
	 * Test create_subpackage_manifest throws when the subpackage lacks a run phase.
	 */
	public function test_create_subpackage_manifest_throws_for_missing_run_phase(): void {
		$parent = $this->get_parent_manifest_with_subpackages();

		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( "Subpackage 'woocommerce/no-run-phase' must specify a 'run' phase" );

		$parent->create_subpackage_manifest( 'woocommerce/no-run-phase' );
	}

	/**
	 * Test utility package detection (no run phase).
	 */
	public function test_utility_package(): void {
		$data = [
			'package' => 'woocommerce/setup-utils',
			'test' => [
				'phases' => [
					'globalSetup' => ['composer install'],
					'setup' => ['npm install'],
					'run' => [], // Empty run phase = utility package
					'teardown' => [],
					'globalTeardown' => []
				],
				'results' => []
			]
		];
		
		$manifest = new TestPackageManifest( $data );
		$this->assertTrue( $manifest->is_utility_package() );
		$this->assertTrue( $manifest->has_global_setup() );
		$this->assertFalse( $manifest->has_global_teardown() );
	}
	
	/**
	 * Test static helper methods for container paths.
	 */
	public function test_static_container_helpers(): void {
		// With version
		$path = TestPackageManifest::create_container_directory_name( 'woocommerce/checkout:1.0.0' );
		$this->assertEquals( 'woocommerce-checkout-1.0.0', $path );
		
		// Without version
		$path = TestPackageManifest::create_container_directory_name( 'woocommerce/checkout' );
		$this->assertEquals( 'woocommerce-checkout', $path );
		
		// Full path
		$full_path = TestPackageManifest::create_container_path( 'woocommerce/checkout:2.0.0' );
		$this->assertEquals( '/qit/packages/woocommerce-checkout-2.0.0', $full_path );
	}
	
	/**
	 * Test missing required configuration.
	 */
	public function test_missing_test_configuration(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Manifest missing "test" configuration' );
		
		new TestPackageManifest( [
			'package' => 'woocommerce/checkout'
			// Missing 'test' key
		] );
	}
	
	/**
	 * Test normalized data flag (for caching).
	 */
	public function test_normalized_data_loading(): void {
		$normalized = [
			'_normalized' => true,
			'package_id' => 'woocommerce/checkout',
			'namespace' => 'woocommerce',
			'package_name' => 'checkout',
			'tags' => ['e2e', 'critical'],
			'test_type' => 'e2e',
			'test_dir' => './',
			'description' => 'Test package',
			'requires' => [],
			'phases' => [
				'globalSetup' => [],
				'setup' => [],
				'run' => ['test'],
				'teardown' => [],
				'globalTeardown' => []
			],
			'test_results' => [],
			'mu_plugins' => [],
			'env_vars' => [],
			'timeout' => 1800,
			'retry' => ['times' => 0, 'delay' => 0],
			'subpackages' => [],
			'parent_package' => null
		];
		
		$manifest = new TestPackageManifest( $normalized );
		
		// Should load directly without adaptation
		$this->assertEquals( 'woocommerce/checkout', $manifest->get_package_id() );
		$this->assertEquals( ['e2e', 'critical'], $manifest->get_tags() );
	}

	/**
	 * Test actions field parsing (capability name → file path).
	 */
	public function test_actions_field(): void {
		$data = [
			'package' => 'stripe/payments',
			'test' => [
				'phases' => [ 'globalSetup' => [] ],
				'results' => []
			],
			'actions' => [
				'makePurchase' => './flows/pay.ts',
				'refundOrder' => './flows/refund.ts',
			]
		];

		$manifest = new TestPackageManifest( $data );

		$this->assertEquals( [
			'makePurchase' => './flows/pay.ts',
			'refundOrder' => './flows/refund.ts',
		], $manifest->get_actions() );
	}

	/**
	 * Test actions returns empty array when not declared.
	 */
	public function test_actions_empty_when_not_declared(): void {
		$data = [
			'package' => 'test/package',
			'test' => [
				'phases' => [ 'run' => ['test'] ],
				'results' => []
			]
		];

		$manifest = new TestPackageManifest( $data );

		$this->assertEquals( [], $manifest->get_actions() );
	}

	/**
	 * External manifest data exercising every supported field.
	 *
	 * @return array<string, mixed>
	 */
	private function get_full_manifest_data(): array {
		return [
			'package' => 'woocommerce/e2e-suite',
			'tags' => ['e2e', 'critical'],
			'package_type' => 'test',
			'test_type' => 'e2e',
			'test_dir' => './tests',
			'description' => 'Full manifest',
			'requires' => [
				'plugins' => ['woocommerce'],
				'themes' => ['storefront'],
				'secrets' => ['API_KEY'],
				'network' => true,
				'tunnel' => true,
			],
			'test' => [
				'phases' => [
					'globalSetup' => ['./bootstrap/global-setup.sh'],
					'setup' => ['npm ci'],
					'run' => ['npx playwright test'],
					'teardown' => ['./cleanup.sh'],
					'globalTeardown' => ['./global-teardown.sh'],
				],
				'results' => ['ctrf-json' => './results/ctrf.json'],
			],
			'mu_plugins' => ['./mu/helper.php'],
			'envs' => [
				'FOO' => 'bar',
				'DEBUG' => true,
				'RETRIES' => 3,
			],
			'timeout' => 900,
			'retry' => ['times' => 2, 'delay' => 5],
			'subpackages' => [
				'woocommerce/checkout' => [
					'description' => 'Checkout tests only',
					'tags' => ['checkout'],
					'test' => ['phases' => ['run' => ['npx playwright test tests/checkout.spec.js']]],
				],
			],
			'actions' => [
				'makePurchase' => './flows/pay.ts',
				'refundOrder' => './flows/refund.ts',
			],
		];
	}

	/**
	 * The phases as stored internally, including the defaults applied for missing phases.
	 *
	 * @return array<string, array<string>>
	 */
	private function get_full_manifest_phases(): array {
		return [
			'globalSetup' => ['./bootstrap/global-setup.sh'],
			'setup' => ['npm ci'],
			'run' => ['npx playwright test'],
			'teardown' => ['./cleanup.sh'],
			'globalTeardown' => ['./global-teardown.sh'],
		];
	}

	/**
	 * Test to_array() exports every field of the normalized (cache) representation.
	 */
	public function test_to_array_exports_all_fields(): void {
		$manifest = new TestPackageManifest( $this->get_full_manifest_data() );

		$expected = [
			'_normalized' => true,
			'package_id' => 'woocommerce/e2e-suite',
			'namespace' => 'woocommerce',
			'package_name' => 'e2e-suite',
			'package_type' => 'test',
			'tags' => ['e2e', 'critical'],
			'test_type' => 'e2e',
			'test_dir' => './tests',
			'description' => 'Full manifest',
			'requires' => [
				'plugins' => ['woocommerce'],
				'themes' => ['storefront'],
				'secrets' => ['API_KEY'],
				'network' => true,
				'tunnel' => true,
			],
			'phases' => $this->get_full_manifest_phases(),
			'test_results' => ['ctrf-json' => './results/ctrf.json'],
			'mu_plugins' => ['./mu/helper.php'],
			// Env values are stringified.
			'env_vars' => [
				'FOO' => 'bar',
				'DEBUG' => 'true',
				'RETRIES' => '3',
			],
			'timeout' => 900,
			'retry' => ['times' => 2, 'delay' => 5],
			'subpackages' => [
				'woocommerce/checkout' => [
					'description' => 'Checkout tests only',
					'tags' => ['checkout'],
					'test' => ['phases' => ['run' => ['npx playwright test tests/checkout.spec.js']]],
				],
			],
			'parent_package' => null,
			'requires_network' => true,
			'requires_tunnel' => true,
			'actions' => [
				'makePurchase' => './flows/pay.ts',
				'refundOrder' => './flows/refund.ts',
			],
		];

		$this->assertEquals( $expected, $manifest->to_array() );
	}

	/**
	 * Test jsonSerialize() exports every field in the external manifest format.
	 */
	public function test_json_serialize_exports_all_fields(): void {
		$manifest = new TestPackageManifest( $this->get_full_manifest_data() );

		$expected = [
			'package' => 'woocommerce/e2e-suite',
			'tags' => ['e2e', 'critical'],
			'package_type' => 'test',
			'test_type' => 'e2e',
			'test_dir' => './tests',
			'description' => 'Full manifest',
			'requires' => [
				'plugins' => ['woocommerce'],
				'themes' => ['storefront'],
				'secrets' => ['API_KEY'],
				'network' => true,
				'tunnel' => true,
			],
			'test' => [
				'phases' => $this->get_full_manifest_phases(),
				'results' => ['ctrf-json' => './results/ctrf.json'],
			],
			'mu_plugins' => ['./mu/helper.php'],
			// Env values are stringified.
			'envs' => [
				'FOO' => 'bar',
				'DEBUG' => 'true',
				'RETRIES' => '3',
			],
			'timeout' => 900,
			'retry' => ['times' => 2, 'delay' => 5],
			'subpackages' => [
				'woocommerce/checkout' => [
					'description' => 'Checkout tests only',
					'tags' => ['checkout'],
					'test' => ['phases' => ['run' => ['npx playwright test tests/checkout.spec.js']]],
				],
			],
			'parent_package' => null,
			'actions' => [
				'makePurchase' => './flows/pay.ts',
				'refundOrder' => './flows/refund.ts',
			],
		];

		$this->assertEquals( $expected, $manifest->jsonSerialize() );
	}

	/**
	 * Test to_array() -> constructor round trip is lossless (cache write/read).
	 */
	public function test_to_array_round_trip_preserves_all_fields(): void {
		$original = new TestPackageManifest( $this->get_full_manifest_data() );

		$reloaded = new TestPackageManifest( $original->to_array() );

		$this->assertEquals( $original->to_array(), $reloaded->to_array() );
		$this->assertEquals( $original->jsonSerialize(), $reloaded->jsonSerialize() );
	}

	/**
	 * Test jsonSerialize() -> constructor round trip is lossless.
	 *
	 * Comparing the normalized state of both manifests catches any field that
	 * jsonSerialize() forgets to emit, since a dropped field would fall back to
	 * its default when the serialized output is re-adapted.
	 */
	public function test_json_serialize_round_trip_preserves_all_fields(): void {
		$original = new TestPackageManifest( $this->get_full_manifest_data() );

		$reloaded = new TestPackageManifest( $original->jsonSerialize() );

		$this->assertEquals( $original->jsonSerialize(), $reloaded->jsonSerialize() );
		$this->assertEquals( $original->to_array(), $reloaded->to_array() );
	}

	/**
	 * Test a round trip through an actual JSON string is lossless.
	 */
	public function test_json_encoded_round_trip_preserves_all_fields(): void {
		$original = new TestPackageManifest( $this->get_full_manifest_data() );

		$json = json_encode( $original->jsonSerialize() );
		$this->assertIsString( $json );

		$reloaded = new TestPackageManifest( json_decode( $json, true ) );

		$this->assertEquals( $original->to_array(), $reloaded->to_array() );
	}

	/**
	 * Test both serializations always emit every key, using defaults for fields
	 * that the external manifest did not declare.
	 */
	public function test_serialization_of_minimal_manifest_uses_defaults(): void {
		$manifest = new TestPackageManifest( [
			'package' => 'test/package',
			'test' => [
				'phases' => ['run' => ['npx playwright test']],
			],
		] );

		$expected_phases = [
			'globalSetup' => [],
			'globalTeardown' => [],
			'setup' => [],
			'run' => ['npx playwright test'],
			'teardown' => [],
		];

		$this->assertEquals( [
			'_normalized' => true,
			'package_id' => 'test/package',
			'namespace' => 'test',
			'package_name' => 'package',
			// Derived: has a run phase, so it is a test package.
			'package_type' => PackageType::TEST,
			'tags' => [],
			'test_type' => 'e2e',
			'test_dir' => './',
			'description' => '',
			'requires' => [],
			'phases' => $expected_phases,
			'test_results' => [],
			'mu_plugins' => [],
			'env_vars' => [],
			'timeout' => 1800,
			'retry' => ['times' => 0, 'delay' => 0],
			'subpackages' => [],
			'parent_package' => null,
			'requires_network' => false,
			'requires_tunnel' => false,
			'actions' => [],
		], $manifest->to_array() );

		$this->assertEquals( [
			'package' => 'test/package',
			'tags' => [],
			'package_type' => PackageType::TEST,
			'test_type' => 'e2e',
			'test_dir' => './',
			'description' => '',
			'requires' => [],
			'test' => [
				'phases' => $expected_phases,
				'results' => [],
			],
			'mu_plugins' => [],
			'envs' => [],
			'timeout' => 1800,
			'retry' => ['times' => 0, 'delay' => 0],
			'subpackages' => [],
			'parent_package' => null,
			'actions' => [],
		], $manifest->jsonSerialize() );
	}

	/**
	 * Test normalized data written before actions existed still loads (backwards compatibility).
	 */
	public function test_normalized_data_without_actions_defaults_to_empty(): void {
		$normalized = [
			'_normalized' => true,
			'package_id' => 'woocommerce/checkout',
			'namespace' => 'woocommerce',
			'package_name' => 'checkout',
			'tags' => [],
			'test_type' => 'e2e',
			'test_dir' => './',
			'description' => 'Test package',
			'requires' => [],
			'phases' => ['run' => ['test']],
			'test_results' => [],
			'mu_plugins' => [],
			'env_vars' => [],
			'timeout' => 1800,
			'retry' => ['times' => 0, 'delay' => 0],
			'subpackages' => [],
			'parent_package' => null,
			// No 'actions' key - cached before actions were supported.
		];

		$manifest = new TestPackageManifest( $normalized );

		$this->assertSame( [], $manifest->get_actions() );
		$this->assertSame( [], $manifest->to_array()['actions'] );
		$this->assertSame( [], $manifest->jsonSerialize()['actions'] );
	}

	/**
	 * Test a utility package (no run phase) serializes its derived package type.
	 */
	public function test_serialization_of_utility_package_type(): void {
		$manifest = new TestPackageManifest( [
			'package' => 'woocommerce/setup-utils',
			'test' => [
				'phases' => ['globalSetup' => ['composer install']],
			],
		] );

		$this->assertSame( PackageType::UTILITY, $manifest->to_array()['package_type'] );
		$this->assertSame( PackageType::UTILITY, $manifest->jsonSerialize()['package_type'] );

		// The derived type survives a round trip rather than being re-derived differently.
		$reloaded = new TestPackageManifest( $manifest->jsonSerialize() );
		$this->assertSame( PackageType::UTILITY, $reloaded->get_package_type() );
	}

	/**
	 * Test string network/tunnel requirements are normalized to booleans on export.
	 */
	public function test_serialization_normalizes_string_network_and_tunnel_flags(): void {
		$manifest = new TestPackageManifest( [
			'package' => 'test/package',
			'requires' => [
				'network' => 'true',
				'tunnel' => 'false',
			],
			'test' => [
				'phases' => ['run' => ['test']],
			],
		] );

		$normalized = $manifest->to_array();
		$this->assertTrue( $normalized['requires_network'] );
		$this->assertFalse( $normalized['requires_tunnel'] );

		// The raw requires values are exported untouched, and re-adapted on reload.
		$this->assertSame( 'true', $manifest->jsonSerialize()['requires']['network'] );
		$this->assertSame( 'false', $manifest->jsonSerialize()['requires']['tunnel'] );

		$reloaded = new TestPackageManifest( $manifest->jsonSerialize() );
		$this->assertTrue( $reloaded->requires_network() );
		$this->assertFalse( $reloaded->requires_tunnel() );
	}

	/**
	 * Test a synthesized subpackage serializes all of its inherited and overridden fields.
	 */
	public function test_serialization_of_synthesized_subpackage(): void {
		$parent = new TestPackageManifest( $this->get_full_manifest_data() );

		$subpackage = $parent->create_subpackage_manifest( 'woocommerce/checkout' );
		$serialized = $subpackage->jsonSerialize();

		// Subpackage identity, including the namespace/package name derived from the subpackage ID
		// rather than inherited from the parent.
		$this->assertSame( 'woocommerce/checkout', $serialized['package'] );
		$this->assertSame( 'woocommerce/e2e-suite', $serialized['parent_package'] );
		$this->assertSame( [], $serialized['subpackages'] );
		$this->assertSame( 'woocommerce', $subpackage->get_namespace() );
		$this->assertSame( 'checkout', $subpackage->get_package_name() );
		$this->assertSame( 'woocommerce-checkout', $subpackage->get_container_directory_name() );

		// Overridden fields.
		$this->assertEquals( ['npx playwright test tests/checkout.spec.js'], $serialized['test']['phases']['run'] );
		$this->assertSame( 'Checkout tests only', $serialized['description'] );
		$this->assertEquals( ['checkout'], $serialized['tags'] );

		// Everything else is inherited from the parent.
		$parent_serialized = $parent->jsonSerialize();
		foreach ( ['package_type', 'test_type', 'test_dir', 'requires', 'mu_plugins', 'envs', 'timeout', 'retry', 'actions'] as $inherited ) {
			$this->assertEquals( $parent_serialized[ $inherited ], $serialized[ $inherited ], "Field '{$inherited}' was not inherited." );
		}
		$this->assertEquals( $parent_serialized['test']['results'], $serialized['test']['results'] );
		foreach ( ['globalSetup', 'setup', 'teardown', 'globalTeardown'] as $phase ) {
			$this->assertEquals( $parent_serialized['test']['phases'][ $phase ], $serialized['test']['phases'][ $phase ], "Phase '{$phase}' was not inherited." );
		}

		// The serialized subpackage reloads into an equivalent manifest. Since jsonSerialize() only
		// emits the combined 'package' field, this also pins that a synthesized subpackage's
		// namespace/package name match what re-deriving them from the package ID produces.
		$reloaded = new TestPackageManifest( $serialized );
		$this->assertEquals( $subpackage->to_array(), $reloaded->to_array() );
	}

	/**
	 * Test a subpackage in a different namespace to its parent derives its own identity.
	 */
	public function test_synthesized_subpackage_in_other_namespace_derives_own_identity(): void {
		$parent = new TestPackageManifest( [
			'package' => 'woocommerce/e2e-suite',
			'test' => [
				'phases' => ['run' => ['npx playwright test']],
				'results' => []
			],
			'subpackages' => [
				'partner/checkout' => [
					'test' => ['phases' => ['run' => ['npx playwright test tests/checkout.spec.js']]],
				],
			],
		] );

		$subpackage = $parent->create_subpackage_manifest( 'partner/checkout' );

		$this->assertSame( 'partner/checkout', $subpackage->get_package_id() );
		$this->assertSame( 'partner', $subpackage->get_namespace() );
		$this->assertSame( 'checkout', $subpackage->get_package_name() );

		$normalized = $subpackage->to_array();
		$this->assertSame( 'partner', $normalized['namespace'] );
		$this->assertSame( 'checkout', $normalized['package_name'] );

		// The identity survives both round trips.
		$this->assertEquals( $normalized, ( new TestPackageManifest( $normalized ) )->to_array() );
		$this->assertEquals( $normalized, ( new TestPackageManifest( $subpackage->jsonSerialize() ) )->to_array() );
	}
}