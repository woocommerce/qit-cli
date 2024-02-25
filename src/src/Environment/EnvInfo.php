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