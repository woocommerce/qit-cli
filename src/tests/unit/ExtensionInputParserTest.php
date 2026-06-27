<?php

namespace QIT_CLI_Tests;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Extensions\ExtensionInputParser;

/**
 * Unit tests for ExtensionInputParser.
 *
 * Tests the CLI --plugin/--theme string parsing grammar:
 *   slug              → bare slug, resolver decides source/version
 *   slug:version      → pin version, resolver decides source
 *   slug@/path        → explicit slug + local source
 *   slug@https://url  → explicit slug + URL source
 *   /path/to/thing    → local source, slug inferred
 *   https://url/thing → URL source, slug inferred
 *
 * Only one special character (: or @) per value.
 * For full flexibility, use qit.json object form.
 */
class ExtensionInputParserTest extends TestCase {

	/**
	 * Bare slug inputs.
	 */
	public function test_bare_slug(): void {
		$ext = ExtensionInputParser::parse( 'woocommerce', 'plugin' );

		$this->assertSame( 'woocommerce', $ext->slug );
		$this->assertSame( 'plugin', $ext->type );
		$this->assertSame( 'stable', $ext->version );
		$this->assertSame( 'stable', $ext->requested_version );
		$this->assertNull( $ext->from, 'Bare slug should not set from — let resolver decide' );
	}

	public function test_bare_slug_theme(): void {
		$ext = ExtensionInputParser::parse( 'storefront', 'theme' );

		$this->assertSame( 'storefront', $ext->slug );
		$this->assertSame( 'theme', $ext->type );
		$this->assertSame( 'stable', $ext->version );
	}

	public function test_bare_slug_with_hyphens_and_underscores(): void {
		$ext = ExtensionInputParser::parse( 'my-cool_plugin', 'plugin' );

		$this->assertSame( 'my-cool_plugin', $ext->slug );
	}

	/**
	 * Slug:version inputs.
	 */
	public function test_slug_version_semver(): void {
		$ext = ExtensionInputParser::parse( 'woocommerce:8.8.0', 'plugin' );

		$this->assertSame( 'woocommerce', $ext->slug );
		$this->assertSame( '8.8.0', $ext->version );
		$this->assertSame( '8.8.0', $ext->requested_version );
		$this->assertNull( $ext->from, 'slug:version should not set from — let resolver decide' );
	}

	public function test_slug_version_stable(): void {
		$ext = ExtensionInputParser::parse( 'jetpack:stable', 'plugin' );

		$this->assertSame( 'jetpack', $ext->slug );
		$this->assertSame( 'stable', $ext->version );
		$this->assertSame( 'stable', $ext->requested_version );
	}

	public function test_slug_version_rc(): void {
		$ext = ExtensionInputParser::parse( 'woocommerce:rc', 'plugin' );

		$this->assertSame( 'woocommerce', $ext->slug );
		$this->assertSame( 'rc', $ext->version );
		$this->assertSame( 'rc', $ext->requested_version );
	}

	public function test_slug_version_nightly(): void {
		$ext = ExtensionInputParser::parse( 'woocommerce:nightly', 'plugin' );

		$this->assertSame( 'woocommerce', $ext->slug );
		$this->assertSame( 'nightly', $ext->version );
	}

	public function test_slug_version_two_part(): void {
		$ext = ExtensionInputParser::parse( 'gutenberg:19.1', 'plugin' );

		$this->assertSame( 'gutenberg', $ext->slug );
		$this->assertSame( '19.1', $ext->version );
	}

	public function test_slug_version_theme(): void {
		$ext = ExtensionInputParser::parse( 'storefront:4.5.0', 'theme' );

		$this->assertSame( 'storefront', $ext->slug );
		$this->assertSame( 'theme', $ext->type );
		$this->assertSame( '4.5.0', $ext->version );
	}

	/**
	 * Slug@path inputs with explicit local sources.
	 */
	public function test_slug_at_local_directory(): void {
		$dir = sys_get_temp_dir();
		$ext = ExtensionInputParser::parse( "my-plugin@{$dir}", 'plugin' );

		$this->assertSame( 'my-plugin', $ext->slug );
		$this->assertSame( 'local', $ext->from );
		$this->assertSame( realpath( $dir ), $ext->source );
	}

	public function test_slug_at_local_zip(): void {
		// Create a temp zip to test against
		$zip_path = tempnam( sys_get_temp_dir(), 'qit_test_' ) . '.zip';
		file_put_contents( $zip_path, 'fake zip' );

		try {
			$ext = ExtensionInputParser::parse( "my-plugin@{$zip_path}", 'plugin' );

			$this->assertSame( 'my-plugin', $ext->slug );
			$this->assertSame( 'local', $ext->from );
		} finally {
			@unlink( $zip_path );
		}
	}

	/**
	 * Slug@url inputs with explicit remote sources.
	 */
	public function test_slug_at_url(): void {
		$ext = ExtensionInputParser::parse( 'my-plugin@https://example.com/builds/latest.zip', 'plugin' );

		$this->assertSame( 'my-plugin', $ext->slug );
		$this->assertSame( 'url', $ext->from );
		$this->assertSame( 'https://example.com/builds/latest.zip', $ext->source );
	}

	/**
	 * Local paths with inferred slugs.
	 */
	public function test_local_directory_path(): void {
		$dir = sys_get_temp_dir();
		$ext = ExtensionInputParser::parse( $dir, 'plugin' );

		$this->assertSame( 'local', $ext->from );
		$this->assertNotNull( $ext->slug );
	}

	/**
	 * URL sources with inferred slugs.
	 */
	public function test_url_source(): void {
		$ext = ExtensionInputParser::parse( 'https://example.com/downloads/jetpack.zip', 'plugin' );

		$this->assertSame( 'url', $ext->from );
		$this->assertSame( 'jetpack', $ext->slug );
		$this->assertSame( 'https://example.com/downloads/jetpack.zip', $ext->source );
	}

	public function test_url_with_version_in_filename(): void {
		$ext = ExtensionInputParser::parse( 'https://example.com/jetpack.12.5.zip', 'plugin' );

		$this->assertSame( 'jetpack', $ext->slug, 'Version suffix should be stripped from inferred slug' );
	}

	/**
	 * @ and : cannot combine.
	 */
	public function test_at_takes_precedence_over_colon(): void {
		// "my-plugin@https://example.com/thing:8080/file.zip"
		// @ splits first: slug=my-plugin, rest=https://example.com/thing:8080/file.zip
		// The colon in the URL is part of the URL, not a version separator
		$ext = ExtensionInputParser::parse( 'my-plugin@https://example.com:8080/file.zip', 'plugin' );

		$this->assertSame( 'my-plugin', $ext->slug );
		$this->assertSame( 'url', $ext->from );
		$this->assertSame( 'https://example.com:8080/file.zip', $ext->source );
	}

	public function test_mixing_colon_and_at_throws(): void {
		// "woo:8.0@/tmp" mixes both special characters — ambiguous input, fail fast.
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'cannot use both' );
		ExtensionInputParser::parse( 'woo:8.0@/tmp/some-path', 'plugin' );
	}

	/**
	 * Invalid inputs.
	 */
	public function test_empty_string_throws(): void {
		$this->expectException( \InvalidArgumentException::class );
		ExtensionInputParser::parse( '', 'plugin' );
	}

	public function test_whitespace_only_throws(): void {
		$this->expectException( \InvalidArgumentException::class );
		ExtensionInputParser::parse( '   ', 'plugin' );
	}

	public function test_slug_at_nothing_throws(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Missing source after @' );
		ExtensionInputParser::parse( 'my-plugin@', 'plugin' );
	}

	public function test_slug_at_nonexistent_path_throws(): void {
		$this->expectException( \InvalidArgumentException::class );
		ExtensionInputParser::parse( 'my-plugin@/nonexistent/path/that/does/not/exist', 'plugin' );
	}

	public function test_colon_with_empty_version_falls_through(): void {
		// "woocommerce:" — colon found but version is empty string, so colon check is skipped.
		// Then pure slug check: "woocommerce:" is not a valid slug (contains :).
		// Should throw.
		$this->expectException( \InvalidArgumentException::class );
		ExtensionInputParser::parse( 'woocommerce:', 'plugin' );
	}

	/**
	 * URLs with colons do not trigger slug:version parsing.
	 */
	public function test_http_url_not_parsed_as_slug_version(): void {
		$ext = ExtensionInputParser::parse( 'https://example.com/plugin.zip', 'plugin' );

		// Should be parsed as URL, not as slug "https" with version "//example.com/plugin.zip"
		$this->assertSame( 'url', $ext->from );
	}

	public function test_colon_in_path_does_not_trigger_version(): void {
		// On Linux, colons are valid in filenames but rare.
		// If the path doesn't exist, it won't match is_existing_path.
		// If it contains ":", the colon check would try to split it.
		// But the left side "https" IS a valid slug, so it would parse as slug:version.
		// The URL check runs first (step 2) and catches it.
		$ext = ExtensionInputParser::parse( 'http://localhost/plugin.zip', 'plugin' );

		$this->assertSame( 'url', $ext->from );
	}
}
