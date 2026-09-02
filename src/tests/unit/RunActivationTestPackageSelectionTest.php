<?php

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\RunActivationTestCommand;
use QIT_CLI\ManagerSync;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Exposes the package choice without reflection, which behaves differently across
 * the PHP versions this suite runs on.
 */
class ExposedRunActivationTestCommand extends RunActivationTestCommand {
	public function resolve_test_package_for_test( QITInput $input, OutputInterface $output ): string {
		return $this->resolve_test_package( $input, $output );
	}
}

/**
 * `run:activation` picks its package by the WooCommerce version under test.
 *
 * The activation specs drive WooCommerce's admin UI, so their selectors are tied
 * to the markup of a release. The selection rule is the one the Core E2E command
 * uses, through the shared trait; what differs is which package is selected and
 * what it falls back to.
 */
class RunActivationTestPackageSelectionTest extends \QIT_CLI_Tests\QITTestCase {
	private const FALLBACK = 'woocommerce/activation:latest';

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
	private function published( array $versions, string $package = 'woocommerce/activation' ): array {
		return [ 'package' => $package, 'versions' => $versions ];
	}

	private function resolve_for( string $woocommerce_version ): string {
		$input = $this->createMock( QITInput::class );
		$input->method( 'get_environment_options' )->willReturn( [
			'--environment'         => 'default',
			'--woocommerce_version' => $woocommerce_version,
		] );
		$input->method( 'get_environment_config' )->willReturn( [] );

		return App::make( ExposedRunActivationTestCommand::class )
			->resolve_test_package_for_test( $input, new NullOutput() );
	}

	public function test_takes_the_activation_version_covering_the_woocommerce_version(): void {
		$this->given_sync_offers( [ 'activation' => $this->published( [ '11.0', '11.1' ] ) ] );

		$this->assertSame( 'woocommerce/activation:11.0', $this->resolve_for( '11.0.1' ) );
		$this->assertSame( 'woocommerce/activation:11.1', $this->resolve_for( '11.1.0-rc.1' ) );
	}

	public function test_reads_its_own_key_not_the_core_e2e_one(): void {
		$this->given_sync_offers( [
			'e2e'        => $this->published( [ '11.1' ], 'woocommerce/core-e2e-tests' ),
			'activation' => $this->published( [ '11.0' ] ),
		] );

		$this->assertSame( 'woocommerce/activation:11.0', $this->resolve_for( '11.1.0' ) );
	}

	public function test_falls_back_to_the_activation_package_not_the_core_e2e_one(): void {
		$this->given_sync_offers( [ 'e2e' => $this->published( [ '11.1' ], 'woocommerce/core-e2e-tests' ) ] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.1.0' ) );
	}

	public function test_uses_a_version_pinned_as_a_plugin_option(): void {
		$this->given_sync_offers( [ 'activation' => $this->published( [ '10.9', '11.0' ] ) ] );

		$input = $this->createMock( QITInput::class );
		$input->method( 'get_environment_options' )->willReturn( [
			'--environment' => 'default',
			'--plugin'      => [ 'woocommerce:10.9.4' ],
		] );
		$input->method( 'get_environment_config' )->willReturn( [] );

		$this->assertSame(
			'woocommerce/activation:10.9',
			App::make( ExposedRunActivationTestCommand::class )
				->resolve_test_package_for_test( $input, new NullOutput() )
		);
	}

	public function test_falls_back_when_nothing_published_is_low_enough(): void {
		$this->given_sync_offers( [ 'activation' => $this->published( [ '11.0' ] ) ] );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '10.9.4' ) );
	}

	public function test_falls_back_when_the_manager_publishes_no_versions(): void {
		$this->given_sync_offers( null );

		$this->assertSame( self::FALLBACK, $this->resolve_for( '11.0.1' ) );
	}
}
