<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Qit env:reset – reset the database to post-setup state.
 */
class ResetEnvironmentCommand extends QITCommand {
	/** @var EnvironmentMonitor */
	private EnvironmentMonitor $environment_monitor;

	/** @var Docker */
	private Docker $docker;

	protected static $defaultName = 'env:reset'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	public function __construct( EnvironmentMonitor $environment_monitor, Docker $docker ) {
		$this->environment_monitor = $environment_monitor;
		$this->docker              = $docker;
		parent::__construct();
	}

	protected function configure(): void {
		parent::configure(); // Call parent to set up base options
		$this->setDescription( 'Restore the post-setup database snapshot and flush the WordPress object cache' )
			->addArgument( 'env_id', InputArgument::OPTIONAL, 'Environment ID (uses current if not specified)' )
			->setHelp( <<<HELP
The <info>env:reset</info> command restores the database to the state saved after running setup phases.
It can be called repeatedly against the same running environment; each call restores the same
post-setup database snapshot and flushes the WordPress object cache.

This is useful when:
  • You want to run tests with a clean state
  • You've made changes during manual testing and want to start fresh
  • You need to debug a specific test in isolation

Examples:
  <info>qit env:reset</info>
      Resets the current environment's database

  <info>qit env:reset qitenv1234abcd</info>
      Resets a specific environment's database

Note: This only works if the environment was started with setup phases
(i.e., a qit-test.json file was present and --skip-setup was not used).

Scope: env:reset restores database state and flushes the WordPress object cache. It does not restore
uploads, plugin files, other filesystem changes, or external services. Test harnesses must contain
those side effects separately.
HELP
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		// Get environment ID
		$env_id = $input->getArgument( 'env_id' );

		if ( ! $env_id ) {
			// Get list of all environments
			$all_environments = $this->environment_monitor->get();

			// Filter to only running environments
			$environments = [];
			foreach ( $all_environments as $env ) {
				if ( isset( $env->status ) && $env->status === 'started' ) {
					$environments[ $env->env_id ] = $env;
				}
			}

			if ( empty( $environments ) ) {
				$output->writeln( '<error>No running environments found.</error>' );
				return Command::FAILURE;
			}

			if ( count( $environments ) === 1 ) {
				// Only one environment, use it
				$env_info = reset( $environments );
				$env_id   = $env_info->env_id;
			} else {
				// Multiple environments found
				// In non-interactive contexts (like tests), use the most recent environment
				if ( ! $input->isInteractive() ) {
					// Sort by env_id (which contains timestamp) to get most recent
					uksort( $environments, function ( $a, $b ) {
						return strcmp( $b, $a ); // Reverse sort for most recent first
					} );
					$env_info = reset( $environments );
					$env_id   = $env_info->env_id;
					$output->writeln( "<info>Multiple environments found. Using most recent: {$env_id}</info>" );
				} else {
					// Interactive mode - ask user to choose
					$helper  = $this->getHelper( 'question' );
					$choices = [];

					foreach ( $environments as $env ) {
						// Cast to E2EEnvInfo to access php/wp properties
						if ( $env instanceof \QIT_CLI\Environment\Environments\E2E\E2EEnvInfo ) {
							$choices[ $env->env_id ] = sprintf( '%s (PHP %s, WP %s)',
								$env->env_id,
								$env->php_version,
								$env->wordpress_version
							);
						} else {
							$choices[ $env->env_id ] = $env->env_id;
						}
					}

					$question = new ChoiceQuestion(
						'Multiple environments found. Please select one:',
						array_values( $choices ),
						0
					);

					$selected = $helper->ask( $input, $output, $question );
					$env_id   = array_search( $selected, $choices, true );
					$env_info = $environments[ $env_id ];
				}
			}
		} else {
			// Load specified environment
			try {
				$env_info = $this->environment_monitor->get_env_info_by_id( $env_id );
			} catch ( \Exception $e ) {
				$output->writeln( "<error>Environment '{$env_id}' not found.</error>" );
				return Command::FAILURE;
			}
		}

		// Check if backup exists
		$backup_dir  = sys_get_temp_dir() . '/qit-env-backups/' . $env_id;
		$backup_file = $backup_dir . '/setup-complete.sql';

		if ( ! file_exists( $backup_file ) ) {
			$output->writeln( '<error>No database backup found for this environment.</error>' );
			$output->writeln( '<comment>Database backups are created when running "qit env:up" with a qit-test.json file.</comment>' );
			return Command::FAILURE;
		}

		// Load metadata to show info
		$metadata_file = $backup_dir . '/metadata.json';
		if ( file_exists( $metadata_file ) ) {
			$metadata = json_decode( file_get_contents( $metadata_file ), true );
			$created  = isset( $metadata['created'] ) ? gmdate( 'Y-m-d H:i:s', $metadata['created'] ) : 'unknown';
			$output->writeln( "<info>Restoring database backup from: {$created}</info>" );
		}

		// Copy backup to container
		$output->write( 'Restoring database...' );

		try {
			// Copy SQL file to container
			$container_path = '/tmp/restore-' . uniqid() . '.sql';
			$this->docker->copy_into_docker( $env_info, $backup_file, $container_path );

			// Import the database - run in WordPress directory with defaults
			$this->docker->run_inside_docker( $env_info, [ 'sh', '-c', "cd /var/www/html && wp db import {$container_path} --defaults --quiet" ] );

			// Clean up the temp file in container
			$this->docker->run_inside_docker( $env_info, [ 'rm', '-f', $container_path ] );
		} catch ( \Exception $e ) {
			$output->writeln( ' <error>Failed!</error>' );
			$output->writeln( '<error>Database restore failed: ' . $e->getMessage() . '</error>' );
			return Command::FAILURE;
		}

		try {
			// A successful reset requires persistent and in-process object caches to be flushed.
			$this->docker->run_inside_docker( $env_info, [ 'sh', '-c', 'cd /var/www/html && wp cache flush --quiet' ] );
		} catch ( \Exception $e ) {
			$output->writeln( ' <error>Failed!</error>' );
			$output->writeln( '<error>Object-cache flush failed after database restore: ' . $e->getMessage() . '</error>' );
			return Command::FAILURE;
		}

		$output->writeln( ' <info>Done!</info>' );
		$output->writeln( '<info>✓ Database restored to post-setup state.</info>' );
		$output->writeln( '<info>✓ WordPress object cache flushed.</info>' );

		return Command::SUCCESS;
	}
}
