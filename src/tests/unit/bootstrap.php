<?php

namespace QIT_CLI_Tests;

use Exception;
use FilesystemIterator;
use lucatume\DI52\Container;
use QIT_CLI\App;
use QIT_CLI\IO\Output;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\NullOutput;
use function QIT_CLI\get_manager_url;

echo "Fetching latest sync.json from production QIT Manager... ";
require_once __DIR__ . '/data/pull-sync-json.php';
echo "Done.\n";

define( 'UNIT_TESTS', true );

function qit_tests_reset_config_dir() {
	// Must match the QIT_HOME set below. On macOS, sys_get_temp_dir() is not "/tmp",
	// so hardcoding "/tmp/.woo-qit-tests" here would leak state between test runs.
	$config_dir = sys_get_temp_dir() . '/.woo-qit-tests';

	exec( sprintf( 'rm -rf %s', escapeshellarg( $config_dir ) ) );

	if ( ! mkdir( $config_dir ) ) {
		throw new RuntimeException( 'Could not create config dir for tests..' );
	}

	if ( ! mkdir( $config_dir . '/environments' ) ) {
		throw new RuntimeException( 'Could not create environments dir for tests.' );
	}

	if ( ! file_exists( __DIR__ . '/data/environments/e2e.zip' ) ) {
		throw new RuntimeException( 'Could not find e2e environment for tests.' );
	}

	if ( ! copy( __DIR__ . '/data/environments/e2e.zip', $config_dir . '/environments/e2e.zip' ) ) {
		throw new RuntimeException( 'Could not copy e2e environment for tests.' );
	}
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helpers.php';

$verbose = false;

// Check if PHPUnit is running in verbose mode.
if ( isset( $_SERVER['argv'] ) && in_array( '--verbose', $_SERVER['argv'], true ) || in_array( '-v', $_SERVER['argv'], true ) ) {
	$verbose = true;
}

qit_tests_reset_config_dir();

putenv( sprintf( 'QIT_HOME=%s/.woo-qit-tests', sys_get_temp_dir() ) );

// Initialize DI container.
$container = new Container();
App::setContainer( $container );

// Temporary Output for pre-bootstrapping.
App::bind( Output::class, NullOutput::class );

// Mocks the response of a sync request with local data.
App::setVar( sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/cli/sync' ), file_get_contents( __DIR__ . '/data/sync.json' ) );

// Mock V2 sync endpoints.
App::setVar( sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v2/cli/sync' ), file_get_contents( __DIR__ . '/data/sync-v2.json' ) );
App::setVar( sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v2/cli/sync/extensions' ), file_get_contents( __DIR__ . '/data/sync-v2-extensions.json' ) );

// Set a mock manager secret so maybe_sync_extensions() passes the auth guard during bootstrap.
App::make( \QIT_CLI\Cache::class )->set( 'manager_secret', 'test-secret', -1 );

/** @var Application $qit_application */
$GLOBALS['qit_application'] = require_once __DIR__ . '/../../src/bootstrap.php';
$GLOBALS['qit_application']->setAutoExit( false );

/**
 * The bootstrap process will conditionally skip adding some commands,
 * such as "RemovePartner" command if there are no Partners added.
 *
 * For testing purposes, we register all available commands.
 *
 * For that, we loop over the Commands folder and add any Command that
 * is not registered yet.
 *
 * @var SplFileInfo $file
 * @var RecursiveDirectoryIterator $it
 */
$failed_to_build = [];
$it              = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( __DIR__ . '/../../src/Commands', FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
	if ( $file->isFile() && $file->getExtension() === 'php' && ! $file->isLink() ) {
		$content = file_get_contents( $file->getPathname() );

		$namespace = $class = null;

		// Match namespace and class from the file content
		if ( preg_match( '/namespace\s+([^;]+);/', $content, $matches ) ) {
			$namespace = $matches[1];
		}
		if ( preg_match( '/class\s+(\w+)/', $content, $matches ) ) {
			$class = $matches[1];
		}

		if ( is_null( $namespace ) || is_null( $class ) ) {
			if ( $verbose ) {
				echo "Skipping file without namespace or class: {$file->getPathname()}\n";
			}
			continue;
		}

		$fqdn = sprintf( '%s\\%s', $namespace, $class );

		if ( ! class_exists( $fqdn ) ) {
			if ( $verbose ) {
				echo "Skipping non-existing class: $fqdn\n";
			}
			continue;
		}

		if ( ! ( new ReflectionClass( $fqdn ) )->isSubclassOf( Command::class ) ) {
			if ( $verbose ) {
				echo "Skipping non-command class: $fqdn\n";
			}
			continue;
		}
		if ( ! ( new ReflectionClass( $fqdn ) )->isInstantiable() ) {
			if ( $verbose ) {
				echo "Skipping non-instantiable class: $fqdn\n";
			}
			continue;
		}
		if ( is_null( $fqdn::getDefaultName() ) ) {
			if ( $verbose ) {
				echo "Skipping command without default name: $fqdn\n";
			}
			continue;
		}

		if ( ! $GLOBALS['qit_application']->has( $fqdn::getDefaultName() ) ) {
			if ( $verbose ) {
				echo "Adding command: $fqdn\n";
			}
			try {
				$GLOBALS['qit_application']->add( App::make( $fqdn ) );
			} catch ( Exception $e ) {
				$failed_to_build[] = $fqdn;
			}
		} else {
			if ( $verbose ) {
				echo "Skipping already added command: $fqdn\n";
			}
		}
	}
}
if ( ! $verbose ) {
	// Mention they can use verbose to see which commands are being added.
	echo "Use --verbose to see which commands are being added.\n";
}
/*
 * Commands that use "reuseOption" might require a specific load order, which is respected
 * on our manual bootstrap.php, but not here.
 *
 * In that case, we "defer" any command that fails to add and try to add them again
 * after all other commands have been added.
 */
if ( ! empty( $failed_to_build ) ) {
	foreach ( $failed_to_build as $fqdn ) {
		echo "Adding deferred command: $fqdn\n";
		$GLOBALS['qit_application']->add( App::make( $fqdn ) );
	}
}

define( 'UNIT_TESTS_BOOTSTRAPPED', true );