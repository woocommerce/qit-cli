<?php

namespace QIT_CLI_Tests\Environment;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use QIT_CLI\ManagerSync;
use QIT_CLI\WooExtensionsList;
use QIT_CLI_Tests\QITTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;
use function QIT_CLI\get_manager_url;

class RunE2ECommandTest extends QITTestCase {
	use MatchesSnapshots;

	/** @var ApplicationTester */
	protected $application_tester;

	protected $scenarios_dir = __DIR__ . '/../data/rune2e-scenarios/';

	public function setUp(): void {
		parent::setUp();

		// Before instantiating RunE2ECommand, override the WooExtensionsList dependency:
		App::when( RunE2ECommand::class )
		   ->needs( WooExtensionsList::class )
		   ->give( function () {
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
		   } );

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
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-cli-only';
		chdir( $fixture_dir );

		// Using a known extension: "woocommerce-amazon-s3-storage"
		// Ensure the scenario-cli-only directory has a ./woocommerce-amazon-s3-storage plugin directory and tests.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--source'      => './woocommerce-amazon-s3-storage', // Local source
			'--woo'         => '7.1',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * scenario-config-only:
	 * qit.yml present, no CLI overrides.
	 * Using woocommerce-amazon-s3-storage as known extension.
	 */
	public function test_config_only() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-config-only';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * scenario-config-overrides:
	 * qit.yml present, CLI overrides source and test_tags.
	 * Pass test as second argument.
	 */
	public function test_config_with_cli_overrides() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-config-overrides';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'test'          => 'new-test-tag',
			'--source'      => './overridden-source',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * scenario-conflict-woo:
	 * Triggering conflict by adding --plugin=woocommerce along with --woo.
	 */
	public function test_conflict_woo() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-conflict-woo';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
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
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-dependencies';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'        => 'run:e2e',
			'woo_extension'  => 'woocommerce-amazon-s3-storage',
			'--dependencies' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * scenario-env-vars:
	 * Run with --env and ensure env vars appear in final config.
	 */
	public function test_env_vars() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-env-vars';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--env'         => [ 'FOO=bar' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * scenario-missing-test-tags:
	 * qit.yml defines plugin but no test_tags, defaults to ['default'].
	 */
	public function test_missing_test_tags() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-missing-test-tags';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * scenario-sharding:
	 * Multiple tests, run with --shard=1/2.
	 */
	public function test_sharding() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-sharding';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--shard'       => '1/2',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * scenario-ui-codegen:
	 * Run with --ui.
	 */
	public function test_ui_mode() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-ui-codegen';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--ui'          => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	/**
	 * scenario-ui-codegen:
	 * Run with --codegen.
	 */
	public function test_codegen_mode() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-ui-codegen';
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--codegen'     => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_with_plugin_dependencies_cli_only() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		// Mock plugin dependencies
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), json_encode( [
			'plugins'        => [ 'woocommerce-gateway-stripe', 'woocommerce-subscriptions' ],
			'themes'         => [],
			'php_extensions' => [],
		] ) );

		$this->application_tester->run( [
			'command'        => 'run:e2e',
			'woo_extension'  => 'woocommerce-amazon-s3-storage',
			'--source'       => './woocommerce-amazon-s3-storage', // CLI-only, local dir
			'--dependencies' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_with_theme_dependencies_cli_only() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		// Mock theme dependencies
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), json_encode( [
			'plugins'        => [ 'woocommerce-services' ],
			'themes'         => [ 'storefront' ],
			'php_extensions' => [],
		] ) );

		$this->application_tester->run( [
			'command'        => 'run:e2e',
			'woo_extension'  => 'woocommerce-amazon-s3-storage',
			'--source'       => './woocommerce-amazon-s3-storage',
			'--dependencies' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_with_php_extensions_cli_only() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		// Mock PHP extensions dependencies
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), json_encode( [
			'plugins'        => [],
			'themes'         => [],
			'php_extensions' => [ 'intl', 'soap' ],
		] ) );

		$this->application_tester->run( [
			'command'        => 'run:e2e',
			'woo_extension'  => 'woocommerce-amazon-s3-storage',
			'--source'       => './woocommerce-amazon-s3-storage',
			'--dependencies' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_with_valid_tunnel_cli_only() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--source'      => './woocommerce-amazon-s3-storage',
			'--tunnel'      => 'cloudflared-binary',
			'--json'        => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}


	public function test_with_woo_version_cli_only() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		// No additional mocks needed if no dependencies required.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--source'      => './woocommerce-amazon-s3-storage',
			'--woo'         => '6.0',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}


	public function tearDown(): void {
		parent::tearDown();
		putenv( 'QIT_TESTING_ENV_CONFIG' );
	}

	public function test_cli_override_dependencies() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );
		chdir( $this->scenarios_dir . 'scenario-cli-override-dependencies' );

		// Mock dependencies to check they are merged and overridden by CLI.
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ), json_encode( [
			'plugins'        => [ 'woocommerce-services' ],
			'themes'         => [],
			'php_extensions' => [],
		] ) );

		// qit.yml sets action=test. We'll override it to bootstrap via CLI.
		$this->application_tester->run( [
			'command'        => 'run:e2e',
			'woo_extension'  => 'woocommerce-amazon-s3-storage', // SUT
			'-p'             => [ 'woocommerce-sample-plugin:bootstrap' ],
			'--dependencies' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_cli_add_new_plugin() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );
		chdir( $this->scenarios_dir . 'scenario-cli-add-new-plugin' );

		// qit.yml defines woocommerce-known-plugin only.
		// We'll add a new plugin via CLI that isn't in qit.yml.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--plugin'      => [ 'woocommerce-new-plugin:bootstrap' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_cli_default_action() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );
		chdir( $this->scenarios_dir . 'scenario-cli-default-action' );

		// qit.yml sets no action for woocommerce-no-action.
		// We'll just specify the plugin in CLI with no action. It should default to 'test'.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--plugin'      => [ 'woocommerce-no-action' ], // no action specified
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_multiple_test_tags_from_config_and_cli() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		// Change to the new scenario directory.
		$fixture_dir = $this->scenarios_dir . 'scenario-multiple-test-tags';
		chdir( $fixture_dir );

		// We pass the second argument "test" as comma-separated tags:
		// "self-test-multiple-test-tags,self-test-multiple-test-tags-another"
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'test'          => 'self-test-multiple-test-tags,self-test-multiple-test-tags-another',
		], [ 'capture_stderr_separately' => true ] );

		// Initially, if the code doesn't handle splitting comma-separated test tags,
		// it might fail or produce incorrect JSON.
		// After you implement the fix to split comma-separated tags,
		// this should pass and match the snapshot.
		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_additional_plugin_multiple_test_tags() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-additional-plugin-multiple-test-tags';
		chdir( $fixture_dir );

		// The SUT test tags come via CLI as comma-separated:
		// "self-test-multiple-test-tags,self-test-multiple-test-tags-another"
		// The additional plugin is also given via CLI, with :test: plus comma-separated tags:
		// "woocommerce-progressive-discounts:test:self-test-extra-tag,self-test-extra-tag-2"
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'test'          => 'self-test-multiple-test-tags,self-test-multiple-test-tags-another',
			'--plugin'      => [
				'woocommerce-progressive-discounts:test:self-test-extra-tag,self-test-extra-tag-2',
			],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_theme_sut() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-sut';
		chdir( $fixture_dir );

		// We know qit.yml defines the theme "storefront" as SUT.
		// We'll run run:e2e with woo_extension = 'storefront'.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'storefront', // This should now be detected as a theme SUT.
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_plugin_and_theme_sut_theme() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-plugin-and-theme-sut';
		chdir( $fixture_dir );

		// qit.yml has both plugins and themes, we pick 'deli-theme' as SUT.
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'deli-theme',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_theme_with_dependencies() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-with-dependencies';
		chdir( $fixture_dir );

		// storefront is the theme SUT, we run with --dependencies=bootstrap to apply dependencies
		$this->application_tester->run( [
			'command'        => 'run:e2e',
			'woo_extension'  => 'storefront',
			'--dependencies' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_theme_with_additional_plugin() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-with-additional-plugin';
		chdir( $fixture_dir );

		// deli-theme from qit.yml, add plugin via CLI
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'deli-theme',
			'--plugin'      => [ 'woocommerce-extra-plugin:bootstrap' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_theme_with_cli_source_override() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-with-cli-source';
		chdir( $fixture_dir );

		// boutique in qit.yml, override source via CLI
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'boutique',
			'--source'      => './custom-boutique-theme',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_theme_only_config_no_cli() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-only-config-no-cli';
		chdir( $fixture_dir );

		// hestia defined in qit.yml, no CLI overrides
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'hestia',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_theme_and_plugin_both_config() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-and-plugin-both-config';
		chdir( $fixture_dir );

		// twentytwentyone is the theme in qit.yml, choose it as SUT
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'twentytwentyone',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}

	public function test_theme_with_tunnel() {
		putenv( 'QIT_TESTING_ENV_CONFIG=1' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-with-tunnel';
		chdir( $fixture_dir );

		// blocksy defined in qit.yml, run with a tunnel
		$this->application_tester->run( [
			'command'       => 'run:e2e',
			'woo_extension' => 'blocksy',
			'--tunnel'      => 'cloudflared-binary',
			'--json'        => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->application_tester->getDisplay() );
	}
}
