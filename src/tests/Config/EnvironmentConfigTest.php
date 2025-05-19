<?php

namespace QIT_CLI_Tests\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class EnvironmentConfigTest extends AbstractConfigTest {
	public function test_environment_config_inheritance() {
		$configData = [
			'slug'         => 'awesome-plugin',
			'type'         => 'plugin',
			'environments' => [
				'base'   => [ 'php_version' => '8.2', 'wordpress_version' => 'stable', 'plugins' => [ 'plugin' ] ],
				'legacy' => [ 'extends' => 'base', 'php_version' => '7.4', 'wordpress_version' => '6.1' ],
			],
		];

		file_put_contents( 'qit.json', json_encode( $configData, JSON_PRETTY_PRINT ) );

		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--get-environment' => 'legacy' ] );

		$expectedOutput = json_encode( [
			'php_version'       => '7.4',
			'wordpress_version' => '6.1',
			'plugins'           => [ 'plugin' ]
		] );

		$this->assertCommandOutput( $tester, $expectedOutput, Command::SUCCESS );
	}

	public function test_missing_environment_config() {
		$configData = [
			'slug' => 'awesome-plugin',
			'type' => 'plugin'
		];

		file_put_contents( 'qit.json', json_encode( $configData, JSON_PRETTY_PRINT ) );

		$tester = new CommandTester( $this->application->find( 'test:config' ) );
		$tester->execute( [ '--get-environment' => 'non_existing' ] );

		$this->assertCommandOutput(
			$tester,
			"Configuration 'non_existing' not found in section 'environments'",
			Command::FAILURE
		);
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

		// Check that the command executed successfully
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );

		// Decode the JSON output for reliable assertions
		$output = $tester->getDisplay();
		$config = json_decode( $output, true );
		$this->assertNotNull( $config, 'Failed to decode JSON output' );

		// Assert the expected values
		$this->assertEquals( '7.4', $config['php_version'] );
		$this->assertEquals( '6.1', $config['wordpress_version'] );
		$this->assertEquals( [ 'plugin', 'akismet' ], $config['plugins'] );
		$this->assertEquals( [ 'QIT_DEBUG' => 'true' ], $config['env_vars'] );
		$this->assertEquals( [ './:/var/www/html/wp-content/plugins/awesome-plugin' ], $config['volumes'] );
		$this->assertEquals( [ [ 'slug' => 'akismet', 'test_package' => 'foo:helpers' ] ], $config['bootstrap'] );
		$this->assertTrue( $config['object_cache'] );
	}
}

