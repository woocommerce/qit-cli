<?php

namespace QIT_CLI\Ngrok;

use QIT_CLI\Environment\Docker;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class NgrokRunner {
	/** @var Docker */
	protected $docker;

	/** @var NgrokConfig */
	protected $ngrok_config;

	/** @var OutputInterface */
	protected $output;

	protected static $ngrok_process;

	public function __construct( Docker $docker, NgrokConfig $ngrok_config, OutputInterface $output ) {
		$this->docker       = $docker;
		$this->ngrok_config = $ngrok_config;
		$this->output       = $output;
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

		$process->start( function ( $type, $buffer ) use ( &$output ) {
			$this->output->write( $buffer );
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
			$attempts ++;
		}

		$process->stop();

		if ( ! $process->isSuccessful() ) {
			$exit_code    = $process->getExitCode();
			$output       = $process->getOutput();
			$error_output = $process->getErrorOutput();

			$message = "Command not successul (Container exited with $exit_code).";

			$message .= "\n" . $error_output;

			$message .= "\n" . 'Command that was executed: ' . $process->getCommandLine();

			throw new \RuntimeException( $message );
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

		$process->start( function ( $type, $buffer ) use ( &$output ) {
			$this->output->write( $buffer );
		} );

		static::$ngrok_process = $process;

		register_shutdown_function( [ __CLASS__, 'stop_ngrok' ] );

		return $domain;
	}

	public static function stop_ngrok(): void {
		if ( static::$ngrok_process instanceof Process ) {
			if ( static::$ngrok_process->isRunning() ) {
				static::$ngrok_process->stop();
			}
		}
	}
}