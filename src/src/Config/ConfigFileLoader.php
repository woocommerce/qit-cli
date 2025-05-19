<?php

namespace QIT_CLI\Config;

class ConfigFileLoader {
	private string $config_file;
	private array $raw_config = [];

	public function load_config( string $config_file ): array {
		$this->config_file = $config_file;
		if ( ! file_exists( $config_file ) ) {
			$this->raw_config = [];

			return [];
		}

		$contents = file_get_contents( $config_file );
		$decoded  = json_decode( $contents, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
			throw new \RuntimeException( 'Invalid qit.json format. Must be a JSON object.' );
		}

		$this->raw_config = $decoded;

		return $decoded;
	}

	public function get_raw_config(): array {
		return $this->raw_config;
	}

	public function get_config_file(): string {
		return $this->config_file;
	}
}