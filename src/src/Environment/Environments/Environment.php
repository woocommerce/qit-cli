<?php

namespace QIT_CLI\Environment\Environments;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Config;
use QIT_CLI\Environment\Docker;
use QIT_CLI\PreCommand\Download\EnvironmentDownloader;
use QIT_CLI\PreCommand\Download\TestPackageDownloader;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\EnvironmentVars;
use QIT_CLI\Environment\CTRFValidator;
use QIT_CLI\SafeRemove;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use QIT_CLI\Zipper;
use function QIT_CLI\is_ci;
use function QIT_CLI\is_windows;
use function QIT_CLI\normalize_path;
use function QIT_CLI\use_tty;

abstract class Environment {
	protected EnvironmentDownloader $environment_downloader;
	protected Cache $cache;
	protected EnvironmentMonitor $environment_monitor;
	protected Filesystem $filesystem;
	protected Docker $docker;
	protected TestPackageDownloader $test_package_downloader;
	protected Zipper $zipper;
	protected EnvironmentVars $environment_vars;
	protected CTRFValidator $ctrf_validator;
	protected TestPackageManifestParser $manifest_parser;
	protected string $cache_dir;
	protected string $source_environment_path;
	protected EnvInfo $env_info;
	protected OutputInterface $output;
	/** @var array<string,array{local: string, in_container: string}> */
	protected array $volumes;
	protected string $type; // "up" or "up_and_test"

	public function __construct(
		EnvironmentDownloader $environment_downloader,
		Cache $cache,
		EnvironmentMonitor $environment_monitor,
		Filesystem $filesystem,
		Docker $docker,
		TestPackageDownloader $test_package_downloader,
		Zipper $zipper,
		EnvironmentVars $environment_vars,
		CTRFValidator $ctrf_validator,
		TestPackageManifestParser $manifest_parser,
		OutputInterface $output
	) {
		$this->environment_downloader  = $environment_downloader;
		$this->cache                   = $cache;
		$this->environment_monitor     = $environment_monitor;
		$this->filesystem              = $filesystem;
		$this->docker                  = $docker;
		$this->test_package_downloader = $test_package_downloader;
		$this->zipper                  = $zipper;
		$this->environment_vars        = $environment_vars;
		$this->ctrf_validator          = $ctrf_validator;
		$this->manifest_parser         = $manifest_parser;
		$this->cache_dir               = normalize_path( Config::get_qit_dir() . 'cache' );
		$this->source_environment_path = normalize_path( Config::get_qit_dir() . 'environments/' . $this->get_name() );
		$this->output                  = $output;
	}

	abstract public function get_name(): string;

	public function init( EnvInfo $env_info ): void {
		$this->env_info = $env_info;

		// Set environment variables for Docker containers
		App::setVar( 'QIT_DOCKER_ENV_VARS', $env_info->envs ?? [] );
	}

	/**
	 * @return array<string,string>
	 */
	abstract protected function get_generate_docker_compose_envs(): array;

	abstract protected function post_generate_docker_compose(): void;

	abstract protected function post_up(): void;

	/**
	 * @param array<string,string> $default_volumes
	 *
	 * @return array<string,string>
	 */
	abstract protected function additional_default_volumes( array $default_volumes ): array;

	abstract protected function additional_output(): void;

	/** @param array<string,array{local: string, in_container: string}> $volumes */
	public function set_volumes( array $volumes ): void {
		$this->volumes = $volumes;
	}

	/**
	 * @param string $type "up" just spin the environment. "up_and_test" also download custom tests.
	 *
	 * @return void
	 */
	public function up( string $type = 'up' ): void {
		if ( ! in_array( $type, [ 'up', 'up_and_test' ], true ) ) {
			throw new \InvalidArgumentException( 'Invalid type: ' . $type );
		}

		try {
			App::make( Docker::class )->find_docker();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( 'QIT needs Docker to be able to process this command.' );
		}

		// Start the benchmark.
		$start = microtime( true );

		$this->environment_downloader->maybe_download( $this->get_name() );
		$this->maybe_create_cache_dir();
		$this->copy_environment();
		$this->environment_monitor->environment_added_or_updated( $this->env_info );

		if ( ! empty( $this->env_info->plugins ) || ! empty( $this->env_info->themes ) ) {
			$message = 'Processing plugins and themes...';
			if ( $this->env_info instanceof \QIT_CLI\Environment\Environments\QITEnvInfo && ! empty( $this->env_info->woocommerce_version ) ) {
				$message .= ' (WooCommerce: ' . $this->env_info->woocommerce_version . ')';
			}
			$this->output->writeln( '<info>' . $message . '</info>' );
		}

		$this->install_extensions();

		// $this->extension_downloader->download( $this->env_info, $this->cache_dir, $this->env_info->plugins, $this->env_info->themes );

		if ( $type === 'up_and_test' && ! empty( $this->env_info->test_packages_metadata ) ) {
			// Download test packages if needed
			$packages_to_download = [];
			foreach ( $this->env_info->test_packages_metadata as $package_id => $metadata ) {
				if ( isset( $metadata['manifest'] ) && $metadata['manifest'] instanceof \QIT_CLI\PreCommand\Objects\TestPackageManifest ) {
					// Package already downloaded and has manifest
					continue;
				}
				// Add package to download list
				$packages_to_download[ $package_id ] = $metadata;
			}

			if ( ! empty( $packages_to_download ) ) {
				// Download all packages at once
				$this->test_package_downloader->download( $packages_to_download, $this->cache_dir );
			}
		}

		$this->output->writeln( '<info>Starting Docker Environment...</info>' );
		$this->generate_docker_compose();
		$this->post_generate_docker_compose();
		$this->up_docker_compose();
		$this->post_up();

		if ( $this->output->isVerbose() ) {
			$this->output->writeln( 'Server started in ' . round( microtime( true ) - $start, 2 ) . ' seconds' );
		}

		$this->additional_output();
	}

	private function install_extensions(): void {
		$extensions = array_merge( $this->env_info->plugins, $this->env_info->themes );

		foreach ( $extensions as $extension ) {
			if ( empty( $extension->downloaded_source ) ) {
				continue;
			}

			$target_dir = "/var/www/html/wp-content/{$extension->type}s/{$extension->slug}";

			if ( is_file( $extension->downloaded_source ) && pathinfo( $extension->downloaded_source, PATHINFO_EXTENSION ) === 'zip' ) {
				// Extract ZIP file
				$extract_to = "{$this->env_info->temporary_env}/html/wp-content/{$extension->type}s";
				if ( ! file_exists( $extract_to ) ) {
					mkdir( $extract_to, 0755, true );
				}

				$this->zipper->extract_zip( $extension->downloaded_source, $extract_to );

				// Verify extraction worked
				$local_path = "{$this->env_info->temporary_env}/html/wp-content/{$extension->type}s/{$extension->slug}";
				if ( ! file_exists( $local_path ) ) {
					throw new \RuntimeException( "Failed to extract {$extension->type} '{$extension->slug}'. The ZIP file might not have the expected directory structure." );
				}

				// Add volume mapping
				$this->env_info->volumes[ $target_dir ] = $local_path;
			} elseif ( is_dir( $extension->downloaded_source ) ) {
				// Local directory - create volume mapping
				$mapping = $target_dir;
				if ( ! getenv( 'QIT_ALLOW_WRITE' ) ) {
					$mapping .= ':ro,cached';
					if ( $this->output->isVerbose() ) {
						$this->output->writeln( "Info: Mapping '{$extension->type}s/{$extension->slug}' as read-only to protect your local copy." );
					}
				}
				$this->env_info->volumes[ $mapping ] = $extension->downloaded_source;
			}
		}
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

		$default_volumes = [
			'/qit/bin'        => "{$this->env_info->temporary_env}/bin",
			'/qit/mu-plugins' => "{$this->env_info->temporary_env}/mu-plugins",
			'/qit/cache'      => $this->cache_dir,
		];

		if ( file_exists( $this->env_info->temporary_env . '/wp-cli.yml' ) ) {
			$default_volumes['/qit/wp-cli.yml'] = "{$this->env_info->temporary_env}/wp-cli.yml";
		}

		$default_volumes = $this->additional_default_volumes( $default_volumes );

		/* Mount test‑packages (global_setup_packages) as read‑only -----------------------*/
		if ( ! empty( $this->env_info->global_setup_packages ) ) {
			foreach ( $this->env_info->global_setup_packages as $pkg_id => $info ) {
				if ( empty( $info['path'] ) || ! is_dir( $info['path'] ) ) {
					continue;
				}
				// Container path MUST be provided - no fallback allowed
				if ( ! isset( $info['container_path'] ) ) {
					throw new \RuntimeException( "Missing container_path for global setup package: {$pkg_id}" );
				}
				$container                                    = $info['container_path'];
				$default_volumes[ $container . ':ro,cached' ] = $info['path'];
			}
		}

		/* Mount test‑packages (test_packages_metadata) as read‑only -------------------*/
		if ( ! empty( $this->env_info->test_packages_metadata ) ) {
			foreach ( $this->env_info->test_packages_metadata as $pkg_id => $info ) {
				if ( empty( $info['path'] ) || ! is_dir( $info['path'] ) ) {
					continue;
				}
				// Container path MUST be provided - no fallback allowed
				if ( ! isset( $info['container_path'] ) ) {
					throw new \RuntimeException( "Missing container_path for test package: {$pkg_id}" );
				}
				$container                                    = $info['container_path'];
				$default_volumes[ $container . ':ro,cached' ] = $info['path'];
			}
		}

		$volumes = array_merge( $default_volumes, $this->env_info->volumes );

		/*
		 * Create directories if needed so that they are mapped to inside
		 * the container with the correct permissions, unless they are a file name,
		 * at which point create the parent dir.
		 */
		foreach ( $volumes as $in_container => &$local ) {
			if ( strpos( $local, 'qit_env_volume' ) !== false ) {
				continue;
			}

			/*
			 * Ensure volume sources are treated as paths, not named volumes.
			 *
			 * In Docker Compose, entries in the "volumes" section can be interpreted as either paths or named volumes.
			 *
			 * Undesired (interpreted as a named volume):
			 *   - "my-extension:/var/www/html/wp-content/plugins/my-extension"
			 *
			 * Desired (interpreted as a path):
			 *   - "/path/to/my-extension:/var/www/html/wp-content/plugins/my-extension"
			 *   - "./my-extension:/var/www/html/wp-content/plugins/my-extension"
			 *
			 * To avoid ambiguity and ensure Docker treats all volume sources as paths (bind mounts), we convert local paths
			 * to absolute or resolved relative paths.
			 */
			if ( file_exists( realpath( $local ) ) ) {
				$local = realpath( $local );
			} else {
				// Check if it can be resolved by relative path to the working directory.
				if ( file_exists( getcwd() . '/' . $local ) ) {
					$local = getcwd() . '/' . $local;
				}
			}

			// If it doesn't contain a "dot", it's a directory.
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

		$this->env_info->volumes = $volumes;

		$process->setEnv( array_merge( $process->getEnv(), [
			'QIT_ENV_ID'         => $this->env_info->env_id,
			'VOLUMES'            => json_encode( $volumes ),
			'NORMALIZED_ENV_DIR' => $this->env_info->temporary_env,
			'QIT_DOCKER_NGINX'   => 'yes', // Default. Might be overridden by the concrete environment.
			'QIT_DOCKER_REDIS'   => 'no', // Default. Might be overridden by the concrete environment.
			'ENV_VARS'           => json_encode( App::getVar( 'QIT_DOCKER_ENV_VARS' ) ),
		], $this->get_generate_docker_compose_envs() ) );

		if ( $this->output->isVeryVerbose() ) {
			$this->output->writeln( $process->getCommandLine() );
		}

		$process->run( function ( $type, $buffer ) {
			if ( $this->output->isVerbose() || $type === Process::ERR ) {
				$this->output->write( $buffer );
			}
		} );

		if ( ! $process->isSuccessful() ) {
			throw new \RuntimeException( "Failed to generate docker-compose.yml. Output:\n" . $process->getOutput() . $process->getErrorOutput() );
		}
	}

	protected function up_docker_compose(): void {
		$this->add_container_names();

		if ( empty( $this->cache->get( 'qit_env_up_first_run' ) ) && ! is_ci() ) {
			$this->cache->set( 'qit_env_up_first_run', '1', - 1 );
			$this->output->writeln( '<info>First-time setup is pulling Docker images and caching downloads. Subsequent runs will be faster.</info>' );
		}

		// Do a docker compose pull first, to make sure images are updated.
		$this->docker->maybe_pull_docker_compose( $this->env_info->temporary_env . '/docker-compose.yml', 'e2e' );

		$args = array_merge( $this->docker->find_docker_compose(), [ '-f', $this->env_info->temporary_env . '/docker-compose.yml', 'up', '-d' ] );

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
		$up_process->setPty( use_tty() );

		$up_process->run( function ( $type, $buffer ) {
			if ( $this->output->isVerbose() ) {
				$this->output->write( $buffer );
			}
		} );

		if ( ! $up_process->isSuccessful() ) {
			$error_output = $up_process->getOutput() . $up_process->getErrorOutput();

			// Detect Docker subnet pool exhaustion and suggest fix.
			if ( strpos( $error_output, 'subnetted' ) !== false ) {
				static::down( $this->env_info );
				throw new \RuntimeException(
					"Docker has run out of network address space.\n\n" .
					"This happens when too many Docker networks accumulate. Fix with:\n\n" .
					"  1. Clean unused networks:  docker network prune\n" .
					"  2. Prevent recurrence — add to /etc/docker/daemon.json:\n\n" .
					"     \"default-address-pools\": [{\"base\": \"172.17.0.0/12\", \"size\": 24}]\n\n" .
					"     Then restart Docker:  sudo systemctl restart docker\n"
				);
			}

			static::down( $this->env_info );
			$compose_file = $this->env_info->temporary_env . '/docker-compose.yml';
			if ( file_exists( $compose_file ) ) {
				$this->output->writeln( file_get_contents( $compose_file ) );
			}
			throw new \RuntimeException( "Failed to start the environment. Output: \n" . $error_output );
		}

		$this->env_info->status = 'started';

		$this->environment_monitor->environment_added_or_updated( $this->env_info );
	}

	public static function down( EnvInfo $env_info, ?OutputInterface $output = null ): void {
		$output              = $output ?? App::make( OutputInterface::class );
		$environment_monitor = App::make( EnvironmentMonitor::class );

		// Execute globalTeardown phase for global setup packages - always executed, even on failure
		if ( ! empty( $env_info->global_setup_packages ) ) {
			// Use DI container with autowiring to create PackagePhaseRunner with all dependencies
			$runner = App::make( \QIT_CLI\Environment\PackagePhaseRunner::class );

			$output->writeln( "\n<comment>🧹  Test‑package globalTeardown phase</comment>" );
			$output->writeln( '<comment>-----------------------------------</comment>' );

			// Create a null orchestrator for teardown phase (non-test packages)
			$ctrf_validator    = \QIT_CLI\App::make( \QIT_CLI\Environment\CTRFValidator::class );
			$null_orchestrator = new \QIT_CLI\Environment\PackageOrchestrator( new \Symfony\Component\Console\Output\NullOutput(), $ctrf_validator );

			foreach ( $env_info->global_setup_packages as $pkg_id => $info ) {
				try {
					$teardown_cmds = $runner->run_phase(
						$env_info,
						'globalTeardown',
						$pkg_id,
						$info['path'],
						null,  // No artifacts_dir for global setup packages
						$null_orchestrator
					);

					if ( $teardown_cmds > 0 ) {
						$output->writeln( "<info>✓ {$pkg_id}: {$teardown_cmds} globalTeardown commands executed</info>" );
					}
				} catch ( \Exception $e ) {
					// Log the error but continue with teardown - globalTeardown failures should not prevent environment cleanup
					$output->writeln( "<error>Failed to execute globalTeardown for {$pkg_id}: " . $e->getMessage() . '</error>' );
				}
			}
		}

		if ( ! file_exists( $env_info->temporary_env ) ) {
			if ( $output->isVerbose() ) {
				$output->writeln( sprintf( 'Tried to stop environment %s, but it does not exist.', $env_info->temporary_env ) );
			}

			$environment_monitor->environment_stopped( $env_info );

			return;
		}

		if ( file_exists( $env_info->temporary_env . '/docker-compose.yml' ) ) {
			$down_process = new Process( array_merge( App::make( Docker::class )->find_docker_compose(), [
				'-f',
				$env_info->temporary_env . '/docker-compose.yml',
				'down',
				'--volumes',
				'--remove-orphans',
			] ) );
			$down_process->setTimeout( 300 );
			$down_process->setIdleTimeout( 300 );
			$down_process->setPty( use_tty() );

			$down_process->run( static function ( $type, $buffer ) use ( $output ) {
				if ( $output->isVeryVerbose() ) {
					$output->write( $buffer );
				}
			} );

			if ( $down_process->isSuccessful() ) {
				if ( $output->isVeryVerbose() ) {
					$output->writeln( 'Removing temporary environment: ' . $env_info->temporary_env );
				}
				SafeRemove::delete_dir( $env_info->temporary_env, static::get_temp_envs_dir() );
			} else {
				$output->writeln( 'Failed to remove temporary environment: ' . $env_info->temporary_env );
			}
		}

		if ( $env_info->tunnel ) {
			TunnelRunner::stop_tunnel( $env_info->env_id );
		}

		$environment_monitor->environment_stopped( $env_info );
	}

	protected function add_container_names(): void {
		$containers     = [];
		$docker_network = null;

		$file = new \SplFileObject( $this->env_info->temporary_env . '/docker-compose.yml' );
		while ( ! $file->eof() ) {
			$line = $file->fgets();
			if ( preg_match( '/^\s+container_name:\s*(\w+)/', $line, $matches ) ) {
				$containers[] = $matches[1];
			}

			/*
			 * Eg:
			 *     networks:
			 *           - qit_network_1234
			 */
			if ( is_null( $docker_network ) && preg_match( '/^\s+networks:\s*$/', $line ) ) {
				// Read the next line.
				$line = $file->fgets();
				if ( preg_match( '/^\s+-\s*(\w+)/', $line, $matches ) ) {
					// eg: "1234_qit_network_1234".
					$docker_network = basename( $this->env_info->temporary_env ) . '_' . $matches[1];
				}
			}
		}
		$containers = array_unique( $containers );

		// Exclude init containers - they are expected to exit after completing.
		$containers = array_filter( $containers, static function ( string $name ): bool {
			return strpos( $name, 'qit_env_init_' ) === false;
		} );

		if ( empty( $containers ) ) {
			throw new \RuntimeException( 'Failed to start the environment. No containers found.' );
		}

		$this->env_info->docker_images  = array_values( $containers );
		$this->env_info->docker_network = $docker_network;
	}

	/**
	 * Returns the port of the Nginx container from the host perspective.
	 *
	 * @return int
	 * @throws \Exception When there's no Nginx container running.
	 * @throws \RuntimeException When the process fails.
	 */
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
		if ( ! is_numeric( $port ) || intval( $port ) != $port ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison,Universal.Operators.StrictComparisons.LooseNotEqual
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
