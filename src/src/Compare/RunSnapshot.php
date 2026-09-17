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
		'canary_profile'      => 'Canary profile',
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
	 * The annotation the ecosystem canary publishes its store shape in.
	 */
	public const PROFILE_ANNOTATION = 'canary-profile';

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
		$snapshot->context    = self::normalize_context( $run, $ctrf, $snapshot->tests );

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
	 * @param array<string,mixed> $tests Normalized tests, for context carried in annotations.
	 *
	 * @return array<string,string>
	 */
	private static function normalize_context( array $run, array $ctrf, array $tests ): array {
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

		$context['test_packages']  = self::normalize_packages( $ctrf );
		$context['canary_profile'] = self::normalize_canary_profile( $tests );

		return $context;
	}

	/**
	 * The store shape an ecosystem canary run used, read off its `canary-profile`
	 * annotation.
	 *
	 * A canary profile decides what the probes ran *against* - whether the store
	 * held legacy row shapes, where its orders were stored, whether a persistent
	 * object cache was in place. A finding present under one shape and absent under
	 * another says nothing about the build, so two runs on different profiles are
	 * as incomparable as two runs on different PHP versions, and belong in the same
	 * guard.
	 *
	 * The whole applied state is rendered, not just the profile name. A build with
	 * no HPOS classes cannot honour a profile that asks for HPOS, so two runs can
	 * agree on the name and still have run against different order storage - which
	 * is the case the name alone would hide.
	 *
	 * Empty for every run that carries no such annotation, which is every test type
	 * but this one; the renderer drops a context row that is empty on both sides,
	 * and the guard sees two equal values.
	 *
	 * @param array<string,mixed> $tests
	 */
	private static function normalize_canary_profile( array $tests ): string {
		$rendered = [];

		foreach ( $tests as $test ) {
			$annotations = isset( $test['annotations'] ) && is_array( $test['annotations'] ) ? $test['annotations'] : [];

			foreach ( $annotations as $annotation ) {
				if ( ! is_array( $annotation ) || ( $annotation['type'] ?? '' ) !== self::PROFILE_ANNOTATION ) {
					continue;
				}

				$description = isset( $annotation['description'] ) && is_scalar( $annotation['description'] )
					? (string) $annotation['description']
					: '';

				$summary = self::render_canary_profile( $description );

				if ( $summary !== '' && ! in_array( $summary, $rendered, true ) ) {
					$rendered[] = $summary;
				}
			}
		}

		// Every probe in a run publishes the same state, so more than one value here
		// means the run changed profile partway through. Joined rather than picked
		// from, because silently reporting one of them would hide that.
		sort( $rendered );

		return implode( ' | ', $rendered );
	}

	/**
	 * Render one `canary-profile` payload as a single comparable line.
	 *
	 * Deliberately lossy on the refusal reason, which is prose from WooCommerce and
	 * would put a paragraph in a table cell; that a refusal happened is the part the
	 * guard needs, and the reason is in the run's own results.
	 *
	 * A payload that is not the expected JSON is returned trimmed rather than
	 * dropped: an unreadable value that differs between two runs is still a
	 * difference worth flagging.
	 */
	private static function render_canary_profile( string $description ): string {
		$decoded = json_decode( $description, true );

		if ( ! is_array( $decoded ) ) {
			return trim( $description );
		}

		$id = isset( $decoded['id'] ) && is_scalar( $decoded['id'] ) ? (string) $decoded['id'] : '';

		if ( $id === '' ) {
			return trim( $description );
		}

		$parts = [
			'HPOS: ' . self::render_tristate( $decoded['hpos'] ?? null ),
			'sync: ' . self::render_tristate( $decoded['hpos_sync'] ?? null ),
			'object cache: ' . self::render_tristate( $decoded['object_cache'] ?? null ),
		];

		$legacy = isset( $decoded['legacy_data'] ) && is_array( $decoded['legacy_data'] ) ? $decoded['legacy_data'] : [];

		if ( ! empty( $legacy ) ) {
			$parts[] = sprintf( 'legacy shapes: %d', count( $legacy ) );
		}

		$refused = isset( $decoded['hpos_refused'] ) && is_scalar( $decoded['hpos_refused'] )
			? trim( (string) $decoded['hpos_refused'] )
			: '';

		if ( $refused !== '' ) {
			$parts[] = 'storage change refused';
		}

		return sprintf( '%s (%s)', $id, implode( ', ', $parts ) );
	}

	/**
	 * `on`, `off`, or `unknown` for a dimension the build could not report.
	 *
	 * `unknown` is not `off`: a build with no HPOS classes cannot say where orders
	 * are stored, and reporting that as "off" would make it compare equal to a run
	 * that genuinely had HPOS disabled.
	 *
	 * @param mixed $value
	 */
	private static function render_tristate( $value ): string {
		if ( is_null( $value ) ) {
			return 'unknown';
		}

		return $value ? 'on' : 'off';
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
