<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\PreCommand\Extensions\ExtensionCacheManager;
use QIT_CLI\PreCommand\Objects\Extension;

class ExtensionCacheManagerTest extends QITTestCase {
	/** @var string[] */
	private $tmp_files = [];

	public function tearDown(): void {
		foreach ( $this->tmp_files as $f ) {
			if ( file_exists( $f ) ) {
				unlink( $f );
			}
		}
		parent::tearDown();
	}

	private function write_plugin_zip( string $path, string $slug, string $version ): void {
		file_put_contents( $path, $this->createMinimalPluginZip( $slug, $version ) );
		$this->tmp_files[] = $path;
	}

	private function cached_plugin_version( string $zip_path, string $slug ): string {
		$zip = new \ZipArchive();
		$this->assertTrue( $zip->open( $zip_path ) === true, "Could not open cached zip: $zip_path" );
		$content = $zip->getFromName( "$slug/$slug.php" );
		$zip->close();

		return (string) $content;
	}

	private function validate_cache( ExtensionCacheManager $cache_manager, string $zip_path, Extension $extension ): bool {
		$ref = new \ReflectionMethod( ExtensionCacheManager::class, 'validate_cache' );
		$ref->setAccessible( true );

		return (bool) $ref->invoke( $cache_manager, $zip_path, $extension );
	}

	public function test_woocommerce_dev_cache_expires_after_five_minutes(): void {
		$cache_manager = App::make( ExtensionCacheManager::class );
		$slug          = 'woocommerce';
		$cache_file    = sys_get_temp_dir() . "/qit-cache-test-dev-$slug.zip";

		$this->write_plugin_zip( $cache_file, $slug, '11.0.0-dev' );
		touch( $cache_file, time() - 6 * MINUTE_IN_SECONDS );
		clearstatcache( true, $cache_file );

		$ext          = new Extension( $slug, 'plugin', 'https://github.com/woocommerce/woocommerce/releases/download/11.0.0-dev/woocommerce.zip' );
		$ext->from    = 'url';
		$ext->version = '11.0.0-dev';

		$this->assertFalse(
			$this->validate_cache( $cache_manager, $cache_file, $ext ),
			'WooCommerce dev builds should refresh after the short cache window.'
		);
	}

	public function test_regular_version_cache_survives_five_minutes(): void {
		$cache_manager = App::make( ExtensionCacheManager::class );
		$slug          = 'woocommerce';
		$cache_file    = sys_get_temp_dir() . "/qit-cache-test-stable-$slug.zip";

		$this->write_plugin_zip( $cache_file, $slug, '9.5.0' );
		touch( $cache_file, time() - 6 * MINUTE_IN_SECONDS );
		clearstatcache( true, $cache_file );

		$ext          = new Extension( $slug, 'plugin', 'https://downloads.wordpress.org/plugin/woocommerce.9.5.0.zip' );
		$ext->from    = 'wporg';
		$ext->version = '9.5.0';

		$this->assertTrue(
			$this->validate_cache( $cache_manager, $cache_file, $ext ),
			'Regular released versions should keep the longer cache window.'
		);
	}

	/**
	 * Rebuilding a local zip in place (same path) must invalidate the path-based
	 * cache. Previously the copy was skipped whenever a cache file already existed,
	 * so an in-place rebuild was tested against the stale cached copy.
	 */
	public function test_overwriting_local_zip_in_place_refreshes_cache(): void {
		$cache_manager = App::make( ExtensionCacheManager::class );
		$cache_dir     = Config::get_qit_dir() . 'cache';

		$slug   = 'my-awesome-plugin';
		$source = sys_get_temp_dir() . "/qit-cache-test-$slug.zip";

		// First build.
		$this->write_plugin_zip( $source, $slug, '1.0.0' );

		$ext       = new Extension( $slug, 'plugin', $source );
		$ext->from = 'local';
		$cache_manager->ensure_cached( $ext, $cache_dir );

		$cache_file = $ext->downloaded_source;
		$this->assertNotEmpty( $cache_file );
		$this->assertStringContainsString( 'Version: 1.0.0', $this->cached_plugin_version( $cache_file, $slug ) );

		// Rebuild in place: same path, new contents, newer mtime.
		$this->write_plugin_zip( $source, $slug, '2.0.0' );
		touch( $source, time() + 5 );
		clearstatcache();

		// Fresh extension object, same path — simulates a new CLI invocation.
		$ext2       = new Extension( $slug, 'plugin', $source );
		$ext2->from = 'local';
		$cache_manager->ensure_cached( $ext2, $cache_dir );

		$this->assertSame( $cache_file, $ext2->downloaded_source, 'Same source path should resolve to the same cache key.' );
		$this->assertStringContainsString(
			'Version: 2.0.0',
			$this->cached_plugin_version( $ext2->downloaded_source, $slug ),
			'Cache should be refreshed after the source zip is overwritten in place.'
		);
	}

	/**
	 * An unchanged source zip should keep using the cached copy (no needless re-copy).
	 */
	public function test_unchanged_local_zip_reuses_cache(): void {
		$cache_manager = App::make( ExtensionCacheManager::class );
		$cache_dir     = Config::get_qit_dir() . 'cache';

		$slug   = 'my-awesome-plugin';
		$source = sys_get_temp_dir() . "/qit-cache-test-unchanged-$slug.zip";

		$this->write_plugin_zip( $source, $slug, '1.0.0' );

		$ext       = new Extension( $slug, 'plugin', $source );
		$ext->from = 'local';
		$cache_manager->ensure_cached( $ext, $cache_dir );

		$cache_file  = $ext->downloaded_source;
		$cached_mtime = filemtime( $cache_file );

		// Make the cache file demonstrably newer than the (untouched) source, then
		// resolve again. The freshness check should leave the cache file alone.
		touch( $cache_file, time() + 5 );
		clearstatcache();
		$expected_mtime = filemtime( $cache_file );

		$ext2       = new Extension( $slug, 'plugin', $source );
		$ext2->from = 'local';
		$cache_manager->ensure_cached( $ext2, $cache_dir );

		clearstatcache();
		$this->assertSame( $cache_file, $ext2->downloaded_source );
		$this->assertSame( $expected_mtime, filemtime( $ext2->downloaded_source ), 'Unchanged source should not trigger a re-copy.' );
	}
}
