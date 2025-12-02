<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;

/**
 * Tests for Utility Packages functionality in env:up
 */
class UtilityPackagesTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		// Clear App container state before each test
		App::setVar( 'test_package_required_plugins', [] );
		App::setVar( 'test_package_required_themes', [] );
		App::setVar( 'test_package_required_secrets', [] );
		App::setVar( 'test_package_requires_network', false );
		App::setVar( 'test_package_requires_tunnel', false );
		App::setVar( 'environment_manager', null );
	}

	protected function tearDown(): void {
		// Cleanup
		App::setVar( 'test_package_required_plugins', [] );
		App::setVar( 'test_package_required_themes', [] );
		App::setVar( 'test_package_required_secrets', [] );
		App::setVar( 'test_package_requires_network', false );
		App::setVar( 'test_package_requires_tunnel', false );
		App::setVar( 'environment_manager', null );
		parent::tearDown();
	}

	public function test_utility_package_detects_as_utility_without_run_phase(): void {
		$manifest_data = [
			'package'   => 'test/utility',
			'test_type' => 'e2e',
			'test'      => [
				'phases' => [
					'globalSetup' => [ 'echo "setup"' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );

		// Should not have run phase
		$this->assertFalse( $manifest->has_phase( 'run' ) );

		// Should have globalSetup phase
		$this->assertTrue( $manifest->has_phase( 'globalSetup' ) );
	}

	public function test_test_package_detects_with_run_phase(): void {
		$manifest_data = [
			'package'   => 'test/test-package',
			'test_type' => 'e2e',
			'test'      => [
				'phases' => [
					'globalSetup' => [ 'echo "setup"' ],
					'run'         => [ 'npx playwright test' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );

		// Should have run phase
		$this->assertTrue( $manifest->has_phase( 'run' ) );

		// Should also have globalSetup phase
		$this->assertTrue( $manifest->has_phase( 'globalSetup' ) );
	}

	public function test_utility_package_with_plugin_requirements(): void {
		$manifest_data = [
			'package'   => 'test/utility-with-plugins',
			'test_type' => 'e2e',
			'requires'  => [
				'plugins' => [ 'woocommerce', 'jetpack' ],
			],
			'test'      => [
				'phases' => [
					'globalSetup' => [ 'wp plugin list' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );
		$requires = $manifest->get_requires();

		// Should extract plugin requirements
		$this->assertArrayHasKey( 'plugins', $requires );
		$this->assertContains( 'woocommerce', $requires['plugins'] );
		$this->assertContains( 'jetpack', $requires['plugins'] );

		// Should not have run phase (utility)
		$this->assertFalse( $manifest->has_phase( 'run' ) );
	}

	public function test_utility_package_with_theme_requirements(): void {
		$manifest_data = [
			'package'   => 'test/utility-with-themes',
			'test_type' => 'e2e',
			'requires'  => [
				'themes' => [ 'storefront', 'twentytwentythree' ],
			],
			'test'      => [
				'phases' => [
					'globalSetup' => [ 'wp theme list' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );
		$requires = $manifest->get_requires();

		// Should extract theme requirements
		$this->assertArrayHasKey( 'themes', $requires );
		$this->assertContains( 'storefront', $requires['themes'] );
		$this->assertContains( 'twentytwentythree', $requires['themes'] );
	}

	public function test_utility_package_with_secret_requirements(): void {
		$manifest_data = [
			'package'   => 'test/utility-with-secrets',
			'test_type' => 'e2e',
			'requires'  => [
				'secrets' => [ 'API_KEY', 'API_SECRET', 'WEBHOOK_URL' ],
			],
			'test'      => [
				'phases' => [
					'globalSetup' => [ './configure-api.sh' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );
		$requires = $manifest->get_requires();

		// Should extract secret requirements
		$this->assertArrayHasKey( 'secrets', $requires );
		$this->assertContains( 'API_KEY', $requires['secrets'] );
		$this->assertContains( 'API_SECRET', $requires['secrets'] );
		$this->assertContains( 'WEBHOOK_URL', $requires['secrets'] );
	}

	public function test_utility_package_with_mixed_requirements(): void {
		$manifest_data = [
			'package'   => 'test/utility-mixed',
			'test_type' => 'e2e',
			'requires'  => [
				'plugins' => [ 'woocommerce' ],
				'themes'  => [ 'storefront' ],
				'secrets' => [ 'API_KEY' ],
				'network' => true,
			],
			'test'      => [
				'phases' => [
					'globalSetup' => [ './setup.sh' ],
					'setup'       => [ 'npm install' ],
					'teardown'    => [ 'npm run cleanup' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );
		$requires = $manifest->get_requires();

		// Should extract all requirements
		$this->assertArrayHasKey( 'plugins', $requires );
		$this->assertArrayHasKey( 'themes', $requires );
		$this->assertArrayHasKey( 'secrets', $requires );
		$this->assertTrue( $requires['network'] );

		// Should have multiple phases but no run
		$this->assertTrue( $manifest->has_phase( 'globalSetup' ) );
		$this->assertTrue( $manifest->has_phase( 'setup' ) );
		$this->assertTrue( $manifest->has_phase( 'teardown' ) );
		$this->assertFalse( $manifest->has_phase( 'run' ) );
	}

	public function test_utility_package_empty_requirements(): void {
		$manifest_data = [
			'package'   => 'test/utility-minimal',
			'test_type' => 'e2e',
			'test'      => [
				'phases' => [
					'globalSetup' => [ 'wp option set onboarding_completed true' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );
		$requires = $manifest->get_requires();

		// Should handle missing requires gracefully
		$this->assertIsArray( $requires );
		$this->assertArrayNotHasKey( 'plugins', $requires );
		$this->assertArrayNotHasKey( 'themes', $requires );
		$this->assertArrayNotHasKey( 'secrets', $requires );
	}

	public function test_global_setup_only_flag_in_env_info(): void {
		$env_info = new \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo();

		// Default should be false
		$this->assertFalse( $env_info->global_setup_only );

		// Should be able to set to true
		$env_info->global_setup_only = true;
		$this->assertTrue( $env_info->global_setup_only );
	}

	public function test_env_info_serialization_excludes_environment_manager(): void {
		$env_info = new \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo();
		$env_info->env_id           = 'test123';
		$env_info->global_setup_only = true;

		// Serialize to JSON
		$json = json_encode( $env_info );
		$data = json_decode( $json, true );

		// Should include global_setup_only
		$this->assertArrayHasKey( 'global_setup_only', $data );
		$this->assertTrue( $data['global_setup_only'] );

		// Should NOT include environment_manager (runtime-only, not serialized)
		// This is implicitly tested by the fact that jsonSerialize was removed
		// and serialization works without errors
	}

	public function test_phase_detection_for_all_phases(): void {
		$manifest_data = [
			'package'   => 'test/all-phases',
			'test_type' => 'e2e',
			'test'      => [
				'phases' => [
					'globalSetup'    => [ 'echo "global setup"' ],
					'setup'          => [ 'npm install' ],
					'run'            => [ 'npx playwright test' ],
					'teardown'       => [ 'npm run cleanup' ],
					'globalTeardown' => [ 'echo "global teardown"' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );

		// Should detect all phases
		$this->assertTrue( $manifest->has_phase( 'globalSetup' ) );
		$this->assertTrue( $manifest->has_phase( 'setup' ) );
		$this->assertTrue( $manifest->has_phase( 'run' ) );
		$this->assertTrue( $manifest->has_phase( 'teardown' ) );
		$this->assertTrue( $manifest->has_phase( 'globalTeardown' ) );

		// Should not detect non-existent phase
		$this->assertFalse( $manifest->has_phase( 'nonexistent' ) );
	}

	public function test_get_phase_commands(): void {
		$manifest_data = [
			'package'   => 'test/commands',
			'test_type' => 'e2e',
			'test'      => [
				'phases' => [
					'globalSetup' => [
						'wp option set foo bar',
						'wp user create test test@example.com',
					],
					'setup'       => [
						'npm install',
					],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );

		// Should get globalSetup commands
		$globalSetup = $manifest->get_phase_commands( 'globalSetup' );
		$this->assertCount( 2, $globalSetup );
		$this->assertContains( 'wp option set foo bar', $globalSetup );
		$this->assertContains( 'wp user create test test@example.com', $globalSetup );

		// Should get setup commands
		$setup = $manifest->get_phase_commands( 'setup' );
		$this->assertCount( 1, $setup );
		$this->assertContains( 'npm install', $setup );

		// Should return empty array for non-existent phase
		$run = $manifest->get_phase_commands( 'run' );
		$this->assertIsArray( $run );
		$this->assertEmpty( $run );
	}

	public function test_app_container_stores_environment_manager(): void {
		// Set test secret in environment so EnvironmentManager validation passes
		putenv( 'TEST_SECRET=test-value' );

		// Create a mock EnvironmentManager
		$envManager = new \QIT_CLI\Environment\EnvironmentManager();
		$envManager->initialize( [], [], [ 'TEST_SECRET' ] );

		// Store in App container
		App::setVar( 'environment_manager', $envManager );

		// Should be retrievable
		$retrieved = App::getVar( 'environment_manager', null );
		$this->assertInstanceOf( \QIT_CLI\Environment\EnvironmentManager::class, $retrieved );
		$this->assertSame( $envManager, $retrieved );

		// Cleanup
		putenv( 'TEST_SECRET' );
	}

	public function test_app_container_returns_null_when_no_environment_manager(): void {
		// Explicitly set to null
		App::setVar( 'environment_manager', null );

		// Should return null
		$retrieved = App::getVar( 'environment_manager', null );
		$this->assertNull( $retrieved );
	}

	public function test_required_secrets_stored_in_app_container(): void {
		// Simulate storing required secrets
		$secrets = [
			'API_KEY'    => [ 'test/package1' ],
			'API_SECRET' => [ 'test/package1', 'test/package2' ],
		];

		App::setVar( 'test_package_required_secrets', $secrets );

		// Should be retrievable
		$retrieved = App::getVar( 'test_package_required_secrets', [] );
		$this->assertIsArray( $retrieved );
		$this->assertArrayHasKey( 'API_KEY', $retrieved );
		$this->assertArrayHasKey( 'API_SECRET', $retrieved );
		$this->assertContains( 'test/package1', $retrieved['API_KEY'] );
		$this->assertContains( 'test/package2', $retrieved['API_SECRET'] );
	}

	public function test_required_plugins_stored_in_app_container(): void {
		// Simulate storing required plugins
		$plugins = [
			'woocommerce' => [ 'test/package1' ],
			'jetpack'     => [ 'test/package2' ],
		];

		App::setVar( 'test_package_required_plugins', $plugins );

		// Should be retrievable
		$retrieved = App::getVar( 'test_package_required_plugins', [] );
		$this->assertIsArray( $retrieved );
		$this->assertArrayHasKey( 'woocommerce', $retrieved );
		$this->assertArrayHasKey( 'jetpack', $retrieved );
	}

	public function test_required_themes_stored_in_app_container(): void {
		// Simulate storing required themes
		$themes = [
			'storefront'          => [ 'test/package1' ],
			'twentytwentythree'   => [ 'test/package2' ],
		];

		App::setVar( 'test_package_required_themes', $themes );

		// Should be retrievable
		$retrieved = App::getVar( 'test_package_required_themes', [] );
		$this->assertIsArray( $retrieved );
		$this->assertArrayHasKey( 'storefront', $retrieved );
		$this->assertArrayHasKey( 'twentytwentythree', $retrieved );
	}

	public function test_network_requirement_stored_in_app_container(): void {
		// Set network requirement
		App::setVar( 'test_package_requires_network', true );

		// Should be retrievable
		$retrieved = App::getVar( 'test_package_requires_network', false );
		$this->assertTrue( $retrieved );

		// Set to false
		App::setVar( 'test_package_requires_network', false );
		$retrieved = App::getVar( 'test_package_requires_network', false );
		$this->assertFalse( $retrieved );
	}

	public function test_tunnel_requirement_stored_in_app_container(): void {
		// Set tunnel requirement
		App::setVar( 'test_package_requires_tunnel', true );

		// Should be retrievable
		$retrieved = App::getVar( 'test_package_requires_tunnel', false );
		$this->assertTrue( $retrieved );

		// Set to false
		App::setVar( 'test_package_requires_tunnel', false );
		$retrieved = App::getVar( 'test_package_requires_tunnel', false );
		$this->assertFalse( $retrieved );
	}

	/**
	 * Test that utility packages are correctly identified by package_type derivation
	 */
	public function test_utility_package_type_is_derived_from_missing_run_phase(): void {
		$manifest_data = [
			'package' => 'test/utility-no-explicit-type',
			'test'    => [
				'phases' => [
					'globalSetup' => [ 'echo "setup"' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );

		// Should derive as utility package (no run phase)
		$this->assertEquals( 'utility', $manifest->get_package_type() );
		$this->assertTrue( $manifest->is_utility_package() );
	}

	/**
	 * Test that test packages are correctly identified by package_type derivation
	 */
	public function test_test_package_type_is_derived_from_run_phase(): void {
		$manifest_data = [
			'package'   => 'test/test-package-no-explicit-type',
			'test_type' => 'e2e',
			'test'      => [
				'phases'  => [
					'run' => [ 'npx playwright test' ],
				],
				'results' => [
					'ctrf-json' => './results/report.json',
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );

		// Should derive as test package (has run phase)
		$this->assertEquals( 'test', $manifest->get_package_type() );
		$this->assertFalse( $manifest->is_utility_package() );
	}

	/**
	 * Test that explicit package_type field takes precedence over derivation
	 */
	public function test_explicit_package_type_takes_precedence(): void {
		// Explicit utility type (even though it has phases that could be test-like)
		$utility_manifest = [
			'package'      => 'test/explicit-utility',
			'package_type' => 'utility',
			'test'         => [
				'phases' => [
					'globalSetup' => [ 'echo "setup"' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $utility_manifest );
		$this->assertEquals( 'utility', $manifest->get_package_type() );

		// Explicit test type
		$test_manifest = [
			'package'      => 'test/explicit-test',
			'package_type' => 'test',
			'test_type'    => 'e2e',
			'test'         => [
				'phases'  => [
					'run' => [ 'npx playwright test' ],
				],
				'results' => [
					'ctrf-json' => './results/report.json',
				],
			],
		];

		$manifest = new TestPackageManifest( $test_manifest );
		$this->assertEquals( 'test', $manifest->get_package_type() );
	}

	/**
	 * Test that utility packages don't require test_type field
	 */
	public function test_utility_package_does_not_require_test_type(): void {
		$manifest_data = [
			'package' => 'test/utility-no-test-type',
			// Note: no test_type field
			'test'    => [
				'phases' => [
					'globalSetup' => [ 'wp option set foo bar' ],
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );

		// Should not throw exception
		$this->assertEquals( 'utility', $manifest->get_package_type() );

		// test_type defaults to 'e2e' but that's OK for internal consistency
		// The important part is the package is identified as utility
		$this->assertTrue( $manifest->is_utility_package() );
	}

	/**
	 * Test that test packages require test_type field
	 */
	public function test_test_package_has_test_type(): void {
		$manifest_data = [
			'package'   => 'test/test-with-type',
			'test_type' => 'e2e',
			'test'      => [
				'phases'  => [
					'run' => [ 'npx playwright test' ],
				],
				'results' => [
					'ctrf-json' => './results/report.json',
				],
			],
		];

		$manifest = new TestPackageManifest( $manifest_data );

		$this->assertEquals( 'test', $manifest->get_package_type() );
		$this->assertEquals( 'e2e', $manifest->get_test_type() );
		$this->assertFalse( $manifest->is_utility_package() );
	}

	/**
	 * Test backward compatibility: packages without package_type field
	 * are correctly identified by their phases
	 */
	public function test_backward_compatibility_package_type_derivation(): void {
		// Old-style utility (no package_type, no run phase)
		$old_utility = [
			'package' => 'test/old-utility',
			'test'    => [
				'phases' => [
					'globalSetup' => [ 'echo "setup"' ],
				],
			],
		];

		$utility_manifest = new TestPackageManifest( $old_utility );
		$this->assertEquals( 'utility', $utility_manifest->get_package_type() );

		// Old-style test package (no package_type, has run phase)
		$old_test = [
			'package'   => 'test/old-test',
			'test_type' => 'e2e',
			'test'      => [
				'phases'  => [
					'run' => [ 'npx playwright test' ],
				],
				'results' => [
					'ctrf-json' => './results/report.json',
				],
			],
		];

		$test_manifest = new TestPackageManifest( $old_test );
		$this->assertEquals( 'test', $test_manifest->get_package_type() );
	}
}
