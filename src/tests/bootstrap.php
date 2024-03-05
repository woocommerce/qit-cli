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
	exec( 'rm -rf /tmp/.woo-qit-tests' );
}


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers.php';

qit_tests_clean_config_dir();

if ( ! mkdir( '/tmp/.woo-qit-tests' ) ) {
	throw new RuntimeException( 'Could not create config dir for tests..' );
}

if ( ! mkdir( '/tmp/.woo-qit-tests/environments' ) ) {
	throw new RuntimeException( 'Could not create environments dir for tests.' );
}

if ( ! file_exists( __DIR__ . '/data/environments/e2e.zip' ) ) {
	throw new RuntimeException( 'Could not find e2e environment for tests.' );
}

if ( ! copy( __DIR__ . '/data/environments/e2e.zip', '/tmp/.woo-qit-tests/environments/e2e.zip' ) ) {
	throw new RuntimeException( 'Could not copy e2e environment for tests.' );
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
			continue;
		}

		$fqdn = sprintf( '%s\\%s', $namespace, $class );

		if ( ! class_exists( $fqdn ) ) {
			continue;
		}

		if ( ! ( new ReflectionClass( $fqdn ) )->isSubclassOf( Command::class ) ) {
			continue;
		}
		if ( is_null( $fqdn::getDefaultName() ) ) {
			continue;
		}

		if ( ! $GLOBALS['qit_application']->has( $fqdn::getDefaultName() ) ) {
			echo "Adding command: " . $fqdn::getDefaultName() . PHP_EOL;
			$GLOBALS['qit_application']->add( App::make( $fqdn ) );
		}
	}
}

define( 'UNIT_TESTS_BOOTSTRAPPED', true );
