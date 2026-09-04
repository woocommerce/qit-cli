<?php

namespace QIT_CLI\Compare;

/**
 * The part of a comparison that a generic annotation diff gets wrong.
 *
 * The `woocommerce/ecosystem-canary` package publishes what each probe saw as
 * `canary-finding` annotations, already normalised at record time, so a diff of
 * annotation strings compares the right things. Two properties of that data make
 * a plain per-test diff report things that did not happen:
 *
 * 1. A finding is not tied to the probe that recorded it. A fatal raised by a
 *    loopback request or a scheduled action lands under whichever probe happened
 *    to be running, and that is not stable between runs. Diffed per test, one
 *    relocated finding reads as a regression *and* a fix, and both are wrong. So
 *    every finding carries two identities: a `key` scoped to its probe, and a
 *    `signature` for the problem on its own. A signature that left one probe and
 *    appeared under another moved; it was not introduced, and the baseline's copy
 *    was not resolved.
 *
 * 2. An absent finding does not always mean a fixed one. Each probe publishes a
 *    `canary-probe-state`, and only `complete` means the probe ran to the end of
 *    its scenario. A `truncated` probe stopped early after recording why, and an
 *    `incomplete` one broke and published nothing: in both cases the checks after
 *    the stopping point were never made, so a baseline finding the other run
 *    lacks was never looked for. Reporting it resolved invents an improvement,
 *    which is the failure mode this whole comparison exists to avoid.
 *
 * Nothing here knows anything about WooCommerce. Normalisation happens in the
 * package, at record time, so this only ever compares already-normalised strings
 * against each other and reads two annotation types it does not interpret.
 */
class CanaryComparison {
	/**
	 * The annotation carrying one finding, as a JSON object with at least a `key`
	 * and a `signature`.
	 */
	public const FINDING_ANNOTATION = 'canary-finding';

	/**
	 * The annotation carrying how far the probe got.
	 */
	public const PROBE_STATE_ANNOTATION = 'canary-probe-state';

	/**
	 * The annotation carrying the probe's stable id.
	 *
	 * A CTRF test key is suite plus name, which for a probe means its filename and
	 * its human-readable description. Both are prose that can be reworded without
	 * the probe changing, and a reworded description would make a completed probe
	 * look absent - turning resolved findings into unverified ones and inventing
	 * probe state changes. The package publishes an id built for this, validated
	 * against a pattern at declaration time, so that is the identity used here.
	 */
	public const PROBE_ID_ANNOTATION = 'probe-id';

	/**
	 * The annotation types this class accounts for, and which the generic
	 * annotation diff therefore leaves alone: reporting a moved finding as one
	 * annotation added and another removed is exactly what this exists to prevent.
	 *
	 * `probe-id` is in here because it is read as the probe's identity. Rewording a
	 * probe changes its CTRF test key, so the generic diff would report the
	 * unchanged id as removed from one test and added to another, contradicting the
	 * canary section that has just recognised it as the same probe.
	 */
	public const HANDLED_ANNOTATIONS = [ self::FINDING_ANNOTATION, self::PROBE_STATE_ANNOTATION, self::PROBE_ID_ANNOTATION ];

	/**
	 * The types that make a run a canary run. `probe-id` is deliberately absent: it
	 * is a generic enough name that another package could emit it, and one stray
	 * annotation should not conjure a canary section onto an unrelated comparison.
	 */
	private const CANARY_ANNOTATIONS = [ self::FINDING_ANNOTATION, self::PROBE_STATE_ANNOTATION ];

	/**
	 * The one probe state that makes an absent finding mean something.
	 */
	public const STATE_COMPLETE = 'complete';

	/**
	 * A probe that has no state at all is treated as one that did not finish. The
	 * package publishes a state on every probe that published anything, so a
	 * missing one means the probe never got that far - or is not in this run.
	 */
	public const STATE_UNKNOWN = 'unknown';

	/**
	 * Probe states, from the most to the least of the scenario covered. A probe
	 * that somehow published two states is read as the least covered of them,
	 * because that is the only reading that cannot invent an improvement.
	 */
	private const STATE_RANK = [
		self::STATE_COMPLETE => 0,
		'truncated'          => 1,
		'incomplete'         => 2,
		self::STATE_UNKNOWN  => 3,
	];

	/**
	 * Findings in run A, keyed by their finding key.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	private array $a_findings;

	/**
	 * @var array<string,array<string,mixed>>
	 */
	private array $b_findings;

	/**
	 * Probe state in run A, keyed by CTRF test key.
	 *
	 * @var array<string,string>
	 */
	private array $a_states;

	/**
	 * @var array<string,string>
	 */
	private array $b_states;

	/**
	 * Findings whose annotation could not be read, per run.
	 *
	 * Counted rather than dropped: a finding that silently disappears from one
	 * side of the comparison reads as an improvement.
	 *
	 * @var array<string,int>
	 */
	private array $malformed;

	public function __construct( RunSnapshot $a, RunSnapshot $b ) {
		/*
		 * One keying scheme for both runs, decided here rather than per run.
		 *
		 * Keying by published id where a run has one and by test key where it does
		 * not would key the same probe two different ways across a comparison: the
		 * probe would look absent from the candidate, its findings would go to
		 * unverified, and two probe state changes would appear that nobody caused.
		 * Where either run is missing an id, both fall back to the test key, which
		 * at least matches itself.
		 */
		$by_id = self::publishes_probe_ids( $a ) && self::publishes_probe_ids( $b );

		$this->a_findings = self::findings( $a, $by_id );
		$this->b_findings = self::findings( $b, $by_id );
		$this->a_states   = self::probe_states( $a, $by_id );
		$this->b_states   = self::probe_states( $b, $by_id );
		$this->malformed  = [
			'a' => self::malformed_findings( $a ),
			'b' => self::malformed_findings( $b ),
		];
	}

	/**
	 * True when either run carries canary data, and a canary-aware comparison is
	 * therefore something other than an empty section.
	 */
	public static function present( RunSnapshot $a, RunSnapshot $b ): bool {
		return self::carries_canary_data( $a ) || self::carries_canary_data( $b );
	}

	private static function carries_canary_data( RunSnapshot $run ): bool {
		foreach ( $run->tests as $test ) {
			foreach ( $test['annotations'] as $annotation ) {
				if ( in_array( $annotation['type'], self::CANARY_ANNOTATIONS, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * The canary comparison, as a plain array, which is what `--format json` prints
	 * and what the human renderer reads.
	 *
	 * @return array<string,mixed>
	 */
	public function to_array(): array {
		$a_keys = array_keys( $this->a_findings );
		$b_keys = array_keys( $this->b_findings );

		$only_a = $this->only_a();
		$only_b = $this->only_b();

		/*
		 * A signature moved when it left at least one probe and turned up under at
		 * least one other. Requiring both directions matters: a signature that is
		 * still recorded under its original probe in both runs and *also* appears
		 * under a new one in B has not moved anywhere, it happened somewhere new,
		 * and calling that a move would hide it.
		 */
		$moved_signatures = $this->moved_signatures();

		$moved        = $this->compare_moved( $moved_signatures, $only_a, $only_b );
		$introduced   = $this->compare_introduced( $only_b, $moved_signatures );
		$verdicts     = $this->compare_absent( $only_a, $moved_signatures );
		$pre_existing = $this->compare_pre_existing( $a_keys, $b_keys );

		$findings = [
			'introduced'   => $introduced,
			'resolved'     => $verdicts['resolved'],
			'moved'        => $moved,
			'unverified'   => $verdicts['unverified'],
			'pre_existing' => $pre_existing,
		];

		return [
			'findings' => $findings,
			'probes'   => [
				'a'       => $this->a_states,
				'b'       => $this->b_states,
				'changed' => $this->changed_probe_states(),
			],
			'warnings' => $this->warnings( $findings ),
			'totals'   => [
				'introduced'   => count( $findings['introduced'] ),
				'resolved'     => count( $findings['resolved'] ),
				'moved'        => count( $findings['moved'] ),
				'unverified'   => count( $findings['unverified'] ),
				'pre_existing' => count( $findings['pre_existing'] ),
			],
		];
	}

	/**
	 * How many findings the candidate introduced, for a caller that wants the
	 * verdict without the whole report.
	 */
	public function introduced_count(): int {
		return count( $this->compare_introduced( $this->only_b(), $this->moved_signatures() ) );
	}

	/**
	 * The CTRF tests in run B whose findings are all accounted for as moved or
	 * pre-existing, so a status change on them says nothing new.
	 *
	 * A probe fails when it records anything at all, so a finding that only changed
	 * probes flips two results: the probe that lost it passes, the probe that
	 * gained it fails. Read as ordinary test statuses that is a fix and a
	 * regression, which is the double report the moved bucket exists to prevent -
	 * and it would still reach the exit code through the generic verdict.
	 *
	 * A test that failed while recording nothing is deliberately not listed. Its
	 * failure is the harness rather than an observation, and that is a real
	 * regression whatever the findings say.
	 *
	 * @return array<string,bool>
	 */
	public function tests_explained_by_moves(): array {
		$introduced = [];

		foreach ( $this->compare_introduced( $this->only_b(), $this->moved_signatures() ) as $finding ) {
			$introduced[ $finding['test'] ] = true;
		}

		$explained = [];

		foreach ( $this->b_findings as $finding ) {
			if ( isset( $introduced[ $finding['test'] ] ) ) {
				continue;
			}

			$explained[ $finding['test'] ] = true;
		}

		return $explained;
	}

	/**
	 * @return array<int,string>
	 */
	private function only_a(): array {
		return array_values( array_diff( array_keys( $this->a_findings ), array_keys( $this->b_findings ) ) );
	}

	/**
	 * @return array<int,string>
	 */
	private function only_b(): array {
		return array_values( array_diff( array_keys( $this->b_findings ), array_keys( $this->a_findings ) ) );
	}

	/**
	 * @return array<string,bool>
	 */
	private function moved_signatures(): array {
		return array_intersect_key(
			self::signatures_of( $this->only_a(), $this->a_findings ),
			self::signatures_of( $this->only_b(), $this->b_findings )
		);
	}

	/**
	 * One entry per signature that changed probes, listing where it went from and to.
	 *
	 * Grouped by signature rather than paired up key by key, because a signature
	 * that left two probes and arrived under one has no honest pairing - and the
	 * point of the bucket is that none of these keys is a regression or a fix.
	 *
	 * @param array<string,bool> $moved_signatures
	 * @param array<int,string>  $only_a
	 * @param array<int,string>  $only_b
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function compare_moved( array $moved_signatures, array $only_a, array $only_b ): array {
		$moved = [];

		foreach ( array_keys( $moved_signatures ) as $signature ) {
			$moved[ $signature ] = [
				'signature' => $signature,
				'type'      => '',
				'from'      => [],
				'to'        => [],
			];
		}

		foreach ( [
			'from' => [ $only_a, $this->a_findings ],
			'to'   => [ $only_b, $this->b_findings ],
		] as $side => $source ) {
			list( $keys, $findings ) = $source;

			foreach ( $keys as $key ) {
				$finding = $findings[ $key ];

				if ( ! isset( $moved[ $finding['signature'] ] ) ) {
					continue;
				}

				$moved[ $finding['signature'] ]['type']    = $finding['type'];
				$moved[ $finding['signature'] ][ $side ][] = [
					'key'     => $finding['key'],
					'probe'   => $finding['probe'],
					'test'    => $finding['test'],
					'surface' => $finding['surface'],
				];
			}
		}

		ksort( $moved );

		return array_values( $moved );
	}

	/**
	 * Findings run B has that run A does not, and that did not simply move.
	 *
	 * The baseline's probe state rides along rather than gating the bucket. An
	 * absence on the baseline side is as uninformative as one on the candidate
	 * side, but the two errors are not symmetric: a finding wrongly called
	 * resolved is an improvement that never happened, while a finding wrongly
	 * called introduced is one more advisory line for a human to dismiss. This
	 * package gates nothing, so the safe direction is to report and to say what
	 * the baseline probe did.
	 *
	 * @param array<int,string>  $only_b
	 * @param array<string,bool> $moved_signatures
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function compare_introduced( array $only_b, array $moved_signatures ): array {
		$introduced = [];

		foreach ( $only_b as $key ) {
			$finding = $this->b_findings[ $key ];

			if ( isset( $moved_signatures[ $finding['signature'] ] ) ) {
				continue;
			}

			$introduced[] = $this->describe_finding( $finding, [
				'baseline_probe_state' => $this->state_of( $this->a_states, $finding['probe'] ),
			] );
		}

		return self::sorted( $introduced );
	}

	/**
	 * Findings run A has that run B does not, split by whether run B looked.
	 *
	 * Two conditions, not one. The obvious read is the probe that recorded the
	 * finding in the baseline, since its later steps are what would have found it
	 * again. But a finding is not tied to the probe that recorded it - that is the
	 * premise of the moved bucket - so a fatal the candidate's probe did not see
	 * may simply have landed during another probe's window, and if that probe did
	 * not finish it published nothing. Gating on the recording probe alone would
	 * call that a fix.
	 *
	 * So the whole candidate run has to be conclusive. That is deliberately
	 * stricter than it needs to be for a finding a probe measures inside its own
	 * workflow, where another probe's truncation is irrelevant. Telling those apart
	 * means deciding which finding types can travel, and the cost of guessing that
	 * wrong is a false resolution, which is the one error this comparison exists to
	 * avoid. A run where every probe completed - the healthy case, and the case in
	 * the reproduction - is unaffected.
	 *
	 * @param array<int,string>  $only_a
	 * @param array<string,bool> $moved_signatures
	 *
	 * @return array{resolved:array<int,array<string,mixed>>,unverified:array<int,array<string,mixed>>}
	 */
	private function compare_absent( array $only_a, array $moved_signatures ): array {
		$resolved   = [];
		$unverified = [];

		foreach ( $only_a as $key ) {
			$finding = $this->a_findings[ $key ];

			if ( isset( $moved_signatures[ $finding['signature'] ] ) ) {
				continue;
			}

			$state = $this->state_of( $this->b_states, $finding['probe'] );
			$entry = $this->describe_finding( $finding, [ 'probe_state' => $state ] );

			if ( self::STATE_COMPLETE !== $state ) {
				$entry['reason'] = self::absence_reason( $state, $finding['probe'] );

				$unverified[] = $entry;
				continue;
			}

			$unfinished = $this->unfinished_probes();

			if ( ! empty( $unfinished ) ) {
				$entry['reason'] = sprintf(
					'The probe "%s" completed in run B, but %s did not, and a finding can be recorded by whichever probe is running when it happens.',
					$finding['probe'],
					self::list_probes( $unfinished )
				);

				$unverified[] = $entry;
				continue;
			}

			$resolved[] = $entry;
		}

		return [
			'resolved'   => self::sorted( $resolved ),
			'unverified' => self::sorted( $unverified ),
		];
	}

	/**
	 * Findings both runs recorded under the same key: how the product already
	 * behaves, and the bulk of a healthy comparison.
	 *
	 * @param array<int,string> $a_keys
	 * @param array<int,string> $b_keys
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function compare_pre_existing( array $a_keys, array $b_keys ): array {
		$pre_existing = [];

		foreach ( array_intersect( $a_keys, $b_keys ) as $key ) {
			$pre_existing[] = $this->describe_finding( $this->b_findings[ $key ] );
		}

		return self::sorted( $pre_existing );
	}

	/**
	 * The probes run B did not carry to completion, including any that ran in the
	 * baseline and are missing from it altogether.
	 *
	 * @return array<int,string>
	 */
	private function unfinished_probes(): array {
		$unfinished = [];

		foreach ( array_keys( $this->a_states + $this->b_states ) as $probe ) {
			if ( self::STATE_COMPLETE !== $this->state_of( $this->b_states, (string) $probe ) ) {
				$unfinished[] = (string) $probe;
			}
		}

		return $unfinished;
	}

	/**
	 * @param array<int,string> $probes
	 */
	private static function list_probes( array $probes ): string {
		if ( count( $probes ) === 1 ) {
			return sprintf( '"%s"', $probes[0] );
		}

		return sprintf( '%d other probes', count( $probes ) );
	}

	/**
	 * Why an absence proves nothing, in the words of the state that caused it.
	 */
	private static function absence_reason( string $state, string $probe ): string {
		if ( 'truncated' === $state ) {
			return sprintf( 'The probe "%s" stopped early in run B, so this was never looked for.', $probe );
		}

		if ( 'incomplete' === $state ) {
			return sprintf( 'The probe "%s" broke in run B and published nothing, so this was never looked for.', $probe );
		}

		return sprintf( 'Run B has no completed probe "%s", so this was never looked for.', $probe );
	}

	/**
	 * Probes whose state is not the same in both runs, including the ones that only
	 * one run has. A probe that stopped earlier than it used to is the reason half
	 * of the findings above cannot be judged, so it is reported next to them.
	 *
	 * @return array<int,array<string,string>>
	 */
	private function changed_probe_states(): array {
		$changed = [];

		foreach ( array_keys( $this->a_states + $this->b_states ) as $probe ) {
			$a = $this->state_of( $this->a_states, (string) $probe );
			$b = $this->state_of( $this->b_states, (string) $probe );

			if ( $a === $b ) {
				continue;
			}

			$changed[] = [
				'probe' => (string) $probe,
				'a'     => $a,
				'b'     => $b,
			];
		}

		return $changed;
	}

	/**
	 * Say what the comparison could not answer, and why.
	 *
	 * @param array<string,array<int,array<string,mixed>>> $findings
	 *
	 * @return array<int,string>
	 */
	private function warnings( array $findings ): array {
		$warnings = [];

		if ( count( $findings['unverified'] ) > 0 ) {
			$warnings[] = sprintf(
				'%d baseline finding(s) could not be judged: run B did not complete every probe that could have recorded them, so their absence is not a fix. Each one says which probe.',
				count( $findings['unverified'] )
			);
		}

		$on_unfinished_baseline = 0;

		foreach ( $findings['introduced'] as $finding ) {
			if ( self::STATE_COMPLETE !== $finding['baseline_probe_state'] ) {
				++$on_unfinished_baseline;
			}
		}

		if ( $on_unfinished_baseline > 0 ) {
			$warnings[] = sprintf(
				'%d introduced finding(s) sit on a probe that did not run to completion in run A, so they may be pre-existing rather than new.',
				$on_unfinished_baseline
			);
		}

		foreach ( $this->malformed as $side => $count ) {
			if ( $count > 0 ) {
				$warnings[] = sprintf(
					'%d finding annotation(s) in run %s could not be read and are not part of this comparison.',
					$count,
					strtoupper( $side )
				);
			}
		}

		return $warnings;
	}

	/**
	 * @param array<string,mixed> $finding
	 * @param array<string,mixed> $extra
	 *
	 * @return array<string,mixed>
	 */
	private function describe_finding( array $finding, array $extra = [] ): array {
		return array_merge( [
			'key'       => $finding['key'],
			'signature' => $finding['signature'],
			'type'      => $finding['type'],
			'surface'   => $finding['surface'],
			'profile'   => $finding['profile'],
			'fixtures'  => $finding['fixtures'],
			'probe'     => $finding['probe'],
			'test'      => $finding['test'],
		], $extra );
	}

	/**
	 * @param array<int,array<string,mixed>> $findings
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private static function sorted( array $findings ): array {
		usort( $findings, function ( array $a, array $b ): int {
			return strcmp( (string) $a['key'], (string) $b['key'] );
		} );

		return $findings;
	}

	/**
	 * The set of signatures carried by the given finding keys.
	 *
	 * @param array<int,string>                 $keys
	 * @param array<string,array<string,mixed>> $findings
	 *
	 * @return array<string,bool>
	 */
	private static function signatures_of( array $keys, array $findings ): array {
		$signatures = [];

		foreach ( $keys as $key ) {
			$signatures[ (string) $findings[ $key ]['signature'] ] = true;
		}

		return $signatures;
	}

	/**
	 * @param array<string,string> $states
	 */
	private function state_of( array $states, string $test ): string {
		return $states[ $test ] ?? self::STATE_UNKNOWN;
	}

	/**
	 * The probe's identity: its published id, or the CTRF test key when a run
	 * predates the id being published.
	 *
	 * @param string                                              $test_key
	 * @param array<string,array{type:string,description:string}> $annotations
	 * @param bool                                                $by_id
	 */
	private static function probe_identity( string $test_key, array $annotations, bool $by_id ): string {
		if ( ! $by_id ) {
			return $test_key;
		}

		return self::published_probe_id( $annotations ) ?? $test_key;
	}

	/**
	 * @param array<string,array{type:string,description:string}> $annotations
	 */
	private static function published_probe_id( array $annotations ): ?string {
		foreach ( $annotations as $annotation ) {
			if ( self::PROBE_ID_ANNOTATION === $annotation['type'] && '' !== trim( $annotation['description'] ) ) {
				return trim( $annotation['description'] );
			}
		}

		return null;
	}

	/**
	 * True when every canary result in the run publishes a probe id.
	 */
	private static function publishes_probe_ids( RunSnapshot $run ): bool {
		foreach ( $run->tests as $test ) {
			$is_canary = false;

			foreach ( $test['annotations'] as $annotation ) {
				if ( in_array( $annotation['type'], self::CANARY_ANNOTATIONS, true ) ) {
					$is_canary = true;
					break;
				}
			}

			if ( $is_canary && is_null( self::published_probe_id( $test['annotations'] ) ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Read every `canary-finding` annotation in a run, keyed by finding key.
	 *
	 * Two annotations with the same key are the same identity by construction, so
	 * the first one wins - the comparison is over identities, not occurrences.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function findings( RunSnapshot $run, bool $by_id ): array {
		$findings = [];

		foreach ( $run->tests as $test_key => $test ) {
			$probe = self::probe_identity( (string) $test_key, $test['annotations'], $by_id );

			foreach ( $test['annotations'] as $annotation ) {
				if ( self::FINDING_ANNOTATION !== $annotation['type'] ) {
					continue;
				}

				$finding = self::decode_finding( $annotation['description'] );

				if ( is_null( $finding ) || isset( $findings[ $finding['key'] ] ) ) {
					continue;
				}

				$finding['probe']            = $probe;
				$finding['test']             = (string) $test_key;
				$findings[ $finding['key'] ] = $finding;
			}
		}

		return $findings;
	}

	/**
	 * A finding annotation is a JSON object. Anything without both identities is
	 * not one this comparison can place, and is counted rather than guessed at.
	 *
	 * @return array<string,mixed>|null
	 */
	private static function decode_finding( string $description ): ?array {
		$decoded = json_decode( $description, true );

		if ( ! is_array( $decoded ) ) {
			return null;
		}

		$key       = isset( $decoded['key'] ) && is_scalar( $decoded['key'] ) ? (string) $decoded['key'] : '';
		$signature = isset( $decoded['signature'] ) && is_scalar( $decoded['signature'] ) ? (string) $decoded['signature'] : '';

		if ( '' === $key || '' === $signature ) {
			return null;
		}

		return [
			'key'       => $key,
			'signature' => $signature,
			'type'      => isset( $decoded['type'] ) && is_scalar( $decoded['type'] ) ? (string) $decoded['type'] : '',
			'surface'   => isset( $decoded['surface'] ) && is_scalar( $decoded['surface'] ) ? (string) $decoded['surface'] : '',
			'profile'   => isset( $decoded['profile'] ) && is_scalar( $decoded['profile'] ) ? (string) $decoded['profile'] : '',
			'fixtures'  => self::string_list( $decoded['fixtures'] ?? [] ),
		];
	}

	/**
	 * @param mixed $value
	 *
	 * @return array<int,string>
	 */
	private static function string_list( $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}

		$strings = [];

		foreach ( $value as $entry ) {
			if ( is_scalar( $entry ) ) {
				$strings[] = (string) $entry;
			}
		}

		return $strings;
	}

	private static function malformed_findings( RunSnapshot $run ): int {
		$malformed = 0;

		foreach ( $run->tests as $test ) {
			foreach ( $test['annotations'] as $annotation ) {
				if ( self::FINDING_ANNOTATION === $annotation['type'] && is_null( self::decode_finding( $annotation['description'] ) ) ) {
					++$malformed;
				}
			}
		}

		return $malformed;
	}

	/**
	 * How far each probe got, keyed by CTRF test key.
	 *
	 * @return array<string,string>
	 */
	private static function probe_states( RunSnapshot $run, bool $by_id ): array {
		$states = [];

		foreach ( $run->tests as $test_key => $test ) {
			$probe = self::probe_identity( (string) $test_key, $test['annotations'], $by_id );

			foreach ( $test['annotations'] as $annotation ) {
				if ( self::PROBE_STATE_ANNOTATION !== $annotation['type'] ) {
					continue;
				}

				$state = strtolower( trim( $annotation['description'] ) );

				if ( ! isset( self::STATE_RANK[ $state ] ) ) {
					$state = self::STATE_UNKNOWN;
				}

				$states[ $probe ] = self::least_covered( $states[ $probe ] ?? self::STATE_COMPLETE, $state );
			}
		}

		return $states;
	}

	private static function least_covered( string $a, string $b ): string {
		return self::STATE_RANK[ $a ] >= self::STATE_RANK[ $b ] ? $a : $b;
	}
}
