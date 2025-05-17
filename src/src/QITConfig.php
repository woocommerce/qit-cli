<?php

namespace QIT_CLI;

class QITConfig {
	private array $config = [];
	private string $configFile;

	public function __construct( string $configFile = 'qit.json' ) {
		$this->configFile = $configFile;
		$this->load_config();
	}

	private function load_config(): void {
		if ( ! file_exists( $this->configFile ) ) {
			$this->config = [];

			return;
		}

		$contents = file_get_contents( $this->configFile );
		$decoded  = json_decode( $contents, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
			throw new \RuntimeException( 'Invalid qit.json format. Must be a JSON object.' );
		}

		$this->config = $decoded;
	}

	public function get( string $key, $default = null ) {
		return $this->config[ $key ] ?? $default;
	}

	public function getAll(): array {
		return $this->config;
	}

	public function getConfigFile(): string {
		return $this->configFile;
	}
}