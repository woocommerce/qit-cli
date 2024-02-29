<?php

namespace QIT_CLI;

use QIT_CLI\Commands\OnboardingCommand;
use QIT_CLI\Exceptions\IOException;
use QIT_CLI\IO\Input;

class Config {
	/** @var string  */
	protected $config_file;

	/** @var array<scalar> */
	protected $config = [];

	/** @var array<scalar> */
	protected $schema = [
		'current_environment' => 'default',
		'development_mode'    => false,
		'proxy_url'           => '127.0.0.1:8080',
		'last_diagnosis'      => 0,
	];

	/** @var bool */
	protected $did_init = false;

	public function __construct() {
		if ( count( func_get_args() ) > 0 ) {
			// Please do not add arguments to this constructor.
			throw new \LogicException();
		}

		$this->config_file = self::get_qit_dir() . '.qit-config.json';
		$this->init();
	}

	public static function set_development_mode( bool $development_mode ): void {
		App::make( self::class )->set( 'development_mode', $development_mode );
	}

	/**
	 * @return bool True if running in Developer mode. False if not.
	 */
	public static function is_development_mode(): bool {
		return (bool) App::make( self::class )->get( 'development_mode' );
	}

	public static function set_current_manager_environment( string $manager_backend ): void {
		App::make( self::class )->set( 'current_environment', $manager_backend );

		// Update the existing Cache singleton with the new environment.
		App::make( Cache::class )->init_cache();
	}

	public static function get_current_manager_backend(): string {
		return (string) App::make( self::class )->get( 'current_environment' ) ?: 'default';
	}

	public static function set_proxy_url( string $proxy_url ): void {
		App::make( self::class )->set( 'proxy_url', $proxy_url );
	}

	public static function get_proxy_url(): string {
		if ( ! empty( getenv( 'QIT_PROXY_URL' ) ) ) {
			return getenv( 'QIT_PROXY_URL' );
		}
		return (string) App::make( self::class )->get( 'proxy_url' ) ?: '127.0.0.1:8080';
	}

	public static function set_last_diagnosis( int $last_diagnosis ): void {
		App::make( self::class )->set( 'last_diagnosis', $last_diagnosis );
	}

	public static function get_last_diagnosis(): int {
		return (int) App::make( self::class )->get( 'last_diagnosis' ) ?: 0;
	}

	public static function needs_onboarding(): bool {
		if ( defined( 'UNIT_TESTS' ) && UNIT_TESTS ) {
			return false;
		}

		if ( getenv( 'QIT_DISABLE_ONBOARDING' ) === 'yes' ) {
			return false;
		}

		// There's no point in showing the onboarding wizard in a CI context.
		if ( getenv( 'CI' ) ) {
			return false;
		}

		// Consistent behavior if we are running the onboarding command directly.
		if ( App::make( Input::class )->getFirstArgument() === OnboardingCommand::getDefaultName() ) {
			return true;
		}

		clearstatcache();

		return ! file_exists( self::get_qit_dir() . '.qit-config.json' );
	}

	/**
	 * @param string $key
	 * @param scalar $value
	 *
	 * @return void
	 * @throws IOException When can't write to file.
	 */
	protected function set( string $key, $value ): void {
		$this->config[ $key ] = $value;
		$this->save();
	}

	/**
	 * @param string $key The config key to retrieve.
	 *
	 * @return scalar|null
	 */
	protected function get( string $key ) {
		return $this->config[ $key ] ?? null;
	}

	/**
	 * @return void
	 * @throws IOException If can't write to file.
	 */
	protected function save() {
		$json    = json_encode( $this->config, JSON_PRETTY_PRINT );
		$written = file_put_contents( $this->config_file, $json );

		if ( ! $written ) {
			throw IOException::cant_write_to_file( $this->config_file );
		}
	}

	protected function init(): void {
		if ( $this->did_init ) {
			return;
		} else {
			$this->did_init = true;
		}

		if ( file_exists( $this->config_file ) ) {
			$config = json_decode( file_get_contents( $this->config_file ), true );

			if ( ! is_array( $config ) ) {
				throw new \UnexpectedValueException( 'Config is not JSON.' );
			}

			// Generate an array with the existing data. Fill-in the blanks with the schema.
			$config = array_merge( $this->schema, $config );

			// Remove items that are not in the schema.
			$config = array_intersect_key( $config, $this->schema );

			$this->config = $config;
		}
	}

	/**
	 * @throws \RuntimeException When it can't find the QIT CLI directory.
	 * @return string The path to the QIT CLI directory.
	 */
	public static function get_qit_dir(): string {
		$normalize_path = static function ( string $path ): string {
			// Converts Windows-style directory separator to Unix-style. Makes sure it ends with a trailing slash.
			return rtrim( str_replace( '\\', '/', $path ), '/' ) . '/';
		};

		if ( ! empty( getenv( 'QIT_HOME' ) ) ) {
			$qit_dir = $normalize_path( getenv( 'QIT_HOME' ) );
		} else {
			if ( is_windows() ) {
				if ( empty( getenv( 'APPDATA' ) ) ) {
					throw new \RuntimeException( 'The APPDATA or QIT_HOME environment variables must be defined in Windows.' );
				}
				$parent_config_dir = getenv( 'APPDATA' );
			} elseif ( ! empty( getenv( 'HOME' ) ) ) {
				$home = $normalize_path( getenv( 'HOME' ) );
				if ( static::use_xdg() ) {
					$xdg_config        = getenv( 'XDG_CONFIG_HOME' ) ?: $home . '.config';
					$parent_config_dir = $xdg_config;
				} else {
					$parent_config_dir = $home;
				}
			} else {
				throw new \RuntimeException( 'The HOME, APPDATA, or QIT_HOME environment variables must be defined.' );
			}

			// Normalize and append 'woo-qit-cli/' to the parent directory.
			$qit_dir = $normalize_path( $parent_config_dir ) . 'woo-qit-cli/';
		}

		// Create the directory if it does not exist.
		if ( ! is_dir( $qit_dir ) && ! mkdir( $qit_dir, 0700, true ) ) {
			throw new \RuntimeException( "Unable to create the QIT CLI directory: $qit_dir" );
		}

		return $qit_dir;
	}


	protected static function use_xdg(): bool {
		if ( defined( 'UNIT_TESTS' ) && UNIT_TESTS ) {
			if ( App::getVar( 'MIMICK_XDG' ) ) {
				return true;
			}
		}
		foreach ( array_keys( $_SERVER ) as $key ) {
			if ( strpos( $key, 'XDG_' ) === 0 ) {
				return true;
			}
		}

		return @is_dir( '/etc/xdg' );
	}
}
