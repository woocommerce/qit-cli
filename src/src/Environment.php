<?php

namespace QIT_CLI;

use QIT_CLI\IO\Output;

class Environment {
	/**
	 * @var array<string> The allowed environments.
	 */
	public static $allowed_environments = [
		'local'      => 'local',
		'tests'      => 'tests',
		'staging'    => 'staging',
		'production' => 'production',
		'default'    => 'default',
	];

	public function is_allowed_environment( string $environment ): bool {
		if ( substr( $environment, 0, 8 ) === 'partner-' ) {
			return preg_match( '/^partner-[a-z0-9]{1,60}$/', $environment );
		}

		return in_array( $environment, self::$allowed_environments, true );
	}

	public function create_environment( string $environment, bool $switch_now = true ): void {
		if ( ! $this->is_allowed_environment( $environment ) ) {
			throw new \InvalidArgumentException( 'Invalid environment.' );
		}

		if ( file_exists( $this->make_cache_filepath( $environment ) ) ) {
			$unlinked = $this->make_cache_filepath( $environment );

			if ( ! $unlinked ) {
				throw new \RuntimeException( 'Could not delete environment file to override with new value. Please check that PHP has write permission to file: ' . $this->make_cache_filepath( $environment ) );
			}
		}

		$written = touch( $this->make_cache_filepath( $environment ) );

		if ( ! $written ) {
			throw new \RuntimeException( 'Could not create environment file. Please check that PHP has write permission to file: ' . $this->make_cache_filepath( $environment ) );
		}

		/*
		 * Make sure the file has the correct permissions. (0600)
		 * Try to set it, warn if cannot.
		 */
		if ( decoct( fileperms( $this->make_cache_filepath( $environment ) ) & 0777 ) !== '600' && ! chmod( $this->make_cache_filepath( $environment ), 0600 ) && ! App::getVar( "WARNED_ENV_PERMISSION_$environment", false ) ) {
			App::make( Output::class )->writeln(
				sprintf(
					'<warning>Warning: Could not set permissions on environment file. Please check that PHP has write permission to file: %s</warning>',
					$this->make_cache_filepath( $environment )
				)
			);

			// Show this only once per request.
			App::setVar( "WARNED_ENV_PERMISSION_$environment", true );
		}

		if ( $switch_now ) {
			$this->switch_to_environment( $environment );
		}
	}

	/**
	 * @param string $environment The environment to switch to.
	 *
	 * @return void
	 * @throws \InvalidArgumentException When the provided environment is invalid.
	 *
	 * @throws \RuntimeException When the provided environment is not yet configured, and therefore can't be switched to.
	 */
	public function switch_to_environment( string $environment ): void {
		if ( ! $this->is_allowed_environment( $environment ) ) {
			throw new \InvalidArgumentException( 'Invalid environment.' );
		}

		if ( ! file_exists( $this->make_cache_filepath( $environment ) ) ) {
			throw new \RuntimeException( "Cannot switch to environment '$environment', as it has not been configured yet." );
		}

		Config::set_current_environment( $environment );
	}

	public function environment_exists( string $environment ): bool {
		if ( ! $this->is_allowed_environment( $environment ) ) {
			throw new \InvalidArgumentException( "The environment $environment is not allowed." );
		}

		return file_exists( $this->make_cache_filepath( $environment ) );
	}

	/**
	 * @return string The file path of the current QIT CLI cache file.
	 */
	public function get_cache_filepath(): string {
		// Eg: /home/foo/.woo-qit-cli-production.
		return $this->make_cache_filepath( Config::get_current_environment() );
	}

	private function make_cache_filepath( string $environment ): string {
		return Config::get_qit_dir() . ".env-$environment";
	}

	/**
	 * @return array<string> The list of Partners configured.
	 */
	public function get_configured_environments( bool $partners_only ): array {
		if ( ! file_exists( Config::get_qit_dir() ) ) {
			return [];
		}

		$partners = [];

		$files = scandir( Config::get_qit_dir() );

		if ( ! is_array( $files ) ) {
			return [];
		}

		foreach ( $files as $f ) {
			// Skip. Not an environment file.
			if ( strpos( $f, '.env' ) !== 0 ) {
				continue;
			}

			if ( $partners_only ) {
				if ( strpos( $f, '.env-partner-' ) === 0 ) {
					$partners[] = str_replace( '.env-partner-', '', $f );
				}
			} else {
				// Do not include the "default" environment as a configured environment.
				if ( $f === '.env-default' ) {
					continue;
				}
				$partners[] = str_replace( '.env-', '', $f );
			}
		}

		return $partners;
	}

	/**
	 * @param string $environment The environment to remove.
	 *
	 * @return void
	 * @throws \RuntimeException When the provided environment is not yet configured, and therefore can't be removed.
	 *
	 * @throws \InvalidArgumentException When the provided environment is invalid.
	 */
	public function remove_environment( string $environment ): void {
		if ( ! $this->is_allowed_environment( $environment ) ) {
			throw new \InvalidArgumentException( 'Invalid environment.' );
		}

		if ( ! $this->environment_exists( $environment ) ) {
			throw new \RuntimeException( "Environment '$environment' does not exist/is not configured yet." );
		}

		$unlinked = unlink( $this->make_cache_filepath( $environment ) );

		if ( ! $unlinked ) {
			throw new \RuntimeException( sprintf( 'Could not remove environment "%s". Please delete the cache file manually: %s', $environment, $this->make_cache_filepath( $environment ) ) );
		}

		// Are we deleting the environment we are currently in?
		if ( Config::get_current_environment() === $environment ) {
			$other_environments = $this->get_configured_environments( false );
			// Switch to next available environment, if it exists.
			if ( ! empty( $other_environments ) ) {
				$next_environment = array_shift( $other_environments );
				$this->switch_to_environment( $next_environment );
				App::make( Output::class )->writeln( sprintf( "<comment>Switched to environment '%s'.</comment>", $next_environment ) );
			} else {
				Config::set_current_environment( 'default' );
			}
		}
	}
}
