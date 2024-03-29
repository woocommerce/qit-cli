<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Environment\Environments\EnvInfo;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function QIT_CLI\is_windows;
use function QIT_CLI\use_tty;

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

	public function enter_environment( EnvInfo $env_info, string $docker_image = 'php', string $terminal = '/bin/bash', string $user = '', bool $dev_mode = false ): void {
		$docker_image = $env_info->get_docker_container( $docker_image );

		$command = [ $this->find_docker(), 'exec', '-i' ];

		if ( $dev_mode ) {
			$this->run_inside_docker( $env_info, [ '/bin/sh', '-c', 'mkdir -p /qit/cache/apk && apk update --cache-dir /qit/cache/apk && apk add --cache-dir /qit/cache/apk --progress --verbose bash less vim' ], [], '0:0', 600, $docker_image );
			$command[] = '-e';
			$command[] = 'PAGER=less';
			$command[] = '-e';
			$command[] = 'LESS=-R';
		} else {
			// Use 'PAGER=more' because we use Alpine images that have a minimalist version of "less".
			$command[] = '-e';
			$command[] = 'PAGER=more';
		}

		if ( ! empty( $user ) ) {
			$command[] = '--user';
			$command[] = $user;
		}

		if ( use_tty() ) {
			$command = array_merge( $command, [ '-t' ] );
		}

		$command[] = $docker_image;
		$command[] = $terminal;

		$process = new Process( $command );
		$process->setTimeout( null );
		$process->setIdleTimeout( null );
		$process->setTty( use_tty() );

		if ( $this->output->isVerbose() ) {
			// Print the command that will run.
			$this->output->writeln( $process->getCommandLine() );
		}

		$process->run();
	}

	/**
	 * Retrieves the current user and group.
	 *
	 * This function attempts to get the user and group information from environment variables.
	 * If environment variables are not set, it falls back to using the posix_getuid and posix_getgid functions.
	 * Throws a RuntimeException if neither method can provide the user and group information.
	 *
	 * @return array{user: string|int, group: string|int} Array containing 'user' and 'group' keys.
	 * @throws \RuntimeException If the user and group cannot be determined.
	 */
	public static function get_user_and_group(): array {
		$env_user  = getenv( 'QIT_DOCKER_USER' );
		$env_group = getenv( 'QIT_DOCKER_GROUP' );

		if ( $env_user !== false && $env_group !== false ) {
			return [
				'user'  => $env_user,
				'group' => $env_group,
			];
		} elseif ( function_exists( 'posix_getuid' ) && function_exists( 'posix_getgid' ) ) {
			return [
				'user'  => posix_getuid(),
				'group' => posix_getgid(),
			];
		} else {
			throw new \RuntimeException( 'Could not find user and group' );
		}
	}

	/**
	 * @param EnvInfo              $env_info
	 * @param array<scalar>        $command The Command to run, in a Symfony Process format.
	 * @param array<string,scalar> $env_vars Any additional env vars to set in the process.
	 * @param string|null          $user The user to run the command as.
	 * @param int                  $timeout
	 * @param string               $image The docker image to run the command in.
	 *
	 * @return void
	 */
	public function run_inside_docker( EnvInfo $env_info, array $command, array $env_vars = [], ?string $user = null, int $timeout = 300, string $image = 'php', bool $force_output = false ): void {
		$docker_image   = $env_info->get_docker_container( $image );
		$docker_command = [ $this->find_docker(), 'exec' ];

		if ( $this->output->isVerbose() && use_tty() ) {
			$docker_command = array_merge( $docker_command, [ '-it' ] );
		}

		// Check if user is not set and try to set it from ENV vars or posix functions.
		if ( is_null( $user ) ) {
			try {
				$u    = static::get_user_and_group();
				$user = $u['user'] . ':' . $u['group'];
			} catch ( \RuntimeException $e ) {
				if ( ! is_windows() ) {
					$this->output->writeln( '<info>To run the environment with the correct permissions, please install the posix extension on PHP, or set QIT_DOCKER_USER/QIT_DOCKER_GROUP env vars.</info>' );
				}
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

		if ( getenv( 'QIT_DOCKER_RUN_TIMEOUT' ) !== false && is_numeric( getenv( 'QIT_DOCKER_RUN_TIMEOUT' ) ) ) {
			$timeout = getenv( 'QIT_DOCKER_RUN_TIMEOUT' );
		}

		$process = new Process( $docker_command );
		$process->setTty( $this->output->isVerbose() && use_tty() );
		$process->setTimeout( $timeout );
		$process->setIdleTimeout( $timeout );

		if ( $this->output->isVeryVerbose() ) {
			// Print the command that will run.
			$this->output->writeln( $process->getCommandLine() );
		}

		$process->run( function ( $type, $buffer ) use ( $force_output ) {
			if ( $this->output->isVerbose() || $force_output ) {
				$this->output->write( $buffer );
			}
		} );

		if ( ! $process->isSuccessful() ) {
			$output       = $process->getOutput();
			$error_output = $process->getErrorOutput();

			$message = 'Command not successul.';

			// If $force_output is true, we already printed this.
			if ( ! $force_output ) {
				if ( ! empty( $output ) ) {
					$message .= "\n" . $output;
				}

				if ( ! empty( $error_output ) ) {
					$message .= "\n" . $error_output;
				}
			}

			if ( $this->output->isVerbose() ) {
				$message .= "\n" . 'Command that was executed: ' . $process->getCommandLine();
			}

			throw new \RuntimeException( $message );
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
