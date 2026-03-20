<?php

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use Dotenv\Dotenv;

class QITTestExtension implements Extension {
	public function bootstrap( Configuration $configuration, Facade $facade, ParameterCollection $parameters ): void {
		$facade->registerSubscriber( new \QITTestStart() );
		$facade->registerSubscriber( new \QITTestFinish() );
	}
}

class QITTestStart implements ExecutionStartedSubscriber {

	/**
	 * Stable path for the initialized QIT_HOME source.
	 * This survives across runs so the hourly cache always points to a valid directory.
	 * Workers mirror FROM here into their own ephemeral qit-test-* directories.
	 */
	const INIT_SOURCE_DIR = '/tmp/qit-init-source';

	public function notify( ExecutionStarted $event ): void {
		if ( file_exists( __DIR__ . '/qit-env.json' ) ) {
			unlink( __DIR__ . '/qit-env.json' );
			echo 'Deleted qit-env.json';
		}

		$dotenv = Dotenv::createImmutable( __DIR__ );
		$dotenv->load();
		$dotenv->required( [
			'QIT_CUSTOM_TESTS_USER',
			'QIT_CUSTOM_TESTS_USER_QIT_TOKEN',
			'QIT_CUSTOM_TESTS_SECRET',
			'QIT_CUSTOM_TESTS_URL',
			'QIT_CUSTOM_TESTS_ENV',
		] );

		if ( ! file_exists( __DIR__ . '/../../../qit' ) ) {
			throw new \RuntimeException( sprintf( 'The qit binary was not found at %s.', realpath( __DIR__ . '/../../../qit' ) ) );
		}

		$GLOBALS['qit-php'] = __DIR__ . '/../../../src/qit-cli.php';

		if ( ! file_exists( $GLOBALS['qit-php'] ) ) {
			throw new \RuntimeException( sprintf( 'The qit-php file was not found at %s (realpath: %s).', $GLOBALS['qit-php'], realpath( $GLOBALS['qit-php'] ) ) );
		}

		// Generate an ID for this run.
		$run_id             = uniqid( 'qit_custom_tests_' );
		$GLOBALS['RUN_ID'] = $run_id;

		// QIT_HOME is already set by bootstrap.php (ephemeral, per-worker).
		if ( empty( $GLOBALS['QIT_HOME'] ) ) {
			throw new \RuntimeException( 'QIT_HOME must be set by bootstrap.php before QITTestExtension runs' );
		}

		$fs = new Filesystem();

		// Check for cached initialization data with hourly timestamp.
		$cache_timestamp = date( 'Ymd-H' ); // e.g., 20250522-20
		$cache_file      = sys_get_temp_dir() . "/qit-init-cache-$cache_timestamp.json";
		$cache_valid     = file_exists( $cache_file );

		// Detect if running in Paratest or single-process PHPUnit.
		$is_paratest = ! empty( getenv( 'TEST_TOKEN' ) ) && strpos( getenv( 'TEST_TOKEN' ), 'serial-' ) !== 0;

		if ( ! getenv( 'QIT_FORCE_FRESH_SYNC' ) && $cache_valid && is_dir( self::INIT_SOURCE_DIR ) ) {
			// Fast path: stable source directory exists and cache is fresh.
			echo "Using cached initialization from " . self::INIT_SOURCE_DIR . "\n";
			$fs->mkdir( $GLOBALS['QIT_HOME'] );
			self::mirror_config_only( self::INIT_SOURCE_DIR, $GLOBALS['QIT_HOME'] );
			$GLOBALS['IS_SOURCE'] = false;
		} else {
			// Slow path: need to initialize. Use filesystem lock to coordinate parallel workers.
			if ( ! touch( sys_get_temp_dir() . '/test-initialization-lock-file' ) ) {
				throw new \RuntimeException( 'Failed to create lock file at ' . sys_get_temp_dir() . '/test-initialization-lock-file' );
			}

			$lock_file = fopen( sys_get_temp_dir() . '/test-initialization-lock-file', 'w+' );

			if ( ! $lock_file ) {
				throw new \RuntimeException( 'Failed to open lock file at ' . sys_get_temp_dir() . '/test-initialization-lock-file' );
			}

			// Attempt to get an exclusive lock — first process wins.
			if ( flock( $lock_file, LOCK_EX | LOCK_NB ) ) {
				echo sprintf( "Process %s has exclusive lock\n", getenv( 'TEST_TOKEN' ) ?: 'single' );

				// Clean up stale tracking files from previous runs.
				$files_to_delete = array_merge(
					glob( sys_get_temp_dir() . '/qit-running-*' ) ?: [],
					glob( sys_get_temp_dir() . '/qit-test-tag-lock-*' ) ?: []
				);
				foreach ( $files_to_delete as $file ) {
					@unlink( $file );
				}

				// Clean up stale per-worker directories from PREVIOUS runs (older than 60s).
				// Triple-guarded: age + realpath prefix + basename regex.
				$cleanup_cutoff = time() - 600;
				$cleanup_tmp    = realpath( sys_get_temp_dir() );
				foreach ( glob( sys_get_temp_dir() . '/qit-test-*' ) as $cleanup_dir ) {
					if ( is_dir( $cleanup_dir )
						&& filemtime( $cleanup_dir ) < $cleanup_cutoff
						&& strpos( realpath( $cleanup_dir ), $cleanup_tmp ) === 0
						&& preg_match( '/^qit-test-[a-f0-9]+$/', basename( $cleanup_dir ) )
					) {
						exec( 'rm -rf ' . escapeshellarg( $cleanup_dir ) );
					}
				}

				// Initialize into the STABLE source directory (not the ephemeral worker dir).
				// This path is what the cache points to — it must survive across runs.
				if ( is_dir( self::INIT_SOURCE_DIR ) ) {
					$fs->remove( self::INIT_SOURCE_DIR );
				}
				$fs->mkdir( self::INIT_SOURCE_DIR );

				// Enable dev mode.
				try {
					$dev = new Process( [ 'php', $GLOBALS['qit-php'], 'dev' ] );
					$dev->setEnv( [ 'QIT_HOME' => self::INIT_SOURCE_DIR, 'MANAGER_URL' => $_ENV['QIT_CUSTOM_TESTS_URL'] ] );
					$dev->setTimeout( 30 );
					$dev->setIdleTimeout( 30 );
					$dev->mustRun( function ( $type, $buffer ) {
						echo $buffer;
					} );
				} catch ( \Exception $e ) {
					throw new \RuntimeException( "Failed to run qit dev: {$e->getMessage()}" );
				}

				// Add the environment.
				try {
					$add_environment = new Process( [
						'php',
						$GLOBALS['qit-php'],
						'backend:add',
						'--manager_url',
						$_ENV['QIT_CUSTOM_TESTS_URL'],
						'--qit_secret',
						$_ENV['QIT_CUSTOM_TESTS_SECRET'],
						'--environment',
						$_ENV['QIT_CUSTOM_TESTS_ENV'],
					] );
					$add_environment->setEnv( [ 'QIT_HOME' => self::INIT_SOURCE_DIR, 'MANAGER_URL' => $_ENV['QIT_CUSTOM_TESTS_URL'] ] );
					$add_environment->mustRun( function ( $type, $buffer ) {
						echo $buffer;
					} );
				} catch ( \Exception $e ) {
					throw new \RuntimeException( "Failed to run backend:add: {$e->getMessage()}" );
				}

				if ( $_ENV['QIT_CUSTOM_TESTS_ENV'] !== 'staging' ) {
					// Add the partner account that will be used.
					try {
						$add_partner = new Process( [
							'php',
							$GLOBALS['qit-php'],
							'partner:add',
							'--user',
							$_ENV['QIT_CUSTOM_TESTS_USER'],
							'--qit_token',
							$_ENV['QIT_CUSTOM_TESTS_USER_QIT_TOKEN'],
						] );
						$add_partner->setEnv( [ 'QIT_HOME' => self::INIT_SOURCE_DIR, 'MANAGER_URL' => $_ENV['QIT_CUSTOM_TESTS_URL'] ] );
						$add_partner->mustRun( function ( $type, $buffer ) {
							echo $buffer;
						} );
					} catch ( \Exception $e ) {
						throw new \RuntimeException( "Failed to run partner:add: {$e->getMessage()}" );
					}
				} else {
					echo "Skipping partner add for staging environment\n";
				}

				// Validate connection.
				$max_attempts = 2;
				$sync_data    = null;
				for ( $attempt = 1; $attempt <= $max_attempts; $attempt++ ) {
					try {
						$extensions = new Process( [ 'php', $GLOBALS['qit-php'], 'extensions' ] );
						$extensions->setEnv( [ 'QIT_HOME' => self::INIT_SOURCE_DIR, 'MANAGER_URL' => $_ENV['QIT_CUSTOM_TESTS_URL'] ] );
						$extensions->mustRun();
						$sync_data = $extensions->getOutput();
						break;
					} catch ( \Exception $e ) {
						if ( $attempt === $max_attempts ) {
							throw new \RuntimeException( "Failed to run qit extensions after $max_attempts attempts: {$e->getMessage()}" );
						}
						sleep( 5 );
					}
				}

				$woocommerce_ids_per_environment = [
					'staging'    => 18734003134382,
					'local'      => 18734001206047,
					'production' => 18734002449992,
				];

				$woocommerce_id = $woocommerce_ids_per_environment[ $_ENV['QIT_CUSTOM_TESTS_ENV'] ];

				if ( strpos( $sync_data, (string) $woocommerce_id ) === false ) {
					if ( ! fwrite( $lock_file, 'failed' ) ) {
						throw new \RuntimeException( 'Failed to write to lock file.' );
					}
					throw new \RuntimeException( "Sync validation failed. Expected WooCommerce ID $woocommerce_id not found in sync data. Sync data: $sync_data\n" );
				}

				// Write the stable source path to the lock file so other workers can find it.
				if ( ! fwrite( $lock_file, self::INIT_SOURCE_DIR ) ) {
					throw new \RuntimeException( 'Failed to write to lock file.' );
				}

				// Copy test cache into the initialized source.
				if ( ! file_exists( __DIR__ . '/cache' ) ) {
					mkdir( __DIR__ . '/cache' );
				}
				if ( ! file_exists( __DIR__ . '/tmp' ) ) {
					mkdir( __DIR__ . '/tmp' );
				}
				$fs->mirror( __DIR__ . '/cache', self::INIT_SOURCE_DIR . '/cache' );

				// Write hourly cache pointing to the stable source directory.
				$cache_data  = [ 'source_qit_home' => self::INIT_SOURCE_DIR ];
				$json_output = json_encode( $cache_data, JSON_THROW_ON_ERROR );
				if ( file_put_contents( $cache_file, $json_output ) === false ) {
					throw new \RuntimeException( "Failed to write cache file $cache_file" );
				}

				// Now mirror from stable source to this worker's ephemeral QIT_HOME.
				$fs->mkdir( $GLOBALS['QIT_HOME'] );
				self::mirror_config_only( self::INIT_SOURCE_DIR, $GLOBALS['QIT_HOME'] );

				$GLOBALS['IS_SOURCE'] = true;

				echo "Main process has authenticated and is releasing the lock...\n";

				flock( $lock_file, LOCK_UN );
				fclose( $lock_file );

				// Brief pause so other workers can acquire the shared lock and read the path.
				if ( $is_paratest ) {
					sleep( 2 );
				}
			} else {
				// Wait for the first process to finish initialization (with timeout).
				$lock_timeout = 120; // seconds — init does 4+ network calls that can take 60s+ total
				$lock_start   = time();
				while ( ! flock( $lock_file, LOCK_SH | LOCK_NB ) ) {
					if ( time() - $lock_start > $lock_timeout ) {
						throw new \RuntimeException( "Timed out after {$lock_timeout}s waiting for initialization lock. A previous run may have died without releasing the lock. Delete " . sys_get_temp_dir() . '/test-initialization-lock-file and retry.' );
					}
					usleep( 250000 ); // 250ms
				}

				echo sprintf( "Process %s proceeding after the lock is released\n", getenv( 'TEST_TOKEN' ) );

				// Read the stable source path from the lock file.
				$source_qit_home = fread( $lock_file, 1024 );
				fclose( $lock_file );

				if ( $source_qit_home === 'failed' ) {
					throw new \RuntimeException( 'Bailing because it failed to initialize.' );
				}

				if ( ! file_exists( $source_qit_home ) ) {
					throw new \RuntimeException( sprintf( 'The QIT_HOME directory "%s" does not exist.', $source_qit_home ) );
				}

				$GLOBALS['QIT_SOURCE_DIR'] = $source_qit_home;

				// Mirror the stable source to this worker's ephemeral QIT_HOME.
				$fs->mkdir( $GLOBALS['QIT_HOME'] );
				self::mirror_config_only( $source_qit_home, $GLOBALS['QIT_HOME'] );
			}
		}
	}

	/**
	 * Copy config files from source to destination. Large directories
	 * (cache, node-deps, temporary-envs) are symlinked instead of copied
	 * so all workers share the same data without duplicating ~2.7GB.
	 */
	private static function mirror_config_only( string $source, string $dest ): void {
		$fs = new Filesystem();
		$fs->mkdir( $dest );

		$symlink_dirs = [ 'cache', 'node-deps', 'temporary-envs' ];

		foreach ( new \DirectoryIterator( $source ) as $item ) {
			if ( $item->isDot() ) {
				continue;
			}

			$target = $dest . '/' . $item->getBasename();

			if ( $item->isDir() && in_array( $item->getBasename(), $symlink_dirs, true ) ) {
				// Symlink large directories — shared across all workers.
				if ( ! file_exists( $target ) ) {
					symlink( $item->getPathname(), $target );
				}
				continue;
			}

			if ( $item->isDir() ) {
				$fs->mirror( $item->getPathname(), $target );
			} else {
				$fs->copy( $item->getPathname(), $target, true );
			}
		}
	}
}

class QITTestFinish implements ExecutionFinishedSubscriber {
	public function notify( ExecutionFinished $event ): void {
		if ( getenv( 'CI' ) ) {
			echo "Skipping cleanup because this is a CI environment.\n";
		}

		self::delete_temp_environment();

		// Clean up old cache files and directories (older than 24 hours).
		$fs  = new Filesystem();
		$now = time();
		foreach ( glob( sys_get_temp_dir() . '/qit-init-cache-*.json' ) as $old_cache_file ) {
			if ( $now - filemtime( $old_cache_file ) > 86400 ) {
				$fs->remove( $old_cache_file );
			}
		}
		foreach ( glob( __DIR__ . '/tmp/qit-cache-*' ) as $old_cache_dir ) {
			if ( $now - filemtime( $old_cache_dir ) > 86400 ) {
				$fs->remove( $old_cache_dir );
			}
		}
	}

	public static function delete_temp_environment(): void {
		if ( empty( $GLOBALS['QIT_HOME'] ) ) {
			throw new \LogicException( 'The "QIT_HOME" GLOBALS must be set.' );
		}

		$fs = new Filesystem();

		// Check that $GLOBALS['QIT_HOME'] is in a safe location (either test dir or system temp dir)
		$is_in_test_dir = strpos( $GLOBALS['QIT_HOME'], __DIR__ ) === 0;
		$is_in_temp_dir = strpos( $GLOBALS['QIT_HOME'], sys_get_temp_dir() ) === 0;
		if ( ! $is_in_test_dir && ! $is_in_temp_dir ) {
			throw new \RuntimeException( sprintf( 'The QIT_HOME directory is not in a safe location. QIT_HOME: %s', $GLOBALS['QIT_HOME'] ) );
		}

		if ( file_exists( __DIR__ . '/qit-env.json' ) ) {
			unlink( __DIR__ . '/qit-env.json' );
		}

		if ( $fs->exists( $GLOBALS['QIT_HOME'] ) ) {
			// Only the source process mirrors cache back to avoid parallel write races.
			if ( ! empty( $GLOBALS['IS_SOURCE'] ) && file_exists( $GLOBALS['QIT_HOME'] . '/cache' ) ) {
				$fs->mirror( $GLOBALS['QIT_HOME'] . '/cache', __DIR__ . '/cache' );
			}

			// Only clean up ephemeral per-worker QIT_HOME directories (qit-test-*).
			// The stable init source (/tmp/qit-init-source) is NOT cleaned up here —
			// it persists so the hourly cache always points to a valid directory.
			// No need to wait for other workers — they mirror from the stable init source,
			// not from this worker's QIT_HOME.
			if ( strpos( $GLOBALS['QIT_HOME'], 'qit-test-' ) !== false && is_dir( $GLOBALS['QIT_HOME'] ) ) {
				$fs->remove( $GLOBALS['QIT_HOME'] );
			}
		}
	}
}
