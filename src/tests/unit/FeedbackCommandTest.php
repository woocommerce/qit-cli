<?php

use QIT_CLI\App;
use function QIT_CLI\get_manager_url;

class FeedbackCommandTest extends \QIT_CLI_Tests\QITTestCase {

	protected $application_tester;

	public function setUp(): void {
		parent::setUp();
		$this->application_tester = $this->make_application_tester();
	}

	public function test_feedback_sends_message(): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/cli/feedback' ),
			json_encode( [ 'success' => true ] )
		);

		$this->application_tester->run(
			[
				'command' => 'feedback',
				'message' => 'The JSON output is hard to parse',
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertCommandIsSuccessful( $this->application_tester );

		$request = App::getVar( 'mocked_request' );
		$this->assertSame( 'The JSON output is hard to parse', $request['post_body']['message'] );
		$this->assertSame( phpversion(), $request['post_body']['php_version'] );
		$this->assertSame( PHP_OS_FAMILY, $request['post_body']['os'] );
		$this->assertSame( 'qit_cli', $request['post_body']['client'] );
	}

	public function test_feedback_shows_success(): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/cli/feedback' ),
			json_encode( [ 'success' => true ] )
		);

		$this->application_tester->run(
			[
				'command' => 'feedback',
				'message' => 'Great tool!',
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertCommandIsSuccessful( $this->application_tester );
		$this->assertStringContainsString( 'Feedback sent', $this->application_tester->getDisplay() );
	}

	public function test_feedback_shows_error_on_failure(): void {
		App::setVar(
			sprintf( 'mock_%s%s', get_manager_url(), '/wp-json/cd/v1/cli/feedback' ),
			'exception: Rate limit exceeded'
		);

		$exit_code = $this->application_tester->run(
			[
				'command' => 'feedback',
				'message' => 'Test message',
			],
			[ 'capture_stderr_separately' => true ]
		);

		$this->assertNotEquals( 0, $exit_code );
		$this->assertStringContainsString( 'Rate limit exceeded', $this->application_tester->getDisplay() );
	}
}
