<?php
/*
 * This file will watch for changes to the CD Client and
 * re-generate the Phar if any relevant files changes.
 *
 * Usage: php watch.php
 */

$md5_sum = null;

$it = new AppendIterator();

// Watch all files in the root of the "project" folder.
$it->append( new FilesystemIterator( __DIR__ . '/../src', FilesystemIterator::SKIP_DOTS ) );

// Watch all files recursively in the "src" folder.
$it->append( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( __DIR__ . '/../src/src', FilesystemIterator::SKIP_DOTS ) ) );

$debug = in_array( '--debug', $argv );

do {
	$hashes = [];
	$it->rewind();
	echo $debug ? "\nChecking for changes on the following files:\n" : '';
	/** @var FilesystemIterator $it */
	while ( $it->valid() ) {
		if ( $it->isFile() ) {
			echo $debug ? $it->getPathname() . "\n" : '';
			$hashes[] = md5( $it->getMTime() . $it->getSize() );
		}
		$it->next();
	}
	$current_md5_sum = implode( '', $hashes );
	if ( $current_md5_sum !== $md5_sum ) {
		$md5_sum = $current_md5_sum;
		echo 'Re-building... ';
		$start = microtime( true );
		shell_exec( 'cd .. && make build' );
		echo sprintf( "Done. (%ss)\n", number_format( microtime( true ) - $start, 3 ) );
	}

	sleep( 2 );
} while ( true );
