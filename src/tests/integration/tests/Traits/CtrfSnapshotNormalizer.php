<?php

namespace QIT\IntegrationTests\Traits;

use Spatie\Snapshots\Drivers\JsonDriver;
use Spatie\Snapshots\MatchesSnapshots;

trait CtrfSnapshotNormalizer {
	use MatchesSnapshots;

	/**
	 * Normalize CTRF output for snapshot testing.
	 *
	 * This normalizes:
	 * - Timestamps (start, stop) to fixed values
	 * - Durations to fixed values
	 * - Random test identifiers (e.g., qit_test_XXXXXX) to placeholders
	 *
	 * @param array $ctrf_data The CTRF data to normalize
	 *
	 * @return array The normalized CTRF data
	 */
	protected function normalize_ctrf_for_snapshot( array $ctrf_data ): array {
		// Normalize summary timestamps and duration
		if ( isset( $ctrf_data['results']['summary'] ) ) {
			$summary = &$ctrf_data['results']['summary'];

			if ( isset( $summary['start'] ) ) {
				$summary['start'] = 1000000000000;
			}

			if ( isset( $summary['stop'] ) ) {
				$summary['stop'] = 1000000001000;
			}
		}

		// Normalize test data
		if ( isset( $ctrf_data['results']['tests'] ) && is_array( $ctrf_data['results']['tests'] ) ) {
			foreach ( $ctrf_data['results']['tests'] as &$test ) {
				// Normalize timestamps
				if ( isset( $test['start'] ) ) {
					$test['start'] = 1000000000;
				}

				if ( isset( $test['stop'] ) ) {
					$test['stop'] = 1000000000;
				}

				// Normalize duration
				if ( isset( $test['duration'] ) ) {
					$test['duration'] = 100;
				}

				// Normalize random test identifiers in file paths
				if ( isset( $test['filePath'] ) ) {
					$test['filePath'] = $this->normalize_test_identifiers( $test['filePath'] );
				}

				// Normalize identifiers in extra data
				if ( isset( $test['extra']['packageSlug'] ) ) {
					$test['extra']['packageSlug'] = $this->normalize_test_identifiers( $test['extra']['packageSlug'] );
				}
			}
		}

		return $ctrf_data;
	}

	/**
	 * Normalize random test identifiers in strings.
	 *
	 * Replaces patterns like qit_test_XXXXXX with qit_test_NORMALIZED
	 *
	 * @param string $string The string to normalize
	 *
	 * @return string The normalized string
	 */
	private function normalize_test_identifiers( string $string ): string {
		// Replace qit_test_[random_id] patterns with normalized placeholder
		return preg_replace( '/qit_test_[a-z0-9]+/', 'qit_test_NORMALIZED', $string );
	}

	/**
	 * Assert that CTRF data matches a snapshot after normalization.
	 *
	 * @param array $ctrf_data The CTRF data to test
	 * @param string|null $snapshot_name Optional custom snapshot name
	 */
	protected function assertCtrfMatchesSnapshot( array $ctrf_data, ?string $snapshot_name = null ): void {
		$normalized_data = $this->normalize_ctrf_for_snapshot( $ctrf_data );

		if ( $snapshot_name ) {
			$this->assertMatchesJsonSnapshot( $normalized_data, new JsonDriver(), $snapshot_name );
		} else {
			$this->assertMatchesJsonSnapshot( $normalized_data );
		}
	}
}