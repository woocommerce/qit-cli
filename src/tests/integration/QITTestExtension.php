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
		$run_id = uniqid( 'qit_custom_tests_' );
		$GLOBALS['RUN_ID'] = $run_id;

		// QIT_HOME is already set by bootstrap.php
		if ( empty( $GLOBALS['QIT_HOME'] ) ) {
			throw new \RuntimeException( 'QIT_HOME must be set by bootstrap.php before QITTestExtension runs' );
		}

		$fs = new Filesystem();

		// Check for cached initialization data with hourly timestamp.
		$cache_timestamp = date( 'Ymd-H' ); // e.g., 20250522-20
		$cache_file      = sys_get_temp_dir() . "/qit-init-cache-$cache_timestamp.json";
		$cache_valid     = file_exists( $cache_file );

		// Detect if running in Paratest (TEST_TOKEN is set to a number) or single-process PHPUnit (TEST_TOKEN starts with 'serial-').
		$is_paratest = ! empty( getenv( 'TEST_TOKEN' ) ) && strpos( getenv( 'TEST_TOKEN' ), 'serial-' ) !== 0;

		if ( ! getenv( 'QIT_FORCE_FRESH_SYNC' ) && $cache_valid ) {
			echo "Warning: Using cached initialization data from $cache_file\n";
			try {
				$cached_data = json_decode( file_get_contents( $cache_file ), true, 512, JSON_THROW_ON_ERROR );
				file_put_contents( '/tmp/qit_debug.log', "Cache file contents: " . print_r( $cached_data, true ) . "\n", FILE_APPEND );
				if ( ! $cached_data || ! isset( $cached_data['source_qit_home'] ) || ! file_exists( $cached_data['source_qit_home'] ) ) {
					throw new \RuntimeException( "Invalid or missing cache data in $cache_file. Cache contents: " . print_r( $cached_data, true ) );
				}
				$source_qit_home = $cached_data['source_qit_home'];
				$fs->mkdir( $GLOBALS['QIT_HOME'] );
				$fs->mirror( $source_qit_home, $GLOBALS['QIT_HOME'] );
				$GLOBALS['IS_SOURCE'] = false;
			} catch ( \JsonException $e ) {
				file_put_contents( '/tmp/qit_debug.log', "Cache JSON decode failed: {$e->getMessage()}\n", FILE_APPEND );
				throw new \RuntimeException( "Failed to parse cache file $cache_file: {$e->getMessage()}" );
			}
		} else {
			// We utilize the filesystem as shared state to coordinate parallel processes.
			if ( ! touch( sys_get_temp_dir() . '/test-initialization-lock-file' ) ) {
				throw new \RuntimeException( 'Failed to create lock file at ' . sys_get_temp_dir() . '/test-initialization-lock-file' );
			}

			$lock_file = fopen( sys_get_temp_dir() . '/test-initialization-lock-file', 'w+' );

			if ( ! $lock_file ) {
				throw new \RuntimeException( 'Failed to open lock file at ' . sys_get_temp_dir() . '/test-initialization-lock-file' );
			}

			// Attempt to get an exclusive lock - first process wins.
			if ( flock( $lock_file, LOCK_EX | LOCK_NB ) ) {
				echo sprintf( "Process %s has exclusive lock\n", getenv( 'TEST_TOKEN' ) ?: 'single' );
				// Since we are the single process that has an exclusive lock, we run the initialization.

				// Cleanup initial state.
				// DEBUG: Log what we're deleting
				$files_to_delete = array_merge(
					glob( sys_get_temp_dir() . '/qit-running-*' ) ?: [],
					glob( sys_get_temp_dir() . '/qit-test-tag-lock-*' ) ?: []
				);
				foreach ( $files_to_delete as $file ) {
					error_log( "[QIT DEBUG] Deleting: " . basename( $file ) );
					unlink( $file );
				}
				if ( file_exists( sys_get_temp_dir() . '/qit-semaphore' ) ) {
					unlink( sys_get_temp_dir() . '/qit-semaphore' );
				}
				// Clean up old tmp_qit_config-* directories in sys_get_temp_dir()
				$old_test_dirs = glob( sys_get_temp_dir() . '/tmp_qit_config-*' );
				if ( $old_test_dirs ) {
					foreach ( $old_test_dirs as $dir ) {
						try {
							$fs->remove( $dir );
						} catch ( \Exception $e ) {
							// Ignore cleanup errors for old directories
						}
					}
				}

				// Ensure QIT_HOME directory exists
				$fs->mkdir( $GLOBALS['QIT_HOME'] );

				// Enable dev mode.
				try {
					$dev = new Process( [ 'php', $GLOBALS['qit-php'], 'dev' ] );
					$dev->setEnv( [ 'QIT_HOME' => $GLOBALS['QIT_HOME'] ] );
					$dev->setTimeout( 30 );
					$dev->setIdleTimeout( 30 );
					$dev->mustRun( function ( $type, $buffer ) {
						echo $buffer;
					} );
				} catch ( \Exception $e ) {
					file_put_contents( '/tmp/qit_debug.log', "qit dev failed: {$e->getMessage()}\n", FILE_APPEND );
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
					$add_environment->setEnv( [ 'QIT_HOME' => $GLOBALS['QIT_HOME'] ] );
					$add_environment->mustRun( function ( $type, $buffer ) {
						echo $buffer;
					} );
				} catch ( \Exception $e ) {
					file_put_contents( '/tmp/qit_debug.log', "backend:add failed: {$e->getMessage()}\n", FILE_APPEND );
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
						$add_partner->setEnv( [ 'QIT_HOME' => $GLOBALS['QIT_HOME'] ] );
						$add_partner->mustRun( function ( $type, $buffer ) {
							echo $buffer;
						} );
					} catch ( \Exception $e ) {
						file_put_contents( '/tmp/qit_debug.log', "partner:add failed: {$e->getMessage()}\n", FILE_APPEND );
						throw new \RuntimeException( "Failed to run partner:add: {$e->getMessage()}" );
					}
				} else {
					echo "Skipping partner add for staging environment\n";
				}

				// Validate connection. Run "qit extensions" and assert "woocommerce_id" is present.
				$max_attempts = 2;
				$sync_data    = null;
				for ( $attempt = 1; $attempt <= $max_attempts; $attempt ++ ) {
					try {
						$extensions = new Process( [ 'php', $GLOBALS['qit-php'], 'extensions' ] );
						$extensions->setEnv( [ 'QIT_HOME' => $GLOBALS['QIT_HOME'] ] );
						$extensions->mustRun();
						$sync_data = $extensions->getOutput();
						file_put_contents( '/tmp/qit_debug.log', "qit extensions output (attempt $attempt): $sync_data\n", FILE_APPEND );
						break;
					} catch ( \Exception $e ) {
						file_put_contents( '/tmp/qit_debug.log', "qit extensions failed (attempt $attempt): {$e->getMessage()}\n", FILE_APPEND );
						if ( $attempt === $max_attempts ) {
							throw new \RuntimeException( "Failed to run qit extensions after $max_attempts attempts: {$e->getMessage()}" );
						}
						sleep( 5 ); // Wait before retrying
					}
				}

				$woocommerce_ids_per_environment = [
					'staging'    => 18734003134382,
					'local'      => 18734001206047,
					'production' => 18734002449992,
				];

				$woocommerce_id = $woocommerce_ids_per_environment[ $_ENV['QIT_CUSTOM_TESTS_ENV'] ];

				if ( strpos( $sync_data, (string) $woocommerce_id ) === false ) {
					file_put_contents( '/tmp/qit_debug.log', "Sync validation failed. Expected ID: $woocommerce_id, Sync data: $sync_data\n", FILE_APPEND );
					if ( ! fwrite( $lock_file, 'failed' ) ) {
						throw new \RuntimeException( 'Failed to write to lock file.' );
					}
					throw new \RuntimeException( "Sync validation failed. Expected WooCommerce ID $woocommerce_id not found in sync data. Sync data: $sync_data\n" );
				}

				if ( ! fwrite( $lock_file, $GLOBALS['QIT_HOME'] ) ) {
					throw new \RuntimeException( 'Failed to write to lock file.' );
				}

				if ( ! file_exists( __DIR__ . '/cache' ) ) {
					mkdir( __DIR__ . '/cache' );
				}

				if ( ! file_exists( __DIR__ . '/tmp' ) ) {
					mkdir( __DIR__ . '/tmp' );
				}

				$fs->mirror( __DIR__ . '/cache', $GLOBALS['QIT_HOME'] . '/cache' );

				// Cache the initialized QIT_HOME directory path.
				$cache_data = [ 'source_qit_home' => $GLOBALS['QIT_HOME'] ];
				try {
					$json_output = json_encode( $cache_data, JSON_THROW_ON_ERROR );
					if ( file_put_contents( $cache_file, $json_output ) === false ) {
						throw new \RuntimeException( "Failed to write cache file $cache_file" );
					}
					file_put_contents( '/tmp/qit_debug.log', "Cache file written: $cache_file, Contents: $json_output\n", FILE_APPEND );
				} catch ( \JsonException $e ) {
					file_put_contents( '/tmp/qit_debug.log', "Cache JSON encode failed: {$e->getMessage()}\n", FILE_APPEND );
					throw new \RuntimeException( "Failed to encode cache data for $cache_file: {$e->getMessage()}" );
				}

				$GLOBALS['IS_SOURCE'] = true;

				echo "Main process has authenticated and is releasing the lock...\n";

				flock( $lock_file, LOCK_UN );
				fclose( $lock_file );

				// Wait after unlocking so that other processes can copy this source directory (only for Paratest).
				if ( $is_paratest ) {
					sleep( 10 );
				}
			} else {
				if ( ! touch( sys_get_temp_dir() . "/qit-running-{$GLOBALS['RUN_ID']}" ) ) {
					throw new \RuntimeException( 'Failed to create running file at ' . sys_get_temp_dir() . "/qit-running-{$GLOBALS['RUN_ID']}" );
				}

				// If no exclusive lock is available, block until the first process is done with initialization
				flock( $lock_file, LOCK_SH );

				echo sprintf( "Process %s proceeding after the lock is released\n", getenv( 'TEST_TOKEN' ) );

				// Read the contents of the lock file to get the QIT_HOME directory.
				$source_qit_home = fread( $lock_file, 1024 );
				fclose( $lock_file );

				if ( $source_qit_home === 'failed' ) {
					throw new \RuntimeException( 'Bailing because it failed to initialize.' );
				}

				if ( ! file_exists( $source_qit_home ) ) {
					throw new \RuntimeException( sprintf( 'The QIT_HOME directory "%s" does not exist.', $source_qit_home ) );
				}

				$GLOBALS['QIT_SOURCE_DIR'] = $source_qit_home;

				// Mirror the directory to the test-specific QIT_HOME.
				$fs->mkdir( $GLOBALS['QIT_HOME'] );
				$fs->mirror( $source_qit_home, $GLOBALS['QIT_HOME'] );
			}
		}

		// Make sure each parallel process is spaced out a little bit (only for Paratest).
		if ( $is_paratest ) {
			$semaphore = sys_get_temp_dir() . '/qit-semaphore.log';
			$fp        = fopen( $semaphore, 'c+' );
			if ( ! $fp ) {
				throw new \RuntimeException( "Failed to open semaphore file. $semaphore" );
			}
			$start = microtime( true );
			if ( flock( $fp, LOCK_EX ) ) {
				if ( empty( fread( $fp, 1 ) ) ) {
					// First process.
					file_put_contents( $semaphore, sprintf( "[%s - Microtime: %s] First process\n", getenv( 'TEST_TOKEN' ), microtime() ) );
				} else {
					// Lock acquired, now wait 5 seconds between each request.
					$sleep = 5000000;
					file_put_contents( $semaphore, sprintf( "[%s - Microtime: %s], Sleeping for $sleep microseconds\n", getenv( 'TEST_TOKEN' ), microtime() ), FILE_APPEND );
					usleep( $sleep );
					file_put_contents( $semaphore, sprintf( "[%s - Microtime: %s] Finished sleeping (Waited %s total)\n", getenv( 'TEST_TOKEN' ), microtime(), microtime( true ) - $start ), FILE_APPEND );
				}
				flock( $fp, LOCK_UN );
			}
			fclose( $fp );
		}
	}
}

class QITTestFinish implements ExecutionFinishedSubscriber {
	public function notify( ExecutionFinished $event ): void {
		if ( getenv( 'CI' ) ) {
			echo "Skipping cleanup because this is a CI environment.\n";
		}

		if ( ! isset( $GLOBALS['RUN_ID'] ) ) {
			throw new \RuntimeException( 'The "RUN_ID" GLOBAL must be set.' );
		}

		if ( file_exists( sys_get_temp_dir() . "/qit-running-{$GLOBALS['RUN_ID']}" ) ) {
			unlink( sys_get_temp_dir() . "/qit-running-{$GLOBALS['RUN_ID']}" );
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
			// Copy files from $GLOBALS['QIT_HOME'] . '/cache' to __DIR__. '/cache'
			if ( file_exists( $GLOBALS['QIT_HOME'] . '/cache' ) ) {
				$fs->mirror( $GLOBALS['QIT_HOME'] . '/cache', __DIR__ . '/cache' );
			}

			// Only clean up non-persistent QIT_HOME directories (tmp_qit_config-*).
			if ( strpos( $GLOBALS['QIT_HOME'], 'tmp_qit_config-' ) !== false ) {
				if ( isset( $GLOBALS['IS_SOURCE'] ) && $GLOBALS['IS_SOURCE'] ) {
					$timeout = 300;
					// Wait for all other tests to finish.
					while ( count( glob( sys_get_temp_dir() . '/qit-running-*' ) ) > 1 ) {
						echo sprintf( "Main process is waiting for %d other tests to finish.\n", count( glob( sys_get_temp_dir() . '/qit-running-*' ) ) - 1 );
						sleep( 5 );
						$timeout -= 5;
						if ( $timeout <= 0 ) {
							throw new \RuntimeException( 'Timeout waiting for other tests to finish.' );
						}
					}
				}
				$fs->remove( $GLOBALS['QIT_HOME'] );
			}
		}
	}
}
