<?php

use QIT_CLI\App;
use QIT_CLI\Commands\CreateRunCommands;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Application;
use function QIT_CLI\get_manager_url;

class RunTestsTest extends \QIT_CLI_Tests\QITTestCase {
	use MatchesSnapshots;

	protected $application_tester;

	public function setUp(): void {
		\QIT_CLI_Tests\QITTestCase::setUp();

		$this->application_tester = $this->make_application_tester( static function ( Application $application ) {
			App::make( CreateRunCommands::class )->register_commands( $application );
		} );
	}

	protected function get_last_request() {
		$request = App::getVar( 'mocked_request' );

		if ( ! is_array( $request ) ) {
			return $request;
		}

		if ( ! empty( $request['post_body']['wordpress_version'] ) ) {
			$request['post_body']['wordpress_version'] = 'X.X.X';
		}

		if ( ! empty( $request['post_body']['woocommerce_version'] ) ) {
			$request['post_body']['woocommerce_version'] = 'X.X.X';
		}

		return $request;
	}

	public function test_run_with_additional_plugins() {
		App::setVar( sprintf( 'mock_%s', get_manager_url() . '/wp-json/cd/v1/enqueue-woo-e2e' ), json_encode( [
			'test_run_id'              => 123456,
			'test_results_manager_url' => ''
		] ) );

		$this->application_tester->run( [
			'command'              => 'run:woo-e2e',
			'sut'                  => 'wccom-plugin-1', // Using slug.
			'--additional_plugins' => '10002,1000245', // Using IDs.
			'--async'              => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->get_last_request() );

		$this->application_tester->run( [
			'command'              => 'run:woo-e2e',
			'sut'                  => '10001', // Using ID.
			'--additional_plugins' => 'wccom-plugin-2,wccom-plugin-4', // Using Slugs.
			'--async'              => true,
		], [ 'capture_stderr_separately' => true ] );

		// If this fails, debug "$this->application_tester->getDisplay()".
		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->get_last_request() );

		$this->application_tester->run( [
			'command'              => 'run:woo-e2e',
			'sut'                  => 'wccom-plugin-1', // Using slug.
			'--additional_plugins' => '10002,wccom-plugin-5', // Using mixed.
			'--async'              => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertMatchesJsonSnapshot( $this->get_last_request() );

		$this->application_tester->run( [
			'command'              => 'run:woo-e2e',
			'sut'                  => 'wccom-plugin-1',
			'--additional_plugins' => '1234567890', // If the user passes an invalid ID, the Manager should flag that.
			'--async'              => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertCommandIsSuccessful( $this->application_tester );
	}

	public function test_run_with_additional_plugins_invalid() {
		$exitCode = $this->application_tester->run( [
			'command' => 'run:woo-e2e',
			'sut' => 'non-existing-extension',
			'--async' => true,
		], [ 'capture_stderr_separately' => true ] );

		$this->assertNotEquals( 0, $exitCode, 'Expected command to fail' );
		$output = $this->application_tester->getDisplay();
		$errorOutput = $this->application_tester->getErrorOutput();
		$allOutput = $output . $errorOutput;
		$this->assertStringContainsString( 'Could not find Woo Extension with slug non-existing-extension.', $allOutput );
	}
}
