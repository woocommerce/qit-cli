<?php

namespace QIT_CLI\LocalTests\E2E\Runner;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\LocalTests\E2E\Result\TestResult;
use QIT_CLI\Upload;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;
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

		$test_media_dir = $env_info->temporary_env . '/test-media';

		if ( ! file_exists( $test_media_dir ) ) {
			if ( ! mkdir( $test_media_dir, 0755, true ) ) {
				throw new \RuntimeException( sprintf( 'Could not create the test media directory: %s', $test_media_dir ) );
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

		$plugin_activation_stack = array_map( static function ( $plugin ) {
			return $plugin['slug'];
		}, array_reverse( $env_info->plugins ) );

		$sut_qit_config = [];

		if ( file_exists( "$env_info->sut_path/qit.json" ) ) {
			$sut_qit_config = json_decode( file_get_contents( "$env_info->sut_path/qit.json" ), true );

			if ( is_null( $sut_qit_config ) ) {
				$this->output->writeln( '<comment>qit.json is not a valid JSON file. Skipping...</comment>' );
			} else {
				$this->output->writeln( "<info>qit.json found for {$env_info->sut_slug}.</info>" );
			}
		}

		file_put_contents( $env_info->temporary_env . 'playwright/test-info.json', json_encode( [
			'SUT_SLUG'                => $env_info->sut_slug,
			'SUT_TYPE'                => $env_info->sut_type,
			'SUT_ENTRYPOINT'          => $env_info->sut_entrypoint,
			'SUT_QIT_CONFIG'          => $sut_qit_config,
			'PLUGIN_ACTIVATION_STACK' => $plugin_activation_stack,
		] ) );

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
			$env_info->temporary_env . '/test-media:/qit/tests/e2e/test-media',
			'-v',
			$env_info->temporary_env . '/playwright/qitHelpers.js:/qitHelpers/qitHelpers.js',
			'-v',
			$env_info->temporary_env . '/playwright/test-info.json:/qitHelpers/test-info.json', // Contains information about the test, such as the SUT slug, type, etc.
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

		$dependencies_command = ' && ';
		// Todo: bundle axios in the PW Docker image.
		$dependencies_to_install = [ 'axios@0.27.2' ];

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

		// @phpstan-ignore-next-line
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
			"npx playwright test $options --config /qit/tests/e2e/qit-playwright.config.js --output /qit/results/playwright $shard 2>&1",
		] );

		// Pull the image.
		if ( ! getenv( 'QIT_NO_PULL' ) ) {
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
		}

		// Run the tests.
		$playwright_process = new Process( $playwright_args );
		$playwright_process->setTimeout( 10800 ); // 3 hours.
		$playwright_process->setIdleTimeout( 3600 ); // 1 hour.

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( 'Playwright command: ' . $playwright_process->getCommandLine() );
		}

		// Initialize a variable to keep track of total lines printed.
		$line_buffer         = [];
		$total_lines_printed = 0;

		$output_callback = function ( $type, $out ) use ( $playwright_container_name, &$line_buffer, &$total_lines_printed ) {
			$max_lines = 100;

			// Handle both STDOUT and STDERR.
			if ( $type !== Process::OUT && $type !== Process::ERR ) {
				return;
			}

			// Remove unnecessary output.
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
			$out = preg_replace( '/\e\[\d*[ABCD]/', '', $out ); // Remove cursor movements.
			$out = preg_replace( '/\e\[2K/', '', $out );        // Remove clear line codes.
			$out = trim( $out );

			if ( empty( $out ) ) {
				return;
			}

			// Update terminal width in case it changes.
			$terminal_width = ( new Terminal() )->getWidth();

			// Split the output into individual lines.
			$lines = explode( "\n", $out );

			// For each line, calculate the height and add to buffer.
			foreach ( $lines as $line_content ) {
				// Strip ANSI codes for width calculation.
				$plain_line = preg_replace( '/\033\[[0-9;]*[a-zA-Z]/', '', $line_content );
				$plain_line = Helper::removeDecoration( $this->output->getFormatter(), $plain_line );

				// Calculate visual width and height.
				$visual_width = mb_strwidth( $plain_line, 'UTF-8' );
				// Calculate how many terminal lines this line will occupy.
				$line_height = max( 1, ceil( $visual_width / $terminal_width ) );

				// Prepare line info.
				$line_info = [
					'content' => $line_content,
					'height'  => $line_height,
				];

				// Add to buffer.
				$line_buffer[] = $line_info;
			}

			// If buffer exceeds max_lines, remove the oldest line(s).
			while ( count( $line_buffer ) > $max_lines ) { // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found
				array_shift( $line_buffer );
			}

			// Calculate total height of the buffer.
			$buffer_height = array_sum( array_column( $line_buffer, 'height' ) );

			// Move cursor up by total_lines_printed.
			if ( $total_lines_printed > 0 ) {
				$this->output->write( "\033[{$total_lines_printed}A", false, OutputInterface::OUTPUT_RAW );
			}

			// Clear lines equal to total_lines_printed.
			for ( $i = 0; $i < $total_lines_printed; $i++ ) {
				$this->output->write( "\033[2K\033[1B", false, OutputInterface::OUTPUT_RAW ); // Clear line and move cursor down.
			}

			// Move cursor back up.
			if ( $total_lines_printed > 0 ) {
				$this->output->write( "\033[{$total_lines_printed}A", false, OutputInterface::OUTPUT_RAW );
			}

			// Reprint the buffer.
			foreach ( $line_buffer as $line ) {
				// Print the content.
				$this->output->write( $line['content'], false, OutputInterface::OUTPUT_RAW );
				$this->output->write( "\n", false, OutputInterface::OUTPUT_RAW );
			}

			// Update total_lines_printed to the new buffer height.
			$total_lines_printed = $buffer_height;
		};

		if ( function_exists( 'pcntl_signal' ) ) {
			$signal_handler = static function () use ( $playwright_process ): void {
				// We just need to stop the process, since "--rm" takes care of the cleanup.
				$playwright_process->signal( SIGTERM );
			};

			pcntl_signal( SIGINT, $signal_handler ); // Ctrl+C.
			pcntl_signal( SIGTERM, $signal_handler ); // eg: kill 123, where "123" is the PID of this PHP process.
		}

		$this->output->writeln( '<info>Running E2E Tests</info>' );
		$playwright_process->run( $output_callback );

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

		$exit_status_code = $playwright_process->getExitCode();

		/*
		 * Upload test media if test not aborted.
		 */
		if ( $exit_status_code !== 143 && file_exists( $env_info->temporary_env . '/test-media' ) ) {
			$allowed_extensions = [ 'jpg', 'webm', 'json' ];

			$count_of_allowed_files = 0;

			foreach ( new \DirectoryIterator( $env_info->temporary_env . '/test-media' ) as $file ) {
				if ( $file->isDot() ) {
					continue;
				}
				if ( $file->isDir() ) {
					throw new \RuntimeException( sprintf( 'Screenshots directory contains a directory: %s', $file->getFilename() ) );
				}
				if ( ! $file->isFile() ) {
					throw new \RuntimeException( sprintf( 'Screenshots directory contains a non-file: %s', $file->getFilename() ) );
				}

				if ( in_array( $file->getExtension(), $allowed_extensions, true ) ) {
					++$count_of_allowed_files;
				} else {
					throw new \RuntimeException( sprintf( 'Screenshots directory contains file disallowed file type: %s', $file->getFilename() ) );
				}
			}

			if ( $count_of_allowed_files > 0 ) {
				App::make( Zipper::class )->zip_directory( $env_info->temporary_env . '/test-media', $results_dir . '/test-media.zip' );

				// If it got bigger than 50mb, bail.
				if ( filesize( $results_dir . '/test-media.zip' ) > 50 * 1024 * 1024 ) {
					$this->output->writeln( '<error>Test medias are too large to upload. Skipping...</error>' );
				} else {
					App::make( Upload::class )->upload_build( 'test-media', App::getVar( 'test_run_id' ), $results_dir . '/test-media.zip', $this->output );
				}
			}
		}

		$this->output->writeln( sprintf( 'Test artifacts being saved to: %s', $results_dir ) );

		return $exit_status_code;
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
						'devices'     => [ 'Desktop Chrome' ],
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
						'devices'     => [ 'Desktop Chrome' ],
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
					'devices'     => [ 'Desktop Chrome' ],
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
