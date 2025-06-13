<?php

namespace QIT_CLI_Tests\PreCommand;

use QIT_CLI\App;
use QIT_CLI\PreCommand\Configuration\Parser\QitJsonParser;
use Spatie\Snapshots\MatchesSnapshots;

class TestPackagesConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.zip' );
		$this->mockDownloadUrl( 'https://downloads.wordpress.org/plugin/woocommerce.zip', $this->createMinimalPluginZip( 'woocommerce', '8.0.0' ) );
		// Create plugin-folder in temp_dir
		$plugin_folder = $this->temp_dir . '/plugin-folder';
		if ( ! is_dir( $plugin_folder ) ) {
			mkdir( $plugin_folder, 0777, true );
		}
		// Create minimal plugin file
		$plugin_file = $plugin_folder . '/local-plugin-1.php';
		file_put_contents( $plugin_file, "<?php\n/**\n * Plugin Name: Local Plugin 1\n * Version: 1.0\n */" );
		$this->to_delete[] = $plugin_folder;
		$this->to_delete[] = $plugin_file;
	}

	/**
	 * Tests that test packages are correctly parsed from qit.json into a nested structure.
	 */
	public function test_test_packages_parsing(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
				[
					'type'    => 'e2e',
					'name'    => 'basic',
					'file'    => 'tests/e2e/basic.json',
					'extends' => 'default',
				],
			],
		];

		$default_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Awesome Team',
			'description'  => 'Full checkout flow test for local-plugin-1',
			'test_command' => 'npm run playwright',
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
		];

		$basic_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0.1',
			'author'       => 'Default Author',
			'test_command' => 'npm run playwright --project basic',
			'description'  => 'Basic checkout flow test for local-plugin-1',
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );
		$this->mock_file( 'tests/e2e/basic.json', json_encode( $basic_json ) );

		$config_path = $this->create_temp_config_file( $config );
		$parser      = new QitJsonParser( $config_path );

		$this->assertArrayHasKey( 'test_packages', $parser->parsed_config );
		$this->assertIsArray( $parser->parsed_config['test_packages'] );
		$this->assertArrayHasKey( 'e2e', $parser->parsed_config['test_packages'] );
		$this->assertArrayHasKey( 'default', $parser->parsed_config['test_packages']['e2e'] );
		$this->assertArrayHasKey( 'basic', $parser->parsed_config['test_packages']['e2e'] );
		$this->assertEquals( 'npm run playwright', $parser->parsed_config['test_packages']['e2e']['default']['test_command'] );
		$this->assertEquals( 'npm run playwright --project basic', $parser->parsed_config['test_packages']['e2e']['basic']['test_command'] );
		$this->assertEquals( [ 'QIT_E2E_DEBUG' => 'true' ], $parser->parsed_config['test_packages']['e2e']['basic']['env_vars'] );
	}

	/**
	 * Tests that test packages are included in env_info with the correct nested structure.
	 * This addresses the failure in test_test_package_with_lifecycle.
	 */
	public function test_test_package_with_lifecycle(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'          => 'https://qit.woo.com/json-schema/test-package',
			'version'          => '1.0',
			'author'           => 'Awesome Team',
			'description'      => 'Full checkout flow test for awesome-plugin',
			'test_command'     => 'npm run playwright',
			'lifecycle'        => [
				'before_all_tests' => [
					[
						'command'  => './default/before_all.sh',
						'priority' => 10,
						'runs_on'  => 'docker',
					],
					[
						'command'  => 'npm run playwright --project setup',
						'priority' => 5,
						'runs_on'  => 'host',
					],
				],
				'after_all_tests'  => [
					[
						'command' => './default/after_all.sh',
						'runs_on' => 'docker',
					],
				],
				'before_sut_tests' => [
					[
						'command' => './default/after_all.sh',
						'runs_on' => 'docker',
					],
				],
				'after_sut_tests'  => [],
			],
			'test_results'     => [
				'ctrf'   => './results/ctrf.json',
				'allure' => './results/allure-results',
			],
			'mu_plugins'       => [
				'./default/mu-plugin.php',
			],
			'required_secrets' => [
				'CHECKOUT_KEY',
			],
			'env_vars'         => [
				'QIT_E2E_DEBUG' => true,
			],
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );

		// Create the necessary script files
		$this->mock_file( 'tests/e2e/default/before_all.sh', '#!/bin/bash\necho "Before all tests"' );
		$this->mock_file( 'tests/e2e/default/after_all.sh', '#!/bin/bash\necho "After all tests"' );
		$this->mock_file( 'tests/e2e/default/mu-plugin.php', '<?php\n// Test mu-plugin' );

		// Create directories for test results
		$results_dir = 'tests/e2e/results';
		if (!is_dir($this->temp_dir . DIRECTORY_SEPARATOR . $results_dir)) {
			mkdir($this->temp_dir . DIRECTORY_SEPARATOR . $results_dir, 0777, true);
		}

		$env_info = $this->run_unit_test( $config );

		// Verify test_packages structure in env_info
		$this->assertArrayHasKey( 'test_packages', $env_info );
		$this->assertIsArray( $env_info['test_packages'] );
		$this->assertArrayHasKey( 'e2e', $env_info['test_packages'] );
		$this->assertArrayHasKey( 'default', $env_info['test_packages']['e2e'] );

		// Verify specific configuration details
		$default_package = $env_info['test_packages']['e2e']['default'];
		$this->assertEquals( 'npm run playwright', $default_package['test_command'] );
		$this->assertEquals( 'npm run playwright --project setup', $default_package['lifecycle']['before_all_tests'][0]['command'] );
		$this->assertEquals( './default/before_all.sh', $default_package['lifecycle']['before_all_tests'][1]['command'] );
		$this->assertEquals( './results/ctrf.json', $default_package['test_results']['ctrf'] );
		$this->assertEquals( './default/mu-plugin.php', $default_package['mu_plugins'][0] );
		$this->assertEquals( [], $default_package['lifecycle']['after_sut_tests'] );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Tests that test package extensions are correctly resolved and included in env_info.
	 */
	public function test_test_package_extends(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default', 'local/basic' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
				[
					'type'    => 'e2e',
					'name'    => 'basic',
					'file'    => 'tests/e2e/basic.json',
					'extends' => 'default',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'          => 'https://qit.woo.com/json-schema/test-package',
			'version'          => '1.0',
			'author'           => 'Awesome Team',
			'description'      => 'Full checkout flow test for awesome-plugin',
			'test_command'     => 'npm run playwright',
			'lifecycle'        => [
				'before_all_tests' => [
					[
						'command'  => './default/before_all.sh',
						'priority' => 10,
						'runs_on'  => 'docker',
					],
					[
						'command'  => 'npm run playwright --project setup',
						'priority' => 5,
						'runs_on'  => 'host',
					],
				],
				'after_all_tests'  => [
					[
						'command' => './default/after_all.sh',
						'runs_on' => 'docker',
					],
				],
				'before_sut_tests' => [
					[
						'command' => './default/after_all.sh',
						'runs_on' => 'docker',
					],
				],
				'after_sut_tests'  => [],
			],
			'test_results'     => [
				'ctrf'   => './results/ctrf.json',
				'allure' => './results/allure-results',
			],
			'mu_plugins'       => [
				'./default/mu-plugin.php',
			],
			'required_secrets' => [
				'CHECKOUT_KEY',
			],
			'env_vars'         => [
				'QIT_E2E_DEBUG' => true,
			],
		];

		$basic_json = [
			'$schema'          => 'https://qit.woo.com/json-schema/test-package',
			'version'          => '1.0.1',
			'author'           => 'Default Author',
			'test_command'     => 'npm run playwright --project basic',
			'description'      => 'Basic checkout flow test for awesome-plugin',
			'required_secrets' => [
				'CHECKOUT_KEY',
			],
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );
		$this->mock_file( 'tests/e2e/basic.json', json_encode( $basic_json ) );

		// Create the necessary script files
		$this->mock_file( 'tests/e2e/default/before_all.sh', '#!/bin/bash\necho "Before all tests"' );
		$this->mock_file( 'tests/e2e/default/after_all.sh', '#!/bin/bash\necho "After all tests"' );
		$this->mock_file( 'tests/e2e/default/mu-plugin.php', '<?php\n// Test mu-plugin' );

		// Create directories for test results
		$results_dir = 'tests/e2e/results';
		if (!is_dir($this->temp_dir . DIRECTORY_SEPARATOR . $results_dir)) {
			mkdir($this->temp_dir . DIRECTORY_SEPARATOR . $results_dir, 0777, true);
		}

		$env_info = $this->run_unit_test( $config );

		// Verify test_packages structure
		$this->assertArrayHasKey( 'test_packages', $env_info );
		$this->assertArrayHasKey( 'e2e', $env_info['test_packages'] );
		$this->assertArrayHasKey( 'basic', $env_info['test_packages']['e2e'] );

		// Verify extended package configuration
		$basic_package = $env_info['test_packages']['e2e']['basic'];
		$this->assertEquals( 'npm run playwright --project basic', $basic_package['test_command'] );
		$this->assertEquals( [ 'QIT_E2E_DEBUG' => 'true' ], $basic_package['env_vars'] );
		$this->assertEquals( 'npm run playwright --project setup', $basic_package['lifecycle']['before_all_tests'][0]['command'] );
		$this->assertEquals( './default/before_all.sh', $basic_package['lifecycle']['before_all_tests'][1]['command'] );
		$this->assertEquals( './results/ctrf.json', $basic_package['test_results']['ctrf'] );
		$this->assertEquals( './default/mu-plugin.php', $basic_package['mu_plugins'][0] );
		$this->assertEquals( [ 'CHECKOUT_KEY' ], $basic_package['required_secrets'] );
		$this->assertCount( 1, $basic_package['required_secrets'] );
		$this->assertEquals( [], $basic_package['lifecycle']['after_sut_tests'] );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Tests that resolved test packages are correctly published without environment setup.
	 */
	public function test_publish_resolved_package(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default', 'local/basic' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
				[
					'type'    => 'e2e',
					'name'    => 'basic',
					'file'    => 'tests/e2e/basic.json',
					'extends' => 'default',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'          => 'https://qit.woo.com/json-schema/test-package',
			'version'          => '1.0',
			'author'           => 'Awesome Team',
			'description'      => 'Full checkout flow test for awesome-plugin',
			'test_command'     => 'npm run playwright',
			'lifecycle'        => [
				'before_all_tests' => [
					[
						'command'  => './default/before_all.sh',
						'priority' => 10,
						'runs_on'  => 'docker',
					],
					[
						'command'  => 'npm run playwright --project setup',
						'priority' => 5,
						'runs_on'  => 'host',
					],
				],
				'after_all_tests'  => [
					[
						'command' => './default/after_all.sh',
						'runs_on' => 'docker',
					],
				],
				'before_sut_tests' => [
					[
						'command' => './default/after_all.sh',
						'runs_on' => 'docker',
					],
				],
				'after_sut_tests'  => [],
			],
			'test_results'     => [
				'ctrf'   => './results/ctrf.json',
				'allure' => './results/allure-results',
			],
			'mu_plugins'       => [
				'./default/mu-plugin.php',
			],
			'required_secrets' => [
				'CHECKOUT_KEY',
			],
			'env_vars'         => [
				'QIT_E2E_DEBUG' => true,
			],
		];

		$basic_json = [
			'$schema'          => 'https://qit.woo.com/json-schema/test-package',
			'version'          => '1.0.1',
			'author'           => 'Default Author',
			'test_command'     => 'npm run playwright --project basic',
			'description'      => 'Basic checkout flow test for awesome-plugin',
			'required_secrets' => [
				'CHECKOUT_KEY',
			],
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );
		$this->mock_file( 'tests/e2e/basic.json', json_encode( $basic_json ) );

		// Create the necessary script files
		$this->mock_file( 'tests/e2e/default/before_all.sh', '#!/bin/bash\necho "Before all tests"' );
		$this->mock_file( 'tests/e2e/default/after_all.sh', '#!/bin/bash\necho "After all tests"' );
		$this->mock_file( 'tests/e2e/default/mu-plugin.php', '<?php\n// Test mu-plugin' );

		// Create directories for test results
		$results_dir = 'tests/e2e/results';
		if (!is_dir($this->temp_dir . DIRECTORY_SEPARATOR . $results_dir)) {
			mkdir($this->temp_dir . DIRECTORY_SEPARATOR . $results_dir, 0777, true);
		}

		$config_path = $this->create_temp_config_file( $config );
		$parser      = new QitJsonParser( $config_path );

		// Create the packages array with the expected structure
		$packages = [
			'e2e' => [
				'default' => [
					'config'  => $default_json,
					'extends' => null,
				],
				'basic'   => [
					'config'  => $basic_json,
					'extends' => 'default',
				],
			],
		];

		$resolved_package = App::make( \QIT_CLI\PreCommand\Parsers\CustomTestPackageParser::class )
		                       ->get_resolved_package( 'e2e', 'basic', $packages, dirname( $config_path ) );

		$expected = [
			'version'          => '1.0.1',
			'author'           => 'Default Author',
			'description'      => 'Basic checkout flow test for awesome-plugin',
			'test_command'     => 'npm run playwright --project basic',
			'lifecycle'        => [
				'before_all_tests' => [
					[
						'command'  => './default/before_all.sh',
						'priority' => 10,
						'runs_on'  => 'docker',
					],
					[
						'command'  => 'npm run playwright --project setup',
						'priority' => 5,
						'runs_on'  => 'host',
					],
				],
				'after_all_tests'  => [
					[
						'command' => './default/after_all.sh',
						'runs_on' => 'docker',
					],
				],
				'before_sut_tests' => [
					[
						'command' => './default/after_all.sh',
						'runs_on' => 'docker',
					],
				],
				'after_sut_tests'  => [],
			],
			'test_results'     => [
				'ctrf'   => './results/ctrf.json',
				'allure' => './results/allure-results',
			],
			'mu_plugins'       => [
				'./default/mu-plugin.php',
			],
			'required_secrets' => [
				'CHECKOUT_KEY',
			],
			'env_vars'         => [
				'QIT_E2E_DEBUG' => 'true',
			],
		];

		$this->assertEquals( $expected, $resolved_package );
		$this->assertMatchesJsonSnapshot( json_encode( $resolved_package, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Tests that an invalid test package reference in test_types results in a clear error message.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Emma, a new QIT CLI user and plugin developer
	 * Goal: Emma is configuring her qit.json to run E2E tests for her plugin. She accidentally references a test package
	 *       ("local/nonexistent") that isn't defined in test_packages. She expects a clear error message that points to
	 *       the issue and suggests checking the test_packages configuration, rather than a generic failure.
	 * System Behavior: The system should validate test package references in test_types against test_packages during
	 *                  parsing or environment setup. If a reference (e.g., "local/nonexistent") is invalid, it should
	 *                  throw a RuntimeException with a message like:
	 *                  "Test package 'local/nonexistent' in 'e2e:default' not found in test_packages configuration.
	 *                  Ensure it is defined with matching type and name."
	 *                  The error should occur before environment setup to fail fast and avoid unnecessary processing.
	 */
	public function test_invalid_test_package_reference_throws_clear_error(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/nonexistent' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Awesome Team',
			'test_command' => 'npm run playwright',
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );

		$result = $this->run_unit_test( $config, [], true );

		$this->assertArrayHasKey( 'exit_code', $result );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( "Test package 'local/nonexistent' in 'e2e:default' not found in test_packages configuration. Ensure it is defined with matching type and name.", $result['output'] );
	}

	/**
	 * Tests that local test package references are unversioned and remote test package references support versioning.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Chloe, a test engineer using QIT CLI
	 * Goal: Chloe is configuring her qit.json to run E2E tests with both local and remote test packages. For local
	 *       packages, she references "local/default" and expects it to use the current codebase state without a
	 *       version. She accidentally tries "local/default@1.0", expecting a clear error since local packages are
	 *       unversioned. For remote packages, she references "remote/standard@1.1" and expects the specific version,
	 *       or "remote/standard" to default to "stable". She wants the system to enforce these rules clearly to avoid
	 *       confusion and ensure deterministic test execution.
	 * System Behavior: The system should validate test package references in test_types during parsing or environment
	 *                  setup. Local references (starting with "local/") must not include a version (e.g., reject
	 *                  "local/default@1.0" with a RuntimeException: "Versioned reference 'local/default@1.0' in
	 *                  'e2e:default' is not supported for local test packages. Use 'local/default'."). Remote
	 *                  references (starting with "remote/") should allow versioning (e.g., "remote/standard@1.1") and
	 *                  default to "stable" if no version is specified. Only referenced packages should appear in
	 *                  env_info['test_packages'] with correct configurations.
	 * Why Critical: Enforcing unversioned local references prevents confusion about versioning for local packages,
	 *               aligning with their current-state nature. Supporting versioned remote references (or rejecting
	 *               them clearly if unsupported) ensures clarity for future extensibility. Clear errors and correct
	 *               env_info inclusion are critical for reliable test execution and DevEx. Low maintenance due to
	 *               simple configuration and stable error assertions.
	 */
	public function test_local_unversioned_and_remote_versioned_test_package_references(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default@1.0' ], // Invalid versioned local reference
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Awesome Team',
			'test_command' => 'npm run playwright',
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
			'description'  => 'Local default test package',
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );

		$result = $this->run_unit_test( $config, [], true );

		$this->assertArrayHasKey( 'exit_code', $result );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( "Versioned reference 'local/default@1.0' in 'e2e:default' is not supported for local test packages", $result['output'] );
	}

	/**
	 * Tests that duplicate local test package references resolve to the same current-state configuration.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Sam, a mid-level QIT CLI user and QA engineer
	 * Goal: Sam is configuring a complex qit.json with multiple test profiles (e2e:default and e2e:smoke) that both
	 *       reference the same local test package ("local/default"). Since local packages reflect the current state of
	 *       the codebase, Sam expects both profiles to use the same configuration without conflicts or version
	 *       mismatches, ensuring consistent test execution.
	 * System Behavior: The system should treat local test packages as unversioned, representing the current codebase
	 *                  state. If "local/default" is referenced multiple times (e.g., in different test_types profiles),
	 *                  it should resolve to the same configuration in env_info, using the single definition from
	 *                  test_packages. The test_packages[e2e][default] entry should be identical across references,
	 *                  reflecting the latest local state.
	 */
	public function test_duplicate_local_test_package_references_resolve_consistently(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
					'smoke'   => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Awesome Team',
			'description'  => 'Full checkout flow test for awesome-plugin',
			'test_command' => 'npm run playwright',
			'lifecycle'    => [
				'before_all_tests' => [
					[
						'command'  => './default/before_all.sh',
						'priority' => 10,
						'runs_on'  => 'docker',
					],
				],
				'after_all_tests'  => [
					[
						'command' => './default/after_all.sh',
						'runs_on' => 'docker',
					],
				],
			],
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );

		// Create the necessary script files
		$this->mock_file( 'tests/e2e/default/before_all.sh', '#!/bin/bash\necho "Before all tests"' );
		$this->mock_file( 'tests/e2e/default/after_all.sh', '#!/bin/bash\necho "After all tests"' );

		$env_info = $this->run_unit_test( $config );

		// Verify test_packages structure for both profiles
		$this->assertArrayHasKey( 'test_packages', $env_info );
		$this->assertArrayHasKey( 'e2e', $env_info['test_packages'] );
		$this->assertArrayHasKey( 'default', $env_info['test_packages']['e2e'] );

		// Verify configuration is consistent
		$default_package = $env_info['test_packages']['e2e']['default'];
		$this->assertEquals( 'npm run playwright', $default_package['test_command'] );
		$this->assertEquals( './default/before_all.sh', $default_package['lifecycle']['before_all_tests'][0]['command'] );
		$this->assertEquals( [ 'QIT_E2E_DEBUG' => 'true' ], $default_package['env_vars'] );

		// Run unit test with 'smoke' profile to ensure same configuration
		$config['test_types']['e2e'] = [ 'smoke' => $config['test_types']['e2e']['smoke'] ];
		$env_info_smoke              = $this->run_unit_test( $config );

		$this->assertArrayHasKey( 'test_packages', $env_info_smoke );
		$this->assertArrayHasKey( 'e2e', $env_info_smoke['test_packages'] );
		$this->assertArrayHasKey( 'default', $env_info_smoke['test_packages']['e2e'] );
		$this->assertEquals( $default_package, $env_info_smoke['test_packages']['e2e']['default'] );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Tests that a missing test package file in test_packages results in a clear error during parsing.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Liam, a plugin developer new to QIT CLI
	 * Goal: Liam is setting up his qit.json to run E2E tests. He specifies a test package file
	 *       ("tests/e2e/missing.json") that doesn't exist on disk, due to a typo. He expects a clear
	 *       error message indicating the missing file and suggesting a check of the file path, rather
	 *       than a vague failure.
	 * System Behavior: During parsing in CustomTestPackageParser, the system should check if the file
	 *                  specified in test_packages (e.g., "tests/e2e/missing.json") exists. If it doesn't,
	 *                  it should throw a RuntimeException with a message like:
	 *                  "Test package file '/path/to/tests/e2e/missing.json' for 'e2e:default' not found.
	 *                  Verify the file path in test_packages configuration."
	 *                  The error should occur during QitJsonParser::parse_config to fail fast.
	 * Why Critical: Missing files are a common configuration error that can halt environment setup.
	 *               Early validation with clear errors prevents downstream failures and improves DevEx.
	 *               Low maintenance due to simple file existence check and stable error expectation.
	 */
	public function test_missing_test_package_file_throws_clear_error(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/missing.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$result = $this->run_unit_test( $config, [], true );

		$this->assertArrayHasKey( 'exit_code', $result );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( "Test package file '", $result['output'] );
		$this->assertStringContainsString( "/tests/e2e/missing.json' for 'e2e:default' not found. Verify the file path in test_packages configuration.", $result['output'] );
	}

	/**
	 * Tests that duplicate test package definitions with the same type and name are rejected.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Priya, a senior test engineer using QIT CLI
	 * Goal: Priya is configuring a qit.json with multiple E2E test packages. She accidentally defines
	 *       two test packages with the same type ("e2e") and name ("default") but different files.
	 *       She expects the system to detect this ambiguity and fail with a clear error, preventing
	 *       unpredictable test execution.
	 * System Behavior: CustomTestPackageParser should check for duplicate test package definitions
	 *                  (same type and name) during parsing. If found, it should throw a RuntimeException
	 *                  with a message like:
	 *                  "Duplicate test package definition for 'e2e:default' in test_packages. Each test
	 *                  package must have a unique type and name combination."
	 *                  The error should occur in QitJsonParser::parse_config to fail fast.
	 * Why Critical: Duplicate definitions can cause ambiguous test execution, leading to unpredictable
	 *               results. Early detection ensures configuration integrity. Low maintenance due to
	 *               straightforward uniqueness check and stable error expectation.
	 */
	public function test_duplicate_test_package_definitions_rejected(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/another-default.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Awesome Team',
			'test_command' => 'npm run playwright',
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );
		$this->mock_file( 'tests/e2e/another-default.json', json_encode( $default_json ) );

		$result = $this->run_unit_test( $config, [], true );

		$this->assertArrayHasKey( 'exit_code', $result );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( "Duplicate test package definition for 'e2e:default' in test_packages. Each test package must have a unique type and name combination.", $result['output'] );
	}

	/**
	 * Tests that invalid JSON in a test package file results in a clear error during parsing.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Mia, a plugin developer using QIT CLI
	 * Goal: Mia is updating her E2E test package configuration in tests/e2e/default.json. Due to a syntax error,
	 *       the JSON file is invalid (e.g., missing a closing brace). She expects a clear error message indicating
	 *       the file and the JSON parsing issue, helping her fix the file quickly without debugging obscure errors.
	 * System Behavior: CustomTestPackageParser should validate the JSON in test package files during parsing. If the
	 *                  JSON is invalid, it should throw a RuntimeException with a message like:
	 *                  "Invalid JSON in test package file '/path/to/tests/e2e/default.json' for 'e2e:default':
	 *                  Syntax error. Verify the file contains valid JSON."
	 *                  The error should occur in QitJsonParser::parse_config to fail fast.
	 * Why Critical: Invalid JSON is a common error when editing configuration files, and it can halt environment
	 *               setup. Early validation with clear errors prevents downstream failures and improves DevEx.
	 *               Low maintenance due to simple JSON validation and stable error expectation.
	 */
	public function test_invalid_json_in_test_package_file_throws_clear_error(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		// Mock an invalid JSON file
		$invalid_json = '{ "$schema": "https://qit.woo.com/json-schema/test-package", "version": "1.0", "author": "Awesome Team"'; // Missing closing brace
		$this->mock_file( 'tests/e2e/default.json', $invalid_json );

		$result = $this->run_unit_test( $config, [], true );

		$this->assertArrayHasKey( 'exit_code', $result );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( "Invalid JSON in test package file", $result['output'] );
		$this->assertStringContainsString( "for 'e2e:default'", $result['output'] );
		$this->assertStringContainsString( "Syntax error", $result['output'] );
	}

	/**
	 * Tests that test packages not referenced in test_types are excluded from env_info.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Aisha, a QA engineer using QIT CLI
	 * Goal: Aisha is configuring her qit.json with multiple test packages for E2E tests but only wants to run
	 *       one ("local/default") in the 'e2e:default' profile. She defines an unused package ("local/extra") in
	 *       test_packages, expecting it to be ignored in env_info to keep the environment setup lean and avoid
	 *       unnecessary processing.
	 * System Behavior: EnvInfoBuilder should only include test packages referenced in test_types in
	 *                  env_info['test_packages']. Unreferenced packages (e.g., "local/extra") should be excluded,
	 *                  ensuring env_info contains only the relevant configurations. This should happen during
	 *                  EnvInfoBuilder::build_env_info to optimize environment setup.
	 * Why Critical: Including unused test packages bloats env_info, potentially causing performance issues or
	 *               conflicts in complex setups. Excluding them ensures efficiency and clarity, critical for
	 *               large test suites. Low maintenance due to simple reference check and stable assertion.
	 */
	public function test_unreferenced_test_packages_excluded_from_env_info(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
				[
					'type' => 'e2e',
					'name' => 'extra',
					'file' => 'tests/e2e/extra.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Awesome Team',
			'test_command' => 'npm run playwright',
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
		];

		$extra_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Awesome Team',
			'test_command' => 'npm run playwright --extra',
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );
		$this->mock_file( 'tests/e2e/extra.json', json_encode( $extra_json ) );

		$env_info = $this->run_unit_test( $config );

		// Verify only referenced test package is included
		$this->assertArrayHasKey( 'test_packages', $env_info );
		$this->assertArrayHasKey( 'e2e', $env_info['test_packages'] );
		$this->assertArrayHasKey( 'default', $env_info['test_packages']['e2e'] );

		// Check if 'extra' key exists in the test_packages array
		$extra_exists = false;
		if (isset($env_info['test_packages']['e2e']['extra'])) {
			$extra_exists = true;
		}
		$this->assertFalse($extra_exists, "The 'extra' test package should not be included in env_info");

		$this->assertEquals( 'npm run playwright', $env_info['test_packages']['e2e']['default']['test_command'] );

		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	/**
	 * Tests that lifecycle scripts referencing non-existent files are rejected during parsing.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Raj, a senior plugin developer using QIT CLI
	 * Goal: Raj is configuring an E2E test package with lifecycle scripts in tests/e2e/default.json. He
	 *       accidentally references a non-existent script ("./default/nonexistent.sh") in the lifecycle
	 *       section. He expects the system to detect this during parsing and fail with a clear error,
	 *       preventing runtime failures during test execution.
	 * System Behavior: CustomTestPackageParser should validate that files referenced in lifecycle scripts
	 *                  (e.g., "./default/nonexistent.sh") exist relative to the test package file's directory
	 *                  during parsing. If a file is missing, it should throw a RuntimeException with a message
	 *                  like:
	 *                  "Lifecycle script file '/path/to/tests/e2e/default/nonexistent.sh' for 'e2e:default'
	 *                  not found. Verify the file path in lifecycle configuration."
	 *                  The error should occur in QitJsonParser::parse_config to fail fast.
	 * Why Critical: Non-existent lifecycle scripts can cause test execution to fail at runtime, disrupting
	 *               CI/CD pipelines or local testing. Early validation ensures reliability, critical for
	 *               senior developers like Raj. Low maintenance due to simple file existence check and
	 *               stable error expectation.
	 */
	public function test_non_existent_lifecycle_script_throws_clear_error(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Awesome Team',
			'test_command' => 'npm run playwright',
			'lifecycle'    => [
				'before_all_tests' => [
					[
						'command'  => './default/nonexistent.sh',
						'priority' => 10,
						'runs_on'  => 'docker',
					],
				],
			],
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
		];

		// Create the directory structure for the test package
		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );

		// Create the default directory but not the script file
		$default_dir = 'tests/e2e/default';
		if (!is_dir($this->temp_dir . DIRECTORY_SEPARATOR . $default_dir)) {
			mkdir($this->temp_dir . DIRECTORY_SEPARATOR . $default_dir, 0777, true);
		}

		$result = $this->run_unit_test( $config, [], true );

		$this->assertArrayHasKey( 'exit_code', $result );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( "Lifecycle script file", $result['output'] );
		$this->assertStringContainsString( "for 'e2e:default' not found", $result['output'] );
		$this->assertStringContainsString( "Verify the file path in lifecycle configuration", $result['output'] );
	}

	/**
	 * Tests that circular dependencies in test package extensions are detected and rejected.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Tara, a senior QA engineer using QIT CLI
	 * Goal: Tara is configuring a complex qit.json with multiple E2E test packages that extend each other.
	 *       She accidentally creates a circular dependency (e.g., package A extends B, B extends A). She
	 *       expects the system to detect this during parsing and fail with a clear error, preventing crashes
	 *       or infinite loops during test execution.
	 * System Behavior: CustomTestPackageParser should detect circular dependencies in the 'extends' field
	 *                  during parsing (e.g., in resolve_extends). If a circular dependency is found, it
	 *                  should throw a RuntimeException with a message like:
	 *                  "Circular dependency detected in test package 'e2e:package_a'. Check 'extends' references."
	 *                  The error should occur in QitJsonParser::parse_config to fail fast.
	 * Why Critical: Circular dependencies can cause infinite loops or crashes, halting all test workflows.
	 *               Early detection is essential for system stability. Low maintenance due to simple
	 *               configuration and stable error expectation.
	 */
	public function test_circular_dependency_in_test_package_extensions_throws_error(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/package_a' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type'    => 'e2e',
					'name'    => 'package_a',
					'file'    => 'tests/e2e/package_a.json',
					'extends' => 'package_b',
				],
				[
					'type'    => 'e2e',
					'name'    => 'package_b',
					'file'    => 'tests/e2e/package_b.json',
					'extends' => 'package_a',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$package_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			'author'       => 'Awesome Team',
			'test_command' => 'npm run playwright',
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
		];

		$this->mock_file( 'tests/e2e/package_a.json', json_encode( $package_json ) );
		$this->mock_file( 'tests/e2e/package_b.json', json_encode( $package_json ) );

		$result = $this->run_unit_test( $config, [], true );

		$this->assertArrayHasKey( 'exit_code', $result );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( "Circular dependency detected in test package 'e2e:package_", $result['output'] );
	}

	/**
	 * Tests that missing required fields in test package JSON are rejected during parsing.
	 *
	 * @reasoning/ux-scenario
	 * Persona: Jamal, a plugin developer using QIT CLI
	 * Goal: Jamal is creating a new E2E test package in tests/e2e/default.json but forgets to include the
	 *       required 'author' field. He expects the system to detect this during parsing and fail with a
	 *       clear error, helping him correct the configuration before environment setup.
	 * System Behavior: CustomTestPackageParser should validate that required fields ('version', 'author')
	 *                  are present in test package JSON files during parsing. If a field is missing, it
	 *                  should throw a RuntimeException with a message like:
	 *                  "Test package 'e2e:default' is missing required field 'author'. Ensure all required
	 *                  fields are defined."
	 *                  The error should occur in QitJsonParser::parse_config to fail fast.
	 * Why Critical: Missing required fields can lead to incomplete configurations, causing runtime errors
	 *               or silent failures during test execution. Early validation ensures reliable setups.
	 *               Low maintenance due to simple field check and stable error expectation.
	 */
	public function test_missing_required_fields_in_test_package_json_throws_error(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'local-plugin-1',
				'source' => [
					'type' => 'directory',
					'path' => $this->temp_dir . '/plugin-folder',
				],
			],
			'test_types'    => [
				'e2e' => [
					'default' => [
						'environment' => 'default',
						'run'         => [
							'test_packages' => [ 'local/default' ],
						],
					],
				],
			],
			'test_packages' => [
				[
					'type' => 'e2e',
					'name' => 'default',
					'file' => 'tests/e2e/default.json',
				],
			],
			'environments'  => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$default_json = [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0',
			// Missing 'author' field
			'test_command' => 'npm run playwright',
			'env_vars'     => [ 'QIT_E2E_DEBUG' => true ],
		];

		$this->mock_file( 'tests/e2e/default.json', json_encode( $default_json ) );

		$result = $this->run_unit_test( $config, [], true );

		$this->assertArrayHasKey( 'exit_code', $result );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertNotEquals( 0, $result['exit_code'] );
		$this->assertStringContainsString( "Test package 'e2e:default' must include 'version' and 'author'", $result['output'] );
	}
}
