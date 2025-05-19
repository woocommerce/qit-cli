<?php

namespace QIT_CLI_Tests\Config;

use QIT_CLI\QITConfig;
use Spatie\Snapshots\MatchesSnapshots;

class GroupConfigTest extends AbstractConfigTest {
	use MatchesSnapshots;

	public function test_get_group_tests() {
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
		},
		"bar": { "default": { "env": "base" } },
		"baz": { "basic": { "settings": { "level": 0 } } }
	},
	"groups": {
		"pre_release": {
			"foo": ["default"],
			"bar": ["default"],
			"baz": ["basic"]
		}
	},
	"environments": {
		"base": { "php_version": "8.2", "wordpress_version": "stable" }
	}
}
JSON
		);
		$config      = new QITConfig( 'qit.json', $this->application );
		$group_tests = $config->get_group_tests( 'pre_release' );
		$this->assertCount( 3, $group_tests );
		$this->assertEquals( [
			'type'    => 'foo',
			'profile' => 'default',
			'config'  => [
				'env'                 => 'base',
				'test_package'        => 'default',
				'compatibility_tests' => [ 'plugin/basic:1.0.0', 'plugin/rc', 'plugin/stable' ],
				'pre_test_build'      => [ 'command' => 'npm run build', 'output' => './plugin.zip' ],
			],
		], $group_tests[0] );
		$this->assertEquals( [
			'type'    => 'bar',
			'profile' => 'default',
			'config'  => [ 'env' => 'base', 'pre_test_build' => [ 'command' => 'npm run build', 'output' => './plugin.zip' ] ],
		], $group_tests[1] );
		$this->assertEquals( [
			'type'    => 'baz',
			'profile' => 'basic',
			'config'  => [ 'settings' => [ 'level' => 0 ], 'pre_test_build' => [ 'command' => 'npm run build', 'output' => './plugin.zip' ] ],
		], $group_tests[2] );
	}

	public function test_get_group_tests_invalid_ref() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"groups": { "invalid": ["foo"] }
}
JSON
		);
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Test type in group 'invalid' must be a string." );
		new QITConfig( 'qit.json', $this->application );
	}
}

