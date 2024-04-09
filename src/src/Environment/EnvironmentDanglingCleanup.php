<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Cache;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\SafeRemove;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use function QIT_CLI\normalize_path;

class EnvironmentDanglingCleanup {
	/** @var EnvironmentMonitor */
	protected $environment_monitor;

	/** @var Filesystem */
	protected $filesystem;

	/** @var InputInterface */
	protected $input;

	/** @var OutputInterface */
	protected $output;

	/** @var Cache */
	protected $cache;

	/** @var bool */
	protected $header_printed = false;

	/** @var array<string> */
	protected $dangling_directories = [];

	/** @var array<string> */
	protected $dangling_containers = [];

	/** @var array<string> */
	protected $dangling_networks = [];

	/** @var array<string> */
	protected $dangling_volumes = [];

	public function __construct(
		EnvironmentMonitor $environment_monitor,
		Filesystem $filesystem,
		OutputInterface $output,
		InputInterface $input,
		Cache $cache
	) {
		$this->environment_monitor = $environment_monitor;
		$this->filesystem          = $filesystem;
		$this->output              = $output;
		$this->input               = $input;
		$this->cache               = $cache;
	}

	protected function debug_output( string $message ): void {
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( $message );
		}
	}

	public function cleanup_dangling(): void {
		$this->remove_dangling_environments();
		$this->detect_dangling_containers_exited();
		$this->detect_dangling_containers_running();
		$this->detect_dangling_networks();
		$this->detect_dangling_volumes();
		$this->detect_dangling_directories();

		// Check if there are actions to perform.
		if ( empty( $this->dangling_directories ) && empty( $this->dangling_containers ) && empty( $this->dangling_networks ) ) {
			return;
		}

		if ( ! $this->header_printed ) {
			$this->output->writeln( '<info>Removing dangling test environments...</info>' );
			$this->header_printed = true;
		}

		foreach ( $this->dangling_containers as $container_name ) {
			if ( substr( $container_name, 0, strlen( 'qit_env_' ) ) !== 'qit_env_' ) {
				$this->debug_output( "Skipping non-qit container: {$container_name}" );
				continue;
			}
			$this->debug_output( "Removing dangling Docker containers: {$container_name}" );

			$stop_process = new Process( [ 'docker', 'stop', $container_name ] );
			try {
				$stop_process->mustRun();
			} catch ( \Exception $e ) {
				$this->debug_output( "Failed to stop container: {$container_name} - " . $stop_process->getOutput() . $stop_process->getErrorOutput() );
			}

			$remove_process = new Process( [ 'docker', 'rm', $container_name ] );
			try {
				$remove_process->mustRun();
			} catch ( \Exception $e ) {
				$this->debug_output( "Failed to remove container: {$container_name} - " . $remove_process->getOutput() . $remove_process->getErrorOutput() );
			}
		}

		foreach ( $this->dangling_networks as $network_name ) {
			$this->debug_output( "Removing dangling Docker network: {$network_name}" );

			$remove_process = new Process( [ 'docker', 'network', 'rm', $network_name ] );
			try {
				$remove_process->mustRun();
			} catch ( \Exception $e ) {
				$this->debug_output( "Failed to remove network: {$network_name} - " . $remove_process->getOutput() . $remove_process->getErrorOutput() );
			}
		}

		foreach ( $this->dangling_volumes as $volume_name ) {
			$this->debug_output( "Removing dangling Docker volume: {$volume_name}" );

			$remove_process = new Process( [ 'docker', 'volume', 'rm', $volume_name ] );
			try {
				$remove_process->mustRun();
			} catch ( \Exception $e ) {
				$this->debug_output( "Failed to remove volume: {$volume_name} - " . $remove_process->getOutput() . $remove_process->getErrorOutput() );
			}
		}

		if ( empty( $this->dangling_directories ) ) {
			return;
		}

		/*
		 * The directories that are expected to exist in the root dir of a temporary environment.
		 */
		$expected_directories = [
			'bin',
			'cache',
			'html',
			'docker',
			'mu-plugins',
			'tests',
			'playwright',
		];

		/*
		 * The file extensions that are expected to exist in the root dir of a temporary environment.
		 */
		$allowed_extensions = [
			'php',
			'js',
			'json',
			'yml',
		];

		foreach ( $this->dangling_directories as $directory ) {
			$unexpected_contents = null;

			/*
			 * We are being extra zealous here.
			 * We already have good security boundaries for deleting files, but since
			 * this is a recursive directory deletion, we validate the contents of the directory to be deleted.
			 */
			/** @var \DirectoryIterator $file_info */
			foreach ( new \DirectoryIterator( $directory ) as $file_info ) {
				if ( $file_info->isDot() || $file_info->isLink() ) {
					continue;
				}

				if ( $file_info->isDir() ) {
					if ( ! in_array( $file_info->getFilename(), $expected_directories, true ) ) {
						$this->debug_output( "Found non-expected directory: {$file_info->getPathname()}" );
						$unexpected_contents = $file_info;
						break;
					}
				} elseif ( $file_info->isFile() ) {
					$extension = pathinfo( $file_info->getFilename(), PATHINFO_EXTENSION );
					if ( ! in_array( $extension, $allowed_extensions, true ) ) {
						$this->debug_output( "Found non-expected file: {$file_info->getPathname()}" );
						$unexpected_contents = $file_info;
						break;
					}
				}
			}

			if ( ! is_null( $unexpected_contents ) ) {
				$this->output->writeln( '<comment>Failed to cleanup dangling directory</comment>' );
				$this->output->writeln( sprintf( 'Unexpected %s: %s', $unexpected_contents->isDir() ? 'directory' : 'file', $unexpected_contents->getFilename() ) );

				// Ask the user if we can delete it.
				$question = new Question( sprintf( 'Do you want to delete this directory "%s"? [y/N] ', $directory ), 'n' );
				$answer   = ( new QuestionHelper() )->ask( $this->input, $this->output, $question );
				if ( strtolower( $answer ) !== 'y' ) {
					$this->output->writeln( 'Skipping directory deletion.' );
					continue;
				}
			}

			$this->debug_output( "Removing dangling directory: {$directory}" );

			SafeRemove::delete_dir( $directory, Environment::get_temp_envs_dir() );
		}
	}

	/**
	 * Validates that all directories to delete are in the same parent directory.
	 *
	 * @return string The parent directory of all directories to delete.
	 */
	protected function get_parent_dir_to_delete(): string {
		// Make sure all environments to delete are in the same directory.
		$parent_dirs = array_map( function ( $directory ) {
			return dirname( $directory );
		}, $this->dangling_directories );

		$parent_dirs = array_unique( $parent_dirs );

		if ( count( $parent_dirs ) !== 1 ) {
			$this->output->writeln( '<error>Directories to delete are not in the same parent directory, please delete them manually.</error>' );
			$this->output->writeln( sprintf( 'Parent directories found: %s', implode( ', ', $parent_dirs ) ) );
			// Print the directories to be deleted manually by the user.
			foreach ( $this->dangling_directories as $directory ) {
				$this->output->writeln( "- Remove directory $directory" );
			}
			throw new \RuntimeException( 'Directories to delete are not in the same parent directory.' );
		}

		return array_shift( $parent_dirs );
	}

	/**
	 * - Removes any exited containers.
	 */
	protected function detect_dangling_containers_exited(): void {
		// List the exited containers.
		$list_process = new Process( [ 'docker', 'container', 'ls', '--format=json', '--filter=status=exited', '--filter=name=qit_env_' ] );
		$list_process->run();
		$containers_output = $list_process->getOutput();

		$lines = explode( "\n", $containers_output );

		foreach ( $lines as $line ) {
			$c = json_decode( $line, true );
			if ( $c === null ) {
				continue;
			}
			if ( empty( $c['Names'] ) ) {
				continue;
			}
			$container_name = $c['Names'];

			if ( substr( $container_name, 0, strlen( 'qit_env_' ) ) === 'qit_env_' ) {
				$this->dangling_containers[] = $container_name;
			}
		}
	}

	/**
	 * - Detect running containers that have no environments associated.
	 */
	protected function detect_dangling_containers_running(): void {
		$running_environments = $this->environment_monitor->get();

		// List the running containers.
		$list_process = new Process( [ 'docker', 'container', 'ls', '--format=json', '--filter=status=running', '--filter=name=qit_env_' ] );
		$list_process->run();
		$containers_output = $list_process->getOutput();

		$lines = explode( "\n", $containers_output );

		$running_containers = [];

		foreach ( $lines as $line ) {
			$c = json_decode( $line, true );
			if ( $c === null ) {
				continue;
			}
			if ( empty( $c['Names'] ) ) {
				continue;
			}
			$container_name = $c['Names'];

			if ( substr( $container_name, 0, strlen( 'qit_env_' ) ) === 'qit_env_' ) {
				$running_containers[] = $container_name;
			}
		}

		foreach ( $running_containers as $container_name ) {
			foreach ( $running_environments as $env_info ) {
				if ( strpos( $container_name, $env_info->env_id ) !== false ) {
					continue 2;
				}
			}

			$this->dangling_containers[] = $container_name;
		}
	}

	/**
	 * - Checks that all directories in the temp envs directory are in use by a running environment.
	 * - Mark the directory as orphaned if it's not in use by a running environment.
	 */
	protected function detect_dangling_directories(): void {
		$running_environment_paths = array_map( function ( EnvInfo $env_info ) {
			return normalize_path( $env_info->temporary_env );
		}, $this->environment_monitor->get() );

		/** @var \DirectoryIterator $file_info */
		foreach ( new \DirectoryIterator( Environment::get_temp_envs_dir() ) as $file_info ) {
			if ( $file_info->isDot() || $file_info->isLink() || ! $file_info->isDir() ) {
				continue;
			}

			if ( ! in_array( normalize_path( $file_info->getPathname() ), $running_environment_paths, true ) ) {
				$this->dangling_directories[] = $file_info->getPathname();
			}
		}
	}

	protected function detect_dangling_networks(): void {
		$running_environments = $this->environment_monitor->get();

		// List the networks.
		$list_process = new Process( [ 'docker', 'network', 'ls', '--format=json', '--filter=name=_qit_network_' ] );
		$list_process->run();
		$networks_output = $list_process->getOutput();

		$lines = explode( "\n", $networks_output );

		foreach ( $lines as $line ) {
			$c = json_decode( $line, true );
			if ( $c === null ) {
				continue;
			}
			if ( empty( $c['Name'] ) ) {
				continue;
			}
			$network_name = $c['Name'];

			foreach ( $running_environments as $env_info ) {
				if ( strpos( $network_name, $env_info->env_id ) !== false ) {
					continue 2;
				}
			}

			if ( strpos( $network_name, '_qit_network_' ) !== false ) {
				$this->dangling_networks[] = $network_name;
			}
		}
	}

	protected function detect_dangling_volumes(): void {
		$running_environments = $this->environment_monitor->get();

		// List the networks.
		$list_process = new Process( [ 'docker', 'volume', 'ls', '--format=json', '--filter=name=_qit_env_volume_' ] );
		$list_process->run();
		$volumes_output = $list_process->getOutput();

		$lines = explode( "\n", $volumes_output );

		foreach ( $lines as $line ) {
			$c = json_decode( $line, true );
			if ( $c === null ) {
				continue;
			}
			if ( empty( $c['Name'] ) ) {
				continue;
			}
			$volume_name = $c['Name'];

			foreach ( $running_environments as $env_info ) {
				if ( strpos( $volume_name, $env_info->env_id ) !== false ) {
					continue 2;
				}
			}

			if ( strpos( $volume_name, '_qit_env_volume_' ) !== false ) {
				$this->dangling_volumes[] = $volume_name;
			}
		}
	}

	/**
	 * - Checks that all docker containers in all environments are running.
	 * - Mark the environment as orphaned if any of its containers are not running.
	 */
	protected function remove_dangling_environments(): void {
		$running_environments = $this->environment_monitor->get();

		// List the running containers.
		$list_process = new Process( [ 'docker', 'container', 'ls', '--format=json', '--filter=status=running', '--filter=name=qit_env_' ] );
		$list_process->run();
		$containers_output = $list_process->getOutput();

		$lines = explode( "\n", $containers_output );

		$running_containers = [];

		foreach ( $lines as $line ) {
			$c = json_decode( $line, true );
			if ( $c === null ) {
				continue;
			}
			if ( empty( $c['Names'] ) ) {
				continue;
			}
			$container_name = $c['Names'];

			if ( substr( $container_name, 0, strlen( 'qit_env_' ) ) === 'qit_env_' ) {
				$running_containers[] = $container_name;
			}
		}

		foreach ( $running_environments as $env_info ) {
			if ( empty( $env_info->docker_images ) ) {
				$this->debug_output( "Removing dangling environment (no containers): {$env_info->env_id}" );
				Environment::down( $env_info );
			}
		}

		foreach ( $running_environments as $env_info ) {
			$containers_not_running = [];
			foreach ( $env_info->docker_images as $docker_container ) {
				if ( ! in_array( $docker_container, $running_containers, true ) ) {
					$containers_not_running[] = $docker_container;
				}
			}
			if ( ! empty( $containers_not_running ) ) {
				if ( ! $this->header_printed ) {
					$this->output->writeln( '<info>Cleaning up dangling temporary environments...</info>' );
					$this->header_printed = true;
				}

				$this->debug_output( "Removing dangling environment: {$env_info->env_id}" );
				if ( $this->output->isVeryVerbose() ) {
					$this->output->writeln( 'Expected containers: ' . implode( ', ', $env_info->docker_images ) );
					$this->output->writeln( 'Missing containers: ' . implode( ', ', $containers_not_running ) );
				}
				Environment::down( $env_info );
			}
		}
	}
}
