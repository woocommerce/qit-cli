<?php

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\RunWooE2ETestCommand;
use QIT_CLI\ManagerSync;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Exposes the package choice without reflection, which behaves differently across
 * the PHP versions this suite runs on: `setAccessible()` is required before 8.1
 * and deprecated from 8.5.
 */
class ExposedRunWooE2ETestCommand extends RunWooE2ETestCommand {
	public function resolve_test_package_for_test( QITInput $input, OutputInterface $output ): string {
		return $this->resolve_test_package( $input, $output );
	}
}

/**
 * `run:woo-e2e` picks its package from the versions the Manager has published.
 *
 * It used to hardcode `latest` for every run, so a run on one WooCommerce
 * version executed the same specs as a run on any other. The Manager says which
 * package versions exist; matching a WooCommerce version against them happens
 * here, because a local run chooses its package before the Manager is told the
 * run exists, and may name any version.
 */
class RunWooE2ETestPackageSelectionTest extends \QIT_CLI_Tests\QITTestCase {
	private const FALLBACK = 'woocommerce/core-e2e-tests:latest';

	/**
	 * @param array<int, string>|null $versions Published package versions; null removes the key.
	 */
	private function given_published( ?array $versions ): void {
		$this->set_sync_key(
			'test_package_versions',
			$versions === null
				? null
				: [ 'e2e' => [ 'package' => 'woocommerce/core-e2e-tests', 'versions' => $versions ] ]
		);
	}

	/**
	 * @param mixed $value Null removes the key.
	 */
	private function set_sync_key( string $key, $value ): void {
		$cache        = App::make( Cache::class );
		$manager_sync = App::make( ManagerSync::class );
		$sync_data    = $cache->get( $manager_sync->bootstrap_cache_key );

		$this->assertIsArray( $sync_data );

		if ( is_null( $value ) ) {
			unset( $sync_data[ $key ] );
		} else {
			$sync_data[ $key ] = $value;
		}

		$cache->set( $manager_sync->bootstrap_cache_key, $sync_data, 60 );
	}

	private function resolve_for( ?string $woocommerce_version ): string {
		$env_options = $woocommerce_version === null
			? [ '--environment' => 'default' ]
			: [ '--environment' => 'default', '--woocommerce_version' => $woocommerce_version ];

		return $this->resolve( $env_options, [] );
	}

	/**
	 * @param array<string, mixed> $env_options        As get_environment_options() reports them.
	 * @param array<string, mixed> $environment_config The selected environment block.
	 */
	private function resolve( array $env_options, array $environment_config, ?OutputInterface $output = null ): string {
		$input = $this->createMock( QITInput::class );
		$input->method( 'get_environment_options' )->willReturn( $env_options );
		$input->method( 'get_environment_config' )->willReturn( $environment_config );

		return App::make( ExposedRunWooE2ETestCommand::class )
			->resolve_test_package_for_test( $input, $output ?? new NullOutput() );
	}

	public function test_a_patch_release_takes_the_version_covering_it(): void {
		$this->given_published( [ '11.0', '11.1' ] );

		$this->assertSame( 'woocommerce/core-e2e-tests:11.0', $this->resolve_for( '11.0.1' ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:11.1', $this->resolve_for( '11.1.4' ) );
	}

	public function test_a_prerelease_keeps_the_version_it_belongs_to(): void {
		$this->given_published( [ '11.1', '11.2' ] );

		$this->assertSame( 'woocommerce/core-e2e-tests:11.2', $this->resolve_for( '11.2.0-beta.1' ) );
	}

	public function test_falls_back_to_an_older_version_when_none_covers_it_exactly(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		// Nothing published for 11.1 yet, so it runs the 11.0 specs rather than a
		// moving tag.
		$this->assertSame( 'woocommerce/core-e2e-tests:11.0', $this->resolve_for( '11.1.0' ) );
	}

	public function test_never_takes_a_version_above_the_requested_one(): void {
		$this->given_published( [ '10.9', '11.0', '11.1' ] );

		$this->assertSame( 'woocommerce/core-e2e-tests:10.9', $this->resolve_for( '10.9.4' ) );
	}

	public function test_compares_versions_numerically_not_as_strings(): void {
		$this->given_published( [ '9.9', '11.0', '11.10' ] );

		$this->assertSame( 'woocommerce/core-e2e-tests:11.10', $this->resolve_for( '11.10.1' ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:11.0', $this->resolve_for( '11.0.5' ) );
	}

	public function test_takes_a_historical_version_the_manager_never_offers(): void {
		// The Manager's own version list holds a handful of recent releases, but a
		// local run may name any version, and a package published for it must win.
		$this->given_published( [ '10.0', '11.0' ] );

		$this->assertSame( 'woocommerce/core-e2e-tests:10.0', $this->resolve_for( '10.0.0' ) );
	}

	public function test_resolves_a_channel_to_the_version_it_stands_for(): void {
		$this->given_published( [ '9.6', '10.6' ] );

		// The fixture's sync data resolves stable to 9.6.0, and rc to 10.6.1
		// through rc_unsynced.
		$this->assertSame( 'woocommerce/core-e2e-tests:9.6', $this->resolve_for( 'stable' ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:10.6', $this->resolve_for( 'rc' ) );
	}

	public function test_rc_follows_the_release_the_environment_installs(): void {
		// `VersionResolver::resolve_woo('rc')` builds its download URL from
		// rc_unsynced, so that is the release installed. The fixture has the two
		// keys disagreeing — rc is 9.7.0-beta.1, rc_unsynced is 10.6.1 — and
		// reading rc put a 10.6 environment on the 9.7 specs.
		$this->given_published( [ '9.7', '10.6' ] );

		$this->assertSame( 'woocommerce/core-e2e-tests:10.6', $this->resolve_for( 'rc' ) );
	}

	public function test_a_run_that_names_no_version_takes_the_stable_channel(): void {
		$this->given_published( [ '9.6', '9.7' ] );

		// stable is 9.6.0 in the fixture, so an unpinned run must not take 9.7.
		$this->assertSame( 'woocommerce/core-e2e-tests:9.6', $this->resolve_for( null ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:9.6', $this->resolve_for( '' ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:9.6', $this->resolve_for( '  ' ) );
	}

	public function test_uses_a_version_pinned_by_the_selected_environment(): void {
		$this->given_published( [ '10.0', '11.1' ] );

		// `get_environment_options()` passes the block on as `--environment` and lets
		// `env:up` resolve it, so the version is only in the block itself.
		$only_environment = [ '--environment' => 'default' ];

		$this->assertSame(
			'woocommerce/core-e2e-tests:10.0',
			$this->resolve( $only_environment, [ 'woocommerce_version' => '10.0.0' ] )
		);

		// The short form is the same pin.
		$this->assertSame(
			'woocommerce/core-e2e-tests:10.0',
			$this->resolve( $only_environment, [ 'woo' => '10.0.0' ] )
		);

		// So is pinning it as a plugin entry.
		$this->assertSame(
			'woocommerce/core-e2e-tests:10.0',
			$this->resolve( $only_environment, [
				'plugins' => [
					[ 'slug' => 'some-other-plugin', 'version' => '3.0.0' ],
					[ 'slug' => 'woocommerce', 'version' => '10.0.0' ],
				],
			] )
		);
	}

	public function test_uses_a_version_pinned_as_a_plugin_option(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		// `--plugin woocommerce:10.9.0` installs 10.9.0 as surely as `--woo` does.
		// Reading only `--woocommerce_version` here put the run on the 11.0 specs
		// against a 10.9 environment.
		$this->assertSame(
			'woocommerce/core-e2e-tests:10.9',
			$this->resolve( [ '--environment' => 'default', '--plugin' => [ 'woocommerce:10.9.0' ] ], [] )
		);

		// Alongside other plugins, and unbothered by their versions.
		$this->assertSame(
			'woocommerce/core-e2e-tests:10.9',
			$this->resolve(
				[ '--environment' => 'default', '--plugin' => [ 'some-plugin:11.0.0', 'woocommerce:10.9.4' ] ],
				[]
			)
		);
	}

	public function test_an_environment_that_asks_for_woocommerce_outranks_a_plugin_option(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		// `EnvironmentConfigResolver` keys requests by the raw value, so the block's
		// `woocommerce` and the CLI's `woocommerce:10.9.0` are two requests, and
		// `ExtensionResolver` keeps the first it meets for the slug — the block's.
		// The environment installs stable, so the specs have to be stable's.
		$this->assertSame(
			self::FALLBACK,
			$this->resolve(
				[ '--environment' => 'default', '--plugin' => [ 'woocommerce:10.9.0' ] ],
				[ 'plugins' => [ 'woocommerce' ] ]
			)
		);

		// The same when the block spells it out as an entry without a version.
		$this->assertSame(
			self::FALLBACK,
			$this->resolve(
				[ '--environment' => 'default', '--plugin' => [ 'woocommerce:10.9.0' ] ],
				[ 'plugins' => [ [ 'slug' => 'woocommerce' ] ] ]
			)
		);

		// And when it names its own version, that is the one installed.
		$this->assertSame(
			'woocommerce/core-e2e-tests:11.0',
			$this->resolve(
				[ '--environment' => 'default', '--plugin' => [ 'woocommerce:10.9.0' ] ],
				[ 'plugins' => [ [ 'slug' => 'woocommerce', 'version' => '11.0.1' ] ] ]
			)
		);
	}

	public function test_a_plugin_option_still_counts_when_the_environment_asks_for_others(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		// Only a WooCommerce request in the block displaces the flag.
		$this->assertSame(
			'woocommerce/core-e2e-tests:10.9',
			$this->resolve(
				[ '--environment' => 'default', '--plugin' => [ 'woocommerce:10.9.0' ] ],
				[ 'plugins' => [ 'some-other-plugin', [ 'slug' => 'another' ] ] ]
			)
		);
	}

	public function test_ignores_a_plugin_option_that_names_no_readable_version(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		// The fixture's stable is 9.6.0, below everything published, so anything
		// this cannot read falls back rather than silently taking a version.
		foreach ( [ 'woocommerce', 'woocommerce@/tmp/woo.zip', 'woocommerce:', 'other:10.9.0' ] as $value ) {
			$this->assertSame(
				self::FALLBACK,
				$this->resolve( [ '--environment' => 'default', '--plugin' => [ $value ] ], [] ),
				sprintf( '--plugin %s should not pin a version', $value )
			);
		}
	}

	public function test_a_plugin_option_outranks_a_version_flag(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		// `resolveWooCommerceVersion()` drops requests whose slug is literally
		// `woocommerce`, and the slug of the raw `woocommerce:10.9.0` is the whole
		// string, so it survives and the flag's entry is appended behind it. 10.9
		// is what gets installed, however backwards that reads.
		$this->assertSame(
			'woocommerce/core-e2e-tests:10.9',
			$this->resolve(
				[ '--woocommerce_version' => '11.0.1', '--plugin' => [ 'woocommerce:10.9.0' ] ],
				[]
			)
		);

		// Same for a version the environment block declares.
		$this->assertSame(
			'woocommerce/core-e2e-tests:10.9',
			$this->resolve(
				[ '--environment' => 'default', '--plugin' => [ 'woocommerce:10.9.0' ] ],
				[ 'woocommerce_version' => '11.0.1' ]
			)
		);
	}

	public function test_a_version_clears_the_environments_own_request(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		// With a scalar set, `resolveWooCommerceVersion()` removes the block's
		// WooCommerce entries and installs the version instead.
		$this->assertSame(
			'woocommerce/core-e2e-tests:11.0',
			$this->resolve(
				[ '--woocommerce_version' => '11.0.1' ],
				[ 'plugins' => [ 'woocommerce' ] ]
			)
		);

		$this->assertSame(
			'woocommerce/core-e2e-tests:11.0',
			$this->resolve(
				[ '--woocommerce_version' => '11.0.1' ],
				[ 'plugins' => [ [ 'slug' => 'woocommerce', 'version' => '10.9.4' ] ] ]
			)
		);
	}

	public function test_a_supplied_woocommerce_plugin_leaves_its_version_unknown(): void {
		// As above: 9.6 is published so that reading these as stable is visible.
		$this->given_published( [ '9.6', '10.9', '11.0' ] );

		// The ZIP behind `@` decides the version, and nothing here can read it, so
		// this must not settle for stable.
		$this->assertSame(
			self::FALLBACK,
			$this->resolve(
				[ '--environment' => 'default', '--plugin' => [ 'woocommerce@https://example.test/woo.zip' ] ],
				[]
			)
		);

		// Nor when the environment declares it as a non-wporg source.
		$this->assertSame(
			self::FALLBACK,
			$this->resolve(
				[ '--environment' => 'default' ],
				[ 'plugins' => [ [ 'slug' => 'woocommerce', 'from' => 'url', 'url' => 'https://example.test/woo.zip' ] ] ]
			)
		);
	}

	public function test_a_flag_outranks_a_version_pinned_by_the_environment(): void {
		$this->given_published( [ '10.0', '11.0' ] );

		$this->assertSame(
			'woocommerce/core-e2e-tests:11.0',
			$this->resolve(
				[ '--environment' => 'default', '--woocommerce_version' => '11.0.1' ],
				[ 'woocommerce_version' => '10.0.0' ]
			)
		);
	}

	public function test_falls_back_when_nothing_published_is_low_enough(): void {
		$this->given_published( [ '11.0', '11.1' ] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '10.9.4' ) );
	}

	public function test_falls_back_when_the_manager_publishes_no_versions(): void {
		$this->given_published( null );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.0.1' ) );
	}

	public function test_falls_back_when_nothing_is_published_for_e2e(): void {
		$this->set_sync_key( 'test_package_versions', [] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.0.1' ) );
	}

	public function test_ignores_anything_that_is_not_exactly_two_segments(): void {
		$this->given_published( [ 'latest', '11.0.1', '11.0.0-beta.1' ] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.0.1' ) );
	}

	/**
	 * @param array<string, mixed> $sut     As get_sut() reports it; null for none.
	 * @param array<string, mixed> $options As get_environment_options() reports them.
	 */
	private function resolve_with_sut( ?array $sut, array $options = [], string $zip = '' ): string {
		$input = $this->createMock( QITInput::class );
		$input->method( 'get_environment_options' )->willReturn( $options + [ '--environment' => 'default' ] );
		$input->method( 'get_environment_config' )->willReturn( [] );
		$input->method( 'get_sut' )->willReturn( $sut );
		$input->method( 'getOption' )->willReturnMap( [
			[ 'zip', $zip ],
			[ 'source', '' ],
		] );

		return App::make( ExposedRunWooE2ETestCommand::class )
			->resolve_test_package_for_test( $input, new NullOutput() );
	}

	public function test_a_supplied_woocommerce_leaves_its_version_unknown(): void {
		$this->given_published( [ '9.6', '10.9', '11.0' ] );

		// The environment installs the ZIP, and no pin here changes what is in it.
		// Assuming stable put a build of one version on another version's specs.
		$this->assertSame(
			self::FALLBACK,
			$this->resolve_with_sut( [ 'slug' => 'woocommerce' ], [ '--woocommerce_version' => '11.0.1' ], '/tmp/woo.zip' )
		);
	}

	public function test_a_supplied_woocommerce_says_why_it_cannot_tell(): void {
		$this->given_published( [ '11.0' ] );

		$output = new BufferedOutput();
		$input  = $this->createMock( QITInput::class );
		$input->method( 'get_environment_options' )->willReturn( [ '--environment' => 'default' ] );
		$input->method( 'get_environment_config' )->willReturn( [] );
		$input->method( 'get_sut' )->willReturn( [ 'slug' => 'woocommerce' ] );
		$input->method( 'getOption' )->willReturnMap( [
			[ 'zip', '' ],
			[ 'source', './woocommerce' ],
		] );

		App::make( ExposedRunWooE2ETestCommand::class )->resolve_test_package_for_test( $input, $output );
		$written = $output->fetch();

		$this->assertStringContainsString( 'WooCommerce is the extension under test', $written );
		$this->assertStringContainsString( self::FALLBACK, $written );
	}

	public function test_a_bare_plugin_option_replaces_the_environments_entry(): void {
		$this->given_published( [ '9.6', '10.9', '11.0' ] );

		// `ConfigMerger::merge_by_slug()` indexes by the raw value, so a bare
		// `woocommerce` from the CLI lands on the same key as the block's entry and
		// replaces it. The environment installs stable, not 10.9.4.
		$this->assertSame(
			'woocommerce/core-e2e-tests:9.6',
			$this->resolve(
				[ '--environment' => 'default', '--plugin' => [ 'woocommerce' ] ],
				[ 'plugins' => [ [ 'slug' => 'woocommerce', 'version' => '10.9.4' ] ] ]
			)
		);

		// A pin is a different key, so it joins the list instead of replacing.
		$this->assertSame(
			'woocommerce/core-e2e-tests:10.9',
			$this->resolve(
				[ '--environment' => 'default', '--plugin' => [ 'woocommerce:11.0.1' ] ],
				[ 'plugins' => [ [ 'slug' => 'woocommerce', 'version' => '10.9.4' ] ] ]
			)
		);
	}

	public function test_a_raw_artifact_that_reads_as_woocommerce_is_unknown(): void {
		$this->given_published( [ '9.6', '10.9', '11.0' ] );

		// `ExtensionInputParser` infers the slug from the filename, so each of
		// these installs WooCommerce from an artifact whose version is inside it.
		foreach ( [
			'https://example.test/woocommerce.zip',
			'https://example.test/woocommerce.10.9.0.zip',
			'/tmp/woocommerce.zip',
		] as $value ) {
			$this->assertSame(
				self::FALLBACK,
				$this->resolve(
					[ '--woocommerce_version' => '11.0.1', '--plugin' => [ $value ] ],
					[]
				),
				$value
			);
		}

		// An artifact for something else says nothing about WooCommerce. Nor does
		// `woocommerce-v11.0.1.zip`: the parser strips `.0.1` first, and its second
		// pattern then finds no dot left to match, so it infers `woocommerce-v11`.
		// Mirrored quirk and all — the point is to agree with it, not to improve it.
		foreach ( [ 'https://example.test/some-plugin.zip', '/tmp/woocommerce-v11.0.1.zip' ] as $value ) {
			$this->assertSame(
				'woocommerce/core-e2e-tests:11.0',
				$this->resolve(
					[ '--woocommerce_version' => '11.0.1', '--plugin' => [ $value ] ],
					[]
				),
				$value
			);
		}
	}

	public function test_an_artifact_that_is_not_the_sut_says_so(): void {
		$this->given_published( [ '11.0' ] );

		$output = new BufferedOutput();
		$this->resolve(
			[ '--environment' => 'default', '--plugin' => [ 'woocommerce@https://example.test/woo.zip' ] ],
			[],
			$output
		);
		$written = $output->fetch();

		$this->assertStringContainsString( 'WooCommerce is supplied as an artifact', $written );
		$this->assertStringNotContainsString( 'extension under test', $written );
	}

	public function test_a_supplied_woocommerce_sut_named_by_id_leaves_its_version_unknown(): void {
		// 9.6 is what the fixture's stable resolves to, so falling through to
		// stable would answer 9.6 rather than the fallback.
		$this->given_published( [ '9.6', '10.9', '11.0' ] );
		$this->set_sync_key( 'extensions', [ [ 'id' => 123456, 'slug' => 'woocommerce', 'type' => 'plugin' ] ] );

		// `RunE2ECommand` turns the id into a slug later, so matching on the slug
		// alone missed `run:activation <woo id> --zip`, which is the form the CI
		// runner uses.
		$this->assertSame(
			self::FALLBACK,
			$this->resolve_with_sut( [ 'slug' => '123456' ], [], '/tmp/woo.zip' )
		);
	}

	public function test_an_id_for_another_extension_is_left_alone(): void {
		$this->given_published( [ '10.9', '11.0' ] );
		$this->set_sync_key( 'extensions', [
			[ 'id' => 123456, 'slug' => 'woocommerce', 'type' => 'plugin' ],
			[ 'id' => 10001, 'slug' => 'wccom-plugin-1', 'type' => 'plugin' ],
		] );

		// The common case: a partner extension supplied as a ZIP, with WooCommerce
		// coming from the flag. Treating every id-and-artifact run as unknowable
		// would put all of these on the default.
		$this->assertSame(
			'woocommerce/core-e2e-tests:11.0',
			$this->resolve_with_sut( [ 'slug' => '10001' ], [ '--woocommerce_version' => '11.0.1' ], '/tmp/plugin.zip' )
		);
	}

	public function test_an_unresolvable_id_changes_nothing(): void {
		$this->given_published( [ '10.9', '11.0' ] );
		$this->set_sync_key( 'extensions', [] );

		$this->assertSame(
			'woocommerce/core-e2e-tests:11.0',
			$this->resolve_with_sut( [ 'slug' => '999999' ], [ '--woocommerce_version' => '11.0.1' ], '/tmp/plugin.zip' )
		);
	}

	public function test_a_woocommerce_sut_that_names_its_version_uses_it(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		$this->assertSame(
			'woocommerce/core-e2e-tests:10.9',
			$this->resolve_with_sut( [ 'slug' => 'woocommerce', 'version' => '10.9.4' ], [], '/tmp/woo.zip' )
		);
	}

	public function test_a_woocommerce_sut_named_by_slug_still_takes_the_flag(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		// Nothing was supplied, so WooCommerce comes from wp.org and the flag is
		// what decides which version that is.
		$this->assertSame(
			'woocommerce/core-e2e-tests:11.0',
			$this->resolve_with_sut( [ 'slug' => 'woocommerce' ], [ '--woocommerce_version' => '11.0.1' ] )
		);
	}

	public function test_says_nothing_when_the_caller_asked_for_json(): void {
		$this->given_published( [ '11.0' ] );

		// stdout is a payload in JSON mode, and a notice beside it is a parse error.
		foreach ( [ '11.0.1', '9.0.0' ] as $woocommerce_version ) {
			$output = new BufferedOutput();
			$this->resolve(
				[ '--woocommerce_version' => $woocommerce_version, '--json' => true ],
				[],
				$output
			);

			$this->assertSame( '', $output->fetch(), sprintf( 'WooCommerce %s', $woocommerce_version ) );
		}
	}

	public function test_says_which_package_it_chose(): void {
		$this->given_published( [ '11.0' ] );

		$output = new BufferedOutput();
		$this->resolve( [ '--woocommerce_version' => '11.0.1' ], [], $output );

		$this->assertStringContainsString(
			'Using test package woocommerce/core-e2e-tests:11.0',
			$output->fetch()
		);
	}

	public function test_says_so_when_no_package_covers_the_version(): void {
		$this->given_published( [ '11.0' ] );

		$output = new BufferedOutput();
		$this->resolve( [ '--woocommerce_version' => '9.0.0' ], [], $output );
		$written = $output->fetch();

		$this->assertStringContainsString( 'No test package covers WooCommerce 9.0.0', $written );
		$this->assertStringContainsString( self::FALLBACK, $written );
	}

	public function test_says_so_when_the_manager_publishes_no_versions(): void {
		$this->given_published( null );

		$output = new BufferedOutput();
		$this->resolve( [ '--woocommerce_version' => '11.0.1' ], [], $output );
		$written = $output->fetch();

		$this->assertStringContainsString( 'does not publish test package versions', $written );
		$this->assertStringContainsString( self::FALLBACK, $written );
	}
}
