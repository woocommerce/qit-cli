<?php

namespace QIT_CLI\LocalTests;

class PlaywrightToPuppeteerConverter {
	/**
	 * @param array<mixed> $results The Playwright result JSON decoded.
	 *
	 * @return array<mixed> The converted result to Puppeteer format.
	 */
	public function convert_pw_to_puppeteer( array $results ): array {
		$formatted_result = [
			'numFailedTestSuites'  => 0,
			'numPassedTestSuites'  => 0,
			'numPendingTestSuites' => 0,
			'numTotalTestSuites'   => 0,
			'numFailedTests'       => 0,
			'numPassedTests'       => 0,
			'numPendingTests'      => 0,
			'numTotalTests'        => 0,
			'testResults'          => [],
			'summary'              => '',
		];

		$parse_suite = function ( $suite, $parent_title = '' ) use ( &$parse_suite, &$formatted_result ): array {
			$suite_title = $parent_title ? "$parent_title > {$suite['title']}" : $suite['title'];

			$result = [
				'file'        => $suite['file'],
				'status'      => 'passed',
				'has_pending' => false,
				'tests'       => [],
			];

			$is_container_suite = true;

			if ( array_key_exists( 'specs', $suite ) ) {
				foreach ( $suite['specs'] as $spec ) {
					$this->parse_specs( $spec, $result, $formatted_result, $suite_title );
					$is_container_suite = false;
				}
			}

			$child_suite_statuses = [];
			if ( array_key_exists( 'suites', $suite ) && is_array( $suite['suites'] ) && ! empty( $suite['suites'] ) ) {
				foreach ( $suite['suites'] as $nested_suite ) {
					$child_result           = $parse_suite( $nested_suite, $suite_title );
					$child_suite_statuses[] = $child_result['status'];
				}
			}

			// Determine the suite's status based on its children
			if ( ! empty( $child_suite_statuses ) ) {
				if ( in_array( 'failed', $child_suite_statuses ) ) {
					$result['status'] = 'failed';
				} elseif ( in_array( 'pending', $child_suite_statuses ) ) {
					$result['status'] = 'pending';
				} else {
					$result['status'] = 'passed';
				}
			}

			// Only include non-container suites in the final results
			if ( ! $is_container_suite ) {
				$formatted_result['numTotalTestSuites'] += 1;

				if ( $result['status'] === 'failed' ) {
					$formatted_result['numFailedTestSuites'] += 1;
				} elseif ( $result['status'] === 'passed' ) {
					$formatted_result['numPassedTestSuites'] += 1;
				} elseif ( $result['status'] === 'pending' ) {
					$formatted_result['numPendingTestSuites'] += 1;
				}

				$formatted_result['testResults'][] = $result;
			}

			return $result;
		};

		if ( ! empty( $results['suites'] ) ) {
			foreach ( $results['suites'] as $suite ) {
				$parse_suite( $suite );
			}
		}

		$formatted_result['summary'] = sprintf(
			'Test Suites: %d skipped, %d failed, %d passed, %d total | Tests: %d skipped, %d failed, %d passed, %d total.',
			$formatted_result['numPendingTestSuites'],
			$formatted_result['numFailedTestSuites'],
			$formatted_result['numPassedTestSuites'],
			$formatted_result['numTotalTestSuites'],
			$formatted_result['numPendingTests'],
			$formatted_result['numFailedTests'],
			$formatted_result['numPassedTests'],
			$formatted_result['numTotalTests']
		);

		return $formatted_result;
	}

	/**
	 * @param array<mixed> $spec
	 *
	 * @return string
	 */
	protected function get_status( array $spec ): string {
		$status = $spec['tests'][0]['status'] ?? end( $spec['tests'][0]['results'] )['status'];

		switch ( $status ) {
			case 'skipped':
				return 'pending';
			case 'expected':
				return 'passed';
			case 'unexpected':
				return 'failed';
			case 'flaky':
				return 'passed';
			default:
				return $status;
		}
	}

	/**
	 * @param array<mixed> $spec
	 * @param array<mixed> $result
	 * @param array<mixed> $formatted_result
	 * @param string $key
	 *
	 * @return void
	 */
	protected function parse_specs( array $spec, array &$result, array &$formatted_result, string $key ): void {
		$title  = $spec['title'];
		$status = $this->get_status( $spec );

		$result['tests'][ $key ][] = [
			'title'  => $title,
			'status' => $status,
		];

		switch ( $status ) {
			case 'failed':
				$result['status']                   = 'failed';
				$formatted_result['numFailedTests'] += 1;
				break;
			case 'passed':
				$formatted_result['numPassedTests'] += 1;
				break;
			case 'pending':
				$result['has_pending']               = true;
				$formatted_result['numPendingTests'] += 1;
				break;
		}

		$formatted_result['numTotalTests'] += 1;
	}
}