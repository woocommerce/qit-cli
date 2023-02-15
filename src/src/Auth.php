<?php

namespace QIT_CLI;

class Auth {
	/** @var Environment $environment */
	protected $environment;

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}

	/**
	 * @return string|null base64 encoded string of user:application_password, or null if not defined.
	 */
	public function get_app_pass() {
		$user                 = $this->environment->get_cache()->get( 'user' );
		$application_password = $this->environment->get_cache()->get( 'application_password' );

		if ( ! empty( $user ) && ! empty( $application_password ) ) {
			return base64_encode( sprintf( '%s:%s', $user, $application_password ) );
		}

		return null;
	}

	/**
	 * @return string|null MANAGER_SECRET, or null if not defined.
	 */
	public function get_manager_secret() {
		return $this->environment->get_cache()->get( 'manager_secret' );
	}

	/**
	 * @param string $user
	 * @param string $app_pass
	 *
	 * @return void
	 */
	public function set_auth_app_pass( $user, $app_pass ): void {
		$this->environment->get_cache()->set( 'user', $user, - 1 );
		$this->environment->get_cache()->set( 'application_password', $app_pass, - 1 );
	}

	/**
	 * @param string $manager_secret
	 *
	 * @return void
	 */
	public function set_manager_secret( $manager_secret ): void {
		$this->environment->get_cache()->set( 'manager_secret', $manager_secret, - 1 );
	}

	public function delete_manager_secret(): void {
		$this->environment->get_cache()->delete( 'manager_secret' );
	}
}
