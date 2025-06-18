<?php

namespace QIT_CLI_Tests\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigLoadingTest extends AbstractConfigTest {
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

