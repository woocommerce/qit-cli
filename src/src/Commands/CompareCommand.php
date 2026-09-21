<?php

namespace QIT_CLI\Commands;

use QIT_CLI\QITInput;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class CompareCommand extends QITCommand {
	protected static $defaultName = 'compare'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/**
	 * How many entries each section prints before it is truncated, unless --limit says otherwise.
	 */
	private const DEFAULT_LIMIT = 25;

	/**
	 * The comparison document version this CLI can render.
	 */
	private const SCHEMA = 1;

	/**
	 * Context rows, in display order, for schema 1.
	 */
	private const CONTEXT_LABELS = [
		'test_type'           => 'Test type',
		'wordpress_version'   => 'WordPress',
		'woocommerce_version' => 'WooCommerce',
		'php_version'         => 'PHP',
		'mysql_version'       => 'MySQL',
		'extension_set'       => 'Extension set',
		'sut'                 => 'Extension',
		'sut_version'         => 'Extension version',
		'test_packages'       => 'Test packages',
		'canary_profile'      => 'Canary profile',
	];

	private const SUMMARY_KEYS = [ 'tests', 'passed', 'failed', 'skipped', 'pending', 'other' ];

	/**
	 * Top-level sections every schema 1 document carries.
	 */
	private const DOCUMENT_KEYS = [ 'runs', 'guard', 'summary', 'tests', 'annotations', 'totals' ];

	private const PROBE_COMPLETE = 'complete';

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Compare two finished test runs.' )
			->addArgument( 'run_a', InputArgument::REQUIRED, 'The baseline test run ID.' )
			->addArgument( 'run_b', InputArgument::REQUIRED, 'The test run ID to compare against the baseline.' )
			->addOption( 'format', null, InputOption::VALUE_REQUIRED, 'Output format: "human" or "json".', 'human' )
			->addOption( 'limit', null, InputOption::VALUE_REQUIRED, 'Maximum entries printed per section in human output. Use 0 for no limit.', (string) self::DEFAULT_LIMIT )
			->addOption( 'exit-code', null, InputOption::VALUE_NONE, 'Exit with 1 when run B introduced failures, for use as a CI gate. Without it, a comparison that ran exits 0 whatever it found.' )
			->setHelp( <<<'HELP'
Compare two test runs that already finished, without re-running anything.

	qit compare 12345 12346
	qit compare 12345 12346 --format=json

Reports, for the two runs:

	* Introduced, resolved and still-failing tests, by name and status
	* Tests that were added or removed between the runs
	* Summary counts side by side
	* Changes to the tests' CTRF annotations

Runs of the WooCommerce ecosystem canary get an extra section, because two of
its properties defeat a plain annotation diff. A canary finding is not tied to
the probe that recorded it - a fatal from a loopback request or a scheduled
action lands under whichever probe was running - so a finding that changed
probes is reported as "moved" rather than as a regression and a fix at once. And
each probe says how far it got: a finding missing from a probe that stopped
early was never looked for, so it is reported as such instead of as resolved.

Both runs must be of the same test type, and must report results in CTRF format,
which covers the activation, compatibility, woo-api and woo-e2e test types. Two
different test types are two different populations of tests, so comparing them is
refused rather than reported as everything being added and removed at once.

The comparison only sees what survives the round trip through QIT: test names,
statuses, durations, "extra.annotations", package metadata, and the run's own
environment metadata. CTRF attachments do NOT survive - they hold filesystem
paths from the machine that ran the tests, and those are gone once the job ends.
A test package that wants its data compared must emit it as an annotation, not
as an attachment.

Run A is the baseline and run B the candidate. The QIT Manager computes the
comparison; this command renders it with the same sections as the comparison's
report page, and human output ends with a link to that page.

When the two runs differ in more than one dimension (WordPress, PHP, package
version, and so on), the comparison is still printed but flagged, because a
difference in results cannot be attributed to any single one of them, and it
ends without a verdict.

Reporting a difference is not a failure: a comparison that ran exits 0 whatever it
found, so dropping this into a pipeline does not turn the step red. Pass
--exit-code to gate on the result, as you would with "git diff --exit-code", and
it exits 1 when run B introduced failures. That holds for runs that are not
comparable too, so a mismatched pair cannot pass a gate unnoticed.

Exit status codes: 0 (the comparison ran), 1 (only with --exit-code: run B
introduced failures), 2 (the runs could not be fetched or compared).
HELP
			);
	}

	protected function doExecute( QITInput $input, OutputInterface $output ): int {
		$run_a = trim( (string) $input->getArgument( 'run_a' ) );
		$run_b = trim( (string) $input->getArgument( 'run_b' ) );

		$format = (string) $input->getOption( 'format' );

		if ( ! in_array( $format, [ 'human', 'json' ], true ) ) {
			$output->writeln( sprintf( '<error>Invalid --format "%s". Allowed values: human, json.</error>', OutputFormatter::escape( $format ) ) );

			return Command::INVALID;
		}

		if ( $run_a === '' || $run_b === '' ) {
			$output->writeln( '<error>Both test run IDs are required.</error>' );

			return Command::INVALID;
		}

		if ( $run_a === $run_b ) {
			$output->writeln( '<error>Cannot compare a test run against itself.</error>' );

			return Command::INVALID;
		}

		try {
			$comparison = $this->fetch_comparison( $run_a, $run_b );
		} catch ( \RuntimeException $e ) {
			$this->render_error( $e->getMessage(), $output );

			return Command::INVALID;
		}

		if ( $format === 'json' ) {
			$output->writeln( (string) json_encode( $comparison, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		} else {
			$this->render_human( $comparison, $output, $this->get_limit( $input ) );
		}

		/*
		 * Finding a difference is the command working, not the command failing, so the
		 * result does not colour the exit code unless the caller asks for it. This is
		 * the "git diff --exit-code" split: a bare compare is safe to drop into a
		 * pipeline, and gating on the outcome is opted into explicitly. A genuine
		 * failure - an unfetchable run, two different test types - still exits 2 above,
		 * so a mistyped ID can never pass silently.
		 */
		if ( $input->getOption( 'exit-code' ) && ! empty( $comparison['has_regressions'] ) ) {
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}

	/**
	 * The Manager computes the comparison; this command only renders it.
	 *
	 * @return array<string,mixed>
	 *
	 * @throws \RuntimeException If the Manager could not compare the runs.
	 */
	private function fetch_comparison( string $run_a, string $run_b ): array {
		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/compare' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'a' => $run_a,
					'b' => $run_b,
				] )
				// Read the body of the expected failures instead of throwing, to tell them apart.
				->with_expected_status_codes( [ 200, 400, 401, 404, 422 ] )
				->with_retry( 0 )
				->request();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( sprintf(
				'Could not compare test runs %s and %s: %s',
				$run_a,
				$run_b,
				$this->unwrap_manager_message( $e->getMessage() )
			), 0, $e );
		}

		$response = json_decode( $json, true );

		if ( is_array( $response ) && ( $response['code'] ?? '' ) === 'rest_no_route' ) {
			throw new \RuntimeException( 'This QIT Manager cannot compare test runs yet.' );
		}

		if ( is_array( $response ) && isset( $response['schema'] ) ) {
			if ( (int) $response['schema'] !== self::SCHEMA ) {
				throw new \RuntimeException( sprintf(
					"This comparison is in format version %d, which this version of the QIT CLI cannot read.\nUpdate the QIT CLI and try again.",
					(int) $response['schema']
				) );
			}

			foreach ( self::DOCUMENT_KEYS as $key ) {
				if ( ! isset( $response[ $key ] ) || ! is_array( $response[ $key ] ) ) {
					throw new \RuntimeException( 'The Manager returned an unexpected response.' );
				}
			}

			return $response;
		}

		if ( is_array( $response ) && isset( $response['message'] ) && is_string( $response['message'] ) ) {
			$message = $response['message'];
		} elseif ( trim( $json ) !== '' && ! is_array( $response ) ) {
			$message = $this->unwrap_manager_message( $json );
		} else {
			$message = 'The Manager returned an unexpected response.';
		}

		// A WP_Error body carries a code: authentication, not the run IDs, went wrong.
		if ( is_array( $response ) && isset( $response['code'] ) ) {
			throw new \RuntimeException( $message );
		}

		throw new \RuntimeException( sprintf(
			"Could not compare test runs %s and %s: %s\nCheck that both IDs are correct and belong to this account. Run \"qit list-tests\" to see your recent test runs.",
			$run_a,
			$run_b,
			$message
		) );
	}

	/**
	 * The Manager reports some errors as a bare JSON string, which reaches us still
	 * carrying its quotes - "Test run with ID 1 does not exist." - and reads like a
	 * quoting bug when pasted into a sentence. Unwrap that, and pass anything else
	 * through untouched.
	 *
	 * The {"message": "..."} shape needs no handling here: it is read before this
	 * method is reached.
	 */
	private function unwrap_manager_message( string $message ): string {
		$message = trim( $message );
		$decoded = json_decode( $message, true );

		if ( is_string( $decoded ) && trim( $decoded ) !== '' ) {
			return trim( $decoded );
		}

		return $message;
	}

	/**
	 * Print a failure. The first line is the error itself; any further lines are
	 * guidance on what to do about it, and read better unhighlighted.
	 */
	private function render_error( string $message, OutputInterface $output ): void {
		$lines = explode( "\n", $message );

		foreach ( $lines as $index => $line ) {
			$line = OutputFormatter::escape( $line );
			$output->writeln( $index === 0 ? "<error>{$line}</error>" : $line );
		}
	}

	private function get_limit( QITInput $input ): int {
		$limit = $input->getOption( 'limit' );

		if ( ! is_numeric( $limit ) || (int) $limit < 0 ) {
			return self::DEFAULT_LIMIT;
		}

		return (int) $limit;
	}

	/**
	 * @param array<string,mixed> $comparison
	 */
	private function render_human( array $comparison, OutputInterface $output, int $limit ): void {
		$this->render_comparison( $comparison, $output, $limit );

		if ( ! empty( $comparison['report_url'] ) && is_string( $comparison['report_url'] ) ) {
			$output->writeln( '' );
			$output->writeln( sprintf( 'Report: <href=%1$s>%1$s</>', OutputFormatter::escape( $comparison['report_url'] ) ) );
		}
	}

	/**
	 * @param array<string,mixed> $comparison
	 */
	private function render_comparison( array $comparison, OutputInterface $output, int $limit ): void {
		$a = $comparison['runs']['a'];
		$b = $comparison['runs']['b'];

		$output->writeln( sprintf(
			'<info>Comparing baseline %s (A) against candidate %s (B)</info>',
			OutputFormatter::escape( $a['id'] ),
			OutputFormatter::escape( $b['id'] )
		) );
		$output->writeln( '' );

		$this->render_context( $comparison, $output );
		$this->render_summary( $comparison['summary'], $output );

		if ( isset( $comparison['canary'] ) ) {
			$this->render_canary( $comparison['canary'], $output, $limit );
		}

		$this->render_tests( $comparison, $output, $limit );

		$output->writeln( sprintf(
			'<info>%d test(s) unchanged.</info>',
			$comparison['tests']['unchanged_count']
		) );

		if ( empty( $comparison['guard']['comparable'] ) ) {
			$output->writeln( '<comment>Not comparable: the runs differ in more than the version under test, so these counts are not a verdict.</comment>' );

			return;
		}

		/*
		 * The same number the exit code gates on, so the summary cannot contradict
		 * either the sections above it or the status the command exits with. For a
		 * canary run that means findings rather than probe statuses: a probe fails
		 * when it records anything, so "one more failing probe" and "one new finding"
		 * are not the same statement.
		 */
		$regressions = $comparison['totals']['regressions'];

		if ( $regressions === 0 ) {
			$output->writeln( '<info>No failures introduced by the candidate.</info>' );

			return;
		}

		// A failure left over once findings are counted is one no finding accounts
		// for, which usually means the harness rather than the product.
		$findings = isset( $comparison['canary'] ) ? $comparison['canary']['totals']['introduced'] : 0;
		$failures = $regressions - $findings;

		if ( $findings > 0 && $failures > 0 ) {
			$output->writeln( sprintf( '<fg=red>The candidate introduced %d canary finding(s) and %d unexplained failure(s).</>', $findings, $failures ) );
		} elseif ( $findings > 0 ) {
			$output->writeln( sprintf( '<fg=red>The candidate introduced %d canary finding(s).</>', $findings ) );
		} else {
			$output->writeln( sprintf( '<fg=red>The candidate introduced %d failure(s).</>', $failures ) );
		}
	}

	/**
	 * @param array<string,mixed> $comparison
	 */
	private function render_context( array $comparison, OutputInterface $output ): void {
		$a_context = (array) $comparison['runs']['a']['context'];
		$b_context = (array) $comparison['runs']['b']['context'];
		$differing = array_column( $comparison['guard']['differences'], 'field' );

		$rows   = [];
		$labels = array_column( $comparison['guard']['differences'], 'label', 'field' ) + self::CONTEXT_LABELS;
		// Known fields in order, then any the Manager added since, so a differing field is never hidden.
		$fields = array_unique( array_merge( array_keys( self::CONTEXT_LABELS ), array_keys( array_merge( $a_context, $b_context ) ) ) );

		foreach ( $fields as $field ) {
			$label   = (string) ( $labels[ $field ] ?? $field );
			$a_value = (string) ( $a_context[ $field ] ?? '' );
			$b_value = (string) ( $b_context[ $field ] ?? '' );

			if ( $a_value === '' && $b_value === '' ) {
				continue;
			}

			$rows[] = [
				$label,
				OutputFormatter::escape( $a_value ),
				OutputFormatter::escape( $b_value ),
				in_array( $field, $differing, true ) ? 'differs' : '',
			];
		}

		if ( ! empty( $rows ) ) {
			$table = new Table( $output );
			$table->setStyle( 'compact' )
				->setHeaders( [ 'Environment', 'Baseline', 'Candidate', '' ] )
				->setRows( $rows );
			$table->render();
			$output->writeln( '' );
		}

		$this->render_warnings( $comparison['guard']['warnings'], $output );
	}

	/**
	 * @param array<string,array<string,int>> $summary
	 */
	private function render_summary( array $summary, OutputInterface $output ): void {
		$rows = [];

		foreach ( self::SUMMARY_KEYS as $key ) {
			$delta  = $summary['delta'][ $key ];
			$rows[] = [
				ucfirst( $key ),
				(string) $summary['a'][ $key ],
				(string) $summary['b'][ $key ],
				$delta > 0 ? '+' . $delta : (string) $delta,
			];
		}

		$table = new Table( $output );
		$table->setStyle( 'compact' )
			->setHeaders( [ 'Results', 'Baseline', 'Candidate', 'Delta' ] )
			->setRows( $rows );
		$table->render();
		$output->writeln( '' );
	}

	/**
	 * Same sections, order and names as the report page's Tests section. A test
	 * added in a failing state is newly failing, as it is in the regression count.
	 *
	 * @param array<string,mixed> $comparison
	 */
	private function render_tests( array $comparison, OutputInterface $output, int $limit ): void {
		$tests         = $comparison['tests'];
		$added_failing = [];
		$added_other   = [];

		foreach ( $tests['added'] as $test ) {
			if ( $test['status'] === 'failed' ) {
				$added_failing[] = $this->describe_test( $test['key'], 'only in the candidate, ' . $test['status'], 'fg=red' );
			} else {
				$added_other[] = $this->describe_test( $test['key'], $test['status'] );
			}
		}

		$transitions = function ( array $tests, string $style = '' ): array {
			return array_map( function ( array $test ) use ( $style ): string {
				// A status that did not move (still failing) reads as noise as "failed -> failed".
				$transition = $test['status']['a'] === $test['status']['b']
					? $test['status']['b']
					: $test['status']['a'] . ' -> ' . $test['status']['b'];

				return $this->describe_test( $test['key'], $transition, $style );
			}, $tests );
		};

		$annotations = [];

		foreach ( [
			'added'   => '<fg=green>+</>',
			'removed' => '<fg=red>-</>',
		] as $key => $sign ) {
			foreach ( $comparison['annotations'][ $key ] as $annotation ) {
				$annotations[] = sprintf(
					'  %s %s <fg=gray>[%s]</> %s',
					$sign,
					OutputFormatter::escape( $annotation['test'] ),
					OutputFormatter::escape( $annotation['type'] ),
					OutputFormatter::escape( $annotation['description'] )
				);
			}
		}

		$output->writeln( '<options=bold>Tests</>' );
		$output->writeln( '' );

		$empty_sections = [];

		$this->render_section( 'Newly failing', array_merge( $transitions( $tests['introduced'], 'fg=red' ), $added_failing ), $output, $limit, $empty_sections );
		$this->render_section( 'Added', $added_other, $output, $limit, $empty_sections );
		$this->render_section( 'Still failing', $transitions( $tests['still_failing'], 'fg=yellow' ), $output, $limit, $empty_sections );
		$this->render_section( 'Resolved', $transitions( $tests['resolved'], 'fg=green' ), $output, $limit, $empty_sections );
		$this->render_section( 'Other status changes', $transitions( $tests['status_changed'] ), $output, $limit, $empty_sections );
		$this->render_section(
			'Removed',
			array_map( function ( array $test ): string {
				return $this->describe_test( $test['key'], $test['status'] );
			}, $tests['removed'] ),
			$output,
			$limit,
			$empty_sections
		);
		$this->render_section( 'Annotation changes', $annotations, $output, $limit, $empty_sections );

		$this->render_empty( $empty_sections, $output );
	}

	private function describe_test( string $key, string $detail, string $style = '' ): string {
		$name = OutputFormatter::escape( $key );

		return sprintf(
			'  %s <fg=gray>(%s)</>',
			$style === '' ? $name : sprintf( '<%s>%s</>', $style, $name ),
			OutputFormatter::escape( $detail )
		);
	}

	/**
	 * Same buckets, order and names as the report page's canary section.
	 *
	 * "Moved" and "not looked for" are the two buckets that only exist here, and both
	 * are there to avoid claiming something the runs do not show: a finding that
	 * changed probes is neither a regression nor a fix, and a finding missing from
	 * a probe that stopped early was never looked for.
	 *
	 * @param array<string,mixed> $canary
	 */
	private function render_canary( array $canary, OutputInterface $output, int $limit ): void {
		$output->writeln( '<options=bold>Ecosystem canary findings (advisory)</>' );
		$output->writeln( '' );

		$this->render_warnings( $canary['warnings'], $output );

		$moved = array_map( function ( array $finding ): string {
			return sprintf(
				"    %s\n      <fg=gray>%s -> %s</>",
				OutputFormatter::escape( (string) $finding['signature'] ),
				OutputFormatter::escape( implode( ', ', array_column( $finding['from'], 'probe' ) ) ),
				OutputFormatter::escape( implode( ', ', array_column( $finding['to'], 'probe' ) ) )
			);
		}, $canary['findings']['moved'] );

		$probe_states = array_map( function ( array $probe ): string {
			return sprintf(
				'    %s <fg=gray>(%s -> %s)</>',
				OutputFormatter::escape( $probe['probe'] ),
				OutputFormatter::escape( (string) $probe['a'] ),
				OutputFormatter::escape( (string) $probe['b'] )
			);
		}, $canary['probes']['changed'] );

		$empty_sections = [];

		$this->render_section( '  Introduced', $this->describe_findings( $canary['findings']['introduced'], 'fg=red' ), $output, $limit, $empty_sections );
		$this->render_section( '  Moved between probes', $moved, $output, $limit, $empty_sections );
		$this->render_section( '  Not looked for on the candidate', $this->describe_findings( $canary['findings']['unverified'], 'fg=yellow' ), $output, $limit, $empty_sections );
		$this->render_section( '  Resolved', $this->describe_findings( $canary['findings']['resolved'], 'fg=green' ), $output, $limit, $empty_sections );
		$this->render_section( '  Pre-existing', $this->describe_findings( $canary['findings']['pre_existing'], 'fg=gray' ), $output, $limit, $empty_sections );
		$this->render_section( '  Probe state changes', $probe_states, $output, $limit, $empty_sections );

		$this->render_empty( $empty_sections, $output );

		/*
		 * The test sections below are about probe results, which are a different
		 * thing from findings. A probe fails when it records anything, so a finding
		 * that only moved probes shows up there as one probe passing and another
		 * failing, and a reader who has just been told the finding moved needs to
		 * know why the other section disagrees.
		 */
		if ( count( $canary['findings']['moved'] ) > 0 ) {
			$output->writeln( '<comment>A probe fails when it records anything, so a finding that moved probes also shows below as one probe resolved and another newly failing. Neither is a change in what the build does.</comment>' );
			$output->writeln( '' );
		}
	}

	/**
	 * @param array<int,array<string,mixed>> $findings
	 *
	 * @return string[]
	 */
	private function describe_findings( array $findings, string $style ): array {
		return array_map( function ( array $finding ) use ( $style ): string {
			return sprintf(
				'    <%s>%s</> <fg=gray>(%s)</>',
				$style,
				OutputFormatter::escape( (string) $finding['key'] ),
				OutputFormatter::escape( $this->describe_finding_context( $finding ) )
			);
		}, $findings );
	}

	/**
	 * @param array<string,mixed> $finding
	 */
	private function describe_finding_context( array $finding ): string {
		$parts = array_filter( [ (string) $finding['surface'], (string) $finding['profile'] ] );

		if ( ! empty( $finding['fixtures'] ) ) {
			$parts[] = 'fixtures: ' . implode( ' + ', $finding['fixtures'] );
		}

		if ( isset( $finding['probe_state'] ) && $finding['probe_state'] !== self::PROBE_COMPLETE ) {
			$parts[] = 'candidate probe: ' . $finding['probe_state'];
		}

		if ( isset( $finding['baseline_probe_state'] ) && $finding['baseline_probe_state'] !== self::PROBE_COMPLETE ) {
			$parts[] = 'baseline probe: ' . $finding['baseline_probe_state'];
		}

		return implode( ', ', $parts );
	}

	/**
	 * Prints a section, or adds its heading to $empty_sections so the group ends with a
	 * single "Nothing in:" line, as the report page does.
	 *
	 * @param string          $heading
	 * @param string[]        $entries Formatted entries; one may span several lines.
	 * @param OutputInterface $output
	 * @param int             $limit
	 * @param string[]        $empty_sections
	 */
	private function render_section( string $heading, array $entries, OutputInterface $output, int $limit, array &$empty_sections ): void {
		if ( empty( $entries ) ) {
			$empty_sections[] = trim( $heading );

			return;
		}

		$output->writeln( sprintf( '<options=bold>%s (%d)</>', $heading, count( $entries ) ) );

		foreach ( $this->limited( $entries, $limit ) as $entry ) {
			$output->writeln( $entry );
		}

		if ( $limit > 0 && count( $entries ) > $limit ) {
			$output->writeln( sprintf( '  <fg=gray>... and %d more. Use --limit=0 to show all.</>', count( $entries ) - $limit ) );
		}

		$output->writeln( '' );
	}

	/**
	 * @param string[] $titles
	 */
	private function render_empty( array $titles, OutputInterface $output ): void {
		if ( empty( $titles ) ) {
			return;
		}

		$output->writeln( sprintf( '<fg=gray>Nothing in: %s.</>', implode( ', ', $titles ) ) );
		$output->writeln( '' );
	}

	/**
	 * @param string[] $warnings
	 */
	private function render_warnings( array $warnings, OutputInterface $output ): void {
		foreach ( $warnings as $warning ) {
			$output->writeln( sprintf( '<comment>Warning: %s</comment>', OutputFormatter::escape( $warning ) ) );
		}

		if ( ! empty( $warnings ) ) {
			$output->writeln( '' );
		}
	}

	/**
	 * @param string[] $entries
	 *
	 * @return string[]
	 */
	private function limited( array $entries, int $limit ): array {
		if ( $limit <= 0 ) {
			return $entries;
		}

		return array_slice( $entries, 0, $limit );
	}
}
