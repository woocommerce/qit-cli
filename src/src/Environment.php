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

	/** @var Cache The cache file of this environment. */
	protected $cache;

	public function __construct( Cache $cache ) {
		$this->cache      = $cache;
	}

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

		$new_environment_file = $this->cache->make_cache_path_for_environment( $environment );

		if ( file_exists( $new_environment_file ) ) {
			$unlinked = unlink( $new_environment_file );

			if ( ! $unlinked ) {
				throw new \RuntimeException( 'Could not delete environment file to override with new value. Please check that PHP has write permission to file: ' . $new_environment_file );
			}
		}

		$written = file_put_contents( $new_environment_file, json_encode( [] ) );

		if ( ! $written ) {
			throw new \RuntimeException( 'Could not create environment file. Please check that PHP has write permission to file: ' . $new_environment_file );
		}

		/*
		 * Make sure the file has the correct permissions. (0600)
		 * Try to set it, warn if cannot.
		 */
		if ( decoct( fileperms( $new_environment_file ) & 0777 ) !== '600' && ! chmod( $new_environment_file, 0600 ) && ! App::getVar( "WARNED_ENV_PERMISSION_$environment", false ) ) {
			App::make( Output::class )->writeln(
				sprintf(
					'<warning>Warning: Could not set permissions on environment file. Please check that PHP has write permission to file: %s</warning>',
					$this->cache->get_cache_file_path()
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

		if ( ! file_exists( $this->cache->make_cache_path_for_environment( $environment ) ) ) {
			throw new \RuntimeException( "Cannot switch to environment '$environment', as it has not been configured yet." );
		}

		Config::set_current_environment( $environment );
		$this->cache = App::make( Cache::class );
	}

	public function environment_exists( string $environment ): bool {
		if ( ! $this->is_allowed_environment( $environment ) ) {
			throw new \InvalidArgumentException( "The environment $environment is not allowed." );
		}

		return file_exists( $this->cache->make_cache_path_for_environment( $environment ) );
	}

	public function get_cache(): Cache {
		return $this->cache;
	}

	/**
	 * @param bool $partners_only
	 *
	 * @return array<string> The list of Partners configured.
	 */
	public static function get_configured_environments( bool $partners_only = false ): array {
		if ( ! file_exists( Config::get_qit_dir() ) ) {
			return [];
		}

		$environments = [];

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
					$environments[] = $f->getPathname();
				}
			} else {
				// Do not include the "default" environment as a configured environment.
				if ( $f->getBasename() === '.env-default' ) {
					$files->next();
					continue;
				}
				$environments[] = $f->getPathname();
			}
			$files->next();
		}

		return $environments;
	}

	/**
	 * @param bool $partners_only
	 *
	 * @return array<string> The human-friendly names of the environments configured.
	 */
	public function get_configured_environment_names( bool $partners_only = false ): array {
		$environments = self::get_configured_environments( $partners_only );
		foreach ( $environments as &$e ) {
			$e = str_replace( [ '.env-partner-', '.env-', '.json' ], '', $e );
		}

		return $environments;
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

		$unlinked = unlink( $this->cache->make_cache_path_for_environment( $environment ) );

		if ( ! $unlinked ) {
			throw new \RuntimeException( sprintf( 'Could not remove environment "%s". Please delete the cache file manually: %s', $environment, $this->cache->get_cache_file_path() ) );
		}

		// Are we deleting the environment we are currently in?
		if ( Config::get_current_environment() === $environment ) {
			$other_environments = self::get_configured_environments( false );
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
