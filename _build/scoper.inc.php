<?php

use Isolated\Symfony\Component\Finder\Finder;

$polyfillsBootstraps = \array_map(
	function ( SplFileInfo $fileInfo ) {
		return $fileInfo->getPathname();
	},
	\iterator_to_array(
		Finder::create()
		      ->files()
		      ->in( __DIR__ . '/../src-tmp/vendor/symfony/polyfill-*' )
		      ->name( 'bootstrap*.php' ),
		false,
	),
);

$polyfillsStubs = [];
try {
	$polyfillsStubs = \array_map(
		function ( SplFileInfo $fileInfo ) {
			return $fileInfo->getPathname();
		},
		\iterator_to_array(
			Finder::create()
			      ->files()
			      ->in( __DIR__ . '/../src-tmp/vendor/symfony/polyfill-*/Resources/stubs' )
			      ->name( '*.php' ),
			false,
		),
	);
} catch ( Throwable $e ) {
	// There may not be any stubs?
}

return [
	'exclude-namespaces' => [
		'Symfony\Polyfill'
	],
	'exclude-constants' => [
		// Symfony global constants
		'/^SYMFONY\_[\p{L}_]+$/',
	],
	'exclude-files' => \array_merge( $polyfillsBootstraps, $polyfillsStubs ),
];
