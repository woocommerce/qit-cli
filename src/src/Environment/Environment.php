<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Cache;

abstract class Environment {
	abstract public static function get_name(): string;

	abstract public function up(): void;

	abstract protected function maybe_download(): void;
}