<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use QIT_CLI\Commands\Environment\RestartEnvironmentCommand;
use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use QIT_CLI\Commands\Environment\DownEnvironmentCommand;
use QIT_CLI\App;

class RestartEnvironmentTest extends TestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	private $application;
	private $restart_command_tester;

	protected function setUp(): void {
		$this->application = new Application();

		$this->application->add( App::make( UpEnvironmentCommand::class ) );
		$this->application->add( App::make( DownEnvironmentCommand::class ) );
		$this->application->add( App::make( RestartEnvironmentCommand::class ) );

		$this->restart_command_tester = new CommandTester( $this->application->find( RestartEnvironmentCommand::getDefaultName() ) );
	}

	public function testRestartEnvironment() {
		$this->restart_command_tester->execute( [], [ 'verbosity' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE ] );

		$this->assertMatchesSnapshot( $this->restart_command_tester->getDisplay() );
	}

	public function testRestartEnvironmentWithOptions() {
		App::make( UpEnvironmentCommand::class )->run( new ArrayInput( [
			'--php_version' => '8.2',
		] ), new NullOutput() );
		$this->restart_command_tester->execute( [], [ 'verbosity' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE ] );

		$this->assertMatchesSnapshot( $this->restart_command_tester->getDisplay() );
	}

	public function testRestartEnvironmentWithMultipleOptions() {
		App::make( UpEnvironmentCommand::class )->run( new ArrayInput( [
			'--php_version'         => '8.1',
			'--wordpress_version'   => 'rc',
			'--woocommerce_version' => 'stable',
		] ), new NullOutput() );
		$this->restart_command_tester->execute( [], [ 'verbosity' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE ] );

		$this->assertMatchesSnapshot( $this->restart_command_tester->getDisplay() );
	}

	public function testRestartEnvironmentMultipleTimes() {
		App::make( UpEnvironmentCommand::class )->run( new ArrayInput( [
			'--php_version'         => '8.1',
			'--wordpress_version'   => 'rc',
			'--woocommerce_version' => 'stable',
		] ), new NullOutput() );

		$this->restart_command_tester->execute( [], [ 'verbosity' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE ] );

		App::make( UpEnvironmentCommand::class )->run( new ArrayInput( [
			'--php_version'         => '8.0',
			'--wordpress_version'   => 'stable',
			'--woocommerce_version' => 'stable',
		] ), new NullOutput() );

		$this->restart_command_tester->execute( [], [ 'verbosity' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE ] );

		$this->assertMatchesSnapshot( $this->restart_command_tester->getDisplay() );
	}
}
