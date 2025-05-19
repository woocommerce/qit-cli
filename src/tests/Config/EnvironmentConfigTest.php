<?php

namespace QIT_CLI_Tests\Config;

use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class EnvironmentConfigTest extends AbstractConfigTest {
	use MatchesSnapshots;

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

