<?php

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\ManagerSync;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * The CLI reads the payload the Manager actually produces.
 *
 * data/e2e-sync-from-manager.json was captured from cd/v1/cli/sync running the
 * Manager against a registry holding core-e2e-tests 10.0, 11.0 and 11.1, and
 * activation 10.9, 11.0, 11.1 and latest. Both halves of the contract are real;
 * only the transport is skipped.
 */
class ContractWithManagerTest extends \QIT_CLI_Tests\QITTestCase {
	public function setUp(): void {
		parent::setUp();

		$captured = json_decode( (string) file_get_contents( __DIR__ . '/data/e2e-sync-from-manager.json' ), true );

		$this->assertIsArray( $captured );

		$cache        = App::make( Cache::class );
		$manager_sync = App::make( ManagerSync::class );
		$sync_data    = $cache->get( $manager_sync->bootstrap_cache_key );

		$sync_data['test_package_versions'] = $captured['test_package_versions'];
		$sync_data['versions']              = $captured['versions'];

		$cache->set( $manager_sync->bootstrap_cache_key, $sync_data, 60 );
	}

	private function select( ?string $woocommerce_version, string $command = ExposedRunWooE2ETestCommand::class ): string {
		$env_options = $woocommerce_version === null
			? [ '--environment' => 'default' ]
			: [ '--environment' => 'default', '--woocommerce_version' => $woocommerce_version ];

		$input = $this->createMock( QITInput::class );
		$input->method( 'get_environment_options' )->willReturn( $env_options );
		$input->method( 'get_environment_config' )->willReturn( [] );

		return App::make( $command )->resolve_test_package_for_test( $input, new BufferedOutput() );
	}

	/**
	 * @return array<string, array<int, string|null>>
	 */
	public function selection_cases(): array {
		return [
			'patch release'              => [ '11.0.1', 'woocommerce/core-e2e-tests:11.0' ],
			'prerelease of its own line' => [ '11.1.0-rc.1', 'woocommerce/core-e2e-tests:11.1' ],
			'historical, published'      => [ '10.0.0', 'woocommerce/core-e2e-tests:10.0' ],
			'between published versions' => [ '10.9.4', 'woocommerce/core-e2e-tests:latest' ],
			'newer than anything'        => [ '11.2.0', 'woocommerce/core-e2e-tests:latest' ],
			'older than everything'      => [ '9.0.0', 'woocommerce/core-e2e-tests:latest' ],
			'stable channel'             => [ 'stable', 'woocommerce/core-e2e-tests:11.0' ],
			'rc channel'                 => [ 'rc', 'woocommerce/core-e2e-tests:11.1' ],
			'no version given'           => [ null, 'woocommerce/core-e2e-tests:11.0' ],
		];
	}

	/**
	 * @dataProvider selection_cases
	 */
	public function test_selection_against_the_manager_payload( ?string $requested, string $expected ): void {
		$this->assertSame( $expected, $this->select( $requested ) );
	}

	/**
	 * The activation package is published on its own cycle: its specs work on
	 * 11.0 today, so 10.9 and 11.0 exist while 11.1 is being written.
	 *
	 * @return array<string, array<int, string|null>>
	 */
	public function activation_selection_cases(): array {
		return [
			'patch release'          => [ '11.0.1', 'woocommerce/activation:11.0' ],
			'prerelease'             => [ '11.1.0-rc.1', 'woocommerce/activation:11.1' ],
			'the older line offered' => [ '10.9.4', 'woocommerce/activation:10.9' ],
			'older than anything'    => [ '9.0.0', 'woocommerce/activation:latest' ],
			'stable channel'         => [ 'stable', 'woocommerce/activation:11.0' ],
			'rc channel'             => [ 'rc', 'woocommerce/activation:11.1' ],
			'no version given'       => [ null, 'woocommerce/activation:11.0' ],
		];
	}

	/**
	 * @dataProvider activation_selection_cases
	 */
	public function test_activation_selection_against_the_manager_payload( ?string $requested, string $expected ): void {
		$this->assertSame( $expected, $this->select( $requested, ExposedRunActivationTestCommand::class ) );
	}
}
