<?php

use lucatume\DI52\App;
use lucatume\DI52\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\Output;

if ( ! file_exists( __DIR__ . '/../../qit' ) ) {
	echo sprintf( 'QIT CLI not found in %s', __DIR__ . '/../../qit' );
	exit( 1 );
}

try {
	require_once __DIR__ . '/vendor/autoload.php';
	require_once __DIR__ . '/QITTestRunner.php';

	// Initialize DI container.
	$container = new Container();
	App::setContainer( $container );

	App::setVar( 'qit', __DIR__ . '/../../qit' );

	$application = new Application();
	$application->add( new QITTestRunner() );

	exit( $application->run() );
} catch ( \Exception $e ) {
	App::make( Output::class )->writeln( "<error>{$e->getMessage()}</error>" );
	exit( 1 );
}