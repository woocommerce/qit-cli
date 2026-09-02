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
		$this->given_published( [ '9.6', '9.7' ] );

		// The fixture's sync data resolves stable to 9.6.0 and rc to 9.7.0-beta.1.
		$this->assertSame( 'woocommerce/core-e2e-tests:9.6', $this->resolve_for( 'stable' ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:9.7', $this->resolve_for( 'rc' ) );
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

	public function test_a_version_flag_outranks_a_plugin_option(): void {
		$this->given_published( [ '10.9', '11.0' ] );

		// env:up drops every WooCommerce plugin entry once a version is set, so
		// the flag is what gets installed.
		$this->assertSame(
			'woocommerce/core-e2e-tests:11.0',
			$this->resolve(
				[ '--woocommerce_version' => '11.0.1', '--plugin' => [ 'woocommerce:10.9.0' ] ],
				[]
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
