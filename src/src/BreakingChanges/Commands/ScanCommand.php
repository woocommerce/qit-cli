<?php

namespace QIT_CLI\BreakingChanges\Commands;

use QIT_CLI\BreakingChanges\Diff\HookDiffer;
use QIT_CLI\BreakingChanges\Diff\SymbolDiffer;
use QIT_CLI\BreakingChanges\Extraction\DirectoryExtractor;
use QIT_CLI\BreakingChanges\Models\HookDiffResult;
use QIT_CLI\BreakingChanges\Models\ScanResult;
use QIT_CLI\BreakingChanges\Models\SymbolDiffResult;
use QIT_CLI\BreakingChanges\PluginSourceResolver;
use QIT_CLI\BreakingChanges\Renderers\ScanRenderer;
use QIT_CLI\BreakingChanges\Scanner\ReferenceScanner;
use QIT_CLI\BreakingChanges\WooDevelopedFetcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScanCommand extends Command {
	protected static $defaultName = 'breaking-changes:scan'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	private PluginSourceResolver $resolver;
	private DirectoryExtractor $extractor;
	private SymbolDiffer $symbol_differ;
	private HookDiffer $hook_differ;
	private ReferenceScanner $scanner;
	private ScanRenderer $renderer;
	private ?WooDevelopedFetcher $woo_developed_fetcher;

	public function __construct(
		PluginSourceResolver $resolver,
		DirectoryExtractor $extractor,
		SymbolDiffer $symbol_differ,
		HookDiffer $hook_differ,
		ReferenceScanner $scanner,
		ScanRenderer $renderer,
		?WooDevelopedFetcher $woo_developed_fetcher = null
	) {
		$this->resolver              = $resolver;
		$this->extractor             = $extractor;
		$this->symbol_differ         = $symbol_differ;
		$this->hook_differ           = $hook_differ;
		$this->scanner               = $scanner;
		$this->renderer              = $renderer;
		$this->woo_developed_fetcher = $woo_developed_fetcher;

		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setDescription( 'Scan a plugin for references to breaking changes in a dependency.' )
			->setHelp( 'Diffs a dependency between two versions, then scans the target plugin for references to removed symbols and hooks.' )
			->addArgument( 'target', InputArgument::OPTIONAL, 'Plugin slug or path to scan for breaking references' )
			->addOption( 'dependency', null, InputOption::VALUE_REQUIRED, 'Dependency plugin slug or path' )
			->addOption( 'old', null, InputOption::VALUE_REQUIRED, 'Old version of the dependency' )
			->addOption( 'new', null, InputOption::VALUE_REQUIRED, 'New version of the dependency (default: latest)' )
			->addOption( 'check-against', null, InputOption::VALUE_REQUIRED, 'Scan multiple plugins: "woo-developed" or comma-separated slugs' )
			->addOption( 'format', null, InputOption::VALUE_REQUIRED, 'Output format: table, json, github', 'table' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$target        = $input->getArgument( 'target' );
		$dependency    = $input->getOption( 'dependency' );
		$old_version   = $input->getOption( 'old' );
		$new_version   = $input->getOption( 'new' );
		$check_against = $input->getOption( 'check-against' );
		$format        = $input->getOption( 'format' );

		if ( empty( $dependency ) ) {
			$output->writeln( '<error>The --dependency option is required.</error>' );
			return Command::FAILURE;
		}

		if ( empty( $old_version ) ) {
			$output->writeln( '<error>The --old option is required.</error>' );
			return Command::FAILURE;
		}

		if ( empty( $target ) && empty( $check_against ) ) {
			$output->writeln( '<error>Either a target argument or --check-against option is required.</error>' );
			return Command::FAILURE;
		}

		// Step 1: Resolve and diff the dependency.
		$output->writeln( 'Resolving dependency versions...', OutputInterface::VERBOSITY_VERBOSE );
		$old_dep_path = $this->resolve_source( $dependency, $old_version );
		$new_dep_path = $this->resolve_source( $dependency, $new_version );

		$output->writeln( 'Extracting symbols from dependency...', OutputInterface::VERBOSITY_VERBOSE );
		$old_symbols = $this->extractor->extract( $old_dep_path );
		$new_symbols = $this->extractor->extract( $new_dep_path );

		$symbol_diff = $this->symbol_differ->diff( $old_symbols, $new_symbols );
		$hook_diff   = $this->hook_differ->diff( $old_symbols, $new_symbols );

		if ( ! $symbol_diff->has_removals() && ! $hook_diff->has_removals() ) {
			$output->writeln( '<info>No breaking changes in dependency. Nothing to scan.</info>' );
			return Command::SUCCESS;
		}

		$output->writeln( sprintf(
			'Found %d removed symbol(s) and %d removed hook(s) in dependency.',
			count( $symbol_diff->removed ),
			count( $hook_diff->removed )
		), OutputInterface::VERBOSITY_VERBOSE );

		// Step 2: Determine which plugins to scan.
		if ( ! empty( $check_against ) ) {
			try {
				return $this->scan_multiple( $check_against, $symbol_diff, $hook_diff, $dependency, $output, $format );
			} catch ( \RuntimeException $e ) {
				$output->writeln( sprintf( '<error>%s</error>', $e->getMessage() ) );
				return Command::FAILURE;
			}
		}

		// Single target scan.
		$output->writeln( 'Scanning target plugin...', OutputInterface::VERBOSITY_VERBOSE );
		$target_path = $this->resolve_source( $target, null );

		$result = $this->scanner->scan( $target_path, $symbol_diff, $hook_diff, $this->get_slug( $target ) );

		$this->renderer->render( $result, $output, $format );

		return $result->has_breaking_references() ? Command::FAILURE : Command::SUCCESS;
	}

	/**
	 * Scan multiple plugins against the diff result.
	 */
	private function scan_multiple(
		string $check_against,
		SymbolDiffResult $symbol_diff,
		HookDiffResult $hook_diff,
		string $dependency,
		OutputInterface $output,
		string $format
	): int {
		$slugs = $this->resolve_check_against( $check_against, $dependency );

		if ( empty( $slugs ) ) {
			$output->writeln( '<comment>No plugins to scan.</comment>' );
			return Command::SUCCESS;
		}

		if ( $format !== 'json' ) {
			$output->writeln( sprintf( 'Scanning %d plugin(s)...', count( $slugs ) ) );
		}

		$results     = [];
		$has_failure = false;

		foreach ( $slugs as $slug ) {
			$output->writeln( sprintf( '  Scanning %s...', $slug ), OutputInterface::VERBOSITY_VERBOSE );

			try {
				$target_path = $this->resolver->resolve( $slug );
				$result      = $this->scanner->scan( $target_path, $symbol_diff, $hook_diff, $slug );
				$results[]   = $result;

				if ( $result->has_breaking_references() ) {
					$has_failure = true;
				}
			} catch ( \Exception $e ) {
				$output->writeln( sprintf( '  <comment>Skipping %s: %s</comment>', $slug, $e->getMessage() ) );
				$results[] = new ScanResult( $slug, [], [ $e->getMessage() ] );
			}
		}

		$this->renderer->render_multi( $results, $output, $format );

		return $has_failure ? Command::FAILURE : Command::SUCCESS;
	}

	/**
	 * Resolve --check-against value to a list of plugin slugs.
	 *
	 * @return string[]
	 */
	private function resolve_check_against( string $value, string $dependency ): array {
		if ( strtolower( $value ) === 'woo-developed' ) {
			if ( $this->woo_developed_fetcher === null ) {
				throw new \RuntimeException( 'Cannot use --check-against=woo-developed: not connected to QIT backend.' );
			}

			$slugs = $this->woo_developed_fetcher->fetch();

			// Exclude the dependency itself from the scan list.
			$dep_slug = $this->get_slug( $dependency );
			return array_values( array_filter( $slugs, function ( string $slug ) use ( $dep_slug ) {
				return $slug !== $dep_slug;
			} ) );
		}

		// Comma-separated slugs.
		$slugs = array_map( 'trim', explode( ',', $value ) );
		return array_filter( $slugs, function ( string $slug ) {
			return $slug !== '';
		} );
	}

	private function resolve_source( string $slug, ?string $version_or_path ): string {
		if ( $version_or_path !== null && ( is_dir( $version_or_path ) || is_file( $version_or_path ) ) ) {
			return $this->resolver->resolve( $version_or_path );
		}

		return $this->resolver->resolve( $slug, $version_or_path );
	}

	private function get_slug( string $slug_or_path ): string {
		if ( is_dir( $slug_or_path ) || is_file( $slug_or_path ) ) {
			return basename( $slug_or_path );
		}

		return $slug_or_path;
	}
}
