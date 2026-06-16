<?php

namespace QIT_CLI_Tests;

use QIT_CLI\Commands\ExtensionSetTrait;
use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\ManagerSync;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Guards the locally-handled managed tests (run:activation, run:woo-api,
 * run:woo-e2e) against losing `--extension_set`.
 *
 * These commands were converted from Manager schema-driven commands (which
 * exposed --extension_set automatically) to static classes extending
 * RunE2ECommand. They are now in CreateRunCommands' $ignored_test_types, so
 * the option must be declared on each command and resolved CLI-side via
 * ExtensionSetTrait. run:woo-api and run:woo-e2e previously regressed here.
 */
class RunManagedTestExtensionSetTest extends QITTestCase {

	/**
	 * @return array<string,array{string}>
	 */
	public function extension_set_command_provider(): array {
		return [
			'activation' => [ 'run:activation' ],
			'woo-api'    => [ 'run:woo-api' ],
			'woo-e2e'    => [ 'run:woo-e2e' ],
		];
	}

	/**
	 * @dataProvider extension_set_command_provider
	 */
	public function test_command_exposes_extension_set_option( string $command_name ): void {
		$command = $GLOBALS['qit_application']->find( $command_name );

		$this->assertTrue(
			$command->getDefinition()->hasOption( 'extension_set' ),
			sprintf( '%s must expose the --extension_set option.', $command_name )
		);
	}

	public function test_resolves_extension_set_into_plugins(): void {
		$input  = $this->make_input( [ '--extension_set' => 'test-set' ] );
		$output = new BufferedOutput();

		$result = $this->make_resolver()->resolve( $input, $output );

		$this->assertNull( $result );
		$this->assertSame( [ 'wccom-plugin-4', 'wccom-plugin-5' ], $input->getOption( 'plugin' ) );
	}

	public function test_extension_set_merges_with_explicit_plugins_without_duplicates(): void {
		$input = $this->make_input( [
			'--extension_set' => 'test-set',
			'--plugin'        => [ 'my-plugin', 'wccom-plugin-4' ],
		] );

		$result = $this->make_resolver()->resolve( $input, new BufferedOutput() );

		$this->assertNull( $result );
		$this->assertSame(
			[ 'my-plugin', 'wccom-plugin-4', 'wccom-plugin-5' ],
			$input->getOption( 'plugin' )
		);
	}

	public function test_no_extension_set_leaves_plugins_untouched(): void {
		$input = $this->make_input( [ '--plugin' => [ 'my-plugin' ] ] );

		$result = $this->make_resolver()->resolve( $input, new BufferedOutput() );

		$this->assertNull( $result );
		$this->assertSame( [ 'my-plugin' ], $input->getOption( 'plugin' ) );
	}

	public function test_unknown_extension_set_returns_invalid(): void {
		$input  = $this->make_input( [ '--extension_set' => 'does-not-exist' ] );
		$output = new BufferedOutput();

		$result = $this->make_resolver()->resolve( $input, $output );

		$this->assertSame( Command::INVALID, $result );
		$this->assertStringContainsString( 'Unknown extension set "does-not-exist"', $output->fetch() );
	}

	public function test_activation_extension_set_uses_remote_payload(): void {
		$options = $this->run_remote_payload_command( [
			'command'         => 'run:activation',
			'sut'             => 'woocommerce',
			'--extension_set' => 'test-set',
			'--json'          => true,
		] );

		$this->assertSame( 'test-set', $options['extension_set'] );
		$this->assertSame( 123456, $options['woo_id'] );
		$this->assertSame( 'cli_published_extension_test', $options['event'] );
		$this->assertArrayNotHasKey( 'plugin', $options );
	}

	public function test_activation_extension_set_with_zip_uses_development_payload(): void {
		$zip_path = $this->write_plugin_zip();
		$this->mock_upload_response( 'upload-activation' );
		App::setVar( 'QIT_JSON_MODE', true );

		try {
			$options = $this->run_remote_payload_command( [
				'command'         => 'run:activation',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--zip'           => $zip_path,
				'--json'          => true,
			] );
		} finally {
			App::setVar( 'QIT_JSON_MODE', null );
			unlink( $zip_path );
		}

		$this->assertSame( 'test-set', $options['extension_set'] );
		$this->assertSame( 123456, $options['woo_id'] );
		$this->assertSame( 'upload-activation', $options['upload_id'] );
		$this->assertSame( 'cli_development_extension_test', $options['event'] );
		$this->assertArrayNotHasKey( 'zip', $options );
	}

	public function test_woo_api_extension_set_uses_remote_payload(): void {
		$options = $this->run_remote_payload_command( [
			'command'         => 'run:woo-api',
			'sut'             => 'woocommerce',
			'--extension_set' => 'test-set',
			'--json'          => true,
		] );

		$this->assertSame( 'test-set', $options['extension_set'] );
		$this->assertSame( 123456, $options['woo_id'] );
		$this->assertSame( 'cli_published_extension_test', $options['event'] );
		$this->assertArrayNotHasKey( 'plugin', $options );
	}

	public function test_woo_api_extension_set_allows_schema_options(): void {
		$options = $this->run_remote_payload_command( [
			'command'             => 'run:woo-api',
			'sut'                 => 'woocommerce',
			'--extension_set'     => 'test-set',
			'--optional_features' => [ 'hpos' ],
			'--json'              => true,
		] );

		$this->assertSame( [ 'hpos' ], $options['optional_features'] );
	}

	public function test_woo_api_extension_set_with_zip_uses_development_payload(): void {
		$zip_path = $this->write_plugin_zip();
		$this->mock_upload_response( 'upload-woo-api' );
		App::setVar( 'QIT_JSON_MODE', true );

		try {
			$options = $this->run_remote_payload_command( [
				'command'         => 'run:woo-api',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--zip'           => $zip_path,
				'--json'          => true,
			] );
		} finally {
			App::setVar( 'QIT_JSON_MODE', null );
			unlink( $zip_path );
		}

		$this->assertSame( 'test-set', $options['extension_set'] );
		$this->assertSame( 123456, $options['woo_id'] );
		$this->assertSame( 'upload-woo-api', $options['upload_id'] );
		$this->assertSame( 'cli_development_extension_test', $options['event'] );
		$this->assertArrayNotHasKey( 'zip', $options );
	}

	public function test_extension_set_announces_remote_mode_for_human_output(): void {
		putenv( 'QIT_SELF_TEST=remote_test' );
		try {
			$application = $this->make_application_tester();
			$exit_code   = $application->run( [
				'command'         => 'run:woo-api',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
			] );
		} finally {
			putenv( 'QIT_SELF_TEST' );
		}

		$this->assertSame( Command::SUCCESS, $exit_code, $application->getDisplay() );
		$this->assertStringContainsString( '--extension_set detected; running this managed test on QIT servers.', $application->getDisplay() );
	}

	public function test_woo_e2e_extension_set_uses_remote_payload(): void {
		$options = $this->run_remote_payload_command( [
			'command'         => 'run:woo-e2e',
			'sut'             => 'woocommerce',
			'--extension_set' => 'test-set',
			'--json'          => true,
		] );

		$this->assertSame( 'test-set', $options['extension_set'] );
		$this->assertSame( 123456, $options['woo_id'] );
		$this->assertSame( 'cli_published_extension_test', $options['event'] );
		$this->assertArrayNotHasKey( 'plugin', $options );
	}

	public function test_woo_e2e_extension_set_with_zip_uses_development_payload(): void {
		$zip_path = $this->write_plugin_zip();
		$this->mock_upload_response( 'upload-woo-e2e' );
		App::setVar( 'QIT_JSON_MODE', true );

		try {
			$options = $this->run_remote_payload_command( [
				'command'         => 'run:woo-e2e',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--zip'           => $zip_path,
				'--json'          => true,
			] );
		} finally {
			App::setVar( 'QIT_JSON_MODE', null );
			unlink( $zip_path );
		}

		$this->assertSame( 'test-set', $options['extension_set'] );
		$this->assertSame( 123456, $options['woo_id'] );
		$this->assertSame( 'upload-woo-e2e', $options['upload_id'] );
		$this->assertSame( 'cli_development_extension_test', $options['event'] );
		$this->assertArrayNotHasKey( 'zip', $options );
	}

	public function test_woo_api_extension_set_uses_explicit_environment_config(): void {
		$config_path = $this->write_config( [
			'environments' => [
				'remote-env' => [
					'wordpress_version' => '6.6.1',
					'php_version'       => '8.3',
				],
			],
			'test_types'   => [
				'woo-api' => [
					'default' => [],
				],
			],
		] );

		try {
			$options = $this->run_remote_payload_command( [
				'command'         => 'run:woo-api',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--environment'   => 'remote-env',
				'--config'        => $config_path,
				'--json'          => true,
			] );
		} finally {
			unlink( $config_path );
		}

		$this->assertSame( '6.6.1', $options['wordpress_version'] );
		$this->assertSame( '8.3', $options['php_version'] );
	}

	public function test_woo_e2e_extension_set_prefers_woo_e2e_profile(): void {
		$config_path = $this->write_config( [
			'test_types' => [
				'e2e'     => [
					'default' => [
						'wordpress_version' => '6.5.5',
					],
				],
				'woo-e2e' => [
					'default' => [
						'wordpress_version' => '6.4.5',
					],
				],
			],
		] );

		try {
			$options = $this->run_remote_payload_command( [
				'command'         => 'run:woo-e2e',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--config'        => $config_path,
				'--json'          => true,
			] );
		} finally {
			unlink( $config_path );
		}

		$this->assertSame( '6.4.5', $options['wordpress_version'] );
	}

	public function test_woo_e2e_extension_set_falls_back_to_e2e_profile(): void {
		$config_path = $this->write_config( [
			'test_types' => [
				'e2e' => [
					'default' => [
						'wordpress_version' => '6.5.5',
					],
				],
			],
		] );

		try {
			$options = $this->run_remote_payload_command( [
				'command'         => 'run:woo-e2e',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--config'        => $config_path,
				'--json'          => true,
			] );
		} finally {
			unlink( $config_path );
		}

		$this->assertSame( '6.5.5', $options['wordpress_version'] );
	}

	public function test_woo_api_extension_set_rejects_local_only_options(): void {
		putenv( 'QIT_SELF_TEST=remote_test' );
		try {
			$application = $this->make_application_tester();
			$exit_code   = $application->run( [
				'command'         => 'run:woo-api',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--test-package'  => [ 'woocommerce/core-api-tests:latest' ],
				'--json'          => true,
			] );
		} finally {
			putenv( 'QIT_SELF_TEST' );
		}

		$this->assertSame( Command::INVALID, $exit_code );
		$this->assertStringContainsString( 'local-only option', $application->getDisplay() );
		$this->assertStringContainsString( '--test-package', $application->getDisplay() );
	}

	public function test_activation_extension_set_rejects_local_only_options(): void {
		putenv( 'QIT_SELF_TEST=remote_test' );
		try {
			$application = $this->make_application_tester();
			$exit_code   = $application->run( [
				'command'         => 'run:activation',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--test-package'  => [ 'woocommerce/activation:latest' ],
				'--json'          => true,
			] );
		} finally {
			putenv( 'QIT_SELF_TEST' );
		}

		$this->assertSame( Command::INVALID, $exit_code );
		$this->assertStringContainsString( 'local-only option', $application->getDisplay() );
		$this->assertStringContainsString( '--test-package', $application->getDisplay() );
	}

	public function test_woo_api_extension_set_rejects_unknown_remote_options(): void {
		putenv( 'QIT_SELF_TEST=remote_test' );
		try {
			$application = $this->make_application_tester();
			$exit_code   = $application->run( [
				'command'         => 'run:woo-api',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--notify'        => true,
				'--json'          => true,
			] );
		} finally {
			putenv( 'QIT_SELF_TEST' );
		}

		$this->assertSame( Command::INVALID, $exit_code );
		$this->assertStringContainsString( 'local-only option', $application->getDisplay() );
		$this->assertStringContainsString( '--notify', $application->getDisplay() );
	}

	public function test_woo_api_extension_set_fails_when_remote_schema_is_missing(): void {
		$cache              = App::make( Cache::class );
		$manager_sync       = App::make( ManagerSync::class );
		$bootstrap_key      = $manager_sync->get_cache_key_for( 'schemas' );
		$original_bootstrap = $cache->get( $bootstrap_key );

		$this->assertIsArray( $original_bootstrap );

		$mutated_bootstrap = $original_bootstrap;
		unset( $mutated_bootstrap['schemas']['woo-api'] );
		$cache->set( $bootstrap_key, $mutated_bootstrap, 0 );

		putenv( 'QIT_SELF_TEST=remote_test' );
		try {
			$application = $this->make_application_tester();
			$exit_code   = $application->run( [
				'command'         => 'run:woo-api',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
				'--json'          => true,
			] );
		} finally {
			$cache->set( $bootstrap_key, $original_bootstrap, 0 );
			putenv( 'QIT_SELF_TEST' );
		}

		$this->assertSame( Command::FAILURE, $exit_code );
		$this->assertStringContainsString( 'Could not load Manager schema for test type "woo-api"', $application->getDisplay() );
	}

	/**
	 * The completion summary must keep distinguishing success from failure.
	 * (Guards against the refactor collapsing both into a generic
	 * "Test completed" message. ApplicationTester output is not a console
	 * section, so this exercises the non-interactive branch, which shares the
	 * same status switch as the interactive one.)
	 */
	public function test_remote_run_completion_reports_success(): void {
		$display = $this->run_remote_waiting_command( 'success' );

		$this->assertStringContainsString( 'Test completed.', $display );
		$this->assertStringContainsString( 'success', $display );
	}

	public function test_remote_run_completion_reports_failure(): void {
		$display = $this->run_remote_waiting_command( 'failed', Command::FAILURE );

		$this->assertStringContainsString( 'Test completed.', $display );
		$this->assertStringContainsString( 'failed', $display );
	}

	/**
	 * Drive run:woo-api through the live enqueue + poll path (no QIT_SELF_TEST
	 * short-circuit) with mocked Manager responses, and return the display.
	 */
	private function run_remote_waiting_command( string $status, int $expected_exit = Command::SUCCESS ): string {
		$test_run_id = 4242;

		App::setVar(
			sprintf( 'mock_%s%s', \QIT_CLI\get_manager_url(), '/wp-json/cd/v1/enqueue-woo-api' ),
			json_encode( [ 'test_run_id' => $test_run_id ] )
		);
		App::setVar(
			sprintf( 'mock_%s%s', \QIT_CLI\get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( [
				'test_run_id'     => $test_run_id,
				'update_complete' => true,
				'status'          => $status,
			] )
		);

		try {
			$application = $this->make_application_tester( function ( $application ) {
				$application->add( App::make( \QIT_CLI\Commands\RunWooApiTestCommand::class ) );
			} );
			$exit_code   = $application->run( [
				'command'         => 'run:woo-api',
				'sut'             => 'woocommerce',
				'--extension_set' => 'test-set',
			] );
		} finally {
			App::setVar( sprintf( 'mock_%s%s', \QIT_CLI\get_manager_url(), '/wp-json/cd/v1/enqueue-woo-api' ), null );
			App::setVar( sprintf( 'mock_%s%s', \QIT_CLI\get_manager_url(), '/wp-json/cd/v1/get-single' ), null );
		}

		$this->assertSame( $expected_exit, $exit_code, $application->getDisplay() );

		return $application->getDisplay();
	}

	/**
	 * A minimal object that exposes the trait's protected resolver for testing.
	 */
	private function make_resolver(): object {
		return new class() {
			use ExtensionSetTrait;

			public function resolve( QITInput $input, BufferedOutput $output ): ?int {
				return $this->resolve_extension_set( $input, $output );
			}
		};
	}

	/**
	 * @param array<string,mixed> $cli_options
	 */
	private function make_input( array $cli_options ): QITInput {
		$definition = new InputDefinition();
		$definition->addOption( new InputOption( 'extension_set', null, InputOption::VALUE_OPTIONAL ) );
		$definition->addOption( new InputOption( 'plugin', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '', [] ) );

		return new QITInput( new ArrayInput( $cli_options, $definition ), [], 'e2e' );
	}

	/**
	 * @param array<string,mixed> $input
	 * @return array<string,mixed>
	 */
	private function run_remote_payload_command( array $input ): array {
		putenv( 'QIT_SELF_TEST=remote_test' );
		try {
			$application = $this->make_application_tester( function ( $application ) use ( $input ) {
				if ( ( $input['command'] ?? '' ) === 'run:activation' ) {
					$application->add( App::make( \QIT_CLI\Commands\RunActivationTestCommand::class ) );
				}

				if ( ( $input['command'] ?? '' ) === 'run:woo-api' ) {
					$application->add( App::make( \QIT_CLI\Commands\RunWooApiTestCommand::class ) );
				}

				if ( ( $input['command'] ?? '' ) === 'run:woo-e2e' ) {
					$application->add( App::make( \QIT_CLI\Commands\RunWooE2ETestCommand::class ) );
				}
			} );
			$exit_code   = $application->run( $input );
		} finally {
			putenv( 'QIT_SELF_TEST' );
		}

		$this->assertSame( Command::SUCCESS, $exit_code, $application->getDisplay() );

		$decoded = json_decode( $application->getDisplay(), true );
		$this->assertIsArray( $decoded );

		return $decoded;
	}

	/**
	 * @param array<string,mixed> $config
	 */
	private function write_config( array $config ): string {
		$config_path = tempnam( sys_get_temp_dir(), 'qit_config_' );
		if ( $config_path === false ) {
			$this->fail( 'Could not create temporary qit config.' );
		}

		file_put_contents( $config_path, json_encode( $config ) );

		return $config_path;
	}

	private function mock_upload_response( string $upload_id ): void {
		App::setVar(
			sprintf( 'mock_%s%s', \QIT_CLI\get_manager_url(), '/wp-json/cd/v1/upload-build' ),
			json_encode( [ 'upload_id' => $upload_id ] )
		);
	}

	private function write_plugin_zip(): string {
		$zip_path = tempnam( sys_get_temp_dir(), 'qit_plugin_zip_' );
		if ( $zip_path === false ) {
			$this->fail( 'Could not create temporary plugin zip.' );
		}

		$written = file_put_contents( $zip_path, $this->createMinimalPluginZip( 'test-plugin', '1.0.0' ) );
		if ( $written === false ) {
			$this->fail( 'Could not write temporary plugin zip.' );
		}

		return $zip_path;
	}
}
