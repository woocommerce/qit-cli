<?php

namespace QIT_CLI\Commands;

use QIT_CLI\App;
use QIT_CLI\Blueprints\BlueprintEnvironment;
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
 * data, and what to run when nothing covers the version. Which tag covers a
 * version WooCommerce has not released is the Manager's to say, and it says so
 * in sync data alongside the versions.
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
		$speak  = empty( $options['--json'] );
		$pinned = $this->pinned_woocommerce_version( $input );

		if ( $pinned['version'] === null ) {
			// WooCommerce arrives as an artifact, so which version the environment
			// ends up with is inside that ZIP. Say so rather than assume:
			// `--test-package` names one outright, and both commands leave a named
			// package alone.
			$this->announce( $output, $speak, sprintf(
				'<comment>WooCommerce is %s, so its version is not known before the environment is built. Using %s.</comment>',
				$pinned['from'] === 'sut' ? 'the extension under test' : 'supplied as an artifact',
				$this->fallback_test_package()
			) );

			return $this->fallback_test_package();
		}

		$requested = $pinned['version'];

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

		// The Manager advertises its nightly tag only while that tag is published,
		// so its absence is the answer to whether one can be run. Nothing here can
		// check that itself: a local run picks its package before the Manager is
		// told the run exists.
		//
		// A tag, not a package id — the same shape the versions arrive in, and
		// composed onto `package` the same way, so nothing downstream can be handed
		// a bare word where it expects a reference.
		$nightly = is_array( $offered ) ? ( $offered[ $this->package_test_type() ]['nightly'] ?? null ) : null;
		$nightly = is_string( $package ) && is_string( $nightly ) && $nightly !== ''
			? $package . ':' . $nightly
			: null;

		$covering = is_string( $package ) && is_array( $versions )
			? self::covering_version( $requested, $versions )
			: null;

		if ( $covering === null ) {
			// Every WooCommerce version without a package of its own lands here,
			// and they split in two. A version ahead of every published line is
			// running trunk's markup, so it takes the package published from
			// trunk. Anything else stays on the newest published stable line.
			$uncovered = $this->uncovered_test_package(
				$requested,
				is_array( $versions ) ? $versions : [],
				$nightly
			);

			// Worth saying out loud either way: the suite was not written for the
			// version it is running against, and both tags move, so a rerun may
			// not run the same specs. "Nothing covers it" reads as a shortfall,
			// which is wrong for the nightly branch — nothing is meant to cover a
			// version with no released line, and that tag is written for it.
			$notice = $uncovered === $nightly
				? '<comment>WooCommerce %s has no released line of its own, so its package is %s.</comment>'
				: '<comment>No test package covers WooCommerce %s. Using %s instead.</comment>';

			$this->announce( $output, $speak, sprintf( $notice, $requested, $uncovered ) );

			return $uncovered;
		}

		$test_package = $package . ':' . $covering;

		$this->announce( $output, $speak, sprintf(
			'<comment>Using test package %s for WooCommerce %s.</comment>',
			$test_package,
			$requested
		) );

		return $test_package;
	}

	/**
	 * The package for a WooCommerce version nothing covers.
	 *
	 * The Manager's nightly tag when the version is ahead of every published line
	 * and WooCommerce has not released it, the stable fallback otherwise. A test
	 * type the Manager advertises no nightly tag for keeps the single fallback,
	 * so this can only ever narrow what it used to return.
	 *
	 * @param string            $requested The version the run asked for.
	 * @param array<int, mixed> $published Published versions, as sync data lists them.
	 * @param string|null       $nightly   The tag sync data advertises, if any.
	 */
	private function uncovered_test_package( string $requested, array $published, ?string $nightly ): string {
		if (
			$nightly === null
			|| ! self::names_an_unreleased_version( $requested )
			|| ! self::ahead_of_published( $requested, $published )
		) {
			return $this->fallback_test_package();
		}

		return $nightly;
	}

	/**
	 * Whether the request names something WooCommerce has not released.
	 *
	 * The nightly package is for versions that do not exist as a release yet, and
	 * asking for one is opting in to that. A released version is a different
	 * matter even when no package covers it: a line can go GA before its package
	 * is published, and `stable` resolves to it, so a plain `run:activation` with
	 * no `--woo` would land there. Sending the default run to trunk's suite for
	 * the length of a publishing gap trades one markup mismatch for another, on
	 * the path most runs take. It keeps the stable fallback, as it always had.
	 *
	 * So: `nightly`, and anything carrying a prerelease or `-dev` suffix. A plain
	 * `11.2.0` is released. A string that names no version at all cannot be
	 * placed, and is treated as released for the same reason.
	 *
	 * Only `nightly` and a `-dev` build are trunk. A prerelease is tagged from
	 * `release/X.Y`, which is cut before trunk bumps to the next line, so during
	 * 11.2's RC window trunk is already 11.3.0-dev and the tag is a line further
	 * along than the store under test. It is included anyway: the RC branched off
	 * trunk weeks earlier, while `latest` is a whole cycle behind it, and asking
	 * for an RC is opting into an unreleased version either way. An approximation
	 * on an opt-in path, where a released line gets none on the default one.
	 * Publishing the line's package ends it.
	 */
	private static function names_an_unreleased_version( string $requested ): bool {
		$requested = trim( $requested );

		if ( $requested === 'nightly' ) {
			return true;
		}

		if ( self::major_minor( $requested ) === null ) {
			return false;
		}

		return preg_match( '/^\d+(?:\.\d+)*$/', $requested ) !== 1;
	}

	/**
	 * Whether a WooCommerce version is newer than every published package version.
	 *
	 * `nightly` is ahead by definition: it is built from trunk, which is always
	 * the line no package covers yet. It is also the one request that carries no
	 * version to compare, and resolving it to trunk's `major.minor` would only
	 * reintroduce that question a release later.
	 *
	 * Everything else is compared on `major.minor`, so a prerelease counts as the
	 * line it belongs to: 11.2.0-rc.1 is ahead while 11.1 is the newest published,
	 * and stops being ahead the moment 11.2 is published. A version that cannot be
	 * read as one — and there is no list to compare against on a Manager that
	 * publishes none — is not ahead, which keeps the stable fallback.
	 *
	 * That last case is why `nightly` and a prerelease can part company here with
	 * nothing published: `nightly` says which build is running and needs no list,
	 * while `11.2.0-rc.1` is a version that needs one to be placed. With no list,
	 * placing it is a guess, and the stable fallback is the cautious guess.
	 *
	 * @param string            $requested The version the run asked for.
	 * @param array<int, mixed> $published Published versions, as sync data lists them.
	 */
	private static function ahead_of_published( string $requested, array $published ): bool {
		if ( $requested === 'nightly' ) {
			return true;
		}

		$requested = self::major_minor( $requested );

		if ( $requested === null ) {
			return false;
		}

		$ahead = false;

		foreach ( $published as $version ) {
			if ( ! is_scalar( $version ) ) {
				continue;
			}

			$version = self::major_minor( trim( (string) $version ) );

			if ( $version === null ) {
				// `latest`, and anything else that does not name a line.
				continue;
			}

			if ( version_compare( $requested, $version, '<=' ) ) {
				return false;
			}

			$ahead = true;
		}

		return $ahead;
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
	 * The published version covering a WooCommerce version: the one published for
	 * its `major.minor`, or none.
	 *
	 * A package version covers a whole WooCommerce `major.minor`, so 11.0.0 and
	 * 11.0.1 both take `11.0`, and a prerelease keeps the version it belongs to:
	 * 11.2.0-beta.1 is 11.2.
	 *
	 * Nothing else covers it. Taking the nearest older version instead reads as a
	 * match while running specs written for another release, which is the one
	 * thing this exists to prevent — and it hides the case that comes round every
	 * cycle, where the next line's RC is offered before its package is published.
	 * Falling to the default at least says so, and the default is the line in
	 * development, which is what an unpublished newer version most resembles.
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

			if ( $version === $requested ) {
				return $version;
			}
		}

		return null;
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
	 * `env:up` builds one plugin list out of every source and installs the first
	 * request for the `woocommerce` slug left standing in it, so this builds the
	 * same list rather than guessing at the outcome. In the order
	 * `UpEnvironmentCommand` assembles them:
	 *
	 *   merge( blueprint, merge( environment block, CLI ) )
	 *
	 * with the extension under test appended to the CLI's `--plugin` afterwards by
	 * `add_sut_to_env_up_options()`. Three details decide the result, and none is
	 * the one you would guess:
	 *
	 * - `ConfigMerger::merge_by_slug()` indexes by the raw value, so a bare
	 *   `woocommerce` replaces an entry for the same slug, while
	 *   `woocommerce:10.9.0` is a separate key that joins the list behind it.
	 * - `resolveWooCommerceVersion()` then drops requests whose slug is literally
	 *   `woocommerce` once a scalar version is set, which a raw
	 *   `woocommerce:10.9.0` is not — its slug is the whole string. So a
	 *   `--plugin` pin survives a scalar, whose entry is appended behind it.
	 * - With no scalar, nothing is dropped and the merged order stands.
	 *
	 * @return array{version: string|null, from: string} `version` is an empty
	 *         string when the winning request leaves it to wp.org, and null when
	 *         it cannot be known here at all — an artifact, whose version lives
	 *         inside the ZIP. `from` says which source that was, for the message.
	 */
	private function pinned_woocommerce_version( QITInput $input ): array {
		$options     = $input->get_environment_options();
		$environment = EnvironmentConfigResolver::normalize_aliases( $input->get_environment_config() );
		$blueprint   = $this->blueprint_config( $input );

		// A scalar is overlaid the other way round, so the CLI's wins and the
		// Blueprint, being the base of the last merge, is the last resort.
		$scalar = self::first_scalar( [
			$options['--woocommerce_version'] ?? null,
			$environment['woocommerce_version'] ?? null,
			$blueprint['woocommerce_version'] ?? null,
		] );

		$cli = is_array( $options['--plugin'] ?? null ) ? $options['--plugin'] : [];
		$sut = $this->woocommerce_sut_request( $input );

		$plugins = self::merge_by_slug( [
			'blueprint'   => $blueprint['plugins'] ?? null,
			'environment' => $environment['plugins'] ?? null,
			'request'     => $cli,
			'sut'         => $sut === null ? [] : [ $sut ],
		] );

		foreach ( $plugins as $plugin ) {
			$version = self::woocommerce_request( $plugin['entry'], $scalar !== null );

			if ( $version !== false ) {
				return [
					'version' => $version,
					'from'    => $plugin['from'],
				];
			}
		}

		return [
			'version' => $scalar ?? '',
			'from'    => 'scalar',
		];
	}

	/**
	 * What a `--blueprint` contributes to the environment, if one was given.
	 *
	 * `apply_blueprint()` merges it as the base, under everything else, and a
	 * Blueprint installing WooCommerce from wp.org sets a version there. Parsing
	 * is pure — `prepare()` transpiles and writes nothing — and a Blueprint this
	 * cannot read is one `RunE2ECommand` will report on properly later.
	 *
	 * @return array<string, mixed>
	 */
	private function blueprint_config( QITInput $input ): array {
		$path = $input->hasOption( 'blueprint' ) ? $input->getOption( 'blueprint' ) : null;

		if ( ! is_scalar( $path ) || trim( (string) $path ) === '' ) {
			return [];
		}

		try {
			return App::make( BlueprintEnvironment::class )->prepare( trim( (string) $path ) )->env_config;
		} catch ( \Throwable $e ) {
			return [];
		}
	}

	/**
	 * The plugin entry the extension under test will add, when it is WooCommerce.
	 *
	 * `add_sut_to_env_up_options()` appends the slug, or `slug@source` when the
	 * artifact is supplied, and nothing else — a version alongside the SUT never
	 * reaches the environment, so it is not read here either.
	 *
	 * A numeric argument is a WooCommerce.com ID, which `RunE2ECommand` turns into
	 * a slug later on. The same lookup is done here off cached sync data, so
	 * `run:activation <woo id> --zip` is recognised as well as the slug form. An
	 * id this cannot resolve is one it cannot rule out either, and guessing would
	 * put every id-and-artifact run on the default.
	 */
	private function woocommerce_sut_request( QITInput $input ): ?string {
		$sut = $input->get_sut();

		if ( ! is_array( $sut ) ) {
			return null;
		}

		$slug = (string) ( $sut['slug'] ?? '' );

		if ( is_numeric( $slug ) ) {
			try {
				$slug = $this->woo_extensions_list->get_woo_extension_slug_by_id( (int) $slug );
			} catch ( \Throwable $e ) {
				return null;
			}
		}

		if ( $slug !== 'woocommerce' ) {
			return null;
		}

		$source = self::first_scalar( [ $input->getOption( 'zip' ), $input->getOption( 'source' ) ] );

		return $source === null ? $slug : $slug . '@' . $source;
	}

	/**
	 * The plugin list `env:up` will work from, in its order, each entry labelled
	 * with the source it survived from.
	 *
	 * Replays `ConfigMerger::merge_by_slug()`, which indexes by the raw value and
	 * lets a later list replace a matching key in place, keeping everything else
	 * in the order it arrived.
	 *
	 * @param array<string, mixed> $lists Plugin requests by source, in merge order.
	 *
	 * @return array<int, array{entry: mixed, from: string}>
	 */
	private static function merge_by_slug( array $lists ): array {
		$index = [];

		foreach ( $lists as $from => $list ) {
			foreach ( is_array( $list ) ? $list : [] as $entry ) {
				$slug = is_array( $entry ) ? ( $entry['slug'] ?? null ) : $entry;

				if ( is_scalar( $slug ) && (string) $slug !== '' ) {
					$index[ (string) $slug ] = [
						'entry' => $entry,
						'from'  => (string) $from,
					];
				}
			}
		}

		return array_values( $index );
	}

	/**
	 * What one plugin request says about the WooCommerce version, if anything.
	 *
	 * False when the request is not for WooCommerce, or when a scalar version will
	 * drop it. Otherwise the version it names, an empty string when it leaves that
	 * to wp.org, and null when it names an artifact instead.
	 *
	 * @param mixed $plugin     One plugin request, in either shape `env:up` accepts.
	 * @param bool  $scalar_set Whether a scalar version will clear the literal ones.
	 *
	 * @return string|null|false
	 */
	private static function woocommerce_request( $plugin, bool $scalar_set ) {
		if ( is_array( $plugin ) ) {
			// `resolveWooCommerceVersion()` drops these once a scalar is set.
			if ( ( $plugin['slug'] ?? '' ) !== 'woocommerce' || $scalar_set ) {
				return false;
			}

			$version = self::first_scalar( [ $plugin['version'] ?? null ] );

			if ( $version !== null ) {
				return $version;
			}

			$from = (string) ( $plugin['from'] ?? '' );

			return in_array( $from, [ '', 'wporg' ], true ) ? '' : null;
		}

		if ( ! is_scalar( $plugin ) ) {
			return false;
		}

		$plugin = trim( (string) $plugin );

		if ( $plugin === 'woocommerce' ) {
			return $scalar_set ? false : '';
		}

		// Everything below keeps its whole value as its slug, so no scalar drops it.
		if ( strpos( $plugin, 'woocommerce@' ) === 0 ) {
			return null;
		}

		if ( strpos( $plugin, 'woocommerce:' ) === 0 ) {
			return trim( substr( $plugin, strlen( 'woocommerce:' ) ) );
		}

		// A bare URL or path whose filename reads as WooCommerce is a WooCommerce
		// artifact to `ExtensionInputParser`, which infers the slug that way.
		return self::infers_woocommerce( $plugin ) ? null : false;
	}

	/**
	 * Whether `ExtensionInputParser` would read a raw URL or path as WooCommerce.
	 *
	 * Mirrors its slug inference: the filename, without a `.zip` extension and
	 * without a trailing version.
	 */
	private static function infers_woocommerce( string $value ): bool {
		if ( strpos( $value, ':' ) !== false && strpos( $value, '://' ) === false ) {
			// A `slug:version` for something else, already ruled out above.
			return false;
		}

		$path = strpos( $value, '://' ) !== false ? (string) parse_url( $value, PHP_URL_PATH ) : $value;

		if ( $path === '' ) {
			return false;
		}

		$basename = basename( $path );

		if ( substr( $basename, -4 ) === '.zip' ) {
			$basename = substr( $basename, 0, -4 );
		}

		$basename = (string) preg_replace( '/\.\d+(\.\d+)*$/', '', $basename );
		$basename = (string) preg_replace( '/-v?\d+\.\d+(\.\d+)*$/', '', $basename );

		return $basename === 'woocommerce';
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
}
