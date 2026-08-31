<?php

namespace QIT_CLI\Compare;

/**
 * A normalized, comparable view of one finished test run.
 *
 * Anything a comparison needs has to survive the round trip through the Manager,
 * which means it has to live either in the run record or in the CTRF report.
 *
 * Notably, CTRF `attachments` do NOT survive: they carry filesystem paths from the
 * machine that ran the tests, and those paths are gone once the job ends. Test
 * packages that want their data compared must emit it as CTRF `extra.annotations`.
 */
class RunSnapshot {
	/**
	 * The context fields used both for display and for the comparability guard,
	 * mapped to the human label used when rendering them.
	 */
	public const CONTEXT_LABELS = [
		'test_type'           => 'Test type',
		'wordpress_version'   => 'WordPress',
		'woocommerce_version' => 'WooCommerce',
		'php_version'         => 'PHP',
		'extension_set'       => 'Extension set',
		'sut'                 => 'Extension',
		'sut_version'         => 'Extension version',
		'test_packages'       => 'Test packages',
	];

	public string $id = '';

	public string $status = '';

	public string $created_at = '';

	public string $result_url = '';

	/**
	 * Environment/context dimensions, keyed by the CONTEXT_LABELS keys.
	 *
	 * @var array<string,string>
	 */
	public array $context = [];

	/**
	 * CTRF summary counters (tests, passed, failed, skipped, pending, other).
	 *
	 * @var array<string,int>
	 */
	public array $summary = [];

	/**
	 * Normalized tests keyed by their comparison key.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	public array $tests = [];

	/**
	 * The CTRF summary counter keys, in display order.
	 */
	public const SUMMARY_KEYS = [ 'tests', 'passed', 'failed', 'skipped', 'pending', 'other' ];

	/**
	 * Build a snapshot from a Manager test run record.
	 *
	 * @param string              $run_id  The ID the run was requested by.
	 * @param array<string,mixed> $run     The Manager test run record.
	 *
	 * @throws \RuntimeException If the run carries no usable CTRF results.
	 */
	public static function from_manager_run( string $run_id, array $run ): self {
		$ctrf = self::extract_ctrf( $run );

		if ( is_null( $ctrf ) ) {
			throw new \RuntimeException( sprintf(
				'Test run %s has no CTRF results to compare. Only test types that report in CTRF format can be compared (activation, compatibility, woo-api, woo-e2e), and the run must have finished.',
				$run_id
			) );
		}

		$snapshot             = new self();
		$snapshot->id         = (string) ( $run['test_run_id'] ?? $run_id );
		$snapshot->status     = (string) ( $run['status'] ?? '' );
		$snapshot->created_at = (string) ( $run['created_at'] ?? '' );
		$snapshot->result_url = (string) ( $run['test_results_manager_url'] ?? '' );
		$snapshot->tests      = self::normalize_tests( $ctrf['tests'] ?? [] );
		$snapshot->summary    = self::normalize_summary( $ctrf['summary'] ?? [], $snapshot->tests );
		$snapshot->context    = self::normalize_context( $run, $ctrf );

		return $snapshot;
	}

	/**
	 * Pull the CTRF `results` object out of a Manager run record.
	 *
	 * `qit get <id> --json-results` returns the CTRF verbatim, so the shape here is
	 * `{ reportFormat, results: { summary, tests, extra } }`. Some reporters omit the
	 * `results` wrapper, so an already-unwrapped payload is accepted too.
	 *
	 * @param array<string,mixed> $run
	 *
	 * @return array<string,mixed>|null
	 */
	private static function extract_ctrf( array $run ): ?array {
		if ( empty( $run['ctrf_json'] ) ) {
			return null;
		}

		$decoded = $run['ctrf_json'];

		if ( is_string( $decoded ) ) {
			$decoded = json_decode( $decoded, true );
		}

		if ( ! is_array( $decoded ) ) {
			return null;
		}

		if ( isset( $decoded['results'] ) && is_array( $decoded['results'] ) ) {
			$decoded = $decoded['results'];
		}

		if ( ! isset( $decoded['tests'] ) || ! is_array( $decoded['tests'] ) ) {
			return null;
		}

		return $decoded;
	}

	/**
	 * Normalize the CTRF test list into a map keyed by a stable comparison key.
	 *
	 * CTRF does not guarantee unique test names, so colliding keys get a `#n` suffix
	 * in the order they appear. That keeps a run with N identical names comparable
	 * against another run with the same N names, instead of collapsing them to one.
	 *
	 * @param array<mixed> $tests
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function normalize_tests( array $tests ): array {
		$normalized = [];

		foreach ( $tests as $test ) {
			if ( ! is_array( $test ) ) {
				continue;
			}

			$name  = isset( $test['name'] ) && is_scalar( $test['name'] ) ? (string) $test['name'] : '';
			$suite = isset( $test['suite'] ) && is_scalar( $test['suite'] ) ? (string) $test['suite'] : '';

			if ( $name === '' ) {
				continue;
			}

			$key      = $suite === '' ? $name : $suite . ' :: ' . $name;
			$base_key = $key;
			$dupe     = 1;

			while ( isset( $normalized[ $key ] ) ) {
				++$dupe;
				$key = $base_key . ' #' . $dupe;
			}

			$normalized[ $key ] = [
				'key'         => $key,
				'name'        => $name,
				'suite'       => $suite,
				'status'      => self::normalize_status( $test['status'] ?? '' ),
				'duration'    => isset( $test['duration'] ) && is_numeric( $test['duration'] ) ? (int) $test['duration'] : null,
				'message'     => isset( $test['message'] ) && is_scalar( $test['message'] ) ? (string) $test['message'] : '',
				'annotations' => self::normalize_annotations( $test ),
			];
		}

		return $normalized;
	}

	/**
	 * CTRF defines five statuses. Anything else is reported as "other" rather than
	 * silently treated as a distinct status that would show up as a spurious change.
	 *
	 * @param mixed $status
	 */
	private static function normalize_status( $status ): string {
		$status = is_scalar( $status ) ? strtolower( trim( (string) $status ) ) : '';

		if ( in_array( $status, [ 'passed', 'failed', 'skipped', 'pending', 'other' ], true ) ) {
			return $status;
		}

		return 'other';
	}

	/**
	 * Read `extra.annotations` off a CTRF test, keyed by type + description so that
	 * two runs can be diffed on annotation identity.
	 *
	 * @param array<string,mixed> $test
	 *
	 * @return array<string,array{type:string,description:string}>
	 */
	private static function normalize_annotations( array $test ): array {
		$annotations = $test['extra']['annotations'] ?? [];

		if ( ! is_array( $annotations ) ) {
			return [];
		}

		$normalized = [];

		foreach ( $annotations as $annotation ) {
			if ( is_string( $annotation ) ) {
				$annotation = [ 'description' => $annotation ];
			}

			if ( ! is_array( $annotation ) ) {
				continue;
			}

			$type        = isset( $annotation['type'] ) && is_scalar( $annotation['type'] ) ? (string) $annotation['type'] : '';
			$description = isset( $annotation['description'] ) && is_scalar( $annotation['description'] ) ? (string) $annotation['description'] : '';

			if ( $type === '' && $description === '' ) {
				continue;
			}

			$normalized[ $type . "\0" . $description ] = [
				'type'        => $type,
				'description' => $description,
			];
		}

		return $normalized;
	}

	/**
	 * Prefer the summary the reporter emitted, filling any missing counter from the
	 * test list so both sides of a comparison always have the same keys.
	 *
	 * @param mixed                             $summary
	 * @param array<string,array<string,mixed>> $tests
	 *
	 * @return array<string,int>
	 */
	private static function normalize_summary( $summary, array $tests ): array {
		$computed = array_fill_keys( self::SUMMARY_KEYS, 0 );

		foreach ( $tests as $test ) {
			++$computed['tests'];
			if ( isset( $computed[ $test['status'] ] ) ) {
				++$computed[ $test['status'] ];
			}
		}

		if ( ! is_array( $summary ) ) {
			$summary = [];
		}

		$normalized = [];

		foreach ( self::SUMMARY_KEYS as $key ) {
			$normalized[ $key ] = isset( $summary[ $key ] ) && is_numeric( $summary[ $key ] )
				? (int) $summary[ $key ]
				: $computed[ $key ];
		}

		return $normalized;
	}

	/**
	 * Collect the dimensions the comparability guard checks.
	 *
	 * @param array<string,mixed> $run
	 * @param array<string,mixed> $ctrf
	 *
	 * @return array<string,string>
	 */
	private static function normalize_context( array $run, array $ctrf ): array {
		$context = [];

		foreach ( array_keys( self::CONTEXT_LABELS ) as $field ) {
			$context[ $field ] = '';
		}

		foreach ( [ 'test_type', 'wordpress_version', 'woocommerce_version', 'php_version', 'extension_set' ] as $field ) {
			if ( isset( $run[ $field ] ) && is_scalar( $run[ $field ] ) ) {
				$context[ $field ] = (string) $run[ $field ];
			}
		}

		if ( isset( $run['woo_extension']['name'] ) && is_scalar( $run['woo_extension']['name'] ) ) {
			$context['sut'] = (string) $run['woo_extension']['name'];
		}

		if ( isset( $run['version'] ) && is_scalar( $run['version'] ) ) {
			$context['sut_version'] = (string) $run['version'];
		}

		$context['test_packages'] = self::normalize_packages( $ctrf );

		return $context;
	}

	/**
	 * Render `extra.qitPackageMetadata.packages` as a stable, sorted string so that a
	 * package version bump shows up as a single context difference.
	 *
	 * @param array<string,mixed> $ctrf
	 */
	private static function normalize_packages( array $ctrf ): string {
		$packages = $ctrf['extra']['qitPackageMetadata']['packages'] ?? [];

		if ( ! is_array( $packages ) ) {
			return '';
		}

		$rendered = [];

		foreach ( $packages as $package ) {
			if ( is_string( $package ) ) {
				$rendered[] = $package;
				continue;
			}

			if ( ! is_array( $package ) ) {
				continue;
			}

			$id = $package['packageId'] ?? ( $package['id'] ?? '' );

			if ( ! is_scalar( $id ) || (string) $id === '' ) {
				continue;
			}

			$version    = isset( $package['version'] ) && is_scalar( $package['version'] ) ? (string) $package['version'] : '';
			$rendered[] = $version === '' ? (string) $id : $id . '@' . $version;
		}

		sort( $rendered );

		return implode( ', ', $rendered );
	}
}
