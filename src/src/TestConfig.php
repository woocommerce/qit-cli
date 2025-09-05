<?php

namespace QIT_CLI;

abstract class TestConfig {
	/** @var array<string, mixed> */
	protected array $config;

	/**
	 * @param array<string, mixed> $config
	 */
	public function __construct( array $config ) {
		$this->config = $config;
	}

	abstract public function get_test_type(): string;

	/**
	 * @return array<string, mixed>
	 */
	public function get_config(): array {
		return $this->config;
	}
}
