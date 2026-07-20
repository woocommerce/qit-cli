<?php

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\CreateRunCommands;
use QIT_CLI\ManagerSync;
use QIT_CLI\Zipper;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\get_manager_url;

class ApiFuzzCommandTest extends \QIT_CLI_Tests\QITTestCase {
	public function setUp(): void {
		parent::setUp();

		$cache        = App::make( Cache::class );
		$manager_sync = App::make( ManagerSync::class );
		$sync_data    = $cache->get( $manager_sync->bootstrap_cache_key );

		$this->assertIsArray( $sync_data );
		$sync_data['test_types']['api-fuzz'] = 'api-fuzz';
		$sync_data['schemas']['api-fuzz']    = [
			'title'       => 'API Fuzz Test',
			'description' => 'Enqueue authenticated beta API fuzz tests for plugins.',
			'type'        => 'object',
			'properties'  => [
				'woo_id'    => [ 'type' => 'integer', 'required' => true ],
				'upload_id' => [ 'type' => 'uuid' ],
				'client'    => [ 'type' => 'string', 'default' => 'other' ],
				'event'     => [ 'type' => 'string', 'default' => '' ],
			],
		];
		$cache->set( $manager_sync->bootstrap_cache_key, $sync_data, 60 );
	}

	public function test_command_is_registered_with_beta_help_and_without_group_option(): void {
		$application = clone $GLOBALS['qit_application'];
		App::make( CreateRunCommands::class )->register_commands( $application );

		$this->assertTrue( $application->has( 'run:api-fuzz' ) );
		$command = $application->get( 'run:api-fuzz' );

		$this->assertStringContainsString( 'authenticated beta API fuzz', $command->getDescription() );
		$this->assertStringContainsString( '2,500 generated requests', $command->getHelp() );
		$this->assertFalse( $command->getDefinition()->hasOption( 'group' ) );
	}

	public function test_command_builds_standard_slug_and_numeric_id_payloads(): void {
		putenv( 'QIT_SELF_TEST=remote_test' );

		try {
			$slug_tester = $this->make_api_fuzz_tester();
			$this->assertSame( Command::SUCCESS, $slug_tester->run( [
				'command' => 'run:api-fuzz',
				'sut'     => 'woocommerce',
				'--json'  => true,
			] ) );
			$slug_payload = json_decode( $slug_tester->getDisplay(), true );

			$id_tester = $this->make_api_fuzz_tester();
			$this->assertSame( Command::SUCCESS, $id_tester->run( [
				'command' => 'run:api-fuzz',
				'sut'     => '987654',
				'--json'  => true,
			] ) );
			$id_payload = json_decode( $id_tester->getDisplay(), true );
		} finally {
			putenv( 'QIT_SELF_TEST' );
		}

		$this->assertSame( 123456, $slug_payload['woo_id'] );
		$this->assertSame( 987654, $id_payload['woo_id'] );
		$this->assertSame( 'cli_published_extension_test', $slug_payload['event'] );
	}

	public function test_async_human_output_includes_status_and_gates_report_url(): void {
		$this->mock_enqueue_response( [
			'test_run_id'              => 4242,
			'status'                   => 'pending',
			'test_results_manager_url' => 'https://qit.test/results/4242.secret',
		] );

		try {
			$hidden_tester = $this->make_api_fuzz_tester();
			$this->assertSame( Command::SUCCESS, $hidden_tester->run( [
				'command' => 'run:api-fuzz',
				'sut'     => '123456',
				'--async' => true,
			] ) );

			$this->assertStringContainsString( 'Status: pending', $hidden_tester->getDisplay() );
			$this->assertStringNotContainsString( '4242.secret', $hidden_tester->getDisplay() );

			$visible_tester = $this->make_api_fuzz_tester();
			$this->assertSame( Command::SUCCESS, $visible_tester->run( [
				'command'            => 'run:api-fuzz',
				'sut'                => '123456',
				'--async'            => true,
				'--print-report-url' => true,
			] ) );
			$this->assertStringContainsString( '4242.secret', $visible_tester->getDisplay() );
		} finally {
			$this->clear_manager_mocks();
		}
	}

	public function test_local_zip_artifact_uses_standard_upload_payload(): void {
		$zip_path = tempnam( sys_get_temp_dir(), 'qit-api-fuzz-' );
		$this->assertNotFalse( $zip_path );
		file_put_contents( $zip_path, $this->createMinimalPluginZip( 'test-plugin', '1.0.0' ) );

		$this->mock_upload_response( 'local-zip-upload' );
		App::setVar( 'QIT_JSON_MODE', true );

		try {
			$payload = $this->run_self_test_payload( [ '--zip' => $zip_path ] );
		} finally {
			App::setVar( 'QIT_JSON_MODE', null );
			$this->clear_upload_mock();
			unlink( $zip_path );
		}

		$this->assertSame( 'local-zip-upload', $payload['upload_id'] );
		$this->assertSame( 'cli_development_extension_test', $payload['event'] );
		$this->assertArrayNotHasKey( 'zip', $payload );
	}

	public function test_directory_artifact_is_zipped_before_upload(): void {
		$directory = sys_get_temp_dir() . '/qit-api-fuzz-dir-' . uniqid();
		mkdir( $directory );
		file_put_contents( $directory . '/test-plugin.php', "<?php\n/* Plugin Name: Test Plugin */\n" );

		$original_zipper = App::make( Zipper::class );
		$zipper          = $this->createMock( Zipper::class );
		$zipper->expects( $this->once() )
			->method( 'zip_directory' )
			->with( $directory, $this->isType( 'string' ) )
			->willReturnCallback( function ( string $source, string $destination ): void {
				file_put_contents( $destination, $this->createMinimalPluginZip( 'test-plugin', '1.0.0' ) );
			} );
		App::singleton( Zipper::class, $zipper );

		$this->mock_upload_response( 'directory-upload' );
		App::setVar( 'QIT_JSON_MODE', true );

		try {
			$payload = $this->run_self_test_payload( [ '--zip' => $directory ] );
		} finally {
			App::singleton( Zipper::class, $original_zipper );
			App::setVar( 'QIT_JSON_MODE', null );
			$this->clear_upload_mock();
			unlink( $directory . '/test-plugin.php' );
			rmdir( $directory );
		}

		$this->assertSame( 'directory-upload', $payload['upload_id'] );
		$this->assertSame( 'cli_development_extension_test', $payload['event'] );
	}

	public function test_remote_zip_artifact_is_downloaded_before_upload(): void {
		$url = 'https://example.test/test-plugin.zip';
		App::setVar( 'mock_' . $url, $this->createMinimalPluginZip( 'test-plugin', '1.0.0' ) );
		$this->mock_upload_response( 'remote-zip-upload' );
		App::setVar( 'QIT_JSON_MODE', true );

		try {
			$payload = $this->run_self_test_payload( [ '--zip' => $url ] );
		} finally {
			App::setVar( 'mock_' . $url, null );
			App::setVar( 'QIT_JSON_MODE', null );
			$this->clear_upload_mock();
		}

		$this->assertSame( 'remote-zip-upload', $payload['upload_id'] );
		$this->assertSame( 'cli_development_extension_test', $payload['event'] );
	}

	public function test_sync_json_decodes_results_and_preserves_campaign_state(): void {
		$this->mock_enqueue_response( [ 'test_run_id' => 4242, 'status' => 'pending' ] );
		$this->mock_get_response( [
			'test_run_id'     => 4242,
			'test_type'       => 'api-fuzz',
			'update_complete' => true,
			'status'          => 'warning',
			'test_result_json' => json_encode( [
				'campaign' => [ 'state' => 'partial' ],
				'findings' => [],
			] ),
		] );

		try {
			$tester = $this->make_api_fuzz_tester();
			$this->assertSame( 3, $tester->run( [
				'command' => 'run:api-fuzz',
				'sut'     => '123456',
				'--json'  => true,
			] ) );
			$output = json_decode( $tester->getDisplay(), true );
		} finally {
			$this->clear_manager_mocks();
		}

		$this->assertIsArray( $output['test_result_json'] );
		$this->assertSame( 'partial', $output['test_result_json']['campaign']['state'] );
	}

	public function test_async_json_decodes_existing_results_and_retains_report_url(): void {
		$this->mock_enqueue_response( [
			'test_run_id'              => 4242,
			'status'                   => 'pending',
			'test_results_manager_url' => 'https://qit.test/results/4242.secret',
			'test_result_json'         => json_encode( [ 'campaign' => [ 'state' => 'partial' ] ] ),
		] );

		try {
			$tester = $this->make_api_fuzz_tester();
			$this->assertSame( Command::SUCCESS, $tester->run( [
				'command' => 'run:api-fuzz',
				'sut'     => '123456',
				'--async' => true,
				'--json'  => true,
			] ) );
			$output = json_decode( $tester->getDisplay(), true );
		} finally {
			$this->clear_manager_mocks();
		}

		$this->assertSame( 'pending', $output['status'] );
		$this->assertSame( 'partial', $output['test_result_json']['campaign']['state'] );
		$this->assertSame( 'https://qit.test/results/4242.secret', $output['test_results_manager_url'] );
	}

	public function test_cancelled_run_is_terminal_and_fails(): void {
		$this->mock_enqueue_response( [ 'test_run_id' => 4242, 'status' => 'pending' ] );
		$this->mock_get_response( [
			'test_run_id'     => 4242,
			'test_type'       => 'api-fuzz',
			'update_complete' => false,
			'status'          => 'cancelled',
		] );

		try {
			$tester    = $this->make_api_fuzz_tester();
			$exit_code = $tester->run( [
				'command' => 'run:api-fuzz',
				'sut'     => '123456',
			] );
		} finally {
			$this->clear_manager_mocks();
		}

		$this->assertSame( Command::FAILURE, $exit_code );
		$this->assertStringContainsString( 'cancelled', $tester->getDisplay() );
	}

	public function test_unavailable_campaign_fails_through_manager_lifecycle_status(): void {
		$this->mock_enqueue_response( [ 'test_run_id' => 4242, 'status' => 'pending' ] );
		$this->mock_get_response( [
			'test_run_id'     => 4242,
			'test_type'       => 'api-fuzz',
			'update_complete' => true,
			'status'          => 'failed',
			'test_result_json' => json_encode( [
				'campaign' => [ 'state' => 'unavailable' ],
				'findings' => [],
			] ),
		] );

		try {
			$tester = $this->make_api_fuzz_tester();
			$this->assertSame( Command::FAILURE, $tester->run( [
				'command' => 'run:api-fuzz',
				'sut'     => '123456',
			] ) );
		} finally {
			$this->clear_manager_mocks();
		}

		$this->assertStringContainsString( 'Status: failed', $tester->getDisplay() );
		$this->assertStringContainsString( 'Campaign State: unavailable', $tester->getDisplay() );
	}

	public function test_human_output_separates_lifecycle_and_campaign_state(): void {
		$this->mock_enqueue_response( [ 'test_run_id' => 4242, 'status' => 'pending' ] );
		$this->mock_get_response( [
			'test_run_id'     => 4242,
			'test_type'       => 'api-fuzz',
			'update_complete' => true,
			'status'          => 'warning',
			'test_result_json' => json_encode( [
				'campaign' => [ 'state' => 'not_applicable' ],
				'findings' => [],
			] ),
		] );

		try {
			$tester = $this->make_api_fuzz_tester();
			$this->assertSame( 3, $tester->run( [
				'command' => 'run:api-fuzz',
				'sut'     => '123456',
			] ) );
		} finally {
			$this->clear_manager_mocks();
		}

		$this->assertStringContainsString( 'Status: warning', $tester->getDisplay() );
		$this->assertStringContainsString( 'Campaign State: not_applicable', $tester->getDisplay() );
	}

	/** @param array<string,mixed> $response */
	private function mock_enqueue_response( array $response ): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/enqueue-api-fuzz' ),
			json_encode( $response )
		);
	}

	/** @param array<string,mixed> $response */
	private function mock_get_response( array $response ): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ),
			json_encode( $response )
		);
	}

	private function clear_manager_mocks(): void {
		App::setVar( sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/enqueue-api-fuzz' ), null );
		App::setVar( sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get-single' ), null );
	}

	private function mock_upload_response( string $upload_id ): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/upload-build' ),
			json_encode( [ 'upload_id' => $upload_id ] )
		);
	}

	private function clear_upload_mock(): void {
		App::setVar( sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/upload-build' ), null );
	}

	/**
	 * @param array<string,mixed> $options Additional CLI options.
	 * @return array<string,mixed>
	 */
	private function run_self_test_payload( array $options ): array {
		putenv( 'QIT_SELF_TEST=remote_test' );
		try {
			$tester    = $this->make_api_fuzz_tester();
			$exit_code = $tester->run( array_merge( [
				'command' => 'run:api-fuzz',
				'sut'     => '123456',
				'--json'  => true,
			], $options ) );
		} finally {
			putenv( 'QIT_SELF_TEST' );
		}

		$this->assertSame( Command::SUCCESS, $exit_code, $tester->getDisplay() );
		$payload = json_decode( $tester->getDisplay(), true );
		$this->assertIsArray( $payload );

		return $payload;
	}

	private function make_api_fuzz_tester(): \Symfony\Component\Console\Tester\ApplicationTester {
		return $this->make_application_tester( static function ( $application ): void {
			App::make( CreateRunCommands::class )->register_commands( $application );
		} );
	}
}
