<?php

namespace QIT_CLI_Tests;

use QIT_CLI\Commands\Environment\UpEnvironmentCommand;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Unit tests for the internal `--subpackage` / `--subpackage-parent` options
 * that `run:e2e` passes to `env:up`.
 */
class UpEnvironmentSubpackageOptionTest extends QITTestCase {

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
	 * Create a temp local parent package with a single subpackage.
	 */
	private function create_parent_package_dir(): string {
		$dir = sys_get_temp_dir() . '/qit-env-up-subpackage-test-' . uniqid();
		mkdir( $dir, 0777, true );
		$this->temp_dirs[] = $dir;

		file_put_contents( $dir . '/qit-test.json', json_encode( [
			'package'     => 'woocommerce/e2e-suite',
			'test_type'   => 'e2e',
			'test'        => [
				'phases'  => [ 'run' => [ 'npx playwright test' ] ],
				'results' => [
					'ctrf-json' => './results/ctrf.json',
					'blob-dir'  => './blob-report',
				],
			],
			'subpackages' => [
				'woocommerce/cart' => [
					'test' => [ 'phases' => [ 'run' => [ 'npx playwright test tests/cart.spec.js' ] ] ],
				],
			],
		] ) );

		return $dir;
	}

	/**
	 * Resolve the subpackage selection for the given `env:up` options.
	 *
	 * @param array<string,mixed> $options The `env:up` options.
	 *
	 * @return array{0:array<string>,1:string|null} The selected subpackages and parent directory.
	 */
	private function resolve_selection( array $options ): array {
		/** @var UpEnvironmentCommand $command */
		$command = $GLOBALS['qit_application']->find( 'env:up' );

		$input = new ArrayInput( $options, $command->getDefinition() );

		$reflection = new \ReflectionClass( UpEnvironmentCommand::class );
		$method     = $reflection->getMethod( 'resolve_subpackage_selection' );
		$method->setAccessible( true );
		$method->invoke( $command, $input );

		$selected = $reflection->getProperty( 'selected_subpackages' );
		$selected->setAccessible( true );
		$parent = $reflection->getProperty( 'subpackage_parent_dir' );
		$parent->setAccessible( true );

		return [ $selected->getValue( $command ), $parent->getValue( $command ) ];
	}

	/**
	 * Test that a valid selection is stored with the parent's real path.
	 */
	public function test_valid_selection_is_resolved(): void {
		$dir = $this->create_parent_package_dir();

		$this->assertSame(
			[ [ 'woocommerce/cart' ], realpath( $dir ) ],
			$this->resolve_selection( [
				'--subpackage'        => [ 'woocommerce/cart' ],
				'--subpackage-parent' => $dir . '/.',
			] )
		);
	}

	/**
	 * Test that no selection leaves the state empty, including after a
	 * previous run on the same (reused) command instance.
	 */
	public function test_no_selection_resets_state(): void {
		$dir = $this->create_parent_package_dir();
		$this->resolve_selection( [
			'--subpackage'        => [ 'woocommerce/cart' ],
			'--subpackage-parent' => $dir,
		] );

		$this->assertSame( [ [], null ], $this->resolve_selection( [] ) );
	}

	/**
	 * Provide invalid option combinations.
	 *
	 * @return array<string,array{0:array<string,mixed>,1:string}>
	 */
	public function invalid_selection_provider(): array {
		return [
			'subpackage without parent'     => [
				[ '--subpackage' => [ 'woocommerce/cart' ] ],
				'The --subpackage option requires --subpackage-parent to be set.',
			],
			'parent without subpackage'     => [
				[ '--subpackage-parent' => '__PARENT__' ],
				'The --subpackage-parent option requires at least one --subpackage value.',
			],
			'unknown subpackage'            => [
				[
					'--subpackage'        => [ 'woocommerce/unknown' ],
					'--subpackage-parent' => '__PARENT__',
				],
				"Subpackage 'woocommerce/unknown' not found in test package 'woocommerce/e2e-suite'.",
			],
			'empty subpackage value'        => [
				[
					'--subpackage'        => [ '' ],
					'--subpackage-parent' => '__PARENT__',
				],
				'The --subpackage option requires a non-empty subpackage ID.',
			],
		];
	}

	/**
	 * Test that invalid selections fail fast.
	 *
	 * @dataProvider invalid_selection_provider
	 *
	 * @param array<string,mixed> $options          The `env:up` options ('__PARENT__' is replaced with a real package dir).
	 * @param string              $expected_message The expected error message.
	 */
	public function test_invalid_selection_is_rejected( array $options, string $expected_message ): void {
		if ( ( $options['--subpackage-parent'] ?? null ) === '__PARENT__' ) {
			$options['--subpackage-parent'] = $this->create_parent_package_dir();
		}

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( $expected_message );

		$this->resolve_selection( $options );
	}
}
