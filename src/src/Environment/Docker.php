<?php

namespace QIT_CLI\Environment;

class Docker {
	public function find_docker() {
		$docker = 'docker';

		$dockerVersion = trim( shell_exec( $docker . ' --version' ) );

		if ( $dockerVersion ) {
			return $docker;
		} else {
			throw new \RuntimeException( 'Could not find docker' );
		}
	}

	public function run_on_docker( EnvInfo $env_info, array $command ) {

	}

	public function find_docker_compose(): array {
		// Find out if it's docker-compose (v1) or docker compose (v2)
		$docker_compose_v2 = 'docker compose';
		$docker_compose_v1 = 'docker-compose';

		$v1_version = trim( shell_exec( $docker_compose_v2 . ' --version' ) );
		$v2_version = trim( shell_exec( $docker_compose_v1 . ' --version' ) );

		if ( $v1_version ) {
			return [ 'docker-compose' ];
		} elseif ( $v2_version ) {
			return [ 'docker', 'compose' ];
		} else {
			throw new \RuntimeException( 'Could not find docker-compose or docker compose' );
		}
	}
}