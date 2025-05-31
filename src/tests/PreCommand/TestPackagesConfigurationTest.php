<?php

namespace QIT_CLI_Tests\PreCommand;

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
		$plugin_file = $plugin_folder . '/awesome-plugin.php';
		file_put_contents( $plugin_file, "<?php\n/**\n * Plugin Name: Awesome Plugin\n * Version: 1.0\n */" );
		$this->to_delete[] = $plugin_folder;
		$this->to_delete[] = $plugin_file;
	}

	public function test_test_package_with_lifecycle(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
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

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'test_packages', $env_info );
		$this->assertArrayHasKey( 'e2e', $env_info['test_packages'] );
		$this->assertArrayHasKey( 'default', $env_info['test_packages']['e2e'] );
		$this->assertEquals( 'npm run playwright --project setup', $env_info['test_packages']['e2e']['default']['lifecycle']['before_all_tests'][0]['command'] );
		$this->assertEquals( 'default/before_all.sh', $env_info['test_packages']['e2e']['default']['lifecycle']['before_all_tests'][1]['command'] );
		$this->assertEquals( 'results/ctrf.json', $env_info['test_packages']['e2e']['default']['test_results']['ctrf'] );
		$this->assertEquals( 'default/mu-plugin.php', $env_info['test_packages']['e2e']['default']['mu_plugins'][0] );
		$this->assertEquals( [], $env_info['test_packages']['e2e']['default']['lifecycle']['after_sut_tests'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_test_package_extends(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
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

		$env_info = $this->run_unit_test( $config );
		$this->assertEquals( 'npm run playwright --project basic', $env_info['test_packages']['e2e']['basic']['test_command'] );
		$this->assertEquals( [ 'QIT_E2E_DEBUG' => 'true' ], $env_info['test_packages']['e2e']['basic']['env_vars'] );
		$this->assertEquals( 'npm run playwright --project setup', $env_info['test_packages']['e2e']['basic']['lifecycle']['before_all_tests'][0]['command'] );
		$this->assertEquals( 'default/before_all.sh', $env_info['test_packages']['e2e']['basic']['lifecycle']['before_all_tests'][1]['command'] );
		$this->assertEquals( 'results/ctrf.json', $env_info['test_packages']['e2e']['basic']['test_results']['ctrf'] );
		$this->assertEquals( 'default/mu-plugin.php', $env_info['test_packages']['e2e']['basic']['mu_plugins'][0] );
		$this->assertEquals( [ 'CHECKOUT_KEY' ], $env_info['test_packages']['e2e']['basic']['required_secrets'] );
		$this->assertCount( 1, $env_info['test_packages']['e2e']['basic']['required_secrets'] );
		$this->assertEquals( [], $env_info['test_packages']['e2e']['basic']['lifecycle']['after_sut_tests'] );
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function test_publish_resolved_package(): void {
		$config = [
			'sut'           => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
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

		$config_path      = $this->create_temp_config_file( $config );
		$parser           = new \QIT_CLI\PreCommand\ConfigFile\ConfigParser( $config_path );

		// Create the packages array with the expected structure
		$packages = [
			'e2e' => [
				'default' => [
					'config' => $default_json,
					'extends' => null,
				],
				'basic' => [
					'config' => $basic_json,
					'extends' => 'default',
				],
			],
		];

		$resolved_package = \QIT_CLI\App::make( \QIT_CLI\PreCommand\ConfigFile\Parsers\CustomTestPackageParser::class )
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
}
