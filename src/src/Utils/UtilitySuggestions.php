<?php

declare( strict_types=1 );

namespace QIT_CLI\Utils;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\QITEnvInfo;
use Symfony\Component\Console\Output\OutputInterface;

class UtilitySuggestions {

	/**
	 * Show a tip about available utility packages for plugins in the environment.
	 *
	 * Checks the Manager's default_utilities map (from sync data) against the
	 * plugins in the environment. If any suggested utilities aren't already
	 * included as test packages, prints a one-line tip.
	 *
	 * @param OutputInterface $output Console output.
	 * @param QITEnvInfo      $env_info Environment info with resolved plugins and test packages.
	 */
	public static function render( OutputInterface $output, QITEnvInfo $env_info ): void {
		$default_utilities = App::make( Cache::class )->get_manager_sync_data( 'default_utilities' );

		if ( empty( $default_utilities ) || ! is_array( $default_utilities ) ) {
			return;
		}

		// Collect plugin slugs in this environment.
		$plugin_slugs = [];
		foreach ( $env_info->plugins as $plugin ) {
			$plugin_slugs[] = $plugin->slug;
		}

		// Collect already-included package identifiers (strip version for matching).
		$included_packages = [];
		foreach ( array_keys( $env_info->test_packages_for_setup ) as $pkg_id ) {
			// Package IDs may include version (e.g., "woocommerce/baseline:latest"), strip it.
			$included_packages[] = explode( ':', (string) $pkg_id )[0];
		}
		// Also check test_packages_metadata for packages added via utilities config.
		foreach ( array_keys( $env_info->test_packages_metadata ) as $pkg_id ) {
			$included_packages[] = explode( ':', (string) $pkg_id )[0];
		}

		// Find suggestions that aren't already included.
		$suggestions = [];
		foreach ( $plugin_slugs as $slug ) {
			if ( ! isset( $default_utilities[ $slug ] ) || ! is_array( $default_utilities[ $slug ] ) ) {
				continue;
			}
			foreach ( $default_utilities[ $slug ] as $utility_ref ) {
				$utility_slug = explode( ':', $utility_ref )[0];
				if ( ! in_array( $utility_slug, $included_packages, true ) ) {
					$suggestions[] = $utility_ref;
				}
			}
		}

		if ( empty( $suggestions ) ) {
			return;
		}

		$output->writeln( '' );
		$output->writeln( '<fg=cyan>Tip:</> Utility packages are available for plugins in this environment:' );
		foreach ( $suggestions as $suggestion ) {
			$output->writeln( sprintf( '  <info>--test-package=%s</info>', $suggestion ) );
		}
	}
}
