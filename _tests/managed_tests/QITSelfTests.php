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
	$to_delete = array_unique( Context::$to_delete );
	foreach ( $to_delete as $file ) {
		if ( file_exists( $file ) ) {
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

$qitRunner = new QitRunner( $logger, $phpUnitRunner, $liveOutput );

// Generate zips
foreach ( $tests_to_run as $test_type => $test_type_test_runs ) {
	$zipManager->generate_zips( $test_type_test_runs );
}

// Run tests
try {
	$qitRunner->run_test_runs( $tests_to_run, $tests_based_on_custom_tests );
} catch ( \Exception $e ) {
	$logger->log( "Exception: " . $e->getMessage() );
	echo $e->getMessage() . "\nExiting by exception\n";
	die( 1 );
}