<?php

use QIT_CLI\App;
use QIT_CLI\Commands\CreateRunCommands;
use QIT_CLI\Commands\DevModeCommand;
use QIT_CLI\Commands\Encrypt\ChangeEncryptionKeyCommand;
use QIT_CLI\Commands\Encrypt\DisableEncryptionCommand;
use QIT_CLI\Commands\Encrypt\EnableEncryptionCommand;
use QIT_CLI\Commands\Environment\AddEnvironment;
use QIT_CLI\Commands\Environment\CurrentEnvironment;
use QIT_CLI\Commands\Environment\RemoveEnvironment;
use QIT_CLI\Commands\Environment\SwitchEnvironment;
use QIT_CLI\Commands\GetCommand;
use QIT_CLI\Commands\ListCommand;
use QIT_CLI\Commands\Partner\AddPartner;
use QIT_CLI\Commands\Partner\RemovePartner;
use QIT_CLI\Commands\Partner\SwitchPartner;
use QIT_CLI\Commands\WooExtensionsCommand;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Encryption;
use QIT_CLI\Environment;
use QIT_CLI\Exceptions\NetworkErrorException;
use QIT_CLI\Exceptions\UpdateRequiredException;
use QIT_CLI\IO\Input;
use QIT_CLI\IO\Output;
use QIT_CLI\ManagerSync;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

if ( ! isset( $container ) ) {
	throw new LogicException( 'This file must be called from the context where a $container is defined.' );
}

App::setVar( 'CLI_VERSION', '@QIT_CLI_VERSION@' );

// Initialize Console.
$application = new class( 'Quality Insights Toolkit CLI', App::getVar( 'CLI_VERSION' ) ) extends Application {
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

$container->singleton( ManagerSync::class );
$container->singleton( Encryption::class );
$container->singleton( Cache::class );
$container->singleton( Config::class );

$application->configureIO( $container->make( Input::class ), $container->make( Output::class ) );

// Detect whether this is a "_completion" command that runs on the background in Bash. If so, no remote requests will be made.
$container->setVar( 'doing_autocompletion', stripos( (string) $container->make( Input::class ), '_completion' ) !== false );

$container->make( Encryption::class )->init();

try {
	if ( ! $container->getVar( 'doing_autocompletion' ) ) {
		App::make( ManagerSync::class )->maybe_sync();
		App::make( ManagerSync::class )->enforce_latest_version();
	}
} catch ( UpdateRequiredException $e ) {
	exit( 1 );
} catch ( NetworkErrorException $e ) {
	// If we got here, this means the Manager is not accessible or is responding with an error.
	App::setVar( 'offline_mode', true );

	$application->add( $container->make( DevModeCommand::class ) );
	$application->add( $container->make( AddEnvironment::class ) );

	$application->add( $container->make( ChangeEncryptionKeyCommand::class ) );
	$application->add( $container->make( EnableEncryptionCommand::class ) );
	$application->add( $container->make( DisableEncryptionCommand::class ) );

	// Run a quick diagnose check to see what might be happening and provide some feedback to the user.
	( new \QIT_CLI\Diagnosis() )->run( App::make( Output::class ) );

	echo $e->getMessage(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	return $application;
}

$env = App::make( Environment::class );

// Encryption commands.
$application->add( $container->make( ChangeEncryptionKeyCommand::class ) );
$application->add( $container->make( EnableEncryptionCommand::class ) );
$application->add( $container->make( DisableEncryptionCommand::class ) );

// Global commands.
$application->add( $container->make( DevModeCommand::class ) );

// Partner commands.
$application->add( $container->make( AddPartner::class ) );

// Only show option to Remove Partner if there are Partners to remove.
if ( count( $env->get_configured_environments( true ) ) > 0 ) {
	$application->add( $container->make( RemovePartner::class ) );
}

// Only show option to Switch to another partner if there are more than one Partner.
if ( count( $env->get_configured_environments( true ) ) > 1 ) {
	$application->add( $container->make( SwitchPartner::class ) );
}

// Dev commands.
if ( Config::is_development_mode() ) {
	$application->add( $container->make( AddEnvironment::class ) );

	// Only show options to remove and see the current environment if there's at least one environment added.
	if ( count( $env->get_configured_environments( false ) ) > 0 ) {
		$application->add( $container->make( RemoveEnvironment::class ) );
		$application->add( $container->make( CurrentEnvironment::class ) );
	}

	// Only show option to Switch to another environment if there are more than one environment.
	if ( count( $env->get_configured_environments( false ) ) > 1 ) {
		$application->add( $container->make( SwitchEnvironment::class ) );
	}
}

// Commands that require initialization.
if ( $container->make( Cache::class )->is_initialized() ) {
	// Dynamically create commands to run tests, based on Schema fetched from Manager REST API.
	$container->make( CreateRunCommands::class )->register_run_commands( $application );

	// List tests runs.
	$application->add( $container->make( ListCommand::class ) );

	// Get a single test run.
	$application->add( $container->make( GetCommand::class ) );

	// List the Woo Extensions the user can run tests against.
	$application->add( $container->make( WooExtensionsCommand::class ) );
}

if ( $container->make( Output::class )->isVerbose() ) {
	$container->make( Output::class )->writeln( sprintf( '<info>QIT Environment: %s</info>', Config::get_current_environment() ) );
}

return $application;