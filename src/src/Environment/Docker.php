<?php

namespace QIT_CLI\Environment;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Docker {
	/** @var OutputInterface */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	public function find_docker() {
		$docker = 'docker';

		$docker_version = trim( shell_exec( $docker . ' --version' ) );

		if ( $docker_version ) {
			return $docker;
		} else {
			throw new \RuntimeException( 'Could not find docker' );
		}
	}

	public function run_inside_docker( EnvInfo $env_info, array $command, array $env_vars = [], string $user = '', string $image = 'php' ): void {
		$docker_image   = $env_info->get_docker_container( $image );
		$docker_command = [ $this->find_docker(), 'exec', '-it' ];

		if ( empty( $user ) ) {
			if ( function_exists( 'posix_getuid' ) && function_exists( 'posix_getuid' ) ) {
				$docker_command[] = '--user';
				$docker_command[] = posix_getuid() . ':' . posix_getgid();
			}
		} else {
			$docker_command[] = '--user';
			$docker_command[] = $user;
		}

		$env_vars = array_merge( $env_vars, [
			'QIT_ENV_ID' => $env_info->env_id,
		] );

		foreach ( $env_vars as $key => $value ) {
			$docker_command[] = '-e';
			$docker_command[] = "$key=$value";
		}

		$docker_command[] = $docker_image;
		$docker_command   = array_merge( $docker_command, $command );

		$process = new Process( $docker_command );
		$process->setTty( true );

		$process->run( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'Failed to run command inside docker' );
		}
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