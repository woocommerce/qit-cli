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
 * A using command declares two things: the key it is published under in sync
 * data, and what to run when nothing covers the version.
 */
trait SelectsVersionedTestPackage {
	/**
	 * The key this package is published under in sync data.
	 *
	 * Methods rather than constants: constants in traits need PHP 8.2 and this
	 * runs from 7.4, and `static::` on a constant a trait does not declare is
	 * invisible to static analysis either way.
	 */
	abstract protected function package_test_type(): string;

	/** What to run when no published version covers the WooCommerce version. */
	abstract protected function fallback_test_package(): string;

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
		$options = $input->get_environment_options();

		// `--json` makes stdout a payload, and a notice printed alongside it is a
		// parse error for whatever reads it. RunE2ECommand guards its own output
		// the same way.
		$speak     = empty( $options['--json'] );
		$requested = $this->pinned_woocommerce_version( $input );

		if ( $requested === null ) {
			// WooCommerce is the extension under test and arrives as an artifact, so
			// which version the environment ends up with is inside that ZIP. Say so
			// rather than assume: `--test-package` names one outright, and both
			// commands leave a named package alone.
			$this->announce( $output, $speak, sprintf(
				'<comment>WooCommerce is the extension under test, so its version is not known before the environment is built. Using %s.</comment>',
				$this->fallback_test_package()
			) );

			return $this->fallback_test_package();
		}

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
			$this->announce( $output, $speak, sprintf(
				'<comment>This QIT Manager does not publish test package versions. Using %s.</comment>',
				$this->fallback_test_package()
			) );

			return $this->fallback_test_package();
		}

		$package  = is_array( $offered ) ? ( $offered[ $this->package_test_type() ]['package'] ?? null ) : null;
		$versions = is_array( $offered ) ? ( $offered[ $this->package_test_type() ]['versions'] ?? null ) : null;

		$covering = is_string( $package ) && is_array( $versions )
			? self::covering_version( $requested, $versions )
			: null;

		if ( $covering === null ) {
			// Every WooCommerce version older than the oldest published package
			// lands here, and so does one too new to have a package yet. The run
			// goes ahead on the default, which is worth saying out loud: the suite
			// it runs was not written for the version it is running against.
			$this->announce( $output, $speak, sprintf(
				'<comment>No test package covers WooCommerce %s. Using %s instead.</comment>',
				$requested,
				$this->fallback_test_package()
			) );

			return $this->fallback_test_package();
		}

		$test_package = $package . ':' . $covering;

		$this->announce( $output, $speak, sprintf(
			'<comment>Using test package %s for WooCommerce %s.</comment>',
			$test_package,
			$requested
		) );

		return $test_package;
	}

	/** Writes a line unless the caller asked for machine-readable output. */
	private function announce( OutputInterface $output, bool $speak, string $message ): void {
		if ( $speak ) {
			$output->writeln( $message );
		}
	}

	/**
	 * A channel name replaced by the version it stands for.
	 *
	 * `--woo stable` and `--woo rc` name a channel, not a version, and what they
	 * currently mean is in sync data already. Resolving them here keeps the
	 * matching below working on versions only.
	 *
	 * `rc` is read from `rc_unsynced`, which is the key `VersionResolver::resolve_woo()`
	 * builds its download URL from — so this follows the release the environment
	 * actually installs. The Manager fills both keys from one call today, but they
	 * are separate keys, and the one that decides the environment is the one to
	 * follow. `rc` remains the fallback for a Manager that publishes only it.
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

		$woocommerce = is_array( $versions ) && is_array( $versions['woocommerce'] ?? null )
			? $versions['woocommerce']
			: [];

		$keys = $requested === 'rc' ? [ 'rc_unsynced', 'rc' ] : [ $requested ];

		foreach ( $keys as $key ) {
			$resolved = $woocommerce[ $key ] ?? null;

			if ( is_scalar( $resolved ) && trim( (string) $resolved ) !== '' ) {
				return trim( (string) $resolved );
			}
		}

		return $requested;
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
	 * `env:up` merges the environment block with the CLI overlay and then runs
	 * `resolveWooCommerceVersion()`, and what gets installed is the first request
	 * for the `woocommerce` slug to survive that. Two details decide the order,
	 * and neither is the one you would guess:
	 *
	 * - That filter drops a request whose slug is literally `woocommerce`, which a
	 *   raw `woocommerce:10.9.0` is not — the slug there is the whole string. So a
	 *   `--plugin` pin survives a scalar version, and the entry the scalar builds
	 *   is appended behind it.
	 * - With no scalar version nothing is dropped, and `ConfigMerger` keeps base
	 *   before overlay, so the environment's own request comes first.
	 *
	 * Hence: a `--plugin` pin outranks a scalar, a scalar clears the
	 * environment's requests, and without a scalar the environment wins.
	 *
	 * Returns an empty string when the winning request leaves the version to
	 * wp.org, and null when it cannot be known here at all — an artifact, whose
	 * version lives inside the ZIP.
	 */
	private function pinned_woocommerce_version( QITInput $input ): ?string {
		$options = $input->get_environment_options();

		// The SUT is added to the environment later and is never displaced, so it
		// settles the matter before any of the above.
		$sut = $this->woocommerce_sut( $input );

		if ( $sut !== null ) {
			return $sut['version'];
		}

		$environment = EnvironmentConfigResolver::normalize_aliases( $input->get_environment_config() );

		$scalar = self::first_scalar( [
			$options['--woocommerce_version'] ?? null,
			$environment['woocommerce_version'] ?? null,
		] );

		$requests = array_merge(
			self::woocommerce_requests( $environment['plugins'] ?? null, $scalar !== null ),
			self::woocommerce_requests( $options['--plugin'] ?? null, $scalar !== null )
		);

		if ( ! empty( $requests ) ) {
			return $requests[0];
		}

		return $scalar ?? '';
	}

	/**
	 * The extension under test, when it is WooCommerce.
	 *
	 * Null when it is anything else, so a caller can carry on. Otherwise the
	 * version it brings, which is null when it arrives as an artifact: the
	 * environment installs what that ZIP holds, no pin changes it, and nothing
	 * reads it before the environment is built.
	 *
	 * A numeric argument is a WooCommerce.com ID, which `RunE2ECommand` turns into
	 * a slug later on. The same lookup is done here off cached sync data, so
	 * `run:activation <woo id> --zip` is recognised as well as the slug form.
	 *
	 * @return array{version: string|null}|null
	 */
	private function woocommerce_sut( QITInput $input ): ?array {
		$sut = $input->get_sut();

		if ( ! is_array( $sut ) ) {
			return null;
		}

		$slug = (string) ( $sut['slug'] ?? '' );

		if ( is_numeric( $slug ) ) {
			try {
				$slug = $this->woo_extensions_list->get_woo_extension_slug_by_id( (int) $slug );
			} catch ( \Throwable $e ) {
				// An id this cannot resolve is one it cannot rule out either, and
				// guessing would put every id-and-artifact run on the default.
				return null;
			}
		}

		if ( $slug !== 'woocommerce' ) {
			return null;
		}

		$version = self::first_scalar( [ $sut['version'] ?? null ] );

		if ( $version !== null ) {
			return [ 'version' => $version ];
		}

		return self::has_custom_source( $input ) ? [ 'version' => null ] : null;
	}

	/**
	 * Every request for WooCommerce in a plugin list, in the order `env:up` keeps
	 * them, as versions.
	 *
	 * An entry contributes the version it names; an empty string when it leaves
	 * that to wp.org; null when it names an artifact instead.
	 *
	 * @param mixed $plugins    Plugin requests, in either shape `env:up` accepts.
	 * @param bool  $scalar_set Whether a scalar version will clear the literal ones.
	 *
	 * @return array<int, string|null>
	 */
	private static function woocommerce_requests( $plugins, bool $scalar_set ): array {
		$found = [];

		foreach ( is_array( $plugins ) ? $plugins : [] as $plugin ) {
			if ( is_array( $plugin ) ) {
				if ( ( $plugin['slug'] ?? '' ) !== 'woocommerce' ) {
					continue;
				}

				// `resolveWooCommerceVersion()` drops these once a scalar is set.
				if ( $scalar_set ) {
					continue;
				}

				$version = self::first_scalar( [ $plugin['version'] ?? null ] );

				if ( $version !== null ) {
					$found[] = $version;
					continue;
				}

				$from = (string) ( $plugin['from'] ?? '' );

				$found[] = in_array( $from, [ '', 'wporg' ], true ) ? '' : null;
				continue;
			}

			if ( ! is_scalar( $plugin ) ) {
				continue;
			}

			$plugin = trim( (string) $plugin );

			if ( $plugin === 'woocommerce' ) {
				if ( ! $scalar_set ) {
					$found[] = '';
				}

				continue;
			}

			// A source after `@` hides the version inside the ZIP it points at, and
			// the slug of such a request is the whole string, so no scalar drops it.
			if ( strpos( $plugin, 'woocommerce@' ) === 0 ) {
				$found[] = null;
				continue;
			}

			if ( strpos( $plugin, 'woocommerce:' ) !== 0 ) {
				continue;
			}

			$found[] = trim( substr( $plugin, strlen( 'woocommerce:' ) ) );
		}

		return $found;
	}

	/**
	 * The first of several candidates that says something, trimmed.
	 *
	 * @param array<int, mixed> $candidates
	 */
	private static function first_scalar( array $candidates ): ?string {
		foreach ( $candidates as $candidate ) {
			if ( is_scalar( $candidate ) && trim( (string) $candidate ) !== '' ) {
				return trim( (string) $candidate );
			}
		}

		return null;
	}

	/**
	 * Whether the extension under test is supplied rather than named.
	 *
	 * The same pair `RunE2ECommand` treats as a custom source.
	 */
	private static function has_custom_source( QITInput $input ): bool {
		return self::first_scalar( [ $input->getOption( 'zip' ), $input->getOption( 'source' ) ] ) !== null;
	}
}
