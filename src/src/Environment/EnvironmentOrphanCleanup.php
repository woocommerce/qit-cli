<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\IO\Input;
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
		InputInterface $input,
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

		// List the actions to be taken
		$this->output->writeln( 'The following actions will be taken:' );
		foreach ( $this->directories_to_delete as $directory ) {
			$this->output->writeln( "- Remove directory $directory" );
		}
		foreach ( $this->containers_to_remove as $containerName ) {
			$this->output->writeln( "- Delete docker container $containerName" );
		}

		// Prepare the confirmation question
		$helper   = new QuestionHelper();
		$question = new ConfirmationQuestion(
			'Orphaned environments and containers were found. Do you want to remove them? [y/N]',
			false
		);

		// Ask the user for confirmation
		if ( ! $helper->ask( $this->input, $this->output, $question ) ) {
			$this->output->writeln( 'Action cancelled by user.' );

			return;
		}

		foreach ( $this->directories_to_delete as $directory ) {
			$this->output->writeln( "Removing orphaned environment: {$directory}" );
			SafeRemove::delete_dir( $directory, Environment::get_temp_envs_dir() );
		}

		foreach ( $this->containers_to_remove as $containerName ) {
			$this->output->writeln( "Removing orphaned container: {$containerName}" );
			$stopProcess = new Process( [ 'docker', 'stop', $containerName ] );
			$stopProcess->run();
			$removeProcess = new Process( [ 'docker', 'rm', $containerName ] );
			$removeProcess->run();
		}
	}

	protected function delete_orphaned_docker_containers() {
		$running_environment_docker_images = array_map( function ( EnvInfo $env_info ) {
			return $env_info->docker_images;
		}, $this->environment_monitor->get() );

		// 1. List the running containers
		$listProcess = new Process( [ 'docker', 'container', 'ls', '--format=json' ] );
		$listProcess->run();
		$containersOutput = $listProcess->getOutput();

		$lines = explode( "\n", $containersOutput );

		foreach ( $lines as $line ) {
			$c = json_decode( $line, true );
			if ( $c === null ) {
				continue;
			}
			if ( empty( $c['Names'] ) ) {
				continue;
			}
			$containerName = $c['Names'];

			if ( substr( $containerName, 0, 9 ) === 'qit_env_' ) {
				foreach ( $running_environment_docker_images as $running_environment_docker_image ) {
					if ( in_array( $containerName, $running_environment_docker_image, true ) ) {
						continue 2;
					}
				}
				$this->containers_to_remove[] = $containerName;
			}
		}
	}

	protected function delete_orphaned_environments_from_fileystem() {
		$running_environment_paths = array_map( function ( EnvInfo $env_info ) {
			return $env_info->temporary_env;
		}, $this->environment_monitor->get() );

		/** @var \SplFileInfo $fileInfo */
		foreach ( new \DirectoryIterator( Environment::get_temp_envs_dir() ) as $fileInfo ) {
			if ( $fileInfo->isDot() || $fileInfo->isLink() || ! $fileInfo->isDir() ) {
				continue;
			}

			if ( ! in_array( $fileInfo->getPathname(), $running_environment_paths, true ) ) {
				$this->directories_to_delete[] = $fileInfo->getPathname();
			}
		}
	}
}