<?php

use QIT\SelfTests\CustomTests\Traits\ScaffoldHelpers;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

class RunE2ETest extends \PHPUnit\Framework\TestCase {
	use SnapshotHelpers;
	use ScaffoldHelpers;

	public function test_fails_if_dependency_unmet() {
		$output = qit( [
			'run:e2e',
			'woocommerce-amazon-s3-storage',
			$this->scaffold_test(),
		],
			[],
			1
		);

		$output = $this->normalize_scaffolded_test_run_output( $output );

		$this->assertMatchesNormalizedSnapshot( $output );
	}

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
    await expect(page.getByRole('cell', { name: 'Deli' })).toBeVisible();
    await page.getByRole('link', { name: 'Install Parent Theme' }).click();
    await page.getByRole('link', { name: 'Activate “Storefront”' }).click();
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
			'--testing_theme',
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
    await expect(page.getByRole('cell', { name: 'Deli' })).toBeVisible();
    await page.getByRole('link', { name: 'Install Parent Theme' }).click();
    await page.getByRole('link', { name: 'Activate “Storefront”' }).click();
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
			'--testing_theme',
			'--update_snapshots',
		] );

		$this->assertFileExists( $scaffolded_dir . '/__snapshots__' );
		$this->assertMatchesNormalizedSnapshot( $this->normalize_scaffolded_test_run_output( $output ) );

		// Run the second time to validate snapshot.
		$output = qit( [
			'run:e2e',
			'deli',
			$scaffolded_dir,
			'--testing_theme',
		] );

		$this->assertMatchesNormalizedSnapshot( $this->normalize_scaffolded_test_run_output( $output ) );
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

		// "Loading environment config from override parameter /tmp/qit-env-97d237784cddc7ec1341113ca364110d.json..." Normalize "97d237784cddc7ec1341113ca364110d".
		$output = preg_replace( '/qit-env-[a-f0-9]{32}/', 'qit-env-<hash>', $output );

		// "Slow test file: [woocommerce-amazon-s3-storage-local] › woocommerce-amazon-s3-storage/local/example.spec.js (7.1s)" Normalize "7.1s".
		$output = preg_replace( '/\d+\.\d+s/', '<time>s', $output );

		// Sometimes, for some reason, this has some spaces. "Consider splitting slow test files to speed up parallel execution"
		$output = preg_replace( '#\s+Consider#', "\nConsider", $output );

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