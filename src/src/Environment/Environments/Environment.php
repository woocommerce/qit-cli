<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\EnvironmentDownloader;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\SafeRemove;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use function QIT_CLI\is_windows;
use function QIT_CLI\normalize_path;
use function QIT_CLI\use_tty;

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

	/** @var EnvInfo */
	protected $env_info;

	/** @var OutputInterface */
	protected $output;

	/** @var array<string,array{local: string, in_container: string}> */
	protected $volumes;

	public function __construct(
		EnvironmentDownloader $environment_downloader,
		Cache $cache,
		EnvironmentMonitor $environment_monitor,
		Filesystem $filesystem,
		Docker $docker,
		OutputInterface $output
	) {
		$this->environment_downloader = $environment_downloader;
		$this->cache                  = $cache;
		$this->environment_monitor    = $environment_monitor;
		$this->filesystem             = $filesystem;
		$this->docker                 = $docker;

		$this->cache_dir               = normalize_path( Config::get_qit_dir() . 'cache' );
		$this->source_environment_path = normalize_path( Config::get_qit_dir() . 'environments/' . $this->get_name() );
		$this->output                  = $output;
	}

	abstract public function get_name(): string;

	public function init( EnvInfo $env_info ): void {
		$this->env_info = $env_info;
	}

	/**
	 * The concrete classes that implement this method must call "$this->do_up()".
	 *
	 * @return void
	 */
	abstract public function up(): void;

	/**
	 * @return array<string,string>
	 */
	abstract protected function get_generate_docker_compose_envs(): array;

	abstract protected function post_generate_docker_compose(): void;

	abstract protected function post_up(): void;

	abstract protected function additional_output(): void;

	/** @param array<string,array{local: string, in_container: string}> $volumes */
	public function set_volumes( array $volumes ): void {
		$this->volumes = $volumes;
	}

	protected function do_up( bool $attached = false ): void {
		// Start the benchmark.
		$start = microtime( true );

		$this->environment_downloader->maybe_download( $this->get_name() );
		$this->maybe_create_cache_dir();
		$this->copy_environment();
		$this->environment_monitor->environment_added_or_updated( $this->env_info );
		$this->generate_docker_compose();
		$this->post_generate_docker_compose();
		$this->up_docker_compose( $attached );
		$this->post_up();

		$this->output->writeln( 'Server started at ' . round( microtime( true ) - $start, 2 ) . ' seconds' );
		$this->output->writeln( "Temporary environment: {$this->env_info->temporary_env}\n" );
		$this->additional_output();
	}

	/**
	 * Copies the source environment to the temporary environment.
	 */
	protected function copy_environment(): void {
		$this->filesystem->mirror( $this->source_environment_path, $this->env_info->temporary_env );

		if ( ! file_exists( $this->env_info->temporary_env . '/docker-compose-generator.php' ) ) {
			throw new \RuntimeException( 'Failed to copy the environment.' );
		}
	}

	/**
	 * Creates the cache directory if it doesn't exist.
	 */
	protected function maybe_create_cache_dir(): void {
		if ( ! file_exists( $this->cache_dir ) ) {
			if ( mkdir( $this->cache_dir, 0755 ) === false ) {
				throw new \RuntimeException( 'Failed to create cache directory on ' . $this->cache_dir );
			}
		}
	}

	protected function generate_docker_compose(): void {
		$process = new Process( [ PHP_BINARY, $this->env_info->temporary_env . '/docker-compose-generator.php' ] );

		$volumes = [
			'/var/www/html' => "{$this->env_info->temporary_env}/html",
			'/qit/bin'      => "{$this->env_info->temporary_env}/bin",
			'/qit/cache'    => $this->cache_dir,
		];

		// Map volumes from the command line.
		if ( ! empty( $this->volumes ) ) {
			foreach ( $this->volumes as $v ) {
				if ( array_key_exists( $v['in_container'], $volumes ) ) {
					throw new \RuntimeException( "Volume {$v['in_container']} already exists." );
				}

				$volumes[ $v['in_container'] ] = $v['local'];
			}
		}

		// Map mu-plugins individually instead of the whole container to avoid bringing mu-plugins in container back to host.
		foreach ( new \DirectoryIterator( $this->env_info->temporary_env . '/mu-plugins' ) as $mu_plugin ) {
			/** @var \SplFileInfo $mu_plugin */
			if ( $mu_plugin->isFile() && $mu_plugin->getExtension() === 'php' ) {
				$volumes[ '/var/www/html/wp-content/mu-plugins/' . $mu_plugin->getFilename() ] = "{$this->env_info->temporary_env}/mu-plugins/{$mu_plugin->getFilename()}";
			}
		}

		/*
		 * Create directories if needed so that they are mapped to inside
		 * the container with the correct permissions, unless they are a file name,
		 * at which point create the parent dir.
		 */
		foreach ( $volumes as $in_container => $local ) {
			if ( stripos( $local, '.' ) === false ) {
				if ( ! file_exists( $local ) ) {
					if ( ! mkdir( $local, 0755, true ) ) {
						throw new \RuntimeException( "Failed to create volume directory: $local" );
					}
				}
			} else {
				$dir = dirname( $local );
				if ( ! file_exists( $dir ) ) {
					if ( ! mkdir( $dir, 0755, true ) ) {
						throw new \RuntimeException( "Failed to create volume directory: $dir" );
					}
				}
			}
		}

		$process->setEnv( array_merge( $process->getEnv(), [
			'QIT_ENV_ID'         => $this->env_info->env_id,
			'VOLUMES'            => json_encode( $volumes ),
			'NORMALIZED_ENV_DIR' => $this->env_info->temporary_env,
			'DOMAIN'             => $this->env_info->domain,
			'QIT_DOCKER_NGINX'   => 'yes', // Default. Might be overridden by the concrete environment.
			'QIT_DOCKER_REDIS'   => 'no', // Default. Might be overridden by the concrete environment.
		], $this->get_generate_docker_compose_envs() ) );

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( $process->getCommandLine() );
			$this->output->writeln( json_encode( $process->getEnv(), JSON_PRETTY_PRINT ) );
		}

		$process->run( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( 'Failed to generate docker-compose.yml' );
		}
	}

	protected function up_docker_compose( bool $attached ): void {
		$this->add_container_names();

		$args = array_merge( $this->docker->find_docker_compose(), [ '-f', $this->env_info->temporary_env . '/docker-compose.yml', 'up' ] );

		if ( ! $attached ) {
			$args[] = '-d';
		}

		$up_process = new Process( $args );

		try {
			$u = Docker::get_user_and_group();
			$up_process->setEnv( array_merge( $up_process->getEnv(), [
				'FIXUID' => $u['user'],
				'FIXGID' => $u['group'],
			] ) );
		} catch ( \RuntimeException $e ) {
			if ( ! is_windows() ) {
				$this->output->writeln( '<info>To run the environment with the correct permissions, please install the posix extension on PHP, or set QIT_DOCKER_USER/QIT_DOCKER_GROUP env vars.</info>' );
			}
		}

		$up_process->setTimeout( 300 );
		$up_process->setIdleTimeout( 300 );
		$up_process->setTty( use_tty() );

		$up_process->run( function ( $type, $buffer ) {
			$this->output->write( $buffer );
		} );

		if ( ! $up_process->isSuccessful() ) {
			static::down( $this->env_info );
			throw new \RuntimeException( 'Failed to start the environment.' );
		}

		$this->env_info->status = 'started';

		$this->environment_monitor->environment_added_or_updated( $this->env_info );
	}

	public static function down( EnvInfo $env_info ): void {
		$output              = App::make( OutputInterface::class );
		$environment_monitor = App::make( EnvironmentMonitor::class );

		if ( ! file_exists( $env_info->temporary_env ) ) {
			if ( $output->isVerbose() ) {
				$output->writeln( sprintf( 'Tried to stop environment %s, but it does not exist.', $env_info->temporary_env ) );
			}

			// It's fine to just remove it, as any dangling leftovers will be cleaned up later.
			$environment_monitor->environment_stopped( $env_info );

			return;
		}

		$down_process = new Process( array_merge( App::make( Docker::class )->find_docker_compose(), [ '-f', $env_info->temporary_env . '/docker-compose.yml', 'down' ] ) );
		$down_process->setTimeout( 300 );
		$down_process->setIdleTimeout( 300 );
		$down_process->setTty( use_tty() );

		$down_process->run( static function ( $type, $buffer ) use ( $output ) {
			$output->write( $buffer );
		} );

		if ( $down_process->isSuccessful() ) {
			$output->writeln( 'Removing temporary environment: ' . $env_info->temporary_env );
			SafeRemove::delete_dir( $env_info->temporary_env, static::get_temp_envs_dir() );
		} else {
			$output->writeln( 'Failed to remove temporary environment: ' . $env_info->temporary_env );
		}

		$environment_monitor->environment_stopped( $env_info );
	}

	protected function add_container_names(): void {
		$containers = [];

		$file = new \SplFileObject( $this->env_info->temporary_env . '/docker-compose.yml' );
		while ( ! $file->eof() ) {
			$line = $file->fgets();
			if ( preg_match( '/^\s+container_name:\s*(\w+)/', $line, $matches ) ) {
				$containers[] = $matches[1];
			}
		}
		$containers = array_unique( $containers );

		if ( empty( $containers ) ) {
			throw new \RuntimeException( 'Failed to start the environment. No containers found.' );
		}

		$this->env_info->docker_images = $containers;
	}

	protected function get_nginx_port(): int {
		$nginx_container = $this->env_info->get_docker_container( 'nginx' );
		if ( ! $nginx_container ) {
			throw new \Exception( 'Nginx container not found in docker containers.' );
		}

		$docker                 = $this->docker->find_docker();
		$get_nginx_port_process = new Process( [ $docker, 'port', $nginx_container, '80' ] );
		$get_nginx_port_process->run();

		if ( ! $get_nginx_port_process->isSuccessful() ) {
			throw new \RuntimeException( $get_nginx_port_process->getErrorOutput() );
		}

		$output = $get_nginx_port_process->getOutput();
		// The expected output format might be "0.0.0.0:PORT" or just "PORT".
		$output = trim( $output );
		if ( empty( $output ) ) {
			throw new \Exception( 'No output received from docker port command.' );
		}

		// Extract port from the output.
		if ( strpos( $output, ':' ) !== false ) {
			// If the output contains ":", split and get the port.
			$parts = explode( ':', $output );
			$port  = end( $parts ); // Get the last part which should be the port.
		} else {
			// If there's no ":", assume the entire output is the port.
			$port = $output;
		}

		// Validate that the port is an integer.
		if ( ! is_numeric( $port ) || intval( $port ) != $port ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			throw new \Exception( 'Invalid port number extracted: ' . $port );
		}

		return (int) $port;
	}

	public static function get_temp_envs_dir(): string {
		$dir = rtrim( Config::get_qit_dir(), '/' ) . '/temporary-envs/';

		if ( ! file_exists( $dir ) && ! mkdir( $dir, 0755 ) ) {
			throw new \RuntimeException( 'Failed to create temporary environments directory.' );
		}

		return $dir;
	}
}
