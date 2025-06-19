<?php

namespace QIT_CLI\AI;

use Symfony\Component\Process\Process;

class Ollama {
	public function is_available(): bool {
		$process = new Process( [ 'ollama', '--version' ] );
		$process->run();

		return $process->isSuccessful();
	}
}