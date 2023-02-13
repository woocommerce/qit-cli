<?php

use lucatume\DI52\Container;
use QIT_CLI\App;
use QIT_CLI\IO\Output;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/helpers.php';

// Initialize DI container.
$container = new Container();
App::setContainer( $container );

try {
	$application = require_once __DIR__ . '/src/bootstrap.php';
	
	// Handle CLI request.
	exit( $application->run() );
} catch ( \Exception $e ) {
	App::make( Output::class )->writeln( "<error>{$e->getMessage()}</error>" );
	exit( 1 );
}
