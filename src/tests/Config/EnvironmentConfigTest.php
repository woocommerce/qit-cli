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

class EnvironmentConfigTest extends TestCase {
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

	public function test_environment_config_retrieval() {
		$testCases = [
			'inheritance' => [
				'configData'     => [
					'slug'         => 'awesome-plugin',
					'type'         => 'plugin',
					'environments' => [
						'base'   => [ 'php_version' => '8.2', 'wordpress_version' => 'stable', 'plugins' => [ 'plugin' ] ],
						'legacy' => [ 'extends' => 'base', 'php_version' => '7.4', 'wordpress_version' => '6.1' ],
					],
				],
				'env'            => 'legacy',
				'expectedOutput' => json_encode( [ 'php_version' => '7.4', 'wordpress_version' => '6.1', 'plugins' => [ 'plugin' ] ] ),
				'expectedError'  => null,
				'expectedStatus' => Command::SUCCESS,
			],
			'missing'     => [
				'configData'     => [ 'slug' => 'awesome-plugin', 'type' => 'plugin' ],
				'env'            => 'non_existing',
				'expectedOutput' => null,
				'expectedError'  => "Configuration 'non_existing' not found in section.",
				'expectedStatus' => Command::FAILURE,
			],
		];

		foreach ( $testCases as $caseName => $case ) {
			file_put_contents( 'qit.json', <<<'JSON'
{
	"slug": "awesome-plugin",
	"type": "plugin",
	"environments": {
		"base": { "php_version": "8.2", "wordpress_version": "stable", "plugins": ["plugin"] },
		"legacy": { "extends": "base", "php_version": "7.4", "wordpress_version": "6.1" }
	}
}
JSON
			);
			$tester = new CommandTester( $this->application->find( 'test:config' ) );
			$tester->execute( [ '--get-environment' => $case['env'] ] );
			if ( $case['expectedOutput'] ) {
				$this->assertCommandOutput( $tester, $case['expectedOutput'], $case['expectedStatus'] );
			} elseif ( $case['expectedError'] ) {
				$this->assertCommandOutput( $tester, $case['expectedError'], $case['expectedStatus'] );
			}
		}
	}

	public function test_inheritance_nested_fields() {
		file_put_contents( 'qit.json', json_encode( [
			'slug'         => 'awesome-plugin',
			'type'         => 'plugin',
			'environments' => [
				'base'   => [
					'php_version'       => '8.2',
					'wordpress_version' => 'stable',
					'object_cache'      => true,
					'plugins'           => [ 'plugin', 'akismet' ],
					'env_vars'          => [ 'QIT_DEBUG' => 'true' ],
					'bootstrap'         => [
						[ 'slug' => 'akismet', 'test_package' => 'foo:helpers' ],
					],
					'volumes'           => [ './:/var/www/html/wp-content/plugins/awesome-plugin' ],
				],
				'legacy' => [
					'extends'           => 'base',
					'php_version'       => '7.4',
					'wordpress_version' => '6.1',
				],
			],
		] ) );
		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--get-environment' => 'legacy' ] );
		$this->assertCommandOutput( $tester, '"php_version":"7.4"', Command::SUCCESS );
		$this->assertCommandOutput( $tester, '"wordpress_version":"6.1"', Command::SUCCESS );
		$this->assertCommandOutput( $tester, '"plugins":["plugin","akismet"]', Command::SUCCESS );
		$this->assertCommandOutput( $tester, '"env_vars":{"QIT_DEBUG":"true"}', Command::SUCCESS );
		$this->assertCommandOutput( $tester, '"volumes":["./:/var/www/html/wp-content/plugins/awesome-plugin"]', Command::SUCCESS );
		$this->assertCommandOutput( $tester, '"bootstrap":[{"slug":"akismet","test_package":"foo:helpers"}]', Command::SUCCESS );
		$this->assertCommandOutput( $tester, '"object_cache":true', Command::SUCCESS );
	}
}

