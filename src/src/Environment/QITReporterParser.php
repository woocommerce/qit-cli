<?php

namespace QIT_CLI\Environment;

use Symfony\Component\Console\Output\OutputInterface;

class QITReporterParser {
	private OutputInterface $output;
	private array $state = [
		'totalTests' => 0,
		'completed' => 0,
		'passed' => 0,
		'failed' => 0,
		'skipped' => 0,
		'flaky' => 0,
		'startTime' => null,
		'running' => [],
	];
	private bool $useQITReporter = false;

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * Parse a line of output from the test process.
	 *
	 * @param string $line The output line to parse.
	 * @return bool Whether to suppress this line from output.
	 */
	public function parseLine( string $line ): bool {
		// Check for QIT reporter output
		if ( strpos( $line, '::QIT::' ) === 0 ) {
			$this->useQITReporter = true;
			$json_start = strpos( $line, '::QIT::' ) + 7;
			$json_str = substr( $line, $json_start );
			
			try {
				$event = json_decode( trim( $json_str ), true );
				if ( $event && isset( $event['event'] ) ) {
					$this->handleEvent( $event );
					return true; // Suppress the raw JSON line
				}
			} catch ( \Exception $e ) {
				// Fall through to show the line
			}
		}

		// If we're using QIT reporter, suppress certain Playwright output
		if ( $this->useQITReporter ) {
			// Filter out duplicate progress information
			if ( $this->shouldSuppressLine( $line ) ) {
				return true;
			}
		}

		return false; // Don't suppress
	}

	private function handleEvent( array $event ): void {
		$eventType = $event['event'];
		$data = $event['data'] ?? [];
		
		switch ( $eventType ) {
			case 'begin':
				$this->handleBegin( $data );
				break;
			case 'testBegin':
				$this->handleTestBegin( $data );
				break;
			case 'testEnd':
				$this->handleTestEnd( $data );
				break;
			case 'end':
				$this->handleEnd( $data );
				break;
			case 'error':
				$this->handleError( $data );
				break;
			case 'stdout':
			case 'stderr':
				// We can handle these if needed, for now ignore
				break;
			case 'stepBegin':
			case 'stepEnd':
				// We can handle test steps if needed, for now ignore
				break;
		}
	}

	private function handleBegin( array $data ): void {
		$this->state['totalTests'] = $data['totalTests'];
		$this->state['startTime'] = microtime( true );
		$this->output->writeln( '' );
		$this->output->writeln( '<comment>Running ' . $data['totalTests'] . ' tests using ' . $data['workers'] . ' worker(s)</comment>' );
		$this->output->writeln( '' );
	}

	private function handleTestBegin( array $data ): void {
		// Store in state but don't output anything to avoid line overwrites
		$testId = $data['file'] . ':' . $data['line'] . ':' . $data['title'];
		$this->state['running'][$testId] = $data;
	}

	private function handleTestEnd( array $data ): void {
		$testId = $data['file'] . ':' . $data['line'] . ':' . $data['title'];
		unset( $this->state['running'][$testId] );
		$this->state['completed']++;

		$icon = '✓';
		$color = 'info';

		switch ( $data['status'] ) {
			case 'passed':
				$this->state['passed']++;
				if ( $data['retry'] > 0 ) {
					$icon = '⚡';
					$color = 'comment';
					$this->state['flaky']++;
				}
				break;
			case 'failed':
			case 'timedOut':
				$this->state['failed']++;
				$icon = '✗';
				$color = 'error';
				break;
			case 'skipped':
				$this->state['skipped']++;
				$icon = '-';
				$color = 'comment';
				break;
		}

		$duration = $this->formatDuration( $data['duration'] );
		$retry = $data['retry'] > 0 ? " <comment>(retry {$data['retry']})</comment>" : '';
		
		$this->output->writeln( "  <{$color}>{$icon} {$data['title']}</{$color}> <comment>{$duration}</comment>{$retry}" );

		if ( ! empty( $data['errors'] ) ) {
			foreach ( $data['errors'] as $error ) {
				$this->output->writeln( "    <error>" . $this->truncate( $error['message'], 80 ) . "</error>" );
			}
		}
	}

	private function handleEnd( array $data ): void {
		$this->output->writeln( '' );
		
		$duration = $this->state['startTime'] ? (int)((microtime( true ) - $this->state['startTime']) * 1000) : $data['duration'];
		$status = $data['status'] === 'passed' ? '<info>✓</info>' : '<error>✗</error>';
		
		$this->output->writeln( "{$status} {$this->state['passed']}/{$this->state['totalTests']} tests passed" );
		
		if ( $this->state['failed'] > 0 ) {
			$this->output->writeln( "  <error>{$this->state['failed']} failed</error>" );
		}
		if ( $this->state['flaky'] > 0 ) {
			$this->output->writeln( "  <comment>{$this->state['flaky']} flaky</comment>" );
		}
		if ( $this->state['skipped'] > 0 ) {
			$this->output->writeln( "  <comment>{$this->state['skipped']} skipped</comment>" );
		}
		
		$this->output->writeln( "  <comment>Duration: " . $this->formatDuration( $duration ) . "</comment>" );
	}

	private function handleError( array $data ): void {
		$this->output->writeln( "<error>Error: {$data['message']}</error>" );
	}


	private function shouldSuppressLine( string $line ): bool {
		// Suppress duplicate information when using QIT reporter
		$suppress_patterns = [
			'/^\s*Running \d+ tests? using \d+ workers?/',
			'/^\s*\d+ passed/',
			'/^\s*\d+ failed/',
			'/^\s*\d+ skipped/',
			'/^\s*\d+ flaky/',
		];

		foreach ( $suppress_patterns as $pattern ) {
			if ( preg_match( $pattern, $line ) ) {
				return true;
			}
		}

		return false;
	}

	private function formatDuration( int $ms ): string {
		if ( $ms < 1000 ) {
			return "{$ms}ms";
		}
		if ( $ms < 60000 ) {
			return round( $ms / 1000, 1 ) . 's';
		}
		$minutes = floor( $ms / 60000 );
		$seconds = round( ( $ms % 60000 ) / 1000 );
		return "{$minutes}m {$seconds}s";
	}

	private function truncate( string $str, int $length ): string {
		if ( strlen( $str ) <= $length ) {
			return $str;
		}
		return substr( $str, 0, $length - 3 ) . '...';
	}

	public function isUsingQITReporter(): bool {
		return $this->useQITReporter;
	}
}