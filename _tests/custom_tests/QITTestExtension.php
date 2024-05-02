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

		if ( ! file_exists( __DIR__ . '/../../qit' ) ) {
			throw new \RuntimeException( sprintf( 'The qit binary was not found at %s.', realpath( __DIR__ . '/../../qit' ) ) );
		}

		$GLOBALS['qit'] = __DIR__ . '/../../qit';

		// Generate an ID for this run.
		$run_id      = uniqid( 'qit_custom_tests_' );
		$qit_tmp_dir = __DIR__ . "/tmp/tmp_qit_config-$run_id";

		$GLOBALS['QIT_HOME'] = $qit_tmp_dir;
		$GLOBALS['RUN_ID']   = $run_id;

		$fs = new Filesystem();

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
			echo sprintf( "Process %s has exclusive lock\n", getenv( 'TEST_TOKEN' ) );
			// Since we are the single process that has an exclusive lock, we run the initialization.

			// Cleanup initial state.
			array_map( 'unlink', glob( sys_get_temp_dir() . '/qit-running-*' ) );
			array_map( 'unlink', glob( sys_get_temp_dir() . '/qit-test-tag-lock-*' ) );
			if ( file_exists( sys_get_temp_dir() . '/qit-semaphore' ) ) {
				unlink( sys_get_temp_dir() . '/qit-semaphore' );
			}
			// Delete all directories in the current dir that matches the pattern "tmp_qit_config-*"
			$fs->remove( __DIR__ . '/tmp/' );

			if ( ! mkdir( __DIR__ . '/tmp', 0755, true ) ) {
				throw new \RuntimeException( 'Failed to create the tmp directory.' );
			}

			// Enable dev mode.
			$dev = new Process( [ $GLOBALS['qit'], 'dev' ] );
			$dev->setEnv( [
				'QIT_HOME' => $GLOBALS['QIT_HOME'],
			] );
			$dev->setTimeout( 30 );
			$dev->setIdleTimeout( 30 );
			$dev->mustRun( function ( $type, $buffer ) {
				echo $buffer;
			} );

			// Add the environment.
			$add_environment = new Process( [
				$GLOBALS['qit'],
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

			if ( $_ENV['QIT_CUSTOM_TESTS_ENV'] !== 'staging' ) {
				// Add the partner account that will be used.
				$add_partner = new Process( [
					$GLOBALS['qit'],
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
			} else {
				echo "Skipping partner add for staging environment\n";
			}

			// Validate connection. Run "qit extensions" and assert "18734003134382" is present, which is the ID of the "woocommerce" extension.
			$extensions = new Process( [ $GLOBALS['qit'], 'extensions' ] );
			$extensions->setEnv( [ 'QIT_HOME' => $GLOBALS['QIT_HOME'] ] );
			$extensions->mustRun();

			$woocommerce_ids_per_environment = [
				'staging'    => 18734003134382,
				'local'      => 18734002449992,
				'production' => 18734002449992,
			];

			$woocommerce_id = $woocommerce_ids_per_environment[ $_ENV['QIT_CUSTOM_TESTS_ENV'] ];

			if ( strpos( $extensions->getOutput(), $woocommerce_id ) === false ) {
				if ( ! fwrite( $lock_file, 'failed' ) ) {
					throw new \RuntimeException( 'Failed to write to lock file.' );
				}
			} else {
				if ( ! fwrite( $lock_file, $GLOBALS['QIT_HOME'] ) ) {
					throw new \RuntimeException( 'Failed to write to lock file.' );
				}
			}

			if ( ! file_exists( __DIR__ . '/cache' ) ) {
				mkdir( __DIR__ . '/cache' );
			}

			if ( ! file_exists( __DIR__ . '/tmp' ) ) {
				mkdir( __DIR__ . '/tmp' );
			}

			$fs->mirror( __DIR__ . '/cache', $GLOBALS['QIT_HOME'] . '/cache' );

			$GLOBALS['IS_SOURCE'] = true;

			echo "Main process has authenticated and is releasing the lock...\n";

			flock( $lock_file, LOCK_UN );
			fclose( $lock_file );

			// Wait after unlocking so that other processes can copy this source directory.
			sleep( 10 );
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

			// Mirror the directory.
			$fs->mirror( $source_qit_home, $GLOBALS['QIT_HOME'] );
		}

		// Make sure each parallel process is spaced out a little bit.
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
	}

	public static function delete_temp_environment(): void {
		if ( empty( $GLOBALS['QIT_HOME'] ) || empty( $GLOBALS['qit'] ) ) {
			throw new \LogicException( 'The "QIT_HOME" and "qit" GLOBALS must be set.' );
		}

		$fs = new Filesystem();

		// Check that $GLOBALS['QIT_HOME'] is inside this directory.
		if ( strpos( $GLOBALS['QIT_HOME'], __DIR__ ) !== 0 ) {
			throw new \RuntimeException( sprintf( 'The QIT_HOME directory is not inside the test directory. This is a security risk. QIT_HOME: %s, Test Directory: %s', $GLOBALS['QIT_HOME'], __DIR__ ) );
		}

		if ( file_exists( __DIR__ . '/qit-env.json' ) ) {
			unlink( __DIR__ . '/qit-env.json' );
		}

		if ( $fs->exists( $GLOBALS['QIT_HOME'] ) ) {
			// Copy files from $GLOBALS['QIT_HOME'] . '/cache' to __DIR__. '/cache'
			if ( file_exists( $GLOBALS['QIT_HOME'] . '/cache' ) ) {
				$fs->mirror( $GLOBALS['QIT_HOME'] . '/cache', __DIR__ . '/cache' );
			}

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