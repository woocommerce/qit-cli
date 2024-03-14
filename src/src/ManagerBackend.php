<?php

namespace QIT_CLI;

use QIT_CLI\IO\Output;

class ManagerBackend {
	/**
	 * @var array<string> The allowed Manager environments.
	 */
	public static $allowed_manager_backends = [
		'local'      => 'local',
		'tests'      => 'tests',
		'staging'    => 'staging',
		'production' => 'production',
		'default'    => 'default',
	];

	/** @var Cache The cache file of this backend. */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache = $cache;
	}

	protected function is_allowed_manager_backend( string $manager_backend ): bool {
		// Partner environment.
		if ( substr( $manager_backend, 0, 8 ) === 'partner-' ) {
			$parts = explode( '-', $manager_backend );

			if ( count( $parts ) !== 3 ) {
				return false;
			}

			if ( ! in_array( $parts[1], self::$allowed_manager_backends, true ) ) {
				return false;
			}

			/**
			 * Validates whether a string is a valid WordPress username.
			 *
			 * This regular expression allows usernames that:
			 * - Contain only letters (uppercase and lowercase), numbers, underscores, and hyphens.
			 * - Are between 2 and 60 characters in length.
			 *
			 * Examples of valid usernames: my_username, john123, user-name_1.
			 * Examples of invalid usernames: my@username, john.smith, a_very_long_username_that_is_more_than_60_characters.
			 */
			return preg_match( '#^[a-zA-Z0-9_\-]{2,60}$#', $parts[2] );
		} else {
			return in_array( $manager_backend, self::$allowed_manager_backends, true );
		}
	}

	public function add_manager_backend( string $manager_backend, bool $switch_now = true ): void {
		if ( ! $this->is_allowed_manager_backend( $manager_backend ) ) {
			throw new \InvalidArgumentException( 'Invalid Manager backend.' );
		}

		$new_manager_backend_file = $this->cache->make_cache_path( $manager_backend );

		if ( file_exists( $new_manager_backend_file ) ) {
			$unlinked = unlink( $new_manager_backend_file );

			if ( ! $unlinked ) {
				throw new \RuntimeException( 'Could not delete Manager backend file to override with new value. Please check that PHP has write permission to file: ' . $new_manager_backend_file );
			}
		}

		$written = file_put_contents( $new_manager_backend_file, json_encode( [] ) );

		if ( ! $written ) {
			throw new \RuntimeException( 'Could not create Manager backend file. Please check that PHP has write permission to file: ' . $new_manager_backend_file );
		}

		/*
		 * Make sure the file has the correct permissions. (0600)
		 * Try to set it, warn if cannot.
		 */
		if ( decoct( fileperms( $new_manager_backend_file ) & 0777 ) !== '600' && ! chmod( $new_manager_backend_file, 0600 ) && ! App::getVar( "WARNED_ENV_PERMISSION_$manager_backend", false ) ) {
			App::make( Output::class )->writeln(
				sprintf(
					'<warning>Warning: Could not set permissions on Manager environment file. Please check that PHP has write permission to file: %s</warning>',
					$this->cache->get_cache_file_path()
				)
			);

			// Show this only once per request.
			App::setVar( "WARNED_ENV_PERMISSION_$manager_backend", true );
		}

		if ( $switch_now ) {
			$this->switch_to_manager_backend( $manager_backend );
		}
	}

	/**
	 * @param string $manager_backend The backend to switch to.
	 *
	 * @return void
	 * @throws \InvalidArgumentException When the provided backend is invalid.
	 *
	 * @throws \RuntimeException When the provided backend is not yet configured, and therefore can't be switched to.
	 */
	public function switch_to_manager_backend( string $manager_backend ): void {
		if ( ! $this->is_allowed_manager_backend( $manager_backend ) ) {
			throw new \InvalidArgumentException( 'Invalid backend.' );
		}

		if ( ! file_exists( $this->cache->make_cache_path( $manager_backend ) ) ) {
			throw new \RuntimeException( "Cannot switch to backend '$manager_backend', as it has not been configured yet." );
		}

		Config::set_current_manager_environment( $manager_backend );
		App::make( ManagerSync::class )->maybe_sync( true );
	}

	public function add_partner( string $partner, bool $switch_now = true ): void {
		$this->add_manager_backend( $this->get_partner_filename( $partner ), $switch_now );
	}

	public function remove_partner( string $partner ): void {
		$this->remove_manager_backend( $this->get_partner_filename( $partner ) );
	}

	public function switch_to_partner( string $partner ): void {
		$this->switch_to_manager_backend( $this->get_partner_filename( $partner ) );
	}

	public function partner_exists( string $partner ): bool {
		return $this->manager_backend_exists( $this->get_partner_filename( $partner ) );
	}

	protected function get_partner_filename( string $partner ): string {
		$current_backend = Config::get_current_manager_backend();

		/*
		 * This code determines the current environment based on the partner environment file convention,
		 * which follows the format: partner-{ENVIRONMENT}-{PARTNER}.
		 * For example, if the current environment is a partner test environment, the file name convention
		 * would be partner-tests-foopartner.
		 * The code breaks the file name down into three parts and uses the second part as the current environment.
		 * This is necessary to prevent the name generation from becoming recursively bigger if additional partners
		 * are added while using a partner environment.
		 */
		if ( strpos( $current_backend, 'partner-' ) === 0 ) {
			$current_backend = explode( '-', $current_backend )[1];
		}

		return sprintf( 'partner-%s-%s', $current_backend, $partner );
	}

	public function manager_backend_exists( string $manager_backend ): bool {
		if ( ! $this->is_allowed_manager_backend( $manager_backend ) ) {
			throw new \InvalidArgumentException( "The environment $manager_backend is not allowed." );
		}

		return file_exists( $this->cache->make_cache_path( $manager_backend ) );
	}

	/**
	 * @param bool $partners_only
	 *
	 * @return array<string> The list of Partners configured.
	 */
	public static function get_configured_manager_backends( bool $partners_only = false ): array {
		if ( ! file_exists( Config::get_qit_dir() ) ) {
			return [];
		}

		$manager_environments = [];

		$files = new \DirectoryIterator( Config::get_qit_dir() );

		while ( $files->valid() ) {
			$f = $files->current();
			// Skip. Not an environment file.
			if ( strpos( $f->getBasename(), '.env' ) !== 0 ) {
				$files->next();
				continue;
			}

			if ( $partners_only ) {
				if ( strpos( $f->getBasename(), '.env-partner-' ) === 0 ) {
					$manager_environments[] = $f->getPathname();
				}
			} else {
				// Do not include the "default" environment as a configured environment.
				if ( $f->getBasename() === '.env-default' ) {
					$files->next();
					continue;
				}
				$manager_environments[] = $f->getPathname();
			}
			$files->next();
		}

		return $manager_environments;
	}

	/**
	 * @param bool $partners_only
	 *
	 * @return array<string> The human-friendly names of the environments configured.
	 */
	public function get_configured_manager_backend_names( bool $partners_only = false ): array {
		$manager_environments = self::get_configured_manager_backends( $partners_only );

		foreach ( $manager_environments as &$e ) {
			// "/tmp/qit/.env-default.json" => ".env-default.json"
			$e = basename( $e );

			// ".env-default.json" => ".env-default"
			$e = str_replace( '.json', '', $e );

			// ".env-default" => "default"
			$e = str_replace( '.env-', '', $e );
		}

		return $manager_environments;
	}

	/**
	 * @param string $manager_backend The environment to remove.
	 *
	 * @return void
	 * @throws \RuntimeException When the provided environment is not yet configured, and therefore can't be removed.
	 *
	 * @throws \InvalidArgumentException When the provided environment is invalid.
	 */
	public function remove_manager_backend( string $manager_backend ): void {
		if ( ! $this->is_allowed_manager_backend( $manager_backend ) ) {
			throw new \InvalidArgumentException( 'Invalid Manager environment.' );
		}

		if ( ! $this->manager_backend_exists( $manager_backend ) ) {
			throw new \RuntimeException( "Manager environment '$manager_backend' does not exist/is not configured yet." );
		}

		$unlinked = unlink( $this->cache->make_cache_path( $manager_backend ) );

		if ( ! $unlinked ) {
			throw new \RuntimeException( sprintf( 'Could not remove Manager environment "%s". Please delete the cache file manually: %s', $manager_backend, $this->cache->get_cache_file_path() ) );
		}

		// Are we deleting the environment we are currently in?
		if ( Config::get_current_manager_backend() === $manager_backend ) {
			$other_environments = self::get_configured_manager_backend_names( false );
			// Switch to next available environment, if it exists.
			if ( ! empty( $other_environments ) ) {
				$next_environment = array_shift( $other_environments );
				$this->switch_to_manager_backend( strtolower( $next_environment ) );
				App::make( Output::class )->writeln( sprintf( "\n<comment>Switched to Manager environment '%s'.</comment>", $next_environment ) );
			} else {
				Config::set_current_manager_environment( 'default' );
			}
		}
	}
}
