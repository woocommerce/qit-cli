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
