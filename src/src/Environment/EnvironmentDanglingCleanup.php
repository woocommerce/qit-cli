<?php

namespace QIT_CLI\Environment;

use QIT_CLI\App;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\Environment\Environments\Environment;
use QIT_CLI\IO\Output;
use QIT_CLI\SafeRemove;
use QIT_CLI\Tunnel\TunnelRunner;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;
use function QIT_CLI\normalize_path;
use function QIT_CLI\use_tty;

class EnvironmentDanglingCleanup {
	/** @var EnvironmentMonitor */
	protected $environment_monitor;

	/** @var InputInterface */
	protected $input;

	/** @var OutputInterface */
	protected $output;

	/** @var array<string> */
	protected $dangling_directories = [];

	public function __construct(
		EnvironmentMonitor $environment_monitor,
		OutputInterface $output,
		InputInterface $input
	) {
		$this->environment_monitor = $environment_monitor;
		$this->output              = $output;
		$this->input               = $input;
	}

	protected function debug_output( string $message ): void {
		if ( $this->output->isVerbose() ) {
			$this->output->writeln( $message );
		}
	}

	public function cleanup_dangling(): void {
		try {
			App::make( Docker::class )->find_docker();
		} catch ( \Exception $e ) {
			if ( $this->output->isVerbose() ) {
				$this->output->writeln( '<error>Docker not found, skipping cleanup.</error>' );
			}

			return;
		}

		if ( getenv( 'QIT_DISABLE_CLEANUP' ) === '1' ) {
			return;
		}

		$this->detect_dangling_directories();
		$this->stop_dangling_local_tunnels();

		if ( empty( $this->dangling_directories ) ) {
			return;
		}

		$this->output->writeln( '<info>Removing dangling test environments...</info>' );

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
			'k6',
			'php',
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

			if ( file_exists( $directory . '/docker-compose.yml' ) ) {
				$down_process = new Process( array_merge( App::make( Docker::class )->find_docker_compose(), [ '-f', $directory . '/docker-compose.yml', 'down', '--volumes', '--remove-orphans' ] ) );
				$down_process->setTimeout( 300 );
				$down_process->setIdleTimeout( 300 );
				$down_process->setPty( use_tty() );

				$output = App::make( Output::class );

				$down_process->run( static function ( $type, $buffer ) use ( $output ) {
					$output->write( $buffer );
				} );
			}

			SafeRemove::delete_dir( $directory, Environment::get_temp_envs_dir() );
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

	protected function stop_dangling_local_tunnels(): void {
		$running_environments = $this->environment_monitor->get();

		$running_env_ids = array_map( function ( EnvInfo $env_info ) {
			return $env_info->env_id;
		}, $running_environments );

		$pid_files = glob( sys_get_temp_dir() . '/qit_env_tunnel_*.pid' );

		foreach ( $pid_files as $pid_file ) {
			$env_id = preg_replace( '#^.*qit_env_tunnel_(.*)\.pid$#', '$1', $pid_file );

			if ( ! in_array( $env_id, $running_env_ids, true ) ) {
				$this->debug_output( "Removing dangling local tunnel: {$pid_file}" );
				try {
					TunnelRunner::stop_tunnel( $env_id );
				} catch ( \Exception $e ) {
					// Just a warning.
					$this->output->writeln( $e->getMessage() );
				}
			}
		}
	}
}
