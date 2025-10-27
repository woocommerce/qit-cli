<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo;
use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\IO\Output;
use function QIT_CLI\normalize_path;

abstract class EnvInfo implements \JsonSerializable {
	/**
	 * @var array<string>
	 */
	public static $not_user_configurable = [
		'docker_images',
		'temporary_env',
		'env_id',
		'created_at',
		'status',
		'domain',
		'environment',
	];

	/** @var array<string, mixed> */
	public array $extra = [];

	/** @var string */
	public string $environment;

	/** @var string */
	public string $dependencies_mode = 'activate';

	/** @var string */
	public string $temporary_env;

	/** @var int */
	public int $created_at;

	/** @var string */
	public string $status;

	/** @var string */
	public string $env_id;

	/**
	 * Holds an array of volume mappings, where each key is a container path and its value is the corresponding local path.
	 *
	 * @var array<string, string> $volumes Each element of the array is:
	 *                                    - Key: Container path (string)
	 *                                    - Value: Local path (string) (Optional ":<FLAGS>", such as ":ro" for read-only)
	 */
	public array $volumes = [];

	/**
	 * @var array<string> Array of docker images associated with this environment.
	 * @example [ 'qit_php_123456', 'qit_db_123456', 'qit_nginx_123456' ]
	 */
	public array $docker_images = [];

	/** @var string */
	public string $docker_network;

	/**
	 * @var array<string> Array of PHP extensions to be installed in the environment.
	 */
	public array $php_extensions = [];

	/**
	 * @var array<Extension> Array of plugins to feed to WP CLI.
	 */
	public array $plugins = [];

	/**
	 * @var array<Extension> Array of themes to feed to WP CLI.
	 */
	public array $themes = [];

	/**
	 * @var array<string> Array of environment variables to be passed to the test runner.
	 */
	public array $envs = [];

	/** @var array<string,array{path:string,container_path?:string}>  (package‑id => ['path' => dir, 'container_path' => optional container path]) */
	public array $global_setup_packages = [];

	/** @var array<string,array{path:string,container_path?:string,manifest?:\QIT_CLI\PreCommand\Objects\TestPackageManifest}>  (package‑id => ['path' => dir, 'container_path' => optional container path, 'manifest' => optional manifest object]) */
	public array $test_packages_metadata = [];

	/**
	 * @var bool Whether to use tunnels to expose the environment.
	 */
	public bool $tunnel = false;

	public string $tunnel_type = 'no_tunnel';

	/** @var string The site URL, if any. */
	public string $site_url = '';

	/** @var string The domain for accessing the environment. */
	public string $domain = '';

	/**
	 * Prevent dynamic property assignment to catch typos and ensure type safety.
	 *
	 * @param string $name  The property name being set.
	 * @param mixed  $value The value being assigned.
	 *
	 * @throws \LogicException When attempting to set an undeclared property.
	 */
	final public function __set( string $name, $value ): void {
		throw new \LogicException(
			sprintf( 'Unknown property "%s" assigned to %s', $name, static::class )
		);
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return get_object_vars( $this );
	}

	public function get_docker_container( string $docker_container ): string {
		$docker_images = $this->docker_images;

		// Find docker image string that matches the $image.
		$docker_image = array_filter( $docker_images, function ( $docker_image ) use ( $docker_container ) {
			return strpos( $docker_image, $docker_container ) !== false;
		} );

		// Bail if more than one or empty.
		if ( count( $docker_image ) !== 1 ) {
			throw new \RuntimeException( 'Could not find docker image' );
		}

		return array_shift( $docker_image );
	}

	/**
	 * Deserialize an array into an EnvInfo object.
	 *
	 * @param array<string,mixed> $env_info_array The array to deserialize.
	 *
	 * @return EnvInfo The deserialized EnvInfo object.
	 * @phpstan-return \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo|\QIT_CLI\Environment\Environments\Performance\PerformanceEnvInfo
	 */
	public static function from_array( array $env_info_array ): EnvInfo {
		$environment_type = $env_info_array['environment'] ?? 'e2e';

		switch ( $environment_type ) {
			case 'e2e':
				$env_info = new E2EEnvInfo();
				break;
			case 'performance':
				$env_info = new PerformanceEnvInfo();
				break;
			default:
				// Fallback to e2e for unknown environment types.
				App::make( Output::class )->writeln( sprintf( '<warning>Warning: Unknown environment type "%s" found in cache. Falling back to "e2e" environment type.</warning>', $environment_type ) );
				$env_info = new E2EEnvInfo();
				// Override the environment type to e2e to prevent future issues.
				$env_info_array['environment'] = 'e2e';
				break;
		}

		// Set basic properties
		$env_info->environment   = $environment_type;
		$env_info->env_id        = $env_info_array['env_id'] ?? ( 'qitenv' . bin2hex( random_bytes( 8 ) ) );
		$env_info->temporary_env = $env_info_array['temporary_env'] ?? normalize_path( Environment::get_temp_envs_dir() . $environment_type . '-' . $env_info->env_id );
		$env_info->created_at    = $env_info_array['created_at'] ?? time();
		$env_info->status        = $env_info_array['status'] ?? 'pending';

		// Handle tunnel
		$env_info->tunnel      = ! empty( $env_info_array['tunnel'] );
		$env_info->tunnel_type = $env_info_array['tunnel_type'] ?? 'no_tunnel';

		// Set domain based on environment exposure
		if ( isset( $env_info_array['domain'] ) ) {
			$env_info->domain = $env_info_array['domain'];
		} elseif ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) === 'DOCKER' ) {
			// Environment accessible from inside Docker containers.
			$env_info->domain = "qitenvnginx{$env_info->env_id}";
		} else {
			// Environment accessible from host.
			$env_info->domain = getenv( 'QIT_DOMAIN' ) ?: 'localhost';
		}

		// Handle plugins and themes
		if ( isset( $env_info_array['plugins'] ) && is_array( $env_info_array['plugins'] ) ) {
			$env_info->plugins = array_map( function ( $plugin_data ) {
				// Skip non-array data (cleanup bad cached data)
				if ( ! is_array( $plugin_data ) ) {
					return null;
				}
				// Skip invalid plugins with empty slugs (cleanup bad cached data)
				if ( empty( $plugin_data['slug'] ) ) {
					return null;
				}
				// Valid plugin data
				return Extension::fromArray( $plugin_data );
			}, $env_info_array['plugins'] );
			// Filter out null values
			$env_info->plugins = array_filter( $env_info->plugins );
		}

		if ( isset( $env_info_array['themes'] ) && is_array( $env_info_array['themes'] ) ) {
			$env_info->themes = array_map( function ( $theme_data ) {
				// Skip non-array data (cleanup bad cached data)
				if ( ! is_array( $theme_data ) ) {
					return null;
				}
				// Skip invalid themes with empty slugs (cleanup bad cached data)
				if ( empty( $theme_data['slug'] ) ) {
					return null;
				}
				// Valid theme data
				return Extension::fromArray( $theme_data );
			}, $env_info_array['themes'] );
			// Filter out null values
			$env_info->themes = array_filter( $env_info->themes );
		}

		// Set other properties dynamically
		$ignore_keys = [
			'json',
			'help',
			'quiet',
			'verbose',
			'version',
			'no-interaction', // Symfony boilerplate
			'env_file', // Handled separately
			'extension_set', // Handled elsewhere
			'plugins', // Already handled above and converted to Extension objects
			'themes', // Already handled above and converted to Extension objects
			'domain', // Already handled above for environments that support it
		];

		foreach ( $env_info_array as $key => $value ) {
			if ( in_array( $key, $ignore_keys, true ) ) {
				continue;
			}

			if ( property_exists( $env_info, $key ) ) {
				// Prevent a plain array from overwriting a freshly-instantiated object
				// Use reflection to check if property is initialized
				$reflection = new \ReflectionProperty( $env_info, $key );
				if ( $reflection->isInitialized( $env_info ) ) {
					$current_value = $env_info->$key;
					if ( is_object( $current_value ) && ! is_object( $value ) ) {
						continue;
					}
				}
				$env_info->$key = $value;
			} else {
				if ( App::make( Output::class )->isVeryVerbose() ) {
					App::make( Output::class )->writeln(
						sprintf( '<comment>Warning: Key "%s" not found in environment info.</comment>', $key )
					);
				}
				$env_info->extra[ $key ] = $value;
			}
		}

		return $env_info;
	}
}
