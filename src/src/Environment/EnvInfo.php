<?php

namespace QIT_CLI\Environment;

class EnvInfo implements \JsonSerializable {
	/** @var string */
	public $type;

	/** @var string */
	public $temporary_env;

	/** @var int */
	public $created_at;

	/** @var string */
	public $status;

	/** @var string */
	public $env_id;

	/**
	 * @var array<string> Array of docker images associated with this environment.
	 * @example [ 'qit_php_123456', 'qit_db_123456', 'qit_nginx_123456' ]
	 */
	public $docker_images;

	public function jsonSerialize() {
		return $this;
	}

	public function get_docker_container( string $docker_container ): string {
		$docker_images = $this->docker_images;

		// Find docker image string that matches the $image
		$docker_image = array_filter( $docker_images, function ( $docker_image ) use ( $docker_container ) {
			return strpos( $docker_image, $docker_container ) !== false;
		} );

		// Bail if more than one or empty.
		if ( count( $docker_image ) !== 1 ) {
			throw new \RuntimeException( 'Could not find docker image' );
		}

		return array_shift( $docker_image );
	}

	public static function from_array( array $decoded_json ): EnvInfo {
		$instance = new self();

		foreach ( $decoded_json as $key => $value ) {
			if ( property_exists( $instance, $key ) ) {
				$instance->$key = $value;
			}
		}

		return $instance;
	}
}