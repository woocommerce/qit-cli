<?php

namespace QIT_CLI_Tests\Config;

use PHPUnit\Framework\TestCase;
use QIT_CLI\App;
use QIT_CLI_Tests\BarTestCommand;
use QIT_CLI_Tests\BazTestCommand;
use QIT_CLI_Tests\FooTestCommand;
use QIT_CLI_Tests\TestConfigCommand;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractConfigTest extends TestCase {
	use MatchesSnapshots;

	protected Application $application;
	protected array $files_to_clean = [ 'qit.json', 'custom.json' ];

	public function setUp(): void {
		require_once __DIR__ . '/test-config-classes.php';
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
}

