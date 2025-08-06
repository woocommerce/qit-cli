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
		'running' => [],
		'lastProgressLine' => null,
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
				if ( $event ) {
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
		switch ( $event['type'] ) {
			case 'session:start':
				$this->handleSessionStart( $event['data'] );
				break;
			case 'test:start':
				$this->handleTestStart( $event['data'] );
				break;
			case 'test:end':
				$this->handleTestEnd( $event['data'] );
				break;
			case 'progress':
				$this->handleProgress( $event['data'] );
				break;
			case 'session:end':
				$this->handleSessionEnd( $event['data'] );
				break;
			case 'error':
				$this->handleError( $event['data'] );
				break;
		}
	}

	private function handleSessionStart( array $data ): void {
		$this->state['totalTests'] = $data['totalTests'];
		$this->output->writeln( '' );
		$this->output->writeln( '<comment>Running ' . $data['totalTests'] . ' tests using ' . $data['workers'] . ' worker(s)</comment>' );
		$this->output->writeln( '' );
	}

	private function handleTestStart( array $data ): void {
		// Store in state but don't output anything to avoid line overwrites
		$this->state['running'][$data['id']] = $data;
	}

	private function handleTestEnd( array $data ): void {
		unset( $this->state['running'][$data['id']] );
		$this->state['completed']++;

		$icon = '✓';
		$color = 'info';

		switch ( $data['status'] ) {
			case 'passed':
				$this->state['passed']++;
				if ( $data['retry'] > 0 ) {
					$icon = '⚡';
					$color = 'comment';
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

	private function handleProgress( array $data ): void {
		// Update internal state
		$this->state = array_merge( $this->state, $data );
		// Don't render progress bar - it causes line overwrite issues
	}

	private function handleSessionEnd( array $data ): void {
		$this->output->writeln( '' );
		
		$summary = $data['summary'];
		$status = $data['status'] === 'passed' ? '<info>✓</info>' : '<error>✗</error>';
		
		$this->output->writeln( "{$status} {$summary['passed']}/{$summary['total']} tests passed" );
		
		if ( $summary['failed'] > 0 ) {
			$this->output->writeln( "  <error>{$summary['failed']} failed</error>" );
		}
		if ( $summary['flaky'] > 0 ) {
			$this->output->writeln( "  <comment>{$summary['flaky']} flaky</comment>" );
		}
		if ( $summary['skipped'] > 0 ) {
			$this->output->writeln( "  <comment>{$summary['skipped']} skipped</comment>" );
		}
		
		$this->output->writeln( "  <comment>Duration: " . $this->formatDuration( $data['duration'] ) . "</comment>" );
	}

	private function handleError( array $data ): void {
		$this->output->writeln( "<error>Error: {$data['message']}</error>" );
	}

	private function renderProgressBar(): void {
		if ( $this->state['totalTests'] === 0 ) {
			return;
		}

		$percentage = round( ( $this->state['completed'] / $this->state['totalTests'] ) * 100 );
		$bar_length = 30;
		$filled = round( ( $percentage / 100 ) * $bar_length );
		$empty = $bar_length - $filled;

		$bar = str_repeat( '█', $filled ) . str_repeat( '░', $empty );
		
		$progress_line = sprintf(
			"  [%s] %d/%d (%d%%) ✓%d ✗%d -%d",
			$bar,
			$this->state['completed'],
			$this->state['totalTests'],
			$percentage,
			$this->state['passed'],
			$this->state['failed'],
			$this->state['skipped']
		);

		// Clear previous progress line and write new one
		if ( $this->state['lastProgressLine'] !== null ) {
			// Move cursor up and clear line
			$this->output->write( "\x1B[1A\x1B[2K" );
		}
		
		$this->output->writeln( "<comment>{$progress_line}</comment>" );
		$this->state['lastProgressLine'] = $progress_line;
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