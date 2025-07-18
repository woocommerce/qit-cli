<?php

namespace QIT\SelfTests\CustomTests\Traits;

use Spatie\Snapshots\Drivers\TextDriver;
use Spatie\Snapshots\MatchesSnapshots;

trait SnapshotHelpers {
	use MatchesSnapshots;

	protected function normalize_untriggered_show_output( string $test_output_string ): string {
		// Replace hash values with a standard placeholder "12345"
		$test_output_string = preg_replace_callback(
			'/"hash"\s*:\s*"([^"]+)"|\'hash\'\s*:\s*\'([^\']+)\'/i',
			function ( $matches ) {
				// Replace the hash with "12345" while keeping the original quote style
				if ( ! empty( $matches[1] ) ) {
					return '"hash":"12345"';
				} else {
					return "'hash':'12345'";
				}
			},
			$test_output_string
		);

		// Replace woo_id values with a standard placeholder "12345"
		$test_output_string = preg_replace_callback(
			'/"woo_id"\s*:\s*"?(\d+)"?|\'woo_id\'\s*:\s*\'?(\d+)\'?/i',
			function ( $matches ) {
				// Replace the woo_id with "12345" while maintaining the original format
				return '"woo_id":12345';
			},
			$test_output_string
		);

		return $test_output_string;
	}

	protected function normalized_registered_group_output( string $test_output_string ): string {
		// Replace hash values with a standard placeholder
		$test_output_string = preg_replace_callback(
			'/"hash"\s*:\s*"([^"]+)"|\'hash\'\s*:\s*\'([^\']+)\'/i',
			function ( $matches ) {
				// Replace the hash with "12345" while keeping the original quote style
				if ( ! empty( $matches[1] ) ) {
					return '"hash":"12345"';
				} else {
					return "'hash':'12345'";
				}
			},
			$test_output_string
		);

		// Replace woo_id values with a standard placeholder
		$test_output_string = preg_replace_callback(
			'/"woo_id"\s*:\s*"?(\d+)"?|\'woo_id\'\s*:\s*\'?(\d+)\'?/i',
			function ( $matches ) {
				// Replace the woo_id with "12345" while maintaining the original format
				return '"woo_id":12345';
			},
			$test_output_string
		);

		// Normalize Group ID
		$test_output_string = preg_replace( '/Group ID: \d+/', 'Group ID: 12345', $test_output_string );

		// Normalize Group Identifier
		$test_output_string = preg_replace( '/Group Identifier: .+/', 'Group Identifier: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx_0000000000', $test_output_string );

		// Normalize Test Run ID
		$test_output_string = preg_replace( '/Test Run ID: \d+/', 'Test Run ID: 12345', $test_output_string );

		// Normalize Woo ID in text format
		$test_output_string = preg_replace( '/Woo ID: \d+/', 'Woo ID: 12345', $test_output_string );

		// Normalize Test Results Manager URL
		$test_output_string = preg_replace(
			'/https?:\/\/[^\/\s]+\?qit_results=\d+\.[a-zA-Z0-9]+/',
			'https://example.com?qit_results=12345.normalized_hash',
			$test_output_string
		);

		return $test_output_string;
	}

	protected function normalize_remote_group_run_output( string $test_output_string ): string {
		// Replace hash values with a standard placeholder
		$test_output_string = preg_replace_callback(
			'/"hash"\s*:\s*"([^"]+)"|\'hash\'\s*:\s*\'([^\']+)\'/i',
			function ( $matches ) {
				// Replace the hash with "12345" while keeping the original quote style
				if ( ! empty( $matches[1] ) ) {
					return '"hash":"12345"';
				} else {
					return "'hash':'12345'";
				}
			},
			$test_output_string
		);

		// Replace woo_id values with a standard placeholder
		$test_output_string = preg_replace_callback(
			'/"woo_id"\s*:\s*"?(\d+)"?|\'woo_id\'\s*:\s*\'?(\d+)\'?/i',
			function ( $matches ) {
				// Replace the woo_id with "12345" while maintaining the original format
				return '"woo_id":12345';
			},
			$test_output_string
		);

		// Normalize Group ID
		$test_output_string = preg_replace( '/Group ID: \d+/', 'Group ID: 12345', $test_output_string );

		// Normalize Group Identifier
		$test_output_string = preg_replace( '/Group Identifier: [a-f0-9-]+_\d+/', 'Group Identifier: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx_0000000000', $test_output_string );

		// Normalize Test Run ID
		$test_output_string = preg_replace( '/Test Run ID: \d+/', 'Test Run ID: 12345', $test_output_string );

		// Normalize Woo ID in text format
		$test_output_string = preg_replace( '/Woo ID: \d+/', 'Woo ID: 12345', $test_output_string );

		// Normalize Test Results Manager URL
		$test_output_string = preg_replace(
			'/https?:\/\/[^\/\s]+\?qit_results=\d+\.[a-zA-Z0-9]+/',
			'https://example.com?qit_results=12345.normalized_hash',
			$test_output_string
		);

		return $test_output_string;
	}

	protected function normalize_complete_group_run_output( string $test_output_string, bool $skip_status = false ): string {
		// Replace hash values with a standard placeholder
		$test_output_string = preg_replace_callback(
			'/"hash"\s*:\s*"([^"]+)"|\'hash\'\s*:\s*\'([^\']+)\'/i',
			function ( $matches ) {
				// Replace the hash with "12345" while keeping the original quote style
				if ( ! empty( $matches[1] ) ) {
					return '"hash":"12345"';
				} else {
					return "'hash':'12345'";
				}
			},
			$test_output_string
		);


		// Normalize Group ID
		$test_output_string = preg_replace( '/Group ID: \d+/', 'Group ID: 12345', $test_output_string );

		// Normalize Group Identifier
		$test_output_string = preg_replace( '/Group Identifier: .+/', 'Group Identifier: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx_0000000000', $test_output_string );

		// Normalize Test Run ID
		$test_output_string = preg_replace( '/Test Run ID: \d+/', 'Test Run ID: 12345', $test_output_string );

		// Normalize Woo ID in text format
		$test_output_string = preg_replace( '/Woo ID: \d+/', 'Woo ID: 12345', $test_output_string );

		// Normalize Status values
		if ( ! $skip_status ) {
			$test_output_string = preg_replace( '/Status: [a-zA-Z0-9_-]+/', 'Status: normalized', $test_output_string );
		}

		// Normalize Test Results Manager URL
		$test_output_string = preg_replace(
			'/https?:\/\/[^\/\s]+\?qit_results=\d+\.[a-zA-Z0-9]+/',
			'https://example.com?qit_results=12345.normalized_hash',
			$test_output_string
		);

		return $test_output_string;
	}

	public function assertMatchesNormalizedSnapshot( string $actual, ?\Spatie\Snapshots\Driver $driver = null ): void {
		$actual = str_replace( rtrim( sys_get_temp_dir(), '/' ) . '/', '/tmp-normalized/', $actual );
		$actual = str_replace( '/tmp/', '/tmp-normalized/', $actual );
		$actual = preg_replace( '/qit-results-[a-z0-9]+/', 'qit-results-normalizedid', $actual );
		$actual = preg_replace( '/qit-env-[a-f0-9]{32}\.json/', 'qit-env-<hash>.json', $actual );
		// Remove Docker pull-related lines
		// remove Docker pull-related lines
		$actual = preg_replace(
			'/^(?:Unable to find image .*?locally'
			. '|.*Pulling fs layer.*'
			. '|.*Verifying Checksum.*'
			. '|.*Download complete.*'
			. '|.*Pull complete.*'
			. '|Digest:.*'
			. '|Status: Downloaded newer image for .*?'
			. '|.*: Pulling from .*'
			. '|\\d+(?:\\.\\d+)+:'  // <-- matches a version pattern like 1.48.1.
			. '|Pulling from automattic\\/qit-runner-playwright'
			. '|.*Waiting.*)\r?\n/m',
			'',
			$actual
		);
		// Remove lines that are just a hex string followed by a colon (layer IDs)
		$actual = preg_replace( '/^[a-f0-9]+:\r?\n/m', '', $actual );

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
			'high severity vulnerabilities',
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

	/**
	 * Assert that an early‑bail Pre‑Command result matches its snapshot.
	 *
	 * @param string|array $payload Raw JSON string returned by qit_precommand()
	 *                               **or** the already‑decoded array.
	 * @param array<string,string> $extraReplacements Optional additional
	 *                               string‑to‑string replacements a caller
	 *                               might want to inject.
	 */
	protected function assertMatchesPrecommandSnapshot(
		string|array $payload,
		array $extraReplacements = []
	): void {
		// ─────────────────────────────────────────────────────────────
		// 1. Decode (if necessary) and discover volatile identifiers
		// ─────────────────────────────────────────────────────────────
		$data = is_string( $payload ) ? json_decode( $payload, true ) : $payload;

		if ( ! is_array( $data ) ) {
			throw new \RuntimeException( 'Pre‑Command payload is not valid JSON.' );
		}

		$envId    = $data['env_info']['env_id'] ?? null;
		$port     = $data['env_info']['nginx_port'] ?? null;
		$repoRoot = realpath( __DIR__ . '/../../..' );   // adjust depth once
		$tmp      = rtrim( sys_get_temp_dir(), '/' );

		// ─────────────────────────────────────────────────────────────
		// 2. Recursive normalisation helper
		// ─────────────────────────────────────────────────────────────
		$normalise = function ( &$value ) use (
			&$normalise, $envId, $port, $tmp, $extraReplacements, $repoRoot
		) {
			if ( is_array( $value ) ) {
				foreach ( $value as &$v ) {
					$normalise( $v );
				}

				return;
			}

			if ( ! is_string( $value ) ) {
				// created_at → constant timestamp
				if ( is_numeric( $value ) ) {
					// keys are lost here, fix afterwards
				}

				return;
			}

			// Generic path / id replacements
			$value = str_replace( "$tmp/", '/tmp-normalised/', $value );

			if ( $envId ) {
				$value = str_replace( $envId, 'ENV_ID', $value );
				$value = preg_replace( '/e2e-[a-f0-9]{11,}/', 'e2e-ENV_ID', $value );
				$value = str_replace( "qit_network_$envId", 'qit_network_ENV_ID', $value );
			}

			if ( $port ) {
				$value = str_replace( ":$port", ':PORT', $value );
			}

			$value = preg_replace(
				'/qit-env-[a-f0-9]{32}\.json/',
				'qit-env-<hash>.json',
				$value
			);

			$value = str_replace( $repoRoot, '/repo', $value );

			// File / dir slugs with random tails
			$value = preg_replace( '/qit_scaffolded_e2e-[a-f0-9]+/', 'qit_scaffolded_e2e-NORMALISED', $value );
			$value = preg_replace( '/qit_config-qit_custom_tests_[a-f0-9]+/', 'qit_config-qit_custom_tests_NORMALISED', $value );
			$value = preg_replace( '/cache\/[a-f0-9]+/', 'cache/NORMALISED_ID/', $value );

			// Caller‑supplied replacements last
			foreach ( $extraReplacements as $from => $to ) {
				$value = str_replace( $from, $to, $value );
			}
		};

		$normalise( $data );

		// Fix scalar dynamic fields we could not detect by key (timestamps, ports)
		$data['env_info']['created_at']    = 0;
		$data['env_info']['nginx_port']    = 'PORT';
		$data['env_info']['temporary_env'] = '/tmp-normalised/temporary-env/';

		// ─────────────────────────────────────────────────────────────
		// 3. Snapshot!
		// ─────────────────────────────────────────────────────────────
		$this->assertMatchesSnapshot(
			$data,
			new \Spatie\Snapshots\Drivers\JsonDriver()
		);
	}

}
