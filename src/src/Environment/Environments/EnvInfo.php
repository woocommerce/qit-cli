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
		'env_id',
		'domain',
		'environment',
	];

	/** @var string */
	public $environment;

	/** @var string */
	public $temporary_env;

	/** @var int */
	public $created_at;

	/** @var string */
	public $status;

	/** @var string */
	public $env_id;

	/**
	 * Holds an array of volume mappings, where each key is a container path and its value is the corresponding local path.
	 *
	 * @var array<string, string> $volumes Each element of the array is:
	 *                                    - Key: Container path (string)
	 *                                    - Value: Local path (string) (Optional ":<FLAGS>", such as ":ro" for read-only)
	 */
	public $volumes = [];

	/**
	 * @var array<string> Array of docker images associated with this environment.
	 * @example [ 'qit_php_123456', 'qit_db_123456', 'qit_nginx_123456' ]
	 */
	public $docker_images = [];

	/** @var string */
	public $docker_network;

	/**
	 * @var array<string> Array of PHP extensions to be installed in the environment.
	 */
	public $php_extensions = [];

	/**
	 * @var array<Extension> Array of plugins to feed to WP CLI.
	 */
	public $plugins = [];

	/**
	 * @var array<Extension> Array of themes to feed to WP CLI.
	 */
	public $themes = [];

	/**
	 * @var bool Whether to use tunnels to expose the environment.
	 */
	public $tunnel = false;

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this;
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
	 * @param array<string,scalar|array<scalar>> $env_info_array
	 */
	public static function from_array( array $env_info_array ): EnvInfo {
		switch ( $env_info_array['environment'] ?? 'e2e' ) {
			case 'e2e':
				$env_info = new E2EEnvInfo();
				break;
			default:
				throw new \RuntimeException( 'Invalid environment type.' );
		}

		$env_info->environment   = $env_info_array['environment'] ?? 'e2e';
		$env_info->env_id        = uniqid();
		$env_info->temporary_env = normalize_path( Environment::get_temp_envs_dir() . $env_info->environment . '-' . $env_info->env_id );
		$env_info->created_at    = time();
		$env_info->status        = 'pending';

		if ( ! empty( $env_info_array['tunnel'] ) ) {
			$env_info->tunnel = true;
		}

		if ( $env_info instanceof E2EEnvInfo ) {
			if ( getenv( 'QIT_EXPOSE_ENVIRONMENT_TO' ) === 'DOCKER' ) {
				// Environment accessible from inside Docker containers.
				$env_info->domain = "qitenvnginx{$env_info->env_id}";
			} else {
				// Environment accessible from host.
				$env_info->domain = getenv( 'QIT_DOMAIN' ) ?: 'localhost';
			}
		}

		foreach ( $env_info_array as $key => $value ) {
			if ( property_exists( $env_info, $key ) ) {
				$env_info->$key = $value;
			} else {
				// Boilerplate options added by Symfony Console.
				$ignore_keys = [
					'json',
					'help',
					'quiet',
					'verbose',
					'version',
					'no-interaction',
				];

				if ( in_array( $key, $ignore_keys, true ) ) {
					continue;
				}

				App::make( Output::class )->writeln( sprintf( '<comment>Warning: Key "%s" not found in environment info.</comment>', $key ) );
			}
		}

		return $env_info;
	}
}
