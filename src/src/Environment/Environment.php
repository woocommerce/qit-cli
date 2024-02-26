<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\SafeRemove;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

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

	/** @var OutputInterface */
	protected $output;

	public function __construct(
		EnvironmentDownloader $environment_Downloader,
		Cache $cache,
		EnvironmentMonitor $environment_monitor,
		Filesystem $filesystem,
		Docker $docker,
		OutputInterface $output
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
		$this->output                     = $output;
	}

	abstract public function get_name(): string;

	abstract protected function prepare_generate_docker_compose( Process $process ): void;

	public function up(): void {
		// Start the benchmark.
		$start = microtime( true );

		$this->environment_downloader->maybe_download( $this->get_name() );
		$this->maybe_create_cache_dir();
		$this->copy_environment();
		$env_info = $this->init_env_info();
		$this->environment_monitor->environment_added_or_updated( $env_info );
		$this->generate_docker_compose();
		$this->up_docker_compose( $env_info );

		$server_started = microtime( true );
		echo "Server started at " . round( microtime( true ) - $start, 2 ) . " seconds\n";

		echo "Temporary environment: $this->temporary_environment_path\n";
	}

	// Copies the source environment to the temporary environment.
	protected function copy_environment(): void {
		$this->filesystem->mirror( $this->source_environment_path, $this->temporary_environment_path );

		if ( ! file_exists( $this->temporary_environment_path . '/docker-compose-generator.php' ) ) {
			throw new \RuntimeException( "Failed to copy the environment." );
		}
	}

	// Creates the cache directory if it doesn't exist.
	protected function maybe_create_cache_dir(): void {
		if ( ! file_exists( $this->cache_dir ) ) {
			if ( mkdir( $this->cache_dir, 0755 ) === false ) {
				throw new \RuntimeException( 'Failed to create cache directory on ' . $this->cache_dir );
			}
		}
	}

	// Initialize the default env info for the temporary environment.
	protected function init_env_info(): EnvInfo {
		$env_info                = new EnvInfo();
		$env_info->type          = $this->get_name();
		$env_info->temporary_env = $this->temporary_environment_path;
		$env_info->created_at    = time();
		$env_info->status        = 'pending';

		return $env_info;
	}

	protected function generate_docker_compose(): void {
		$process = new Process( [ PHP_BINARY, $this->temporary_environment_path . '/docker-compose-generator.php' ] );
		$this->prepare_generate_docker_compose( $process );
		$process->setEnv( array_merge( $process->getEnv(), [
			'CACHE_DIR'  => $this->cache_dir,
			'QIT_ENV_ID' => $this->temporary_environment_id,
		] ) );

		try {
			$process->mustRun();

			if ( $this->output->isVerbose() ) {
				$this->output->writeln( $process->getOutput() );
			}
		} catch ( \Exception $e ) {
			throw new \RuntimeException( "Failed to generate docker-compose.yml" );
		}
	}

	protected function up_docker_compose( EnvInfo $env_info ) {
		$this->add_container_names( $env_info );

		$up_process = new Process( array_merge( $this->docker->find_docker_compose(), [ '-f', $env_info->temporary_env . '/docker-compose.yml', 'up', '-d' ] ) );
		$up_process->setTty( true );

		$up_process->run( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );

		if ( ! $up_process->isSuccessful() ) {
			$this->down( $env_info );
			throw new \RuntimeException( "Failed to start the environment." );
		}

		$env_info->status = 'started';

		$this->environment_monitor->environment_added_or_updated( $env_info );
	}

	public function down( EnvInfo $env_info ): void {
		$down_process = new Process( array_merge( $this->docker->find_docker_compose(), [ '-f', $env_info->temporary_env . '/docker-compose.yml', 'down' ] ) );
		$down_process->setTty( true );

		$down_process->run( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );

		if ( $down_process->isSuccessful() ) {
			$this->output->writeln( "Removing temporary environment: " . $env_info->temporary_env );
			SafeRemove::delete_dir( $env_info->temporary_env, static::get_temp_envs_dir() );
		} else {
			$this->output->writeln( "Failed to remove temporary environment: " . $env_info->temporary_env );
		}

		$this->environment_monitor->environment_stopped( $env_info );
	}

	protected function add_container_names( EnvInfo $env_info ): void {
		/*
		 * Get the docker containers names, eg:
		 * [+] Running 4/0
		 *  ✔ DRY-RUN MODE -  Container qit_env_cache_65dcc53c66545  Running                                                                                                                                                                                                                                              0.0s
		 *  ✔ DRY-RUN MODE -  Container qit_env_db_65dcc53c66545     Running                                                                                                                                                                                                                                              0.0s
		 *  ✔ DRY-RUN MODE -  Container qit_env_php_65dcc53c66545    Running                                                                                                                                                                                                                                              0.0s
		 *  ✔ DRY-RUN MODE -  Container qit_env_nginx_65dcc53c66545  Created                                                                                                                                                                                                                                              0.0s
		 * end of 'compose up' output, interactive run is not supported in dry-run mode
		 */
		$up_dry_run_process = new Process( array_merge( $this->docker->find_docker_compose(), [ '-f', $this->temporary_environment_path . '/docker-compose.yml', 'up', '--dry-run' ] ) );
		$up_dry_run_process->run();

		$containers = [];

		foreach ( explode( "\n", $up_dry_run_process->getOutput() . "\n" . $up_dry_run_process->getErrorOutput() ) as $line ) {
			if ( preg_match( '/(qit_env_[\w\d]+)/', $line, $matches ) ) {
				$containers[] = $matches[1];
			}
		}

		$containers = array_unique( $containers );

		if ( empty( $containers ) ) {
			throw new \RuntimeException( "Failed to start the environment. No containers found." );
		}

		$env_info->docker_images = $containers;
	}

	public static function get_temp_envs_dir(): string {
		$dir = Config::get_qit_dir() . '/temporary-envs/';

		if ( ! file_exists( $dir ) && ! mkdir( $dir, 0755 ) ) {
			throw new \RuntimeException( "Failed to create temporary environments directory." );
		}

		return $dir;
	}
}