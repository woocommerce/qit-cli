<?php

namespace QIT_CLI\Commands\CustomTests;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use function QIT_CLI\open_in_browser;

class ShowReportCommand extends Command {
	protected static $defaultName = 'e2e-report'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/** @var Cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		parent::__construct();
		$this->cache = $cache;
	}

	protected function configure() {
		$this
			->addArgument( 'report_dir', InputArgument::OPTIONAL, '(Optional) The report directory. If not set, will show the last report.' )
			->addOption( 'dir_only', null, null, 'Only show the report directory.' )
			->setDescription( 'Shows a test report.' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		if ( ! is_null( $input->getArgument( 'report_dir' ) ) ) {
			$report_dir = $input->getArgument( 'report_dir' );
		} else {
			$report_dir = $this->cache->get( 'last_e2e_report' );
		}

		if ( ! file_exists( $report_dir ) ) {
			throw new \RuntimeException( sprintf( 'Could not find the report directory: %s', $report_dir ) );
		}

		if ( ! file_exists( $report_dir . '/index.html' ) ) {
			throw new \RuntimeException( sprintf( 'Could not find the report file: %s', $report_dir . '/index.html' ) );
		}

		if ( $input->getOption( 'dir_only' ) ) {
			// We usually want the "HTML" report, but here print the general result directory.
			$report_dir = dirname( $report_dir );

			$output->writeln( $report_dir );

			return Command::SUCCESS;
		}

		try {
			$port = $this->start_server( $report_dir );
			echo "Server started on port: $port\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} catch ( \RuntimeException $e ) {
			echo 'Error: ' . $e->getMessage() . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			return Command::FAILURE;
		}

		open_in_browser( "http://localhost:$port" );

		( new QuestionHelper() )->ask( App::make( InputInterface::class ), App::make( Output::class ), new Question( "Report available on http://localhost:$port. Press Ctrl+C to quit." ) );

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
