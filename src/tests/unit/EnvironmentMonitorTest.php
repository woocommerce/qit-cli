<?php

use QIT_CLI\App;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI_Tests\QITTestCase;

class EnvironmentMonitorTest extends QITTestCase {
	use \Spatie\Snapshots\MatchesSnapshots;

	/** @var array<string> Directories to clean up after each test. */
	private array $created_dirs = [];

	protected function tearDown(): void {
		foreach ( $this->created_dirs as $dir ) {
			$this->recursive_rmdir( $dir );
		}
		$this->created_dirs = [];
		parent::tearDown();
	}

	protected function make_env_info( string $identifier = 'foo', string $env_id = '1234567890' ): EnvInfo {
		$temp_envs_dir = Environment::get_temp_envs_dir();
		$env_dir       = $temp_envs_dir . 'e2e-' . $identifier;

		if ( ! is_dir( $env_dir ) ) {
			mkdir( $env_dir, 0755, true );
			$this->created_dirs[] = $env_dir;
		}

		$env_info                = EnvInfo::from_array( [ 'environment' => 'e2e' ] );
		$env_info->temporary_env = $env_dir;
		$env_info->env_id        = $env_id;
		$env_info->created_at    = 1708728299;
		$env_info->status        = 'pending';

		return $env_info;
	}

	public function test_environment_add() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );

		$result = $environment_monitor->get();

		$this->assertCount( 1, $result );
		$this->assertArrayHasKey( '1234567890', $result );
		$this->assertSame( 'pending', $result['1234567890']->status );
	}

	public function test_environment_adds_multiple() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );
		$environment_monitor->environment_added_or_updated( $this->make_env_info( 'bar', '9876543210' ) );

		$result = $environment_monitor->get();

		$this->assertCount( 2, $result );
		$this->assertArrayHasKey( '1234567890', $result );
		$this->assertArrayHasKey( '9876543210', $result );
	}

	public function test_environment_stopped() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );
		$environment_monitor->environment_stopped( $this->make_env_info() );

		$result = $environment_monitor->get();

		$this->assertCount( 0, $result );
	}

	public function test_environment_updated() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );

		$updated         = $this->make_env_info();
		$updated->status = 'running';
		$environment_monitor->environment_added_or_updated( $updated );

		$result = $environment_monitor->get();

		$this->assertCount( 1, $result );
		$this->assertSame( 'running', $result['1234567890']->status );
	}

	public function test_environment_stops_with_multiple() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$environment_monitor->environment_added_or_updated( $this->make_env_info() );

		$bar         = $this->make_env_info( 'bar', '9876543210' );
		$bar->status = 'running';
		$environment_monitor->environment_added_or_updated( $bar );

		// Stop the first environment.
		$environment_monitor->environment_stopped( $this->make_env_info() );

		$result = $environment_monitor->get();

		$this->assertCount( 1, $result );
		$this->assertArrayHasKey( '9876543210', $result );
		$this->assertSame( 'running', $result['9876543210']->status );
	}

	public function test_makes_env_info_from_path() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$env_info            = $this->make_env_info();
		$environment_monitor->environment_added_or_updated( $env_info );

		$found = $environment_monitor->get_env_info_by_path( $env_info->temporary_env );

		$this->assertSame( $env_info->env_id, $found->env_id );
		$this->assertSame( $env_info->temporary_env, $found->temporary_env );
	}

	public function test_makes_env_info_from_id() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$env_info            = $this->make_env_info();
		$environment_monitor->environment_added_or_updated( $env_info );

		$found = $environment_monitor->get_env_info_by_id( $env_info->env_id );

		$this->assertSame( $env_info->env_id, $found->env_id );
	}

	public function test_env_info_persisted_to_disk() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$env_info            = $this->make_env_info();
		$environment_monitor->environment_added_or_updated( $env_info );

		$env_info_file = rtrim( $env_info->temporary_env, '/' ) . '/env_info.json';

		$this->assertFileExists( $env_info_file );

		$data = json_decode( file_get_contents( $env_info_file ), true );

		$this->assertSame( '1234567890', $data['env_id'] );
		$this->assertSame( 'pending', $data['status'] );
	}

	public function test_env_info_removed_from_disk_on_stop() {
		$environment_monitor = App::make( EnvironmentMonitor::class );
		$env_info            = $this->make_env_info();
		$environment_monitor->environment_added_or_updated( $env_info );

		$env_info_file = rtrim( $env_info->temporary_env, '/' ) . '/env_info.json';

		$this->assertFileExists( $env_info_file );

		$environment_monitor->environment_stopped( $env_info );

		$this->assertFalse( file_exists( $env_info_file ), 'env_info.json should be deleted after stop' );
	}

	public function test_directories_without_env_info_are_skipped() {
		$temp_envs_dir = Environment::get_temp_envs_dir();
		$orphan_dir    = $temp_envs_dir . 'e2e-orphan';

		mkdir( $orphan_dir, 0755, true );
		$this->created_dirs[] = $orphan_dir;

		$environment_monitor = App::make( EnvironmentMonitor::class );

		$result = $environment_monitor->get();

		$this->assertCount( 0, $result );
	}

	public function test_get_env_info_by_id_throws_for_missing() {
		$environment_monitor = App::make( EnvironmentMonitor::class );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Environment not found.' );

		$environment_monitor->get_env_info_by_id( 'nonexistent' );
	}
}
