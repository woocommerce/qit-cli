<?php

namespace QIT_CLI\Environment\Environments;

abstract class EnvInfo {
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

	/** @var string */
	public $php_version;

	/**
	 * @var array<string> Array of docker images associated with this environment.
	 * @example [ 'qit_php_123456', 'qit_db_123456', 'qit_nginx_123456' ]
	 */
	public $docker_images;

	/**
	 *  This "DiscriminatorMap" is part of "Symfony Serializer" package, which we use to serialize/deserialize this
	 *  to/from JSON/YML. This property is so that when deserializing a JSON string, it can know which class to
	 *  instantiate based on the "type" property.
	 */
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
}
