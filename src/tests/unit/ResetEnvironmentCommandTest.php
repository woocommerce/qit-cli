<?php

use QIT_CLI\Commands\Environment\ResetEnvironmentCommand;
use QIT_CLI\Commands\Environment\ResetEnvironmentHelper;
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
			foreach ( [ 'setup-complete.sql', 'metadata.json' ] as $file ) {
				if ( file_exists( $backup_dir . '/' . $file ) ) {
					unlink( $backup_dir . '/' . $file );
				}
			}
			if ( is_dir( $backup_dir ) ) {
				rmdir( $backup_dir );
			}
		}

		parent::tearDown();
	}

	public function test_staged_helper_uses_php_72_compatible_timing(): void {
		$script = ResetEnvironmentHelper::script();

		$this->assertStringNotContainsString( 'hrtime(', $script );
		$this->assertStringContainsString( '$started = microtime( true );', $script );
		$this->assertStringContainsString( 'function qit_reset_elapsed( float $phase_started ): float', $script );
		$this->assertStringContainsString( 'return round( microtime( true ) - $phase_started, 6 );', $script );
		$this->assertStringContainsString( "'snapshot_unavailable'", $script );
		$this->assertStringContainsString( "'snapshot_checksum_mismatch'", $script );
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

	public function test_legacy_json_result_contains_all_timed_phases(): void {
		$env_id   = 'qitenv-reset-json-legacy';
		$env_info = $this->create_environment( $env_id );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->once() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->exactly( 3 ) )
			->method( 'run_inside_docker' )
			->willReturn( '' );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id, '--json' => true ], [ 'interactive' => false ] );
		$result = json_decode( $tester->getDisplay(), true );

		$this->assertSame( Command::SUCCESS, $status );
		$this->assertSame( 'success', $result['status'] );
		$this->assertSame( 'copy_per_reset', $result['strategy'] );
		$this->assertNull( $result['failed_phase'] );
		$this->assertSame(
			[ 'environment_lookup', 'snapshot_copy', 'database_import', 'temporary_file_cleanup', 'object_cache_flush' ],
			array_keys( $result['phases'] )
		);
		foreach ( $result['phases'] as $phase ) {
			$this->assertSame( 'completed', $phase['status'] );
			$this->assertGreaterThanOrEqual( 0, $phase['seconds'] );
		}
		$this->assertGreaterThanOrEqual( 0, $result['total_seconds'] );
	}

	public function test_staged_reset_parses_last_json_line_and_uses_one_container_execution(): void {
		$env_id   = 'qitenv-reset-json-staged';
		$metadata = $this->staged_metadata();
		$env_info = $this->create_environment( $env_id, $metadata );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->never() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->once() )
			->method( 'run_inside_docker' )
			->with( $env_info, [ 'php', $metadata['reset_helper'], $metadata['container_snapshot'], $metadata['snapshot_sha256'] ] )
			->willReturn( "PHP Deprecated: noisy extension warning\n" . json_encode( [
				'status'       => 'success',
				'failed_phase' => null,
				'phases'       => [
					'database_import'    => [ 'status' => 'completed', 'seconds' => 0.75 ],
					'object_cache_flush' => [ 'status' => 'completed', 'seconds' => 0.25 ],
				],
			] ) . "\nDocker warning: noisy stderr output" );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id, '--json' => true ], [ 'interactive' => false ] );
		$result = json_decode( $tester->getDisplay(), true );

		$this->assertSame( Command::SUCCESS, $status );
		$this->assertSame( 'container_staged', $result['strategy'] );
		$this->assertEquals( [ 'status' => 'skipped', 'seconds' => 0.0 ], $result['phases']['snapshot_copy'] );
		$this->assertEquals( [ 'status' => 'skipped', 'seconds' => 0.0 ], $result['phases']['temporary_file_cleanup'] );
		$this->assertSame( 0.75, $result['phases']['database_import']['seconds'] );
		$this->assertSame( 0.25, $result['phases']['object_cache_flush']['seconds'] );
	}

	public function test_staged_helper_success_without_cache_phase_returns_structured_failure(): void {
		$env_id   = 'qitenv-reset-json-staged-incomplete-cache';
		$metadata = $this->staged_metadata();
		$env_info = $this->create_environment( $env_id, $metadata );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->never() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->once() )
			->method( 'run_inside_docker' )
			->willReturn( json_encode( [
				'status'       => 'success',
				'failed_phase' => null,
				'phases'       => [
					'database_import' => [ 'status' => 'completed', 'seconds' => 0.75 ],
				],
			] ) );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id, '--json' => true ], [ 'interactive' => false ] );
		$result = json_decode( $tester->getDisplay(), true );

		$this->assertSame( Command::FAILURE, $status );
		$this->assertSame( 'failed', $result['status'] );
		$this->assertSame( 'object_cache_flush', $result['failed_phase'] );
		$this->assertSame( 'failed', $result['phases']['object_cache_flush']['status'] );
		$this->assertStringContainsString( 'without completing object cache flush', $result['message'] );
	}

	public function test_human_output_does_not_claim_incomplete_cache_flush_succeeded(): void {
		$env_id   = 'qitenv-reset-human-staged-incomplete-cache';
		$metadata = $this->staged_metadata();
		$env_info = $this->create_environment( $env_id, $metadata );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->never() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->once() )
			->method( 'run_inside_docker' )
			->willReturn( json_encode( [
				'status'       => 'success',
				'failed_phase' => null,
				'phases'       => [
					'database_import'    => [ 'status' => 'completed', 'seconds' => 0.75 ],
					'object_cache_flush' => [ 'status' => 'not_started', 'seconds' => 0.0 ],
				],
			] ) );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id ], [ 'interactive' => false ] );

		$this->assertSame( Command::FAILURE, $status );
		$this->assertStringContainsString( 'without completing object cache flush', $tester->getDisplay() );
		$this->assertStringNotContainsString( 'WordPress object cache flushed.', $tester->getDisplay() );
	}

	public function test_missing_staged_snapshot_uses_checksum_verified_legacy_fallback(): void {
		$env_id                      = 'qitenv-reset-json-staged-missing';
		$metadata                    = $this->staged_metadata();
		$metadata['snapshot_sha256'] = hash( 'sha256', '-- test backup' );
		$env_info                    = $this->create_environment( $env_id, $metadata );
		$docker                      = $this->createMock( Docker::class );
		$docker->expects( $this->once() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->exactly( 4 ) )
			->method( 'run_inside_docker' )
			->willReturnCallback( static function ( E2EEnvInfo $actual_env, array $command ) use ( $env_info ): string {
				self::assertSame( $env_info, $actual_env );
				if ( $command[0] === 'php' ) {
					return json_encode( [
						'status'       => 'failed',
						'failed_phase' => 'database_import',
						'failure_code'  => 'snapshot_unavailable',
						'message'      => 'The staged database snapshot is missing or unreadable.',
						'phases'       => [
							'database_import'    => [ 'status' => 'failed', 'seconds' => 0.01 ],
							'object_cache_flush' => [ 'status' => 'not_started', 'seconds' => 0.0 ],
						],
					] );
				}

				return '';
			} );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id, '--json' => true ], [ 'interactive' => false ] );
		$result = json_decode( $tester->getDisplay(), true );

		$this->assertSame( Command::SUCCESS, $status );
		$this->assertSame( 'success', $result['status'] );
		$this->assertSame( 'copy_per_reset', $result['strategy'] );
		foreach ( $result['phases'] as $phase ) {
			$this->assertSame( 'completed', $phase['status'] );
		}
	}

	public function test_missing_staged_snapshot_fails_when_host_backup_checksum_does_not_match(): void {
		$env_id   = 'qitenv-reset-json-staged-missing-host-mismatch';
		$metadata = $this->staged_metadata();
		$env_info = $this->create_environment( $env_id, $metadata );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->never() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->once() )
			->method( 'run_inside_docker' )
			->willReturn( json_encode( [
				'status'       => 'failed',
				'failed_phase' => 'database_import',
				'failure_code'  => 'snapshot_unavailable',
				'message'      => 'The staged database snapshot is missing or unreadable.',
				'phases'       => [
					'database_import'    => [ 'status' => 'failed', 'seconds' => 0.01 ],
					'object_cache_flush' => [ 'status' => 'not_started', 'seconds' => 0.0 ],
				],
			] ) );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id, '--json' => true ], [ 'interactive' => false ] );
		$result = json_decode( $tester->getDisplay(), true );

		$this->assertSame( Command::FAILURE, $status );
		$this->assertSame( 'failed', $result['status'] );
		$this->assertSame( 'copy_per_reset', $result['strategy'] );
		$this->assertSame( 'snapshot_copy', $result['failed_phase'] );
		$this->assertSame( 'failed', $result['phases']['snapshot_copy']['status'] );
		$this->assertStringContainsString( 'host backup failed checksum verification', $result['message'] );
	}

	public function test_staged_checksum_failure_is_structured_and_does_not_fall_back(): void {
		$env_id   = 'qitenv-reset-json-checksum-failure';
		$metadata = $this->staged_metadata();
		$env_info = $this->create_environment( $env_id, $metadata );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->never() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->once() )
			->method( 'run_inside_docker' )
			->willReturn( json_encode( [
				'status'       => 'failed',
				'failed_phase' => 'database_import',
				'failure_code'  => 'snapshot_checksum_mismatch',
				'message'      => 'The staged database snapshot failed checksum verification.',
				'phases'       => [
					'database_import'    => [ 'status' => 'failed', 'seconds' => 0.01 ],
					'object_cache_flush' => [ 'status' => 'not_started', 'seconds' => 0.0 ],
				],
			] ) );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id, '--json' => true ], [ 'interactive' => false ] );
		$result = json_decode( $tester->getDisplay(), true );

		$this->assertSame( Command::FAILURE, $status );
		$this->assertSame( 'failed', $result['status'] );
		$this->assertSame( 'container_staged', $result['strategy'] );
		$this->assertSame( 'database_import', $result['failed_phase'] );
		$this->assertStringContainsString( 'checksum verification', $result['message'] );
		$this->assertSame( 'not_started', $result['phases']['object_cache_flush']['status'] );
	}

	public function test_legacy_cleanup_failure_is_fail_closed_and_skips_cache_flush(): void {
		$env_id   = 'qitenv-reset-json-cleanup-failure';
		$env_info = $this->create_environment( $env_id );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->once() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->exactly( 2 ) )
			->method( 'run_inside_docker' )
			->willReturnCallback( static function ( E2EEnvInfo $actual_env, array $command ): string {
				if ( $command[0] === 'rm' ) {
					throw new \RuntimeException( 'Unable to remove temporary snapshot.' );
				}
				return '';
			} );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id, '--json' => true ], [ 'interactive' => false ] );
		$result = json_decode( $tester->getDisplay(), true );

		$this->assertSame( Command::FAILURE, $status );
		$this->assertSame( 'temporary_file_cleanup', $result['failed_phase'] );
		$this->assertSame( 'failed', $result['phases']['temporary_file_cleanup']['status'] );
		$this->assertSame( 'not_started', $result['phases']['object_cache_flush']['status'] );
	}

	public function test_legacy_import_failure_is_structured_and_still_cleans_up(): void {
		$env_id   = 'qitenv-reset-json-import-failure';
		$env_info = $this->create_environment( $env_id );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->once() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->exactly( 2 ) )
			->method( 'run_inside_docker' )
			->willReturnCallback( static function ( E2EEnvInfo $actual_env, array $command ): string {
				if ( $command[0] === 'sh' ) {
					throw new \RuntimeException( 'Import rejected.' );
				}
				return '';
			} );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id, '--json' => true ], [ 'interactive' => false ] );
		$result = json_decode( $tester->getDisplay(), true );

		$this->assertSame( Command::FAILURE, $status );
		$this->assertSame( 'database_import', $result['failed_phase'] );
		$this->assertSame( 'failed', $result['phases']['database_import']['status'] );
		$this->assertSame( 'completed', $result['phases']['temporary_file_cleanup']['status'] );
		$this->assertSame( 'not_started', $result['phases']['object_cache_flush']['status'] );
	}

	public function test_legacy_cache_failure_is_structured(): void {
		$env_id   = 'qitenv-reset-json-cache-failure';
		$env_info = $this->create_environment( $env_id );
		$docker   = $this->createMock( Docker::class );
		$docker->expects( $this->once() )
			->method( 'copy_into_docker' );
		$docker->expects( $this->exactly( 3 ) )
			->method( 'run_inside_docker' )
			->willReturnCallback( static function ( E2EEnvInfo $actual_env, array $command ): string {
				if ( $command === [ 'sh', '-c', 'cd /var/www/html && wp cache flush --quiet' ] ) {
					throw new \RuntimeException( 'Cache flush rejected.' );
				}
				return '';
			} );

		$tester = $this->create_command_tester( $env_info, $docker );
		$status = $tester->execute( [ 'env_id' => $env_id, '--json' => true ], [ 'interactive' => false ] );
		$result = json_decode( $tester->getDisplay(), true );

		$this->assertSame( Command::FAILURE, $status );
		$this->assertSame( 'object_cache_flush', $result['failed_phase'] );
		$this->assertSame( 'failed', $result['phases']['object_cache_flush']['status'] );
		$this->assertStringContainsString( 'Cache flush rejected', $result['message'] );
	}

	/** @param array<string,mixed> $metadata */
	private function create_environment( string $env_id, array $metadata = [] ): E2EEnvInfo {
		$backup_dir         = sys_get_temp_dir() . '/qit-env-backups/' . $env_id;
		$this->backup_dirs[] = $backup_dir;
		if ( ! is_dir( $backup_dir ) ) {
			mkdir( $backup_dir, 0777, true );
		}
		file_put_contents( $backup_dir . '/setup-complete.sql', '-- test backup' );
		if ( ! empty( $metadata ) ) {
			file_put_contents( $backup_dir . '/metadata.json', json_encode( $metadata ) );
		}

		$env_info                        = new E2EEnvInfo();
		$env_info->env_id                = $env_id;
		$env_info->status                = 'started';
		$env_info->temporary_env          = sys_get_temp_dir() . '/' . $env_id;

		return $env_info;
	}

	/** @return array<string,string> */
	private function staged_metadata(): array {
		return [
			'snapshot_strategy'  => 'container_staged',
			'container_snapshot' => '/tmp/qit-env-reset/setup-complete.sql',
			'snapshot_sha256'    => str_repeat( 'a', 64 ),
			'reset_helper'       => '/qit/bin/qit-env-reset.php',
		];
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
