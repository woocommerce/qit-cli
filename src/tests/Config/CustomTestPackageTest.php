<?php

namespace QIT_CLI_Tests\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CustomTestPackageTest extends AbstractConfigTest {
	public function test_custom_test_package_config_retrieval_inheritance() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"custom_test_packages": {
		"foo": {
			"default": {
				"root_path": "./tests/foo",
				"test_command": "npx playwright test"
			},
			"basic": {
				"extends": "default",
				"test_command": "npx playwright test --grep @basic"
			}
		}
	}
}
JSON
		);

		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--get-package' => 'foo.basic' ] );

		$expectedOutput = json_encode( [ 'root_path' => './tests/foo', 'test_command' => 'npx playwright test --grep @basic' ] );
		$this->assertCommandOutput( $tester, $expectedOutput, Command::SUCCESS );
	}

	public function test_custom_test_package_config_retrieval_missing() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin"
}
JSON
		);

		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--get-package' => 'foo.non_existing' ] );

		$expectedError = "Configuration 'non_existing' not found in section 'custom_test_packages.foo";
		$this->assertCommandOutput( $tester, $expectedError, Command::FAILURE );
	}

	public function test_inheritance_nested_fields() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"pre_test_build": {
		"command": "npm run build",
		"output": "./plugin.zip"
	},
	"custom_test_packages": {
		"foo": {
			"default": {
				"root_path": "./tests/foo",
				"test_command": "npx playwright test",
				"test_results": {
					"ctrf": "./results/ctrf.json",
					"allure": "./results/allure-results"
				},
				"mu_plugins": [
					"./bootstrap/mu-plugin.php"
				],
				"env_vars": {
					"QIT_FOO_DEBUG": "true"
				},
				"required_secrets": [
					"MY_API_KEY"
				],
				"lifecycle": {
					"setup": "./bootstrap/setup.sh",
					"teardown": "./bootstrap/teardown.sh"
				},
				"constraints": {
					"wordpress": "^6 || ^7",
					"requires_plugins": {
						"plugin": "^1.0.0"
					}
				}
			},
			"basic": {
				"extends": "default",
				"test_command": "npx playwright test --grep @basic"
			}
		}
	}
}
JSON
		);

		// Execute test:config --get-package foo.basic
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--get-package' => 'foo.basic' ] );

		// Decode the JSON output
		$output = json_decode( $tester->getDisplay(), true );

		// Assert the status code
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode(), 'Command should return success status' );

		// Assert the decoded JSON contains all expected fields
		$this->assertEquals( './tests/foo', $output['root_path'], 'Root path should match default configuration' );
		$this->assertEquals( 'npx playwright test --grep @basic', $output['test_command'], 'Test command should match basic configuration' );
		$this->assertEquals(
			[ 'ctrf' => './results/ctrf.json', 'allure' => './results/allure-results' ],
			$output['test_results'],
			'Test results should match default configuration'
		);
		$this->assertEquals( [ './bootstrap/mu-plugin.php' ], $output['mu_plugins'], 'MU plugins should match default configuration' );
		$this->assertEquals( [ 'QIT_FOO_DEBUG' => 'true' ], $output['env_vars'], 'Environment variables should match default configuration' );
		$this->assertEquals( [ 'MY_API_KEY' ], $output['required_secrets'], 'Required secrets should match default configuration' );
		$this->assertEquals(
			[ 'setup' => './bootstrap/setup.sh', 'teardown' => './bootstrap/teardown.sh' ],
			$output['lifecycle'],
			'Lifecycle scripts should match default configuration'
		);
		$this->assertEquals(
			[ 'wordpress' => '^6 || ^7', 'requires_plugins' => [ 'plugin' => '^1.0.0' ] ],
			$output['constraints'],
			'Constraints should match default configuration'
		);
	}
}

