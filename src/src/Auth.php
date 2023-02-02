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
	public function get_app_pass() {
		$user                 = $this->cache->get_cache( 'user' );
		$application_password = $this->cache->get_cache( 'application_password' );

		if ( ! empty( $user ) && ! empty( $application_password ) ) {
			return base64_encode( sprintf( '%s:%s', $user, $application_password ) );
		}

		return null;
	}

	/**
	 * @return string|null CD_SECRET, or null if not defined.
	 */
	public function get_cd_secret() {
		return $this->cache->get_cache( 'cd_secret' );
	}

	/**
	 * @param string $user
	 * @param string $app_pass
	 *
	 * @return void
	 */
	public function set_auth_app_pass( $user, $app_pass ): void {
		$this->cache->set_cache( 'user', $user, - 1 );
		$this->cache->set_cache( 'application_password', $app_pass, - 1 );
	}

	/**
	 * @param string $cd_secret
	 *
	 * @return void
	 */
	public function set_cd_secret( $cd_secret ): void {
		$this->cache->set_cache( 'cd_secret', $cd_secret, - 1 );
	}

	public function delete_cd_secret(): void {
		$this->cache->delete_cache( 'cd_secret' );
	}
}
