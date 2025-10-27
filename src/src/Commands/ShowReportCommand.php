<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Cache;
use QIT_CLI\Commands\QITCommand;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use function QIT_CLI\open_in_browser;

class ShowReportCommand extends QITCommand {
	protected static $defaultName = 'report'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	protected Cache $cache;

	public function __construct( Cache $cache ) {
		parent::__construct();
		$this->cache = $cache;
	}

	protected function configure(): void {
		parent::configure();
		$this
			->addArgument( 'report_dir', InputArgument::OPTIONAL, '(Optional) The report directory. If not set, will show the last report.' )
			->addOption( 'local', null, null, 'Force showing the local report instead of the remote one.' )
			->addOption( 'dir_only', null, null, 'Only output the local report directory path.' )
			->addOption( 'artifacts_dir', null, null, 'Only output the artifacts directory path (root directory containing all test artifacts).' )
			->setDescription( 'Shows a test report.' );
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// Handle --artifacts_dir option early and specially
		if ( $input->getOption( 'artifacts_dir' ) ) {
			// Try to get from report cache first
			$report_dir = json_decode( $this->cache->get( 'last_e2e_report' ) ?: '', true );

			if ( ! empty( $report_dir ) && isset( $report_dir['local_playwright'] ) ) {
				// Get the artifacts directory (parent of parent of HTML report directory)
				// HTML report is at /artifacts/final/html-report, so we need to go up 2 levels
				$artifacts_dir = dirname( dirname( $report_dir['local_playwright'] ) );

				if ( file_exists( $artifacts_dir ) ) {
					$directory = realpath( $artifacts_dir );
					if ( $directory !== false ) {
						$output->writeln( $directory );
						return Command::SUCCESS;
					}
				}
			}

			// If cache not found or directory doesn't exist, find the most recent artifacts directory
			$pattern        = sys_get_temp_dir() . '/qit-e2e-artifacts-*';
			$artifacts_dirs = glob( $pattern );

			if ( empty( $artifacts_dirs ) ) {
				throw new \RuntimeException( 'No artifacts directories found. Run a test first.' );
			}

			// Sort by modification time (most recent first)
			usort( $artifacts_dirs, function ( $a, $b ) {
				return filemtime( $b ) - filemtime( $a );
			} );

			$artifacts_dir = $artifacts_dirs[0];
			$directory     = realpath( $artifacts_dir );
			if ( $directory === false ) {
				throw new \RuntimeException( sprintf( 'Invalid artifacts directory path: %s', $artifacts_dir ) );
			}

			$output->writeln( $directory );
			return Command::SUCCESS;
		}

		// Determine the report directories for normal flow.
		if ( ! is_null( $input->getArgument( 'report_dir' ) ) ) {
			$local_report  = $input->getArgument( 'report_dir' );
			$remote_report = null; // Assuming no remote report when report_dir is specified.
		} else {
			$report_dir = json_decode( $this->cache->get( 'last_e2e_report' ) ?: '', true );

			if ( empty( $report_dir ) ) {
				$output->writeln( '<error>No report found.</error>' );

				return Command::FAILURE;
			}

			$local_report  = $report_dir['local_playwright'];
			$remote_report = $report_dir['remote_qit'] ?? null; // Use null coalescing in case 'remote_qit' is not set.
		}

		// Handle --dir_only option early.
		if ( $input->getOption( 'dir_only' ) ) {
			// Check if the local report directory exists.
			if ( ! file_exists( $local_report ) ) {
				throw new \RuntimeException( sprintf( 'Could not find the report directory: %s', $local_report ) );
			}

			// Output the local report directory path.
			$directory = realpath( $local_report );
			if ( $directory === false ) {
				throw new \RuntimeException( sprintf( 'Invalid report directory path: %s', $local_report ) );
			}

			$output->writeln( $directory );

			return Command::SUCCESS;
		}

		// If remote_report exists and --local is not set, open the remote report.
		if ( ! $input->getOption( 'local' ) && ! empty( $remote_report ) ) {
			open_in_browser( $remote_report );

			return Command::SUCCESS;
		}

		// Proceed with handling local report.
		if ( ! file_exists( $local_report ) ) {
			throw new \RuntimeException( sprintf( 'Could not find the report directory: %s', $local_report ) );
		}

		if ( ! file_exists( $local_report . '/index.html' ) ) {
			throw new \RuntimeException( sprintf( 'Could not find the report file: %s', $local_report . '/index.html' ) );
		}

		try {
			$port = $this->start_server( $local_report );
			$output->writeln( "<info>Server started on port: $port</info>" );
		} catch ( \RuntimeException $e ) {
			$output->writeln( '<error>Error: ' . $e->getMessage() . '</error>' );

			return Command::FAILURE;
		}

		open_in_browser( "http://localhost:$port" );

		// Prompt the user to keep the server running.
		( new QuestionHelper() )->ask(
			$input,
			$output,
			new Question( "Report available on http://localhost:$port. Press Ctrl+C to quit." )
		);

		return Command::SUCCESS;
	}

	protected function start_server( string $report_dir, int $start_port = 0 ): int {
		$max_tries      = 10; // Maximum number of ports to try before giving up.
		$global_timeout = 30; // Global timeout in seconds.
		$start_time     = microtime( true );

		$port            = $start_port;
		$attempted_ports = [];

		do {
			// Use a random port within the range 30000 to 40000 if not started with a specific port.
			if ( $port === 0 ) {
				do {
					$port = rand( 30000, 40000 );
				} while ( in_array( $port, $attempted_ports, true ) ); // Ensure not to repeat the same port.
			}

			$attempted_ports[] = $port;

			$results_process = new Process( [ PHP_BINARY, '-S', "localhost:$port", '-t', $report_dir ] );
			$results_process->start();

			// Wait for the server to start or for the process to stop.
			while ( $results_process->isRunning() && ( microtime( true ) - $start_time ) < $global_timeout ) {
				usleep( 200000 ); // 0.2 seconds.

				// Check for a message indicating the server has started.
				if ( preg_match( '/Development Server \(http:\/\/localhost:(\d+)\) started/', $results_process->getErrorOutput(), $matches ) ) {
					return (int) $matches[1]; // Return the port number on success.
				}
			}

			// Stop the process if still running after checking.
			if ( $results_process->isRunning() ) {
				$results_process->stop();
			}

			// Calculate the remaining time.
			if ( ( microtime( true ) - $start_time ) >= $global_timeout ) {
				throw new \RuntimeException( 'Timeout reached while trying to start the server.' );
			}

			// Reset port to 0 to allow random selection again.
			$port = 0;

			// Check if we've exhausted the maximum number of tries.
			if ( count( $attempted_ports ) >= $max_tries ) {
				throw new \RuntimeException( 'Could not start the server on any port.' );
			}

			sleep( 1 ); // Slight delay before the next attempt.
		} while ( true ); // Continue until a port is found or timeout/global limit is reached.
	}
}
