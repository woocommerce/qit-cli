<?php

use QIT_CLI\App;
use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class FooCustomHandler extends \QIT_CLI\Environment\ExtensionDownload\Handlers\CustomHandler {
	public function should_handle( Extension $extension ): bool {
		return strpos( $extension->slug, 'foo-custom' ) !== false;
	}

	public function populate_extension_versions( array $extensions ): void {
		// No-op.
	}

	public function maybe_download_extensions( array $extensions, string $cache_dir ): void {
		// No-op.
	}
}

class ExtensionDownloaderTest extends TestCase {
	use MatchesSnapshots;

	/** @var ExtensionDownloader */
	protected $sut;

	protected $to_delete = [];

	protected function setUp(): void {
		parent::setUp();

		if ( ! file_exists( '/.dockerenv' ) ) {
			$this->markTestSkipped( 'Since this test touches the filesystem, it has to run on Docker for your security.' );
		}

		App::container()->when( ExtensionDownloader::class )
		   ->needs( OutputInterface::class )
		   ->give( NullOutput::class );

		$this->sut = App::make( ExtensionDownloader::class );
	}

	protected function tearDown(): void {
		foreach ( $this->to_delete as $file ) {
			unlink( $file );
		}

		parent::tearDown();
	}

	protected function make_extension( string $type, ?string $slug = null, $source = null ): Extension {
		$extension       = new Extension();
		$extension->type = $type;

		if ( ! is_null( $slug ) ) {
			$extension->slug = $slug;
		}

		if ( ! is_null( $source ) ) {
			$extension->source = $source;
		}

		if ( is_null( $source ) && ! is_null( $slug ) ) {
			$extension->source = $slug;
		}

		if ( empty( $extension->source ) ) {
			throw new \LogicException( 'Source cannot be empty' );
		}

		if ( empty( $extension->slug ) ) {
			// Set a random slug if not provided, because we need one.
			$extension->slug = md5( $extension->source );
		}

		return $extension;
	}

	public function test_categorize_extensions() {
		$plugins = [
			$this->make_extension( 'plugin', 'plugin1' ),
			$this->make_extension( 'plugin', 'plugin2' ),
		];
		$themes  = [
			$this->make_extension( 'theme', 'theme1' ),
			$this->make_extension( 'theme', 'theme2' ),
		];

		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_numeric_extensions() {
		$plugins = [
			$this->make_extension( 'plugin', 'plugin1', '123' ),
			$this->make_extension( 'plugin', 'plugin2', '456' ),
		];
		$themes  = [
			$this->make_extension( 'theme', 'theme1', '789' ),
		];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_valid_ur_extensions() {
		$plugins = [
			$this->make_extension( 'plugin', null, 'http://example.com/plugin.zip' ),
			$this->make_extension( 'plugin', null, 'https://example.com/plugin2.zip' ),
		];
		$themes  = [];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_file_path_extensions() {
		$plugin1 = sys_get_temp_dir() . '/plugin';
		$plugin2 = sys_get_temp_dir() . '/plugin2.zip';
		$theme1  = sys_get_temp_dir() . '/theme';
		$theme2  = sys_get_temp_dir() . '/theme2.zip';
		touch( $plugin1 );
		touch( $plugin2 );
		touch( $theme1 );
		touch( $theme2 );
		$this->to_delete = [ $plugin1, $plugin2, $theme1, $theme2 ];

		$plugins = [
			$this->make_extension( 'plugin', null, $plugin1 ),
			$this->make_extension( 'plugin', null, $plugin2 ),
		];
		$themes  = [
			$this->make_extension( 'theme', null, $theme1 ),
			$this->make_extension( 'theme', null, $theme2 ),
		];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_github_repository_string() {
		$plugins = [
			$this->make_extension( 'plugin', null, 'user/repository' ),
			$this->make_extension( 'plugin', null, 'user2/repo2#branch' ),
		];
		$this->expectException( InvalidArgumentException::class );
		$this->sut->categorize_extensions( $plugins, [], '/tmp/cache/' );
	}

	public function test_SSH_url() {
		$plugins = [
			$this->make_extension( 'plugin', null, 'ssh://example.com/plugin' ),
		];
		$themes  = [
			$this->make_extension( 'theme', null, 'ssh://example.com/theme' ),
		];
		$this->expectException( InvalidArgumentException::class );
		$this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' );
	}

	public function test_mixed_valid_and_invalid_extensions() {
		$plugins = [
			$this->make_extension( 'plugin', null, 'plugin' ),
			$this->make_extension( 'plugin', null, 'http://validurl.com/plugin.zip' ),
			$this->make_extension( 'plugin', null, 'invalidurl.com' ),
			$this->make_extension( 'plugin', null, 'user/repo' ),
		];
		$themes  = [
			$this->make_extension( 'theme', null, 'theme' ),
			$this->make_extension( 'theme', null, '123' ),
			$this->make_extension( 'theme', null, 'ssh://example.com/theme' ),
		];
		$this->expectException( InvalidArgumentException::class );
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_empty_arrays() {
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( [], [], '/tmp/cache/' ) );
	}

	public function test_extensions_with_special_characters() {
		$plugins = [
			$this->make_extension( 'plugin', null, 'special_plugin@1.0' ),
			$this->make_extension( 'plugin', null, 'another-plugin#version' ),
		];
		$themes  = [
			$this->make_extension( 'theme', null, 'theme with spaces' ),
			$this->make_extension( 'theme', null, 'theme_special*chars' ),
		];
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Could not find extension' );
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_long_extension_names() {
		$plugins = [
			$this->make_extension( 'plugin', 'this-is-a-very-long-plugin-name-that-might-exceed-typical-limits' ),
		];
		$themes  = [
			$this->make_extension( 'theme', 'this-is-a-very-long-theme-name-that-might-exceed-typical-limits' ),
		];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_duplicate_entries() {
		$plugins = [
			$this->make_extension( 'plugin', 'duplicate-plugin' ),
			$this->make_extension( 'plugin', 'duplicate-plugin' ),
		];
		$themes  = [
			$this->make_extension( 'theme', 'duplicate-theme' ),
			$this->make_extension( 'theme', 'duplicate-theme' ),
		];
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Duplicate extension found: duplicate-plugin' );
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_extensions_with_dot() {
		$plugins = [
			$this->make_extension( 'plugin', 'plugin-v1.2.3' ),
			$this->make_extension( 'plugin', 'plugin-v4.5.6' ),
		];
		$themes  = [
			$this->make_extension( 'theme', 'theme-v7.8.9' ),
		];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_mixed_extension_types_in_single_array() {
		$plugins = [
			$this->make_extension( 'plugin', null, '123' ),
			$this->make_extension( 'plugin', null, 'https://example.com/plugin.zip' ),
			$this->make_extension( 'plugin', null, '/path/to/plugin' ),
			$this->make_extension( 'plugin', null, 'user/repository' ),
		];
		$this->expectException( \InvalidArgumentException::class );
		$this->sut->categorize_extensions( $plugins, [], '/tmp/cache/' );
	}

	public function test_custom_handler() {
		$plugins = [
			$this->make_extension( 'plugin', 'foo-custom-1' ),
			$this->make_extension( 'plugin', 'foo-custom-2' ),
		];
		$themes  = [
			$this->make_extension( 'theme', 'foo-custom-theme-1' ),
			$this->make_extension( 'theme', 'foo-custom-theme-2' ),
		];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function provider_valid_slugs() {
		return [
			[ 'simple-slug' ],
			[ 'version-1.2.3' ],
			[ 'plugin-name123' ],
			[ '123-plugin-name' ],
			[ 'slug-with-multiple-parts' ],
			[ 'another_slug-9' ],
		];
	}

	/**
	 * @dataProvider provider_valid_slugs
	 */
	public function test_validate_valid_slugs( $slug ) {
		$this->assertTrue( ExtensionDownloader::is_valid_plugin_slug( $slug ) );
	}

	public function provider_invalid_slugs() {
		return [
			[ 'InvalidSlug' ],     // Uppercase letters are not allowed
			[ 'invalid slug' ],    // Spaces are not allowed
			[ '-invalid-slug' ],   // Cannot start with a hyphen
			[ 'invalid-slug-' ],   // Cannot end with a hyphen
			[ 'invalid--slug' ],   // Consecutive hyphens are not allowed
			[ '.invalidslug' ],    // Cannot start with a dot
			[ 'invalidslug.' ],    // Cannot end with a dot
			[ 'invalid..slug' ],   // Consecutive dots are not allowed
			[ '' ],                // Empty string
			[ '!nv@l!d' ],         // Special characters are not allowed
			[ '/directory/traversal' ], // Directory traversal characters are not allowed
			[ '../parent-directory' ],  // Directory traversal characters are not allowed
		];
	}

	/**
	 * @dataProvider provider_invalid_slugs
	 */
	public function test_validate_invalid_slugs( $slug ) {
		$this->assertFalse( ExtensionDownloader::is_valid_plugin_slug( $slug ) );
	}

	/**
	 *  This provider was generated with all the 80k plugin slugs from WordPress.org that failed our validation.
	 *  We pre-computed this list in a separate, uncommitted test,  and we are using it's results to test our validation logic.
	 */
	public function provider_invalid_slugs_from_file() {
		$filePath = __DIR__ . '/../data/invalid-slugs.txt';

		if ( ! file_exists( $filePath ) ) {
			throw new Exception( "File not found: $filePath" );
		}

		$slugs = file( $filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		if ( $slugs === false ) {
			throw new Exception( "Failed to read file: $filePath" );
		}

		return array_map( function ( $slug ) {
			return [ $slug ]; // Each item should be an array of arguments for the test method
		}, $slugs );
	}

	/**
	 * @dataProvider provider_invalid_slugs_from_file
	 */
	public function test_invalid_slugs_from_file( $slug ) {
		$this->assertFalse( ExtensionDownloader::is_valid_plugin_slug( $slug ) );
	}
}