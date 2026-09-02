<?php

namespace QIT_CLI\Commands;

use QIT_CLI\PreCommand\Configuration\EnvironmentConfigResolver;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Picks a test package version to match the WooCommerce version under test.
 *
 * Shared by the commands whose package is tied to a WooCommerce release: the
 * Core E2E suite is a port whose assertions belong to a version, and the
 * activation suite drives WooCommerce\'s admin UI, whose markup changes between
 * versions. One implementation on purpose — the Manager applies the same rule
 * for the runs it creates itself, and a third copy would be one too many.
 *
 * A using command declares:
 *
 * - `PACKAGE_TEST_TYPE`: the key it is published under in sync data.
 * - `FALLBACK_TEST_PACKAGE`: what to run when nothing covers the version.
 */
trait SelectsVersionedTestPackage {
	/**
	 * The package covering the WooCommerce version this run asks for.
	 *
	 * Nothing is worked out here on purpose. The Manager resolves it and hands
	 * over a lookup table in sync data, keyed by exactly what `--woo` accepts,
	 * channel names included: which package versions exist is the Manager's to
	 * know, and a second implementation of the rule would drift from it.
	 *
	 * This used to be a single hardcoded `latest` for every run, so a run on one
	 * WooCommerce version executed the same specs as a run on any other.
	 */
	protected function resolve_test_package( QITInput $input, OutputInterface $output ): string {
		$requested = self::pinned_woocommerce_version( $input );

		/*
		 * Nothing pinned a version, so this assumes `stable`. That is an
		 * assumption rather than something observed, and nothing here can observe
		 * it: the package supplies mu-plugins and a globalSetup phase that go into
		 * building the environment, so it has to be chosen before `env:up` reports
		 * a version.
		 *
		 * It holds because two independent sources agree on what "latest stable"
		 * is. The Manager derives `stable` from the WooCommerce GitHub releases,
		 * while an unpinned environment installs WooCommerce to satisfy the
		 * package manifest's `requires.plugins`, which resolves to the wp.org
		 * stable tag. They can disagree for a few hours around a release, and a
		 * run started in that window gets the package for the previous version.
		 */
		if ( $requested === '' ) {
			$requested = 'stable';
		}

		$requested = $this->resolve_channel( $requested );

		try {
			$offered = $this->cache->get_manager_sync_data( 'test_package_versions' );
		} catch ( \Throwable $e ) {
			// Absent on a Manager that does not publish package versions yet.
			$output->writeln( sprintf(
				'<comment>This QIT Manager does not publish test package versions. Using %s.</comment>',
				static::FALLBACK_TEST_PACKAGE
			) );

			return static::FALLBACK_TEST_PACKAGE;
		}

		$package  = is_array( $offered ) ? ( $offered[ static::PACKAGE_TEST_TYPE ]['package'] ?? null ) : null;
		$versions = is_array( $offered ) ? ( $offered[ static::PACKAGE_TEST_TYPE ]['versions'] ?? null ) : null;

		$covering = is_string( $package ) && is_array( $versions )
			? self::covering_version( $requested, $versions )
			: null;

		if ( $covering === null ) {
			// Every WooCommerce version older than the oldest published package
			// lands here, and so does one too new to have a package yet. The run
			// goes ahead on the default, which is worth saying out loud: the suite
			// it runs was not written for the version it is running against.
			$output->writeln( sprintf(
				'<comment>No test package covers WooCommerce %s. Using %s instead.</comment>',
				$requested,
				static::FALLBACK_TEST_PACKAGE
			) );

			return static::FALLBACK_TEST_PACKAGE;
		}

		$test_package = $package . ':' . $covering;

		$output->writeln( sprintf(
			'<comment>Using test package %s for WooCommerce %s.</comment>',
			$test_package,
			$requested
		) );

		return $test_package;
	}

	/**
	 * A channel name replaced by the version it stands for.
	 *
	 * `--woo stable` and `--woo rc` name a channel, not a version, and what they
	 * currently mean is in sync data already. Resolving them here keeps the
	 * matching below working on versions only.
	 */
	private function resolve_channel( string $requested ): string {
		if ( ! in_array( $requested, [ 'stable', 'rc' ], true ) ) {
			return $requested;
		}

		try {
			$versions = $this->cache->get_manager_sync_data( 'versions' );
		} catch ( \Throwable $e ) {
			return $requested;
		}

		$resolved = is_array( $versions ) ? ( $versions['woocommerce'][ $requested ] ?? null ) : null;

		return is_scalar( $resolved ) && trim( (string) $resolved ) !== '' ? trim( (string) $resolved ) : $requested;
	}

	/**
	 * The published version covering a WooCommerce version: the highest whose
	 * `major.minor` is not above the requested one.
	 *
	 * A package version covers a whole WooCommerce `major.minor`, so 11.0.0 and
	 * 11.0.1 both take `11.0`, and a prerelease keeps the version it belongs to:
	 * 11.2.0-beta.1 is 11.2. Falling to an older one is deliberate — it is
	 * reproducible, where resolving to a moving tag is not.
	 *
	 * The Manager applies the same rule for the runs it creates itself. It cannot
	 * apply it for this one: the package is chosen before the Manager is told the
	 * run exists, and any WooCommerce version may be asked for.
	 *
	 * @param string            $woocommerce_version The version the run asked for.
	 * @param array<int, mixed> $published           Published versions, as sync data lists them.
	 */
	private static function covering_version( string $woocommerce_version, array $published ): ?string {
		$requested = self::major_minor( $woocommerce_version );

		if ( $requested === null ) {
			return null;
		}

		$covering = null;

		foreach ( $published as $version ) {
			if ( ! is_scalar( $version ) ) {
				continue;
			}

			$version = trim( (string) $version );

			// Only an exactly two-segment version names a package version. This
			// keeps `latest`, and anything carrying a patch or a suffix, out.
			if ( self::major_minor( $version ) !== $version ) {
				continue;
			}

			if ( version_compare( $version, $requested, '>' ) ) {
				continue;
			}

			if ( $covering === null || version_compare( $version, $covering, '>' ) ) {
				$covering = $version;
			}
		}

		return $covering;
	}

	private static function major_minor( string $version ): ?string {
		if ( preg_match( '/^(\d+)\.(\d+)(?:\D|$)/', trim( $version ), $matches ) !== 1 ) {
			return null;
		}

		return $matches[1] . '.' . $matches[2];
	}

	/**
	 * The WooCommerce version this run will install, as far as it can be known here.
	 *
	 * Three places can pin one, and `env:up` reads all three, so this has to as
	 * well or the suite and the environment diverge — which is what this command
	 * exists to prevent. In its precedence order:
	 *
	 * 1. `--woo`, and 2. a test profile, both of which `get_environment_options()`
	 *    already merges with the flag winning. That array is what is handed to
	 *    `env:up`.
	 * 3. The selected environment block. `get_environment_options()` passes this
	 *    one on as `--environment` and lets `env:up` resolve it later, so the
	 *    version is not in that array and has to be read from the block.
	 *
	 * Returns an empty string when nothing pins a version.
	 */
	private static function pinned_woocommerce_version( QITInput $input ): string {
		$from_options = $input->get_environment_options()['--woocommerce_version'] ?? null;

		if ( is_scalar( $from_options ) && trim( (string) $from_options ) !== '' ) {
			return trim( (string) $from_options );
		}

		$environment = EnvironmentConfigResolver::normalize_aliases( $input->get_environment_config() );
		$from_block  = $environment['woocommerce_version'] ?? null;

		if ( is_scalar( $from_block ) && trim( (string) $from_block ) !== '' ) {
			return trim( (string) $from_block );
		}

		// An environment may pin WooCommerce as a plugin entry instead of a scalar.
		foreach ( ( is_array( $environment['plugins'] ?? null ) ? $environment['plugins'] : [] ) as $plugin ) {
			if ( ! is_array( $plugin ) || ( $plugin['slug'] ?? '' ) !== 'woocommerce' ) {
				continue;
			}

			$version = $plugin['version'] ?? null;

			if ( is_scalar( $version ) && trim( (string) $version ) !== '' ) {
				return trim( (string) $version );
			}
		}

		return '';
	}
}
