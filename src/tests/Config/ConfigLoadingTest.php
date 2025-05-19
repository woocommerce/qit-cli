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

class ConfigLoadingTest extends TestCase {
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
		file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin"
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--output-config' => true ] );
		$this->assertCommandOutput( $tester, 'Config: {"slug":"awesome-plugin","type":"plugin"}', Command::SUCCESS );
	}

	public function test_handles_missing_config_file() {
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--output-config' => true ] );
		$this->assertCommandOutput( $tester, 'Config: []', Command::SUCCESS );
	}

	public function test_custom_config_file() {
		file_put_contents( 'custom.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin"
}
JSON
		);
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--config' => 'custom.json', '--output-config' => true ] );
		$this->assertCommandOutput( $tester, 'Config: {"slug":"awesome-plugin","type":"plugin"}', Command::SUCCESS );
	}

	public function test_invalid_json() {
		file_put_contents( 'qit.json', '{invalid json}' );
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [] );
		$this->assertCommandOutput( $tester, 'Invalid qit.json format. Must be a JSON object.', Command::FAILURE );
	}
}

