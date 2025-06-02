<?php

namespace QIT_CLI\PreCommand\Extension;

use QIT_CLI\App;
use QIT_CLI\Cache;
use QIT_CLI\Environment\Extension;
use QIT_CLI\Environment\Environments\EnvInfo;
use QIT_CLI\WooExtensionsList;
use QIT_CLI\WPORGExtensionsList;

/**
 * Main extension resolver that orchestrates the resolution process.
 */
class ExtensionResolver {
	/** @var ExtensionMetadataFetcher */
	protected $metadata_fetcher;

	/** @var DependencyResolver */
	protected $dependency_resolver;

	/** @var ExtensionCacheManager */
	protected $cache_manager;

	/** @var WooExtensionsList */
	protected $woo_extensions_list;

	/** @var WPORGExtensionsList */
	protected $wporg_extensions_list;

	public function __construct(
		ExtensionMetadataFetcher $metadata_fetcher,
		DependencyResolver $dependency_resolver,
		ExtensionCacheManager $cache_manager,
		WooExtensionsList $woo_extensions_list,
		WPORGExtensionsList $wporg_extensions_list
	) {
		$this->metadata_fetcher      = $metadata_fetcher;
		$this->dependency_resolver   = $dependency_resolver;
		$this->cache_manager         = $cache_manager;
		$this->woo_extensions_list   = $woo_extensions_list;
		$this->wporg_extensions_list = $wporg_extensions_list;
	}

	/**
	 * Main entry point for resolving extensions.
	 *
	 * @param Extension[] $extensions Initial list of extensions to resolve
	 * @param EnvInfo $env_info Environment information
	 * @param string $cache_dir Cache directory path
	 *
	 * @return ResolvedExtensions
	 * @throws \RuntimeException If resolution fails
	 */
	public function resolve( array $extensions, EnvInfo $env_info, string $cache_dir ): ResolvedExtensions {
		file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionResolver: Starting resolution for " . count( $extensions ) . " extensions\n", FILE_APPEND );

		$resolved = new ResolvedExtensions();
		$pending  = $extensions;
		$seen     = [];

		// Process extensions and their dependencies iteratively
		while ( ! empty( $pending ) ) {
			$current_batch = $pending;
			$pending       = [];

			foreach ( $current_batch as $extension ) {
				if ( in_array( $extension->slug, $seen, true ) ) {
					continue;
				}
				$seen[] = $extension->slug;

				// Step 1: Resolve source if not fully resolved
				if ( ! $this->is_source_resolved( $extension ) ) {
					$this->resolve_extension_source( $extension );
				}

				// Step 2: Fetch metadata (version, download URL, etc.)
				try {
					$this->metadata_fetcher->fetch_metadata( [ $extension ] );
				} catch ( \RuntimeException $e ) {
					// If WPORG metadata fetch fails, retry with source resolution
					if ( $extension->from === 'wporg' ) {
						file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionResolver: WPORG metadata fetch failed for '{$extension->slug}': {$e->getMessage()}. Retrying source resolution.\n", FILE_APPEND );
						$extension->from = null; // Reset source to force re-resolution
						$this->resolve_extension_source( $extension );
						$this->metadata_fetcher->fetch_metadata( [ $extension ] );
					} else {
						throw $e; // Rethrow for other failures
					}
				}

				// Step 3: Check cache and download if needed
				$this->cache_manager->ensure_cached( $extension, $cache_dir );

				// Step 4: Add to resolved collection
				$resolved->add_extension( $extension );

				// Step 5: Parse dependencies
				if ( ! empty( $extension->downloaded_source ) ) {
					$dependencies = $this->dependency_resolver->resolve_dependencies( $extension );
					foreach ( $dependencies as $dep ) {
						if ( ! in_array( $dep->slug, $seen, true ) ) {
							$pending[] = $dep;
						}
					}
				}
			}
		}

		file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionResolver: Resolved " . $resolved->count() . " total extensions\n", FILE_APPEND );

		return $resolved;
	}

	/**
	 * Check if extension source is fully resolved.
	 */
	protected function is_source_resolved( Extension $extension ): bool {
		if ( empty( $extension->from ) ) {
			return false;
		}

		// Non-remote sources are resolved if 'from' is set
		if ( in_array( $extension->from, [ 'directory', 'zip', 'url', 'build' ], true ) ) {
			return true;
		}

		// Remote sources (wporg, wccom) require metadata
		return in_array( $extension->from, [ 'wporg', 'wccom' ], true ) && ! empty( $extension->source ) && ! empty( $extension->version );
	}

	/**
	 * Resolve extension source by querying marketplaces.
	 */
	protected function resolve_extension_source( Extension $extension ): void {
		file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionResolver: Resolving source for '{$extension->slug}'\n", FILE_APPEND );

		// Check WPORG first
		try {
			if ( $extension->type === 'plugin' && $this->wporg_extensions_list->is_wporg_plugin( $extension->slug ) ) {
				$extension->from = 'wporg';
				file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionResolver: Found '{$extension->slug}' on WPORG\n", FILE_APPEND );

				return;
			}
			if ( $extension->type === 'theme' && $this->wporg_extensions_list->is_wporg_theme( $extension->slug ) ) {
				$extension->from = 'wporg';
				file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionResolver: Found '{$extension->slug}' on WPORG\n", FILE_APPEND );

				return;
			}
		} catch ( \Exception $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionResolver: WPORG check failed for '{$extension->slug}': {$e->getMessage()}\n", FILE_APPEND );
			// Continue to WCCOM check
		}

		// Check WCCOM
		try {
			$extension->wccom_id = $this->woo_extensions_list->get_woo_extension_id_by_slug( $extension->slug );
			$extension->from     = 'wccom';
			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionResolver: Found '{$extension->slug}' on WCCOM\n", FILE_APPEND );

			return;
		} catch ( \UnexpectedValueException $e ) {
			file_put_contents( '/tmp/qit/qit_debug.log', "ExtensionResolver: WCCOM check failed for '{$extension->slug}': {$e->getMessage()}\n", FILE_APPEND );
			// Continue to local checks
		}

		// Check if it's a local extension
		if ( ! empty( $extension->directory ) && is_dir( $extension->directory ) ) {
			$extension->from = 'directory';

			return;
		}

		if ( ! empty( $extension->source ) ) {
			if ( is_file( $extension->source ) && pathinfo( $extension->source, PATHINFO_EXTENSION ) === 'zip' ) {
				$extension->from = 'zip';

				return;
			}
			if ( filter_var( $extension->source, FILTER_VALIDATE_URL ) ) {
				$extension->from = 'url';

				return;
			}
		}

		throw new \RuntimeException( "Could not resolve source for extension '{$extension->slug}' ({$extension->type}). Not found in WPORG or WCCOM." );
	}
}

/**
 * Container for resolved extensions.
 */
class ResolvedExtensions {
	/** @var Extension[] */
	protected $plugins = [];

	/** @var Extension[] */
	protected $themes = [];

	/** @var string[] */
	protected $php_extensions = [];

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
}