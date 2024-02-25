<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Cache;
use QIT_CLI\Config;
use Symfony\Component\Filesystem\Filesystem;

abstract class Environment {
	/** @var EnvironmentDownloader */
	protected $environment_downloader;

	/** @var Cache */
	protected $cache;

	/** @var EnvironmentMonitor */
	protected $environment_monitor;

	/** @var Filesystem */
	protected $filesystem;

	/** @var Docker */
	protected $docker;

	/** @var string */
	protected $cache_dir;

	/** @var string */
	protected $source_environment_path;

	/** @var string */
	protected $temporary_environment_id;

	/** @var string */
	protected $temporary_environment_path;

	public function __construct(
		EnvironmentDownloader $environment_Downloader,
		Cache $cache,
		EnvironmentMonitor $environment_monitor,
		Filesystem $filesystem,
		Docker $docker
	) {
		$this->environment_downloader = $environment_Downloader;
		$this->cache                  = $cache;
		$this->environment_monitor    = $environment_monitor;
		$this->filesystem             = $filesystem;
		$this->docker                 = $docker;

		$this->cache_dir                  = Config::get_qit_dir() . '/cache';
		$this->source_environment_path    = Config::get_qit_dir() . '/environments/' . $this->get_name();
		$this->temporary_environment_id   = uniqid();
		$this->temporary_environment_path = static::get_temp_envs_dir() . $this->get_name() . '-' . $this->temporary_environment_id;
	}

	abstract public function get_name(): string;

	abstract protected function do_up( EnvInfo $env_info ): void;

	public function up(): void {
		$this->environment_downloader->maybe_download( $this->get_name() );

		// Make sure cache directory exists.
		if ( ! file_exists( $this->cache_dir ) ) {
			if ( mkdir( $this->cache_dir ) === false ) {
				throw new \RuntimeException( 'Failed to create cache directory on ' . $this->cache_dir );
			}
		}

		// Start the benchmark.
		$start = microtime( true );

		// Copy the reference environment to a temporary one.
		$this->filesystem->mirror( $this->source_environment_path, $this->temporary_environment_path );
		if ( ! file_exists( $this->temporary_environment_path . '/docker-compose-generator.php' ) ) {
			throw new \RuntimeException( "Failed to copy the environment." );
		}

		// Create the Env Info.
		$env_info                = new EnvInfo();
		$env_info->type          = $this->get_name();
		$env_info->temporary_env = $this->temporary_environment_path;
		$env_info->created_at    = time();
		$env_info->status        = 'pending';

		$this->environment_monitor->environment_added_or_updated( $env_info );

		$this->do_up( $env_info );

		$server_started = microtime( true );
		echo "Server started at " . round( microtime( true ) - $start, 2 ) . " seconds\n";

		echo "Temporary environment: $this->temporary_environment_path\n";
	}

	public function down( string $env_info_id ) {
		// Stops the given environment.
		$docker_compose = $this->docker->find_docker_compose();

		$env_info = $this->environment_monitor->get_env_info_by_id( $env_info_id );

		exec( $docker_compose . ' -f ' . $env_info->temporary_env . '/docker-compose.yml down', $down_output, $down_result_code );
		$this->environment_monitor->environment_stopped( $env_info );
	}

	public static function get_temp_envs_dir(): string {
		$dir = Config::get_qit_dir() . '/temporary-envs/';

		if ( ! file_exists( $dir ) && ! mkdir( $dir, 0755 ) ) {
			throw new \RuntimeException( "Failed to create temporary environments directory." );
		}

		return $dir;
	}
}