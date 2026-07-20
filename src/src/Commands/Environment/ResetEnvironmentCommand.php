<?php
declare( strict_types=1 );

namespace QIT_CLI\Commands\Environment;

use QIT_CLI\Commands\QITCommand;
use QIT_CLI\Environment\Docker;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\QITInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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
			->addOption( 'json', 'j', InputOption::VALUE_NONE, 'Machine-readable reset result and phase timings' )
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
		$started  = microtime( true );
		$json     = (bool) $input->getOption( 'json' );
		$phases   = $this->initial_phases();
		$strategy = 'unknown';

		// Get environment ID
		$env_id        = $input->getArgument( 'env_id' );
		$phase_started = microtime( true );

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
				$phases['environment_lookup'] = $this->phase( 'failed', $phase_started );
				return $this->failure( $output, $json, $env_id, $strategy, 'environment_lookup', 'No running environments found.', $started, $phases );
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
				$phases['environment_lookup'] = $this->phase( 'failed', $phase_started );
				return $this->failure( $output, $json, $env_id, $strategy, 'environment_lookup', "Environment '{$env_id}' not found.", $started, $phases );
			}
		}
		$phases['environment_lookup'] = $this->phase( 'completed', $phase_started );

		// Check if backup exists
		$backup_dir  = sys_get_temp_dir() . '/qit-env-backups/' . $env_id;
		$backup_file = $backup_dir . '/setup-complete.sql';

		if ( ! file_exists( $backup_file ) ) {
			$phases['snapshot_copy'] = [
				'status'  => 'failed',
				'seconds' => 0.0,
			];
			return $this->failure( $output, $json, $env_id, $strategy, 'snapshot_copy', 'No database backup found for this environment. Database backups are created when running "qit env:up" with a qit-test.json file.', $started, $phases );
		}

		// Load metadata to show info
		$metadata_file = $backup_dir . '/metadata.json';
		$metadata      = [];
		if ( file_exists( $metadata_file ) ) {
			$decoded  = json_decode( file_get_contents( $metadata_file ), true );
			$metadata = is_array( $decoded ) ? $decoded : [];
			$created  = isset( $metadata['created'] ) ? gmdate( 'Y-m-d H:i:s', $metadata['created'] ) : 'unknown';
			if ( ! $json ) {
				$output->writeln( "<info>Restoring database backup from: {$created}</info>" );
			}
		}

		if ( ! $json ) {
			$output->write( 'Restoring database...' );
		}

		if ( isset( $metadata['snapshot_strategy'] ) && $metadata['snapshot_strategy'] === 'container_staged' ) {
			$strategy = 'container_staged';
			$status   = $this->restore_staged( $env_info, $metadata, $phases );
		} else {
			$strategy = 'copy_per_reset';
			$status   = $this->restore_legacy( $env_info, $backup_file, $phases );
		}

		if ( $status['failed_phase'] !== null ) {
			return $this->failure( $output, $json, $env_id, $strategy, $status['failed_phase'], $status['message'], $started, $phases );
		}

		if ( $json ) {
			$this->write_json( $output, 'success', $env_id, $strategy, null, '', $started, $phases );
		} else {
			$output->writeln( ' <info>Done!</info>' );
			$output->writeln( '<info>✓ Database restored to post-setup state.</info>' );
			$output->writeln( '<info>✓ WordPress object cache flushed.</info>' );
		}

		return Command::SUCCESS;
	}

	/**
	 * @return array<string,array{status:string,seconds:float}>
	 */
	private function initial_phases(): array {
		return array_fill_keys(
			[ 'environment_lookup', 'snapshot_copy', 'database_import', 'temporary_file_cleanup', 'object_cache_flush' ],
			[
				'status'  => 'not_started',
				'seconds' => 0.0,
			]
		);
	}

	/**
	 * @return array{status:string,seconds:float}
	 */
	private function phase( string $status, float $started ): array {
		return [
			'status'  => $status,
			'seconds' => round( microtime( true ) - $started, 6 ),
		];
	}

	/**
	 * @param EnvInfo                                          $env_info Environment to reset.
	 * @param array<string,mixed>                              $metadata Snapshot metadata.
	 * @param array<string,array{status:string,seconds:float}> $phases   Timed reset phases.
	 * @return array{failed_phase:?string,message:string}
	 */
	private function restore_staged( EnvInfo $env_info, array $metadata, array &$phases ): array {
		$phases['snapshot_copy']          = [
			'status'  => 'skipped',
			'seconds' => 0.0,
		];
		$phases['temporary_file_cleanup'] = [
			'status'  => 'skipped',
			'seconds' => 0.0,
		];
		$snapshot                         = $metadata['container_snapshot'] ?? '';
		$checksum                         = $metadata['snapshot_sha256'] ?? '';
		$helper                           = $metadata['reset_helper'] ?? '';
		if ( ! is_string( $snapshot ) || ! is_string( $checksum ) || ! is_string( $helper ) || $snapshot === '' || $checksum === '' || $helper === '' ) {
			$phases['database_import'] = [
				'status'  => 'failed',
				'seconds' => 0.0,
			];
			return [
				'failed_phase' => 'database_import',
				'message'      => 'Staged reset metadata is incomplete.',
			];
		}

		try {
			$helper_output = $this->docker->run_inside_docker( $env_info, [ 'php', $helper, $snapshot, $checksum ] );
		} catch ( \Exception $e ) {
			$phases['database_import'] = [
				'status'  => 'failed',
				'seconds' => 0.0,
			];
			return [
				'failed_phase' => 'database_import',
				'message'      => 'Database restore failed: ' . $e->getMessage(),
			];
		}

		$result = json_decode( trim( $helper_output ), true );
		if ( ! is_array( $result ) || ! isset( $result['status'], $result['phases'] ) || ! is_array( $result['phases'] ) ) {
			$phases['database_import'] = [
				'status'  => 'failed',
				'seconds' => 0.0,
			];
			return [
				'failed_phase' => 'database_import',
				'message'      => 'The staged reset helper returned an invalid result.',
			];
		}

		foreach ( [ 'database_import', 'object_cache_flush' ] as $phase_name ) {
			if ( isset( $result['phases'][ $phase_name ] ) && is_array( $result['phases'][ $phase_name ] ) ) {
				$phases[ $phase_name ] = [
					'status'  => (string) ( $result['phases'][ $phase_name ]['status'] ?? 'failed' ),
					'seconds' => round( (float) ( $result['phases'][ $phase_name ]['seconds'] ?? 0.0 ), 6 ),
				];
			}
		}

		if ( $result['status'] !== 'success' ) {
			$failed_phase = (string) ( $result['failed_phase'] ?? 'database_import' );
			if ( ! isset( $phases[ $failed_phase ] ) ) {
				$failed_phase = 'database_import';
			}
			$message = trim( (string) ( $result['message'] ?? '' ) );
			return [
				'failed_phase' => $failed_phase,
				'message'      => $message !== '' ? $message : 'The staged environment reset failed.',
			];
		}

		return [
			'failed_phase' => null,
			'message'      => '',
		];
	}

	/**
	 * @param EnvInfo                                          $env_info   Environment to reset.
	 * @param string                                           $backup_file Host snapshot path.
	 * @param array<string,array{status:string,seconds:float}> $phases     Timed reset phases.
	 * @return array{failed_phase:?string,message:string}
	 */
	private function restore_legacy( EnvInfo $env_info, string $backup_file, array &$phases ): array {
		$container_path = '/tmp/restore-' . uniqid() . '.sql';
		$phase_started  = microtime( true );
		try {
			$this->docker->copy_into_docker( $env_info, $backup_file, $container_path );
			$phases['snapshot_copy'] = $this->phase( 'completed', $phase_started );
		} catch ( \Exception $e ) {
			$phases['snapshot_copy'] = $this->phase( 'failed', $phase_started );
			return [
				'failed_phase' => 'snapshot_copy',
				'message'      => 'Database snapshot copy failed: ' . $e->getMessage(),
			];
		}

		$phase_started = microtime( true );
		try {
			$this->docker->run_inside_docker( $env_info, [ 'sh', '-c', "cd /var/www/html && wp db import {$container_path} --defaults --quiet" ] );
			$phases['database_import'] = $this->phase( 'completed', $phase_started );
		} catch ( \Exception $e ) {
			$phases['database_import'] = $this->phase( 'failed', $phase_started );
			$this->cleanup_legacy_snapshot( $env_info, $container_path, $phases );
			return [
				'failed_phase' => 'database_import',
				'message'      => 'Database restore failed: ' . $e->getMessage(),
			];
		}

		$cleanup_error = $this->cleanup_legacy_snapshot( $env_info, $container_path, $phases );
		if ( $cleanup_error !== null ) {
			return [
				'failed_phase' => 'temporary_file_cleanup',
				'message'      => $cleanup_error,
			];
		}

		$phase_started = microtime( true );
		try {
			$this->docker->run_inside_docker( $env_info, [ 'sh', '-c', 'cd /var/www/html && wp cache flush --quiet' ] );
			$phases['object_cache_flush'] = $this->phase( 'completed', $phase_started );
		} catch ( \Exception $e ) {
			$phases['object_cache_flush'] = $this->phase( 'failed', $phase_started );
			return [
				'failed_phase' => 'object_cache_flush',
				'message'      => 'Object-cache flush failed after database restore: ' . $e->getMessage(),
			];
		}

		return [
			'failed_phase' => null,
			'message'      => '',
		];
	}

	/**
	 * @param EnvInfo                                          $env_info      Environment to reset.
	 * @param string                                           $container_path Temporary snapshot path.
	 * @param array<string,array{status:string,seconds:float}> $phases        Timed reset phases.
	 */
	private function cleanup_legacy_snapshot( EnvInfo $env_info, string $container_path, array &$phases ): ?string {
		$phase_started = microtime( true );
		try {
			$this->docker->run_inside_docker( $env_info, [ 'rm', '-f', $container_path ] );
			$phases['temporary_file_cleanup'] = $this->phase( 'completed', $phase_started );
			return null;
		} catch ( \Exception $e ) {
			$phases['temporary_file_cleanup'] = $this->phase( 'failed', $phase_started );
			return 'Temporary snapshot cleanup failed: ' . $e->getMessage();
		}
	}

	/**
	 * @param OutputInterface                                  $output       Console output.
	 * @param bool                                             $json         Whether to emit JSON.
	 * @param string|null                                      $env_id       Environment ID.
	 * @param string                                           $strategy     Reset strategy.
	 * @param string                                           $failed_phase Failed phase name.
	 * @param string                                           $message      Failure detail.
	 * @param float                                            $started      Reset start timestamp.
	 * @param array<string,array{status:string,seconds:float}> $phases       Timed reset phases.
	 */
	private function failure( OutputInterface $output, bool $json, ?string $env_id, string $strategy, string $failed_phase, string $message, float $started, array $phases ): int {
		if ( $json ) {
			$this->write_json( $output, 'failed', $env_id, $strategy, $failed_phase, $message, $started, $phases );
		} else {
			$output->writeln( ' <error>Failed!</error>' );
			$output->writeln( '<error>' . $message . '</error>' );
		}
		return Command::FAILURE;
	}

	/**
	 * @param OutputInterface                                  $output       Console output.
	 * @param string                                           $status       Overall reset status.
	 * @param string|null                                      $env_id       Environment ID.
	 * @param string                                           $strategy     Reset strategy.
	 * @param string|null                                      $failed_phase Failed phase name.
	 * @param string                                           $message      Failure detail.
	 * @param float                                            $started      Reset start timestamp.
	 * @param array<string,array{status:string,seconds:float}> $phases       Timed reset phases.
	 */
	private function write_json( OutputInterface $output, string $status, ?string $env_id, string $strategy, ?string $failed_phase, string $message, float $started, array $phases ): void {
		$output->writeln( json_encode( [
			'status'        => $status,
			'env_id'        => $env_id,
			'strategy'      => $strategy,
			'total_seconds' => round( microtime( true ) - $started, 6 ),
			'failed_phase'  => $failed_phase,
			'message'       => $message,
			'phases'        => $phases,
		], JSON_UNESCAPED_SLASHES ) );
	}
}
