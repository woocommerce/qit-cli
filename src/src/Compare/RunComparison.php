<?php

namespace QIT_CLI\Compare;

/**
 * Diffs two finished test runs on the parts of CTRF that survive the round trip
 * through the Manager: test names, statuses and annotations.
 *
 * The diff needs no domain knowledge, which is what makes it work for every test
 * type that reports in CTRF (activation, compatibility, ecosystem, woo-api, woo-e2e).
 */
class RunComparison {
	private RunSnapshot $a;

	private RunSnapshot $b;

	/**
	 * Memoized test buckets, so that to_array() and has_regressions() do not each
	 * walk both test lists.
	 *
	 * @var array<string,mixed>|null
	 */
	private ?array $tests = null;

	public function __construct( RunSnapshot $a, RunSnapshot $b ) {
		$this->a = $a;
		$this->b = $b;
	}

	/**
	 * The full comparison, as a plain array. This is what `--format json` prints, and
	 * what the human renderer reads, so both formats always report the same thing.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$tests       = $this->compare_tests();
		$annotations = $this->compare_annotations();

		return [
			'runs'        => [
				'a' => $this->describe_run( $this->a ),
				'b' => $this->describe_run( $this->b ),
			],
			'guard'       => $this->guard(),
			'summary'     => $this->compare_summary(),
			'tests'       => $tests,
			'annotations' => $annotations,
			'totals'      => [
				'introduced'     => count( $tests['introduced'] ),
				'resolved'       => count( $tests['resolved'] ),
				'still_failing'  => count( $tests['still_failing'] ),
				'status_changed' => count( $tests['status_changed'] ),
				'added'          => count( $tests['added'] ),
				'removed'        => count( $tests['removed'] ),
				'unchanged'      => $tests['unchanged_count'],
				'annotations'    => count( $annotations['added'] ) + count( $annotations['removed'] ),
			],
		];
	}

	/**
	 * True when run B introduced failures that run A did not have, either as a
	 * regression on a shared test or as a new test that fails.
	 */
	public function has_regressions(): bool {
		$tests = $this->compare_tests();

		if ( count( $tests['introduced'] ) > 0 ) {
			return true;
		}

		foreach ( $tests['added'] as $test ) {
			if ( $test['status'] === 'failed' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array<string,mixed>
	 */
	private function describe_run( RunSnapshot $run ): array {
		return [
			'id'         => $run->id,
			'status'     => $run->status,
			'created_at' => $run->created_at,
			'result_url' => $run->result_url,
			'context'    => $run->context,
		];
	}

	/**
	 * Flag when the two runs differ in more than the one variable under test.
	 *
	 * Comparing a run against another that changed WordPress *and* PHP *and* the
	 * package version cannot attribute anything to any of them, so rather than
	 * silently diffing incomparable runs we say what moved and let the caller decide.
	 *
	 * @return array{comparable:bool,differences:array<int,array<string,string>>,warnings:array<int,string>}
	 */
	private function guard(): array {
		$differences = [];

		foreach ( RunSnapshot::CONTEXT_LABELS as $field => $label ) {
			$a_value = $this->a->context[ $field ] ?? '';
			$b_value = $this->b->context[ $field ] ?? '';

			if ( $a_value === $b_value ) {
				continue;
			}

			$differences[] = [
				'field' => $field,
				'label' => $label,
				'a'     => $a_value,
				'b'     => $b_value,
			];
		}

		$warnings = [];

		if ( $this->a->context['test_type'] !== $this->b->context['test_type'] ) {
			$warnings[] = sprintf(
				'The runs are of different test types (%s vs %s), so their tests are not the same population.',
				$this->a->context['test_type'] !== '' ? $this->a->context['test_type'] : 'unknown',
				$this->b->context['test_type'] !== '' ? $this->b->context['test_type'] : 'unknown'
			);
		}

		if ( count( $differences ) > 1 ) {
			$labels     = array_column( $differences, 'label' );
			$warnings[] = sprintf(
				'The runs differ in %d dimensions (%s). A difference in results cannot be attributed to any single one of them.',
				count( $differences ),
				implode( ', ', $labels )
			);
		}

		return [
			'comparable'  => empty( $warnings ),
			'differences' => $differences,
			'warnings'    => $warnings,
		];
	}

	/**
	 * @return array<string,array<string,int>>
	 */
	private function compare_summary(): array {
		$delta = [];

		foreach ( RunSnapshot::SUMMARY_KEYS as $key ) {
			$delta[ $key ] = ( $this->b->summary[ $key ] ?? 0 ) - ( $this->a->summary[ $key ] ?? 0 );
		}

		return [
			'a'     => $this->a->summary,
			'b'     => $this->b->summary,
			'delta' => $delta,
		];
	}

	/**
	 * Bucket every test into exactly one category.
	 *
	 * Tests present in both runs are bucketed by how their status moved; tests present
	 * in only one run are reported separately, with their status, so that a failing
	 * test that simply disappeared does not read as "resolved".
	 *
	 * @return array<string,mixed>
	 */
	private function compare_tests(): array {
		if ( ! is_null( $this->tests ) ) {
			return $this->tests;
		}

		$introduced     = [];
		$resolved       = [];
		$still_failing  = [];
		$status_changed = [];
		$added          = [];
		$removed        = [];
		$unchanged      = 0;

		foreach ( $this->a->tests as $key => $a_test ) {
			if ( ! isset( $this->b->tests[ $key ] ) ) {
				$removed[] = $this->describe_test( $a_test );
				continue;
			}

			$b_test = $this->b->tests[ $key ];
			$entry  = $this->describe_transition( $a_test, $b_test );

			if ( $a_test['status'] === $b_test['status'] ) {
				if ( $b_test['status'] === 'failed' ) {
					$still_failing[] = $entry;
				} else {
					++$unchanged;
				}
				continue;
			}

			if ( $b_test['status'] === 'failed' ) {
				$introduced[] = $entry;
			} elseif ( $a_test['status'] === 'failed' ) {
				$resolved[] = $entry;
			} else {
				$status_changed[] = $entry;
			}
		}

		foreach ( $this->b->tests as $key => $b_test ) {
			if ( ! isset( $this->a->tests[ $key ] ) ) {
				$added[] = $this->describe_test( $b_test );
			}
		}

		$this->tests = [
			'introduced'      => $introduced,
			'resolved'        => $resolved,
			'still_failing'   => $still_failing,
			'status_changed'  => $status_changed,
			'added'           => $added,
			'removed'         => $removed,
			'unchanged_count' => $unchanged,
		];

		return $this->tests;
	}

	/**
	 * @param array<string,mixed> $test
	 *
	 * @return array<string,mixed>
	 */
	private function describe_test( array $test ): array {
		return [
			'key'      => $test['key'],
			'name'     => $test['name'],
			'suite'    => $test['suite'],
			'status'   => $test['status'],
			'duration' => $test['duration'],
			'message'  => $test['message'],
		];
	}

	/**
	 * @param array<string,mixed> $a_test
	 * @param array<string,mixed> $b_test
	 *
	 * @return array<string,mixed>
	 */
	private function describe_transition( array $a_test, array $b_test ): array {
		return [
			'key'      => $b_test['key'],
			'name'     => $b_test['name'],
			'suite'    => $b_test['suite'],
			'status'   => [
				'a' => $a_test['status'],
				'b' => $b_test['status'],
			],
			'duration' => [
				'a' => $a_test['duration'],
				'b' => $b_test['duration'],
			],
			'message'  => $b_test['message'],
		];
	}

	/**
	 * Diff `extra.annotations` for every test, across the union of both runs.
	 *
	 * Packages that emit structured, already-normalised data as annotations get
	 * compared for free here, without this command knowing what the data means.
	 *
	 * @return array{added:array<int,array<string,string>>,removed:array<int,array<string,string>>}
	 */
	private function compare_annotations(): array {
		$added   = [];
		$removed = [];

		$keys = array_keys( $this->a->tests + $this->b->tests );

		foreach ( $keys as $key ) {
			$a_annotations = $this->a->tests[ $key ]['annotations'] ?? [];
			$b_annotations = $this->b->tests[ $key ]['annotations'] ?? [];

			foreach ( array_diff_key( $b_annotations, $a_annotations ) as $annotation ) {
				$added[] = [
					'test'        => (string) $key,
					'type'        => $annotation['type'],
					'description' => $annotation['description'],
				];
			}

			foreach ( array_diff_key( $a_annotations, $b_annotations ) as $annotation ) {
				$removed[] = [
					'test'        => (string) $key,
					'type'        => $annotation['type'],
					'description' => $annotation['description'],
				];
			}
		}

		return [
			'added'   => $added,
			'removed' => $removed,
		];
	}
}
