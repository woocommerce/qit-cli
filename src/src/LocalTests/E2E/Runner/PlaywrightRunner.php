<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\E2E\E2ETestManager;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Process\Process;
use function QIT_CLI\normalize_path;
use function QIT_CLI\open_in_browser;

class PlaywrightRunner extends E2ERunner {
	/**
	 * @inheritDoc
	 */
	public function run_test( E2EEnvInfo $env_info, array $test_infos, TestResult $test_result, string $test_mode, ?string $shard = null ): int {
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

		// Special settings for running self-tests.
		if ( getenv( 'QIT_SELF_TESTS' ) ) {
			if ( ! isset( $env_info->playwright_config['reportSlowTests'] ) ) {
				// Increase the "reportSlowTests" for more reliable snapshot testing.
				$env_info->playwright_config['reportSlowTests'] = [
					'max'       => 10,
					'threshold' => 60000,
				];
			}
		}

		$this->output->writeln( sprintf( 'Test artifacts being saved to: %s', $results_dir ) );

		// Generate playwright-config.
		$process = new Process( [ PHP_BINARY, $env_info->temporary_env . '/playwright/playwright-config-generator.php' ] );
		$process->setEnv( [
			'BASE_URL'            => $env_info->site_url,
			'PROJECTS'            => json_encode( $this->make_projects( $test_infos ), JSON_UNESCAPED_SLASHES ),
			'SAVE_AS'             => $env_info->temporary_env . 'qit-playwright.config.js',
			'TEST_RESULT_PATH'    => $results_dir,
			'CONFIG_OVERRIDES'    => json_encode( $env_info->playwright_config ),
			'ATTACHMENT_BASE_URL' => App::getVar( 'attachment_base_url' ) . '/data/',
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

		$ci = ! empty( getenv( 'CI' ) );

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
			'-e',
			sprintf( 'BASE_URL=%s', $env_info->site_url ),
			'-e',
			sprintf( 'QIT_DOMAIN=%s', $env_info->domain ),
			'-v',
			Config::get_qit_dir() . 'cache:/qit/cache',
			// Map the named volume so that PW can share data with the PHP container and vice-versa.
			'-v',
			"qit_env_volume_{$env_info->env_id}" . ':/var/www/html',
			'-v',
			$env_info->temporary_env . 'qit-playwright.config.js:/qit/tests/e2e/qit-playwright.config.js',
			'-v',
			$env_info->temporary_env . '/playwright/db-import.js:/qit/tests/e2e/db-import.js',
			'-v',
			$env_info->temporary_env . '/playwright/qitHelpers.js:/qitHelpers/qitHelpers.js',
			'-v',
			$env_info->temporary_env . '/playwright/qitHelpers-package.json:/qitHelpers/package.json',
			'-v',
			$env_info->temporary_env . '/playwright/global-setup.js:/qit/tests/e2e/global-setup.js',
			'--add-host=host.docker.internal:host-gateway',
			'-v',
			$test_result->get_results_dir() . ':/qit/results',
		];

		if ( $ci ) {
			$playwright_args[] = '-e';
			$playwright_args[] = 'FORCE_COLOR=false';
		}

		if ( Docker::should_set_user() ) {
			$playwright_args[] = '--user';
			$playwright_args[] = implode( ':', Docker::get_user_and_group() );
		}

		$dependencies_command    = ' && ';
		$dependencies_to_install = [];

		foreach ( $test_infos as $test_to_run ) {
			$playwright_args = array_merge( $playwright_args, [
				'-v',
				"{$test_to_run['path_in_host']}:{$test_to_run['path_in_php_container']}",
			] );

			if ( file_exists( "{$test_to_run['path_in_host']}/bootstrap/dependencies.json" ) ) {
				// Read the dependencies JSON and append.
				$dependencies            = json_decode( file_get_contents( "{$test_to_run['path_in_host']}/bootstrap/dependencies.json" ), true );
				$dependencies_to_install = array_merge( $dependencies_to_install, $dependencies );
			}
		}

		if ( ! empty( $dependencies_to_install ) ) {
			$dependencies_to_install = array_unique( $dependencies_to_install );
			$dependencies_command    = '&& npm install ' . implode( ' ', $dependencies_to_install ) . ' && ';
		}

		$options = App::getVar( 'pw_options', '' );

		if ( $test_mode === 'headed' ) {
			$options .= ' --headed --ui-port=8086 --ui-host=0.0.0.0';
		} elseif ( $test_mode === 'ui' ) {
			$options .= ' --ui --ui-port=8086 --ui-host=0.0.0.0';
		}

		if ( ! is_null( $shard ) ) {
			$shard = "--shard $shard";
		} else {
			$shard = '';
		}
		try {
			// Allow to override the Playwright version from the Manager.
			$playwright_version_to_use = App::make( Cache::class )->get_manager_sync_data( 'playwright_version' );
		} catch ( \Exception $e ) {
			$playwright_version_to_use = '1.44.0';
		}

		$playwright_args = array_merge( $playwright_args, [
			"automattic/qit-runner-playwright:$playwright_version_to_use",
			'sh',
			'-c',
			"cd /qit/tests/e2e $dependencies_command" .
			"npx playwright test $options --config /qit/tests/e2e/qit-playwright.config.js --output /qit/results/playwright $shard",
		] );

		// Pull the image.
		$pull_process = new Process( [ App::make( Docker::class )->find_docker(), 'pull', "automattic/qit-runner-playwright:$playwright_version_to_use" ] );
		$pull_process->setTimeout( 300 );
		$pull_process->setIdleTimeout( 300 );
		$pull_process->setEnv( [
			'DOCKER_CLI_HINTS' => 'false',
		] );
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

		$has_previous_line = false;

		$output_callback = function ( $type, $out ) use ( $playwright_container_name, $ci, &$has_previous_line ) {
			$terminal = new Terminal();
			$cursor   = new Cursor( $this->output );
			$width    = $terminal->getWidth();

			if ( strpos( $out, 'Listening on' ) !== false ) {
				$out = $this->get_playwright_headed_output( $playwright_container_name );
			}

			if ( strpos( $out, 'To open last HTML report' ) !== false || strpos( $out, 'playwright show-report' ) !== false ) {
				return;  // Suppress certain lines.
			}

			if ( strpos( $out, 'fixuid' ) !== false && ! $this->output->isVeryVerbose() ) {
				return;
			}

			// Remove same-line cursor movement and clear line.
			$out = preg_replace( '/\e\[1A|\e\[2K/', '', $out );
			$out = trim( $out );

			if ( empty( $out ) ) {
				return;
			}

			// eg: [3/18] [foo] Test name.
			if ( ! $ci && preg_match( '/\[\d+\/\d+\]/', $out ) ) {
				// Test line code.
				if ( $has_previous_line ) {
					$cursor->moveUp( 1 );
					$cursor->moveToColumn( 0 );
					$cursor->clearOutput();
				}
				$out = substr( $out, 0, $width );
				$out = str_replace( "\n", '', $out );
				// Fill string with empty characters until it matches the width of the terminal.
				$out = str_pad( $out, $width, ' ' );
				$this->output->writeln( trim( $out ) );
				$has_previous_line = true;
			} else {
				$this->output->writeln( $out );
			}
		};

		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function () use ( $playwright_process ): void {
				// We just need to stop the process, since "--rm" takes care of the cleanup.
				$playwright_process->signal( SIGTERM );
			};

			pcntl_signal( SIGINT, $signal_handler ); // Ctrl+C.
			pcntl_signal( SIGTERM, $signal_handler ); // eg: kill 123, where "123" is the PID of this PHP process.
		}

		$playwright_process->run( $output_callback );

		if ( file_exists( $results_dir . '/report/index.html' ) ) {
			App::make( Cache::class )->set( 'last_e2e_report', $results_dir . '/report', MONTH_IN_SECONDS );
			E2ETestManager::$has_report = true;
		}

		// Copy snapshots from Container to Host if needed.
		if ( strpos( $options, '--update-snapshots' ) !== false ) {
			$php_container_name = $env_info->get_docker_container( 'php' );
			$docker             = App::make( Docker::class )->find_docker();

			foreach ( $test_infos as $test_to_run ) {
				if ( empty( $test_to_run['path_in_host_original'] ) || ! file_exists( $test_to_run['path_in_host_original'] ) ) {
					continue;
				}

				// Check if the test has snapshots.
				$check_directory_process = new Process( [
					$docker,
					'exec',
					$php_container_name,
					'test',
					'-d',
					"{$test_to_run['path_in_php_container']}/__snapshots__",
				] );
				$check_directory_process->run();

				// If it has, and "update-snapshots" is true, copy it back to the source.
				if ( $check_directory_process->isSuccessful() ) {
					$copy_snapshots_process = new Process( [
						$docker,
						'container',
						'cp',
						"$php_container_name:{$test_to_run['path_in_php_container']}/__snapshots__",
						normalize_path( $test_to_run['path_in_host_original'] ),
					] );
					$copy_snapshots_process->run();
					if ( ! $copy_snapshots_process->isSuccessful() ) {
						throw new \RuntimeException( 'Could not copy snapshots from the PHP container.' );
					}
				}
			}
		}

		// If Allure Report exists, generate the report using a PW container.
		$allure_results_dir = $results_dir . '/allure-playwright';
		if ( file_exists( $allure_results_dir ) ) {
			$allure_report_dir  = $results_dir . '/allure-report';

			if ( ! mkdir( $allure_report_dir, 0755, false ) ) {
				throw new \RuntimeException( 'Could not create the Allure results directory: ' . $allure_results_dir );
			}

			$allure_report_process = new Process( [
				App::make( Docker::class )->find_docker(),
				'run',
				'--rm',
				'-v',
				$allure_results_dir . ':/allure-results',
				'-v',
				$allure_report_dir . ':/allure-report',
				"automattic/qit-runner-playwright:$playwright_version_to_use",
				'sh',
				'-c',
				'cd /qit/tests/e2e && npm install allure-commandline && npx allure generate /allure-results -o /allure-report --clean',
			] );
			$allure_report_process->run();
			if ( ! $allure_report_process->isSuccessful() ) {
				throw new \RuntimeException( sprintf( 'Could not generate the Allure report. Output: %s', $allure_report_process->getOutput() . "\n" . $allure_report_process->getErrorOutput() ) );
			}
		}

		return $playwright_process->getExitCode();
	}

	/**
	 * // phpcs:disable Squiz.Commenting.FunctionComment.MissingParamName
	 *
	 * @param array<int,array{
	 *     slug:string,
	 *     test_tag:string,
	 *     type:string,
	 *     action:string,
	 *     path_in_php_container:string,
	 *     path_in_playwright_container:string,
	 *     path_in_host:string
	 *  }> $test_infos
	 *
	 * // phpcs:enable
	 *
	 * @return array<int,array<string,scalar>>
	 */
	protected function make_projects( array $test_infos ): array {
		$projects = [];
		$is_first = true;

		foreach ( $test_infos as $t ) {
			$base_dir       = $t['path_in_php_container'];
			$has_entrypoint = file_exists( "{$t['path_in_host']}/bootstrap/entrypoint.js" );

			// Include db-import before each project, except the first one.
			if ( $is_first ) {
				$is_first = false;
			} else {
				$projects[] = [
					'name'      => 'db-import',
					'testMatch' => '/qit/tests/e2e/db-import.js',
					'use'       => [
						'browserName' => 'chromium',
					],
				];
			}

			if ( $has_entrypoint ) {
				// Run the entrypoint.
				$projects[] = [
					'name'      => sprintf( '%s-%s-entrypoint', $t['slug'], $t['test_tag'] ),
					'testDir'   => "$base_dir/bootstrap",
					'testMatch' => 'entrypoint.js',
					'use'       => [
						'browserName' => 'chromium',
						'stateDir'    => $base_dir . '/.state',
						'qitTestTag'  => $t['test_tag'],
						'qitTestSlug' => $t['slug'],
					],
				];
			}

			// Run the test.
			$args = [
				'name'    => sprintf( '%s-%s', $t['slug'], $t['test_tag'] ),
				'testDir' => $base_dir,
				'use'     => [
					'browserName' => 'chromium',
					'stateDir'    => $base_dir . '/.state',
					'qitTestTag'  => $t['test_tag'],
					'qitTestSlug' => $t['slug'],
				],
			];

			if ( $has_entrypoint ) {
				$args['dependencies'] = [ sprintf( '%s-%s-entrypoint', $t['slug'], $t['test_tag'] ) ];
			}

			$projects[] = $args;
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

		// Extract the host port specifically looking for IPv4 format (0.0.0.0:port).
		$output = trim( $get_port_process->getOutput() );
		preg_match( '/0\.0\.0\.0:(\d+)/', $output, $matches );
		if ( empty( $matches ) ) {
			throw new \RuntimeException( 'Could not find an IPv4 mapped port for the Playwright container.' );
		}
		$host_port = $matches[1];  // Get the port number from the match.

		open_in_browser( sprintf( 'http://localhost:%s', $host_port ) );

		return sprintf( 'Playwright UI is available at http://localhost:%s', $host_port );
	}
}
