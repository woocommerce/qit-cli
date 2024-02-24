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

	public function find_docker_compose() {
		// Find out if it's docker-compose (v1) or docker compose (v2)
		$dockerComposeV2 = 'docker compose';
		$dockerComposeV1 = 'docker-compose';

		$dockerComposeV2Version = trim( shell_exec( $dockerComposeV2 . ' --version' ) );
		$dockerComposeV1Version = trim( shell_exec( $dockerComposeV1 . ' --version' ) );

		if ( $dockerComposeV2Version ) {
			return $dockerComposeV2;
		} elseif ( $dockerComposeV1Version ) {
			return $dockerComposeV1;
		} else {
			throw new \RuntimeException( 'Could not find docker-compose or docker compose' );
		}
	}
}