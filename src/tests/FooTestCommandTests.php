<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class FooTestCommandTests extends QITTestCase {
	use MatchesSnapshots;

	protected Application $application;

	public function setUp(): void {
		parent::setUp();
		$this->application = new Application();
		$this->application->add( new FooTestCommand() );
		$this->application->add( App::make( \QIT_CLI\Commands\CustomTests\RunE2ECommand::class ) );
	}

	public function tearDown(): void {
		if ( file_exists( 'qit.json' ) ) {
			unlink( 'qit.json' );
		}
		parent::tearDown();
	}

	public function test_generates_foo_test_config_with_default_profile() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'foo' => [
					'default' => [ 'param' => 'value' ]
				]
			]
		] ) );
		$command = new FooTestCommand();
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$this->assertStringContainsString( 'Running foo test profile: default with param: default', $tester->getDisplay() );
		$this->assertStringContainsString( 'Test config: {"param":"value"}', $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}

	public function test_generates_foo_test_config_with_custom_profile() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'foo' => [
					'custom' => [ 'param' => 'custom_value' ]
				]
			]
		] ) );
		$command = new FooTestCommand();
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [ '--profile' => 'custom' ] );
		$this->assertStringContainsString( 'Running foo test profile: custom with param: default', $tester->getDisplay() );
		$this->assertStringContainsString( 'Test config: {"param":"custom_value"}', $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}

	public function test_handles_missing_profile() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'foo' => [
					'default' => [ 'param' => 'value' ]
				]
			]
		] ) );
		$command = new FooTestCommand();
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [ '--profile' => 'non_existing' ] );
		$this->assertStringContainsString( "Test profile 'non_existing' not found for test type 'foo'.", $tester->getDisplay() );
		$this->assertEquals( Command::FAILURE, $tester->getStatusCode() );
	}

	public function test_overrides_param() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'foo' => [
					'default' => [ 'param' => 'value' ]
				]
			]
		] ) );
		$command = new FooTestCommand();
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [ '--param' => 'custom' ] );
		$this->assertStringContainsString( 'Running foo test profile: default with param: custom', $tester->getDisplay() );
		$this->assertStringContainsString( 'Test config: {"param":"value"}', $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}

	public function test_handles_missing_test_config() {
		file_put_contents( 'qit.json', json_encode( [ 'other' => 'data' ] ) );
		$command = new FooTestCommand();
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$this->assertStringContainsString( 'Running foo test profile: default with param: default', $tester->getDisplay() );
		$this->assertStringContainsString( 'Test config: []', $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}

	public function test_invalid_test_profile_key() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'foo' => [
					'default' => [ 'invalid_key' => 'value' ]
				]
			]
		] ) );
		$command = new FooTestCommand();
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$this->assertMatchesTextSnapshot( $tester->getDisplay() );
		$this->assertEquals( Command::FAILURE, $tester->getStatusCode() );
	}

	public function test_valid_settings_key() {
		file_put_contents( 'qit.json', json_encode( [
			'tests' => [
				'foo' => [
					'default' => [
						'param'    => 'value',
						'settings' => [ 'skip' => [ 'test1' ] ]
					]
				]
			]
		] ) );
		$command = new FooTestCommand();
		$command->setApplication( $this->application );
		$tester = new CommandTester( $command );
		$tester->execute( [] );
		$this->assertStringContainsString( 'Running foo test profile: default with param: default', $tester->getDisplay() );
		$this->assertStringContainsString( 'Test config: {"param":"value","settings":{"skip":["test1"]}}', $tester->getDisplay() );
		$this->assertEquals( Command::SUCCESS, $tester->getStatusCode() );
	}
}