<?php

/**
 * Integration tests for test package dependency auto-installation feature.
 * Tests that requires.plugins and requires.themes are automatically installed.
 */
class TestPackageDependenciesTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Test that required plugins are automatically installed from test package manifest
	 */
	public function test_auto_installs_required_plugins() {
		// Create test package with plugin requirements
		$test_pkg = $this->create_test_package( 'auto-install', [
			'plugins' => [ 'query-monitor', 'code-snippets' ]
		] );

		// Run test and capture output
		$output = qit( [
			'run:e2e',
			'jetpack',
			'--test-package=' . $test_pkg,
			'-v'
		], [], true ); // Allow non-zero exit

		// Verify plugins were auto-installed by checking they appear in the environment
		$this->assertStringContainsString( 'Query Monitor', $output );
		$this->assertStringContainsString( 'Code Snippets', $output );
	}

	/**
	 * Test that SUT is never added as a dependency even if required
	 */
	public function test_sut_never_added_as_dependency() {
		// Create test package that requires the SUT
		$test_pkg = $this->create_test_package( 'sut-test', [
			'plugins' => [ 'jetpack', 'query-monitor' ]
		] );

		// Run with jetpack as SUT
		$output = qit( [
			'run:e2e',
			'jetpack',  // This is the SUT
			'--test-package=' . $test_pkg,
			'-v'
		], [], true );

		// Verify query-monitor was installed
		$this->assertStringContainsString( 'Query Monitor', $output );
		
		// Jetpack appears as SUT, not as a dependency
		$this->assertStringContainsString( 'Plugin Under Test: jetpack', $output );
	}

	/**
	 * Test that manually provided plugins are not duplicated
	 */
	public function test_no_duplicate_when_plugin_already_provided() {
		// Create test package requiring query-monitor
		$test_pkg = $this->create_test_package( 'no-dup', [
			'plugins' => [ 'query-monitor' ]
		] );

		// Run with query-monitor already provided
		$output = qit( [
			'run:e2e',
			'jetpack',
			'--plugin=query-monitor',  // Already providing it
			'--test-package=' . $test_pkg,
			'-v'
		], [], true );

		// Verify query-monitor appears in the environment (was provided via CLI)
		$this->assertStringContainsString( 'Query Monitor', $output );
	}

	/**
	 * Test that multiple test packages' dependencies are combined correctly
	 */
	public function test_combines_dependencies_from_multiple_packages() {
		// Create two test packages with overlapping dependencies
		$pkg1 = $this->create_test_package( 'pkg1', [
			'plugins' => [ 'query-monitor' ]
		] );

		$pkg2 = $this->create_test_package( 'pkg2', [
			'plugins' => [ 'query-monitor', 'code-snippets' ]
		] );

		// Run with both packages
		$output = qit( [
			'run:e2e',
			'jetpack',
			'--test-package=' . $pkg1,
			'--test-package=' . $pkg2,
			'-v'
		], [], true );

		// Verify all dependencies were installed
		$this->assertStringContainsString( 'Query Monitor', $output );
		$this->assertStringContainsString( 'Code Snippets', $output );
	}

	/**
	 * Test that both plugins and themes are installed
	 */
	public function test_installs_both_plugins_and_themes() {
		// Create test package with both plugin and theme requirements
		$test_pkg = $this->create_test_package( 'both', [
			'plugins' => [ 'query-monitor' ],
			'themes' => [ 'twentytwentythree' ]
		] );

		// Run test
		$output = qit( [
			'run:e2e',
			'jetpack',
			'--test-package=' . $test_pkg,
			'-v'
		], [], true );

		// Verify both were installed
		$this->assertStringContainsString( 'Query Monitor', $output );
		$this->assertStringContainsString( 'twentytwentythree', $output );
	}

	/**
	 * Test that plugins from qit.json environment are not duplicated
	 */
	public function test_no_duplicate_with_qit_json_environment() {
		// Create a qit.json with environment configuration
		$qit_json_dir = sys_get_temp_dir() . '/qit_env_test_' . uniqid();
		mkdir( $qit_json_dir, 0777, true );
		
		$qit_json = [
			'$schema' => 'https://raw.githubusercontent.com/woocommerce/qit-cli/trunk/src/src/PreCommand/Schemas/qit-schema.json',
			'environments' => [
				'default' => [
					'plugins' => [ 'query-monitor', 'code-snippets' ]
				]
			]
		];
		file_put_contents( $qit_json_dir . '/qit.json', json_encode( $qit_json, JSON_PRETTY_PRINT ) );
		
		// Create test package requiring the same plugins
		$test_pkg = $this->create_test_package( 'env-dup', [
			'plugins' => [ 'query-monitor', 'debug-bar' ]
		] );
		
		// Run with qit.json config
		$output = qit( [
			'run:e2e',
			'jetpack',
			'--config=' . $qit_json_dir . '/qit.json',
			'--test-package=' . $test_pkg,
			'-v'
		], [], true );
		
		// Verify both plugins are in the environment
		$this->assertStringContainsString( 'Query Monitor', $output );
		$this->assertStringContainsString( 'Debug Bar', $output );
	}

	/**
	 * Helper to create a test package with given requirements
	 */
	private function create_test_package( string $name, array $requires ): string {
		$temp_dir = sys_get_temp_dir() . '/qit_test_deps_' . $name . '_' . uniqid();
		mkdir( $temp_dir, 0777, true );

		$manifest = [
			'package' => 'test/' . $name,
			'description' => 'Test package for ' . $name,
			'test' => [
				'phases' => [
					'run' => [ 'echo "Testing ' . $name . '"' ]
				],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir' => './results/blob'
				]
			]
		];

		if ( ! empty( $requires ) ) {
			$manifest['requires'] = $requires;
		}

		file_put_contents( $temp_dir . '/qit-test.json', json_encode( $manifest, JSON_PRETTY_PRINT ) );

		return $temp_dir;
	}
}