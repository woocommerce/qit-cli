<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Config;
use QIT_CLI\PreCommand\Extensions\ExtensionCacheManager;
use QIT_CLI\PreCommand\Objects\Extension;

class ExtensionCacheManagerTest extends QITTestCase {
	/** @var string[] */
	private $tmp_files = [];

	/** @var string[] */
	private $tmp_dirs = [];

	public function tearDown(): void {
		foreach ( $this->tmp_files as $f ) {
			if ( file_exists( $f ) ) {
				unlink( $f );
			}
		}
		foreach ( $this->tmp_dirs as $dir ) {
			$files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ), \RecursiveIteratorIterator::CHILD_FIRST );
			foreach ( $files as $file ) {
				$file->isDir() ? rmdir( $file->getPathname() ) : unlink( $file->getPathname() );
			}
			rmdir( $dir );
		}
		parent::tearDown();
	}

	/**
	 * A local extension directory holding one header file, as `--source <dir>` passes it.
	 */
	private function write_local_directory( string $slug, string $file, string $header ): string {
		$root = sys_get_temp_dir() . '/qit-local-version-' . uniqid();
		mkdir( "$root/$slug", 0777, true );
		file_put_contents( "$root/$slug/$file", $header );
		$this->tmp_dirs[] = $root;

		return "$root/$slug";
	}

	private function write_zip( string $path, string $entry, string $content ): void {
		$zip = new \ZipArchive();
		$this->assertTrue( $zip->open( $path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) === true );
		$zip->addFromString( $entry, $content );
		$zip->close();
		$this->tmp_files[] = $path;
	}

	private function local_extension( string $slug, string $type, string $source ): Extension {
		$ext       = new Extension( $slug, $type, $source );
		$ext->from = 'local';
		if ( is_dir( $source ) ) {
			$ext->directory = $source;
		}

		return $ext;
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

	public function test_cache_hit_found_by_is_cached_still_detects_entrypoint(): void {
		$cache_manager = App::make( ExtensionCacheManager::class );
		$cache_dir     = Config::get_qit_dir() . 'cache';
		$slug          = 'my-awesome-plugin';

		$ext       = new Extension( $slug, 'plugin', "https://example.com/$slug.zip" );
		$ext->from = 'url';

		// A zip left in the cache by an earlier run within the same cache window.
		$make_cache_path = new \ReflectionMethod( ExtensionCacheManager::class, 'make_cache_path' );
		$make_cache_path->setAccessible( true );
		$this->write_plugin_zip( $make_cache_path->invoke( $cache_manager, $ext, $cache_dir ), $slug, '1.0.0' );

		$this->assertTrue( $cache_manager->is_cached( $ext, $cache_dir ) );
		$cache_manager->ensure_cached( $ext, $cache_dir );

		$this->assertSame( "$slug/$slug.php", $ext->entrypoint );
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

	public function test_local_plugin_directory_reports_its_header_version(): void {
		$slug = 'my-local-plugin';
		$dir  = $this->write_local_directory( $slug, "$slug.php", "<?php\n/**\n * Plugin Name: My Local Plugin\n * Version: 1.2.3\n */" );
		$ext  = $this->local_extension( $slug, 'plugin', $dir );

		App::make( ExtensionCacheManager::class )->ensure_cached( $ext, Config::get_qit_dir() . 'cache' );

		$this->assertSame( '1.2.3', $ext->version );
	}

	public function test_local_plugin_with_a_non_standard_main_file_reports_its_header_version(): void {
		$slug = 'my-local-plugin';
		$dir  = $this->write_local_directory( $slug, 'loader.php', "<?php\n/**\n * Plugin Name: My Local Plugin\n * Version: 4.5.6\n */" );
		file_put_contents( "$dir/helpers.php", "<?php\n/**\n * Version: 9.9.9\n */" );
		$ext = $this->local_extension( $slug, 'plugin', $dir );

		App::make( ExtensionCacheManager::class )->ensure_cached( $ext, Config::get_qit_dir() . 'cache' );

		$this->assertSame( "$slug/loader.php", $ext->entrypoint );
		$this->assertSame( '4.5.6', $ext->version );
	}

	public function test_local_plugin_header_past_the_first_8_kb_is_ignored(): void {
		$slug = 'my-local-plugin';
		$dir  = $this->write_local_directory( $slug, "$slug.php", "<?php\n/**\n * Plugin Name: My Local Plugin\n" . str_repeat( " * filler\n", 1000 ) . " * Version: 1.2.3\n */" );
		$ext  = $this->local_extension( $slug, 'plugin', $dir );

		App::make( ExtensionCacheManager::class )->ensure_cached( $ext, Config::get_qit_dir() . 'cache' );

		$this->assertSame( 'undefined', $ext->version );
	}

	public function test_already_detected_local_plugin_still_reports_its_header_version(): void {
		$slug                   = 'my-local-plugin';
		$dir                    = $this->write_local_directory( $slug, "$slug.php", "<?php\n/**\n * Plugin Name: My Local Plugin\n * Version: 1.2.3\n */" );
		$ext                    = $this->local_extension( $slug, 'plugin', $dir );
		$ext->downloaded_source = $dir;
		$ext->entrypoint        = "$slug/$slug.php";

		App::make( ExtensionCacheManager::class )->ensure_cached( $ext, Config::get_qit_dir() . 'cache' );

		$this->assertSame( '1.2.3', $ext->version );
	}

	public function test_local_plugin_zip_reports_its_header_version(): void {
		$slug   = 'my-local-plugin';
		$source = sys_get_temp_dir() . "/qit-local-version-$slug.zip";
		$this->write_plugin_zip( $source, $slug, '2.3.4' );
		$ext = $this->local_extension( $slug, 'plugin', $source );

		App::make( ExtensionCacheManager::class )->ensure_cached( $ext, Config::get_qit_dir() . 'cache' );

		$this->assertSame( '2.3.4', $ext->version );
	}

	public function test_local_theme_directory_and_zip_report_their_style_version(): void {
		$slug  = 'my-local-theme';
		$style = "/*\nTheme Name: My Local Theme\nVersion: 3.1.0\n*/";
		$dir   = $this->write_local_directory( $slug, 'style.css', $style );
		$zip   = sys_get_temp_dir() . "/qit-local-version-$slug.zip";
		$this->write_zip( $zip, "$slug/style.css", $style );

		foreach ( [ $dir, $zip ] as $source ) {
			$ext = $this->local_extension( $slug, 'theme', $source );
			App::make( ExtensionCacheManager::class )->ensure_cached( $ext, Config::get_qit_dir() . 'cache' );

			$this->assertSame( '3.1.0', $ext->version, $source );
		}
	}

	public function test_local_zip_overwritten_in_place_reports_the_new_version(): void {
		$cache_manager = App::make( ExtensionCacheManager::class );
		$slug          = 'my-local-plugin';
		$source        = sys_get_temp_dir() . "/qit-local-version-overwrite-$slug.zip";

		$this->write_plugin_zip( $source, $slug, '1.0.0' );
		$first = $this->local_extension( $slug, 'plugin', $source );
		$cache_manager->ensure_cached( $first, Config::get_qit_dir() . 'cache' );

		$this->write_plugin_zip( $source, $slug, '1.1.0' );
		touch( $source, time() + 5 );
		clearstatcache();
		$second = $this->local_extension( $slug, 'plugin', $source );
		$cache_manager->ensure_cached( $second, Config::get_qit_dir() . 'cache' );

		$this->assertSame( '1.0.0', $first->version );
		$this->assertSame( '1.1.0', $second->version );
		$this->assertSame( $first->downloaded_source, $second->downloaded_source, 'The version must not move the cache path.' );
	}

	/**
	 * @dataProvider header_versions
	 */
	public function test_local_plugin_header_version_is_cleaned_like_wordpress( string $header_line, string $expected ): void {
		$slug = 'my-local-plugin';
		$dir  = $this->write_local_directory( $slug, "$slug.php", "<?php\n/**\n * Plugin Name: My Local Plugin\n$header_line\n */" );
		$ext  = $this->local_extension( $slug, 'plugin', $dir );

		App::make( ExtensionCacheManager::class )->ensure_cached( $ext, Config::get_qit_dir() . 'cache' );

		$this->assertSame( $expected, $ext->version );
	}

	/**
	 * @return array<string,array{string,string}>
	 */
	public function header_versions(): array {
		return [
			'no header'         => [ ' * Description: no version here', 'undefined' ],
			'blank header'      => [ ' * Version:   ', 'undefined' ],
			'closing comment'   => [ ' * Version: 2.0.0 */', '2.0.0' ],
			'extra spaces'      => [ ' *   Version:    5.0.1   ', '5.0.1' ],
			'not at line start' => [ ' * Requires PHP Version: 7.4', 'undefined' ],
			'closing php tag'   => [ ' * Version: 1.0.0 ?>', '1.0.0' ],
			'carriage returns'  => [ "\r * Version: 1.0.2\r", '1.0.2' ],
		];
	}

	public function test_local_plugin_keeps_a_version_that_was_already_set(): void {
		$slug         = 'my-local-plugin';
		$dir          = $this->write_local_directory( $slug, "$slug.php", "<?php\n/**\n * Plugin Name: My Local Plugin\n * Version: 1.2.3\n */" );
		$ext          = $this->local_extension( $slug, 'plugin', $dir );
		$ext->version = '9.0.0';

		App::make( ExtensionCacheManager::class )->ensure_cached( $ext, Config::get_qit_dir() . 'cache' );

		$this->assertSame( '9.0.0', $ext->version );
	}

	public function test_url_source_does_not_take_its_header_version(): void {
		$cache_manager   = App::make( ExtensionCacheManager::class );
		$cache_dir       = Config::get_qit_dir() . 'cache';
		$slug            = 'my-url-plugin';
		$make_cache_path = new \ReflectionMethod( ExtensionCacheManager::class, 'make_cache_path' );
		$make_cache_path->setAccessible( true );

		foreach ( [ 'undefined', '1.0.0' ] as $version ) {
			$ext          = new Extension( $slug, 'plugin', "https://example.com/$slug.zip" );
			$ext->from    = 'url';
			$ext->version = $version;
			$cache_file   = $make_cache_path->invoke( $cache_manager, $ext, $cache_dir );
			$this->write_plugin_zip( $cache_file, $slug, '9.9.9' );

			$this->assertTrue( $cache_manager->is_cached( $ext, $cache_dir ) );
			$cache_manager->ensure_cached( $ext, $cache_dir );

			$this->assertSame( $version, $ext->version );
			$this->assertSame( $cache_file, $ext->downloaded_source );
		}
	}
}
