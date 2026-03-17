<?php

namespace QIT_CLI\BreakingChanges\Commands;

use QIT_CLI\BreakingChanges\Diff\HookDiffer;
use QIT_CLI\BreakingChanges\Diff\SymbolDiffer;
use QIT_CLI\BreakingChanges\Extraction\DirectoryExtractor;
use QIT_CLI\BreakingChanges\Models\DiffResult;
use QIT_CLI\BreakingChanges\PluginSourceResolver;
use QIT_CLI\BreakingChanges\Renderers\DiffRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends Command {
	protected static $defaultName = 'breaking-changes:diff'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	private PluginSourceResolver $resolver;
	private DirectoryExtractor $extractor;
	private SymbolDiffer $symbol_differ;
	private HookDiffer $hook_differ;
	private DiffRenderer $renderer;

	public function __construct(
		PluginSourceResolver $resolver,
		DirectoryExtractor $extractor,
		SymbolDiffer $symbol_differ,
		HookDiffer $hook_differ,
		DiffRenderer $renderer
	) {
		$this->resolver      = $resolver;
		$this->extractor     = $extractor;
		$this->symbol_differ = $symbol_differ;
		$this->hook_differ   = $hook_differ;
		$this->renderer      = $renderer;

		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setDescription( 'Diff two versions of a plugin to detect breaking changes.' )
			->setHelp( 'Compares the public API surface (classes, functions, hooks, constants) between two plugin versions.' )
			->addArgument( 'slug', InputArgument::REQUIRED, 'Plugin slug (WPORG) or local path' )
			->addOption( 'old', null, InputOption::VALUE_REQUIRED, 'Old version number or path' )
			->addOption( 'new', null, InputOption::VALUE_REQUIRED, 'New version number or path (default: latest)' )
			->addOption( 'format', null, InputOption::VALUE_REQUIRED, 'Output format: table, json, github', 'table' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$slug       = $input->getArgument( 'slug' );
		$old_version = $input->getOption( 'old' );
		$new_version = $input->getOption( 'new' );
		$format     = $input->getOption( 'format' );

		if ( empty( $old_version ) ) {
			$output->writeln( '<error>The --old option is required.</error>' );
			return Command::FAILURE;
		}

		$output->writeln( sprintf( 'Resolving old version (%s)...', $old_version ), OutputInterface::VERBOSITY_VERBOSE );
		$old_path = $this->resolve_source( $slug, $old_version );

		$output->writeln( sprintf( 'Resolving new version (%s)...', $new_version ?? 'latest' ), OutputInterface::VERBOSITY_VERBOSE );
		$new_path = $this->resolve_source( $slug, $new_version );

		$output->writeln( 'Extracting symbols from old version...', OutputInterface::VERBOSITY_VERBOSE );
		$old_symbols = $this->extractor->extract( $old_path );

		$output->writeln( 'Extracting symbols from new version...', OutputInterface::VERBOSITY_VERBOSE );
		$new_symbols = $this->extractor->extract( $new_path );

		$output->writeln( 'Diffing...', OutputInterface::VERBOSITY_VERBOSE );
		$symbol_diff = $this->symbol_differ->diff( $old_symbols, $new_symbols );
		$hook_diff   = $this->hook_differ->diff( $old_symbols, $new_symbols );

		$result = new DiffResult( $symbol_diff, $hook_diff );

		$this->renderer->render( $result, $output, $format );

		return $result->has_removals() ? Command::FAILURE : Command::SUCCESS;
	}

	/**
	 * Resolve a version string or local path to a plugin directory.
	 * If the value is a local directory or zip, use it directly.
	 * Otherwise, treat it as a version and resolve via slug.
	 */
	private function resolve_source( string $slug, ?string $version_or_path ): string {
		if ( $version_or_path !== null && ( is_dir( $version_or_path ) || is_file( $version_or_path ) ) ) {
			return $this->resolver->resolve( $version_or_path );
		}

		return $this->resolver->resolve( $slug, $version_or_path );
	}
}
