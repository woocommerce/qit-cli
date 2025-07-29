<?php

namespace QIT_CLI_Tests\Environment;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\CustomTests\RunE2ECommand;
use QIT_CLI\Commands\RunActivationTestCommand;
use QIT_CLI\Environment\PluginsAndThemesParser;
use QIT_CLI\LocalTests\ConfigurationProcessor;
use QIT_CLI\ManagerSync;
use QIT_CLI\PluginDependencies;
use QIT_CLI\WooExtensionsList;
use QIT_CLI_Tests\Helpers\EnvInfoNormalizer;
use QIT_CLI_Tests\QITTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use function QIT_CLI\get_manager_url;

class RunActivationCommandTest extends QITTestCase {
	use MatchesSnapshots;
	use EnvInfoNormalizer;

	/** @var ApplicationTester */
	protected $application_tester;

	/**
	 * Assuming scenarios from the same rune2e-scenarios directory.
	 * Adjust if needed.
	 */
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
						case 'my-sut-plugin':
							return 1234;
						case 'some-dependency-plugin':
							return 1111;
						case 'woocommerce-amazon-s3-storage':
							return 54321;
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

		App::when( RunE2ECommand::class )
		   ->needs( WooExtensionsList::class )
		   ->give( $mocked_woo_extension_list );

		App::when( RunActivationTestCommand::class )
		   ->needs( WooExtensionsList::class )
		   ->give( $mocked_woo_extension_list );

		App::when( PluginsAndThemesParser::class )
		   ->needs( WooExtensionsList::class )
		   ->give( $mocked_woo_extension_list );

		// Mock the get-dependencies endpoint
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => [],
				'themes'         => [],
				'php_extensions' => [],
			] )
		);

		// Register run:activation command.
		$this->application_tester = $this->make_application_tester( static function ( Application $application ) {
			$create_run_commands = \QIT_CLI\App::make( \QIT_CLI\Commands\CreateRunCommands::class );
			$create_run_commands->register_commands( $application );
		} );
	}

	/**
	 * scenario-cli-only:
	 * No qit.yml, CLI-only configuration.
	 * Check that the SUT is bootstrapped properly.
	 */
	public function test_cli_only_config() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-cli-only';
		$this->assertDirectoryExists($fixture_dir);
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:activation',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
			'--wp'          => '6.1',
			'--source'      => './woocommerce-amazon-s3-storage',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * scenario-config-only:
	 * qit.yml present, no CLI overrides. Check bootstrap action.
	 */
	public function test_config_only() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-config-only';
		$this->assertDirectoryExists($fixture_dir);
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:activation',
			'woo_extension' => 'woocommerce-amazon-s3-storage',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * scenario-conflict-woo:
	 * Contains 'woocommerce', use that as SUT to ensure bootstrap action is applied.
	 */
	public function test_woocommerce_sut() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-conflict-woo';
		$this->assertDirectoryExists($fixture_dir);
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:activation',
			'woo_extension' => 'woocommerce', // SUT is woocommerce
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * scenario-cli-add-new-plugin:
	 * Add extra plugins via CLI. Check that SUT remains bootstrap.
	 */
	public function test_with_additional_plugins() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-cli-add-new-plugin';
		$this->assertDirectoryExists($fixture_dir);
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:activation',
			'woo_extension' => 'woocommerce-known-plugin',
			'--plugin'      => [ 'extra-plugin' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * scenario-dependencies:
	 * qit.yml + dependencies.json.
	 * Check that dependencies are handled and SUT is still bootstrap.
	 */
	public function test_scenario_with_dependencies() {
		putenv( 'QIT_SELF_TEST=env_info' );

		// Mock dependencies
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode( [
				'plugins'        => [ 'some-dependency-plugin' ],
				'themes'         => [],
				'php_extensions' => [ 'gd' ],
			] )
		);

		$fixture_dir = $this->scenarios_dir . 'scenario-dependencies';
		$this->assertDirectoryExists($fixture_dir);
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'        => 'run:activation',
			'woo_extension'  => 'woocommerce-amazon-s3-storage',
			'--dependencies_mode' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * Test the current behavior of run:activation with dependencies.
	 * We expect to see the SUT and dependencies in the final JSON config.
	 */
	public function test_activation_plugin_with_dependencies() {
		putenv( 'QIT_SELF_TEST=env_info' );

		// Mock the dependencies response only for this test.
		App::setVar(
			sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/get-dependencies' ),
			json_encode([
				'plugins'        => [ 'some-dependency-plugin' ],
				'themes'         => [],
				'php_extensions' => [],
			])
		);

		$fixture_dir = $this->scenarios_dir . 'scenario-activation-with-dependencies';
		$this->assertDirectoryExists($fixture_dir);
		chdir( $fixture_dir );

		// Non-WooCommerce SUT scenario. "my-sut-plugin" should be defined in qit.yml
		// This plugin is known by our mock WooExtensionsList (just one extra mapping):
		// Minimal additional plugin: in the existing WooExtensionsList mock in setUp(),
		// just add a line for 'my-sut-plugin' => 1234 if not already done.

		$this->application_tester->run( [
			'command'        => 'run:activation',
			'woo_extension'  => 'my-sut-plugin', // non-Woo SUT
			'--dependencies_mode' => 'bootstrap',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );

		// Expected final JSON:
		// - my-sut-plugin (SUT): action=bootstrap, test_tags=pre-activation
		// - some-dependency-plugin (from dependencies): action=bootstrap, test_tags=pre-activation
		// - woocommerce (added by RunActivationTestCommand): action=test, test_tags=activation
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	/**
	 * Test the current behavior of run:activation with additional plugins requested via CLI.
	 * This ensures we see how additional plugins are assigned actions/test_tags by default.
	 */
	public function test_activation_with_additional_plugins() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-activation-with-additional-plugins';
		$this->assertDirectoryExists($fixture_dir);
		chdir( $fixture_dir );

		// Add a plugin without explicit test_tags
		$this->application_tester->run( [
			'command'       => 'run:activation',
			'woo_extension' => 'woocommerce-known-plugin',
			'--plugin'      => [ 'some-other-plugin' ],
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function test_theme_sut_activation() {
		putenv( 'QIT_SELF_TEST=env_info' );

		$fixture_dir = $this->scenarios_dir . 'scenario-theme-activation';
		$this->assertDirectoryExists( $fixture_dir );
		chdir( $fixture_dir );

		$this->application_tester->run( [
			'command'       => 'run:activation',
			'woo_extension' => 'storefront', // This is a known theme slug from your mock.
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->normalize_env_info( json_decode( $this->application_tester->getDisplay(), true ) ) );
	}

	public function tearDown(): void {
		parent::tearDown();
		putenv( 'QIT_SELF_TEST' );
	}
}
