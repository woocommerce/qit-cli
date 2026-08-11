<?php

namespace QIT_CLI_Tests;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use QIT_CLI\Blueprints\BlueprintException;
use QIT_CLI\Blueprints\BlueprintParser;
use QIT_CLI\Blueprints\BlueprintTranspiler;
use QIT_CLI\Blueprints\TranspiledBlueprint;

/**
 * Unit tests for turning Playground Blueprints into QIT environments.
 */
class BlueprintTranspilerTest extends TestCase {

	/**
	 * @param array<string, mixed> $blueprint
	 */
	private function transpile( array $blueprint ): TranspiledBlueprint {
		return ( new BlueprintTranspiler() )->transpile( $blueprint );
	}

	/**
	 * @param TranspiledBlueprint $result
	 *
	 * @return string[]
	 */
	private function commands( TranspiledBlueprint $result ): array {
		return array_column( $result->steps, 'command' );
	}

	/**
	 * Recover the PHP/SQL a payload step writes into the container.
	 */
	private function payload_of( string $command ): string {
		return (string) base64_decode( (string) preg_replace( "/^.*printf %s '([^']+)'.*$/s", '$1', $command ) );
	}

	// ── Declarative half: versions, plugins, themes ──

	public function test_preferred_versions_become_env_config(): void {
		$result = $this->transpile( [
			'preferredVersions' => [
				'php' => '8.1',
				'wp'  => '6.5',
			],
		] );

		$this->assertSame( '8.1', $result->env_config['php_version'] );
		$this->assertSame( '6.5', $result->env_config['wordpress_version'] );
	}

	public function test_version_aliases_are_mapped_to_qit_values(): void {
		$result = $this->transpile( [
			'preferredVersions' => [
				'php' => 'latest',
				'wp'  => 'nightly',
			],
		] );

		$this->assertSame( '8.3', $result->env_config['php_version'] );
		$this->assertSame( 'nightly', $result->env_config['wordpress_version'] );
		$this->assertNotEmpty( $result->warnings, 'Resolving a PHP alias should be reported.' );
	}

	public function test_install_plugin_from_wporg_becomes_a_plugin_entry(): void {
		$result = $this->transpile( [
			'steps' => [
				[
					'step'       => 'installPlugin',
					'pluginData' => [
						'resource' => 'wordpress.org/plugins',
						'slug'     => 'hello-dolly',
					],
				],
			],
		] );

		$this->assertSame(
			[ [ 'slug' => 'hello-dolly', 'from' => 'wporg' ] ],
			$result->env_config['plugins']
		);
		$this->assertSame( [], $result->steps, 'Installing a plugin needs no imperative step.' );
	}

	public function test_install_plugin_from_url_keeps_the_zip_url(): void {
		$result = $this->transpile( [
			'steps' => [
				[
					'step'       => 'installPlugin',
					'pluginData' => [
						'resource' => 'url',
						'url'      => 'https://example.com/my-plugin.zip',
					],
				],
			],
		] );

		$this->assertSame(
			[ [ 'slug' => 'my-plugin', 'from' => 'url', 'url' => 'https://example.com/my-plugin.zip' ] ],
			$result->env_config['plugins']
		);
	}

	public function test_woocommerce_is_pinned_instead_of_listed_as_a_plugin(): void {
		$result = $this->transpile( [
			'steps' => [
				[
					'step'       => 'installPlugin',
					'pluginData' => [
						'resource' => 'wordpress.org/plugins',
						'slug'     => 'woocommerce',
						'version'  => '9.0.0',
					],
				],
			],
		] );

		$this->assertSame( '9.0.0', $result->env_config['woocommerce_version'] );
		$this->assertArrayNotHasKey( 'plugins', $result->env_config );
	}

	public function test_plugins_shorthand_accepts_slugs_and_urls(): void {
		$result = $this->transpile( [
			'plugins' => [ 'akismet', 'https://example.com/other.zip' ],
		] );

		$this->assertSame( [ 'akismet', 'other' ], array_column( $result->env_config['plugins'], 'slug' ) );
		$this->assertSame( [ 'wporg', 'url' ], array_column( $result->env_config['plugins'], 'from' ) );
	}

	public function test_plugin_with_activate_false_is_deactivated_after_install(): void {
		// QIT activates everything it installs, so the Blueprint intent needs an undo step.
		$result = $this->transpile( [
			'steps' => [
				[
					'step'       => 'installPlugin',
					'pluginData' => [ 'resource' => 'wordpress.org/plugins', 'slug' => 'akismet' ],
					'options'    => [ 'activate' => false ],
				],
			],
		] );

		$this->assertSame( [ "wp plugin deactivate 'akismet'" ], $this->commands( $result ) );
	}

	public function test_theme_install_with_activate_emits_activation_step(): void {
		$result = $this->transpile( [
			'steps' => [
				[
					'step'      => 'installTheme',
					'themeData' => [ 'resource' => 'wordpress.org/themes', 'slug' => 'storefront' ],
					'options'   => [ 'activate' => true ],
				],
			],
		] );

		$this->assertSame( [ [ 'slug' => 'storefront', 'from' => 'wporg' ] ], $result->env_config['themes'] );
		$this->assertSame( [ "wp theme activate 'storefront'" ], $this->commands( $result ) );
	}

	// ── Imperative half: steps → WP-CLI ──

	public function test_site_options_run_in_one_pass_and_constants_use_wp_cli(): void {
		$result = $this->transpile( [
			'steps' => [
				[
					'step'    => 'setSiteOptions',
					'options' => [ 'blogname' => 'My Store', 'woocommerce_currency' => 'EUR' ],
				],
				[
					'step'   => 'defineWpConfigConsts',
					'consts' => [ 'WP_DEBUG' => true ],
				],
			],
		] );

		$commands = $this->commands( $result );

		// One WordPress bootstrap for every option, not one per option.
		$this->assertCount( 2, $commands );
		$this->assertStringContainsString( 'wp eval-file', $commands[0] );
		$this->assertSame( "wp config set 'WP_DEBUG' 'true' --type=constant --raw", $commands[1] );

		$payload = $this->payload_of( $commands[0] );

		$this->assertStringContainsString( 'update_option(', $payload );
		$this->assertStringContainsString( base64_encode( '{"blogname":"My Store","woocommerce_currency":"EUR"}' ), $payload );
	}

	public function test_array_site_options_keep_their_structure(): void {
		$result = $this->transpile( [
			'siteOptions' => [ 'my_option' => [ 'a' => 1 ] ],
		] );

		$payload = $this->payload_of( $this->commands( $result )[0] );

		$this->assertStringContainsString( base64_encode( '{"my_option":{"a":1}}' ), $payload );
	}

	public function test_wp_cli_step_is_passed_through(): void {
		$result = $this->transpile( [
			'steps' => [
				[ 'step' => 'wp-cli', 'command' => 'wp post create --post_title=Hello' ],
				[ 'step' => 'wp-cli', 'command' => 'option get home' ],
			],
		] );

		$this->assertSame(
			[ 'wp post create --post_title=Hello', 'wp option get home' ],
			$this->commands( $result )
		);
	}

	public function test_run_php_is_base64_encoded_and_evaluated(): void {
		$code   = '<?php echo "it\'s fine";';
		$result = $this->transpile( [
			'steps' => [ [ 'step' => 'runPHP', 'code' => $code ] ],
		] );

		$command = $this->commands( $result )[0];

		$this->assertStringContainsString( base64_encode( $code ), $command, 'Payload is base64 so quoting never bites.' );
		$this->assertStringContainsString( 'wp eval-file', $command );
	}

	public function test_run_sql_warns_about_the_sqlite_to_mysql_gap(): void {
		$result = $this->transpile( [
			'steps' => [ [ 'step' => 'runSql', 'sql' => [ 'resource' => 'literal', 'contents' => 'SELECT 1;' ] ] ],
		] );

		$this->assertStringContainsString( 'wp db query <', $this->commands( $result )[0] );
		$this->assertNotEmpty( preg_grep( '/SQLite/', $result->warnings ) );
	}

	public function test_site_options_do_not_fail_on_values_wordpress_refuses(): void {
		// `wp option update` exits 1 when WordPress reports a value as unchanged or
		// refuses to store it (WooCommerce does this for a handful of options).
		// Playground calls update_option() and ignores the result, so do the same and
		// report the ones that did not land.
		$result = $this->transpile( [
			'steps' => [
				[ 'step' => 'setSiteOptions', 'options' => [ 'woocommerce_api_enabled' => 'yes' ] ],
				[ 'step' => 'wp-cli', 'command' => 'wp cache flush' ],
			],
		] );

		$payload = $this->payload_of( $this->commands( $result )[0] );

		$this->assertStringContainsString( 'option not applied by WordPress', $payload );
		$this->assertStringNotContainsString( 'wp option update', $this->commands( $result )[0] );
	}

	public function test_plugin_activation_commands_are_coalesced(): void {
		// A Blueprint installing a dozen deactivated plugins would otherwise boot
		// WordPress once per plugin.
		$install = static function ( string $slug ): array {
			return [
				'step'       => 'installPlugin',
				'pluginData' => [ 'resource' => 'wordpress.org/plugins', 'slug' => $slug ],
				'options'    => [ 'activate' => false ],
			];
		};

		$result = $this->transpile( [
			'steps' => [ $install( 'akismet' ), $install( 'jetpack' ), $install( 'classic-editor' ) ],
		] );

		$this->assertSame( [ "wp plugin deactivate 'akismet' 'jetpack' 'classic-editor'" ], $this->commands( $result ) );
		$this->assertSame( 'Deactivate 3 plugins', $result->steps[0]['description'] );
	}

	public function test_run_php_payloads_get_playground_paths_rewritten(): void {
		$result = $this->transpile( [
			'steps' => [
				[ 'step' => 'runPHP', 'code' => "<?php require_once '/wordpress/wp-load.php'; echo 1;" ],
			],
		] );

		$payload = base64_decode( (string) preg_replace( "/^.*printf %s '([^']+)'.*$/s", '$1', $this->commands( $result )[0] ) );

		$this->assertStringContainsString( "/var/www/html/wp-load.php", $payload );
		$this->assertStringNotContainsString( '/wordpress/', $payload );
	}

	public function test_path_rewriting_leaves_urls_alone(): void {
		// A bare /wordpress path is a container path; the same string inside a URL
		// is somebody's host and must survive untouched.
		$result = $this->transpile( [
			'steps' => [
				[
					'step' => 'runPHP',
					'code' => "<?php \$zip = 'https://example.com/wordpress/x.zip'; require_once '/wordpress/wp-load.php';",
				],
			],
		] );

		$payload = $this->payload_of( $this->commands( $result )[0] );

		$this->assertStringContainsString( "'https://example.com/wordpress/x.zip'", $payload );
		$this->assertStringContainsString( "require_once '/var/www/html/wp-load.php'", $payload );
	}

	public function test_php_extension_bundles_are_reported(): void {
		$result = $this->transpile( [ 'phpExtensionBundles' => [ 'kitchen-sink' ] ] );

		$this->assertNotEmpty( preg_grep( '/kitchen-sink/', $result->warnings ) );
	}

	public function test_activate_plugin_accepts_a_plugin_path(): void {
		$result = $this->transpile( [
			'steps' => [ [ 'step' => 'activatePlugin', 'pluginPath' => 'my-plugin/my-plugin.php' ] ],
		] );

		$this->assertSame( [ "wp plugin activate 'my-plugin'" ], $this->commands( $result ) );
	}

	public function test_file_steps_translate_playground_paths_to_the_container_root(): void {
		$result = $this->transpile( [
			'steps' => [
				[ 'step' => 'mkdir', 'path' => '/wordpress/wp-content/uploads/x' ],
				[ 'step' => 'writeFile', 'path' => '/wordpress/wp-content/mu-plugins/x.php', 'data' => '<?php' ],
			],
		] );

		$commands = $this->commands( $result );

		$this->assertSame( "mkdir -p '/var/www/html/wp-content/uploads/x'", $commands[0] );
		$this->assertStringContainsString( '/var/www/html/wp-content/mu-plugins/x.php', $commands[1] );
	}

	public function test_relative_request_urls_resolve_against_the_environment_home(): void {
		$result = $this->transpile( [
			'steps' => [ [ 'step' => 'request', 'request' => [ 'url' => '/wp-admin/admin-ajax.php' ] ] ],
		] );

		$this->assertStringContainsString( '$(wp option get home)/wp-admin/admin-ajax.php', $this->commands( $result )[0] );
	}

	public function test_import_wxr_installs_the_importer_and_imports(): void {
		$result = $this->transpile( [
			'steps' => [
				[
					'step' => 'importWxr',
					'file' => [ 'resource' => 'url', 'url' => 'https://example.com/content.xml' ],
				],
			],
		] );

		$commands = $this->commands( $result );

		$this->assertSame( 'wp plugin install wordpress-importer --activate', $commands[0] );
		$this->assertStringContainsString( 'curl', $commands[1] );
		$this->assertStringContainsString( 'wp import', $commands[2] );
	}

	// ── Steps QIT cannot honour ──

	public function test_git_resources_are_reported_rather_than_installed(): void {
		// Unsupported resource types must not leave a plugin quietly missing.
		$result = $this->transpile( [
			'steps' => [
				[
					'step'       => 'installPlugin',
					'pluginData' => [
						'resource' => 'git:directory',
						'url'      => 'https://github.com/WordPress/learn-app',
						'ref'      => 'dist/main',
					],
				],
			],
		] );

		$this->assertArrayNotHasKey( 'plugins', $result->env_config );
		$this->assertNotEmpty( preg_grep( '/unsupported resource type/', $result->warnings ) );
	}

	public function test_playground_only_steps_are_reported_not_silently_dropped(): void {
		$result = $this->transpile( [
			'steps' => [
				[ 'step' => 'enableMultisite' ],
				[ 'step' => 'resetData' ],
				[ 'step' => 'defineSiteUrl', 'siteUrl' => 'https://example.com' ],
			],
		] );

		$this->assertSame( [ 'enableMultisite', 'resetData', 'defineSiteUrl' ], $result->unsupported );
		$this->assertCount( 3, $result->warnings );
		$this->assertSame( [], $result->steps );
	}

	public function test_disabled_steps_are_skipped(): void {
		// Blueprints allow false entries so steps can be toggled off.
		$result = $this->transpile( [
			'steps' => [ false, null, [ 'step' => 'wp-cli', 'command' => 'wp cache flush' ] ],
		] );

		$this->assertSame( [ 'wp cache flush' ], $this->commands( $result ) );
	}

	public function test_networking_disabled_maps_to_offline_mode(): void {
		$result = $this->transpile( [ 'features' => [ 'networking' => false ] ] );

		$this->assertSame( 'offline', $result->env_config['network_mode'] );
	}

	// ── Utility package emission ──

	public function test_steps_are_written_as_a_utility_package(): void {
		$result = $this->transpile( [
			'steps' => [ [ 'step' => 'wp-cli', 'command' => 'wp cache flush' ] ],
		] );

		$dir = sys_get_temp_dir() . '/qit-blueprint-test-' . uniqid();
		$result->write_utility_package( $dir );

		$manifest = json_decode( (string) file_get_contents( $dir . '/qit-test.json' ), true );

		$this->assertSame( 'utility', $manifest['package_type'] );
		$this->assertArrayNotHasKey( 'test_type', $manifest, 'Utility packages must not declare a test type.' );
		$this->assertSame( 'wp cache flush', $manifest['test']['phases']['globalSetup'][0]['command'] );
		$this->assertSame( 'docker', $manifest['test']['phases']['globalSetup'][0]['runs_on'] );
		$this->assertFalse( $manifest['test']['phases']['globalSetup'][0]['continue_on_error'] );

		unlink( $dir . '/qit-test.json' );
		rmdir( $dir );
	}

	// ── Bundled files (shipped next to the Blueprint) ──

	/**
	 * @param array<string, mixed> $blueprint
	 *
	 * @return array{0: TranspiledBlueprint, 1: string} The result and the bundle directory.
	 */
	private function transpile_bundle( array $blueprint, array $files ): array {
		$dir = sys_get_temp_dir() . '/qit-bundle-' . uniqid();
		mkdir( $dir );

		foreach ( $files as $name => $contents ) {
			file_put_contents( $dir . '/' . $name, $contents );
		}

		$path = $dir . '/blueprint.json';
		file_put_contents( $path, (string) json_encode( $blueprint ) );

		return [ ( new BlueprintTranspiler() )->transpile( $blueprint, $path ), $dir ];
	}

	public function test_bundled_theme_becomes_a_local_extension(): void {
		// QIT already installs local zips, so a bundled theme needs no shell step.
		[ $result, $dir ] = $this->transpile_bundle(
			[
				'steps' => [
					[
						'step'      => 'installTheme',
						'themeData' => [ 'resource' => 'bundled', 'path' => './koinonia.zip' ],
					],
				],
			],
			[ 'koinonia.zip' => 'PK' ]
		);

		$this->assertSame(
			[ [ 'slug' => 'koinonia', 'from' => 'local', 'path' => $dir . '/koinonia.zip' ] ],
			$result->env_config['themes']
		);
		$this->assertSame( [], $result->assets, 'An extension is installed from the host, not shipped in the package.' );
	}

	public function test_bundled_wxr_is_shipped_with_the_package(): void {
		[ $result ] = $this->transpile_bundle(
			[
				'steps' => [
					[ 'step' => 'importWxr', 'file' => [ 'resource' => 'bundled', 'path' => './content.xml' ] ],
				],
			],
			[ 'content.xml' => '<rss/>' ]
		);

		$this->assertArrayHasKey( 'content.xml', $result->assets );
		$this->assertContains(
			"wp import '/qit/packages/blueprint-steps/content.xml' --authors=create",
			$this->commands( $result ),
			'Large bundled files are mounted, never inlined as base64.'
		);
	}

	public function test_bundled_files_are_copied_into_the_package(): void {
		[ $result ] = $this->transpile_bundle(
			[
				'steps' => [
					[ 'step' => 'importWxr', 'file' => [ 'resource' => 'bundled', 'path' => './content.xml' ] ],
				],
			],
			[ 'content.xml' => '<rss>hello</rss>' ]
		);

		$package = sys_get_temp_dir() . '/qit-bundle-pkg-' . uniqid();
		$result->write_utility_package( $package );

		$this->assertSame( '<rss>hello</rss>', file_get_contents( $package . '/content.xml' ) );
	}

	public function test_bundled_unzip_and_sql_use_the_mounted_path(): void {
		[ $result ] = $this->transpile_bundle(
			[
				'steps' => [
					[
						'step'          => 'unzip',
						'zipFile'       => [ 'resource' => 'bundled', 'path' => './uploads.zip' ],
						'extractToPath' => '/wordpress/wp-content/uploads',
					],
					[ 'step' => 'runSql', 'sql' => [ 'resource' => 'bundled', 'path' => './seed.sql' ] ],
				],
			],
			[ 'uploads.zip' => 'PK', 'seed.sql' => 'SELECT 1;' ]
		);

		$commands = $this->commands( $result );

		$this->assertStringContainsString( "unzip -o '/qit/packages/blueprint-steps/uploads.zip'", $commands[0] );
		$this->assertStringContainsString( "-d '/var/www/html/wp-content/uploads'", $commands[0] );
		$this->assertSame( "wp db query < '/qit/packages/blueprint-steps/seed.sql'", $commands[1] );
	}

	public function test_bundled_files_sharing_a_basename_both_survive(): void {
		// Two bundled files land in one flat package directory; the second must not
		// overwrite the first.
		$dir = sys_get_temp_dir() . '/qit-bundle-' . uniqid();
		mkdir( $dir . '/a', 0777, true );
		mkdir( $dir . '/b', 0777, true );
		file_put_contents( $dir . '/a/content.xml', '<rss>a</rss>' );
		file_put_contents( $dir . '/b/content.xml', '<rss>b</rss>' );

		$blueprint = [
			'steps' => [
				[ 'step' => 'importWxr', 'file' => [ 'resource' => 'bundled', 'path' => './a/content.xml' ] ],
				[ 'step' => 'importWxr', 'file' => [ 'resource' => 'bundled', 'path' => './b/content.xml' ] ],
			],
		];

		$path = $dir . '/blueprint.json';
		file_put_contents( $path, (string) json_encode( $blueprint ) );

		$result = ( new BlueprintTranspiler() )->transpile( $blueprint, $path );

		$this->assertCount( 2, $result->assets, 'Both files are shipped under distinct names.' );
		$this->assertSame(
			[ $dir . '/a/content.xml', $dir . '/b/content.xml' ],
			array_values( $result->assets )
		);
	}

	public function test_bundled_paths_cannot_escape_the_blueprint_directory(): void {
		[ $result ] = $this->transpile_bundle(
			[
				'steps' => [
					[ 'step' => 'importWxr', 'file' => [ 'resource' => 'bundled', 'path' => '../../../../etc/hosts' ] ],
				],
			],
			[]
		);

		$this->assertSame( [], $result->assets );
		$this->assertNotEmpty( preg_grep( '/outside the Blueprint directory|was not found/', $result->warnings ) );
	}

	public function test_a_missing_bundled_file_is_reported(): void {
		[ $result ] = $this->transpile_bundle(
			[
				'steps' => [
					[ 'step' => 'importWxr', 'file' => [ 'resource' => 'bundled', 'path' => './nope.xml' ] ],
				],
			],
			[]
		);

		$this->assertNotEmpty( preg_grep( '/was not found next to the Blueprint/', $result->warnings ) );
	}

	public function test_bundled_resources_need_the_blueprint_path(): void {
		// transpile() without a path (blueprint:import of a decoded array) cannot resolve them.
		$result = $this->transpile( [
			'steps' => [
				[ 'step' => 'importWxr', 'file' => [ 'resource' => 'bundled', 'path' => './content.xml' ] ],
			],
		] );

		$this->assertNotEmpty( preg_grep( '/the Blueprint path is unknown/', $result->warnings ) );
	}

	// ── Shared preparation across commands ──

	public function test_package_directory_is_stable_for_the_same_blueprint(): void {
		// env:up and run:e2e each prepare the Blueprint independently; they have to
		// land on the same package directory or env:up would mount it twice.
		$environment = new \QIT_CLI\Blueprints\BlueprintEnvironment();
		$path        = __DIR__ . '/data/blueprint-example.json';

		$this->assertSame( $environment->package_dir( $path ), $environment->package_dir( $path ) );
		$this->assertNotSame( $environment->package_dir( $path ), $environment->package_dir( $path . '.other' ) );
	}

	public function test_a_blueprint_without_steps_materializes_no_package(): void {
		$environment = new \QIT_CLI\Blueprints\BlueprintEnvironment();
		$result      = $this->transpile( [ 'preferredVersions' => [ 'php' => '8.3' ] ] );

		$this->assertNull( $environment->materialize( '/tmp/does-not-matter.json', $result ) );
	}

	// ── Parser ──

	public function test_parser_rejects_remote_blueprints(): void {
		$this->expectException( BlueprintException::class );
		$this->expectExceptionMessageMatches( '/Remote Blueprints are not supported/' );

		( new BlueprintParser() )->from_file( 'https://playground.wordpress.net/blueprint.json' );
	}

	public function test_parser_rejects_blueprint_v2(): void {
		$this->expectException( BlueprintException::class );
		$this->expectExceptionMessageMatches( '/version 1/' );

		( new BlueprintParser() )->from_string( '{"version": 2}' );
	}

	public function test_parser_rejects_invalid_json(): void {
		$this->expectException( BlueprintException::class );

		( new BlueprintParser() )->from_string( '{not json' );
	}
}
