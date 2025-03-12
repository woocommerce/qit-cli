<?php

use PHPUnit\Framework\TestCase;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

/**
 * Tests for verifying single-theme auto-activation, multiple themes, and
 * various theme install sources (WP.org, local paths, etc.).
 */
class EnvUpThemeTest extends TestCase {
	use SnapshotHelpers;

	/**
	 * Where we'll store storefront.zip locally.
	 *
	 * @var string
	 */
	private $storefront_local_path;

	protected function setUp(): void {
		parent::setUp();
		$this->storefront_local_path = sys_get_temp_dir() . '/storefront.zip';

		// If it's not already present, download it once before these tests:
		if ( ! file_exists( $this->storefront_local_path ) ) {
			$this->download_storefront_zip();
		}
	}

	protected function tearDown(): void {
		// Clean up the environment after each test
		qit( [ 'env:down' ] );
		parent::tearDown();
	}

	/**
	 * Download the Storefront zip from WP.org to a local file.
	 *
	 * Adjust if you need special error handling, retries, etc.
	 */
	private function download_storefront_zip(): void {
		$url          = 'https://downloads.wordpress.org/theme/storefront.zip';
		$zip_contents = @file_get_contents( $url );
		if ( ! $zip_contents ) {
			$this->markTestSkipped( 'Could not download Storefront from ' . $url );

			return;
		}

		file_put_contents( $this->storefront_local_path, $zip_contents );
		$this->assertFileExists( $this->storefront_local_path, 'Failed writing storefront.zip to temp directory.' );
	}

	/**
	 * Single theme from the WP.org URL => auto-activate.
	 * (Uses the direct link, no local file.)
	 */
	public function test_env_up_with_one_url_theme_auto_activates() {
		$url = 'https://downloads.wordpress.org/theme/storefront.zip';

		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			$url,
		] );

		$json = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $json, 'No env_id in the QIT JSON output.' );

		// Inspect installed themes:
		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// We expect "storefront" to appear (the exact folder name might be "storefront").
		$this->assertStringContainsString( 'storefront', $theme_list );
		// Should be active:
		$this->assertStringContainsString( 'active', $theme_list );
	}

	/**
	 * Single theme from a local .zip => auto-activate.
	 * We already downloaded storefront.zip in setUp().
	 */
	public function test_env_up_with_one_local_file_theme_auto_activates() {
		if ( ! file_exists( $this->storefront_local_path ) ) {
			$this->markTestSkipped( 'storefront.zip not found locally.' );
		}

		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			$this->storefront_local_path,
		] );

		$json = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $json, 'No env_id in the QIT JSON output.' );

		// Inspect installed themes:
		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// We expect "storefront" to appear.
		$this->assertStringContainsString( 'storefront', $theme_list );
		// Should be active:
		$this->assertStringContainsString( 'active', $theme_list );
	}

	/**
	 * Multiple themes => skip auto-activation entirely.
	 */
	public function test_env_up_with_multiple_themes_skips_theme_activation() {
		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			'storefront',
			'--theme',
			'twentytwentyone',
		] );
		$json   = json_decode( $output, true );

		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// Both themes installed, but neither is active
		$this->assertStringContainsString( 'storefront', $theme_list );
		$this->assertStringContainsString( 'twentytwentyone', $theme_list );
		$this->assertStringNotContainsString( 'storefront    active', $theme_list );
		$this->assertStringNotContainsString( 'twentytwentyone    active', $theme_list );
	}

	/**
	 * No themes passed => no special QIT auto-activation.
	 * The default WP theme might still appear as active, but not "storefront".
	 */
	public function test_env_up_with_no_themes_does_not_activate_theme() {
		$output = qit( [
			'env:up',
			'--json',
		] );
		$json   = json_decode( $output, true );

		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// Expect some WP default theme (maybe "twentytwentythree") to be active,
		// but definitely not "storefront" from QIT auto-activation.
		$this->assertStringNotContainsString( 'storefront', $theme_list );
	}

	/**
	 * If --skip_activating_plugins is used, that also prevents theme activation.
	 */
	public function test_env_up_with_skip_activating_plugins_does_not_activate_theme() {
		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			'storefront',
			'--skip_activating_plugins',
		] );
		$json   = json_decode( $output, true );

		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// storefront is installed but not active
		$this->assertStringContainsString( 'storefront', $theme_list );
		$this->assertStringContainsString( 'inactive', $theme_list );
	}
}