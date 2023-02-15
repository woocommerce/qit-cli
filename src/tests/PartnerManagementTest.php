<?php

namespace QIT_CLI_Tests;

use QIT_CLI\App;
use QIT_CLI\Commands\Environment\AddEnvironment;
use QIT_CLI\Commands\Environment\SwitchEnvironment;
use QIT_CLI\Commands\Partner\AddPartner;
use QIT_CLI\Commands\Partner\SwitchPartner;
use QIT_CLI\Environment;
use Spatie\Snapshots\MatchesSnapshots;
use function QIT_CLI\get_manager_url;

class PartnerManagementTest extends QITTestCase {
	use MatchesSnapshots;

	protected $application_tester;

	public function setUp(): void {
		parent::setUp();

		$this->application_tester = $this->make_application_tester();
	}

	protected function add_partner( string $user, string $app_pass, array $extensions = [], bool $success = true ) {
		// Mock the Partner Authentication response.
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/cli/partner-auth' ), json_encode( [
			'success'    => $success,
			'extensions' => $extensions,
		] ) );

		$this->application_tester->run( [
			'command' => AddPartner::getDefaultName(),
			'-u'      => $user,
			'-p'      => $app_pass,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
	}

	protected function get_snapshot_friendly_cache(): string {
		$json = file_get_contents( App::make( Environment::class )->get_cache()->get_cache_file_path() );

		/*
		 * To ensure consistent snapshot testing results, replace the time-dependent "expire" property in the JSON
		 * string with a static value. This ensures that snapshots taken at different times will not differ due to the
		 * value of the "expire" property. The code uses a regular expression to match the "expire" property followed by
		 * a number, and replaces it with a mocked value. The result is a JSON string with a static "expire" value
		 * suitable for snapshot testing.
		 */

		return preg_replace( '#"expire":\s?\d+,#', '"expire": 1234567890,', $json );
	}

	public function test_add_partner() {
		// Add a Partner.
		$this->add_partner( 'foo_user', 'foo_pass' );

		$this->assertMatchesJsonSnapshot( $this->get_snapshot_friendly_cache() );
	}

	public function test_switch_partner() {
		$this->add_partner( 'foo_user', 'foo_pass' );
		$this->add_partner( 'bar_user', 'bar_pass' );

		// bar_user.
		$this->assertMatchesJsonSnapshot( $this->get_snapshot_friendly_cache() );

		$this->application_tester->run( [
			'command' => SwitchPartner::getDefaultName(),
			'user'    => 'foo_user',
		], [ 'capture_stderr_separately' => true ] );

		// foo_user.
		$this->assertMatchesJsonSnapshot( $this->get_snapshot_friendly_cache() );
	}

	public function test_switch_partner_on_different_environments() {
		// Add environment "local".
		// Add "local_user" partner.
		// Add environment "staging".
		// Add "staging_user" partner.
		// Switch to "local_user" partner and fail.
		// Switch to "local" environment".
		// Switch to "local_user" partner and succeed.

		App::setVar( sprintf( 'mock_%s', 'http://cd_manager.loc:8081/wp-json/cd/v1/cli/sync' ), file_get_contents( __DIR__ . '/data/sync.json' ) );

		$this->application_tester->run( [
			'command' => AddEnvironment::getDefaultName(),
			'-e'      => 'local',
			'-s'      => 'foo-secret',
			'-u'      => 'http://cd_manager.loc:8081',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );

		$this->add_partner( 'local_user', 'local_pass' );

		App::setVar( sprintf( 'mock_%s', 'https://stagingcompatibilitydashboard.wpstaging.com/wp-json/cd/v1/cli/sync' ), file_get_contents( __DIR__ . '/data/sync.json' ) );

		$this->application_tester->run( [
			'command' => AddEnvironment::getDefaultName(),
			'-e'      => 'staging',
			'-s'      => 'foo-secret',
			'-u'      => 'https://stagingcompatibilitydashboard.wpstaging.com',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );

		$this->add_partner( 'staging_user', 'staging_pass' );

		// staging_user.
		$this->assertMatchesJsonSnapshot( $this->get_snapshot_friendly_cache() );

		// Try to switch to local Partner while on Staging environment, fail.
		$this->application_tester->run( [
			'command' => SwitchPartner::getDefaultName(),
			'user'    => 'local_user',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertEquals( 1, $this->application_tester->getStatusCode() );
		$this->assertStringContainsString( 'Cannot switch to environment', $this->application_tester->getErrorOutput() );

		// Switch to "local" environment.
		$this->application_tester->run( [
			'command' => SwitchEnvironment::getDefaultName(),
			'environment'    => 'local',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );

		// Switch to "local_user" partner.
		$this->application_tester->run( [
			'command' => SwitchPartner::getDefaultName(),
			'user'    => 'local_user',
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );

		// local_user.
		$this->assertMatchesJsonSnapshot( $this->get_snapshot_friendly_cache() );
	}
}
