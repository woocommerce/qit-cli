<?php

use QIT_CLI\App;
use Symfony\Component\Console\Command\Command;
use function QIT_CLI\get_manager_url;

class ListCommandTest extends \QIT_CLI_Tests\QITTestCase {
	protected $application_tester;

	public function setUp(): void {
		parent::setUp();
		$this->application_tester = $this->make_application_tester();
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function make_test_runs_response(): array {
		return [
			[
				'test_run_id'         => 12345,
				'test_type'           => 'e2e',
				'wordpress_version'   => '6.7.1',
				'woocommerce_version' => '9.6.0',
				'status'              => 'success',
				'woo_extension'       => [
					'id'   => 123456,
					'name' => 'WooCommerce',
					'type' => 'plugin',
				],
				'version'             => '9.6.0',
				'created_at'          => '2025-01-15 10:30:00',
			],
		];
	}

	private function mock_get_endpoint(): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get' ),
			json_encode( $this->make_test_runs_response() )
		);
	}

	/**
	 * Regression test: the command used to default "test_types" to every test type advertised by
	 * the Manager sync. As soon as sync advertised a test type the endpoint schema did not allow,
	 * every "qit list-tests" run failed with "Invalid parameter(s): test_types".
	 */
	public function test_list_tests_does_not_send_a_test_types_filter_by_default(): void {
		$this->mock_get_endpoint();

		$exit_code = $this->application_tester->run(
			[ 'command' => 'list-tests' ],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( Command::SUCCESS, $exit_code );

		$request = App::getVar( 'mocked_request' );
		$this->assertArrayNotHasKey( 'test_types', $request['post_body'] );
		$this->assertArrayNotHasKey( 'test_status', $request['post_body'] );
	}

	public function test_list_tests_sends_the_test_types_filter_when_given(): void {
		$this->mock_get_endpoint();

		$exit_code = $this->application_tester->run(
			[
				'command'      => 'list-tests',
				'--test_types' => 'e2e,security',
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( Command::SUCCESS, $exit_code );

		$request = App::getVar( 'mocked_request' );
		$this->assertSame( 'e2e,security', $request['post_body']['test_types'] );
	}

	public function test_list_tests_rejects_unknown_test_types_before_requesting(): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/get' ),
			'exception: The request should not have been made'
		);

		$exit_code = $this->application_tester->run(
			[
				'command'      => 'list-tests',
				'--test_types' => 'e2e,not-a-test-type',
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( Command::FAILURE, $exit_code );
		$this->assertStringContainsString( 'Invalid test type(s): not-a-test-type', $this->application_tester->getDisplay() );
	}

	/**
	 * The endpoint parameter is "paged". Sending "page" meant --page was silently ignored and
	 * every run returned the first page.
	 */
	public function test_list_tests_sends_the_page_as_paged(): void {
		$this->mock_get_endpoint();

		$exit_code = $this->application_tester->run(
			[
				'command'    => 'list-tests',
				'--page'     => '3',
				'--per_page' => '5',
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertSame( Command::SUCCESS, $exit_code );

		$request = App::getVar( 'mocked_request' );
		$this->assertSame( '3', $request['post_body']['paged'] );
		$this->assertSame( '5', $request['post_body']['per_page'] );
		$this->assertArrayNotHasKey( 'page', $request['post_body'] );
	}
}
