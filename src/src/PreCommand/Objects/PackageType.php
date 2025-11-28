<?php

namespace QIT_CLI\PreCommand\Objects;

/**
 * Package type constants and utilities.
 *
 * This class provides a central location for all package type related constants
 * to avoid magic strings throughout the codebase.
 */
final class PackageType {
	/**
	 * Test package type - executes tests and produces results.
	 */
	public const TEST = 'test';

	/**
	 * Utility package type - provides environment configuration without test execution.
	 */
	public const UTILITY = 'utility';

	/**
	 * Get the default package type.
	 *
	 * @return string Default package type.
	 */
	public static function get_default(): string {
		return self::TEST;
	}

	/**
	 * Get all valid package types.
	 *
	 * @return array<string> Array of valid package type strings.
	 */
	public static function all(): array {
		return [ self::TEST, self::UTILITY ];
	}

	/**
	 * Check if a package type is valid.
	 *
	 * @param string $type Package type to validate.
	 * @return bool True if valid package type.
	 */
	public static function is_valid( string $type ): bool {
		return in_array( $type, self::all(), true );
	}
}
