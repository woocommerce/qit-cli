<?php

use QIT_CLI\App;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI_Tests\QITTestCase;

class EnvironmentMonitorTest extends QITTestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	protected function make_env_info( string $identifier = 'foo', int $env_id = 1234567890 ): EnvInfo {
		$env_info                = new EnvInfo();
		$env_info->type          = 'e2e';
		$env_info->temporary_env = "/path/to/temporary-envs/$identifier";
		$env_info->env_id        = $env_id;
		$env_info->created_at    = 1708728299;
		$env_info->status        = 'pending';

		return $env_info;
	}

	public function test_environment_add() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );

		$this->assertMatchesJsonSnapshot( $environment_monitor->get() );
	}

	public function test_environment_adds_multiple() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );
		$environment_monitor->environment_added_or_updated( $this->make_env_info( 'bar' ) );

		$this->assertMatchesJsonSnapshot( $environment_monitor->get() );
	}

	public function test_environment_stopped() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );
		$environment_monitor->environment_stopped( $this->make_env_info() );

		$this->assertMatchesJsonSnapshot( $environment_monitor->get() );
	}

	public function test_environment_updated() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );

		$updated         = $this->make_env_info();
		$updated->status = 'running';
		$environment_monitor->environment_added_or_updated( $updated );

		$this->assertMatchesJsonSnapshot( $environment_monitor->get() );
	}

	public function test_environment_stops_with_multiple() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );

		$updated         = $this->make_env_info( 'bar' );
		$updated->status = 'running';
		$environment_monitor->environment_added_or_updated( $updated );

		$environment_monitor->environment_stopped( $this->make_env_info( 'foo', 4567890 ) );

		$this->assertMatchesJsonSnapshot( $environment_monitor->get() );
	}

	public function test_makes_env_info_from_path() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );

		$env_info = $environment_monitor->get_env_info_by_path( '/path/to/temporary-envs/foo' );

		$this->assertMatchesJsonSnapshot( (array) $env_info );
	}

	public function test_makes_env_info_from_id() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$env_info            = $this->make_env_info();
		$environment_monitor->environment_added_or_updated( $env_info );

		$env_info2 = $environment_monitor->get_env_info_by_id( $env_info->env_id );

		$this->assertEquals( $env_info, $env_info2 );
	}
}
