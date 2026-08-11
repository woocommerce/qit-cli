<?php

namespace QIT_CLI_Tests;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use QIT_CLI\Blueprints\BlueprintExporter;
use QIT_CLI\Blueprints\BlueprintTranspiler;

/**
 * Unit tests for exporting a QIT environment as a Playground Blueprint.
 */
class BlueprintExporterTest extends TestCase {

	/**
	 * @param array<string, mixed> $env_config
	 *
	 * @return array<string, mixed>
	 */
	private function export( array $env_config, ?BlueprintExporter &$exporter = null ): array {
		$exporter = new BlueprintExporter();

		return $exporter->export( $env_config );
	}

	public function test_versions_are_mapped_to_playground_aliases(): void {
		$blueprint = $this->export( [
			'php_version'       => '8.2',
			'wordpress_version' => 'stable',
		] );

		$this->assertSame( '8.2', $blueprint['preferredVersions']['php'] );
		$this->assertSame( 'latest', $blueprint['preferredVersions']['wp'] );
	}

	public function test_plugins_become_install_steps(): void {
		$blueprint = $this->export( [
			'plugins' => [
				[ 'slug' => 'akismet', 'from' => 'wporg' ],
				[ 'slug' => 'my-plugin', 'from' => 'url', 'url' => 'https://example.com/my-plugin.zip' ],
			],
		] );

		$installs = array_values( array_filter( $blueprint['steps'], static function ( $step ) {
			return $step['step'] === 'installPlugin';
		} ) );

		$this->assertSame( 'wordpress.org/plugins', $installs[0]['pluginData']['resource'] );
		$this->assertSame( 'akismet', $installs[0]['pluginData']['slug'] );
		$this->assertSame( 'url', $installs[1]['pluginData']['resource'] );
		$this->assertSame( 'https://example.com/my-plugin.zip', $installs[1]['pluginData']['url'] );
	}

	public function test_a_stable_version_is_not_pinned(): void {
		$blueprint = $this->export( [ 'plugins' => [ [ 'slug' => 'akismet', 'from' => 'wporg', 'version' => 'stable' ] ] ] );

		$this->assertSame( 'akismet', $blueprint['steps'][1]['pluginData']['slug'] );
	}

	public function test_qit_json_short_keys_are_understood(): void {
		$blueprint = $this->export( [
			'php' => '8.3',
			'wp'  => '6.7',
			'woo' => '9.4.0',
		] );

		$this->assertSame( '8.3', $blueprint['preferredVersions']['php'] );
		$this->assertSame( '6.7', $blueprint['preferredVersions']['wp'] );
		$this->assertStringContainsString( 'woocommerce.9.4.0.zip', $blueprint['steps'][1]['pluginData']['url'] );
	}

	public function test_only_the_first_theme_is_activated(): void {
		$blueprint = $this->export( [
			'themes' => [ 'storefront', 'twentytwentyfour' ],
		] );

		$themes = array_values( array_filter( $blueprint['steps'], static function ( $step ) {
			return $step['step'] === 'installTheme';
		} ) );

		$this->assertTrue( $themes[0]['options']['activate'] );
		$this->assertFalse( $themes[1]['options']['activate'] );
	}

	public function test_pinned_versions_become_versioned_download_urls(): void {
		// Playground's CorePluginReference carries no version and always fetches the
		// current release, and it does not parse a "slug@version" reference either.
		// Pinning has to point at the versioned zip.
		$blueprint = $this->export( [ 'woocommerce_version' => '9.1.0' ] );

		$woo = $blueprint['steps'][1];

		$this->assertSame( 'installPlugin', $woo['step'] );
		$this->assertSame(
			[ 'resource' => 'url', 'url' => 'https://downloads.wordpress.org/plugin/woocommerce.9.1.0.zip' ],
			$woo['pluginData']
		);
	}

	public function test_pinned_themes_use_the_theme_download_path(): void {
		$blueprint = $this->export( [ 'themes' => [ [ 'slug' => 'storefront', 'from' => 'wporg', 'version' => '4.6.2' ] ] ] );

		$this->assertSame(
			'https://downloads.wordpress.org/theme/storefront.4.6.2.zip',
			$blueprint['steps'][1]['themeData']['url']
		);
	}

	public function test_local_extensions_are_reported_as_lost(): void {
		$exporter  = null;
		$blueprint = $this->export( [
			'plugins' => [ [ 'slug' => 'my-plugin', 'from' => 'local', 'path' => './my-plugin' ] ],
		], $exporter );

		$installs = array_filter( $blueprint['steps'], static function ( $step ) {
			return $step['step'] === 'installPlugin';
		} );

		$this->assertSame( [], $installs, 'A local path cannot be installed by Playground.' );
		$this->assertNotEmpty( preg_grep( '/cannot be expressed in a Blueprint/', $exporter->get_warnings() ) );
	}

	public function test_docker_only_settings_are_reported_as_lost(): void {
		$exporter = null;
		$this->export( [
			'volumes'        => [ './x:/var/www/html/x' ],
			'php_extensions' => [ 'imagick' ],
			'xdebug'         => 'debug',
		], $exporter );

		$this->assertCount( 3, $exporter->get_warnings() );
	}

	// ── Round trip ──

	public function test_export_then_import_preserves_versions_and_extensions(): void {
		$env_config = [
			'php_version'       => '8.1',
			'wordpress_version' => '6.5',
			'plugins'           => [ [ 'slug' => 'akismet', 'from' => 'wporg' ] ],
			'themes'            => [ [ 'slug' => 'storefront', 'from' => 'wporg' ] ],
		];

		$env_config['plugins'][] = [ 'slug' => 'classic-editor', 'from' => 'wporg', 'version' => '1.6.5' ];

		$blueprint  = $this->export( $env_config );
		$round_trip = ( new BlueprintTranspiler() )->transpile( $blueprint );

		$this->assertContains(
			[ 'slug' => 'classic-editor', 'from' => 'wporg', 'version' => '1.6.5' ],
			$round_trip->env_config['plugins'],
			'A pinned version survives the round trip through the slug.'
		);

		$this->assertSame( '8.1', $round_trip->env_config['php_version'] );
		$this->assertSame( '6.5', $round_trip->env_config['wordpress_version'] );
		$this->assertContains( [ 'slug' => 'akismet', 'from' => 'wporg' ], $round_trip->env_config['plugins'] );
		$this->assertSame( [ [ 'slug' => 'storefront', 'from' => 'wporg' ] ], $round_trip->env_config['themes'] );
	}
}
