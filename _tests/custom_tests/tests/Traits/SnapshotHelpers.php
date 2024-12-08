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
		];

		$processing_docker_pull_output = false;

		// Flags to handle npm-related lines.
		// We will remove all npm lines and then add a single placeholder line once we detect at least one npm-related line.
		$npm_lines_detected = false;

		foreach ( explode( "\n", $actual ) as $line ) {
			$trimmed_line = trim( $line );

			// Skip empty lines
			if ( $trimmed_line === '' ) {
				continue;
			}

			// Skip lines known to be removed
			$should_remove = false;
			foreach ( $lines_to_remove as $to_remove ) {
				if ( strpos( $line, $to_remove ) !== false ) {
					$should_remove = true;
					break;
				}
			}
			if ( $should_remove ) {
				continue;
			}

			// Ignore lines with just 'notice'
			if ( $trimmed_line === 'notice' ) {
				continue;
			}

			// Handle Docker pulling images output
			if ( strpos( $line, 'Unable to find image' ) !== false ) {
				$processing_docker_pull_output = true;
				continue;
			} elseif ( $processing_docker_pull_output && strpos( $line, 'Downloaded newer image for' ) !== false ) {
				$processing_docker_pull_output = false;
				continue;
			} elseif ( $processing_docker_pull_output ) {
				continue;
			}

			// Normalize timings
			$line = preg_replace( '/\(\d+\.\d+s\)/', '(TIME)', $line );
			$line = preg_replace( '/\(\d+ms\)/', '(TIME)', $line );

			// Normalize WooCommerce zip names
			$line = preg_replace( '/woocommerce\.[^ ]+\.zip/', 'woocommerce.VERSION.zip', $line );

			// Detect and remove npm-related lines:
			// For example:
			// "added 23 packages in 4s", "N packages are looking for funding",
			// "N vulnerabilities (N moderate, N high)", "some issues need review", etc.
			// We'll replace them all with a single placeholder line after processing.
			if (
				strpos( $line, 'added ' ) !== false && strpos( $line, 'packages' ) !== false ||
				strpos( $line, 'audited ' ) !== false ||
				strpos( $line, 'packages are looking for funding' ) !== false ||
				strpos( $line, 'vulnerabilities' ) !== false ||
				strpos( $line, 'Some issues need review' ) !== false ||
				// This also catches general npm lines if needed
				strpos( $line, 'npm' ) !== false
			) {
				$npm_lines_detected = true;
				continue;
			}

			// After removing npm lines and other variable lines, keep the rest
			$normalized_spaces .= trim( $line ) . "\n";
		}

		// If npm lines were detected, add a standardized placeholder line to the final output.
		if ( $npm_lines_detected ) {
			$normalized_spaces .= "npm packages installed (normalized)\n";
		}

		$this->assertMatchesSnapshot( $normalized_spaces, $driver ?? new TextDriver() );
	}
}