<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\QITConfig;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class QITCommandTest extends QITTestCase {
	use MatchesSnapshots;

	protected Application $application;
	protected array $files_to_clean = [ 'qit.json', 'custom.json' ];

	public function setUp(): void {
		parent::setUp();
		$this->application = App::make( Application::class );
		$this->application->add( new TestConfigCommand() );
		$this->application->add( new FooTestCommand() );
		$this->application->add( new BarTestCommand() );
		$this->application->add( new BazTestCommand() );
	}

	public function tearDown(): void {
		foreach ( $this->files_to_clean as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}
		$this->files_to_clean = [ 'qit.json', 'custom.json' ];
		parent::tearDown();
	}

	protected function assertCommandOutput( CommandTester $tester, string $expectedOutput, int $expectedStatus ): void {
		$this->assertStringContainsString( $expectedOutput, $tester->getDisplay() );
		$this->assertEquals( $expectedStatus, $tester->getStatusCode() );
	}

	public function test_loads_config_successfully() {
		file_put_contents( 'qit.json', json_encode( [ 'key' => 'value' ] ) );
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--output-config' => true ] );
		$this->assertCommandOutput( $tester, 'Config: {"key":"value"}', Command::SUCCESS );
	}

	public function test_handles_missing_config_file() {
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--output-config' => true ] );
		$this->assertCommandOutput( $tester, 'Config: []', Command::SUCCESS );
	}

	public function test_overridable_inputs() {
		file_put_contents( 'qit.json', json_encode( [ 'setting' => 'default' ] ) );
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--setting' => 'overridden' ] );
		$this->assertCommandOutput( $tester, 'Setting: overridden', Command::SUCCESS );
		$tester->execute( [ '--setting' => 'default' ] );
		$this->assertCommandOutput( $tester, 'Setting: default', Command::SUCCESS );
	}

	public function test_custom_config_file() {
		file_put_contents( 'custom.json', json_encode( [ 'key' => 'custom_value' ] ) );
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--config' => 'custom.json', '--output-config' => true ] );
		$this->assertCommandOutput( $tester, 'Config: {"key":"custom_value"}', Command::SUCCESS );
	}

	public function test_invalid_json() {
		file_put_contents( 'qit.json', '{invalid json}' );
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [] );
		$this->assertCommandOutput( $tester, 'Invalid qit.json format. Must be a JSON object.', Command::FAILURE );
	}

	public function test_environment_config_retrieval() {
		$testCases = [
			'inheritance' => [
				'configData'     => [
					'environments' => [
						'base'   => [ 'php_version' => '8.2', 'wordpress_version' => 'stable', 'plugins' => [ 'woocommerce' ] ],
						'legacy' => [ 'extends' => 'base', 'php_version' => '7.4', 'wordpress_version' => '6.1' ]
					]
				],
				'env'            => 'legacy',
				'expectedOutput' => json_encode( [ 'php_version' => '7.4', 'wordpress_version' => '6.1', 'plugins' => [ 'woocommerce' ] ] ),
				'expectedError'  => null,
				'expectedStatus' => Command::SUCCESS
			],
			'missing'     => [
				'configData'     => [],
				'env'            => 'non_existing',
				'expectedOutput' => null,
				'expectedError'  => "Configuration 'non_existing' not found in section.",
				'expectedStatus' => Command::FAILURE
			]
		];

		foreach ( $testCases as $caseName => $case ) {
			file_put_contents( 'qit.json', json_encode( $case['configData'] ) );
			$tester = new CommandTester( $this->application->find( 'test:config' ) );
			$tester->execute( [ '--get-environment' => $case['env'] ] );
			if ( $case['expectedOutput'] ) {
				$this->assertCommandOutput( $tester, $case['expectedOutput'], $case['expectedStatus'] );
			} elseif ( $case['expectedError'] ) {
				$this->assertCommandOutput( $tester, $case['expectedError'], $case['expectedStatus'] );
			}
		}
	}

	public function test_custom_test_package_config_retrieval() {
		$testCases = [
			'inheritance' => [
				'configData'     => [
					'custom_test_packages' => [
						'default' => [ 'root_path' => './tests/e2e', 'test_command' => 'npx playwright test' ],
						'basic'   => [ 'extends' => 'default', 'test_command' => 'npx playwright test --grep @basic' ]
					]
				],
				'package'        => 'basic',
				'expectedOutput' => json_encode( [ 'root_path' => './tests/e2e', 'test_command' => 'npx playwright test --grep @basic' ] ),
				'expectedError'  => null,
				'expectedStatus' => Command::SUCCESS
			],
			'missing'     => [
				'configData'     => [],
				'package'        => 'non_existing',
				'expectedOutput' => null,
				'expectedError'  => "Configuration 'non_existing' not found in section.",
				'expectedStatus' => Command::FAILURE
			]
		];

		foreach ( $testCases as $caseName => $case ) {
			file_put_contents( 'qit.json', json_encode( $case['configData'] ) );
			$tester = new CommandTester( $this->application->find( 'test:config' ) );
			$tester->execute( [ '--get-package' => $case['package'] ] );
			if ( $case['expectedOutput'] ) {
				$this->assertCommandOutput( $tester, $case['expectedOutput'], $case['expectedStatus'] );
			} elseif ( $case['expectedError'] ) {
				$this->assertCommandOutput( $tester, $case['expectedError'], $case['expectedStatus'] );
			}
		}
	}

	public function test_get_group_tests() {
		file_put_contents( 'qit.json', json_encode( [
			'tests'  => [
				'foo' => [ 'default' => [ 'param' => 'value' ] ],
				'bar' => [ 'default' => [ 'env' => 'base' ] ]
			],
			'groups' => [ 'pre_release' => [ 'foo:default', 'bar:default' ] ]
		] ) );
		$config      = new QITConfig( 'qit.json', $this->application );
		$group_tests = $config->get_group_tests( 'pre_release' );
		$this->assertCount( 2, $group_tests );
		$this->assertEquals( [ 'type' => 'foo', 'profile' => 'default', 'config' => [ 'param' => 'value' ] ], $group_tests[0] );
		$this->assertEquals( [ 'type' => 'bar', 'profile' => 'default', 'config' => [ 'env' => 'base' ] ], $group_tests[1] );
	}

	public function test_get_group_tests_invalid_ref() {
		file_put_contents( 'qit.json', json_encode( [ 'groups' => [ 'invalid' => [ 'foo' ] ] ] ) );
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Invalid test reference 'foo' in group 'invalid'. Expected 'type:profile'." );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_get_test_matrix() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'foo' => [
					'default' => [
						'test_matrix' => [
							[ 'slug' => 'woocommerce', 'test_package' => 'foo:basic' ],
							[ 'slug' => 'my-plugin', 'test_package' => './tests/foo' ]
						]
					]
				]
			]
		] ) );
		$config = new QITConfig( 'qit.json', $this->application );
		$matrix = $config->get_compatibility_tests( 'foo', 'default' );
		$this->assertEquals( [
			[ 'slug' => 'woocommerce', 'test_package' => 'foo:basic' ],
			[ 'slug' => 'my-plugin', 'test_package' => './tests/foo' ]
		], $matrix );
	}

	public function test_loads_full_config() {
		file_put_contents( 'qit.json', json_encode( [
			'$schema'              => '[invalid url, do not cite]',
			'slug'                 => 'awesome-plugin',
			'type'                 => 'plugin',
			'pre_test_build'       => [ 'command' => 'npm run build', 'output' => './plugin.zip' ],
			'tests'                => [
				'foo' => [
					'default' => [
						'env'         => 'base',
						'test_matrix' => [
							[ 'slug' => 'woocommerce', 'test_package' => 'foo:basic' ],
							[ 'slug' => 'my-plugin', 'test_package' => './tests/foo' ]
						]
					]
				],
				'bar' => [ 'default' => [ 'settings' => [ 'skip' => [ 'I can do this', '/I can do \\w+/' ] ] ] ],
				'baz' => [ 'basic' => [ 'settings' => [ 'level' => 0 ] ] ]
			],
			'groups'               => [ 'pre_release' => [ 'foo:default', 'bar:default' ] ],
			'custom_test_packages' => [
				'default' => [
					'root_path'    => './tests/foo',
					'test_results' => [ 'ctrf' => './results/ctrf.json' ]
				]
			],
			'environments'         => [
				'base' => [
					'php_version' => '8.2',
					'volumes'     => [ './:/var/www/html/wp-content/plugins/awesome-plugin' ]
				]
			]
		] ) );
		$config = new QITConfig( 'qit.json', $this->application );
		$this->assertEquals( 'awesome-plugin', $config->get( 'slug' ) );
		$this->assertEquals( 'plugin', $config->get( 'type' ) );
		$this->assertEquals( [ 'command' => 'npm run build', 'output' => './plugin.zip' ], $config->get( 'pre_test_build' ) );
		$this->assertNotEmpty( $config->get( 'tests' ) );
		$this->assertNotEmpty( $config->get( 'groups' ) );
		$this->assertNotEmpty( $config->get( 'custom_test_packages' ) );
		$this->assertNotEmpty( $config->get( 'environments' ) );
	}

	public function test_invalid_top_level_key_type() {
		file_put_contents( 'qit.json', json_encode( [ 'tests' => 'not_an_array' ] ) );
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Tests must be an array.' );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_get_nested_value() {
		file_put_contents( 'qit.json', json_encode( [
			'tests'                => [
				'bar' => [ 'default' => [ 'settings' => [ 'skip' => [ 'I can do this', '/I can do \\w+/' ] ] ] ],
				'baz' => [ 'basic' => [ 'settings' => [ 'level' => 0 ] ] ]
			],
			'custom_test_packages' => [
				'default' => [ 'test_results' => [ 'ctrf' => './results/ctrf.json' ] ]
			]
		] ) );
		$config = new QITConfig( 'qit.json', $this->application );
		$this->assertEquals( [ 'I can do this', '/I can do \\w+/' ], $config->get_nested_value( 'tests.bar.default.settings.skip' ) );
		$this->assertEquals( 0, $config->get_nested_value( 'tests.baz.basic.settings.level' ) );
		$this->assertEquals( './results/ctrf.json', $config->get_nested_value( 'custom_test_packages.default.test_results.ctrf' ) );
		$this->assertNull( $config->get_nested_value( 'tests.nonexistent' ) );
	}

	public function test_test_config_with_matrix_and_settings() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'foo' => [
					'default' => [
						'env'         => 'base',
						'test_matrix' => [
							[ 'slug' => 'woocommerce', 'test_package' => 'foo:basic' ],
							[ 'slug' => 'my-plugin', 'test_package' => './tests/foo' ]
						]
					]
				],
				'bar' => [ 'default' => [ 'settings' => [ 'skip' => [ 'I can do this' ] ] ] ]
			]
		] ) );
		$config     = new QITConfig( 'qit.json', $this->application );
		$foo_config = $config->get_test_config( 'foo', 'default' );
		$this->assertEquals( 'base', $foo_config['env'] );
		$this->assertCount( 2, $foo_config['test_matrix'] );
		$bar_config = $config->get_test_config( 'bar', 'default' );
		$this->assertEquals( [ 'I can do this' ], $bar_config['settings']['skip'] );
	}

	public function test_inheritance_nested_fields() {
		file_put_contents( 'qit.json', json_encode( [
			'custom_test_packages' => [
				'default' => [
					'root_path'    => './tests/foo',
					'test_command' => 'npx playwright test',
					'test_results' => [ 'ctrf' => './results/ctrf.json', 'allure' => './results/allure-results' ],
					'mu_plugins'   => [ './bootstrap/mu-plugin.php' ],
					'env_vars'     => [ 'QIT_E2E_DEBUG' => 'true' ]
				],
				'basic'   => [
					'extends'      => 'default',
					'test_command' => 'npx playwright test --grep @basic',
					'test_results' => [ 'ctrf' => './results/ctrf-custom.json' ],
					'mu_plugins'   => [ './bootstrap/custom-mu-plugin.php' ],
					'env_vars'     => [ 'QIT_E2E_VERBOSE' => 'true' ]
				],
			],
			'environments'         => [
				'base'   => [
					'php_version'       => '8.2',
					'wordpress_version' => 'stable',
					'plugins'           => [ 'woocommerce', 'akismet' ],
					'env_vars'          => [ 'QIT_DEBUG' => 'true' ],
					'volumes'           => [ './:/var/www/html/wp-content/plugins/awesome-plugin' ]
				],
				'legacy' => [
					'extends'     => 'base',
					'php_version' => '7.4',
					'plugins'     => [ 'woocommerce' ],
					'env_vars'    => [ 'QIT_LEGACY_MODE' => 'true' ]
				]
			]
		] ) );
		$config = new QITConfig( 'qit.json', $this->application );

		$basic_package = $config->get_custom_test_package( 'basic' );
		$this->assertEquals( './tests/foo', $basic_package['root_path'] );
		$this->assertEquals( 'npx playwright test --grep @basic', $basic_package['test_command'] );
		$this->assertEquals( [ 'ctrf' => './results/ctrf-custom.json' ], $basic_package['test_results'] );
		$this->assertEquals( [ './bootstrap/custom-mu-plugin.php' ], $basic_package['mu_plugins'] );
		$this->assertEquals( [ 'QIT_E2E_VERBOSE' => 'true' ], $basic_package['env_vars'] );

		$legacy_env = $config->get_environment( 'legacy' );
		$this->assertEquals( '7.4', $legacy_env['php_version'] );
		$this->assertEquals( 'stable', $legacy_env['wordpress_version'] );
		$this->assertEquals( [ 'woocommerce' ], $legacy_env['plugins'] );
		$this->assertEquals( [ 'QIT_LEGACY_MODE' => 'true' ], $legacy_env['env_vars'] );
		$this->assertEquals( [ './:/var/www/html/wp-content/plugins/awesome-plugin' ], $legacy_env['volumes'] );
	}

	public function test_get_test_matrix_invalid() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [ 'foo' => [ 'default' => [ 'test_matrix' => 'not_an_array' ] ] ]
		] ) );
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Test_matrix in 'foo:default' must be an array." );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_invalid_custom_test_package_env_vars() {
		file_put_contents( 'qit.json', json_encode( [
			'custom_test_packages' => [
				'bad_env_vars' => [
					'env_vars' => 'not_an_array'
				]
			]
		] ) );
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "env_vars in custom test package 'bad_env_vars' must be an array." );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_circular_inheritance() {
		file_put_contents( 'qit.json', json_encode( [
			'custom_test_packages' => [
				'foo' => [ 'root_path' => './tests/foo', 'test_command' => 'npx playwright test', 'extends' => 'bar' ],
				'bar'  => [ 'extends' => 'foo', 'test_command' => 'npx playwright test --grep @bad' ]
			]
		] ) );
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Deep inheritance not allowed in custom test package: 'bar' cannot extend another configuration." );
		new QITConfig( 'qit.json', $this->application );
	}
}