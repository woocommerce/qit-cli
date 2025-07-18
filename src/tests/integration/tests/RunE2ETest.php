<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;
use Spatie\Snapshots\Drivers\JsonDriver;

/**
 * Tests for the RunE2E command.
 * Some tests only check the resolved configuration (using qit_precommand),
 * while others execute the full command chain (using qit).
 */
class RunE2ETest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;
	use ScaffoldHelpers;

	/**
	 * @param array<string,mixed> $qit_json_array The array to convert to JSON.
	 *
	 * @return string The absolute path to the created qit.json file.
	 * @throws \RuntimeException If file creation fails.
	 */
	private static function create_temporary_qit_json( array $qit_json_array ): string {
		$qit_json_path = __DIR__ . '/qit.json';
		$json_content  = json_encode( $qit_json_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		if ( $json_content === false ) {
			throw new \RuntimeException( 'Failed to encode JSON for qit.json: ' . json_last_error_msg() );
		}

		if ( file_put_contents( $qit_json_path, $json_content ) === false ) {
			throw new \RuntimeException( "Failed to create temporary qit.json file at $qit_json_path." );
		}

		return realpath( $qit_json_path );
	}

	/**
	 * @param string $qit_json_path The absolute path to the qit.json file.
	 *
	 * @return void
	 * @throws \RuntimeException If file deletion fails.
	 */
	private static function delete_temporary_qit_json( string $qit_json_path ): void {
		if ( file_exists( $qit_json_path ) ) {
			if ( ! unlink( $qit_json_path ) ) {
				// Try to provide more context if possible, though unlink() itself doesn't give much.
				$error_details = error_get_last()['message'] ?? 'Unknown error';
				throw new \RuntimeException( "Failed to delete temporary qit.json file at $qit_json_path. Error: $error_details" );
			}
		}
	}

	public function test_runs_scaffolded_e2e() {
		$output = qit( [
				'run:e2e',
				'woocommerce-amazon-s3-storage',
				$this->scaffold_test(),
				'--plugin',
				'woocommerce',
			]
		);

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_tag_and_run_test() {
		try {
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
				'woocommerce',
			] );

			$output = $this->normalize_scaffolded_test_run_output( $output );

			$this->assertMatchesNormalizedSnapshot( $output );
		} finally {
			// Always clean up the tag, even if the test fails
			qit( [ 'tag:delete', 'woocommerce-amazon-s3-storage:self-test-tag-and-run' ] );
		}
	}

	public function test_multiple_tags_and_run_tests() {
		$tag1 = 'woocommerce-amazon-s3-storage:self-test-multiple-test-tags';
		$tag2 = 'woocommerce-amazon-s3-storage:self-test-multiple-test-tags-another';
		
		try {
			qit( [
				'tag:upload',
				$tag1,
				$this->scaffold_test(),
			] );

			qit( [
				'tag:upload',
				$tag2,
				$this->scaffold_test( 'another-tag' ),
			] );

			$output = qit( [
				'run:e2e',
				'woocommerce-amazon-s3-storage',
				'self-test-multiple-test-tags,self-test-multiple-test-tags-another',
				'--plugin',
				'woocommerce',
			] );

			$output = $this->normalize_scaffolded_test_run_output( $output );

			$this->assertMatchesNormalizedSnapshot( $output );
		} finally {
			// Always clean up the tags, even if the test fails
			qit( [ 'tag:delete', $tag1 ] );
			qit( [ 'tag:delete', $tag2 ] );
		}
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

		if ( extension_loaded( 'imagick' ) && method_exists( \ImagickDraw::class, 'rectangle' ) ) {
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
			$this->markTestSkipped( 'Imagick extension is not available or has a minimal build without rectangle method.' );
		}
	}

	public function test_playwright_config_override() {
		$config_json = <<<'JSON'
{
	"playwright_config": {
		"reportSlowTests": {
			"max": 10,
			"threshold": 1
		}
	}
}
JSON;

		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
			'--plugin',
			'woocommerce',
		], $config_json );

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

	public function test_can_use_space() {
		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
			'--plugin',
			'woocommerce',
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
}