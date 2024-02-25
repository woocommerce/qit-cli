<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use QIT_CLI\Environment\EnvInfo;
use QIT_CLI\Environment\Environment;
use QIT_CLI\IO\Output;

class E2EEnvironment extends Environment {
	/** @var string */
	protected $description = 'E2E Environment';

	public function get_name(): string {
		return 'e2e';
	}

	public function do_up( EnvInfo $env_info ): void {
		// Generate docker-compose.yml in the temporary environment.
		passthru( "CACHE_DIR={$this->cache_dir} " . PHP_BINARY . ' ' . $this->temporary_environment_path . '/docker-compose-generator.php' );

		$docker_compose = $this->docker->find_docker_compose();

		// Start docker compose in temporary environment.
		exec( $docker_compose . ' -f ' . $this->temporary_environment_path . '/docker-compose.yml up -d', $up_output, $up_result_code );

		if ( $up_result_code !== 0 ) {
			exec( $docker_compose . ' -f ' . $this->temporary_environment_path . '/docker-compose.yml down', $down_output, $down_result_code );

			$this->filesystem->remove( $this->temporary_environment_path );

			if ( file_exists( $this->temporary_environment_path . '/docker-compose-generator.php' ) ) {
				App::make( Output::class )->writeln( sprintf( '<error>Failed to delete the temporary environment: %s</error>', $this->temporary_environment_path ) );
			}

			$this->environment_monitor->environment_stopped( $env_info );

			throw new \RuntimeException( "Failed to start the environment." );
		}

		$env_info->status = 'started';

		$this->environment_monitor->environment_added_or_updated( $env_info );


	}
}