<?php

namespace QIT_CLI\Ngrok;

use QIT_CLI\Commands\NgrokCommand;
use QIT_CLI\Environment\Docker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class NgrokRunner {
	/** @var Docker */
	protected $docker;

	/** @var NgrokConfig */
	protected $ngrok_config;

	/** @var OutputInterface */
	protected $output;

	/** @var Process */
	protected static $ngrok_process;

	public function __construct( Docker $docker, NgrokConfig $ngrok_config, OutputInterface $output ) {
		$this->docker       = $docker;
		$this->ngrok_config = $ngrok_config;
		$this->output       = $output;
	}

	public function get_config(): NgrokConfig {
		return $this->ngrok_config;
	}

	public function test_ngrok_connection( string $token, string $domain ): string {
		$docker_command = [
			$this->docker->find_docker(),
			'run',
			'--net=host',
			'-e',
			"NGROK_AUTHTOKEN=$token",
			'ngrok/ngrok:latest',
			'http',
			"--domain=$domain",
			'80',
		];

		$process = new Process( $docker_command );
		$process->setTty( false );
		$process->setTimeout( 120 );
		$process->setIdleTimeout( 120 );

		if ( $this->output->isVeryVerbose() ) {
			// Print the command that will run.
			$this->output->writeln( $process->getCommandLine() );
		}

		$process->start( function ( $type, $buffer ) {
			if ( $this->output->isVeryVerbose() ) {
				$this->output->write( $buffer );
			}
		} );

		/*
		 * Then do something like this, but in PHP, try a few times waiting 100ms between each attempt up until it waits 10s.
		 * export WEBHOOK_URL="$(curl http://localhost:4040/api/tunnels | jq ".tunnels[0].public_url")"
		 */
		$webhook_url  = null;
		$attempts     = 0;
		$max_attempts = 100;
		$wait_time    = 100000; // 100ms in microseconds.

		while ( $attempts < $max_attempts ) {
			$response = @file_get_contents( 'http://localhost:4040/api/tunnels' );

			if ( ! empty( $response ) ) {
				$webhook_url = json_decode( $response, true );
				if ( isset( $webhook_url['tunnels'][0]['public_url'] ) ) {
					$webhook_url = $webhook_url['tunnels'][0]['public_url'];
					break;
				}
			}

			usleep( $wait_time );
			++$attempts;
		}

		$process->stop();

		if ( ! $process->isSuccessful() ) {
			$exit_code    = $process->getExitCode();
			$output       = $process->getOutput();
			$error_output = $process->getErrorOutput();

			if ( $this->output->isVeryVerbose() ) {
				$this->output->writeln( $process->getCommandLine() );
				$this->output->writeln( 'Exit code: ' . $exit_code );
				$this->output->writeln( $output );
				$this->output->writeln( $error_output );
			}

			throw new \RuntimeException( trim( $error_output ) );
		}

		if ( is_null( $webhook_url ) ) {
			throw new \RuntimeException( 'Could not get the Ngrok public URL.' );
		}

		return $webhook_url;
	}

	public function start_ngrok( int $local_port ): string {
		$ngrok_config = $this->ngrok_config->get_ngrok_config();
		$token        = $ngrok_config['token'];
		$domain       = $ngrok_config['domain'];

		$docker_command = [
			$this->docker->find_docker(),
			'run',
			'--net=host',
			'--name=qit_ngrok',
			'-e',
			"NGROK_AUTHTOKEN=$token",
			'ngrok/ngrok:latest',
			'http',
			"--domain=$domain",
			$local_port,
		];

		$process = new Process( $docker_command );
		$process->setTty( false );
		$process->setTimeout( 120 );
		$process->setIdleTimeout( 120 );

		if ( $this->output->isVeryVerbose() ) {
			// Print the command that will run.
			$this->output->writeln( $process->getCommandLine() );
		}

		$process->start( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );

		static::$ngrok_process = $process;

		/*
		 * If we are using this environment in a test flow,
		 * the upstream test flow will be responsible for
		 * stopping ngrok when the test is done.
		 */
		if ( getenv( 'QIT_UP_AND_TEST' ) !== '1' ) {
			register_shutdown_function( [ __CLASS__, 'stop_ngrok' ] );
		}

		return $domain;
	}

	public static function stop_ngrok(): void {
		if ( static::$ngrok_process instanceof Process ) {
			if ( static::$ngrok_process->isRunning() ) {
				static::$ngrok_process->stop();
			}
		}
	}

	public function is_ngrok_running(): bool {
		$docker_command = [
			$this->docker->find_docker(),
			'ps',
			'--filter',
			'name=qit_ngrok',
			'--format',
			'{{.Names}}',
		];

		$process = new Process( $docker_command );
		$process->setTty( false );
		$process->setTimeout( 30 );
		$process->setIdleTimeout( 30 );

		$process->run();

		if ( $process->isSuccessful() ) {
			$output = $process->getOutput();
			if ( ! empty( $output ) ) {
				return true;
			}
		}

		return false;
	}

	public static function ngrok_not_configured_warning( InputInterface $input, OutputInterface $output ): void {
		$io = new SymfonyStyle( $input, $output );
		$io->setDecorated( true );
		$io->warning( sprintf( 'To run tests with a live site URL, please run "qit %s" to configure Ngrok first.', NgrokCommand::$defaultName ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
}
