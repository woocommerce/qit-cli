<?php

namespace QIT_CLI;

abstract class TestConfig {
	protected array $config;

	public function __construct(array $config) {
		$this->config = $config;
	}

	abstract public function getTestType(): string;

	public function getConfig(): array {
		return $this->config;
	}
}