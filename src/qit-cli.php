<?php

use lucatume\DI52\Container;
use QIT_CLI\App;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Application;

try {
	define( 'QIT_ABSPATH', __DIR__ );
	require_once __DIR__ . '/vendor/autoload.php';
	require_once __DIR__ . '/src/helpers.php';

	// Initialize DI container.
	$container = new Container();
	App::setContainer( $container );

	/** @var Application $application */
	$application = require_once __DIR__ . '/src/bootstrap.php';

	// Handle CLI request.
	exit( $application->run() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} catch ( \Exception $e ) {
	App::make( Output::class )->writeln( "<error>{$e->getMessage()}</error>" );
	exit( 1 );
}
