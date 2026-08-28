<?php

namespace QIT_CLI\Commands;

use QIT_CLI\Compare\RunComparison;
use QIT_CLI\Compare\RunSnapshot;
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

	protected function configure(): void {
		parent::configure();
		$this
			->setDescription( 'Compare two finished test runs.' )
			->addArgument( 'run_a', InputArgument::REQUIRED, 'The baseline test run ID.' )
			->addArgument( 'run_b', InputArgument::REQUIRED, 'The test run ID to compare against the baseline.' )
			->addOption( 'format', null, InputOption::VALUE_REQUIRED, 'Output format: "human" or "json".', 'human' )
			->addOption( 'limit', null, InputOption::VALUE_REQUIRED, 'Maximum entries printed per section in human output. Use 0 for no limit.', (string) self::DEFAULT_LIMIT )
			->setHelp( <<<'HELP'
Compare two test runs that already finished, without re-running anything.

	qit compare 12345 12346
	qit compare 12345 12346 --format=json

Reports, for the two runs:

	* Introduced, resolved and still-failing tests, by name and status
	* Tests that were added or removed between the runs
	* Summary counts side by side
	* Changes to the tests' CTRF annotations

Both runs must report results in CTRF format, which covers the activation,
compatibility, ecosystem, woo-api and woo-e2e test types.

The comparison only sees what survives the round trip through QIT: test names,
statuses, durations, "extra.annotations", package metadata, and the run's own
environment metadata. CTRF attachments do NOT survive - they hold filesystem
paths from the machine that ran the tests, and those are gone once the job ends.
A test package that wants its data compared must emit it as an annotation, not
as an attachment.

When the two runs differ in more than one dimension (WordPress, PHP, package
version, and so on), the comparison is still printed but flagged, because a
difference in results cannot be attributed to any single one of them.

Exit status codes: 0 (no failures introduced), 1 (run B introduced failures),
2 (the runs could not be fetched or compared).
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
			$test_runs = $this->fetch_runs( $run_a, $run_b );

			$comparison = new RunComparison(
				RunSnapshot::from_manager_run( $run_a, $this->pick_run( $test_runs, $run_a ) ),
				RunSnapshot::from_manager_run( $run_b, $this->pick_run( $test_runs, $run_b ) )
			);
		} catch ( \RuntimeException $e ) {
			$output->writeln( "<error>{$e->getMessage()}</error>" );

			return Command::INVALID;
		}

		if ( $format === 'json' ) {
			$output->writeln( (string) json_encode( $comparison->to_array(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		} else {
			$this->render_human( $comparison->to_array(), $output, $this->get_limit( $input ) );
		}

		return $comparison->has_regressions() ? Command::FAILURE : Command::SUCCESS;
	}

	/**
	 * Fetch both runs in a single request, so the two sides of the comparison always
	 * come from the same read of the Manager.
	 *
	 * @return array<int|string,mixed>
	 *
	 * @throws \RuntimeException If the runs could not be fetched.
	 */
	private function fetch_runs( string $run_a, string $run_b ): array {
		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-multiple' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'test_run_ids' => $run_a . ',' . $run_b,
				] )
				->with_retry( 3 )
				->request();
		} catch ( \Exception $e ) {
			throw new \RuntimeException( sprintf( 'Could not fetch the test runs: %s', $e->getMessage() ), 0, $e );
		}

		$test_runs = json_decode( $json, true );

		if ( ! is_array( $test_runs ) ) {
			throw new \RuntimeException( 'The Manager returned an unexpected response for these test runs.' );
		}

		return $test_runs;
	}

	/**
	 * @param array<int|string,mixed> $test_runs
	 *
	 * @return array<string,mixed>
	 *
	 * @throws \RuntimeException If the run is not in the response.
	 */
	private function pick_run( array $test_runs, string $run_id ): array {
		if ( isset( $test_runs[ $run_id ] ) && is_array( $test_runs[ $run_id ] ) ) {
			return $test_runs[ $run_id ];
		}

		// The Manager keys the response by test run ID, but fall back to a scan rather
		// than failing over a key that came back as an int, a string or padded.
		foreach ( $test_runs as $test_run ) {
			if ( is_array( $test_run ) && isset( $test_run['test_run_id'] ) && (string) $test_run['test_run_id'] === $run_id ) {
				return $test_run;
			}
		}

		throw new \RuntimeException( sprintf( 'Test run %s was not found.', $run_id ) );
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

		$output->writeln( sprintf(
			'<info>%d test(s) unchanged.</info>',
			$comparison['tests']['unchanged_count']
		) );

		$introduced = $comparison['totals']['introduced'] + $this->count_failed( $comparison['tests']['added'] );

		if ( $introduced === 0 ) {
			$output->writeln( '<info>No failures introduced by run B.</info>' );
		} else {
			$output->writeln( sprintf( '<fg=red>Run B introduced %d failure(s).</>', $introduced ) );
		}
	}

	/**
	 * @param array<int,array<string,mixed>> $tests
	 */
	private function count_failed( array $tests ): int {
		$failed = 0;

		foreach ( $tests as $test ) {
			if ( $test['status'] === 'failed' ) {
				++$failed;
			}
		}

		return $failed;
	}

	/**
	 * @param array<string,mixed> $comparison
	 */
	private function render_context( array $comparison, OutputInterface $output ): void {
		$a_context = $comparison['runs']['a']['context'];
		$b_context = $comparison['runs']['b']['context'];
		$differing = array_column( $comparison['guard']['differences'], 'field' );

		$rows = [];

		foreach ( RunSnapshot::CONTEXT_LABELS as $field => $label ) {
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

		foreach ( RunSnapshot::SUMMARY_KEYS as $key ) {
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
