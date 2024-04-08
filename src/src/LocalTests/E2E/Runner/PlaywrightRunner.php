<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\E2E\E2ETestManager;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use Symfony\Component\Process\Process;
use function QIT_CLI\open_in_browser;

class PlaywrightRunner extends E2ERunner {
	/**
	 * @inheritDoc
	 */
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
		$process = new Process( [ PHP_BINARY, $env_info->temporary_env . '/playwright/playwright-config-generator.php' ] );
		$process->setEnv( [
			'BASE_URL'         => $env_info->site_url,
			'PROJECTS'         => json_encode( $this->make_projects( $test_infos ), JSON_UNESCAPED_SLASHES ),
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
			'-e',
			'npm_config_cache=/qit/cache/npm-playwright',
			'-e',
			'PLAYWRIGHT_BROWSERS_PATH=/ms-playwright',
			'-v',
			Config::get_qit_dir() . 'cache:/qit/cache',
			'-v',
			$env_info->temporary_env . 'qit-playwright.config.js:/home/pwuser/qit-playwright.config.js',
			'-v',
			$env_info->temporary_env . '/playwright/db-import.js:/home/pwuser/db-import.js',
			'--add-host=host.docker.internal:host-gateway',
			'-v',
			$test_result->get_results_dir() . ':/qit/results',
		];

		if ( Docker::should_set_user() ) {
			$playwright_args[] = '--user';
			$playwright_args[] = implode( ':', Docker::get_user_and_group() );
		}

		foreach ( $test_infos as $test_to_run ) {
			$playwright_args = array_merge( $playwright_args, [
				'-v',
				"{$test_to_run['path_in_host']}:/home/pwuser/{$test_to_run['extension']}/{$test_to_run['test_tag']}",
			] );
		}

		if ( $test_mode === 'headed' ) {
			$options = '--headed --ui-port=8086 --ui-host=0.0.0.0';
		} elseif ( $test_mode === 'ui' ) {
			$options = '--ui --ui-port=8086 --ui-host=0.0.0.0';
		} else {
			$options = '';
		}

		try {
			// Allow to override the Playwright version from the Manager.
			$playwright_version_to_use = App::make( Cache::class )->get_manager_sync_data( 'playwright_version' );
		} catch ( \Exception $e ) {
			$playwright_version_to_use = '1.42.1';
		}

		$playwright_args = array_merge( $playwright_args, [
			"automattic/qit-runner-playwright:$playwright_version_to_use",
			'sh',
			'-c',
			'cd /home/pwuser && ' .
			"npx playwright test $options --config /home/pwuser/qit-playwright.config.js --output /qit/results/playwright",
		] );

		// Pull the image.
		$pull_process = new Process( [ App::make( Docker::class )->find_docker(), 'pull', "automattic/qit-runner-playwright:$playwright_version_to_use" ] );
		$pull_process->setTimeout( 300 );
		$pull_process->setIdleTimeout( 300 );
		$pull_process->run( function ( $type, $buffer ) {
			if ( $this->output->isVerbose() || $type === Process::ERR ) {
				$this->output->write( $buffer );
			}
		} );
		if ( ! $pull_process->isSuccessful() ) {
			throw new \RuntimeException( 'Could not pull the Playwright image.' );
		}

		// Run the tests.
		$playwright_process = new Process( $playwright_args );
		$playwright_process->setTimeout( 10800 ); // 3 hours.
		$playwright_process->setIdleTimeout( 3600 ); // 1 hour.

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( 'Playwright command: ' . $playwright_process->getCommandLine() );
		}

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

			// Don't print this line.
			if ( strpos( $out, 'playwright show-report' ) !== false ) {
				$out = '';
			}

			// Only print "fixuid" things if on very verbose mode.
			if ( strpos( $out, 'fixuid' ) !== false ) {
				if ( ! $this->output->isVeryVerbose() ) {
					return;
				}
			}

			// Clear the current line and move the cursor to the beginning.
			echo "\r\033[K";

			// Print the output from the process.
			$this->output->writeln( $out );

			// Print the spinner.
			$spinner_index = ( $spinner_index + 1 ) % count( $spinners ); // phpcs:ignore Squiz.Operators.IncrementDecrementUsage.Found
			$spinner       = $spinners[ $spinner_index ];

			// If PCNTL is available.
			if ( function_exists( 'pcntl_signal' ) ) {
				$this->output->write( "$spinner Test running... (To abort, press Ctrl+C)" );
			} else {
				$this->output->write( "$spinner Test running..." );
			}
		};

		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function () use ( $playwright_process ): void {
				// We just need to stop the process, since "--rm" takes care of the cleanup.
				$playwright_process->stop();
			};

			pcntl_signal( SIGINT, $signal_handler ); // Ctrl+C.
			pcntl_signal( SIGTERM, $signal_handler ); // eg: kill 123, where "123" is the PID of this PHP process.
		}

		$playwright_process->run( $output_callback );

		echo "\r\033[K"; // Remove "Test running..." line.

		if ( file_exists( $results_dir . '/report/index.html' ) ) {
			App::make( Cache::class )->set( 'last_e2e_report', $results_dir . '/report', MONTH_IN_SECONDS );
			E2ETestManager::$has_report = true;
		}
	}

	/**
	 * // phpcs:disable
	 * @param array<string,array{
	 *      extension:string,
	 *      type:string,
	 *      test_tag:string,
	 *      path_in_container:string,
	 *      path_in_host:string
	 *  }> $test_infos
	 * // phpcs:enable
	 *
	 * @return array<int,array<string,scalar>>
	 */
	protected function make_projects( array $test_infos ): array {
		$projects = [];
		$is_first = true;

		foreach ( $test_infos as $t ) {
			$base_dir       = sprintf( '/home/pwuser/%s/%s', $t['extension'], $t['test_tag'] );
			$has_entrypoint = file_exists( "{$t['path_in_host']}/entrypoint.qit.js" );

			// Include db-import before each project, except the first one.
			if ( $is_first ) {
				$is_first = false;
			} else {
				$projects[] = [
					'name'      => 'db-import',
					'testMatch' => '/home/pwuser/db-import.js',
					'use'       => [
						'browserName' => 'chromium',
					],
				];
			}

			if ( $has_entrypoint ) {
				// Run the entrypoint.
				$projects[] = [
					'name'      => sprintf( '%s-%s-entrypoint', $t['extension'], $t['test_tag'] ),
					'testDir'   => $base_dir,
					'testMatch' => 'entrypoint.qit.js',
					'use'       => [
						'browserName' => 'chromium',
					],
				];
			}

			// Run the test.
			$projects[] = [
				'name'    => sprintf( '%s-%s', $t['extension'], $t['test_tag'] ),
				'testDir' => $base_dir,
				'use'     => [
					'browserName' => 'chromium',
				],
			];
		}

		return $projects;
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
