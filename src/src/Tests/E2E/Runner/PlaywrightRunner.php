<?php

namespace QIT_CLI\Tests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\Commands\TestRuns\RunE2ECommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Tests\E2E\E2ETestManager;
use QIT_CLI\Tests\E2E\Result\TestResult;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class PlaywrightRunner extends E2ERunner {
	public function run_test( EnvInfo $env_info, string $plugin, TestResult $test_result, string $test_mode ): void {
		if ( ! file_exists( Config::get_qit_dir() . 'cache/playwright' ) ) {
			if ( ! mkdir( Config::get_qit_dir() . 'cache/playwright', 0755, true ) ) {
				throw new \RuntimeException( 'Could not create the custom tests directory: ' . Config::get_qit_dir() . 'cache/playwright' );
			}
		}

		if ( ! array_key_exists( $plugin, $env_info->tests ) || ! file_exists( $env_info->tests[ $plugin ]['path_in_host'] ) ) {
			throw new \RuntimeException( sprintf( 'No tests found for plugin %s', $plugin ) );
		}

		if ( $test_mode === E2ETestManager::$test_modes['codegen'] ) {
			$this->run_codegen( $env_info, $plugin, $test_result );
		} else {
			$this->run_no_codegen( $test_mode, $env_info, $plugin, $test_result );
		}
	}

	protected function run_no_codegen( string $test_mode, EnvInfo $env_info, string $plugin, TestResult $test_result ) {
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

		if ( $test_mode === 'headed' ) {
			$options = '--headed --ui-port=8086 --ui-host=0.0.0.0';
		} elseif ( $test_mode === 'ui' ) {
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

	protected function run_codegen( E2EEnvInfo $env_info, string $plugin, TestResult $test_result ) {
		$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );

		// Inform the user about the necessity of having Playwright installed
		$io->note( 'To run the Playwright Codegen, please ensure Playwright is installed on your machine.' );

		// Emphasize the site URL and related information
		$io->section( 'Site Information' );
		$info = [
			sprintf( 'URL: %s', $env_info->site_url ),
			sprintf( 'Admin URL: %s/wp-admin', $env_info->site_url ),
			'Admin Credentials: admin / password',
		];
		foreach ( $info as $line ) {
			$io->text( $line );
		}
		$io->newLine();

		// Instructions for Codegen
		$io->text( [
			'Please run Playwright Codegen locally using the URLs above. After generating tests:',
			'  - Remove all hardcoded URLs from the generated tests.',
			'  - Assume that Playwright\'s "baseURL" is set on the environment your tests will run.',
			'  - Ensure your tests are flexible and follows good practices on choosing selectors.',
		] );

		$io->newLine();

		// Link to the Codegen guide
		$io->text( 'For detailed instructions and best practices, please refer to our Codegen guide: https://qit.woo.com/docs/codegen' );

		// Confirmation question
		$io->text( 'When you are done writing tests, return here and press Enter to shut down the environment.' );

		// Ask the user to run Playwright Codegen
		$io->success( 'Run Playwright Codegen from your computer now.' );

		// Wait for user to press Enter
		$io->ask( '' ); // This will wait until the user presses Enter
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
