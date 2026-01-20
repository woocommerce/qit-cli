<?php

declare( strict_types=1 );

namespace QIT_CLI\Environment\Infra;

use QIT_CLI\App;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\QITEnvInfo;

/**
 * Local infrastructure provider using Docker.
 * Wraps the existing Docker class to implement InfraProvider interface.
 */
class LocalProvider implements InfraProvider {
	protected Docker $docker;

	public function __construct( ?Docker $docker = null ) {
		$this->docker = $docker ?? App::make( Docker::class );
	}

	/**
	 * @inheritDoc
	 */
	public function provision( QITEnvInfo $env ): void {
		// Docker provisioning is handled by Environment classes.
		// This is called after docker-compose.yml is generated.
		// The actual 'docker compose up' happens in Environment::up().
	}

	/**
	 * @inheritDoc
	 */
	public function exec( QITEnvInfo $env, array $command, array $env_vars = [], int $timeout = 300 ): string {
		return $this->docker->run_inside_docker(
			$env,
			$command,
			$env_vars,
			null,
			$timeout
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_site_url( QITEnvInfo $env ): string {
		if ( ! empty( $env->site_url ) ) {
			return $env->site_url;
		}

		return "http://localhost:{$env->nginx_port}";
	}

	/**
	 * @inheritDoc
	 */
	public function reset( QITEnvInfo $env ): void {
		$snapshot_path = $env->temporary_env . '/db-snapshot.sql';

		if ( file_exists( $snapshot_path ) ) {
			$this->exec( $env, [ 'wp', 'db', 'import', '/qit/db-snapshot.sql' ] );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function destroy( QITEnvInfo $env ): void {
		// Docker cleanup is handled by Environment::down().
	}

	/**
	 * @inheritDoc
	 */
	public function upload( QITEnvInfo $env, string $local_path, string $remote_path ): void {
		$this->docker->copy_into_docker( $env, $local_path, $remote_path );
	}

	/**
	 * @inheritDoc
	 */
	public function download( QITEnvInfo $env, string $remote_path, string $local_path ): void {
		$this->docker->copy_from_docker( $env, $remote_path, $local_path );
	}

	/**
	 * @inheritDoc
	 */
	public function is_healthy( QITEnvInfo $env ): bool {
		try {
			$result = $this->exec( $env, [ 'wp', 'eval', 'echo "ok";' ], [], 10 );

			return trim( $result ) === 'ok';
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_type(): string {
		return 'local';
	}
}
