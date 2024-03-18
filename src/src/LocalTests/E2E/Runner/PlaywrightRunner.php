<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\Commands\TestRuns\RunE2ECommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\LocalTests\E2E\E2ETestManager;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use function QIT_CLI\open_in_browser;

class PlaywrightRunner extends E2ERunner {
	public function run_test( E2EEnvInfo $env_info, string $plugin, TestResult $test_result, string $test_mode ): void {
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

	protected function run_no_codegen( string $test_mode, E2EEnvInfo $env_info, string $plugin, TestResult $test_result ): void {
		$playwright_container_name = 'qit_playwright_' . uniqid();
		$test_to_run               = $env_info->tests[ $plugin ]['path_in_host'];

		// Generate playwright-config.
		$process = new Process( [ PHP_BINARY, $env_info->temporary_env . '/playwright-config-generator.php' ] );
		$process->setEnv( [
			'BASE_URL' => $env_info->site_url,
			'SAVE_AS'  => $env_info->temporary_env . 'qit-playwright.config.js',
		] );

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( $process->getCommandLine() );
		}

		$process->run( function ( $type, $buffer ) {
			if ( $this->output->isVerbose() || $type === Process::ERR ) {
				$this->output->write( $buffer );
			}
		} );

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( "Failed to generate docker-compose.yml. Output:\n" . $process->getOutput() . $process->getErrorOutput() );
		}

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
			'-v',
			$env_info->temporary_env . 'qit-playwright.config.js:/home/pwuser/qit-playwright.config.js',
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
			'cd /home/pwuser && ' .
			'npm install @playwright/test@1.42.0 playwright@1.42.0 && npx playwright install chromium && ' .
			'ls -la && cat /home/pwuser/qit-playwright.config.js && ls -la tests &&' .
			"npx playwright test $options --config /home/pwuser/qit-playwright.config.js",
		] );

		$playwright_process = new Process( $playwright_args );

		$playwright_process->start( function ( $type, $out ) use ( $playwright_container_name ) {
			if ( strpos( $out, 'Listening on' ) !== false ) {
				$out = $this->get_playwright_headed_output( $playwright_container_name );
			}
			// Clear the current line and move the cursor to the beginning.
			echo "\r\033[K";

			// Print the output from the process.
			$this->output->write( $out );

			$this->output->writeln( '' );

			// Redraw the prompt.
			$this->output->write( 'Press Enter to terminate...' );
		} );

		RunE2ECommand::press_enter_to_terminate_callback( $playwright_process );
	}

	protected function run_codegen( E2EEnvInfo $env_info, string $plugin, TestResult $test_result ): void {
		$io = new SymfonyStyle( App::make( InputInterface::class ), $this->output );

		$io->note( 'To run the Playwright Codegen, please ensure Playwright is installed on your machine.' );

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

		$io->text( [
			'Please run Playwright Codegen locally using the URLs above. After generating tests:',
			'  - Remove all hardcoded URLs from the generated tests.',
			'  - Assume that Playwright\'s "baseURL" is set on the environment your tests will run.',
			'  - Ensure your tests are flexible and follows good practices on choosing selectors.',
		] );

		$io->newLine();

		$io->text( 'For detailed instructions and best practices, please refer to our Codegen guide: https://qit.woo.com/docs/codegen' );
		$io->text( 'When you are done writing tests, return here and press Enter to shut down the environment.' );
		$io->success( 'Run Playwright Codegen from your computer now.' );

		$io->ask( '' ); // Wait for user to press Enter.
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
		$get_port_process = new Process( [ App::make( Docker::class )->find_docker(), 'port', $playwright_container_name, '8086' ] );
		$get_port_process->run();
		if ( ! $get_port_process->isSuccessful() ) {
			throw new \RuntimeException( 'Could not get mapped port for Playwright container.' );
		}

		// Extract the host port.
		$parts     = explode( ':', trim( $get_port_process->getOutput() ) );
		$host_port = end( $parts );

		open_in_browser( sprintf( 'http://localhost:%s', $host_port ) );

		return sprintf( 'Playwright UI is available at http://localhost:%s', $host_port );
	}
}
