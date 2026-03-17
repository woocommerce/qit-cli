<?php

namespace QIT_CLI\BreakingChanges\Renderers;

use QIT_CLI\BreakingChanges\Models\FoundReference;
use QIT_CLI\BreakingChanges\Models\ScanResult;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class ScanRenderer {
	/**
	 * Render a scan result in the specified format.
	 */
	public function render( ScanResult $result, OutputInterface $output, string $format = 'table' ): void {
		switch ( $format ) {
			case 'json':
				$this->render_json( $result, $output );
				break;
			case 'github':
				$this->render_github( $result, $output );
				break;
			case 'table':
			default:
				$this->render_table( $result, $output );
				break;
		}
	}

	/**
	 * Render multiple scan results.
	 *
	 * @param ScanResult[] $results
	 */
	public function render_multi( array $results, OutputInterface $output, string $format = 'table' ): void {
		switch ( $format ) {
			case 'json':
				$this->render_multi_json( $results, $output );
				break;
			case 'github':
				foreach ( $results as $result ) {
					$this->render_github( $result, $output );
				}
				break;
			case 'table':
			default:
				foreach ( $results as $result ) {
					$this->render_table( $result, $output );
					$output->writeln( '' );
				}
				$this->render_multi_summary( $results, $output );
				break;
		}
	}

	private function render_table( ScanResult $result, OutputInterface $output ): void {
		$output->writeln( sprintf( '<comment>Plugin: %s</comment>', $result->plugin_slug ) );

		if ( ! $result->has_breaking_references() ) {
			$output->writeln( '<info>No references to removed symbols or hooks found.</info>' );
			return;
		}

		// Group references by file.
		$grouped = $this->group_by_file( $result->references );

		foreach ( $grouped as $file => $refs ) {
			$output->writeln( sprintf( '  <comment>%s</comment>', $file ) );

			$table = new Table( $output );
			$table->setHeaders( [ 'Line', 'Type', 'Symbol', 'Context' ] );

			foreach ( $refs as $ref ) {
				$table->addRow( [
					$ref->line,
					$ref->type,
					$ref->name,
					$ref->context,
				] );
			}

			$table->render();
		}

		$output->writeln( sprintf(
			'<error>Found %d reference(s) to removed symbols/hooks.</error>',
			count( $result->references )
		) );
	}

	private function render_json( ScanResult $result, OutputInterface $output ): void {
		$data = $this->result_to_array( $result );
		$output->writeln( json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	/**
	 * @param ScanResult[] $results
	 */
	private function render_multi_json( array $results, OutputInterface $output ): void {
		$data = [
			'plugins' => array_map( [ $this, 'result_to_array' ], $results ),
			'summary' => [
				'total_plugins'   => count( $results ),
				'affected_plugins' => count( array_filter( $results, function ( ScanResult $r ) {
					return $r->has_breaking_references();
				} ) ),
			],
		];

		$output->writeln( json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	private function render_github( ScanResult $result, OutputInterface $output ): void {
		foreach ( $result->references as $ref ) {
			$output->writeln( sprintf(
				'::error file=%s,line=%d::[%s] References removed %s: %s',
				$ref->file,
				$ref->line,
				$result->plugin_slug,
				$ref->type,
				$ref->name
			) );
		}
	}

	/**
	 * @param ScanResult[] $results
	 */
	private function render_multi_summary( array $results, OutputInterface $output ): void {
		$affected = array_filter( $results, function ( ScanResult $r ) {
			return $r->has_breaking_references();
		} );

		$output->writeln( sprintf(
			'Scanned %d plugin(s): <error>%d affected</error>, <info>%d clean</info>',
			count( $results ),
			count( $affected ),
			count( $results ) - count( $affected )
		) );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function result_to_array( ScanResult $result ): array {
		return [
			'plugin_slug'              => $result->plugin_slug,
			'has_breaking_references' => $result->has_breaking_references(),
			'reference_count'          => count( $result->references ),
			'references'               => array_map( function ( FoundReference $ref ) {
				return [
					'name'    => $ref->name,
					'type'    => $ref->type,
					'file'    => $ref->file,
					'line'    => $ref->line,
					'context' => $ref->context,
				];
			}, $result->references ),
			'warnings' => $result->warnings,
		];
	}

	/**
	 * Group references by file path.
	 *
	 * @param FoundReference[] $references
	 * @return array<string, FoundReference[]>
	 */
	private function group_by_file( array $references ): array {
		$grouped = [];
		foreach ( $references as $ref ) {
			$grouped[ $ref->file ][] = $ref;
		}
		return $grouped;
	}
}
