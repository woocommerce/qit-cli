<?php

use lucatume\DI52\Container;
use QIT_CLI\App;
use Symfony\Component\Console\Application;
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

if ( ! file_exists( '/tmp/.woo-qit-tests' ) ) {
	if ( ! mkdir( '/tmp/.woo-qit-tests' ) ) {
		throw new RuntimeException( 'Could not create config dir for tests..' );
	}
} else {
	qit_tests_clean_config_dir();
}

putenv( 'QIT_CLI_CONFIG_DIR=/tmp/.woo-qit-tests' );

// Initialize DI container.
$container = new Container();
App::setContainer( $container );

App::setVar( sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/cli/sync' ), file_get_contents( __DIR__ . '/data/sync.json' ) );

/** @var Application $qit_application */
$GLOBALS['qit_application'] = require_once __DIR__ . '/../bootstrap.php';
$GLOBALS['qit_application']->setAutoExit( false );

define( 'UNIT_TESTS_BOOTSTRAPPED', true );