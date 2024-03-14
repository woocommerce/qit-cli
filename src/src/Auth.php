<?php

namespace QIT_CLI;

class Auth {
	/** @var Cache $cache */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * @return string|null base64 encoded string of user:application_password, or null if not defined.
	 */
	public function get_partner_auth() {
		// Migrate "application_password" to "qit_token" if it exists.
		if ( empty( $this->cache->get( 'qit_token' ) ) && ! empty( $this->cache->get( 'application_password' ) ) ) {
			$this->cache->set( 'qit_token', $this->cache->get( 'application_password' ), - 1 );
		}

		$user      = $this->cache->get( 'user' );
		$qit_token = $this->cache->get( 'qit_token' );

		if ( ! empty( $user ) && ! empty( $qit_token ) ) {
			return base64_encode( sprintf( '%s:%s', $user, $qit_token ) );
		}

		return null;
	}

	/**
	 * @return string|null MANAGER_SECRET, or null if not defined.
	 */
	public function get_manager_secret() {
		return $this->cache->get( 'manager_secret' );
	}

	/**
	 * @param string $user
	 * @param string $qit_token
	 *
	 * @return void
	 */
	public function set_partner_auth( $user, $qit_token ): void {
		$this->cache->set( 'user', $user, - 1 );
		$this->cache->set( 'qit_token', $qit_token, - 1 );
	}

	/**
	 * @param string $manager_secret
	 *
	 * @return void
	 */
	public function set_manager_secret( $manager_secret ): void {
		$this->cache->set( 'manager_secret', $manager_secret, - 1 );
	}

	public function delete_manager_secret(): void {
		$this->cache->delete( 'manager_secret' );
	}
}
