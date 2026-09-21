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
		'extension_set'       => 'Extension set',
		'sut'                 => 'Extension',
		'sut_version'         => 'Extension version',
		'test_packages'       => 'Test packages',
		'canary_profile'      => 'Canary profile',
	];

	private const SUMMARY_KEYS = [ 'tests', 'passed', 'failed', 'skipped', 'pending', 'other' ];

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

The QIT Manager computes the comparison; this command renders it, and human
output ends with a link to the comparison's report page.

When the two runs differ in more than one dimension (WordPress, PHP, package
version, and so on), the comparison is still printed but flagged, because a
difference in results cannot be attributed to any single one of them.

Reporting a difference is not a failure: a comparison that ran exits 0 whatever it
found, so dropping this into a pipeline does not turn the step red. Pass
--exit-code to gate on the result, as you would with "git diff --exit-code", and
it exits 1 when run B introduced failures.

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

			return $response;
		}

		$message = is_array( $response ) && isset( $response['message'] ) && is_string( $response['message'] )
			? $response['message']
			: 'The Manager returned an unexpected response.';

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
	 * The {"message": "..."} shape needs no handling here: RequestBuilder already
	 * unwraps it before it throws, so it cannot reach this method.
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
			'<info>Comparing test run %s (A) against %s (B)</info>',
			OutputFormatter::escape( $a['id'] ),
			OutputFormatter::escape( $b['id'] )
		) );
		$output->writeln( '' );

		$this->render_context( $comparison, $output );
		$this->render_summary( $comparison['summary'], $output );

		$this->render_transitions( 'Introduced failures', $comparison['tests']['introduced'], 'fg=red', $output, $limit );
		$this->render_transitions( 'Resolved failures', $comparison['tests']['resolved'], 'fg=green', $output, $limit );
		$this->render_transitions( 'Still failing', $comparison['tests']['still_failing'], 'fg=yellow', $output, $limit );
		$this->render_transitions( 'Other status changes', $comparison['tests']['status_changed'], 'fg=default', $output, $limit );

		$this->render_tests( 'Added tests (only in B)', $comparison['tests']['added'], $output, $limit );
		$this->render_tests( 'Removed tests (only in A)', $comparison['tests']['removed'], $output, $limit );

		$this->render_annotations( $comparison['annotations'], $output, $limit );

		if ( isset( $comparison['canary'] ) ) {
			$this->render_canary( $comparison['canary'], $output, $limit );
		}

		$output->writeln( sprintf(
			'<info>%d test(s) unchanged.</info>',
			$comparison['tests']['unchanged_count']
		) );

		/*
		 * The same number the exit code gates on, so the summary cannot contradict
		 * either the sections above it or the status the command exits with. For a
		 * canary run that means findings rather than probe statuses: a probe fails
		 * when it records anything, so "one more failing probe" and "one new finding"
		 * are not the same statement.
		 */
		$regressions = $comparison['totals']['regressions'];

		if ( $regressions === 0 ) {
			$output->writeln( '<info>No failures introduced by run B.</info>' );

			return;
		}

		// The two are counted separately because they are different things. A probe
		// fails when it records anything, so its findings are the finer statement and
		// the ones the buckets above name; a failure left over is one no finding
		// accounts for, which usually means the harness rather than the product.
		$findings = isset( $comparison['canary'] ) ? $comparison['canary']['totals']['introduced'] : 0;
		$failures = $regressions - $findings;

		if ( $findings > 0 && $failures > 0 ) {
			$output->writeln( sprintf( '<fg=red>Run B introduced %d canary finding(s) and %d unexplained failure(s).</>', $findings, $failures ) );
		} elseif ( $findings > 0 ) {
			$output->writeln( sprintf( '<fg=red>Run B introduced %d canary finding(s).</>', $findings ) );
		} else {
			$output->writeln( sprintf( '<fg=red>Run B introduced %d failure(s).</>', $failures ) );
		}
	}

	/**
	 * @param array<string,mixed> $comparison
	 */
	private function render_context( array $comparison, OutputInterface $output ): void {
		$a_context = $comparison['runs']['a']['context'];
		$b_context = $comparison['runs']['b']['context'];
		$differing = array_column( $comparison['guard']['differences'], 'field' );

		$rows = [];

		foreach ( self::CONTEXT_LABELS as $field => $label ) {
			$a_value = $a_context[ $field ] ?? '';
			$b_value = $b_context[ $field ] ?? '';

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
				->setHeaders( [ 'Context', 'A', 'B', '' ] )
				->setRows( $rows );
			$table->render();
			$output->writeln( '' );
		}

		foreach ( $comparison['guard']['warnings'] as $warning ) {
			$output->writeln( sprintf( '<comment>Warning: %s</comment>', OutputFormatter::escape( $warning ) ) );
		}

		if ( ! empty( $comparison['guard']['warnings'] ) ) {
			$output->writeln( '' );
		}
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
			->setHeaders( [ 'Summary', 'A', 'B', 'Delta' ] )
			->setRows( $rows );
		$table->render();
		$output->writeln( '' );
	}

	/**
	 * @param string                         $heading
	 * @param array<int,array<string,mixed>> $tests
	 * @param string                         $style
	 * @param OutputInterface                $output
	 * @param int                            $limit
	 */
	private function render_transitions( string $heading, array $tests, string $style, OutputInterface $output, int $limit ): void {
		$this->render_heading( $heading, count( $tests ), $output );

		foreach ( $this->limited( $tests, $limit ) as $test ) {
			// A status that did not move (still failing) reads as noise as "failed -> failed".
			$transition = $test['status']['a'] === $test['status']['b']
				? $test['status']['b']
				: $test['status']['a'] . ' -> ' . $test['status']['b'];

			$output->writeln( sprintf(
				'  <%s>%s</> <fg=gray>(%s)</>',
				$style,
				OutputFormatter::escape( $test['key'] ),
				$transition
			) );
		}

		$this->render_truncation( count( $tests ), $limit, $output );
	}

	/**
	 * @param string                         $heading
	 * @param array<int,array<string,mixed>> $tests
	 * @param OutputInterface                $output
	 * @param int                            $limit
	 */
	private function render_tests( string $heading, array $tests, OutputInterface $output, int $limit ): void {
		$this->render_heading( $heading, count( $tests ), $output );

		foreach ( $this->limited( $tests, $limit ) as $test ) {
			$output->writeln( sprintf(
				'  %s <fg=gray>(%s)</>',
				OutputFormatter::escape( $test['key'] ),
				$test['status']
			) );
		}

		$this->render_truncation( count( $tests ), $limit, $output );
	}

	/**
	 * @param array{added:array<int,array<string,string>>,removed:array<int,array<string,string>>} $annotations
	 */
	private function render_annotations( array $annotations, OutputInterface $output, int $limit ): void {
		$total = count( $annotations['added'] ) + count( $annotations['removed'] );

		$this->render_heading( 'Annotation changes', $total, $output );

		foreach ( $this->limited( $annotations['added'], $limit ) as $annotation ) {
			$output->writeln( sprintf(
				'  <fg=green>+</> %s <fg=gray>[%s]</> %s',
				OutputFormatter::escape( $annotation['test'] ),
				OutputFormatter::escape( $annotation['type'] ),
				OutputFormatter::escape( $annotation['description'] )
			) );
		}

		foreach ( $this->limited( $annotations['removed'], $limit ) as $annotation ) {
			$output->writeln( sprintf(
				'  <fg=red>-</> %s <fg=gray>[%s]</> %s',
				OutputFormatter::escape( $annotation['test'] ),
				OutputFormatter::escape( $annotation['type'] ),
				OutputFormatter::escape( $annotation['description'] )
			) );
		}

		if ( $total > 0 ) {
			$output->writeln( '' );
		}
	}

	/**
	 * Print the ecosystem canary section, which reports observations rather than
	 * assertions and so is bucketed on its own terms.
	 *
	 * "Moved" and "unverified" are the two buckets that only exist here, and both
	 * are there to avoid claiming something the runs do not show: a finding that
	 * changed probes is neither a regression nor a fix, and a finding missing from
	 * a probe that stopped early was never looked for.
	 *
	 * @param array<string,mixed> $canary
	 */
	private function render_canary( array $canary, OutputInterface $output, int $limit ): void {
		$output->writeln( '<options=bold>Ecosystem canary findings</>' );
		$output->writeln( '' );

		$this->render_findings( 'Introduced', $canary['findings']['introduced'], 'fg=red', $output, $limit );
		$this->render_findings( 'Resolved', $canary['findings']['resolved'], 'fg=green', $output, $limit );
		$this->render_moved_findings( $canary['findings']['moved'], $output, $limit );
		$this->render_findings( 'Not looked for in run B', $canary['findings']['unverified'], 'fg=yellow', $output, $limit );
		$this->render_findings( 'Pre-existing', $canary['findings']['pre_existing'], 'fg=gray', $output, $limit );

		$this->render_probe_states( $canary['probes']['changed'], $output, $limit );

		/*
		 * The sections above this one are about findings; the test buckets near the
		 * top of the report are about probe results, which are a different thing. A
		 * probe fails when it records anything, so a finding that only moved probes
		 * shows up there as one probe passing and another failing. Both readings are
		 * true, and a reader who has just been told the finding moved needs to know
		 * why the other section disagrees.
		 */
		if ( count( $canary['findings']['moved'] ) > 0 ) {
			$output->writeln( '<comment>A probe fails when it records anything, so a finding that moved probes also shows above as one probe resolved and another introduced. Neither is a change in what the build does.</comment>' );
			$output->writeln( '' );
		}

		foreach ( $canary['warnings'] as $warning ) {
			$output->writeln( sprintf( '<comment>Warning: %s</comment>', OutputFormatter::escape( $warning ) ) );
		}

		if ( ! empty( $canary['warnings'] ) ) {
			$output->writeln( '' );
		}
	}

	/**
	 * @param string                         $heading
	 * @param array<int,array<string,mixed>> $findings
	 * @param string                         $style
	 * @param OutputInterface                $output
	 * @param int                            $limit
	 */
	private function render_findings( string $heading, array $findings, string $style, OutputInterface $output, int $limit ): void {
		$this->render_heading( '  ' . $heading, count( $findings ), $output );

		foreach ( $this->limited( $findings, $limit ) as $finding ) {
			$output->writeln( sprintf(
				'    <%s>%s</> <fg=gray>(%s)</>',
				$style,
				OutputFormatter::escape( (string) $finding['key'] ),
				OutputFormatter::escape( $this->describe_finding_context( $finding ) )
			) );
		}

		$this->render_truncation( count( $findings ), $limit, $output );
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
			$parts[] = 'run B probe: ' . $finding['probe_state'];
		}

		if ( isset( $finding['baseline_probe_state'] ) && $finding['baseline_probe_state'] !== self::PROBE_COMPLETE ) {
			$parts[] = 'run A probe: ' . $finding['baseline_probe_state'];
		}

		return implode( ', ', $parts );
	}

	/**
	 * @param array<int,array<string,mixed>> $moved
	 */
	private function render_moved_findings( array $moved, OutputInterface $output, int $limit ): void {
		$this->render_heading( '  Moved between probes', count( $moved ), $output );

		foreach ( $this->limited( $moved, $limit ) as $finding ) {
			$output->writeln( sprintf(
				'    %s',
				OutputFormatter::escape( (string) $finding['signature'] )
			) );
			$output->writeln( sprintf(
				'      <fg=gray>%s -> %s</>',
				OutputFormatter::escape( implode( ', ', array_column( $finding['from'], 'probe' ) ) ),
				OutputFormatter::escape( implode( ', ', array_column( $finding['to'], 'probe' ) ) )
			) );
		}

		$this->render_truncation( count( $moved ), $limit, $output );
	}

	/**
	 * @param array<int,array<string,string>> $changed
	 */
	private function render_probe_states( array $changed, OutputInterface $output, int $limit ): void {
		$this->render_heading( '  Probe state changes', count( $changed ), $output );

		foreach ( $this->limited( $changed, $limit ) as $probe ) {
			$output->writeln( sprintf(
				'    %s <fg=gray>(%s -> %s)</>',
				OutputFormatter::escape( $probe['probe'] ),
				$probe['a'],
				$probe['b']
			) );
		}

		$this->render_truncation( count( $changed ), $limit, $output );
	}

	private function render_heading( string $heading, int $count, OutputInterface $output ): void {
		$output->writeln( sprintf( '<options=bold>%s (%d)</>', $heading, $count ) );

		if ( $count === 0 ) {
			$output->writeln( '' );
		}
	}

	/**
	 * @param array<int,array<string,mixed>> $entries
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function limited( array $entries, int $limit ): array {
		if ( $limit <= 0 ) {
			return $entries;
		}

		return array_slice( $entries, 0, $limit );
	}

	private function render_truncation( int $count, int $limit, OutputInterface $output ): void {
		if ( $count === 0 ) {
			return;
		}

		if ( $limit > 0 && $count > $limit ) {
			$output->writeln( sprintf( '  <fg=gray>... and %d more. Use --limit=0 to show all.</>', $count - $limit ) );
		}

		$output->writeln( '' );
	}
}
