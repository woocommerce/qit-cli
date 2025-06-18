<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\Environment\Extension;
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

	public array $extra = [];

	/** @var string */
	public string $environment;

	/** @var string */
	public string $dependencies_mode;

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
	public array $env = [];

	/**
	 * @var bool Whether to use tunnels to expose the environment.
	 */
	public bool $tunnel = false;

	public string $tunnel_type = 'no_tunnel';

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		$data = get_object_vars( $this );

		// Ensure plugins and themes are serialized correctly
		$data['plugins'] = array_map( function ( Extension $plugin ) {
			return [
				'slug'                => $plugin->slug,
				'type'                => $plugin->type,
				'from'                => $plugin->from,
				'version'             => $plugin->version,
				'source'              => $plugin->source,
				'directory'           => $plugin->directory,
				'priority'            => $plugin->priority,
				'added_automatically' => $plugin->added_automatically,
			];
		}, $this->plugins );

		$data['themes'] = array_map( function ( Extension $theme ) {
			return [
				'slug'                => $theme->slug,
				'type'                => $theme->type,
				'from'                => $theme->from,
				'version'             => $theme->version,
				'source'              => $theme->source,
				'directory'           => $theme->directory,
				'priority'            => $theme->priority,
				'added_automatically' => $theme->added_automatically,
			];
		}, $this->themes );

		return $data;
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
	 */
	public static function from_array( array $env_info_array ): EnvInfo {
		$environment = $env_info_array['environment'] ?? 'e2e';

		switch ( $environment ) {
			case 'e2e':
				$env_info = new E2EEnvInfo();
				break;
			default:
				throw new \RuntimeException( "Invalid environment type: $environment" );
		}

		// Set basic properties
		$env_info->environment   = $environment;
		$env_info->env_id        = $env_info_array['env_id'] ?? uniqid();
		$env_info->temporary_env = $env_info_array['temporary_env'] ?? normalize_path( Environment::get_temp_envs_dir() . $environment . '-' . $env_info->env_id );
		$env_info->created_at    = $env_info_array['created_at'] ?? time();
		$env_info->status        = $env_info_array['status'] ?? 'pending';

		// Handle tunnel
		$env_info->tunnel      = ! empty( $env_info_array['tunnel'] );
		$env_info->tunnel_type = $env_info_array['tunnel_type'] ?? 'no_tunnel';

		// Set domain for E2E environments
		if ( $env_info instanceof E2EEnvInfo ) {
			$env_info->domain = $env_info_array['domain'] ?? ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) === 'DOCKER' ? "qitenvnginx{$env_info->env_id}" : ( getenv( 'QIT_DOMAIN' ) ?: 'localhost' ) );
		}

		// Handle plugins and themes
		if ( isset( $env_info_array['plugins'] ) && is_array( $env_info_array['plugins'] ) ) {
			$env_info->plugins = array_map( function ( $plugin_data ) {
				$plugin                      = new Extension( $plugin_data['slug'] ?? '', $plugin_data['type'] ?? 'plugin' );
				$plugin->from                = $plugin_data['from'] ?? 'wporg';
				$plugin->version             = $plugin_data['version'] ?? 'stable';
				$plugin->source              = $plugin_data['source'] ?? null;
				$plugin->directory           = $plugin_data['directory'] ?? null;
				$plugin->priority            = $plugin_data['priority'] ?? Extension::PRIORITY_NORMAL;
				$plugin->added_automatically = $plugin_data['added_automatically'] ?? null;

				return $plugin;
			}, $env_info_array['plugins'] );
		}

		if ( isset( $env_info_array['themes'] ) && is_array( $env_info_array['themes'] ) ) {
			$env_info->themes = array_map( function ( $theme_data ) {
				$theme                      = new Extension( $theme_data['slug'] ?? '', $theme_data['type'] ?? 'theme' );
				$theme->from                = $theme_data['from'] ?? 'wporg';
				$theme->version             = $theme_data['version'] ?? 'stable';
				$theme->source              = $theme_data['source'] ?? null;
				$theme->directory           = $theme_data['directory'] ?? null;
				$theme->priority            = $theme_data['priority'] ?? Extension::PRIORITY_NORMAL;
				$theme->added_automatically = $theme_data['added_automatically'] ?? null;

				return $theme;
			}, $env_info_array['themes'] );
		}

		// Set other properties dynamically
		$ignore_keys = [
			'json',
			'help',
			'quiet',
			'verbose',
			'version',
			'no-interaction', // Symfony boilerplate
			'env',
			'env_file', // Handled separately
			'extension_set', // Handled elsewhere
		];

		foreach ( $env_info_array as $key => $value ) {
			if ( in_array( $key, $ignore_keys, true ) ) {
				continue;
			}

			if ( property_exists( $env_info, $key ) ) {
				$env_info->$key = $value;
			} else {
				App::make( Output::class )->writeln( sprintf( '<comment>Warning: Key "%s" not found in environment info.</comment>', $key ) );
				$env_info->extra[ $key ] = $value;
			}
		}

		return $env_info;
	}
}