<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/QITLiveOutput.php';

if ( ! function_exists( 'clear_output' ) ) {
	function clear_output() {
	}
}

if ( ! function_exists( 'maybe_echo' ) ) {
	function maybe_echo( $message ) {
		echo $message;
	}
}

class QITLiveOutputTest extends TestCase {
	public function test_non_successful_test_returns_failure_exit_code() {
		$live_output = new QITLiveOutput();

		ob_start();
		$live_output->addTest( '123', 'Woo E2E', 1, [] );
		$live_output->setTestCompleted( '123', false );
		$live_output->addTestError( '123', 'Did not finish within 7200 seconds.' );
		$exit_code = $live_output->printFinalSummary( 0 );
		$output    = ob_get_clean();

		$this->assertSame( 1, $exit_code );
		$this->assertStringContainsString( 'Final outcome: ❌', $output );
	}

	public function test_successful_test_returns_success_exit_code() {
		$live_output = new QITLiveOutput();

		ob_start();
		$live_output->addTest( '123', 'Woo E2E', 1, [] );
		$live_output->setTestCompleted( '123', true );
		$exit_code = $live_output->printFinalSummary( 0 );
		ob_end_clean();

		$this->assertSame( 0, $exit_code );
	}
}
