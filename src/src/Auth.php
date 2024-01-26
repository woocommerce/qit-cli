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
	public function get_partner_auth() {
		// Migrate "application_password" to "qit_token" if it exists.
		if ( ! empty( $this->environment->get_cache()->get( 'application_password' ) ) ) {
			$this->environment->get_cache()->set( 'qit_token', $this->environment->get_cache()->get( 'application_password' ), - 1 );
			$this->environment->get_cache()->delete( 'application_password' );
		}

		$user      = $this->environment->get_cache()->get( 'user' );
		$qit_token = $this->environment->get_cache()->get( 'qit_token' );

		if ( ! empty( $user ) && ! empty( $qit_token ) ) {
			return base64_encode( sprintf( '%s:%s', $user, $qit_token ) );
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
	 * @param string $qit_token
	 *
	 * @return void
	 */
	public function set_partner_auth( $user, $qit_token ): void {
		$this->environment->get_cache()->set( 'user', $user, - 1 );
		$this->environment->get_cache()->set( 'qit_token', $qit_token, - 1 );
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
