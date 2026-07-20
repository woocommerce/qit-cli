<?php

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Commands\CreateRunCommands;
use QIT_CLI\ManagerSync;
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

	private function make_api_fuzz_tester(): \Symfony\Component\Console\Tester\ApplicationTester {
		return $this->make_application_tester( static function ( $application ): void {
			App::make( CreateRunCommands::class )->register_commands( $application );
		} );
	}
}
