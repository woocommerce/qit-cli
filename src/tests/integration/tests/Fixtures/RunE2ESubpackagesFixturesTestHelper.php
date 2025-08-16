<?php

namespace QIT\IntegrationTests\Fixtures;

use QIT\IntegrationTests\TestCleanupHelper;

/**
 * Helper class for RunE2ESubpackagesFixturesTest.
 * Provides utilities for creating test packages with unique subpackage names.
 */
class RunE2ESubpackagesFixturesTestHelper {
	
	/**
	 * Updates a manifest's subpackage names to use unique test-prefixed names.
	 * This prevents collisions between test runs.
	 * 
	 * @param array $manifest The manifest array to update
	 * @param string $suffix Optional suffix for the subpackage names
	 * @return array Updated manifest with mapping of old to new subpackage names
	 */
	public static function make_subpackages_unique( array &$manifest, string $suffix = '' ): array {
		if ( empty( $manifest['subpackages'] ) ) {
			return [];
		}
		
		$mapping = [];
		$newSubpackages = [];
		
		foreach ( $manifest['subpackages'] as $oldName => $subpackageConfig ) {
			// Extract namespace and base name from old name
			if ( strpos( $oldName, '/' ) !== false ) {
				list( $namespace, $baseName ) = explode( '/', $oldName, 2 );
			} else {
				$namespace = 'woocommerce';
				$baseName = $oldName;
			}
			
			// Generate unique name with test prefix
			$uniqueName = TestCleanupHelper::generate_test_package_name( 
				$namespace, 
				$baseName . ( $suffix ? '-' . $suffix : '' )
			);
			
			$mapping[ $oldName ] = $uniqueName;
			$newSubpackages[ $uniqueName ] = $subpackageConfig;
		}
		
		$manifest['subpackages'] = $newSubpackages;
		
		return $mapping;
	}
	
	/**
	 * Creates a test package directory with unique names.
	 * 
	 * @param string $sourceDir Source directory to copy from
	 * @param string $tempDir Temporary directory to create in
	 * @param string $packageBaseName Base name for the parent package
	 * @return array Array with 'dir', 'manifest', 'package_name', and 'subpackage_mapping'
	 */
	public static function create_unique_test_package( string $sourceDir, string $tempDir, string $packageBaseName ): array {
		$packageDir = $tempDir . '/' . $packageBaseName;
		exec( "cp -r " . escapeshellarg( $sourceDir ) . " " . escapeshellarg( $packageDir ) );
		
		$manifestPath = $packageDir . '/qit-test.json';
		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		
		// Generate unique parent package name
		$packageName = TestCleanupHelper::generate_test_package_name( 'woocommerce', $packageBaseName );
		$manifest['package'] = $packageName;
		
		// Make subpackages unique
		$subpackageMapping = self::make_subpackages_unique( $manifest );
		
		file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		
		return [
			'dir' => $packageDir,
			'manifest' => $manifest,
			'package_name' => $packageName,
			'subpackage_mapping' => $subpackageMapping,
		];
	}
}