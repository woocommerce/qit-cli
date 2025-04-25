<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/Context.php';
require_once __DIR__ . '/src/Logger.php';
require_once __DIR__ . '/src/Config.php';
require_once __DIR__ . '/src/Validator.php';
require_once __DIR__ . '/src/TestManager.php';
require_once __DIR__ . '/src/ZipManager.php';
require_once __DIR__ . '/src/PhpUnitRunner.php';
require_once __DIR__ . '/src/QitRunner.php';
require_once __DIR__ . '/src/QITLiveOutput.php';
require_once __DIR__ . '/src/test-result-parser.php';

global $output, $gracefulEnding;
$isCI = ! empty( getenv( 'CI' ) );
$output = '';
$gracefulEnding = false;

function maybe_echo( $message ) {
	global $isCI, $output;

	if ( $isCI ) {
		$output .= $message;
	} else {
		echo $message;
	}
}

function clear_output() {
	global $isCI, $output;

	if ( $isCI ) {
		$output = '';
	} else {
		system( 'clear' );
	}
}

$logger = new Logger( __DIR__ . '/last-self-test.log' );
$config = new Config( $argv, $logger );
$config->parse();

$validator = new Validator( $logger );
$validator->validate();

$liveOutput                  = new QITLiveOutput();
$tests_based_on_custom_tests = [ 'activation' ];

$testManager  = new TestManager( $logger, $tests_based_on_custom_tests, $config->one_of_each );
$test_types   = $testManager->get_test_types();
$test_types   = $testManager->filter_test_types( $test_types );
$tests_to_run = $testManager->generate_test_runs( $test_types );

$zipManager    = new ZipManager( $logger );
$phpUnitRunner = new PhpUnitRunner( $logger, $liveOutput );

// Register shutdown cleanup
register_shutdown_function( function () use ( $logger ) {
	global $isCI, $output, $gracefulEnding;
	if ( $isCI && ! $gracefulEnding ) {
		echo $output;
	}
	
	$to_delete  = array_unique( Context::$to_delete );
	$reuse_json = ( getenv( 'QIT_REUSE_JSON' ) === '1' );
	foreach ( $to_delete as $file ) {
		if ( ! $reuse_json && file_exists( $file ) ) {
			if ( ! unlink( $file ) ) {
				$logger->log( "Failed to delete file: $file" );
				throw new RuntimeException( "Failed to delete file: $file" );
			} else {
				$logger->log( "Deleted temp file: $file" );
			}
		}
	}
	$logger->log( "Script shutdown" );
} );

// --- Stage 1: Preparation ---
clear_output();
maybe_echo( "──────────────────────────────────────────────────────────────────────\n" );
maybe_echo( " QIT Test Runner - Stage 1: Preparing Tests\n" );
maybe_echo( " (Verbose logs: last-self-test.log)\n" );
maybe_echo( "──────────────────────────────────────────────────────────────────────\n\n" );

maybe_echo( "Filtering scenarios:\n" );
// The filtering messages were already printed by the script during scenario filtering

maybe_echo( "\nGenerating ZIP packages:\n" );

// Generate zips for all test types found
foreach ( $tests_to_run as $type => $runs ) {
	maybe_echo( "\nGenerating ZIP packages for $type:\n" );
	$zipManager->generate_zips( $runs );
}

maybe_echo( "\nPreparation complete. Moving on to running QIT tests...\n" );
sleep( 2 );

// --- Stage 2: Running QIT Tests ---
clear_output();
maybe_echo( "──────────────────────────────────────────────────────────────────────\n" );
maybe_echo( " QIT Test Runner - Stage 2: Executing Tests on QIT\n" );
maybe_echo( " (Verbose logs: last-self-test.log)\n" );
maybe_echo( "──────────────────────────────────────────────────────────────────────\n\n" );

maybe_echo( "Dispatching tests to QIT infrastructure...\n" );

$qitRunner = new QitRunner( $logger, $phpUnitRunner, $liveOutput );
try {
	$qitRunner->run_test_runs( $tests_to_run, $tests_based_on_custom_tests );
} catch ( \Exception $e ) {
	$logger->log( "Exception: " . $e->getMessage() );
	maybe_echo( $e->getMessage() . "\nExiting by exception\n" );
	die( 1 );
}

// If we reach here, QitRunner has completed polling and printed the final summary

exit;
