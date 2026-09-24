<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\EnvironmentVars;
use QIT_CLI\Environment\PackagePhaseRunner;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Test for PackagePhaseRunner::generate_actions_manifest() - ensures actions
 * are contributed once per physical package directory.
 */
class PackagePhaseRunnerActionsManifestTest extends TestCase {

	/** @var array<string> Temp directories created during the test. */
	private array $temp_dirs = [];

	protected function tearDown(): void {
		foreach ( $this->temp_dirs as $dir ) {
			foreach ( [ 'qit-test.json', 'actions-manifest.json' ] as $file ) {
				if ( file_exists( $dir . '/' . $file ) ) {
					unlink( $dir . '/' . $file );
				}
			}
			if ( is_dir( $dir ) ) {
				rmdir( $dir );
			}
		}
		$this->temp_dirs = [];
		parent::tearDown();
	}

	private function create_temp_dir(): string {
		$dir = sys_get_temp_dir() . '/qit-actions-manifest-test-' . uniqid();
		mkdir( $dir, 0777, true );
		$this->temp_dirs[] = $dir;

		return $dir;
	}

	/**
	 * Create a package directory with a qit-test.json declaring the given actions.
	 *
	 * @param string               $package_id Package ID.
	 * @param array<string,string> $actions    Action name => relative path.
	 */
	private function create_package_dir( string $package_id, array $actions ): string {
		$dir = $this->create_temp_dir();
		file_put_contents( $dir . '/qit-test.json', json_encode( [
			'package' => $package_id,
			'actions' => $actions,
		] ) );

		return $dir;
	}

	/**
	 * Run generate_actions_manifest() against the given metadata and return the decoded manifest.
	 *
	 * @param array<string,array<string,string>> $test_packages_metadata Package ID => metadata.
	 *
	 * @return array<string,array<int,array<string,string>>>
	 */
	private function generate_actions( array $test_packages_metadata ): array {
		$runner = new PackagePhaseRunner(
			$this->createMock( Docker::class ),
			new NullOutput(),
			$this->createMock( EnvironmentVars::class ),
			$this->createMock( TestPackageManifestParser::class )
		);

		$env_info                         = new E2EEnvInfo();
		$env_info->temporary_env          = $this->create_temp_dir();
		$env_info->test_packages_metadata = $test_packages_metadata;

		$method = new \ReflectionMethod( PackagePhaseRunner::class, 'generate_actions_manifest' );
		$method->setAccessible( true );
		$path = $method->invoke( $runner, $env_info );

		return json_decode( file_get_contents( $path ), true );
	}

	public function test_sibling_subpackages_contribute_parent_actions_once(): void {
		$parent_dir = $this->create_package_dir( 'woocommerce/parent', [ 'makePurchase' => './actions/make-purchase.js' ] );

		$actions = $this->generate_actions( [
			'woocommerce/parent-a' => [ 'path' => $parent_dir ],
			'woocommerce/parent-b' => [ 'path' => $parent_dir ],
		] );

		$this->assertSame(
			[
				'makePurchase' => [
					[
						'provider' => 'woocommerce/parent',
						'path'     => $parent_dir . '/actions/make-purchase.js',
					],
				],
			],
			$actions
		);
	}

	public function test_parent_and_subpackage_contribute_actions_once(): void {
		$parent_dir = $this->create_package_dir( 'woocommerce/parent', [ 'makePurchase' => './actions/make-purchase.js' ] );

		$actions = $this->generate_actions( [
			$parent_dir            => [ 'path' => $parent_dir ],
			'woocommerce/parent-a' => [ 'path' => $parent_dir . '/' ], // Different spelling, same directory.
		] );

		$this->assertCount( 1, $actions['makePurchase'] );
	}

	public function test_distinct_packages_providing_same_action_are_both_kept(): void {
		$first_dir  = $this->create_package_dir( 'woocommerce/first', [ 'makePurchase' => './make-purchase.js' ] );
		$second_dir = $this->create_package_dir( 'woocommerce/second', [ 'makePurchase' => './make-purchase.js' ] );

		$actions = $this->generate_actions( [
			'woocommerce/first'  => [ 'path' => $first_dir ],
			'woocommerce/second' => [ 'path' => $second_dir ],
		] );

		$this->assertSame(
			[ 'woocommerce/first', 'woocommerce/second' ],
			array_column( $actions['makePurchase'], 'provider' )
		);
	}
}
