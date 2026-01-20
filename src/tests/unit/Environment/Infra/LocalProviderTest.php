<?php

declare( strict_types=1 );

namespace QIT_CLI_Tests\Environment\Infra;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Infra\InfraProvider;
use QIT_CLI\Environment\Infra\LocalProvider;

class LocalProviderTest extends TestCase {
	public function test_implements_infra_provider(): void {
		$docker_mock = $this->createMock( Docker::class );
		$provider    = new LocalProvider( $docker_mock );

		$this->assertInstanceOf( InfraProvider::class, $provider );
	}

	public function test_get_type_returns_local(): void {
		$docker_mock = $this->createMock( Docker::class );
		$provider    = new LocalProvider( $docker_mock );

		$this->assertEquals( 'local', $provider->get_type() );
	}

	public function test_get_site_url_returns_url_from_env_info(): void {
		$docker_mock = $this->createMock( Docker::class );
		$provider    = new LocalProvider( $docker_mock );

		$env_info           = $this->createMock( E2EEnvInfo::class );
		$env_info->site_url = 'http://example.local';

		$this->assertEquals( 'http://example.local', $provider->get_site_url( $env_info ) );
	}

	public function test_get_site_url_returns_localhost_with_port_when_site_url_empty(): void {
		$docker_mock = $this->createMock( Docker::class );
		$provider    = new LocalProvider( $docker_mock );

		$env_info             = $this->createMock( E2EEnvInfo::class );
		$env_info->site_url   = '';
		$env_info->nginx_port = '8080';

		$this->assertEquals( 'http://localhost:8080', $provider->get_site_url( $env_info ) );
	}

	public function test_exec_delegates_to_docker_run_inside_docker(): void {
		$docker_mock = $this->createMock( Docker::class );

		$env_info = $this->createMock( E2EEnvInfo::class );
		$command  = [ 'wp', 'plugin', 'list' ];
		$env_vars = [ 'FOO' => 'bar' ];
		$timeout  = 120;

		$docker_mock->expects( $this->once() )
			->method( 'run_inside_docker' )
			->with( $env_info, $command, $env_vars, null, $timeout )
			->willReturn( 'plugin output' );

		$provider = new LocalProvider( $docker_mock );
		$result   = $provider->exec( $env_info, $command, $env_vars, $timeout );

		$this->assertEquals( 'plugin output', $result );
	}

	public function test_upload_delegates_to_docker_copy_into_docker(): void {
		$docker_mock = $this->createMock( Docker::class );

		$env_info     = $this->createMock( E2EEnvInfo::class );
		$local_path   = '/local/file.txt';
		$remote_path  = '/var/www/html/file.txt';

		$docker_mock->expects( $this->once() )
			->method( 'copy_into_docker' )
			->with( $env_info, $local_path, $remote_path );

		$provider = new LocalProvider( $docker_mock );
		$provider->upload( $env_info, $local_path, $remote_path );
	}

	public function test_download_delegates_to_docker_copy_from_docker(): void {
		$docker_mock = $this->createMock( Docker::class );

		$env_info    = $this->createMock( E2EEnvInfo::class );
		$remote_path = '/var/www/html/file.txt';
		$local_path  = '/local/file.txt';

		$docker_mock->expects( $this->once() )
			->method( 'copy_from_docker' )
			->with( $env_info, $remote_path, $local_path );

		$provider = new LocalProvider( $docker_mock );
		$provider->download( $env_info, $remote_path, $local_path );
	}

	public function test_is_healthy_returns_true_when_wp_cli_responds(): void {
		$docker_mock = $this->createMock( Docker::class );
		$env_info    = $this->createMock( E2EEnvInfo::class );

		$docker_mock->expects( $this->once() )
			->method( 'run_inside_docker' )
			->with( $env_info, [ 'wp', 'eval', 'echo "ok";' ], [], null, 10 )
			->willReturn( 'ok' );

		$provider = new LocalProvider( $docker_mock );
		$this->assertTrue( $provider->is_healthy( $env_info ) );
	}

	public function test_is_healthy_returns_false_when_wp_cli_fails(): void {
		$docker_mock = $this->createMock( Docker::class );
		$env_info    = $this->createMock( E2EEnvInfo::class );

		$docker_mock->expects( $this->once() )
			->method( 'run_inside_docker' )
			->willThrowException( new \RuntimeException( 'Docker error' ) );

		$provider = new LocalProvider( $docker_mock );
		$this->assertFalse( $provider->is_healthy( $env_info ) );
	}

	public function test_is_healthy_returns_false_when_response_is_not_ok(): void {
		$docker_mock = $this->createMock( Docker::class );
		$env_info    = $this->createMock( E2EEnvInfo::class );

		$docker_mock->expects( $this->once() )
			->method( 'run_inside_docker' )
			->willReturn( 'error: something went wrong' );

		$provider = new LocalProvider( $docker_mock );
		$this->assertFalse( $provider->is_healthy( $env_info ) );
	}

	public function test_provision_is_a_noop(): void {
		$docker_mock = $this->createMock( Docker::class );
		$env_info    = $this->createMock( E2EEnvInfo::class );

		// Docker should NOT be called during provision for LocalProvider
		// as provisioning is handled by Environment classes
		$docker_mock->expects( $this->never() )
			->method( 'run_inside_docker' );

		$provider = new LocalProvider( $docker_mock );
		$provider->provision( $env_info );

		// No exception means success
		$this->assertTrue( true );
	}

	public function test_destroy_is_a_noop(): void {
		$docker_mock = $this->createMock( Docker::class );
		$env_info    = $this->createMock( E2EEnvInfo::class );

		// Docker should NOT be called during destroy for LocalProvider
		// as cleanup is handled by Environment classes
		$docker_mock->expects( $this->never() )
			->method( 'run_inside_docker' );

		$provider = new LocalProvider( $docker_mock );
		$provider->destroy( $env_info );

		// No exception means success
		$this->assertTrue( true );
	}

	public function test_reset_imports_db_snapshot_when_exists(): void {
		$docker_mock   = $this->createMock( Docker::class );
		$env_info      = $this->createMock( E2EEnvInfo::class );
		$temp_dir      = sys_get_temp_dir() . '/qit-test-' . uniqid();
		$snapshot_path = $temp_dir . '/db-snapshot.sql';

		// Create temporary directory and snapshot file
		mkdir( $temp_dir, 0755, true );
		file_put_contents( $snapshot_path, 'SQL DUMP' );

		try {
			$env_info->temporary_env = $temp_dir;

			$docker_mock->expects( $this->once() )
				->method( 'run_inside_docker' )
				->with( $env_info, [ 'wp', 'db', 'import', '/qit/db-snapshot.sql' ], [], null, 300 );

			$provider = new LocalProvider( $docker_mock );
			$provider->reset( $env_info );
		} finally {
			// Cleanup
			unlink( $snapshot_path );
			rmdir( $temp_dir );
		}
	}

	public function test_reset_does_nothing_when_snapshot_does_not_exist(): void {
		$docker_mock = $this->createMock( Docker::class );
		$env_info    = $this->createMock( E2EEnvInfo::class );

		$env_info->temporary_env = '/nonexistent/path';

		// Docker should NOT be called when no snapshot exists
		$docker_mock->expects( $this->never() )
			->method( 'run_inside_docker' );

		$provider = new LocalProvider( $docker_mock );
		$provider->reset( $env_info );

		// No exception means success
		$this->assertTrue( true );
	}
}
