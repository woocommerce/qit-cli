<?php

namespace QIT_CLI_Tests\Config;

use QIT_CLI\QITConfig;

class ValidationTest extends AbstractConfigTest {
	public function test_invalid_top_level_key_type() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"tests": "not_an_array"
}
JSON
		);
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Tests must be an array.' );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_get_compatibility_tests_invalid() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"tests": {
		"e2e": {
			"default": {
				"compatibility_tests": {
					"invalid": "item"
				}
			}
		}
	}
}
JSON
		);
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Compatibility test in 'e2e:default' must be a string." );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_invalid_custom_test_package_env() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"custom_test_packages": {
		"foo": {
			"bad_env": {
				"env": "not_an_array"
			}
		}
	}
}
JSON
		);
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "env in custom test package 'foo:bad_env' must be an array." );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_circular_inheritance() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"custom_test_packages": {
		"foo": {
			"foo": { "root_path": "./tests/foo", "test_command": "npx playwright test", "extends": "bar" },
			"bar": { "extends": "foo", "test_command": "npx playwright test --grep @bad" }
		}
	}
}
JSON
		);
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Deep inheritance not allowed in custom test package 'foo': 'bar' cannot extend another configuration." );
		new QITConfig( 'qit.json', $this->application );
	}
}

