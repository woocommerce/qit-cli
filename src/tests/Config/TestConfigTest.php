<?php

namespace QIT_CLI_Tests\Config;

use QIT_CLI\App;
use PHPUnit\Framework\TestCase;
use QIT_CLI_Tests\BarTestCommand;
use QIT_CLI_Tests\BazTestCommand;
use QIT_CLI_Tests\FooTestCommand;
use QIT_CLI_Tests\TestConfigCommand;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TestConfigTest extends TestCase {
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

	protected function assertCommandOutput( CommandTester $tester, string $expectedOutput, int $expectedStatus ): void {
		$this->assertStringContainsString( $expectedOutput, $tester->getDisplay() );
		$this->assertEquals( $expectedStatus, $tester->getStatusCode() );
	}

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

