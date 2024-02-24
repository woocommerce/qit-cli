<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\IO\Output;
use Symfony\Component\Filesystem\Filesystem;

class E2EEnvironment extends Environment {
	/** @var string */
	protected $description = 'E2E Environment';

	/** @var Cache */
	protected $cache;

	/** @var EnvironmentMonitor */
	protected $environment_monitor;

	/** @var Filesystem */
	protected $filesystem;

	/** @var Docker */
	protected $docker;

	public function __construct( Cache $cache, EnvironmentMonitor $environment_monitor, Filesystem $filesystem, Docker $docker ) {
		$this->cache               = $cache;
		$this->environment_monitor = $environment_monitor;
		$this->filesystem          = $filesystem;
		$this->docker              = $docker;
	}

	public static function get_name(): string {
		return 'e2e';
	}

	public function up(): void {
		$this->maybe_download();

		$cache_dir             = Config::get_qit_dir() . '/cache';
		$source_environment    = Config::get_qit_dir() . '/environments/' . self::get_name();
		$temporary_environment = Config::get_qit_dir() . '/temporary-envs/' . self::get_name() . uniqid();

		if ( ! file_exists( $cache_dir ) ) {
			mkdir( $cache_dir );
		}

		if ( ! file_exists( Config::get_qit_dir() . '/temporary-envs' ) ) {
			mkdir( Config::get_qit_dir() . '/temporary-envs' );
		}

		$start = microtime( true );

		// Copy the reference environment to a temporary one.
		$this->filesystem->mirror( $source_environment, $temporary_environment );

		if ( ! file_exists( $temporary_environment . '/docker-compose-generator.php' ) ) {
			throw new \RuntimeException( "Failed to copy the environment." );
		}

		$env_info                = new EnvInfo();
		$env_info->type          = self::get_name();
		$env_info->temporary_env = $temporary_environment;
		$env_info->created_at    = time();
		$env_info->status        = 'pending';

		$this->environment_monitor->environment_added_or_updated( $env_info );

		// Generate docker-compose.yml in the temporary environment.
		passthru( "CACHE_DIR=$cache_dir " . PHP_BINARY . ' ' . $temporary_environment . '/docker-compose-generator.php' );

		$docker_compose = $this->docker->find_docker_compose();

		// Start docker compose in temporary environment.
		exec( $docker_compose . ' -f ' . $temporary_environment . '/docker-compose.yml up -d', $up_output, $up_result_code );

		if ( $up_result_code !== 0 ) {
			exec( $docker_compose . ' -f ' . $temporary_environment . '/docker-compose.yml down', $down_output, $down_result_code );

			$this->filesystem->remove( $temporary_environment );

			if ( file_exists( $temporary_environment . '/docker-compose-generator.php' ) ) {
				App::make( Output::class )->writeln( sprintf( '<error>Failed to delete the temporary environment: %s</error>', $temporary_environment ) );
			}

			$this->environment_monitor->environment_stopped( $env_info );

			throw new \RuntimeException( "Failed to start the environment." );
		}

		$this->environment_monitor->environment_added_or_updated( $env_info );

		$server_started = microtime( true );
		echo "Server started at " . round( microtime( true ) - $start, 2 ) . " seconds\n";

		echo "Temporary environment: $temporary_environment\n";
	}

	public function down( string $env_info_id ) {
		// Stops the given environment.
		$docker_compose = $this->docker->find_docker_compose();

		$env_info = $this->environment_monitor->get_env_info_by_id( $env_info_id );

		exec( $docker_compose . ' -f ' . $env_info->temporary_env . '/docker-compose.yml down', $down_output, $down_result_code );
		$this->environment_monitor->environment_stopped( $env_info );
	}

	protected function maybe_download(): void {
		$backend_hashes = $this->cache->get_manager_sync_data( 'environments' );

		if ( ! isset( $backend_hashes[ self::get_name() ]['checksum'] ) || ! isset( $backend_hashes[ self::get_name() ]['url'] ) ) {
			throw new \RuntimeException( 'E2E environment not set or incomplete.' );
		}

		if ( $this->cache->get( 'e2e_environment_hash' ) !== $backend_hashes[ self::get_name() ]['checksum'] ) {
			if ( ! file_exists( Config::get_qit_dir() . '/environments' ) ) {
				mkdir( Config::get_qit_dir() . '/environments' );
			}
			// Download the environment.
			file_put_contents( sprintf( Config::get_qit_dir() . '/environments/%s.zip', self::get_name() ), file_get_contents( $backend_hashes[ self::get_name() ]['url'] ) );

			// Extract the environment.
			$zip = new \ZipArchive();
			$zip->open( sprintf( Config::get_qit_dir() . '/environments/%s.zip', self::get_name() ) );
			$zip->extractTo( Config::get_qit_dir() . '/environments/' . self::get_name() );
			$zip->close();

			$this->cache->set( 'e2e_environment_hash', $backend_hashes[ self::get_name() ], MONTH_IN_SECONDS );
		}
	}
}