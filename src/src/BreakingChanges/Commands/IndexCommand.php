<?php

namespace QIT_CLI\BreakingChanges\Commands;

use QIT_CLI\BreakingChanges\Extraction\DirectoryExtractor;
use QIT_CLI\BreakingChanges\Models\ExtractedSymbols;
use QIT_CLI\BreakingChanges\Models\HookInfo;
use QIT_CLI\BreakingChanges\PluginSourceResolver;
use QIT_CLI\RequestBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function QIT_CLI\get_manager_url;

class IndexCommand extends Command {
	protected static $defaultName = 'breaking-changes:index'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	private PluginSourceResolver $resolver;
	private DirectoryExtractor $extractor;

	public function __construct(
		PluginSourceResolver $resolver,
		DirectoryExtractor $extractor
	) {
		$this->resolver  = $resolver;
		$this->extractor = $extractor;

		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setDescription( 'Extract hooks from a plugin and upload to the hook index.' )
			->setHelp( 'Resolves a plugin, extracts hook definitions and references, and POSTs them to the Manager hook index endpoint.' )
			->addArgument( 'slug', InputArgument::REQUIRED, 'Plugin slug (WPORG) or local path' )
			->addOption( 'version', null, InputOption::VALUE_REQUIRED, 'Plugin version (default: latest)' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$slug    = $input->getArgument( 'slug' );
		$version = $input->getOption( 'version' );

		$output->writeln( sprintf( 'Resolving %s%s...', $slug, $version ? "@{$version}" : '' ) );

		try {
			$plugin_path = $this->resolver->resolve( $slug, $version );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Failed to resolve plugin: %s</error>', $e->getMessage() ) );
			return Command::FAILURE;
		}

		$output->writeln( 'Extracting symbols...' );
		$symbols = $this->extractor->extract( $plugin_path );

		$definitions = $this->build_definitions_payload( $symbols );
		$references  = $this->build_references_payload( $symbols );

		$resolved_version = $version ?? 'latest';

		$output->writeln( sprintf(
			'Found %d hook definitions, %d hook references. Uploading...',
			count( $definitions ),
			count( $references )
		) );

		try {
			$this->upload_to_index( $slug, $resolved_version, $definitions, $references );
		} catch ( \Exception $e ) {
			$output->writeln( sprintf( '<error>Upload failed: %s</error>', $e->getMessage() ) );
			return Command::FAILURE;
		}

		$output->writeln( '<info>Hook index updated successfully.</info>' );

		return Command::SUCCESS;
	}

	/**
	 * Build hook definitions payload from extracted symbols.
	 *
	 * @return array<array<string, mixed>>
	 */
	private function build_definitions_payload( ExtractedSymbols $symbols ): array {
		$payload = [];

		foreach ( $symbols->hooks as $hook ) {
			$payload[] = [
				'hook_name'   => $hook->name,
				'hook_type'   => $hook->type,
				'file_path'   => $hook->file,
				'line_number' => $hook->line,
				'arg_count'   => $hook->arg_count,
				'is_dynamic'  => $hook->is_dynamic ? 1 : 0,
			];
		}

		return $payload;
	}

	/**
	 * Build hook references payload.
	 * For now, this scans for add_action/add_filter/remove_action/remove_filter calls
	 * that reference hooks defined in other plugins.
	 *
	 * @return array<array<string, mixed>>
	 */
	private function build_references_payload( ExtractedSymbols $symbols ): array {
		// Hook references are extracted by the HookVisitor as part of the same
		// extraction pass. For the index, we include all hooks as both definitions
		// (do_action/apply_filters) and potential references (the hooks this plugin
		// uses from other plugins are captured separately by ReferenceScanner).
		// For simplicity, we return an empty array here — the ReferenceScanner
		// workflow will populate references via a separate pass.
		return [];
	}

	/**
	 * @param array<array<string, mixed>> $definitions
	 * @param array<array<string, mixed>> $references
	 */
	private function upload_to_index( string $slug, string $version, array $definitions, array $references ): void {
		$url = get_manager_url() . '/wp-json/cd/v1/hook-index';

		$response = ( new RequestBuilder( $url ) )
			->with_method( 'POST' )
			->with_post_body( [
				'plugin_slug'    => $slug,
				'plugin_version' => $version,
				'definitions'    => $definitions,
				'references'     => $references,
			] )
			->request();

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) || empty( $data['success'] ) ) {
			throw new \RuntimeException( 'Hook index ingest returned unexpected response.' );
		}
	}
}
