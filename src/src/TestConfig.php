<?php

namespace QIT_CLI;

abstract class TestConfig {
	protected array $config;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	abstract public function get_test_type(): string;

	public function get_config(): array {
		return $this->config;
	}
}
