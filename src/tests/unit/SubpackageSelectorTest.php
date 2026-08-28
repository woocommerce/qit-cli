<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Utils\SubpackageSelector;

/**
 * Test for SubpackageSelector - validates --subpackage selections
 * against the supplied test packages.
 */
class SubpackageSelectorTest extends TestCase {

	/** @var array<string> Temp directories created during the test. */
	private array $temp_dirs = [];

	protected function tearDown(): void {
		foreach ( $this->temp_dirs as $dir ) {
			if ( file_exists( $dir . '/qit-test.json' ) ) {
				unlink( $dir . '/qit-test.json' );
			}
			if ( is_dir( $dir ) ) {
				rmdir( $dir );
			}
		}
		$this->temp_dirs = [];
		parent::tearDown();
	}

	/**
	 * Create a temp package directory containing the given qit-test.json contents.
	 *
	 * @param array<string,mixed>|string $manifest Manifest data (or raw string for invalid JSON).
	 */
	private function create_package_dir( $manifest ): string {
		$dir = sys_get_temp_dir() . '/qit-subpackage-selector-test-' . uniqid();
		mkdir( $dir, 0777, true );
		$this->temp_dirs[] = $dir;

		$contents = is_string( $manifest ) ? $manifest : json_encode( $manifest );
		file_put_contents( $dir . '/qit-test.json', $contents );

		return $dir;
	}

	/**
	 * A schema-valid e2e results block (ctrf-json + blob-dir are required for e2e).
	 *
	 * @return array<string,string>
	 */
	private function get_e2e_results_config(): array {
		return [
			'ctrf-json' => './results/ctrf.json',
			'blob-dir'  => './blob-report',
		];
	}

	/**
	 * @return array<string,mixed> A schema-valid parent manifest with two subpackages.
	 */
	private function get_parent_manifest_data(): array {
		return [
			'package'     => 'woocommerce/e2e-suite',
			'test_type'   => 'e2e',
			'test'        => [
				'phases'  => [ 'run' => [ 'npx playwright test' ] ],
				'results' => $this->get_e2e_results_config(),
			],
			'subpackages' => [
				'woocommerce/checkout' => [
					'test' => [ 'phases' => [ 'run' => [ 'npx playwright test tests/checkout.spec.js' ] ] ],
				],
				'woocommerce/cart'     => [
					'test' => [ 'phases' => [ 'run' => [ 'npx playwright test tests/cart.spec.js' ] ] ],
				],
			],
		];
	}

	/**
	 * Test happy path: one local package, valid subpackage IDs.
	 */
	public function test_valid_selection_returns_parent_dir(): void {
		$dir = $this->create_package_dir( $this->get_parent_manifest_data() );

		$result = SubpackageSelector::validate_selection(
			[ 'woocommerce/checkout', 'woocommerce/cart' ],
			[ $dir ]
		);

		$this->assertEquals( $dir, $result );
	}

	/**
	 * Test duplicate subpackage IDs are deduplicated (not an error).
	 */
	public function test_duplicate_subpackage_ids_are_deduped(): void {
		$dir = $this->create_package_dir( $this->get_parent_manifest_data() );

		$result = SubpackageSelector::validate_selection(
			[ 'woocommerce/checkout', 'woocommerce/checkout' ],
			[ $dir ]
		);

		$this->assertEquals( $dir, $result );
	}

	/**
	 * Test that a remote package reference is rejected, even alongside a local package.
	 */
	public function test_remote_reference_is_rejected(): void {
		$dir = $this->create_package_dir( $this->get_parent_manifest_data() );

		try {
			SubpackageSelector::validate_selection(
				[ 'woocommerce/checkout' ],
				[ $dir, 'woocommerce/e2e:latest' ]
			);
			$this->fail( 'Expected a RuntimeException for the remote reference, but none was thrown.' );
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( 'Remote test package references are not allowed when using --subpackage', $e->getMessage() );
			$this->assertStringContainsString( 'woocommerce/e2e:latest', $e->getMessage() );
		}
	}

	/**
	 * Test that zero local packages is rejected.
	 */
	public function test_no_local_package_is_rejected(): void {
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'The --subpackage option requires a local test package' );

		SubpackageSelector::validate_selection( [ 'woocommerce/checkout' ], [] );
	}

	/**
	 * Test that multiple local packages are rejected.
	 */
	public function test_multiple_local_packages_are_rejected(): void {
		$dir_one = $this->create_package_dir( $this->get_parent_manifest_data() );
		$dir_two = $this->create_package_dir( [
			'package'   => 'woocommerce/other-suite',
			'test_type' => 'e2e',
			'test'      => [
				'phases'  => [ 'run' => [ 'npx playwright test' ] ],
				'results' => $this->get_e2e_results_config(),
			],
		] );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'requires exactly ONE local test package, but 2 were found' );

		SubpackageSelector::validate_selection( [ 'woocommerce/checkout' ], [ $dir_one, $dir_two ] );
	}

	/**
	 * Test that a package without subpackages is rejected.
	 */
	public function test_package_without_subpackages_is_rejected(): void {
		$dir = $this->create_package_dir( [
			'package'   => 'woocommerce/plain-suite',
			'test_type' => 'e2e',
			'test'      => [
				'phases'  => [ 'run' => [ 'npx playwright test' ] ],
				'results' => $this->get_e2e_results_config(),
			],
		] );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Test package 'woocommerce/plain-suite' ({$dir}) does not define any subpackages" );

		SubpackageSelector::validate_selection( [ 'woocommerce/checkout' ], [ $dir ] );
	}

	/**
	 * Test that an unknown subpackage ID is rejected, listing available IDs.
	 * Short names (without the namespace) must not match - full IDs only.
	 */
	public function test_unknown_subpackage_id_is_rejected_with_available_list(): void {
		$dir = $this->create_package_dir( $this->get_parent_manifest_data() );

		// 'checkout' is a short name - only the full ID 'woocommerce/checkout' is valid.
		try {
			SubpackageSelector::validate_selection( [ 'checkout' ], [ $dir ] );
			$this->fail( 'Expected a RuntimeException for the unknown subpackage ID, but none was thrown.' );
		} catch ( \RuntimeException $e ) {
			$this->assertStringContainsString( "Subpackage 'checkout' not found in test package 'woocommerce/e2e-suite'", $e->getMessage() );
			$this->assertStringContainsString( 'woocommerce/checkout', $e->getMessage() );
			$this->assertStringContainsString( 'woocommerce/cart', $e->getMessage() );
		}
	}

	/**
	 * Test that invalid JSON in the local manifest is rejected.
	 */
	public function test_invalid_manifest_json_is_rejected(): void {
		$dir = $this->create_package_dir( '{ this is not valid json' );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Invalid JSON in {$dir}/qit-test.json" );

		SubpackageSelector::validate_selection( [ 'woocommerce/checkout' ], [ $dir ] );
	}

	/**
	 * Test that a schema-invalid manifest (valid JSON, but violates the test
	 * package schema) is rejected early.
	 */
	public function test_schema_invalid_manifest_is_rejected(): void {
		$dir = $this->create_package_dir( [
			'package'     => 'woocommerce/e2e-suite',
			'test_type'   => 'e2e',
			'test'        => [
				'phases'  => [ 'run' => [ 'npx playwright test' ] ],
				'results' => [], // Missing required ctrf-json / blob-dir for e2e.
			],
			'subpackages' => [
				'woocommerce/checkout' => [
					'test' => [ 'phases' => [ 'run' => [ 'npx playwright test tests/checkout.spec.js' ] ] ],
				],
			],
		] );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Schema validation failed' );

		SubpackageSelector::validate_selection( [ 'woocommerce/checkout' ], [ $dir ] );
	}
}
