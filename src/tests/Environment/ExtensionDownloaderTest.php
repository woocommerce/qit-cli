<?php

use QIT_CLI\App;
use QIT_CLI\Environment\ExtensionDownload\Extension;
use QIT_CLI\Environment\ExtensionDownload\ExtensionDownloader;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class FooCustomHandler extends \QIT_CLI\Environment\ExtensionDownload\Handlers\CustomHandler {
	public function should_handle( Extension $extension ): bool {
		return strpos( $extension->extension_identifier, 'foo-custom' ) !== false;
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

	public function test_categorize_extensions() {
		$plugins = [
			'plugin1',
			'plugin2',
		];
		$themes  = [
			'theme1',
			'theme2',
		];

		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_numeric_extensions() {
		$plugins = [ '123', '456' ];
		$themes  = [ '789' ];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_valid_ur_extensions() {
		$plugins = [ 'http://example.com/plugin.zip', 'https://example.com/plugin2.zip' ];
		$themes  = [];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_invalid_url_extensions() {
		$plugins = [ 'http://example.com/plugin', 'https://example.com/plugin2.tar.gz' ];
		$this->expectException( InvalidArgumentException::class );
		$this->sut->categorize_extensions( $plugins, [], '/tmp/cache/' );
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

		$plugins = [ $plugin1, $plugin2 ];
		$themes  = [ $theme1, $theme2 ];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_github_repository_string() {
		$plugins = [ 'user/repository', 'user2/repo2#branch' ];
		$this->expectException( InvalidArgumentException::class );
		$this->sut->categorize_extensions( $plugins, [], '/tmp/cache/' );
	}

	public function test_SSH_url() {
		$plugins = [ 'ssh://example.com/plugin' ];
		$themes  = [ 'ssh://example.com/theme' ];
		$this->expectException( InvalidArgumentException::class );
		$this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' );
	}

	public function test_mixed_valid_and_invalid_extensions() {
		$plugins = [ 'plugin', 'http://validurl.com/plugin.zip', 'invalidurl.com', 'user/repo' ];
		$themes  = [ 'theme', '123', 'ssh://example.com/theme' ];
		$this->expectException( InvalidArgumentException::class );
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_empty_arrays() {
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( [], [], '/tmp/cache/' ) );
	}

	public function test_non_zip_urls_in_mixed_scenarios() {
		$plugins = [ 'http://example.com/plugin', 'https://example.com/plugin.zip' ];
		$this->expectException( \InvalidArgumentException::class );
		$this->sut->categorize_extensions( $plugins, [], '/tmp/cache/' );
	}

	public function test_extensions_with_special_characters() {
		$plugins = [ 'special_plugin@1.0', 'another-plugin#version' ];
		$themes  = [ 'theme with spaces', 'theme_special*chars' ];
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The provided string could not be parsed as any of the valid formats: WP.org/Woo.com Slugs, Woo.com product ID, Local path, or Zip URLs.' );
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_long_extension_names() {
		$plugins = [ 'this-is-a-very-long-plugin-name-that-might-exceed-typical-limits' ];
		$themes  = [ 'this-is-a-very-long-theme-name-that-might-exceed-typical-limits' ];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_duplicate_entries() {
		$plugins = [ 'duplicate-plugin', 'duplicate-plugin' ];
		$themes  = [ 'duplicate-theme', 'duplicate-theme' ];
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Duplicate extension found.' );
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_extensions_with_dot() {
		$plugins = [ 'plugin-v1.2.3', 'plugin-v4.5.6' ];
		$themes  = [ 'theme-v7.8.9' ];
		$this->assertMatchesJsonSnapshot( $this->sut->categorize_extensions( $plugins, $themes, '/tmp/cache/' ) );
	}

	public function test_mixed_extension_types_in_single_array() {
		$plugins = [ '123', 'https://example.com/plugin.zip', '/path/to/plugin', 'user/repository' ];
		$this->expectException( \InvalidArgumentException::class );
		$this->sut->categorize_extensions( $plugins, [], '/tmp/cache/' );
	}

	public function test_custom_handler() {
		$plugins = [ 'foo-custom-1', 'foo-custom-2' ];
		$themes  = [ 'foo-custom-theme-1', 'foo-custom-theme-2' ];
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

	public function provider_slugify() {
		return [
			[ 'Simple Test', 'simple-test' ],
			[ '123 Numbers 456', '123-numbers-456' ],
			[ 'String_with_underscores', 'string_with_underscores' ],
			[ 'String-With-Mixed_Case', 'string-with-mixed_case' ],
			[ 'Áccented Cháracters', 'ccented-chracters' ],
			[ 'Umlaute ÄÖÜ äöü', 'umlaute' ],
			[ 'Español con ñ y acentos', 'espaol-con-y-acentos' ],
			[ 'Caractères français', 'caractres-franais' ],
			[ 'Кириллица', '' ],
			[ '汉字/漢字', '' ],
			[ 'العربية', '' ],
			[ 'Special !@#$%^&*() Characters', 'special-characters' ],
			[ 'Mixed 文字 with English', 'mixed-with-english' ],
			[ "Invalid \xc3\x28 UTF-8", 'invalid-utf-8' ],
		];
	}

	/**
	 * @dataProvider provider_slugify
	 */
	public function test_slugify( $string, $expected ) {
		$expected_failures = [
			'',
			'Кириллица',
			'汉字/漢字',
			'العربية',
		];

		if ( in_array( $string, $expected_failures, true ) ) {
			$this->expectException( InvalidArgumentException::class );
			ExtensionDownloader::slugify( $string );
		} else {
			$this->assertEquals( $expected, ExtensionDownloader::slugify( $string ) );
		}
	}

	/**
	 * @dataProvider provider_invalid_slugs
	 * @dataProvider provider_invalid_slugs_from_file
	 */
	public function test_invalid_becomes_valid( $invalid_slug ) {
		$expected_failures = [
			'',
			'Кириллица',
			'汉字/漢字',
			'العربية',
			'я-делюсь',
			'لينوكس-ويكى',
			'分享图片到新浪微博',
			'印象码',
			'友言',
			'图片签名插件',
			'多说社会化评论框',
			'开心网开放平台插件',
			'微集分插件',
			'新浪微博',
			'无觅相关文章插件',
			'日志保护',
			'海阔淘宝相关宝贝插件',
			'社交登录',
			'腾讯微博一键登录',
			'ЯндексФотки',
			'бутон-за-споделяне',
			'православный-календарь',
		];

		if ( in_array( $invalid_slug, $expected_failures, true ) ) {
			$this->expectException( InvalidArgumentException::class );
			ExtensionDownloader::slugify( $invalid_slug );
		} else {
			$valid_slug = ExtensionDownloader::slugify( $invalid_slug );
			$this->assertTrue( ExtensionDownloader::is_valid_plugin_slug( $valid_slug ), "Invalid slug: $invalid_slug, Valid slug: $valid_slug" );
		}
	}

	public function provider_urls_to_slugify() {
		return [
			[ 'http://example.com/my file.jpg', 'http-example-com-my-file-jpg' ],
			[ 'https://www.example.com/some/path/', 'https-www-example-com-some-path' ],
			[ 'ftp://example.com/some_file.txt', 'ftp-example-com-some-file-txt' ],
			[ 's3://bucket-name/folder/subfolder/image.jpeg', 's3-bucket-name-folder-subfolder-image-jpeg' ],
			[ 'https://bucket.s3.amazonaws.com/folder-name/file name.jpeg', 'https-bucket-s3-amazonaws-com-folder-name-file-name-jpeg' ],
			[ 'https://example.com/?query=param', 'https-example-com-query-param' ],
			[ 'https://bucket.s3.amazonaws.com/folder/file.jpeg?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Expires=300', 'https-bucket-s3-amazonaws-com-folder-file-jpeg-x-amz-algorithm-aws4-hmac-sha256-x-amz-expires-300' ],
			[ 's3://bucket-name/folder/subfolder/file?X-Amz-Date=20210316T000000Z&X-Amz-SignedHeaders=host', 's3-bucket-name-folder-subfolder-file-x-amz-date-20210316t000000z-x-amz-signedheaders-host' ],
			[ 'https://bucket.s3.amazonaws.com/path/to/resource?X-Amz-Credential=example/20210316/us-east-1/s3/aws4_request', 'https-bucket-s3-amazonaws-com-path-to-resource-x-amz-credential-example-20210316-us-east-1-s3-aws4-request' ],
			[ 'https://bucket.s3.region.amazonaws.com/filename?X-Amz-Signature=abcdef123456', 'https-bucket-s3-region-amazonaws-com-filename-x-amz-signature-abcdef123456' ],
		];
	}

	/**
	 * @dataProvider provider_urls_to_slugify
	 */
	public function test_slugify_urls( $url, $expected ) {
		$this->assertEquals( $expected, ExtensionDownloader::slugify( $url ) );
	}
}
