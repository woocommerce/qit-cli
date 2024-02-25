<?php

namespace QIT_CLI\Environment;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class EnvironmentOrphanCleanup {
	/** @var array|mixed */
	protected $environment_monitor;

	/** @var Filesystem */
	protected $filesystem;

	/** @var OutputInterface */
	protected $output;

	public function __construct(
		EnvironmentMonitor $environment_monitor,
		Filesystem $filesystem,
		OutputInterface $output
	) {
		$this->environment_monitor = $environment_monitor;
		$this->filesystem          = $filesystem;
		$this->output              = $output;
	}

	public function cleanup_orphans(): void {
		$running_environment_paths = array_map( function ( EnvInfo $env_info ) {
			return $env_info->temporary_env;
		}, $this->environment_monitor->get() );

		/** @var \SplFileInfo $fileInfo */
		foreach ( new \DirectoryIterator( Environment::get_temp_envs_dir() ) as $fileInfo ) {
			if ( $fileInfo->isDot() || $fileInfo->isLink() || ! $fileInfo->isDir() ) {
				continue;
			}

			if ( ! in_array( $fileInfo->getPathname(), $running_environment_paths, true ) ) {
				$this->output->writeln( "Removing orphaned environment: {$fileInfo->getPathname()}" );
				$this->filesystem->remove( $fileInfo->getPathname() );
			}
		}
	}
}