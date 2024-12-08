<?php

namespace QIT\SelfTests\CustomTests\Traits;

use Spatie\Snapshots\Drivers\TextDriver;
use Spatie\Snapshots\MatchesSnapshots;

trait SnapshotHelpers {
	use MatchesSnapshots;

	public function assertMatchesNormalizedSnapshot( string $actual, ?\Spatie\Snapshots\Driver $driver = null ): void {
		$actual = str_replace( rtrim( sys_get_temp_dir(), '/' ) . '/', '/tmp-normalized/', $actual );
		$actual = str_replace( '/tmp/', '/tmp-normalized/', $actual );
		$actual = preg_replace( '/qit-results-[a-z0-9]+/', 'qit-results-normalizedid', $actual );

		/*
		 * "paratest" sets the "TEST_TOKEN" env var.
		 * If this is not set, it means we are running in a normal PHPUnit environment.
		 */
		if ( empty( getenv( 'TEST_TOKEN' ) ) ) {
			$actual = preg_replace( '/First-time setup is pulling Docker images and caching downloads. Subsequent runs will be faster.\n/', '', $actual );
		}

		$normalized_spaces = '';

		$lines_to_remove = [
			'First-time setup is pulling Docker images',
			'Wrote debug contents to',
			'npm',
		];

		$processing_docker_pull_output = false;

		foreach ( explode( "\n", $actual ) as $line ) {
			foreach ( $lines_to_remove as $to_remove ) {
				if ( strpos( $line, $to_remove ) !== false ) {
					continue 2;
				}
			}

			if ( trim( $line ) === 'notice' ) {
				continue;
			}

			if ( trim( $line ) === '' ) {
				continue;
			}

			if ( strpos( $line, 'Unable to find image' ) !== false ) {
				$processing_docker_pull_output = true;
				continue;
			} elseif ( $processing_docker_pull_output && strpos( $line, 'Downloaded newer image for' ) !== false ) {
				$processing_docker_pull_output = false;
				continue;
			} elseif ( $processing_docker_pull_output ) {
				continue;
			}

			// Normalize timings (e.g. "(8.9s)" -> "(TIME)")
			$line = preg_replace( '/\(\d+\.\d+s\)/', '(TIME)', $line );
			$line = preg_replace( '/\(\d+ms\)/', '(TIME)', $line );

			// Normalize WooCommerce zip names
			$line = preg_replace( '/woocommerce\.[^ ]+\.zip/', 'woocommerce.VERSION.zip', $line );

			// Normalize npm install/audit lines
			$line = preg_replace( '/added \d+ packages, and audited \d+ packages in \S+/', 'added N packages, and audited N packages in (TIME)', $line );
			$line = preg_replace( '/\d+ packages are looking for funding/', 'N packages are looking for funding', $line );
			$line = preg_replace( '/\d+ vulnerabilities \(\d+ moderate, \d+ high\)/', 'N vulnerabilities (N moderate, N high)', $line );

			$normalized_spaces .= trim( $line ) . "\n";
		}

		$this->assertMatchesSnapshot( $normalized_spaces, $driver ?? new TextDriver() );
	}
}