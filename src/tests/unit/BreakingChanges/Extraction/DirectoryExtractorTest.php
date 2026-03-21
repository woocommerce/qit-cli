<?php

namespace QIT_CLI_Tests\BreakingChanges\Extraction;

use PHPUnit\Framework\TestCase;
use QIT_CLI\BreakingChanges\Extraction\DirectoryExtractor;
use QIT_CLI\BreakingChanges\Extraction\FileParser;

class DirectoryExtractorTest extends TestCase {
	private DirectoryExtractor $extractor;

	protected function setUp(): void {
		parent::setUp();
		$this->extractor = new DirectoryExtractor( new FileParser() );
	}

	public function test_extracts_symbols_from_v1_plugin(): void {
		$dir     = __DIR__ . '/../fixtures/sample-plugin-v1';
		$symbols = $this->extractor->extract( $dir );

		// Classes.
		$this->assertArrayHasKey( 'SamplePlugin\SampleManager', $symbols->classes );
		$this->assertArrayHasKey( 'SamplePlugin\SampleHelper', $symbols->classes );
		$this->assertArrayHasKey( 'SamplePlugin\SampleContract', $symbols->classes );

		// Public methods.
		$this->assertArrayHasKey( 'SamplePlugin\SampleManager::initialize', $symbols->methods );
		$this->assertArrayHasKey( 'SamplePlugin\SampleManager::get_items', $symbols->methods );
		$this->assertArrayHasKey( 'SamplePlugin\SampleManager::process_item', $symbols->methods );
		$this->assertArrayHasKey( 'SamplePlugin\SampleHelper::format_output', $symbols->methods );
		$this->assertArrayHasKey( 'SamplePlugin\SampleHelper::deprecated_method', $symbols->methods );

		// Protected/private methods should NOT be present.
		$this->assertArrayNotHasKey( 'SamplePlugin\SampleManager::internal_helper', $symbols->methods );
		$this->assertArrayNotHasKey( 'SamplePlugin\SampleManager::private_method', $symbols->methods );

		// Functions.
		$this->assertArrayHasKey( 'SamplePlugin\sample_plugin_get_version', $symbols->functions );
		$this->assertArrayHasKey( 'SamplePlugin\sample_plugin_deprecated_function', $symbols->functions );
		$this->assertArrayHasKey( 'SamplePlugin\sample_plugin_helper', $symbols->functions );

		// Constants.
		$this->assertArrayHasKey( 'SAMPLE_PLUGIN_VERSION', $symbols->constants );
		$this->assertArrayHasKey( 'SAMPLE_PLUGIN_DIR', $symbols->constants );
		$this->assertArrayHasKey( 'SAMPLE_PLUGIN_SLUG', $symbols->constants );

		// Hooks.
		$this->assertArrayHasKey( 'sample_plugin_init', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_items', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_before_process', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_after_process', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_format_output', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_helper_result', $symbols->hooks );
	}

	public function test_extracts_symbols_from_v2_plugin(): void {
		$dir     = __DIR__ . '/../fixtures/sample-plugin-v2';
		$symbols = $this->extractor->extract( $dir );

		// New class in v2.
		$this->assertArrayHasKey( 'SamplePlugin\SampleRegistry', $symbols->classes );

		// Removed method should not be in v2.
		$this->assertArrayNotHasKey( 'SamplePlugin\SampleHelper::deprecated_method', $symbols->methods );

		// New method in v2.
		$this->assertArrayHasKey( 'SamplePlugin\SampleHelper::sanitize_output', $symbols->methods );

		// Removed function should not be in v2.
		$this->assertArrayNotHasKey( 'SamplePlugin\sample_plugin_deprecated_function', $symbols->functions );

		// New function in v2.
		$this->assertArrayHasKey( 'SamplePlugin\sample_plugin_new_utility', $symbols->functions );

		// Removed constant should not be in v2.
		$this->assertArrayNotHasKey( 'SAMPLE_PLUGIN_DIR', $symbols->constants );

		// New constant in v2.
		$this->assertArrayHasKey( 'SAMPLE_PLUGIN_MIN_PHP', $symbols->constants );

		// Removed hooks should not be in v2.
		$this->assertArrayNotHasKey( 'sample_plugin_init', $symbols->hooks );
		$this->assertArrayNotHasKey( 'sample_plugin_before_process', $symbols->hooks );
		$this->assertArrayNotHasKey( 'sample_plugin_after_process', $symbols->hooks );

		// New hooks in v2.
		$this->assertArrayHasKey( 'sample_plugin_initialized', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_before_batch', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_after_batch', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_sanitize_output', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_registered', $symbols->hooks );
		$this->assertArrayHasKey( 'sample_plugin_registry_get', $symbols->hooks );
	}

	public function test_returns_empty_for_nonexistent_directory(): void {
		$symbols = $this->extractor->extract( '/nonexistent/dir' );

		$this->assertEmpty( $symbols->classes );
		$this->assertEmpty( $symbols->methods );
		$this->assertEmpty( $symbols->functions );
		$this->assertEmpty( $symbols->constants );
		$this->assertEmpty( $symbols->hooks );
	}

	public function test_no_warnings_on_valid_fixtures(): void {
		$dir     = __DIR__ . '/../fixtures/sample-plugin-v1';
		$symbols = $this->extractor->extract( $dir );

		$this->assertEmpty( $symbols->warnings );
	}

	public function test_skips_vendor_directory(): void {
		$tmp = sys_get_temp_dir() . '/extractor-test-' . uniqid();
		mkdir( $tmp );
		mkdir( $tmp . '/vendor', 0777, true );

		file_put_contents( $tmp . '/main.php', '<?php class Main {}' );
		file_put_contents( $tmp . '/vendor/dep.php', '<?php class VendorDep {}' );

		try {
			$symbols = $this->extractor->extract( $tmp );

			$this->assertArrayHasKey( 'Main', $symbols->classes );
			$this->assertArrayNotHasKey( 'VendorDep', $symbols->classes );
		} finally {
			unlink( $tmp . '/main.php' );
			unlink( $tmp . '/vendor/dep.php' );
			rmdir( $tmp . '/vendor' );
			rmdir( $tmp );
		}
	}

	public function test_relative_paths_in_symbols(): void {
		$dir     = __DIR__ . '/../fixtures/sample-plugin-v1';
		$symbols = $this->extractor->extract( $dir );

		$class = $symbols->classes['SamplePlugin\SampleManager'];
		$this->assertStringNotContainsString( $dir, $class->file );
		$this->assertStringContainsString( 'class-sample-manager.php', $class->file );
	}
}
