<?php

namespace QIT_CLI_Tests\Config;

use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TestConfigTest extends AbstractConfigTest {
	use MatchesSnapshots;

	public function test_foo_config_with_compatibility_tests() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"pre_test_build": { "command": "npm run build", "output": "./plugin.zip" },
	"tests": {
		"foo": {
			"default": {
				"env": "base",
				"test_package": "default",
				"compatibility_tests": ["plugin/basic:1.0.0", "plugin/rc", "plugin/stable"]
			}
		}
	},
	"environments": {
		"base": { "php_version": "8.2" }
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:foo' ) );
		$tester->execute( [ '--profile' => 'default' ] );
		$this->assertCommandOutput( $tester, '"env":"base"', Command::SUCCESS );
		$this->assertCommandOutput( $tester, '"test_package":"default"', Command::SUCCESS );
		$this->assertCommandOutput( $tester, '"compatibility_tests":["plugin\/basic:1.0.0","plugin\/rc","plugin\/stable"]', Command::SUCCESS );
	}

	public function test_bar_config_with_settings() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"bar": { "default": { "settings": { "skip": ["I can do this", "/I can do \\w+/" ] } } }
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:bar' ) );
		$tester->execute( [ '--profile' => 'default' ] );
		$this->assertCommandOutput( $tester, '"settings":{"skip":["I can do this","\/I can do \\\\w+\/"]}', Command::SUCCESS );
	}

	public function test_baz_config_with_pre_test_build() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"pre_test_build": { "command": "npm run build", "output": "./plugin.zip" },
	"tests": {
		"baz": {
			"legacy": {
				"env": "legacy",
				"pre_test_build": { "command": "npm run compile-assets && npm run build", "output": "./plugin.zip" }
			}
		}
	},
	"environments": {
		"legacy": { "php_version": "7.4" }
	}
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'run:baz' ) );
		$tester->execute( [ '--profile' => 'legacy' ] );
		$this->assertCommandOutput( $tester, '"env":"legacy"', Command::SUCCESS );
		$this->assertCommandOutput( $tester, '"pre_test_build":{"command":"npm run compile-assets && npm run build","output":"./plugin.zip"}', Command::SUCCESS );
	}
}

