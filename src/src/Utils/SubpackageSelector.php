<?php

namespace QIT_CLI\Utils;

use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;

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
	 * Validate a --subpackage selection against the candidate test packages.
	 *
	 * @param array<string> $subpackage_ids Requested subpackage IDs (full IDs, e.g. "namespace/name").
	 * @param array<string> $package_refs   Candidate test package references, with local paths already expanded to absolute paths.
	 *
	 * @return string Absolute path of the single local parent package directory.
	 * @throws \RuntimeException On any violation.
	 */
	public static function validate_selection( array $subpackage_ids, array $package_refs ): string {
		$subpackage_ids = array_values( array_unique( array_filter( $subpackage_ids ) ) );

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

				$local_dirs[] = $local_dir;
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
				'  --test-package namespace/subpackage:version'
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

			// Validate that we can create a valid subpackage manifest.
			try {
				$manifest->create_subpackage_manifest( $subpackage_id );
			} catch ( \InvalidArgumentException $e ) {
				throw new \RuntimeException( $e->getMessage() );
			}
		}

		return $parent_dir;
	}
}
