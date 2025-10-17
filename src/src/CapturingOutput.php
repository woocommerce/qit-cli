<?php

namespace QIT_CLI;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Capturing output decorator that writes to both a stream and captures content.
 *
 * This class allows output to be displayed in real-time while also capturing
 * the content for later parsing or analysis.
 */
class CapturingOutput extends StreamOutput {
	private string $captured = '';
	private OutputInterface $decorated;

	public function __construct( OutputInterface $decorated ) {
		$this->decorated = $decorated;
		parent::__construct( fopen( 'php://memory', 'w', false ), $decorated->getVerbosity(), $decorated->isDecorated() );
	}

	protected function doWrite( string $message, bool $newline ): void {
		// Capture the output
		$this->captured .= $message;
		if ( $newline ) {
			$this->captured .= PHP_EOL;
		}

		// Write to the actual output in real-time
		$this->decorated->write( $message, $newline );
	}

	public function getCaptured(): string {
		return $this->captured;
	}
}
