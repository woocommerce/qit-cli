<?php

use lucatume\DI52\Container;
use QIT_CLI\App;
use QIT_CLI\IO\Output;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\NullOutput;
use function QIT_CLI\get_manager_url;

define( 'UNIT_TESTS', true );

function qit_tests_clean_config_dir() {
	/** @var SplFileInfo $item */
	foreach ( new DirectoryIterator( '/tmp/.woo-qit-tests' ) as $item ) {
		if ( $item->isFile() && $item->getBasename() !== '.' && $item->getBasename() !== '..' ) {
			if ( ! unlink( $item->getPathname() ) ) {
				throw new RuntimeException( 'Could not cleanup config dir.' );
			}
		}
	}
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/zip-validation.php';

if ( ! file_exists( '/tmp/.woo-qit-tests' ) ) {
	if ( ! mkdir( '/tmp/.woo-qit-tests' ) ) {
		throw new RuntimeException( 'Could not create config dir for tests..' );
	}
} else {
	qit_tests_clean_config_dir();
}

putenv( 'QIT_HOME=/tmp/.woo-qit-tests' );

// Initialize DI container.
$container = new Container();
App::setContainer( $container );

// Temporary Output for pre-bootstrapping.
App::bind( Output::class, NullOutput::class );

// Mocks the response of a sync request with local data.
App::setVar( sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/cli/sync' ), file_get_contents( __DIR__ . '/data/sync.json' ) );

/** @var Application $qit_application */
$GLOBALS['qit_application'] = require_once __DIR__ . '/../src/bootstrap.php';
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
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( __DIR__ . '/../src/Commands', FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
	if ( $file->isFile() && $file->getExtension() === 'php' ) {
		$command = rtrim( $it->getSubPathname(), '.php' );
		$command = sprintf( 'QIT_CLI\\Commands\\%s', str_replace( '/', '\\', $command ) );
		if ( ! class_exists( $command ) ) {
			continue;
		}
		if ( ! ( new ReflectionClass( $command ) )->isSubclassOf( Command::class ) ) {
			continue;
		}
		if ( is_null( $command::getDefaultName() ) ) {
			continue;
		}
		/** @var Command $command */
		if ( ! $GLOBALS['qit_application']->has( $command::getDefaultName() ) ) {
			$GLOBALS['qit_application']->add( App::make( $command ) );
		}
	}
}

define( 'UNIT_TESTS_BOOTSTRAPPED', true );
