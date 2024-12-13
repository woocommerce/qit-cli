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
		$actual = preg_replace( '/qit-env-[a-f0-9]{32}\.json/', 'qit-env-<hash>.json', $actual );
		// Remove Docker pull-related lines
		$actual = preg_replace(
			'/^(?:Unable to find image .*?locally|.*Pulling fs layer.*|.*Verifying Checksum.*|.*Download complete.*|.*Pull complete.*|Digest:.*|Status: Downloaded newer image for .*?|.*: Pulling from .*|.*Waiting.*)\r?\n/m',
			'',
			$actual
		);

		if ( empty( getenv( 'TEST_TOKEN' ) ) ) {
			$actual = preg_replace(
				'/First-time setup is pulling Docker images and caching downloads. Subsequent runs will be faster.\n/',
				'',
				$actual
			);
		}

		$lines = explode( "\n", $actual );

		// Patterns that indicate npm-related lines, now more comprehensive:
		$npm_patterns = [
			'/added \d+ packages/',
			'/packages are looking for funding/',
			'/\d+ vulnerabilities \(\d+ moderate, \d+ high\)/',
			'/Some issues need review/',
			'/a different dependency\./',

			// General catch-all for npm warnings, notices, etc.
			'/^npm\s/i',

			// More general matching for fund/audit lines:
			'/npm fund/i',
			'/npm audit/i',
		];

		$lines_to_remove = [
			'First-time setup is pulling Docker images',
			'Wrote debug contents to',
			'notice',
		];

		$first_npm_line_index = null;
		$processed            = [];

		foreach ( $lines as $index => $line ) {
			// Check for removable lines
			$should_remove = false;
			foreach ( $lines_to_remove as $remove_str ) {
				if ( strpos( $line, $remove_str ) !== false ) {
					$should_remove = true;
					break;
				}
			}
			if ( $should_remove ) {
				continue;
			}

			// Check if line is npm-related
			$is_npm_line = false;
			foreach ( $npm_patterns as $pattern ) {
				if ( preg_match( $pattern, $line ) ) {
					$is_npm_line = true;
					break;
				}
			}

			if ( $is_npm_line ) {
				// Mark where we first encountered npm output
				if ( $first_npm_line_index === null ) {
					$first_npm_line_index = count( $processed );
				}
				// Skip adding this line
				continue;
			}

			// Normalize timings
			$line = preg_replace( '/\(\d+\.\d+s\)/', '(TIME)', $line );
			$line = preg_replace( '/\(\d+ms\)/', '(TIME)', $line );

			// Normalize WooCommerce zip names
			$line = preg_replace( '/woocommerce\.[^ ]+\.zip/', 'woocommerce.VERSION.zip', $line );

			$processed[] = $line;
		}

		// If npm lines were found, insert our normalized line
		if ( $first_npm_line_index !== null ) {
			array_splice( $processed, $first_npm_line_index, 0, [ 'npm packages installed (normalized)' ] );
		}

		// Trim lines.
		$processed = array_map( 'trim', $processed );

		// Remove empty lines.
		$processed = array_filter( $processed );


		$final_output = implode( "\n", $processed ) . "\n";

		$this->assertMatchesSnapshot( $final_output, $driver ?? new TextDriver() );
	}
}