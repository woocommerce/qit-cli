<?php

use PHPUnit\Framework\TestCase;
use QIT\SelfTests\CustomTests\Traits\SnapshotHelpers;

/**
 * Tests for verifying single-theme auto-activation, multiple themes,
 * child–parent logic, and local zips.
 */
class EnvUpThemeTest extends TestCase {
	use SnapshotHelpers;

	/**
	 * Where we'll store storefront.zip locally.
	 *
	 * @var string
	 */
	private $storefront_local_path;

	/**
	 * Where we'll store deli.zip locally (a Storefront child theme).
	 *
	 * @var string
	 */
	private $deli_local_path;

	protected function setUp(): void {
		parent::setUp();

		// Download both Storefront & Deli into /tmp/
		$this->storefront_local_path = sys_get_temp_dir() . '/storefront.zip';
		$this->deli_local_path       = sys_get_temp_dir() . '/deli.zip';

		// Download once per test run (if they aren’t present already).
		if ( ! file_exists( $this->storefront_local_path ) ) {
			$this->download_storefront_zip();
		}
		if ( ! file_exists( $this->deli_local_path ) ) {
			$this->download_deli_zip();
		}
	}

	protected function tearDown(): void {
		// Clean up the environment after each test
		qit( [ 'env:down' ] );
		parent::tearDown();
	}

	/**
	 * Download the Storefront zip from WP.org to a local file.
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
	 * Download the Deli child theme zip from WP.org to a local file.
	 * (Deli version might change; if so, update the URL accordingly).
	 */
	private function download_deli_zip(): void {
		$url          = 'https://downloads.wordpress.org/theme/deli.zip';
		$zip_contents = @file_get_contents( $url );
		if ( ! $zip_contents ) {
			$this->markTestSkipped( 'Could not download Deli from ' . $url );

			return;
		}

		file_put_contents( $this->deli_local_path, $zip_contents );
		$this->assertFileExists( $this->deli_local_path, 'Failed writing deli.zip to temp directory.' );
	}

	/**
	 * Single theme from the WP.org URL => auto-activate.
	 * (Uses the direct link for Storefront, no local file.)
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

		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// Expect "storefront" to appear and be active
		$this->assertStringContainsString( 'storefront', $theme_list );
		$this->assertStringContainsString( 'active', $theme_list );
	}

	/**
	 * Single theme from a local .zip => auto-activate Storefront.
	 */
	public function test_env_up_with_one_local_file_theme_auto_activates_storefront() {
		$this->assertFileExists( $this->storefront_local_path, 'Storefront zip missing in setUp.' );

		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			$this->storefront_local_path,
		] );

		$json = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $json, 'No env_id in the QIT JSON output.' );

		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// We expect "storefront" to appear and be active
		$this->assertStringContainsString( 'storefront', $theme_list );
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
			$this->storefront_local_path,
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
	 * No themes passed => no special auto-activation from QIT.
	 * The default WP theme might be active instead.
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

		// "storefront" definitely not active
		$this->assertStringNotContainsString( 'storefront', $theme_list );
	}

	/**
	 * If --skip_activating_themes is used, that also prevents theme activation.
	 */
	public function test_env_up_with_skip_activating_themes_does_not_activate_theme() {
		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			$this->storefront_local_path,
			'--skip_activating_themes',
		] );
		$json   = json_decode( $output, true );

		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// Storefront installed but not active
		$this->assertStringContainsString( 'storefront', $theme_list );
		$this->assertStringContainsString( 'inactive', $theme_list );
	}

	/**
	 * Single child + single parent => should auto-activate child.
	 * We use local zips for both Deli (child) and Storefront (parent).
	 */
	public function test_env_up_with_child_theme_and_parent_auto_activates_child() {
		$this->assertFileExists( $this->storefront_local_path, 'Storefront zip missing.' );
		$this->assertFileExists( $this->deli_local_path, 'Deli zip missing.' );

		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			$this->storefront_local_path, // parent
			'--theme',
			$this->deli_local_path,       // child
		] );

		$json = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $json, 'No env_id in JSON output (env:up may have failed)' );

		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// Expect "storefront" (parent) installed but inactive
		$this->assertStringContainsString( 'storefront', $theme_list, 'Storefront not installed' );
		$this->assertStringNotContainsString( 'storefront    active', $theme_list, 'Parent should remain inactive' );

		// Deli (child) auto-activated
		$this->assertStringContainsString( 'deli', $theme_list, 'Deli not installed' );
		$this->assertStringContainsString( 'active', $theme_list, 'Expected Deli to be active' );
	}

	/**
	 * Single child theme alone => success if parent is on WP.org,
	 * because QIT auto-installs the parent inside Docker.
	 */
	public function test_env_up_with_child_theme_alone_succeeds_if_parent_on_wporg() {
		// "deli" references "storefront" in style.css. Both are on WP.org.
		$this->assertFileExists( $this->deli_local_path, 'Deli zip missing.' );

		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			$this->deli_local_path,  // only pass the child
		] );

		// If we reach here, the environment was created successfully
		$json = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $json, 'No env_id in output, env:up may have failed unexpectedly.' );

		// Check that child is active and parent is installed
		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// Expect storefront installed (parent) but inactive
		$this->assertStringContainsString( 'storefront', $theme_list, 'Storefront was not installed automatically' );
		$this->assertStringNotContainsString( 'storefront    active', $theme_list, 'Parent theme should not be activated' );

		// Expect deli installed and active
		$this->assertStringContainsString( 'deli', $theme_list );
		$this->assertStringContainsString( 'active', $theme_list, 'Deli child theme should be active' );
	}

	/**
	 * Child theme + unrelated theme => skip auto-activation,
	 * because there's no recognized parent–child match.
	 */
	public function test_env_up_with_child_and_unrelated_theme_skips_activation() {
		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			$this->deli_local_path,
			'--theme',
			'twentytwentyone',
		] );

		$json = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $json );

		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status'
		] );

		// Child theme won't appear at all because it's broken (no parent).
		// We only see 'twentytwentyone' (installed, "inactive" or possibly "broken" if it’s missing something).
		// But at least we know we didn't forcibly activate twentytwentyone.

		$this->assertStringContainsString( 'twentytwentyone', $theme_list, 'Unrelated theme not installed.' );
		$this->assertStringNotContainsString( 'twentytwentyone    active', $theme_list, 'Unrelated theme should remain inactive.' );
	}

	/**
	 * Child + parent, but --skip_activating_themes => skip theme activation
	 */
	public function test_env_up_with_child_theme_and_skip_activating_themes_flag_skips_child_activation() {
		$this->assertFileExists( $this->storefront_local_path, 'Storefront zip missing.' );
		$this->assertFileExists( $this->deli_local_path, 'Deli zip missing.' );

		$output = qit( [
			'env:up',
			'--json',
			'--theme',
			$this->storefront_local_path,
			'--theme',
			$this->deli_local_path,
			'--skip_activating_themes', // Also disables theme auto-activation
		] );

		$json = json_decode( $output, true );
		$this->assertArrayHasKey( 'env_id', $json );

		$theme_list = qit( [
			'env:exec',
			'--env_id',
			$json['env_id'],
			'wp theme list --fields=name,status',
		] );

		// Both installed, but inactive
		$this->assertStringContainsString( 'storefront', $theme_list );
		$this->assertStringContainsString( 'deli', $theme_list );

		$this->assertStringContainsString( 'inactive', $theme_list, 'Expected all themes to remain inactive due to --skip_activating_themes.' );
	}
}
