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

	/**
	 * @var array<string> Array of docker images associated with this environment.
	 * @example [ 'qit_php_123456', 'qit_db_123456', 'qit_nginx_123456' ]
	 */
	public $docker_images;

	public function get_id(): string {
		return md5($this->temporary_env);
	}

	public function jsonSerialize() {
		return $this;
	}

	public static function from_json( string $json ): EnvInfo {
		$env_info = json_decode( $json, true );

		$instance = new self();

		foreach ( $env_info as $key => $value ) {
			if ( property_exists( $instance, $key ) ) {
				$instance->$key = $value;
			}
		}

		return $instance;
	}
}