<?php

namespace QIT_CLI_Tests\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class FooTestRunCommandTest extends AbstractConfigTest {
	public function test_uses_cli_param() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"foo": {
			"default": {
				"param": "value"
			}
		}
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [ '--profile' => 'default', '--param' => 'custom' ] );
		$this->assertCommandOutput( $tester, 'Running foo test profile: default with param: custom', Command::SUCCESS );
		$this->assertCommandOutput( $tester, 'Test config: {"param":"value"}', Command::SUCCESS );
	}

	public function test_uses_config_param() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"foo": {
			"default": {
				"param": "value"
			}
		}
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [ '--profile' => 'default' ] );
		$this->assertCommandOutput( $tester, 'Running foo test profile: default with param: default', Command::SUCCESS );
		$this->assertCommandOutput( $tester, 'Test config: {"param":"value"}', Command::SUCCESS );
	}

	public function test_uses_default_param() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"foo": {
			"default": []
		}
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [ '--profile' => 'default' ] );
		$this->assertCommandOutput( $tester, 'Running foo test profile: default with param: default', Command::SUCCESS );
		$this->assertCommandOutput( $tester, 'Test config: []', Command::SUCCESS );
	}

	public function test_cli_param_when_config_lacks_param() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"foo": {
			"default": {
				"other_key": "other_value"
			}
		}
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [ '--profile' => 'default', '--param' => 'custom' ] );
		$this->assertCommandOutput( $tester, "Invalid key 'other_key' in profile 'foo:default'", Command::FAILURE );
	}

	public function test_handles_missing_profile() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"foo": {
			"default": {
				"param": "value"
			}
		}
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [ '--profile' => 'non_existing' ] );
		$this->assertCommandOutput( $tester, "Test profile 'non_existing' not found for test type 'foo'.", Command::FAILURE );
	}

	public function test_invalid_config_key() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"foo": {
			"default": {
				"invalid_key": "value"
			}
		}
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [ '--profile' => 'default' ] );
		$this->assertMatchesTextSnapshot( $tester->getDisplay() );
		$this->assertEquals( Command::FAILURE, $tester->getStatusCode() );
	}

	public function test_uses_custom_profile() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"foo": {
			"custom": {
				"param": "custom_value"
			}
		}
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [ '--profile' => 'custom' ] );
		$this->assertCommandOutput( $tester, 'Running foo test profile: custom with param: default', Command::SUCCESS );
		$this->assertCommandOutput( $tester, 'Test config: {"param":"custom_value"}', Command::SUCCESS );
	}

	public function test_handles_missing_test_config() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin"
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [] );
		$this->assertCommandOutput( $tester, "Test profile 'default' not found for test type 'foo'", Command::FAILURE );
	}

	public function test_handles_invalid_top_level_key() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"other": "data"
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [] );
		$this->assertCommandOutput( $tester, "Unknown top-level key 'other' in configuration.", Command::FAILURE );
	}

	public function test_valid_settings_key() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"foo": {
			"default": {
				"param": "value",
				"settings": {
					"skip": ["test1"]
				}
			}
		}
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [ '--profile' => 'default' ] );
		$this->assertCommandOutput( $tester, 'Running foo test profile: default with param: default', Command::SUCCESS );
		$this->assertCommandOutput( $tester, 'Test config: {"param":"value","settings":{"skip":["test1"]}}', Command::SUCCESS );
	}
}