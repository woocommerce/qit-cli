<?php

use QIT_CLI\App;
use QIT_CLI\Auth;

class AuthTest extends \PHPUnit\Framework\TestCase {
	public function setUp(): void {
		App::make( \QIT_CLI\Environment::class )->remove_environment( 'tests' );
		parent::setUp();
	}

	public function test_application_password_auth() {
		$auth = App::make( Auth::class );

		$auth->set_auth_app_pass( 'foo', 'bar' );

		$this->assertEquals( 'foo:bar', base64_decode( $auth->get_auth() ) );
	}

	public function test_cd_secret_auth() {
		$auth = App::make( Auth::class );

		$auth->set_cd_secret( 'foo' );

		$this->assertEquals( 'foo', $auth->get_auth() );
	}

	public function test_cd_secret_auth_overrides_app_pass() {
		$auth = App::make( Auth::class );

		$auth->set_cd_secret( 'foo' );
		$auth->set_auth_app_pass( 'foo', 'bar' );

		$this->assertEquals( 'foo', $auth->get_auth() );
	}
}
