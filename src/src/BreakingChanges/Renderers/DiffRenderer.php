<?php

namespace QIT_CLI\BreakingChanges\Renderers;

use QIT_CLI\BreakingChanges\Models\DiffResult;
use QIT_CLI\BreakingChanges\Models\HookInfo;
use QIT_CLI\BreakingChanges\Models\SymbolInfo;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class DiffRenderer {
	/**
	 * Render a diff result in the specified format.
	 */
	public function render( DiffResult $result, OutputInterface $output, string $format = 'table' ): void {
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

	private function render_table( DiffResult $result, OutputInterface $output ): void {
		$this->render_symbol_table( $result, $output );
		$output->writeln( '' );
		$this->render_hook_table( $result, $output );
		$output->writeln( '' );
		$this->render_summary( $result, $output );
	}

	private function render_symbol_table( DiffResult $result, OutputInterface $output ): void {
		if ( empty( $result->symbols->removed ) && empty( $result->symbols->added ) ) {
			$output->writeln( '<info>No symbol changes detected.</info>' );
			return;
		}

		if ( ! empty( $result->symbols->removed ) ) {
			$output->writeln( '<error>Removed Symbols</error>' );
			$table = new Table( $output );
			$table->setHeaders( [ 'Type', 'Name', 'File', 'Line' ] );

			foreach ( $result->symbols->removed as $symbol ) {
				$table->addRow( [
					$symbol->type,
					$symbol->get_key(),
					$symbol->file,
					$symbol->line,
				] );
			}

			$table->render();
			$output->writeln( '' );
		}

		if ( ! empty( $result->symbols->added ) ) {
			$output->writeln( '<info>Added Symbols</info>' );
			$table = new Table( $output );
			$table->setHeaders( [ 'Type', 'Name', 'File', 'Line' ] );

			foreach ( $result->symbols->added as $symbol ) {
				$table->addRow( [
					$symbol->type,
					$symbol->get_key(),
					$symbol->file,
					$symbol->line,
				] );
			}

			$table->render();
		}
	}

	private function render_hook_table( DiffResult $result, OutputInterface $output ): void {
		if ( empty( $result->hooks->removed ) && empty( $result->hooks->added ) ) {
			$output->writeln( '<info>No hook changes detected.</info>' );
			return;
		}

		if ( ! empty( $result->hooks->removed ) ) {
			$output->writeln( '<error>Removed Hooks</error>' );
			$table = new Table( $output );
			$table->setHeaders( [ 'Type', 'Name', 'File', 'Line' ] );

			foreach ( $result->hooks->removed as $hook ) {
				$table->addRow( [
					$hook->type,
					$hook->name,
					$hook->file,
					$hook->line,
				] );
			}

			$table->render();
			$output->writeln( '' );
		}

		if ( ! empty( $result->hooks->added ) ) {
			$output->writeln( '<info>Added Hooks</info>' );
			$table = new Table( $output );
			$table->setHeaders( [ 'Type', 'Name', 'File', 'Line' ] );

			foreach ( $result->hooks->added as $hook ) {
				$table->addRow( [
					$hook->type,
					$hook->name,
					$hook->file,
					$hook->line,
				] );
			}

			$table->render();
		}
	}

	private function render_summary( DiffResult $result, OutputInterface $output ): void {
		$removed_symbols = count( $result->symbols->removed );
		$added_symbols   = count( $result->symbols->added );
		$removed_hooks   = count( $result->hooks->removed );
		$added_hooks     = count( $result->hooks->added );

		$output->writeln( sprintf(
			'Symbols: <error>%d removed</error>, <info>%d added</info>',
			$removed_symbols,
			$added_symbols
		) );
		$output->writeln( sprintf(
			'Hooks: <error>%d removed</error>, <info>%d added</info>',
			$removed_hooks,
			$added_hooks
		) );

		if ( $result->has_removals() ) {
			$output->writeln( '' );
			$output->writeln( '<error>Breaking changes detected!</error>' );
		} else {
			$output->writeln( '' );
			$output->writeln( '<info>No breaking changes detected.</info>' );
		}
	}

	private function render_json( DiffResult $result, OutputInterface $output ): void {
		$data = [
			'symbols' => [
				'removed' => array_map( [ $this, 'symbol_to_array' ], $result->symbols->removed ),
				'added'   => array_map( [ $this, 'symbol_to_array' ], $result->symbols->added ),
			],
			'hooks'   => [
				'removed' => array_map( [ $this, 'hook_to_array' ], $result->hooks->removed ),
				'added'   => array_map( [ $this, 'hook_to_array' ], $result->hooks->added ),
			],
			'summary' => [
				'has_breaking_changes' => $result->has_removals(),
				'removed_symbols'      => count( $result->symbols->removed ),
				'added_symbols'        => count( $result->symbols->added ),
				'removed_hooks'        => count( $result->hooks->removed ),
				'added_hooks'          => count( $result->hooks->added ),
			],
		];

		$output->writeln( json_encode( $data, JSON_PRETTY_PRINT ) );
	}

	private function render_github( DiffResult $result, OutputInterface $output ): void {
		foreach ( $result->symbols->removed as $symbol ) {
			$output->writeln( sprintf(
				'::error file=%s,line=%d::Removed %s: %s',
				$symbol->file,
				$symbol->line,
				$symbol->type,
				$symbol->get_key()
			) );
		}

		foreach ( $result->hooks->removed as $hook ) {
			$output->writeln( sprintf(
				'::error file=%s,line=%d::Removed %s hook: %s',
				$hook->file,
				$hook->line,
				$hook->type,
				$hook->name
			) );
		}

		foreach ( $result->symbols->added as $symbol ) {
			$output->writeln( sprintf(
				'::notice file=%s,line=%d::Added %s: %s',
				$symbol->file,
				$symbol->line,
				$symbol->type,
				$symbol->get_key()
			) );
		}

		foreach ( $result->hooks->added as $hook ) {
			$output->writeln( sprintf(
				'::notice file=%s,line=%d::Added %s hook: %s',
				$hook->file,
				$hook->line,
				$hook->type,
				$hook->name
			) );
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	private function symbol_to_array( SymbolInfo $symbol ): array {
		return [
			'name'         => $symbol->get_key(),
			'type'         => $symbol->type,
			'file'         => $symbol->file,
			'line'         => $symbol->line,
			'visibility'   => $symbol->visibility,
			'parent_class' => $symbol->parent_class,
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	private function hook_to_array( HookInfo $hook ): array {
		return [
			'name'       => $hook->name,
			'type'       => $hook->type,
			'file'       => $hook->file,
			'line'       => $hook->line,
			'is_dynamic' => $hook->is_dynamic,
			'arg_count'  => $hook->arg_count,
		];
	}
}
