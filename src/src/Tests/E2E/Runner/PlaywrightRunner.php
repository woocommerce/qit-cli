<?php

namespace QIT_CLI\Tests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\TestRuns\RunE2ECommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Tests\E2E\Result\TestResult;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ExecutableFinder;
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

	protected function run_codegen( E2EEnvInfo $env_info, string $plugin, TestResult $test_result ) {
		/*
		 * If running Codegen, the user needs to run "playwright" from host.
		 * So let's:
		 * - Tell this to the user.
		 * - Print the site URL he should use ($env_info->site_url).
		 * - Tell him to remove all hardcoded URLs from the generated tests.
		 * - Point him to our Codegen guide (https://qit.woo.com/docs/codegen)
		 */
		// Inform the user about the necessity of having Playwright installed
		// Inform the user about the necessity of having Playwright installed
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
			'Please run Playwright Codegen using the URLs above. After generating tests:',
			'  - Remove all hardcoded URLs from the generated tests.',
			'  - Assume that Playwright\'s "baseURL" is set on the environment your tests will run.',
			'  - Ensure your tests are flexible and follows good practices on choosing selectors.',
		] );

		$io->newLine();

		// Link to the Codegen guide
		$io->text( 'For detailed instructions and best practices, please refer to our Codegen guide: https://qit.woo.com/docs/codegen' );

		$playwright = App::make( Cache::class )->get( 'playwright_command_host' );

		if ( ! $playwright ) {
			/*
			 * Ask the user how we can run Playwright, eg "npx playwright" or just "playwright",
			 * when they answer, we will validate it by trying to run "--version" on it and getting
			 * what we expect.
			 */
			$question = new ConfirmationQuestion( 'Do you have Playwright installed on your machine? (yes/no) ', false );
			$playwright = ( new QuestionHelper() )->ask( App::make( InputInterface::class ), $this->output, $question );
			// If user answers no, tell him that he needs it to run "codegen".
			if ( ! $playwright ) {
				$io->error( 'You need to have Playwright installed to run "codegen".' );
				$io->text( 'Please install Playwright and run this command again.' );
				return;
			} else {
				// Ask them how they run it:
				$question = 'How do you run Playwright on your machine? (eg: "npx playwright" or "playwright") ';
				$playwright = $io->ask( $question );
				// Validate the command
				$playwright_version_process = new Process( [ $playwright, '--version' ] );
				$playwright_version_process->run();
				if ( ! $playwright_version_process->isSuccessful() ) {
					$io->error( sprintf( 'Could not run "%s --version". Please ensure Playwright is installed and try again.', $playwright ) );
					return;
				}
			}
		}

		// Ask the user to run Playwright Codegen
		$io->success( sprintf( 'Run Playwright Codegen from your computer now, example: "npx playwright codegen %s"', $env_info->site_url ) );

		// Confirmation question
		$io->text( 'When you are done writing tests, return here and press Enter to shut down the environment.' );

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
