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
		$requested = self::pinned_woocommerce_version( $input );

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
	 * Either of the first two can pin it as a plugin instead of as a version. A
	 * scalar version outranks any plugin entry, because
	 * `resolveWooCommerceVersion()` drops every WooCommerce plugin entry when one
	 * is set. Between plugin entries the environment's own list wins over
	 * `--plugin`: `EnvironmentConfigResolver` keys requests by the raw value, so
	 * `woocommerce` and `woocommerce:10.9.0` are two requests rather than one
	 * overriding the other, and `ExtensionResolver` then keeps the first it meets
	 * for a slug — the environment's, loaded before the CLI's.
	 *
	 * Returns an empty string when nothing pins a version, and null when
	 * WooCommerce is itself the extension under test and arrives as an artifact:
	 * the environment then installs whatever that ZIP holds, which no pin here
	 * changes and nothing can read before the environment is built.
	 */
	private static function pinned_woocommerce_version( QITInput $input ): ?string {
		$options = $input->get_environment_options();

		// The SUT is never displaced: `add_plugin_request()` prepends it and lets
		// nothing overwrite it, and a local artifact is not re-resolved from
		// wp.org, so a version pin cannot change what a supplied ZIP contains.
		$sut = $input->get_sut();

		if ( is_array( $sut ) && ( $sut['slug'] ?? '' ) === 'woocommerce' ) {
			$sut_version = $sut['version'] ?? null;

			if ( is_scalar( $sut_version ) && trim( (string) $sut_version ) !== '' ) {
				return trim( (string) $sut_version );
			}

			if ( self::has_custom_source( $input ) ) {
				return null;
			}
		}

		$from_options = $options['--woocommerce_version'] ?? null;

		if ( is_scalar( $from_options ) && trim( (string) $from_options ) !== '' ) {
			return trim( (string) $from_options );
		}

		$environment = EnvironmentConfigResolver::normalize_aliases( $input->get_environment_config() );
		$from_block  = $environment['woocommerce_version'] ?? null;

		if ( is_scalar( $from_block ) && trim( (string) $from_block ) !== '' ) {
			return trim( (string) $from_block );
		}

		// An environment that asks for WooCommerce itself settles the matter: what
		// `--plugin` names never reaches the environment, whether or not the
		// environment's own request carries a version.
		$from_block_plugins = self::woocommerce_request( $environment['plugins'] ?? null );

		if ( $from_block_plugins !== null ) {
			return $from_block_plugins;
		}

		// `--plugin woocommerce:11.0.0` installs a version as surely as `--woo`
		// does, and reaches `env:up` through the same array.
		return self::woocommerce_request( $options['--plugin'] ?? null ) ?? '';
	}

	/**
	 * The version a list of plugin requests asks WooCommerce for.
	 *
	 * Null when the list does not ask for WooCommerce at all, which is what tells
	 * a caller to look elsewhere. An empty string when it asks without naming a
	 * version this can read: a bare slug leaves the version to wp.org, and a path
	 * or URL carries it inside the ZIP, out of reach until the environment is
	 * built. Either way the request is there and outranks whatever comes next.
	 *
	 * Accepts both shapes `env:up` does — a raw `--plugin` string and an
	 * environment block's entry — and reads `slug:version` the way
	 * `ExtensionInputParser` does, which rejects mixing `:` with `@`.
	 *
	 * @param mixed $plugins Plugin requests, as either source reports them.
	 */
	private static function woocommerce_request( $plugins ): ?string {
		foreach ( is_array( $plugins ) ? $plugins : [] as $plugin ) {
			if ( is_array( $plugin ) ) {
				if ( ( $plugin['slug'] ?? '' ) !== 'woocommerce' ) {
					continue;
				}

				$version = $plugin['version'] ?? null;

				return is_scalar( $version ) ? trim( (string) $version ) : '';
			}

			if ( ! is_scalar( $plugin ) ) {
				continue;
			}

			$plugin = trim( (string) $plugin );

			if ( $plugin === 'woocommerce' ) {
				return '';
			}

			// A source after `@` hides the version inside the ZIP it points at.
			if ( strpos( $plugin, '@' ) !== false ) {
				if ( strpos( $plugin, 'woocommerce@' ) === 0 ) {
					return '';
				}

				continue;
			}

			if ( strpos( $plugin, 'woocommerce:' ) !== 0 ) {
				continue;
			}

			return trim( substr( $plugin, strlen( 'woocommerce:' ) ) );
		}

		return null;
	}

	/**
	 * Whether the extension under test is supplied rather than named.
	 *
	 * The same pair `RunE2ECommand` treats as a custom source.
	 */
	private static function has_custom_source( QITInput $input ): bool {
		$zip    = $input->getOption( 'zip' );
		$source = $input->getOption( 'source' );

		return ( is_scalar( $zip ) && trim( (string) $zip ) !== '' )
			|| ( is_scalar( $source ) && trim( (string) $source ) !== '' );
	}
}
