<?php

namespace QIT_CLI\PreCommand\Extension\Sources;

use QIT_CLI\Environment\Extension;

/**
 * Interface for extension source resolvers.
 */
interface SourceResolverInterface {
	/**
	 * Check if this resolver can handle the given extension.
	 *
	 * @param Extension $extension
	 *
	 * @return bool
	 */
	public function can_resolve( Extension $extension ): bool;

	/**
	 * Resolve the extension source and populate metadata.
	 *
	 * @param Extension $extension
	 *
	 * @throws \RuntimeException If resolution fails
	 */
	public function resolve( Extension $extension ): void;

	/**
	 * Get the source type this resolver handles.
	 *
	 * @return string
	 */
	public function get_source_type(): string;
}