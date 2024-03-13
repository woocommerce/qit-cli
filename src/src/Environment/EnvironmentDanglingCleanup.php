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
use function QIT_CLI\is_ci;
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

	public function cleanup_dangling(): void {
		$this->remove_dangling_environments();
		$this->detect_dangling_containers_exited();
		$this->detect_dangling_containers_running();
		$this->detect_dangling_networks();
		$this->detect_dangling_directories();

		// Check if there are actions to perform.
		if ( empty( $this->dangling_directories ) && empty( $this->dangling_containers ) && empty( $this->dangling_networks ) ) {
			return;
		}

		if ( ! $this->header_printed ) {
			$this->output->writeln( '<info>Dangling Temporary Environments found.</info>' );
			$this->header_printed = true;
		}

		foreach ( $this->dangling_containers as $container_name ) {
			if ( substr( $container_name, 0, strlen( 'qit_env_' ) ) !== 'qit_env_' ) {
				$this->output->writeln( "Skipping non-qit container: {$container_name}" );
				continue;
			}
			$this->output->writeln( "Removing dangling Docker containers: {$container_name}" );

			$stop_process = new Process( [ 'docker', 'stop', $container_name ] );
			try {
				$stop_process->mustRun();
			} catch ( \Exception $e ) {
				$this->output->writeln( "Failed to stop container: {$container_name} - " . $stop_process->getOutput() . $stop_process->getErrorOutput() );
			}

			$remove_process = new Process( [ 'docker', 'rm', $container_name ] );
			try {
				$remove_process->mustRun();
			} catch ( \Exception $e ) {
				$this->output->writeln( "Failed to remove container: {$container_name} - " . $remove_process->getOutput() . $remove_process->getErrorOutput() );
			}
		}

		foreach ( $this->dangling_networks as $network_name ) {
			$this->output->writeln( "Removing dangling Docker network: {$network_name}" );

			$remove_process = new Process( [ 'docker', 'network', 'rm', $network_name ] );
			try {
				$remove_process->mustRun();
			} catch ( \Exception $e ) {
				$this->output->writeln( "Failed to remove network: {$network_name} - " . $remove_process->getOutput() . $remove_process->getErrorOutput() );
			}
		}

		if ( empty( $this->dangling_directories ) ) {
			return;
		}

		// Skip asking the user permission to delete in this directory if he answers with an "A".
		$always_delete_from_this_directory = $this->cache->get( 'always_delete_from_this_directory' );

		$parent_dir = $this->get_parent_dir_to_delete();

		if ( $always_delete_from_this_directory !== $parent_dir && ! is_ci() ) {
			// List the actions to be taken.
			$this->output->writeln( '<info>The following actions will be taken:</info>' );

			foreach ( $this->dangling_directories as $directory ) {
				$this->output->writeln( "- Remove directory $directory" );
			}

			$question = new Question(
				"Would you like to perform these actions? [Y/n/A]\nA: Always delete dangling environments in this directory: (Recommended)\n",
				'n' // Default to 'n'.
			);

			switch ( strtolower( ( new QuestionHelper() )->ask( $this->input, $this->output, $question ) ) ) {
				case 'y':
					// no-op. Proceed with the action.
					break;
				case 'a':
					$this->cache->set( 'always_delete_from_this_directory', $parent_dir, YEAR_IN_SECONDS );
					break;
				case 'n':
				default:
					$this->output->writeln( 'Action cancelled by user.' );

					return;
			}
		}

		foreach ( $this->dangling_directories as $directory ) {
			$this->output->writeln( "Removing dangling directory: {$directory}" );
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
			$this->output->writeln( '<error>Directories to delete are not in the same parent directory.</error>' );
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
			$containers_not_running = [];
			foreach ( $env_info->docker_images as $docker_container ) {
				if ( ! in_array( $docker_container, $running_containers, true ) ) {
					$containers_not_running[] = $docker_container;
				}
			}
			if ( ! empty( $containers_not_running ) ) {
				if ( ! $this->header_printed ) {
					$this->output->writeln( '<info>Dangling Temporary Environments found.</info>' );
					$this->header_printed = true;
				}

				$this->output->writeln( "Removing dangling environment: {$env_info->env_id}" );
				if ( $this->output->isVerbose() ) {
					$this->output->writeln( 'Expected containers: ' . implode( ', ', $env_info->docker_images ) );
					$this->output->writeln( 'Missing containers: ' . implode( ', ', $containers_not_running ) );
				}
				Environment::down( $env_info );
			}
		}
	}
}
