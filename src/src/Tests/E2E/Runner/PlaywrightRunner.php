<?php

namespace QIT_CLI\Tests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\Commands\TestRuns\RunE2ECommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Tests\E2E\Result\TestResult;
use Symfony\Component\Process\Process;

class PlaywrightRunner extends E2ERunner {
	public function run_test( EnvInfo $env_info, string $plugin, TestResult $test_result ): void {
		if ( ! file_exists( Config::get_qit_dir() . 'cache/playwright' ) ) {
			if ( ! mkdir( Config::get_qit_dir() . 'cache/playwright', 0755, true ) ) {
				throw new \RuntimeException( 'Could not create the custom tests directory: ' . Config::get_qit_dir() . 'cache/playwright' );
			}
		}

		if ( ! array_key_exists( $plugin, $env_info->tests ) || ! file_exists( $env_info->tests[ $plugin ]['path_in_host'] ) ) {
			throw new \RuntimeException( sprintf( 'No tests found for plugin %s', $plugin ) );
		}

		$modes = [
			'headless',
			'headed',
			'ui',
			'codegen',
		];

		$mode = 'codegen';

		if ( $mode === 'codegen' ) {
			$this->run_codegen( $env_info, $plugin, $test_result );
		} else {
			$this->run_no_codegen( $mode, $env_info, $plugin, $test_result );
		}
	}

	protected function run_no_codegen( string $mode, EnvInfo $env_info, string $plugin, TestResult $test_result ) {
		$playwright_container_name = 'qit_playwright_' . uniqid();
		$test_to_run               = $env_info->tests[ $plugin ]['path_in_host'];

		$playwright_args = [
			App::make( Docker::class )->find_docker(),
			'run',
			"--name=$playwright_container_name",
			"--network={$env_info->docker_network}",
			'--publish',
			'8086', // Expose the internal "8086" port to a random, free port in host.
			'--tty',
			'--rm',
			'--init',
			'--user',
			implode( ':', Docker::get_user_and_group() ),
			'-e',
			'PLAYWRIGHT_BROWSERS_PATH=/qit/cache/playwright',
			'-v',
			Config::get_qit_dir() . 'cache:/qit/cache',
			'--add-host=host.docker.internal:host-gateway',
		];

		$playwright_args = array_merge( $playwright_args, [
			'-v',
			$test_to_run . ':/home/pwuser/tests/',
		] );

		if ( $mode === 'headed' ) {
			$options = '--ui --ui-port=8086 --ui-host=0.0.0.0';
		} elseif ( $mode === 'ui' ) {
			$options = '--ui --ui-port=8086 --ui-host=0.0.0.0';
		} else {
			$options = '';
		}

		$playwright_args = array_merge( $playwright_args, [
			'mcr.microsoft.com/playwright:v1.41.0-jammy',
			'sh',
			'-c',
			"cd /home/pwuser && " .
			"npm install @playwright/test@1.42.0 playwright@1.42.0 && npx playwright install chromium && " .
			"npx playwright test $options",
		] );

		$playwright_process = new Process( $playwright_args );

		$playwright_process->start( function ( $type, $out ) use ( $playwright_container_name ) {
			if ( strpos( $out, 'Listening on' ) !== false ) {
				$out = $this->get_playwright_headed_output( $playwright_container_name );
			}
			// Clear the current line and move the cursor to the beginning
			echo "\r\033[K";

			// Print the output from the process
			$this->output->write( $out );

			$this->output->writeln( '' );

			// Redraw the prompt
			$this->output->write( 'Press Enter to terminate...' );
		} );

		RunE2ECommand::press_enter_to_terminate_callback( $playwright_process );
	}

	protected function run_codegen( EnvInfo $env_info, string $plugin, TestResult $test_result ) {
		$dockerfileDir  = Config::get_qit_dir() . 'cache/docker/playwright';
		$dockerfilePath = $dockerfileDir . '/Dockerfile';

		// Check if Dockerfile exists and create if not
		if ( ! file_exists( $dockerfilePath ) ) {
			if ( ! mkdir( $dockerfileDir, 0755, true ) ) {
				throw new \RuntimeException( 'Could not create the directory for Dockerfile: ' . $dockerfileDir );
			}

			if ( ! file_put_contents( $dockerfilePath, $this->create_docker_file() ) ) {
				throw new \RuntimeException( 'Could not create the Dockerfile for Playwright' );
			}

			if ( ! file_put_contents( $dockerfileDir . '/start-vnc.sh', $this->create_docker_entrypoint() ) ) {
				throw new \RuntimeException( 'Could not create the entrypoint for Playwright' );
			}
		}

		// Docker build
		$imageName    = 'custom_playwright_image';
		$buildProcess = new Process( [
			App::make( Docker::class )->find_docker(),
			'build',
			'-t',
			$imageName,
			$dockerfileDir,
		] );
		$buildProcess->setTimeout( 600 );
		$buildProcess->run( function ( $type, $out ) {
			$this->output->writeln( $out );
		} );

		if ( ! $buildProcess->isSuccessful() ) {
			throw new \RuntimeException( 'Docker image build failed: ' . $buildProcess->getErrorOutput() );
		}

		$playwright_container_name = 'qit_playwright_' . uniqid();
		$test_to_run               = $env_info->tests[ $plugin ]['path_in_host'];

		$playwright_args = [
			App::make( Docker::class )->find_docker(),
			'run',
			"--name=$playwright_container_name",
			"--network={$env_info->docker_network}",
			'--publish',
			'8086', // Expose the internal "8086" port to a random, free port in host.
			'--publish',
			'5900',
			'--tty',
			'--rm',
			'--init',
			'--user',
			implode( ':', Docker::get_user_and_group() ),
			'-e',
			'PLAYWRIGHT_BROWSERS_PATH=/qit/cache/playwright',
			'-v',
			Config::get_qit_dir() . 'cache:/qit/cache',
			'--add-host=host.docker.internal:host-gateway',
		];

		$playwright_args = array_merge( $playwright_args, [
			'-v',
			$test_to_run . ':/home/pwuser/tests/',
		] );

		$playwright_args = array_merge( $playwright_args, [
			$imageName,
			'sh',
			'-c',
			"cd /home/pwuser && " .
			"npm install @playwright/test@1.42.0 playwright@1.42.0 && npx playwright install chromium && " .
			"DISPLAY=:0 ./node_modules/.bin/playwright codegen",
		] );

		$playwright_process = new Process( $playwright_args );

		$playwright_process->start( function ( $type, $out ) use ( $playwright_container_name ) {
			if ( strpos( $out, 'Listening on' ) !== false ) {
				$out = $this->get_playwright_headed_output( $playwright_container_name );
			}
			// Clear the current line and move the cursor to the beginning
			echo "\r\033[K";

			// Print the output from the process
			$this->output->write( $out );

			$this->output->writeln( '' );

			// Redraw the prompt
			$this->output->write( 'Press Enter to terminate...' );
		} );

		RunE2ECommand::press_enter_to_terminate_callback( $playwright_process );

		return;

		sleep( 5 );

		$novnc_container_name = 'qit_novnc_' . uniqid();

		$novnc_args = [
			App::make( Docker::class )->find_docker(),
			'run',
			"--name=$novnc_container_name",
			"--network={$env_info->docker_network}",
			'--publish',
			'6080',
			'-e',
			'AUTOCONNECT=true',
			'-e',
			"VNC_SERVER=$playwright_container_name:5900",
			'-e',
			'VIEW_ONLY=false',
			'bonigarcia/novnc:1.1.0',
		];

		$novnc_process = new Process( $novnc_args );
		$novnc_process->start( function ( $type, $out ) {
			$this->output->write( $out );
		} );

		RunE2ECommand::press_enter_to_terminate_callback( $novnc_process );
	}

	protected function create_docker_file(): string {
		return <<<'DOCKER'
# Use an Ubuntu base image
FROM mcr.microsoft.com/playwright:v1.42.0-jammy

# Set noninteractive installation to avoid dialogs during package installations
ARG DEBIAN_FRONTEND=noninteractive

# Update and install necessary packages
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        x11vnc \
        xvfb \
        fluxbox \
        xterm && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Set up the environment
ENV DISPLAY=:0 \
    RESOLUTION=1280x800

# Add a script to start Xvfb, window manager, and x11vnc
COPY start-vnc.sh /usr/local/bin/start-vnc
RUN chmod +x /usr/local/bin/start-vnc

# Expose the VNC port
EXPOSE 5900

# Set the entrypoint to our VNC startup script
ENTRYPOINT ["start-vnc"]
DOCKER;
	}

	protected function create_docker_entrypoint(): string {
		return <<<'SHELL'
#!/bin/sh
Xvfb :0 -screen 0 ${RESOLUTION}x24 &
fluxbox &
x11vnc -display :0 -nopw -forever -create
SHELL;
	}

	/**
	 * This function will intercept the "Listening at http://0.0.0.0:8086" message
	 * that Playwright prints and replace it with our own message.
	 *
	 * This is because "0.0.0.0:8086" is the internal address inside the container.
	 *
	 * To access it in the host, the user would access something like:
	 * - http://localhost:<RANDOM_PORT>
	 *
	 * Where RANDOM_PORT is defined by Docker when creating the Playwright container,
	 * once it finds the first free available port in the host.
	 *
	 * We, then, replace that message with our own, exposing to the user the URL
	 * that he needs to access in the host to see the Playwright UI.
	 *
	 * @param string $playwright_container_name The Playwright container name.
	 *
	 * @return string The patched message to show to the user.
	 */
	protected function get_playwright_headed_output( string $playwright_container_name ): string {
		// Get the mapped host port for container's 8086 port.
		$docker           = App::make( Docker::class )->find_docker();
		$get_port_process = new Process( [ $docker, 'port', $playwright_container_name, '8086' ] );
		$get_port_process->run();
		if ( ! $get_port_process->isSuccessful() ) {
			throw new \RuntimeException( 'Could not get mapped port for Playwright container.' );
		}

		// Extract the host port.
		$parts     = explode( ':', trim( $get_port_process->getOutput() ) );
		$host_port = end( $parts );

		return sprintf( 'Playwright UI is available at http://localhost:%s', $host_port );
	}
}
