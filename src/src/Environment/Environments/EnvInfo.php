<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use function QIT_CLI\normalize_path;

abstract class EnvInfo implements \JsonSerializable {
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

	/** @var string */
	public $php_version;

	/** @var string The domain being used. */
	public $domain;

	/**
	 * @var array<string> Array of docker images associated with this environment.
	 * @example [ 'qit_php_123456', 'qit_db_123456', 'qit_nginx_123456' ]
	 */
	public $docker_images;

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
	 * @param array<string,scalar> $decoded_json
	 */
	public static function from_array( array $decoded_json ): EnvInfo {
		switch ( $decoded_json['environment'] ?? 'e2e' ) {
			case 'e2e':
				$env_info = new E2EEnvInfo();
				break;
			default:
				throw new \RuntimeException( 'Invalid environment type.' );
		}

		$env_info->environment   = $decoded_json['environment'];
		$env_info->env_id        = uniqid();
		$env_info->temporary_env = normalize_path( Environment::get_temp_envs_dir() . $env_info->environment . '-' . $env_info->env_id );
		$env_info->created_at    = time();
		$env_info->status        = 'pending';
		$env_info->domain        = getenv( 'QIT_DOMAIN' ) ?: 'localhost';

		foreach ( $decoded_json as $key => $value ) {
			if ( property_exists( $env_info, $key ) ) {
				$env_info->$key = $value;
			}
		}

		return $env_info;
	}
}
