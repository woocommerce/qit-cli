<?php

namespace QIT_CLI\Environment;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function QIT_CLI\is_ci;

class Docker {
	/** @var OutputInterface */
	protected $output;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	public function find_docker(): string {
		$docker = 'docker';

		$docker_version = trim( shell_exec( $docker . ' --version' ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec

		if ( $docker_version ) {
			return $docker;
		} else {
			throw new \RuntimeException( 'Could not find docker' );
		}
	}

	public function enter_environment( EnvInfo $env_info, string $docker_image = 'php', string $terminal = '/bin/bash' ): void {
		$docker_image = $env_info->get_docker_container( $docker_image );

		$process = new Process( [ $this->find_docker(), 'exec', '-it', $docker_image, $terminal ] );
		$process->setTty( ! is_ci() );

		if ( $this->output->isVerbose() ) {
			// Print the command that will run.
			$this->output->writeln( $process->getCommandLine() );
		}

		$process->run( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'Failed to enter environment' );
		}
	}

	/**
	 * @param EnvInfo              $env_info
	 * @param array<scalar>        $command The Command to run, in a Symfony Process format.
	 * @param array<string,scalar> $env_vars Any additional env vars to set in the process.
	 * @param string               $user The user to run the command as.
	 * @param string               $image The docker image to run the command in.
	 *
	 * @return void
	 */
	public function run_inside_docker( EnvInfo $env_info, array $command, array $env_vars = [], ?string $user = null, int $timeout = 300, string $image = 'php' ): void {
		$docker_image   = $env_info->get_docker_container( $image );
		$docker_command = [ $this->find_docker(), 'exec' ];

		if ( ! is_ci() ) {
			$docker_command = array_merge( $docker_command, [ '-it' ] );
		}

		// Check if user is not set and try to set it from ENV vars or posix functions.
		if ( is_null( $user ) ) {
			$env_user  = getenv( 'QIT_DOCKER_USER' );
			$env_group = getenv( 'QIT_DOCKER_GROUP' );

			if ( $env_user !== false && $env_group !== false ) {
				// Use user and group from environment variables.
				$user = $env_user . ':' . $env_group;
			} elseif ( function_exists( 'posix_getuid' ) && function_exists( 'posix_getgid' ) ) {
				// Use user and group from posix functions.
				$user = posix_getuid() . ':' . posix_getgid();
			} else {
				// Output warning if neither method is available.
				$this->output->writeln( '<info>To run the environment with the correct permissions, please install the posix extension on PHP, or set QIT_DOCKER_USER/QIT_DOCKER_GROUP env vars.</info>' );
			}
		}

		if ( ! is_null( $user ) ) {
			$docker_command[] = '--user';
			$docker_command[] = $user;

			if ( $user === '0:0' ) {
				$env_vars = array_merge( $env_vars, [
					'WP_CLI_ALLOW_ROOT' => true,
				] );
			}
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
		$process->setTty( ! is_ci() );
		$process->setTimeout( $timeout );
		$process->setIdleTimeout( $timeout );

		if ( $this->output->isVerbose() ) {
			// Print the command that will run.
			$this->output->writeln( $process->getCommandLine() );
		}

		$process->run( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'Failed to run command inside docker' );
		}
	}

	/**
	 * @return array<string> The docker-compose command to use, in a Symfony Process format.
	 */
	public function find_docker_compose(): array {
		// Find out if it's docker-compose (v1) or docker compose (v2).
		$docker_compose_v2 = 'docker compose';
		$docker_compose_v1 = 'docker-compose';

		$v1_version = trim( shell_exec( $docker_compose_v2 . ' --version' ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
		$v2_version = trim( shell_exec( $docker_compose_v1 . ' --version' ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec

		if ( $v1_version ) {
			return [ 'docker-compose' ];
		} elseif ( $v2_version ) {
			return [ 'docker', 'compose' ];
		} else {
			throw new \RuntimeException( 'Could not find docker-compose or docker compose' );
		}
	}
}
