<?php

namespace QIT_CLI;

use QIT_CLI\IO\Output;

class Environment {
	/** @var string This file holds the information of which environment the QIT CLI is currently running on. */
	protected $environment_control_file;

	public function __construct() {
		$this->environment_control_file = $this->get_config_dir() . '/.woo-qit-cli-environment';
	}

	/**
	 * @var array<string> The allowed environments.
	 */
	public static $allowed_environments = [
		'local'      => 'local',
		'tests'      => 'tests',
		'staging'    => 'staging',
		'production' => 'production',
		'undefined'  => 'undefined',
	];

	/**
	 * @return bool True if running in Developer mode. False if not.
	 */
	public function is_development_mode(): bool {
		return file_exists( sprintf( '%s/%s', $this->get_config_dir(), '.woo-qit-cli-dev' ) );
	}

	public function enable_development_mode(): void {
		$dev_file = sprintf( '%s/%s', $this->get_config_dir(), '.woo-qit-cli-dev' );
		if ( ! file_exists( $dev_file ) ) {
			$touched = touch( $dev_file );

			if ( ! $touched ) {
				throw new \RuntimeException( 'Could not create the file flag to enable development mode. Please check that PHP has write permission to file: ' . $dev_file );
			}
		}
	}

	public function create_environment( string $environment ): void {
		if ( ! array_key_exists( $environment, self::$allowed_environments ) ) {
			throw new \InvalidArgumentException( 'Invalid environment. Valid options are: ' . implode( ', ', self::$allowed_environments ) );
		}

		if ( file_exists( $this->make_config_filepath( $environment ) ) ) {
			$unlinked = $this->make_config_filepath( $environment );

			if ( ! $unlinked ) {
				throw new \RuntimeException( 'Could not delete environment config file to override with new value. Please check that PHP has write permission to file: ' . $this->make_config_filepath( $environment ) );
			}
		}

		$written = touch( $this->make_config_filepath( $environment ) );

		if ( ! $written ) {
			throw new \RuntimeException( 'Could not create environment config file. Please check that PHP has write permission to file: ' . $this->make_config_filepath( $environment ) );
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
		if ( ! array_key_exists( $environment, self::$allowed_environments ) ) {
			throw new \InvalidArgumentException( 'Invalid environment. Valid options are: ' . implode( ', ', self::$allowed_environments ) );
		}

		if ( ! file_exists( $this->make_config_filepath( $environment ) ) ) {
			throw new \RuntimeException( "Cannot switch to environment '$environment', as it has not been configured yet." );
		}

		$written = file_put_contents( $this->environment_control_file, $environment );

		if ( ! $written ) {
			throw new \RuntimeException( 'Could not switch to environment. Cannot write to environment control file. Please check that PHP has write permission to file: ' . $this->environment_control_file );
		}
	}

	public function environment_exists( string $environment ): bool {
		if ( ! array_key_exists( $environment, self::$allowed_environments ) ) {
			throw new \InvalidArgumentException( "The environment $environment is not allowed." );
		}

		return file_exists( $this->make_config_filepath( $environment ) );
	}

	/**
	 * @return string Which environment the QIT is currently using.
	 *                Possible values are: undefined, production, staging, local
	 */
	public function get_current_environment(): string {
		if ( defined( 'UNIT_TESTS' ) ) {
			return 'tests';
		}

		if ( ! file_exists( $this->environment_control_file ) ) {
			return 'undefined';
		}

		$allowed_envs = [ 'production', 'staging', 'local' ];

		$environment = file_get_contents( $this->environment_control_file );

		if ( ! in_array( $environment, $allowed_envs, true ) ) {
			unlink( $this->get_config_dir() . '/.woo-qit-cli-environment' );
			App::get( Output::class )->writeln(
				'QIT Warning: Invalid environment. Resetting "%s".',
				$this->get_config_dir() . '/.qit-cli-environment'
			);

			return 'undefined';
		}

		return $environment;
	}

	/**
	 * @throws \RuntimeException When it can't find the QIT CLI directory.
	 * @return string The path to the QIT CLI directory.
	 */
	public function get_config_dir(): string {
		$normalize_path = static function ( string $path ): string {
			// Converts Windows-style directory separator to Unix-style. Makes sure it ends with a trailing slash.
			return rtrim( str_replace( '\\', '/', $path ), '/\\' ) . '/';
		};

		// Windows alternative.
		if ( ! empty( getenv( 'QIT_CLI_CONFIG_DIR' ) ) ) {
			if ( ! file_exists( getenv( 'QIT_CLI_CONFIG_DIR' ) ) ) {
				throw new \RuntimeException( sprintf( 'The QIT_CLI_CONFIG_DIR environment variable is defined, but points to a non-existing directory: %s', getenv( 'QIT_CLI_CONFIG_DIR' ) ) );
			}

			return $normalize_path( getenv( 'QIT_CLI_CONFIG_DIR' ) );
		}

		// Unix.
		if ( isset( $_SERVER['HOME'] ) ) {
			if ( ! file_exists( $_SERVER['HOME'] ) ) {
				throw new \RuntimeException( sprintf( 'The HOME environment variable is defined, but points to a non-existing directory: %s', $_SERVER['HOME'] ) );
			}

			return $normalize_path( $_SERVER['HOME'] );
		}

		$message = '';

		if ( is_windows() ) {
			$message .= 'The QIT CLI is meant to run on Unix environments, such as Windows WSL, Linux, or Mac. On native Windows, ';
		}

		$message .= "You need to set an environment variable 'QIT_CLI_CONFIG_DIR' pointing to a writable directory where the QIT CLI can write it's config file. Do NOT use a directory inside your plugin, as the config file will hold sensitive information that should not be included in your plugin.";

		throw new \RuntimeException( $message );
	}

	/**
	 * @return string The file path of the current QIT CLI config file.
	 */
	public function get_config_filepath(): string {
		// Eg: /home/foo/.woo-qit-cli-production.
		return $this->make_config_filepath( $this->get_current_environment() );
	}

	private function make_config_filepath( string $environment ): string {
		return sprintf( '%s/%s-%s', $this->get_config_dir(), '.woo-qit-cli', $environment );
	}

	/**
	 * @param string $environment The environment to delete.
	 *
	 * @return void
	 * @throws \RuntimeException When the provided environment is not yet configured, and therefore can't be deleted.
	 *
	 * @throws \InvalidArgumentException When the provided environment is invalid.
	 */
	public function delete_environment( string $environment ): void {
		if ( ! in_array( $environment, self::$allowed_environments, true ) ) {
			throw new \InvalidArgumentException( 'Invalid environment. Valid options are: ' . implode( ', ', self::$allowed_environments ) );
		}

		if ( ! $this->environment_exists( $environment ) ) {
			throw new \RuntimeException( "Environment '$environment' does not exist/is not configured yet." );
		}
	}
}
