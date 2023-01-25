<?php

namespace QIT_CLI;

use QIT_CLI\Commands\Partner\AddPartner;

class Auth {
	/** @var Config $config */
	protected $config;

	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 * @return string|null Return either a base64 of user:application_password, or the CD_SECRET if defined, or null if neither are defined.
	 *
	 * @throws \RuntimeException When using application password and it's empty.
	 */
	public function get_auth() {
		$override = $this->config->get_cache( 'cd_secret' );

		if ( ! is_null( $override ) ) {
			return (string) $override;
		}

		$user                 = $this->config->get_cache( 'user' );
		$application_password = $this->config->get_cache( 'application_password' );

		if ( ! empty( $user ) && ! empty( $application_password ) ) {
			return base64_encode( sprintf( '%s:%s', $user, $application_password ) );
		}

		return null;
	}

	/**
	 * @param string $user
	 * @param string $app_pass
	 *
	 * @return void
	 */
	public function set_auth_app_pass( $user, $app_pass ): void {
		$this->config->set_cache( 'user', $user, - 1 );
		$this->config->set_cache( 'application_password', $app_pass, - 1 );
	}

	/**
	 * @param string $cd_secret
	 *
	 * @return void
	 */
	public function set_cd_secret( $cd_secret ): void {
		$this->config->set_cache( 'cd_secret', $cd_secret, - 1 );
	}

	public function delete_cd_secret(): void {
		$this->config->delete_cache( 'cd_secret' );
	}
}
