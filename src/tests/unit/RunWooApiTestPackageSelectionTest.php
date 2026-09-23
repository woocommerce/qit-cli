<?php

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\RunWooApiTestCommand;
use QIT_CLI\ManagerSync;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Exposes the package choice without reflection, which behaves differently across
 * the PHP versions this suite runs on.
 */
class ExposedRunWooApiTestCommand extends RunWooApiTestCommand {
	public function resolve_test_package_for_test( QITInput $input, OutputInterface $output ): string {
		return $this->resolve_test_package( $input, $output );
	}
}

/**
 * `run:woo-api` picks its package by the WooCommerce version under test.
 *
 * It used to hardcode `latest`, so a suite aligned with one WooCommerce line
 * asserted REST behaviour another line does not have, and the failure was
 * reported against the extension under test. The rule is the one the Core E2E
 * command uses, through the shared trait; what differs is the sync data key
 * and the package it falls back to.
 */
class RunWooApiTestPackageSelectionTest extends \QIT_CLI_Tests\QITTestCase {
	private const FALLBACK = 'woocommerce/core-api-tests:latest';
	private const NIGHTLY  = 'woocommerce/core-api-tests:nightly';

	/**
	 * @param array<string, mixed>|null $offered Null removes the key.
	 */
	private function given_sync_offers( ?array $offered ): void {
		$cache        = App::make( Cache::class );
		$manager_sync = App::make( ManagerSync::class );
		$sync_data    = $cache->get( $manager_sync->bootstrap_cache_key );

		$this->assertIsArray( $sync_data );

		if ( is_null( $offered ) ) {
			unset( $sync_data['test_package_versions'] );
		} else {
			$sync_data['test_package_versions'] = $offered;
		}

		$cache->set( $manager_sync->bootstrap_cache_key, $sync_data, 60 );
	}

	/**
	 * @param array<int, string> $versions
	 * @return array<string, mixed>
	 */
	private function published( array $versions, string $package = 'woocommerce/core-api-tests', bool $with_nightly = true ): array {
		$offered = [ 'package' => $package, 'versions' => $versions ];

		if ( $with_nightly ) {
			$offered['nightly'] = 'nightly';
		}

		return $offered;
	}

	private function resolve_for( string $woocommerce_version, ?OutputInterface $output = null ): string {
		$input = $this->createMock( QITInput::class );
		$input->method( 'get_environment_options' )->willReturn( [
			'--environment'         => 'default',
			'--woocommerce_version' => $woocommerce_version,
		] );
		$input->method( 'get_environment_config' )->willReturn( [] );

		return App::make( ExposedRunWooApiTestCommand::class )
			->resolve_test_package_for_test( $input, $output ?? new NullOutput() );
	}

	public function test_takes_the_api_version_covering_the_woocommerce_version(): void {
		$this->given_sync_offers( [ 'api' => $this->published( [ '11.0', '11.1' ] ) ] );

		$this->assertSame( 'woocommerce/core-api-tests:11.0', $this->resolve_for( '11.0.1' ) );
		$this->assertSame( 'woocommerce/core-api-tests:11.1', $this->resolve_for( '11.1.0-rc.1' ) );
	}

	public function test_says_which_package_it_chose(): void {
		$this->given_sync_offers( [ 'api' => $this->published( [ '11.0' ] ) ] );

		$output = new BufferedOutput();
		$this->resolve_for( '11.0.1', $output );

		$this->assertStringContainsString( 'Using test package woocommerce/core-api-tests:11.0 for WooCommerce 11.0.1.', $output->fetch() );
	}

	public function test_reads_its_own_key_not_the_core_e2e_one(): void {
		// Only the Core E2E package is published for 11.1, so reading that key
		// would answer 11.1 where this must fall back, and only the API package
		// is published for 11.0, where this must answer.
		$this->given_sync_offers( [
			'e2e' => $this->published( [ '11.1' ], 'woocommerce/core-e2e-tests' ),
			'api' => $this->published( [ '11.0' ] ),
		] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.1.0' ) );
		$this->assertSame( 'woocommerce/core-api-tests:11.0', $this->resolve_for( '11.0.1' ) );
	}

	public function test_falls_back_to_latest_and_says_so_when_nothing_covers_the_version(): void {
		$this->given_sync_offers( [ 'api' => $this->published( [ '11.0' ] ) ] );

		$output = new BufferedOutput();

		$this->assertSame( self::FALLBACK, $this->resolve_for( '10.9.4', $output ) );
		$this->assertStringContainsString( 'No test package covers WooCommerce 10.9.4. Using ' . self::FALLBACK, $output->fetch() );
	}

	public function test_takes_the_nightly_package_for_an_unreleased_version_ahead_of_every_published_line(): void {
		$this->given_sync_offers( [ 'api' => $this->published( [ '11.0', '11.1' ] ) ] );

		$this->assertSame( self::NIGHTLY, $this->resolve_for( 'nightly' ) );
		$this->assertSame( self::NIGHTLY, $this->resolve_for( '11.2.0-beta.1' ) );

		// Released, so the default run is not sent to trunk's suite.
		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.2.0' ) );
	}

	public function test_keeps_the_stable_fallback_when_the_manager_advertises_no_nightly_tag(): void {
		$this->given_sync_offers( [
			'api' => $this->published( [ '11.0', '11.1' ], 'woocommerce/core-api-tests', false ),
		] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( 'nightly' ) );
	}

	public function test_falls_back_when_the_manager_publishes_no_versions(): void {
		$this->given_sync_offers( null );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.0.1' ) );
	}
}
