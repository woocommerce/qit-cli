<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\Environment\Environment;
use Symfony\Component\Process\Process;

class E2EEnvironment extends Environment {
	/** @var string */
	protected $description = 'E2E Environment';

	public function get_name(): string {
		return 'e2e';
	}

	protected function prepare_generate_docker_compose( Process $process ): void {
		// no-op.
	}
}