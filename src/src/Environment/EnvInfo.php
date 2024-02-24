<?php

namespace QIT_CLI\Environment;

use http\Env;

class EnvInfo {
	/** @var string */
	public $type;

	/** @var string */
	public $temporary_env;

	/** @var int */
	public $created_at;

	/** @var string */
	public $status;

	public static function from_array( array $data ): EnvInfo {
		$env_info = new EnvInfo();
		foreach ( $data as $key => $value ) {
			if ( property_exists( $env_info, $key ) ) {
				$env_info->$key = $value;
			}
		}

		return $env_info;
	}

	public function get_id(): string {
		return md5( $this->temporary_env );
	}
}