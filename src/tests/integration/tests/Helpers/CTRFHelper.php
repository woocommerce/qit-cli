<?php

namespace QIT\IntegrationTests\Helpers;

/**
 * Helper class for generating valid CTRF (Common Test Result Format) reports in tests.
 */
class CTRFHelper {
	/**
	 * Generate a valid CTRF report with all required fields.
	 *
	 * @param array<string, mixed> $options Custom options to override defaults
	 * @return array<string, mixed> Valid CTRF report structure
	 */
	public static function generate_valid_ctrf( array $options = [] ): array {
		$defaults = [
			'tool'        => [
				'name' => 'test-package',
			],
			'total_tests' => 1,
			'passed'      => 1,
			'failed'      => 0,
			'skipped'     => 0,
			'pending'     => 0,
			'other'       => 0,
			'tests'       => [
				[
					'name'     => 'test',
					'status'   => 'passed',
					'duration' => 100,
				],
			],
		];

		$options = array_merge( $defaults, $options );

		// If tool is provided as string, convert to object
		if ( isset( $options['tool'] ) && is_string( $options['tool'] ) ) {
			$options['tool'] = [ 'name' => $options['tool'] ];
		}

		// Calculate summary from tests if not explicitly provided
		if ( ! isset( $options['recalculate_summary'] ) || $options['recalculate_summary'] ) {
			$summary_counts = [
				'passed'  => 0,
				'failed'  => 0,
				'skipped' => 0,
				'pending' => 0,
				'other'   => 0,
			];

			foreach ( $options['tests'] as $test ) {
				$status = $test['status'] ?? 'other';
				if ( isset( $summary_counts[ $status ] ) ) {
					$summary_counts[ $status ]++;
				} else {
					$summary_counts['other']++;
				}
			}

			$options['passed']  = $summary_counts['passed'];
			$options['failed']  = $summary_counts['failed'];
			$options['skipped'] = $summary_counts['skipped'];
			$options['pending'] = $summary_counts['pending'];
			$options['other']   = $summary_counts['other'];
			$options['total_tests'] = count( $options['tests'] );
		}

		$start_time = $options['start'] ?? time() * 1000;
		$stop_time  = $options['stop'] ?? ( $start_time + 1000 );

		return [
			'results' => [
				'tool' => $options['tool'],
				'summary' => [
					'tests'   => $options['total_tests'],
					'passed'  => $options['passed'],
					'failed'  => $options['failed'],
					'skipped' => $options['skipped'],
					'pending' => $options['pending'],
					'other'   => $options['other'],
					'start'   => $start_time,
					'stop'    => $stop_time,
				],
				'tests' => array_map( function ( $test ) {
					// Ensure each test has all required fields
					return array_merge( [
						'name'     => 'unnamed test',
						'status'   => 'passed',
						'duration' => 100,
					], $test );
				}, $options['tests'] ),
			],
		];
	}

	/**
	 * Generate JSON string for a valid CTRF report.
	 *
	 * @param array<string, mixed> $options Custom options to override defaults
	 * @return string JSON-encoded CTRF report
	 */
	public static function generate_valid_ctrf_json( array $options = [] ): string {
		return json_encode( self::generate_valid_ctrf( $options ), JSON_PRETTY_PRINT );
	}

	/**
	 * Create a simple passing CTRF report.
	 *
	 * @param int $num_tests Number of passing tests
	 * @return string JSON-encoded CTRF report
	 */
	public static function create_passing_report( int $num_tests = 1 ): string {
		$tests = [];
		for ( $i = 1; $i <= $num_tests; $i++ ) {
			$tests[] = [
				'name'     => "Test $i",
				'status'   => 'passed',
				'duration' => rand( 50, 500 ),
			];
		}

		return self::generate_valid_ctrf_json( [
			'tests' => $tests,
		] );
	}

	/**
	 * Create a mixed result CTRF report.
	 *
	 * @param int $passed Number of passed tests
	 * @param int $failed Number of failed tests
	 * @param int $skipped Number of skipped tests
	 * @return string JSON-encoded CTRF report
	 */
	public static function create_mixed_report( int $passed = 1, int $failed = 0, int $skipped = 0 ): string {
		$tests = [];
		
		for ( $i = 1; $i <= $passed; $i++ ) {
			$tests[] = [
				'name'     => "Passed Test $i",
				'status'   => 'passed',
				'duration' => rand( 50, 500 ),
			];
		}
		
		for ( $i = 1; $i <= $failed; $i++ ) {
			$tests[] = [
				'name'     => "Failed Test $i",
				'status'   => 'failed',
				'duration' => rand( 50, 500 ),
			];
		}
		
		for ( $i = 1; $i <= $skipped; $i++ ) {
			$tests[] = [
				'name'     => "Skipped Test $i",
				'status'   => 'skipped',
				'duration' => 0,
			];
		}

		return self::generate_valid_ctrf_json( [
			'tests' => $tests,
		] );
	}
}