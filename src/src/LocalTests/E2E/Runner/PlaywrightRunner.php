<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\Commands\TestRuns\RunE2ECommand;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use Symfony\Component\Process\Process;
use function QIT_CLI\open_in_browser;

class PlaywrightRunner extends E2ERunner {
	public function run_test( E2EEnvInfo $env_info, array $test_infos, TestResult $test_result, string $test_mode ): void {
		if ( ! file_exists( Config::get_qit_dir() . 'cache/playwright' ) ) {
			if ( ! mkdir( Config::get_qit_dir() . 'cache/playwright', 0755, true ) ) {
				throw new \RuntimeException( 'Could not create the custom tests directory: ' . Config::get_qit_dir() . 'cache/playwright' );
			}
		}

		if ( ! file_exists( Config::get_qit_dir() . 'cache/npm-playwright' ) ) {
			if ( ! mkdir( Config::get_qit_dir() . 'cache/npm-playwright', 0755, true ) ) {
				throw new \RuntimeException( 'Could not create the npm playwright cache directory: ' . Config::get_qit_dir() . 'cache/npm-playwright' );
			}
		}

		$playwright_container_name = "qit_env_playwright_{$env_info->env_id}";

		// Create results directory for this run.
		$results_dir = $test_result->get_results_dir();

		if ( ! file_exists( $results_dir ) ) {
			if ( ! mkdir( $results_dir, 0755, true ) ) {
				throw new \RuntimeException( sprintf( 'Could not create the results directory: %s', $results_dir ) );
			}
		}

		// Generate playwright-config.
		$process = new Process( [ PHP_BINARY, $env_info->temporary_env . '/playwright-config-generator.php' ] );
		$process->setEnv( [
			'BASE_URL'         => $env_info->site_url,
			'SAVE_AS'          => $env_info->temporary_env . 'qit-playwright.config.js',
			'TEST_RESULT_PATH' => $results_dir,
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
			'--rm',
			'--init',
			'--user',
			implode( ':', Docker::get_user_and_group() ),
			'-e',
			'npm_config_cache=/qit/cache/npm-playwright',
			'-e',
			'PLAYWRIGHT_BROWSERS_PATH=/qit/cache/playwright',
			'-v',
			Config::get_qit_dir() . 'cache:/qit/cache',
			'-v',
			$env_info->temporary_env . 'qit-playwright.config.js:/home/pwuser/qit-playwright.config.js',
			'--add-host=host.docker.internal:host-gateway',
			'-v',
			$test_result->get_results_dir() . ':/qit/results',
		];

		foreach ( $test_infos as $test_to_run ) {
			$playwright_args = array_merge( $playwright_args, [
				'-v',
				$test_to_run['path_in_host'] . ":/home/pwuser/tests/{$test_to_run['extension']}",
			] );
		}

		if ( $test_mode === 'headed' ) {
			$options = '--headed --ui-port=8086 --ui-host=0.0.0.0';
		} elseif ( $test_mode === 'ui' ) {
			$options = '--ui --ui-port=8086 --ui-host=0.0.0.0';
		} else {
			$options = '';
		}

		$playwright_args = array_merge( $playwright_args, [
			'mcr.microsoft.com/playwright:jammy',
			'sh',
			'-c',
			'mkdir /qit/results/playwright && cd /home/pwuser && ' .
			'npm install @playwright/test@1.42.1 && npx playwright install chromium && ' .
			"npx playwright test $options --config /home/pwuser/qit-playwright.config.js --output /qit/results/playwright",
		] );

		$playwright_process = new Process( $playwright_args );

		$spinner_index = 0;
		$spinners      = [
			'⠋',
			'⠙',
			'⠹',
			'⠸',
			'⠼',
			'⠴',
			'⠦',
			'⠧',
			'⠇',
			'⠏',
		];

		$output_callback = function ( $type, $out ) use ( $playwright_container_name, $spinners, &$spinner_index ) {
			if ( strpos( $out, 'Listening on' ) !== false ) {
				$out = $this->get_playwright_headed_output( $playwright_container_name );
			}

			// Don't print this line.
			if ( strpos( $out, 'To open last HTML report' ) !== false ) {
				$out = '';
			}

			// Clear the current line and move the cursor to the beginning.
			echo "\r\033[K";

			// Print the output from the process.
			$this->output->write( $out );

			$this->output->writeln( '' );

			// Print the spinner.
			$spinner_index = ( $spinner_index + 1 ) % count( $spinners ); // phpcs:ignore Squiz.Operators.IncrementDecrementUsage.Found
			$spinner       = $spinners[ $spinner_index ];

			// Redraw the prompt.
			$this->output->write( "$spinner Test running... (To abort, press Enter)" );
		};

		$playwright_process->start( $output_callback );

		// Poke the output callback to print the initial message.
		$output_callback( Process::OUT, '' );

		// This will block the test until it finishes... If we get past here, it means the test is done.
		RunE2ECommand::press_enter_to_terminate_callback( $playwright_process );
		echo "\r\033[K"; // Remove "Test running..." line.


		if ( file_exists( $results_dir . '/report/index.html' ) ) {
			$results_process = new Process( [ PHP_BINARY, '-S', 'localhost:0', '-t', $results_dir . '/report' ] );
			$results_process->start( function ( $type, $output ) use ( &$results_process, &$spinners, &$spinner_index ) {
				if ( preg_match( '/Development Server \(http:\/\/localhost:(\d+)\) started/', $output, $matches ) ) {
					// Server started, extract the port
					$port = $matches[1];
					echo "\r\033[K"; // Clear the line
					echo "Report available on http://localhost:$port\n";

					// Open in browser
					open_in_browser( "http://localhost:$port" );
				}

				echo "\r\033[K"; // Clear the line
				echo "(To finish, press Enter)";
			} );

			RunE2ECommand::press_enter_to_terminate_callback( $results_process );
			echo "\r\033[K"; // Remove "Test running..." line after termination
		}
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