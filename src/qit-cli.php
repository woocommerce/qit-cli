<?php

use lucatume\DI52\Container;
use QIT_CLI\App;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Style\SymfonyStyle;

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
	$container->setVar( 'QIT_JSON_MODE', true );
	if ( App::offsetGet( 'QIT_JSON_MODE' ) === true ) {
		// If JSON mode is enabled, output the error as JSON.
		\QIT_CLI\JsonOutput::output_throwable_as_json( $e );
	}
	$io = new SymfonyStyle( App::make( \QIT_CLI\IO\Input::class ), App::make( Output::class ) );
	$io->error( $e->getMessage() );
	exit( 1 );
}
