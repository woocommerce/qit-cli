<?php

use QIT_CLI\Commands\Environment\ResetEnvironmentCommand;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ResetEnvironmentCommandTest extends \QIT_CLI_Tests\QITTestCase {
	/** @var array<string> */
	private array $backup_dirs = [];

	public function tearDown(): void {
		foreach ( $this->backup_dirs as $backup_dir ) {
			if ( file_exists( $backup_dir . '/setup-complete.sql' ) ) {
				unlink( $backup_dir . '/setup-complete.sql' );
			}
			if ( is_dir( $backup_dir ) ) {
				rmdir( $backup_dir );
			}
		}

		parent::tearDown();
	}

	public function test_flushes_object_cache_from_wordpress_directory(): void {
		$env_id   = 'qitenv-reset-cache-success';
		$env_info = $this->create_environment( $env_id );
		$commands = [];
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->once() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->exactly( 3 ) )
			->method( 'run_inside_docker' )
			->willReturnCallback( static function ( E2EEnvInfo $actual_env, array $command ) use ( $env_info, &$commands ): string {
				self::assertSame( $env_info, $actual_env );
				$commands[] = $command;
				return '';
			} );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id ], [ 'interactive' => false ] );

		$this->assertSame( Command::SUCCESS, $status );
		$this->assertSame( [ 'sh', '-c', 'cd /var/www/html && wp cache flush --quiet' ], $commands[2] );
		$this->assertStringContainsString( 'WordPress object cache flushed.', $tester->getDisplay() );
	}

	public function test_fails_reset_when_object_cache_cannot_be_flushed(): void {
		$env_id   = 'qitenv-reset-cache-failure';
		$env_info = $this->create_environment( $env_id );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->once() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->exactly( 3 ) )
			->method( 'run_inside_docker' )
			->willReturnCallback( static function ( E2EEnvInfo $actual_env, array $command ) use ( $env_info ): string {
				self::assertSame( $env_info, $actual_env );
				if ( $command === [ 'sh', '-c', 'cd /var/www/html && wp cache flush --quiet' ] ) {
					throw new \RuntimeException( 'Persistent object cache rejected the flush.' );
				}

				return '';
			} );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id ], [ 'interactive' => false ] );

		$this->assertSame( Command::FAILURE, $status );
		$this->assertStringContainsString( 'Object-cache flush failed after database restore', $tester->getDisplay() );
		$this->assertStringNotContainsString( 'WordPress object cache flushed.', $tester->getDisplay() );
	}

	private function create_environment( string $env_id ): E2EEnvInfo {
		$backup_dir         = sys_get_temp_dir() . '/qit-env-backups/' . $env_id;
		$this->backup_dirs[] = $backup_dir;
		if ( ! is_dir( $backup_dir ) ) {
			mkdir( $backup_dir, 0777, true );
		}
		file_put_contents( $backup_dir . '/setup-complete.sql', '-- test backup' );

		$env_info                        = new E2EEnvInfo();
		$env_info->env_id                = $env_id;
		$env_info->status                = 'started';
		$env_info->temporary_env          = sys_get_temp_dir() . '/' . $env_id;

		return $env_info;
	}

	private function create_command_tester( E2EEnvInfo $env_info, Docker $docker ): CommandTester {
		$monitor = $this->createMock( EnvironmentMonitor::class );
		$monitor->expects( $this->once() )
			->method( 'get_env_info_by_id' )
			->with( $env_info->env_id )
			->willReturn( $env_info );

		return new CommandTester( new ResetEnvironmentCommand( $monitor, $docker ) );
	}
}
