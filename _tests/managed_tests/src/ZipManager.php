<?php

use Symfony\Component\Process\Process;

class ZipManager {
	private $logger;

	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	public function generate_zips( array $test_type_test_runs ) {
		$this->logger->log( "Generating zips for tests" );
		$zip_processes  = [];
		$generated_zips = [];

		foreach ( $test_type_test_runs as $t ) {
			$path = $t['path'];
			$slug = $t['sut_slug'];

			if ( in_array( md5( $path . $slug ), $generated_zips, true ) ) {
				maybe_echo( "[INFO] Skipping zip generation for test in {$t['path']} (Already zipped)\n" );
				$this->logger->log( "Skipping zip for $slug in $path, already done" );
				continue;
			}

			$generated_zips[] = md5( $path . $slug );

			$args = [
				"docker",
				'run',
				'--rm',
				'--user',
				posix_getuid() . ":" . posix_getgid(),
				'-w',
				'/app',
				'-v',
				"$path:/app",
				'joshkeegan/zip:latest',
				'sh',
				'-c',
				"rm -f sut.zip && zip -r sut.zip $slug",
			];

			$this->logger->log( "Zip command: " . implode( ' ', $args ) );
			$zip_process = new Process( $args );
			$this->add_task_id_to_process( $zip_process, $t );
			$zip_processes[] = $zip_process;
		}

		if ( count( $zip_processes ) === 0 ) {
			// No zip tasks to run
			$this->logger->log( "No zip tasks found. Skipping zip generation." );

			return;
		}

		// Run each process sequentially
		foreach ( $zip_processes as $zip_process ) {
			$zip_process->run( function ( string $type, string $buffer ) {
				maybe_echo( $buffer );
				$this->logger->log( "Zip output: " . $buffer );
			} );

			if ( ! $zip_process->isSuccessful() ) {
				$this->logger->log( "Zip failed for: " . $zip_process->getEnv()['qit_task_id'] );
				throw new RuntimeException(
					"Failed to create zip file for test: {$zip_process->getEnv()['qit_task_id']}"
				);
			} else {
				$this->logger->log( "Zip succeeded for: " . $zip_process->getEnv()['qit_task_id'] );
			}
		}
	}

	private function add_task_id_to_process( Process $process, array $test_run ) {
		$task_id_parts = [
			sprintf( "[%s -", ucwords( $test_run['type'] ) ),
			sprintf( "%s]", $test_run['slug'] ),
		];

		if ( ! empty( $test_run['php'] ) ) {
			$task_id_parts[] = sprintf( "[PHP %s]", $test_run['php'] );
		}
		if ( ! empty( $test_run['wp'] ) ) {
			$task_id_parts[] = sprintf( "[WP %s]", $test_run['wp'] );
		}
		if ( ! empty( $test_run['woo'] ) ) {
			$task_id_parts[] = sprintf( "[Woo %s]", $test_run['woo'] );
		}
		if ( ! empty( $test_run['features'] ) ) {
			$task_id_parts[] = sprintf( "[Features %s]", implode( ', ', $test_run['features'] ) );
		}

		$task_id = implode( ' ', $task_id_parts ) . ": ";
		$process->setEnv( array_merge( $process->getEnv(), [ 'qit_task_id' => $task_id ] ) );
	}
}