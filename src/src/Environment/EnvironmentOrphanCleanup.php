<?php

namespace QIT_CLI\Environment;

use QIT_CLI\SafeRemove;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class EnvironmentOrphanCleanup {
	/** @var array|mixed */
	protected $environment_monitor;

	/** @var Filesystem */
	protected $filesystem;

	/** @var InputInterface */
	protected $input;

	/** @var OutputInterface */
	protected $output;

	/** @var array<string> */
	protected $directories_to_delete = [];

	/** @var array<string> */
	protected $containers_to_remove = [];

	public function __construct(
		EnvironmentMonitor $environment_monitor,
		Filesystem $filesystem,
		OutputInterface $output,
		InputInterface $input
	) {
		$this->environment_monitor = $environment_monitor;
		$this->filesystem          = $filesystem;
		$this->output              = $output;
		$this->input               = $input;
	}

	public function cleanup_orphans(): void {
		$this->delete_orphaned_environments_from_fileystem();
		$this->delete_orphaned_docker_containers();

		// Check if there are actions to perform.
		if ( empty( $this->directories_to_delete ) && empty( $this->containers_to_remove ) ) {
			return;
		}

		// List the actions to be taken.
		$this->output->writeln( 'The following actions will be taken:' );
		foreach ( $this->directories_to_delete as $directory ) {
			$this->output->writeln( "- Remove directory $directory" );
		}
		foreach ( $this->containers_to_remove as $container_name ) {
			$this->output->writeln( "- Delete docker container $container_name" );
		}

		// Prepare the confirmation question.
		$helper   = new QuestionHelper();
		$question = new ConfirmationQuestion(
			'Orphaned environments and containers were found. Do you want to remove them? [y/N]',
			false
		);

		// Ask the user for confirmation.
		if ( ! $helper->ask( $this->input, $this->output, $question ) ) {
			$this->output->writeln( 'Action cancelled by user.' );

			return;
		}

		foreach ( $this->directories_to_delete as $directory ) {
			$this->output->writeln( "Removing orphaned environment: {$directory}" );
			SafeRemove::delete_dir( $directory, Environment::get_temp_envs_dir() );
		}

		foreach ( $this->containers_to_remove as $container_name ) {
			$this->output->writeln( "Removing orphaned container: {$container_name}" );
			$stop_process = new Process( [ 'docker', 'stop', $container_name ] );
			$stop_process->run();
			$remove_process = new Process( [ 'docker', 'rm', $container_name ] );
			$remove_process->run();
		}
	}

	protected function delete_orphaned_docker_containers(): void {
		$running_environment_docker_images = array_map( function ( EnvInfo $env_info ) {
			return $env_info->docker_images;
		}, $this->environment_monitor->get() );

		// 1. List the running containers.
		$list_process = new Process( [ 'docker', 'container', 'ls', '--format=json' ] );
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

			if ( substr( $container_name, 0, 9 ) === 'qit_env_' ) {
				foreach ( $running_environment_docker_images as $running_environment_docker_image ) {
					if ( in_array( $container_name, $running_environment_docker_image, true ) ) {
						continue 2;
					}
				}
				$this->containers_to_remove[] = $container_name;
			}
		}
	}

	protected function delete_orphaned_environments_from_fileystem(): void {
		$running_environment_paths = array_map( function ( EnvInfo $env_info ) {
			return $env_info->temporary_env;
		}, $this->environment_monitor->get() );

		/** @var \SplFileInfo $file_info */
		foreach ( new \DirectoryIterator( Environment::get_temp_envs_dir() ) as $file_info ) {
			$is_dot = in_array( $file_info->getFilename(), [ '.', '..' ], true );
			if ( $is_dot || $file_info->isLink() || ! $file_info->isDir() ) {
				continue;
			}

			if ( ! in_array( $file_info->getPathname(), $running_environment_paths, true ) ) {
				$this->directories_to_delete[] = $file_info->getPathname();
			}
		}
	}
}
