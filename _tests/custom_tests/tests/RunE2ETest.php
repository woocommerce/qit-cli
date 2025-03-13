<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;
use Spatie\Snapshots\Drivers\JsonDriver;

class RunE2ETest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;
	use ScaffoldHelpers;

	public function test_runs_scaffolded_e2e() {
		$output = qit( [
				'run:e2e',
				'woocommerce-amazon-s3-storage',
				$this->scaffold_test(),
				'--plugin',
				'woocommerce:activate',
			]
		);

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_tag_and_run_test() {
		qit( [
			'tag:upload',
			'woocommerce-amazon-s3-storage:self-test-tag-and-run',
			$this->scaffold_test(),
		] );

		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			'self-test-tag-and-run',
			'--plugin',
			'woocommerce:activate',
		] );

		qit( [ 'tag:delete', 'woocommerce-amazon-s3-storage:self-test-tag-and-run' ] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_multiple_tags_and_run_tests() {
		qit( [
			'tag:upload',
			'woocommerce-amazon-s3-storage:self-test-multiple-test-tags',
			$this->scaffold_test(),
		] );

		qit( [
			'tag:upload',
			'woocommerce-amazon-s3-storage:self-test-multiple-test-tags-another',
			$this->scaffold_test( 'another-tag' ),
		] );

		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			'self-test-multiple-test-tags,self-test-multiple-test-tags-another',
			'--plugin',
			'woocommerce:activate',
		] );

		qit( [ 'tag:delete', 'woocommerce-amazon-s3-storage:self-test-multiple-test-tags' ] );
		qit( [ 'tag:delete', 'woocommerce-amazon-s3-storage:self-test-multiple-test-tags-another' ] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_theme_as_sut() {
		// Scaffold.
		$scaffolded_dir = $this->scaffold_test();

		$activate_theme_test = <<<'JS'
import { test, expect } from '@playwright/test';
import qit from '/qitHelpers';

test('I can activate Deli', async ({ page }) => {
    await qit.loginAsAdmin(page);
    await page.getByRole('link', { name: 'Appearance' }).click();
    await page.getByLabel('Activate Deli').click();
    await page.goto('/');
});
JS;

		// Create a new test that will activate the theme.
		if ( ! file_put_contents( $scaffolded_dir . '/activate-theme.spec.js', $activate_theme_test ) ) {
			throw new \RuntimeException( 'Failed to create the scaffolded test file.' );

		}

		// Run.
		$output = qit( [
			'run:e2e',
			'deli',
			$scaffolded_dir,
			'--source',
			__DIR__ . '/../data/deli.zip',
			'--theme',
			'storefront',
			'--skip_activating_themes',
		] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_run_with_snapshot() {
		// Scaffold.
		$scaffolded_dir = $this->scaffold_test();

		$activate_theme_test = <<<'JS'
import { test, expect } from '@playwright/test';
import qit from '/qitHelpers';

test('I can activate Deli', async ({ page }) => {
    await qit.loginAsAdmin(page);
    await page.getByRole('link', { name: 'Appearance' }).click();
    await page.getByLabel('Activate Deli').click();
    await page.goto('/');
    await expect(page).toHaveScreenshot('home.png', { maxDiffPixels: 100 });
});
JS;

		// Create a new test that will activate the theme.
		if ( ! file_put_contents( $scaffolded_dir . '/activate-theme.spec.js', $activate_theme_test ) ) {
			throw new \RuntimeException( 'Failed to create the scaffolded test file.' );
		}

		$this->assertFileDoesNotExist( $scaffolded_dir . '/__snapshots__' );

		// Run the first time to generate snapshots.
		$output = qit( [
			'run:e2e',
			'deli',
			$scaffolded_dir,
			'--source',
			__DIR__ . '/../data/deli.zip',
			'--theme',
			'storefront',
			'--skip_activating_themes',
			'--update_snapshots',
		] );

		$this->assertFileExists( $scaffolded_dir . '/__snapshots__' );
		$this->assertMatchesNormalizedSnapshot( $this->normalize_scaffolded_test_run_output( $output ) );

		// Run the second time to validate snapshot.
		$output = qit( [
			'run:e2e',
			'deli',
			$scaffolded_dir,
			'--source',
			__DIR__ . '/../data/deli.zip',
			'--theme',
			'storefront',
			'--skip_activating_themes',
		] );

		$this->assertMatchesNormalizedSnapshot( $this->normalize_scaffolded_test_run_output( $output ) );

		if ( extension_loaded( 'imagick' ) ) {
			$image_path = $scaffolded_dir . '/__snapshots__/activate-theme.spec.js/home.png';

			// Load the image into Imagick
			$imagick = new \Imagick( $image_path );
			$draw    = new \ImagickDraw();

			// Set fill color to black and remove any gravity or stroke settings
			$draw->setFillColor( new \ImagickPixel( 'black' ) );

			// Draw a small 40x40 black rectangle at position (10,10)
			$draw->rectangle( 10, 10, 50, 50 );

			// Apply the drawing to the image
			$imagick->drawImage( $draw );
			$imagick->writeImage( $image_path );

			// Run the third time to check for snapshot failure.
			$output = qit( [
				'run:e2e',
				'deli',
				$scaffolded_dir,
				'--source',
				__DIR__ . '/../data/deli.zip',
				'--theme',
				'storefront',
				'--skip_activating_themes',
			], [], 1 );

			$output = $this->normalize_snapshot_diff( $output, 892 );

			$this->assertMatchesNormalizedSnapshot( $this->normalize_scaffolded_test_run_output( $output ) );
		} else {
			$this->markTestSkipped( 'Imagick extension is not available.' );
		}
	}

	public function test_playwright_config_override() {
		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
			'--plugin',
			'woocommerce:activate',
		], [
				'playwright_config' => [
					'reportSlowTests' => [
						'max'       => 10,
						'threshold' => 1,
					],
				],
			]
		);

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_cannot_use_woo_and_plugin_woocommerce() {
		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
			'--woo',
			'8.6.2',
			'--plugin',
			'woocommerce',
		],
			[],
			2
		);

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_can_use_space() {
		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
			'--plugin',
			'woocommerce'
		] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_can_use_equal_signs() {
		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
			'--plugin=woocommerce',
		] );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_directory_with_same_basename_as_sut() {
		$this->scaffold_plugin('woocommerce-amazon-s3-storage');

		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
			'--json',
			'--plugin=woocommerce',
		], [], 0, [ 'QIT_SELF_TEST' => 'env_info' ] );

		$output = $this->normalize_env_info( json_decode( $output, true ) );

		$output = json_encode( $output, JSON_PRETTY_PRINT );

		$this->assertMatchesNormalizedSnapshot( $output, new JsonDriver() );
	}

	public function test_directory_with_same_basename_as_sut_with_env_up() {
		$this->scaffold_plugin('woocommerce-amazon-s3-storage');

		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
			'--json',
			'--plugin=woocommerce',
		], [], 0, [ 'QIT_SELF_TEST' => 'env_up' ] );

		$output = $this->normalize_env_info( json_decode( $output, true ) );

		$output = json_encode( $output, JSON_PRETTY_PRINT );

		$this->assertMatchesNormalizedSnapshot( $output, new JsonDriver() );
	}
}