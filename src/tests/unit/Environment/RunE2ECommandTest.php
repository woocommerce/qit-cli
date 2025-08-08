<?php

namespace QIT_CLI_Tests\Environment;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use QIT_CLI\Environment\PluginsAndThemesParser;
use QIT_CLI\LocalTests\ConfigurationProcessor;
use QIT_CLI\ManagerSync;
use QIT_CLI\WooExtensionsList;
use QIT_CLI_Tests\Helpers\EnvInfoNormalizer;
use QIT_CLI_Tests\QITTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;
use function QIT_CLI\get_manager_url;

class RunE2ECommandTest extends QITTestCase {
	use MatchesSnapshots;
	use EnvInfoNormalizer;

	/** @var ApplicationTester */
	protected $application_tester;

	protected $scenarios_dir = __DIR__ . '/../data/rune2e-scenarios/';

	public function setUp(): void {
		parent::setUp();

		$mocked_woo_extension_list = function () {
			$cache        = App::make( Cache::class );
			$manager_sync = App::make( ManagerSync::class );

			return new class( $cache, $manager_sync ) extends WooExtensionsList {
				protected $theme_slugs = [
					'storefront',
					'deli-theme',
					'boutique',
					'blocksy',
					'hestia',
					'twentytwentyone',
				];

				public function __construct( Cache $cache, ManagerSync $manager_sync ) {
					parent::__construct( $cache, $manager_sync );
				}

				public function get_woo_extension_slug_by_id( $id ): string {
					// Assign different IDs for each known theme so we can detect them:
					switch ( $id ) {
						case 999:
							return 'storefront';
						case 998:
							return 'deli-theme';
						case 997:
							return 'boutique';
						case 996:
							return 'blocksy';
						case 995:
							return 'hestia';
						case 994:
							return 'twentytwentyone';
						case 2165910:
							return 'woocommerce-shipping';
					}

					return 'woocommerce-amazon-s3-storage'; // default plugin
				}

				public function get_woo_extension_id_by_slug( $slug ): int {
					// Map each theme slug to a unique ID
					switch ( $slug ) {
						case 'storefront':
							return 999;
						case 'deli-theme':
							return 998;
						case 'boutique':
							return 997;
						case 'blocksy':
							return 996;
						case 'hestia':
							return 995;
						case 'twentytwentyone':
							return 994;
						case 'woocommerce-shipping':
							return 2165910;
					}

					return 123; // default plugin ID
				}

				public function get_woo_extension_type( $id ): string {
					$slug = $this->get_woo_extension_slug_by_id( $id );
					if ( in_array( $slug, $this->theme_slugs, true ) ) {
						return 'theme';
					}

					return 'plugin';
				}
			};
		};

		// Before instantiating RunE2ECommand, override the WooExtensionsList dependency:
		App::when( RunE2ECommand::class )
		   ->needs( WooExtensionsList::class )
		   ->give( $mocked_woo_extension_list );

		App::when( PluginsAndThemesParser::class )
		   ->needs( WooExtensionsList::class )
		   ->give( $mocked_woo_extension_list );

		// Mock the get-dependencies endpoint
		App::setVar(
			sprintf( 'mock_%s', 'https://qit.woo.com/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => [],
				'themes'         => [],
				'php_extensions' => [],
			] )
		);

		// Register run:e2e command.
		$this->application_tester = $this->make_application_tester( static function ( Application $application ) {
			$create_run_commands = \QIT_CLI\App::make( \QIT_CLI\Commands\CreateRunCommands::class );
			$create_run_commands->register_commands( $application );
		} );
	}

	/**
	 * scenario-cli-only:
	 * No qit.yml, CLI-only configuration.
	 * Using known extension: woocommerce-amazon-s3-storage
	 */
	public function test_cli_only_config() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-cli-only';
		chdir( $fixture_dir );

		// Using a known extension: "woocommerce-amazon-s3-storage"
		// Ensure the scenario-cli-only directory has a ./woocommerce-amazon-s3-storage plugin directory and tests.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--source'      => './woocommerce-amazon-s3-storage', // Local source
			'--woo'         => '7.1',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * scenario-config-only:
	 * qit.yml present, no CLI overrides.
	 * Using woocommerce-amazon-s3-storage as known extension.
	 */
	public function test_config_only() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-config-only';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * scenario-config-overrides:
	 * qit.yml present, CLI overrides source and test_tags.
	 * Pass test as second argument.
	 */
	public function test_config_with_cli_overrides() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-config-overrides';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'test'          => 'new-test-tag',
			'--source'      => './overridden-source',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * scenario-conflict-woo:
	 * Triggering conflict by adding --plugin=woocommerce along with --woo.
	 */
	public function test_conflict_woo() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-conflict-woo';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--woo'         => '6.0',
			'--plugin'      => [ 'woocommerce' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertNotEquals( Command::SUCCESS, $this->application_tester->getStatusCode(), 'Command unexpectedly succeeded.' );
	}

	/**
	 * scenario-dependencies:
	 * qit.yml + dependencies.json. Check that dependencies are merged in config.
	 */
	public function test_scenario_with_dependencies() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-dependencies';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'             => 'run:e2e',
			'sut'       => 'woocommerce-amazon-s3-storage',
			'--dependencies_mode' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * scenario-env:
	 * Run with --env and ensure env vars appear in final config.
	 */
	public function test_env() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-env-vars';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--env'         => [ 'FOO=bar' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * scenario-missing-test-tags:
	 * qit.yml defines plugin but no test_tags, defaults to ['default'].
	 */
	public function test_missing_test_tags() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-missing-test-tags';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * Test pass-through arguments using --
	 */
	public function test_pass_through_args() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-cli-only';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'runner_args'   => ['--headed', '--workers=2', '--grep=checkout'],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$env_info = json_decode( $this->application_tester->getDisplay(), true );
		
		// Verify runner_args are present in the env_info
		$this->assertArrayHasKey( 'runner_args', $env_info );
		$this->assertEquals( ['--headed', '--workers=2', '--grep=checkout'], $env_info['runner_args'] );
	}

	public function test_with_plugin_dependencies_cli_only() {
		putenv( 'QIT_SELF_TEST=env_info' );

		// Mock plugin dependencies
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), json_encode( [
			'plugins'        => [ 'woocommerce-gateway-stripe', 'woocommerce-subscriptions' ],
			'themes'         => [],
			'php_extensions' => [],
		] ) );

		$this->application_tester->run( [
			'command'             => 'run:e2e',
			'woo_extension'       => 'woocommerce-amazon-s3-storage',
			'--source'            => './woocommerce-amazon-s3-storage', // CLI-only, local dir
			'--dependencies_mode' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_with_theme_dependencies_cli_only() {
		putenv( 'QIT_SELF_TEST=env_info' );

		// Mock theme dependencies
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), json_encode( [
			'plugins'        => [ 'woocommerce-services' ],
			'themes'         => [ 'storefront' ],
			'php_extensions' => [],
		] ) );

		$this->application_tester->run( [
			'command'             => 'run:e2e',
			'woo_extension'       => 'woocommerce-amazon-s3-storage',
			'--source'            => './woocommerce-amazon-s3-storage',
			'--dependencies_mode' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_with_php_extensions_cli_only() {
		putenv( 'QIT_SELF_TEST=env_info' );

		// Mock PHP extensions dependencies
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), json_encode( [
			'plugins'        => [],
			'themes'         => [],
			'php_extensions' => [ 'intl', 'soap' ],
		] ) );

		$this->application_tester->run( [
			'command'             => 'run:e2e',
			'woo_extension'       => 'woocommerce-amazon-s3-storage',
			'--source'            => './woocommerce-amazon-s3-storage',
			'--dependencies_mode' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_with_valid_tunnel_cli_only() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--source'      => './woocommerce-amazon-s3-storage',
			'--tunnel'      => 'cloudflared-binary',
			'--json'        => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}


	public function test_with_woo_version_cli_only() {
		putenv( 'QIT_SELF_TEST=env_info' );

		// No additional mocks needed if no dependencies required.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--source'      => './woocommerce-amazon-s3-storage',
			'--woo'         => '6.0',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}


	public function tearDown(): void {
		parent::tearDown();
		putenv( 'QIT_SELF_TEST' );
	}

	public function test_cli_override_dependencies() {
		putenv( 'QIT_SELF_TEST=env_info' );
		chdir( $this->scenarios_dir . 'scenario-cli-override-dependencies' );

		// Mock dependencies to check they are merged and overridden by CLI.
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), json_encode( [
			'plugins'        => [ 'woocommerce-services' ],
			'themes'         => [],
			'php_extensions' => [],
		] ) );

		// qit.yml sets action=test. We'll override it to bootstrap via CLI.
		$this->application_tester->run( [
			'command'             => 'run:e2e',
			'woo_extension'       => 'woocommerce-amazon-s3-storage', // SUT
			'-p'                  => [ 'woocommerce-sample-plugin:bootstrap' ],
			'--dependencies_mode' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_cli_add_new_plugin() {
		putenv( 'QIT_SELF_TEST=env_info' );
		chdir( $this->scenarios_dir . 'scenario-cli-add-new-plugin' );

		// qit.yml defines woocommerce-known-plugin only.
		// We'll add a new plugin via CLI that isn't in qit.yml.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--plugin'      => [ 'woocommerce-new-plugin:bootstrap' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_cli_default_action() {
		putenv( 'QIT_SELF_TEST=env_info' );
		chdir( $this->scenarios_dir . 'scenario-cli-default-action' );

		// qit.yml sets no action for woocommerce-no-action.
		// We'll just specify the plugin in CLI with no action. It should default to 'test'.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--plugin'      => [ 'woocommerce-no-action' ], // no action specified
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_multiple_test_tags_from_config_and_cli() {
		putenv( 'QIT_SELF_TEST=env_info' );

		// Change to the new scenario directory.
		$fixture_dir = $this->scenarios_dir . 'scenario-multiple-test-tags';
		chdir( $fixture_dir );

		// We pass the second argument "test" as comma-separated tags:
		// "self-test-multiple-test-tags,self-test-multiple-test-tags-another"
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'test'          => 'self-test-multiple-test-tags,self-test-multiple-test-tags-another',
		], [ 'capture_stderr_separately' => true ] );

		// Initially, if the code doesn't handle splitting comma-separated test tags,
		// it might fail or produce incorrect JSON.
		// After you implement the fix to split comma-separated tags,
		// this should pass and match the snapshot.
		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_additional_plugin_multiple_test_tags() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-additional-plugin-multiple-test-tags';
		chdir( $fixture_dir );

		// The SUT test tags come via CLI as comma-separated:
		// "self-test-multiple-test-tags,self-test-multiple-test-tags-another"
		// The additional plugin is also given via CLI, with :test: plus comma-separated tags:
		// "woocommerce-progressive-discounts:test:self-test-extra-tag,self-test-extra-tag-2"
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'test'          => 'self-test-multiple-test-tags,self-test-multiple-test-tags-another',
			'--plugin'      => [
				'woocommerce-progressive-discounts:test:self-test-extra-tag,self-test-extra-tag-2',
			],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_theme_sut() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-sut';
		$this->assertDirectoryExists( $fixture_dir, "Fixture directory does not exist: {$fixture_dir}" );
		chdir( $fixture_dir );

		// We know qit.yml defines the theme "storefront" as SUT.
		// We'll run run:e2e with woo_extension = 'storefront'.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'storefront', // This should now be detected as a theme SUT.
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_plugin_and_theme_sut_theme() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-plugin-and-theme-sut';
		$this->assertDirectoryExists( $fixture_dir, "Fixture directory does not exist: {$fixture_dir}" );
		chdir( $fixture_dir );

		// qit.yml has both plugins and themes, we pick 'deli-theme' as SUT.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'deli-theme',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_theme_with_dependencies() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-with-dependencies';
		chdir( $fixture_dir );

		// storefront is the theme SUT, we run with --dependencies=bootstrap to apply dependencies
		$this->application_tester->run( [
			'command'             => 'run:e2e',
			'woo_extension'       => 'storefront',
			'--dependencies_mode' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_theme_with_additional_plugin() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-with-additional-plugin';
		chdir( $fixture_dir );

		// deli-theme from qit.yml, add plugin via CLI
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'deli-theme',
			'--plugin'      => [ 'woocommerce-extra-plugin:bootstrap' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_theme_with_cli_source_override() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-with-cli-source';
		chdir( $fixture_dir );

		// boutique in qit.yml, override source via CLI
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'boutique',
			'--source'      => './custom-boutique-theme',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_theme_only_config_no_cli() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-only-config-no-cli';
		chdir( $fixture_dir );

		// hestia defined in qit.yml, no CLI overrides
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'hestia',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_theme_and_plugin_both_config() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-and-plugin-both-config';
		chdir( $fixture_dir );

		// twentytwentyone is the theme in qit.yml, choose it as SUT
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'twentytwentyone',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_theme_with_tunnel() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-with-tunnel';
		chdir( $fixture_dir );

		// blocksy defined in qit.yml, run with a tunnel
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'blocksy',
			'--tunnel'      => 'cloudflared-binary',
			'--json'        => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_multiple_additional_plugins_no_actions_in_cli_only_config() {
		putenv( 'QIT_SELF_TEST=env_info' );
		chdir( $this->scenarios_dir . 'scenario-cli-only' );

		// Run with a SUT and multiple additional plugins without specifying actions.
		// Expected: SUT defaults to "test", additional plugins default to "bootstrap".
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage', // SUT
			'--plugin'      => [ 'plugin-one', 'plugin-two' ],  // No actions specified
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_sut_default_test_with_non_sut_plugins_bootstrap_in_cli_only_config() {
		putenv( 'QIT_SELF_TEST=env_info' );
		chdir( $this->scenarios_dir . 'scenario-cli-only' );

		// SUT with no action => defaults to test.
		// One additional plugin with no action => defaults to bootstrap.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--plugin'      => [ 'extra-plugin' ], // No action specified
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_mixed_explicit_and_default_actions_in_cli_only_config() {
		putenv( 'QIT_SELF_TEST=env_info' );
		chdir( $this->scenarios_dir . 'scenario-cli-only' );

		// SUT defaults to test.
		// explicit-test-plugin explicitly set to test.
		// default-plugin no action => bootstrap.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--plugin'      => [ 'explicit-test-plugin:test', 'default-plugin' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_numeric_id_config_snapshot() {
		// This tells QIT to print out the config and return early with Command::SUCCESS.
		putenv( 'QIT_SELF_TEST=env_info' );

		// Switch to a known scenario directory. Adjust if needed.
		$fixture_dir = $this->scenarios_dir . 'scenario-cli-only';
		chdir( $fixture_dir );

		// Run the command using a numeric ID. We do not provide --source here.
		// If the bug is present, the slug in the JSON will remain "2165910" instead of a proper slug.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => '2165910', // numeric ID
			'--woo'         => '7.1',
		] );

		// The output here is the JSON of the env config. We'll snapshot it.
		// If the slug is numeric, the snapshot will contain that numeric slug.
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_numeric_id_with_source_config_snapshot() {
		// This tells QIT to print out the config and return early.
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-cli-only';
		chdir( $fixture_dir );

		// Run the command using a numeric ID and also provide a --source.
		// If the bug exists, you will again see a numeric slug in the JSON.
		// If you fix the code, rerunning the test will fail snapshot matching, indicating the slug changed.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => '2165910', // numeric ID
			'--woo'         => '7.1',
			'--source'      => './woocommerce-shipping.zip',
		] );

		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_numeric_plugin_id() {
		putenv( 'QIT_SELF_TEST=env_info' );
		chdir( $this->scenarios_dir . 'scenario-cli-only' );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--plugin'      => [ '2165910' ],
			'--woo'         => '7.1'
		] );

		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_numeric_plugin_id_with_qit_config() {
		putenv( 'QIT_SELF_TEST=env_info' );
		$fixture_dir = $this->scenarios_dir . 'scenario-numeric-plugin-id-with-qit';
		chdir( $fixture_dir );

		// Run with qit.yml defined plus a numeric plugin ID from CLI.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'woocommerce-amazon-s3-storage',
			'--woo'         => '7.1'
		] );

		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_theme_sut_with_additional_plugins() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-sut';
		$this->assertDirectoryExists( $fixture_dir );
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'storefront', // a theme SUT
			'--plugin'      => [ 'woocommerce:test:activation' ], // triggers previous issue
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_unknown_wporg_test_tag() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-unknown-wporg-test-tag';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'sut' => 'storefront',
			'--plugin'      => [ 'unknown-wporg-plugin:rc' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}
}
