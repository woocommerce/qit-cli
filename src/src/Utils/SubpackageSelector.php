<?php

namespace QIT_CLI\Utils;

use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;

/**
 * Validates --subpackage selections against the supplied test packages.
 *
 * The --subpackage option only applies when exactly ONE local test package
 * directory is supplied and no remote package references are present.
 * Any violation is a hard error so the command fails fast, before any
 * expensive work such as environment setup.
 */
class SubpackageSelector {

	/**
	 * Prepare the raw --subpackage option values for validation.
	 *
	 * Empty or whitespace-only values are rejected rather than filtered out, so
	 * that e.g. `--subpackage="$UNSET_VAR"` is rejected rather than being skipped.
	 *
	 * @param array<mixed> $raw_subpackage_ids Raw values of the --subpackage option.
	 *
	 * @return array<string> Unique subpackage IDs, in the order given.
	 * @throws \RuntimeException If any value is empty or whitespace-only.
	 */
	public static function get_requested_ids( array $raw_subpackage_ids ): array {
		foreach ( $raw_subpackage_ids as $subpackage_id ) {
			if ( ! is_string( $subpackage_id ) || trim( $subpackage_id ) === '' ) {
				throw new \RuntimeException(
					"The --subpackage option requires a non-empty subpackage ID.\n" .
					'If the value comes from a variable, make sure it is set.'
				);
			}
		}

		return array_values( array_unique( $raw_subpackage_ids ) );
	}

	/**
	 * Validate a --subpackage selection against the candidate test packages.
	 *
	 * @param array<string> $subpackage_ids Requested subpackage IDs (full IDs, e.g. "namespace/name"). These values are expected to be normalized and unique.
	 * @param array<string> $package_refs   Candidate test package references, with local paths already expanded to absolute paths.
	 *
	 * @return string Absolute path of the single local parent package directory.
	 * @throws \RuntimeException On any violation.
	 */
	public static function validate_selection( array $subpackage_ids, array $package_refs ): string {
		// Classify each reference as a local package directory or a remote reference.
		$local_dirs  = [];
		$remote_refs = [];
		foreach ( $package_refs as $ref ) {
			if ( PackageReferenceUtils::is_local_reference( $ref ) ) {
				$local_dir = PackageReferenceUtils::expand_local_path( $ref );

				if ( ! is_dir( $local_dir ) ) {
					throw new \RuntimeException(
						"The local test package directory {$local_dir} does not exist for package {$ref}."
					);
				}

				if ( ! file_exists( rtrim( $local_dir, '/' ) . '/qit-test.json' ) ) {
					throw new \RuntimeException(
						"The local test package directory {$local_dir} does not contain a qit-test.json file.\n" .
						'Unable to validate the specified subpackages.'
					);
				}

				// Canonicalize so aliases of the same directory (trailing "/.", symlinks, etc.) dedupe correctly.
				$real_dir     = realpath( $local_dir );
				$local_dirs[] = $real_dir !== false ? $real_dir : $local_dir;
			} else {
				$remote_refs[] = $ref;
			}
		}

		if ( ! empty( $remote_refs ) ) {
			throw new \RuntimeException(
				"The --subpackage option can only be used with a single local test package.\n" .
				"Remote test package references are not allowed when using --subpackage:\n" .
				'  - ' . implode( "\n  - ", $remote_refs ) . "\n" .
				"\n" .
				"To run a remote subpackage, reference it directly, e.g.:\n" .
				'  --test-package namespace/package/subpackage:version'
			);
		}

		if ( empty( $local_dirs ) ) {
			throw new \RuntimeException(
				"The --subpackage option requires a local test package.\n" .
				'Provide one with --test-package <dir>, via qit.json, or run from a directory containing qit-test.json.'
			);
		}

		$normalized_local_dirs = array_values( array_unique( $local_dirs ) );
		if ( count( $normalized_local_dirs ) > 1 ) {
			throw new \RuntimeException(
				'The --subpackage option requires exactly ONE local test package, but ' . count( $normalized_local_dirs ) . " were found:\n" .
				'  - ' . implode( "\n  - ", $normalized_local_dirs ) . "\n" .
				"\n" .
				'Remove the extra packages or run each selection separately.'
			);
		}

		$parent_dir = reset( $normalized_local_dirs );

		// Ensure we have a valid test package manifest.
		try {
			$manifest = ( new TestPackageManifestParser() )->parse( $parent_dir );
		} catch ( \InvalidArgumentException $e ) {
			$manifest_path = rtrim( $parent_dir, '/' ) . '/qit-test.json';
			throw new \RuntimeException( "Invalid test package manifest at {$manifest_path}: " . $e->getMessage() );
		}

		if ( ! $manifest->has_subpackages() ) {
			throw new \RuntimeException(
				sprintf(
					"Test package '%s' (%s) does not define any subpackages in qit-test.json.",
					$manifest->get_package_id(),
					$parent_dir
				)
			);
		}

		foreach ( $subpackage_ids as $subpackage_id ) {
			if ( $manifest->get_subpackage( $subpackage_id ) === null ) {
				throw new \RuntimeException(
					sprintf(
						"Subpackage '%s' not found in test package '%s'.\nAvailable subpackages:\n  - %s",
						$subpackage_id,
						$manifest->get_package_id(),
						implode( "\n  - ", array_keys( $manifest->get_subpackages() ) )
					)
				);
			}

			self::validate_subpackage_id( $subpackage_id );

			// Validate that we can create a valid subpackage manifest.
			try {
				$manifest->create_subpackage_manifest( $subpackage_id );
			} catch ( \InvalidArgumentException $e ) {
				throw new \RuntimeException( $e->getMessage() );
			}
		}

		return $parent_dir;
	}

	/**
	 * Get the manifests whose requirements should be provisioned for a test package.
	 *
	 * When the package is the local parent of a --subpackage selection, the
	 * requirements come from the selected subpackages' synthesized manifests
	 * instead of the parent, matching how remote subpackage references are
	 * provisioned. Otherwise the package's own manifest is returned.
	 *
	 * @param TestPackageManifest $manifest       The test package manifest.
	 * @param string              $package_ref    The test package reference (local path or remote reference).
	 * @param array<string>       $subpackage_ids Selected subpackage IDs (empty when there is no selection).
	 * @param string|null         $parent_dir     Real path of the local parent package the selection applies to.
	 *
	 * @return array<string,TestPackageManifest> Map of requirement source label => manifest.
	 * @throws \InvalidArgumentException If a selected subpackage manifest cannot be synthesized.
	 */
	public static function get_requirement_manifests( TestPackageManifest $manifest, string $package_ref, array $subpackage_ids, ?string $parent_dir ): array {
		if ( empty( $subpackage_ids )
			|| $parent_dir === null
			|| ! is_dir( $package_ref )
			|| realpath( $package_ref ) !== $parent_dir
		) {
			return [ $package_ref => $manifest ];
		}

		$manifests = [];
		foreach ( $subpackage_ids as $subpackage_id ) {
			$manifests[ "{$subpackage_id} ({$package_ref})" ] = $manifest->create_subpackage_manifest( $subpackage_id );
		}

		return $manifests;
	}

	/**
	 * Validate a subpackage ID.
	 *
	 * @param string $subpackage_id The subpackage ID to validate.
	 *
	 * @return void
	 * @throws \RuntimeException If the subpackage ID is invalid.
	 */
	private static function validate_subpackage_id( string $subpackage_id ): void {
		if ( str_contains( $subpackage_id, '/' ) && ! str_starts_with( $subpackage_id, '/' ) && ! str_ends_with( $subpackage_id, '/' ) ) {
			return;
		}

		throw new \RuntimeException(
			sprintf(
				"Invalid subpackage ID: '%s'. Subpackage IDs must be in the format 'namespace/subpackage'.",
				$subpackage_id
			)
		);
	}
}
