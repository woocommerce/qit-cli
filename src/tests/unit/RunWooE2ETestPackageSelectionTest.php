<?php

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\RunWooE2ETestCommand;
use QIT_CLI\ManagerSync;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Exposes the package choice without reflection, which behaves differently across
 * the PHP versions this suite runs on: `setAccessible()` is required before 8.1
 * and deprecated from 8.5.
 */
class ExposedRunWooE2ETestCommand extends RunWooE2ETestCommand {
	public function resolve_test_package_for_test( QITInput $input, NullOutput $output ): string {
		return $this->resolve_test_package( $input, $output );
	}
}

/**
 * `run:woo-e2e` picks its package from the table the Manager resolved.
 *
 * It used to hardcode `latest` for every run, so a run on one WooCommerce
 * version executed the same specs as a run on any other.
 */
class RunWooE2ETestPackageSelectionTest extends \QIT_CLI_Tests\QITTestCase {
	private const FALLBACK = 'woocommerce/core-e2e-tests:latest';

	/**
	 * @param array<string, array<string, string>>|null $test_package_versions Null removes the key.
	 */
	private function given_sync_offers( ?array $test_package_versions ): void {
		$cache        = App::make( Cache::class );
		$manager_sync = App::make( ManagerSync::class );
		$sync_data    = $cache->get( $manager_sync->bootstrap_cache_key );

		$this->assertIsArray( $sync_data );

		if ( is_null( $test_package_versions ) ) {
			unset( $sync_data['test_package_versions'] );
		} else {
			$sync_data['test_package_versions'] = $test_package_versions;
		}

		$cache->set( $manager_sync->bootstrap_cache_key, $sync_data, 60 );
	}

	/**
	 * The version as `get_environment_options()` reports it: the merged view of a
	 * qit.json profile and the CLI flag, which is also what `env:up` receives.
	 */
	private function resolve_for( ?string $woocommerce_version ): string {
		$env_options = $woocommerce_version === null
			? [ '--environment' => 'default' ]
			: [ '--environment' => 'default', '--woocommerce_version' => $woocommerce_version ];

		$input = $this->createMock( QITInput::class );
		$input->method( 'get_environment_options' )->willReturn( $env_options );

		return App::make( ExposedRunWooE2ETestCommand::class )
			->resolve_test_package_for_test( $input, new NullOutput() );
	}

	public function test_uses_the_package_the_manager_resolved_for_the_requested_version(): void {
		$this->given_sync_offers( [
			'e2e' => [
				'11.0.1' => 'woocommerce/core-e2e-tests:11.0',
				'11.1.0' => 'woocommerce/core-e2e-tests:11.1',
			],
		] );

		$this->assertSame( 'woocommerce/core-e2e-tests:11.0', $this->resolve_for( '11.0.1' ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:11.1', $this->resolve_for( '11.1.0' ) );
	}

	public function test_uses_the_entry_for_a_channel_named_verbatim(): void {
		$this->given_sync_offers( [
			'e2e' => [
				'stable' => 'woocommerce/core-e2e-tests:11.0',
				'rc'     => 'woocommerce/core-e2e-tests:11.1',
			],
		] );

		$this->assertSame( 'woocommerce/core-e2e-tests:11.0', $this->resolve_for( 'stable' ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:11.1', $this->resolve_for( 'rc' ) );
	}

	public function test_a_run_that_names_no_version_takes_the_stable_entry(): void {
		$this->given_sync_offers( [
			'e2e' => [ 'stable' => 'woocommerce/core-e2e-tests:11.0' ],
		] );

		$this->assertSame( 'woocommerce/core-e2e-tests:11.0', $this->resolve_for( null ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:11.0', $this->resolve_for( '' ) );
		$this->assertSame( 'woocommerce/core-e2e-tests:11.0', $this->resolve_for( '  ' ) );
	}

	public function test_uses_a_version_pinned_by_a_profile_rather_than_a_flag(): void {
		$this->given_sync_offers( [
			'e2e' => [
				'11.0.1' => 'woocommerce/core-e2e-tests:11.0',
				'stable' => 'woocommerce/core-e2e-tests:11.1',
			],
		] );

		// `get_environment_options()` reports a profile's `woo: 11.0.1` under the
		// same key as the flag, so the suite follows the environment either way.
		$this->assertSame( 'woocommerce/core-e2e-tests:11.0', $this->resolve_for( '11.0.1' ) );
	}

	public function test_falls_back_for_a_version_the_table_does_not_cover(): void {
		$this->given_sync_offers( [
			'e2e' => [ '11.0.1' => 'woocommerce/core-e2e-tests:11.0' ],
		] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '10.9.4' ) );
	}

	public function test_falls_back_when_the_manager_publishes_no_table(): void {
		$this->given_sync_offers( null );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.0.1' ) );
	}

	public function test_falls_back_when_the_table_holds_nothing_for_e2e(): void {
		$this->given_sync_offers( [] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.0.1' ) );
	}

	public function test_falls_back_on_an_unusable_entry(): void {
		$this->given_sync_offers( [ 'e2e' => [ '11.0.1' => '' ] ] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.0.1' ) );
	}
}
