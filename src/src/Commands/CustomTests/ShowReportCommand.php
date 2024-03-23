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

		$results_process = new Process( [ PHP_BINARY, '-S', 'localhost:0', '-t', $report_dir ] );
		$port            = null;
		$results_process->start();

		$waited = 0;
		while ( $results_process->isRunning() ) {
			usleep( 200000 ); // wait 0.2 seconds.
			$waited += 0.2;
			if ( $waited > 30 ) {
				throw new \RuntimeException( 'Could not get the port for the report server.' );
			}

			if ( preg_match( '/Development Server \(http:\/\/localhost:(\d+)\) started/', $results_process->getErrorOutput(), $matches ) ) {
				$port = $matches[1];
				break;
			}
		}

		open_in_browser( "http://localhost:$port" );

		( new QuestionHelper() )->ask( App::make( InputInterface::class ), App::make( Output::class ), new Question( "Report available on http://localhost:$port. Press Ctrl+C to quit." ) );

		return Command::SUCCESS;
	}
}
