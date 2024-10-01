<?php

namespace QIT_CLI\Tunnel;

abstract class Tunnel {
	abstract public function get_id(): string;

	abstract public function is_available(): bool;

	abstract public function is_configured(): bool;

	abstract public function start_tunnel( string $local_url, string $env_id, string $network_name ): string;

	abstract public function stop_tunnel( string $env_id ): void;
}