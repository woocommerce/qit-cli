<?php

use QIT_CLI\App;
use QIT_CLI\IO\Input;
use QIT_CLI\IO\Output;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/helpers.php';

// Initialize DI container.
$container = new \lucatume\DI52\Container();
\QIT_CLI\App::setContainer( $container );
$container->singleton( \QIT_CLI\Config::class );

// Initialize Console.
$application = new class( 'Quality Insights Toolkit CLI', '@QIT_CLI_VERSION@' ) extends Application {
	// Expose protected method.
	// phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
	public function configureIO( InputInterface $input, OutputInterface $output ) {
		parent::configureIO( $input, $output );
	}

	public function getDefaultCommands() {
		return array_merge(
			parent::getDefaultCommands(),
			[ new CompletionCommand() ]
		);
	}
};
$application->setAutoExit( true );
$application->setCatchExceptions( true );
$application->find( 'completion' )->setHidden( true );
$application->find( 'list' )->setHidden( true );

// Define a global input/output for convenience.
$container->singleton( Input::class, function () {
	return new ArgvInput();
} );
$container->singleton( Output::class, function () {
	return new ConsoleOutput();
} );
$application->configureIO( $container->make( Input::class ), $container->make( Output::class ) );

$container->setVar( 'doing_autocompletion', stripos( (string) $container->make( Input::class ), '_completion' ) !== false );

// Global commands.
$application->add( $container->make( \QIT_CLI\Commands\InitA8CCommand::class ) );
$application->add( $container->make( \QIT_CLI\Commands\InitCommand::class ) );

// Commands that require initialization.
if ( $container->make( \QIT_CLI\Config::class )->is_initialized() ) {
	$do_manager_requests = function () use ( $container, $application ) {
		// Fetch the available test types from the Manager.
		$container->make( \QIT_CLI\TestTypes::class )->maybe_update_test_types();

		// Dynamically create commands to run tests, based on Schema fetched from Manager REST API.
		$container->make( \QIT_CLI\Commands\CreateRunCommands::class )->register_run_commands( $application );
	};

	// Send remote request to the Manager to update the CLI.
	$do_manager_requests();

	// List tests runs.
	$application->add( $container->make( \QIT_CLI\Commands\ListCommand::class ) );

	// Get a single test run.
	$application->add( $container->make( \QIT_CLI\Commands\GetCommand::class ) );

	// List the Woo Extensions the user can run tests against.
	$application->add( $container->make( \QIT_CLI\Commands\WooExtensionsCommand::class ) );
}

// Handle CLI request.
$application->run();
