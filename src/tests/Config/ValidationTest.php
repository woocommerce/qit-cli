<?php

namespace QIT_CLI_Tests\Config;

use QIT_CLI\App;
use QIT_CLI\QITConfig;
use PHPUnit\Framework\TestCase;
use QIT_CLI_Tests\BarTestCommand;
use QIT_CLI_Tests\BazTestCommand;
use QIT_CLI_Tests\FooTestCommand;
use QIT_CLI_Tests\TestConfigCommand;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;

class ValidationTest extends TestCase {
	use MatchesSnapshots;

	protected Application $application;
	protected array $files_to_clean = [ 'qit.json' ];

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
		$this->files_to_clean = [ 'qit.json' ];
		parent::tearDown();
	}

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

	public function test_get_test_matrix_invalid() {
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
		$this->expectExceptionMessage( "Compatibility test in 'foo:default' must be a string." );
		new QITConfig( 'qit.json', $this->application );
	}

	public function test_invalid_custom_test_package_env_vars() {
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"custom_test_packages": {
		"foo": {
			"bad_env_vars": {
				"env_vars": "not_an_array"
			}
		}
	}
}
JSON
		);
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "env_vars in custom test package 'foo:bad_env_vars' must be an array." );
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

