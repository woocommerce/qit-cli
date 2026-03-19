#!/usr/bin/env php
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

	// Normalize option aliases in argv before Symfony Console processes them.
	// Long forms are canonical (--php_version, --wordpress_version, --woocommerce_version).
	// Short forms (--php, --wp, --woo) are user-friendly aliases that get normalized to long forms.
	// IMPORTANT: Fail fast if both forms are provided (no fallbacks per CLAUDE.md).
	$aliases = [
		'--php' => '--php_version',
		'--wp'  => '--wordpress_version',
		'--woo' => '--woocommerce_version',
	];

	// First pass: detect conflicts before normalizing
	$found_params = [];
	foreach ( $_SERVER['argv'] ?? [] as $arg ) {
		foreach ( $aliases as $from => $to ) {
			// Check if this argument uses the alias or canonical form
			$is_alias_form     = ( strpos( $arg, $from . '=' ) === 0 || $arg === $from );
			$is_canonical_form = ( strpos( $arg, $to . '=' ) === 0 || $arg === $to );

			if ( $is_alias_form || $is_canonical_form ) {
				// Track which parameter group this belongs to (both --php and --php_version map to "php_version")
				$param_group = trim( $to, '-' );

				// If we already saw a parameter in this group, it's a conflict
				if ( isset( $found_params[ $param_group ] ) ) {
					fwrite(
						STDERR,
						sprintf(
							"Error: Cannot specify both \"%s\" and \"%s\". Use one or the other.\n",
							trim( $from, '-' ),
							trim( $to, '-' )
						)
					);
					exit( 1 );
				}

				$found_params[ $param_group ] = true;
			}
		}
	}

	// Second pass: normalize (now that we know there are no conflicts)
	foreach ( $_SERVER['argv'] ?? [] as $i => $arg ) {
		foreach ( $aliases as $from => $to ) {
			// Handle --alias=value format
			if ( strpos( $arg, $from . '=' ) === 0 ) {
				$_SERVER['argv'][ $i ] = str_replace( $from . '=', $to . '=', $arg );
				break;
			}
			// Handle --alias value format (two separate args)
			if ( $arg === $from && isset( $_SERVER['argv'][ $i + 1 ] ) && strpos( $_SERVER['argv'][ $i + 1 ], '-' ) !== 0 ) {
				$_SERVER['argv'][ $i ] = $to;
				break;
			}
		}
	}

	// Initialize DI container.
	$container = new Container();
	App::setContainer( $container );

	/** @var Application $application */
	$application = require_once __DIR__ . '/src/bootstrap.php';

	// Handle CLI request.
	exit( $application->run() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} catch ( \Exception $e ) {
	$io = new SymfonyStyle( App::make( \QIT_CLI\IO\Input::class ), App::make( Output::class ) );
	$io->error( $e->getMessage() );
	exit( 1 );
}
