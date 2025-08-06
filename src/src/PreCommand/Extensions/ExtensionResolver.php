<?php

namespace QIT_CLI\PreCommand\Extensions;

use QIT_CLI\PreCommand\Objects\Extension;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;
use function QIT_CLI\debug_log;

/**
 * Main extension resolver that orchestrates the resolution process.
 */
class ExtensionResolver {
	protected ExtensionMetadataFetcher $metadata_fetcher;

	protected DependencyResolver $dependency_resolver;

	protected ExtensionCacheManager $cache_manager;

	protected WooExtensionsList $woo_extensions_list;

	protected WPORGExtensionsList $wporg_extensions_list;

	protected VersionResolver $version_resolver;

	public function __construct(
		ExtensionMetadataFetcher $metadata_fetcher,
		DependencyResolver $dependency_resolver,
		ExtensionCacheManager $cache_manager,
		WooExtensionsList $woo_extensions_list,
		WPORGExtensionsList $wporg_extensions_list,
		VersionResolver $version_resolver
	) {
		$this->metadata_fetcher      = $metadata_fetcher;
		$this->dependency_resolver   = $dependency_resolver;
		$this->cache_manager         = $cache_manager;
		$this->woo_extensions_list   = $woo_extensions_list;
		$this->wporg_extensions_list = $wporg_extensions_list;
		$this->version_resolver      = $version_resolver;
	}

	/**
	 * Main entry point for resolving extensions.
	 *
	 * @param Extension[] $extensions Initial list of extensions to resolve.
	 * @param string      $cache_dir Cache directory path.
	 *
	 * @return ResolvedExtensions
	 * @throws \RuntimeException If resolution fails.
	 */
	public function resolve( array $extensions, string $cache_dir ): ResolvedExtensions {
		debug_log( 'ExtensionResolver: Starting resolution for ' . count( $extensions ) . ' extensions' );

		$resolved = new ResolvedExtensions();
		$pending  = $extensions;
		$seen     = [];

		// Process extensions and their dependencies iteratively
		while ( ! empty( $pending ) ) {
			$current_batch = $pending;
			$pending       = [];

			foreach ( $current_batch as $extension ) {
				debug_log( "ExtensionResolver: Processing extension: {$extension->slug} ({$extension->type}) from {$extension->from}" );

				if ( in_array( $extension->slug, $seen, true ) ) {
					debug_log( "  Skipping already seen extension: {$extension->slug}" );
					continue;
				}
				$seen[] = $extension->slug;

				// Step 1: Resolve source if not fully resolved
				if ( ! $this->is_source_resolved( $extension ) ) {
					debug_log( '  Extension source not fully resolved, resolving...' );
					$this->resolve_extension_source( $extension );
				}

				// Step 2: Fetch metadata (version, download URL, etc.)
				try {
					if ( in_array( $extension->from, [ 'wporg', 'wccom' ], true ) ) {
						debug_log( '  Fetching metadata for remote extension' );
						$this->metadata_fetcher->fetch_metadata( [ $extension ] );
					}
				} catch ( \RuntimeException $e ) {
					// If WPORG metadata fetch fails, retry with source resolution
					if ( $extension->from === 'wporg' ) {
						debug_log( "  WPORG metadata fetch failed: {$e->getMessage()}. Retrying source resolution.", 'error' );
						$extension->from = null; // Reset source to force re-resolution
						$this->resolve_extension_source( $extension );
						$this->metadata_fetcher->fetch_metadata( [ $extension ] );
					} else {
						throw $e; // Rethrow for other failures
					}
				}

				// Step 3: Check cache and download if needed
				debug_log( '  Ensuring extension is cached' );
				$this->cache_manager->ensure_cached( $extension, $cache_dir );

				// Step 4: Add to resolved collection
				$resolved->add_extension( $extension );
				debug_log( '  Added extension to resolved collection' );

				// Step 5: Parse dependencies
				if ( ! empty( $extension->downloaded_source ) ) {
					debug_log( '  Checking for dependencies' );
					$dependencies = $this->dependency_resolver->resolve_dependencies( $extension );
					foreach ( $dependencies as $dep ) {
						if ( ! in_array( $dep->slug, $seen, true ) ) {
							debug_log( "    Found dependency: {$dep->slug}" );
							$pending[] = $dep;
						}
					}
				}
			}
		}

		debug_log( 'ExtensionResolver: Resolved ' . $resolved->count() . ' total extensions' );

		return $resolved;
	}

	/**
	 * Check if extension source is fully resolved.
	 */
	protected function is_source_resolved( Extension $extension ): bool {
		if ( empty( $extension->from ) ) {
			return false;
		}

		// Local, url, and build sources are resolved if 'from' is set
		if ( in_array( $extension->from, [ 'local', 'url', 'build' ], true ) ) {
			return true;
		}

		// For remote sources, check if we have the necessary info
		if ( $extension->from === 'wporg' ) {
			// For WPORG, we just need the version - but special tags like nightly/rc require further resolution.
			$version_lower = strtolower( (string) $extension->version );
			$special_tag   = in_array( $version_lower, [ 'nightly', 'rc' ], true ) || str_contains( $version_lower, '-rc' );

			return ! empty( $extension->version ) && ! $special_tag;
		}

		if ( $extension->from === 'wccom' ) {
			// For WCCOM, we need the version or ID
			return ! empty( $extension->version ) || ! empty( $extension->wccom_id );
		}

		return false;
	}

	/**
	 * Resolve extension source by querying marketplaces.
	 */
	protected function resolve_extension_source( Extension $extension ): void {
		debug_log( "ExtensionResolver: Resolving source for '{$extension->slug}'" );

		// Handle explicit source types
		if ( ! empty( $extension->from ) ) {
			if ( in_array( $extension->from, [ 'local', 'url', 'build' ], true ) ) {
				debug_log( "  Extension has explicit local/non-remote source: {$extension->from}" );

				return; // Local sources are resolved
			}
			if ( in_array( $extension->from, [ 'wporg', 'wccom' ], true ) && ! empty( $extension->source ) && ! empty( $extension->version ) ) {
				debug_log( '  Extension has complete remote source information' );

				return; // Remote sources with metadata are resolved
			}
		}

		// Version resolution is now handled explicitly in UpEnvironmentCommand
		// ExtensionResolver only handles explicit sources - no magic resolution

		// Infer source for unspecified or incomplete sources (e.g., SUT without source)
		debug_log( '  Attempting to infer source from marketplaces' );

		try {
			if ( $extension->type === 'plugin' && $this->wporg_extensions_list->is_wporg_plugin( $extension->slug ) ) {
				$extension->from = 'wporg';
				debug_log( "  Found '{$extension->slug}' on WordPress.org (plugin)" );

				return;
			}
			if ( $extension->type === 'theme' && $this->wporg_extensions_list->is_wporg_theme( $extension->slug ) ) {
				$extension->from = 'wporg';
				debug_log( "  Found '{$extension->slug}' on WordPress.org (theme)" );

				return;
			}
		} catch ( \Exception $e ) {
			debug_log( "  WordPress.org check failed: {$e->getMessage()}", 'error' );
		}

		try {
			$extension->wccom_id = $this->woo_extensions_list->get_woo_extension_id_by_slug( $extension->slug );
			$extension->from     = 'wccom';
			debug_log( "  Found '{$extension->slug}' on WooCommerce.com" );

			return;
		} catch ( \UnexpectedValueException $e ) {
			debug_log( "  WooCommerce.com check failed: {$e->getMessage()}", 'error' );
		}

		// Check local sources
		if ( ! empty( $extension->directory ) || ! empty( $extension->source ) ) {
			debug_log( '  Checking local sources' );

			// Check if it's a directory
			if ( ! empty( $extension->directory ) && is_dir( $extension->directory ) ) {
				$extension->from = 'local';
				debug_log( "  Found local directory: {$extension->directory}" );

				return;
			}

			// Check if source is a directory
			if ( ! empty( $extension->source ) && is_string( $extension->source ) && is_dir( $extension->source ) ) {
				$extension->from      = 'local';
				$extension->directory = $extension->source;
				debug_log( "  Found local directory (from source): {$extension->source}" );

				return;
			}

			// Check if it's a local zip file
			if ( ! empty( $extension->source ) && is_string( $extension->source ) && is_file( $extension->source ) && pathinfo( $extension->source, PATHINFO_EXTENSION ) === 'zip' ) {
				$extension->from = 'local';
				debug_log( "  Found local zip file: {$extension->source}" );

				return;
			}

			// Check if it's a URL
			if ( ! empty( $extension->source ) && filter_var( $extension->source, FILTER_VALIDATE_URL ) ) {
				$extension->from = 'url';
				debug_log( "  Found URL source: {$extension->source}" );

				return;
			}
		}

		throw new \RuntimeException( "Could not resolve source for extension '{$extension->slug}' ({$extension->type}). Not found in WPORG, WCCOM, or local sources." );
	}
}

/**
 * Container for resolved extensions.
 */
// phpcs:ignore Generic.Files.OneObjectStructurePerFile.MultipleFound
class ResolvedExtensions {
	/** @var Extension[] */
	protected array $plugins = [];

	/** @var Extension[] */
	protected array $themes = [];

	/** @var string[] */
	protected array $php_extensions = [];

	public function add_extension( Extension $extension ): void {
		if ( $extension->type === 'plugin' ) {
			$this->plugins[ $extension->slug ] = $extension;
		} elseif ( $extension->type === 'theme' ) {
			$this->themes[ $extension->slug ] = $extension;
		}
	}

	public function get_plugins(): array {
		return array_values( $this->plugins );
	}

	public function get_themes(): array {
		return array_values( $this->themes );
	}

	public function get_php_extensions(): array {
		return $this->php_extensions;
	}

	public function add_php_extensions( array $extensions ): void {
		$this->php_extensions = array_unique( array_merge( $this->php_extensions, $extensions ) );
	}

	public function count(): int {
		return count( $this->plugins ) + count( $this->themes );
	}

	public function merge( ResolvedExtensions $other ): void {
		foreach ( $other->get_plugins() as $plugin ) {
			$this->add_extension( $plugin );
		}
		foreach ( $other->get_themes() as $theme ) {
			$this->add_extension( $theme );
		}
		$this->add_php_extensions( $other->get_php_extensions() );
	}
}
