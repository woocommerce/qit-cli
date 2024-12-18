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
require_once __DIR__ . '/src/ProcessManagerFork.php';
require_once __DIR__ . '/src/QITLiveOutput.php';
require_once __DIR__ . '/src/test-result-parser.php';

$logger = new Logger( __DIR__ . '/mass-test.log' );
$config = new Config( $argv, $logger );
$config->parse();

$validator = new Validator( $logger );
$validator->validate();

$liveOutput                  = new QITLiveOutput();
$tests_based_on_custom_tests = [ 'activation' ];

$testManager  = new TestManager( $logger, $tests_based_on_custom_tests );
$test_types   = $testManager->get_test_types();
$test_types   = $testManager->filter_test_types( $test_types );
$tests_to_run = $testManager->generate_test_runs( $test_types );

$zipManager    = new ZipManager( $logger );
$phpUnitRunner = new PhpUnitRunner( $logger, $liveOutput );

// Register shutdown cleanup
register_shutdown_function( function () use ( $logger ) {
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
clear_screen();
echo "──────────────────────────────────────────────────────────────────────\n";
echo " QIT Test Runner - Stage 1: Preparing Tests\n";
echo " (Verbose logs: mass-test.log)\n";
echo "──────────────────────────────────────────────────────────────────────\n\n";

echo "Filtering scenarios:\n";
// The filtering messages were already printed by the script during scenario filtering
// If you want more explicit info, you could add it here.

echo "\nGenerating ZIP packages:\n";
$zipManager->generate_zips( $tests_to_run['woo-api'] ?? [] );

echo "\nPreparation complete. Moving on to running QIT tests...\n";
sleep( 2 );

// --- Stage 2: Running QIT Tests ---
clear_screen();
echo "──────────────────────────────────────────────────────────────────────\n";
echo " QIT Test Runner - Stage 2: Executing Tests on QIT\n";
echo " (Verbose logs: mass-test.log)\n";
echo "──────────────────────────────────────────────────────────────────────\n\n";

echo "Dispatching tests to QIT infrastructure...\n";

$qitRunner = new QitRunner( $logger, $phpUnitRunner, $liveOutput );
try {
	$qitRunner->run_test_runs( $tests_to_run, $tests_based_on_custom_tests );
} catch ( \Exception $e ) {
	$logger->log( "Exception: " . $e->getMessage() );
	echo $e->getMessage() . "\nExiting by exception\n";
	die( 1 );
}

// If we reach here, QitRunner has completed polling and printed the final summary (Stage 3 is integrated in the QitRunner final summary print).

exit;

// Helper function to clear screen
function clear_screen() {
	if ( stripos( PHP_OS, 'WIN' ) === 0 ) {
		system( 'cls' );
	} else {
		system( 'clear' );
	}
}
