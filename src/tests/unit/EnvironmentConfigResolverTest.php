<?php

namespace QIT_CLI_Tests;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Configuration\EnvironmentConfigResolver;

/**
 * Unit tests for EnvironmentConfigResolver.
 *
 * These test the version pinning and precedence logic — the behaviors that
 * are non-obvious and where the old imperative pipeline had bugs.
 * Trivial behaviors (put X in, get X out) are covered by integration tests.
 */
class EnvironmentConfigResolverTest extends TestCase {

	private function make(): EnvironmentConfigResolver {
		return new EnvironmentConfigResolver();
	}

	// ── The Bug: --woo pin must win over bare --plugin slug ──

	public function test_cli_woo_pin_wins_over_bare_plugin(): void {
		$result = $this->make()
			->loadFromCli( [
				'woocommerce_version' => '8.8.0',
				'plugins'             => [ 'woocommerce' ],
			] )
			->resolve();
		$this->assertEquals( '8.8.0', $result['woocommerce_version'] );
		$slugs = array_column( $result['plugins'], 'slug' );
		$this->assertEquals( 1, count( array_keys( $slugs, 'woocommerce' ) ), 'No duplicates' );
	}

	public function test_cli_woo_pin_wins_over_config_plugin_version(): void {
		$result = $this->make()
			->loadFromConfig( [ 'plugins' => [
				[ 'slug' => 'woocommerce', 'from' => 'wporg', 'version' => '8.0.0' ],
			] ] )
			->loadFromCli( [ 'woocommerce_version' => '8.8.0' ] )
			->resolve();
		$this->assertEquals( '8.8.0', $result['woocommerce_version'] );
	}

	public function test_config_woo_pin_wins_over_config_plugin_version(): void {
		$result = $this->make()
			->loadFromConfig( [
				'woo'     => '8.5.0',
				'plugins' => [
					[ 'slug' => 'woocommerce', 'from' => 'wporg', 'version' => '8.0.0' ],
				],
			] )
			->resolve();
		$this->assertEquals( '8.5.0', $result['woocommerce_version'] );
	}

	public function test_cli_woo_pin_wins_over_config_woo(): void {
		$result = $this->make()
			->loadFromConfig( [ 'woo' => '8.5.0' ] )
			->loadFromCli( [ 'woocommerce_version' => '8.8.0' ] )
			->resolve();
		$this->assertEquals( '8.8.0', $result['woocommerce_version'] );
	}

	// ── No implicit WooCommerce ──

	public function test_no_woocommerce_when_nothing_requests_it(): void {
		$result = $this->make()->resolve();
		$this->assertEquals( '', $result['woocommerce_version'] );
		$this->assertEmpty( $result['plugins'] );
	}

	public function test_bare_plugin_woocommerce_has_no_fabricated_version(): void {
		$result = $this->make()
			->loadFromCli( [ 'plugins' => [ 'woocommerce' ] ] )
			->resolve();
		$slugs = array_column( $result['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $slugs );
		$this->assertEquals( '', $result['woocommerce_version'] );
	}

	public function test_config_plugin_version_respected_without_pin(): void {
		$result = $this->make()
			->loadFromConfig( [ 'plugins' => [
				[ 'slug' => 'woocommerce', 'from' => 'wporg', 'version' => '8.0.0' ],
			] ] )
			->resolve();
		$this->assertEquals( '8.0.0', $result['woocommerce_version'] );
	}

	// ── Version pin adds WooCommerce even without explicit plugin request ──

	public function test_woo_pin_alone_adds_woocommerce_to_plugins(): void {
		$result = $this->make()
			->loadFromCli( [ 'woocommerce_version' => '8.8.0' ] )
			->resolve();
		$slugs = array_column( $result['plugins'], 'slug' );
		$this->assertContains( 'woocommerce', $slugs );
		$this->assertEquals( '8.8.0', $result['woocommerce_version'] );
	}

	// ── Test package deps don't override explicit pins ──

	public function test_test_package_deps_dont_override_pin(): void {
		$result = $this->make()
			->loadFromCli( [ 'woocommerce_version' => '8.8.0' ] )
			->loadFromTestPackages( [ 'woocommerce' ], [] )
			->resolve();
		$this->assertEquals( '8.8.0', $result['woocommerce_version'] );
	}

	// ── SUT deduplication ──

	public function test_sut_not_duplicated_with_config_plugin(): void {
		$sut = [
			'slug'                => 'my-plugin',
			'from'                => 'local',
			'path'                => '/path/to/plugin',
			'added_automatically' => 'SUT',
		];
		$result = $this->make()
			->loadFromConfig( [ 'plugins' => [ 'my-plugin' ] ] )
			->loadFromSut( $sut, 'plugin' )
			->resolve();
		$slugs = array_column( $result['plugins'], 'slug' );
		$this->assertEquals( 1, count( array_keys( $slugs, 'my-plugin' ) ) );
	}

	// ── Load order independence (proves declarative model) ──

	public function test_load_order_does_not_affect_scalar_precedence(): void {
		$cli_first = $this->make()
			->loadFromCli( [ 'php_version' => '8.3' ] )
			->loadFromConfig( [ 'php' => '7.4' ] )
			->resolve();

		$config_first = $this->make()
			->loadFromConfig( [ 'php' => '7.4' ] )
			->loadFromCli( [ 'php_version' => '8.3' ] )
			->resolve();

		$this->assertEquals( '8.3', $cli_first['php_version'] );
		$this->assertEquals( $cli_first['php_version'], $config_first['php_version'] );
	}

	public function test_load_order_does_not_affect_woo_pin(): void {
		$pin_first = $this->make()
			->loadFromCli( [ 'woocommerce_version' => '8.8.0' ] )
			->loadFromConfig( [ 'plugins' => [
				[ 'slug' => 'woocommerce', 'from' => 'wporg', 'version' => '7.0.0' ],
			] ] )
			->resolve();

		$plugin_first = $this->make()
			->loadFromConfig( [ 'plugins' => [
				[ 'slug' => 'woocommerce', 'from' => 'wporg', 'version' => '7.0.0' ],
			] ] )
			->loadFromCli( [ 'woocommerce_version' => '8.8.0' ] )
			->resolve();

		$this->assertEquals( '8.8.0', $pin_first['woocommerce_version'] );
		$this->assertEquals( $pin_first['woocommerce_version'], $plugin_first['woocommerce_version'] );
	}

	// ── Short/long form normalization ──

	public function test_config_short_and_long_form_are_equivalent(): void {
		$short = $this->make()->loadFromConfig( [ 'woo' => '8.5.0' ] )->resolve();
		$long  = $this->make()->loadFromConfig( [ 'woocommerce_version' => '8.5.0' ] )->resolve();
		$this->assertEquals( $short['woocommerce_version'], $long['woocommerce_version'] );
	}
}
