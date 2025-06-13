<?php

namespace QIT_CLI\PreCommand\Configuration;

/**
 * Parser for qit.json configuration files
 */
class QitJsonParser extends BaseJsonParser {
	private array $parsedConfig = [];
	private TestPackageManifestParser $packageParser;
	private array $loadedPackages = []; // Cache for loaded test packages
	private string $currentFilePath; // Track the current file being parsed
	private array $pathContexts       = []; // Track which directory each path came from
	private ?string $urlExtendContext = null; // Track context for URL extends

	public function __construct() {
		parent::__construct();
		$this->packageParser = new TestPackageManifestParser();
	}

	protected function getSchemaType(): string {
		return 'qit';
	}

	public function parse( string $filePath ): array {
		$this->rootPath = dirname( $filePath );

		// Load and validate JSON
		$config = $this->loadAndValidateJson( $filePath );

		// Store the actual file path for extends resolution (without realpath)
		$this->currentFilePath = $filePath;

		// Apply business logic
		return $this->applyBusinessLogic( $config );
	}

	protected function applyBusinessLogic( array $config ): array {
		$this->debugLog( '=== applyBusinessLogic called ===' );

		// Resolve extends first, passing the actual file path
		$config = $this->resolveExtends( $config, $this->currentFilePath );

		// Ensure SUT exists
		if ( ! isset( $config['sut'] ) ) {
			throw new \RuntimeException( "The 'sut' property is required in the configuration" );
		}

		// Process SUT
		$this->debugLog( 'Processing SUT' );
		$config['sut'] = $this->processSut( $config['sut'] );

		// Resolve environment extends
		if ( isset( $config['environments'] ) ) {
			$this->debugLog( 'Resolving environment extends' );
			$config['environments'] = $this->resolveEnvironmentExtends( $config['environments'] );
		}

		// Resolve test type extends
		if ( isset( $config['test_types'] ) ) {
			$this->debugLog( 'Resolving test type extends' );
			$config['test_types'] = $this->resolveTestTypeExtends( $config['test_types'] );
		}

		// Validate cross-references
		$this->debugLog( 'Starting cross-reference validation' );
		$this->validateCrossReferences( $config );

		$this->parsedConfig = $config;
		$this->debugLog( '=== applyBusinessLogic completed ===' );

		return $config;
	}

	/**
	 * Get a loaded test package by reference
	 * This method loads packages on-demand when needed
	 */
	public function getTestPackage( string $reference ): array {
		// Check cache first
		if ( isset( $this->loadedPackages[ $reference ] ) ) {
			return $this->loadedPackages[ $reference ];
		}

		// Parse the reference to determine type
		if ( $this->isLocalPackageReference( $reference ) ) {
			// Local file reference (e.g., "tests/e2e/checkout.json")
			$context                            = $this->getPathContext( $reference );
			$filePath                           = $this->resolvePathWithContext( $reference, $context );
			$this->loadedPackages[ $reference ] = $this->packageParser->parse( $filePath );
		} else {
			// Remote reference (e.g., "woocommerce/minimal:stable")
			// For remote packages, we don't have the actual manifest
			// Just return a placeholder structure
			$this->loadedPackages[ $reference ] = $this->createRemotePackageStub( $reference );
		}

		return $this->loadedPackages[ $reference ];
	}

	/**
	 * Get all test packages referenced in a test configuration
	 */
	public function getTestPackagesForProfile( string $testType, string $profile ): array {
		if ( ! isset( $this->parsedConfig['test_types'][ $testType ][ $profile ]['test_packages'] ) ) {
			return [];
		}

		$packages   = [];
		$references = $this->parsedConfig['test_types'][ $testType ][ $profile ]['test_packages'];

		foreach ( $references as $reference ) {
			$packages[ $reference ] = $this->getTestPackage( $reference );
		}

		return $packages;
	}

	/**
	 * Get setup-only packages for an environment
	 */
	public function getSetupPackagesForEnvironment( string $environment ): array {
		if ( ! isset( $this->parsedConfig['environments'][ $environment ]['setup_only'] ) ) {
			return [];
		}

		$packages   = [];
		$references = $this->parsedConfig['environments'][ $environment ]['setup_only'];

		foreach ( $references as $reference ) {
			$packages[ $reference ] = $this->getTestPackage( $reference );
		}

		return $packages;
	}

	private function isLocalPackageReference( string $reference ): bool {
		// Local references contain paths (/) but no colons for versions
		$isLocal = strpos( $reference, '/' ) !== false && strpos( $reference, ':' ) === false;
		$this->debugLog( "isLocalPackageReference('$reference'): " . ( $isLocal ? 'true' : 'false' ) );

		return $isLocal;
	}

	private function createRemotePackageStub( string $reference ): array {
		// Parse remote reference format: vendor/package:version
		if ( preg_match( '/^([^\/]+)\/([^:]+):(.+)$/', $reference, $matches ) ) {
			return [
				'vendor'    => $matches[1],
				'package'   => $matches[2],
				'version'   => $matches[3],
				'remote'    => true,
				'reference' => $reference,
			];
		}

		throw new \RuntimeException( "Invalid remote package reference: $reference" );
	}

	/**
	 * Resolve file-level extends
	 */
	private function resolveExtends( array $config, string $currentFile = null, array $visited = [] ): array {
		// Debug logging
		$this->debugLog( '=== resolveExtends called ===' );
		$this->debugLog( 'Current file: ' . ( $currentFile ?? 'null' ) );
		$this->debugLog( 'Config has extends: ' . ( isset( $config['extends'] ) ? $config['extends'] : 'no' ) );
		$this->debugLog( 'Visited files: ' . json_encode( $visited ) );

		if ( ! isset( $config['extends'] ) ) {
			// Mark paths in this config with their context
			$this->markPathContexts( $config, dirname( $currentFile ?: $this->currentFilePath ) );

			return $config;
		}

		// Check for circular dependencies - normalize paths for comparison
		if ( $currentFile !== null ) {
			$normalizedCurrent = $this->normalizePath( $currentFile );
			if ( in_array( $normalizedCurrent, $visited ) ) {
				$this->debugLog( "ERROR: Circular dependency detected for file: $normalizedCurrent" );
				throw new \RuntimeException( 'Circular dependency detected in extends' );
			}
			$visited[] = $normalizedCurrent;
		}

		// Resolve the path to the extended config
		$basePath = $this->resolveExtendsPath( $config['extends'] );
		$this->debugLog( "Resolved extends path: $basePath" );

		// Check if we're about to load a file we've already visited
		$normalizedBase = $this->normalizePath( $basePath );
		if ( in_array( $normalizedBase, $visited ) ) {
			$this->debugLog( "ERROR: About to load already visited file: $normalizedBase" );
			throw new \RuntimeException( 'Circular dependency detected in extends' );
		}

		// Load base configuration
		$baseConfig = $this->loadAndValidateJson( $basePath );

		// Temporarily change root path for base config processing
		$originalRoot   = $this->rootPath;
		$this->rootPath = dirname( $basePath );
		$baseConfig     = $this->resolveExtends( $baseConfig, $basePath, $visited );
		$this->rootPath = $originalRoot;

		// Mark paths in the current config before merging
		$this->markPathContexts( $config, dirname( $currentFile ?: $this->currentFilePath ) );

		// Merge configurations
		unset( $config['extends'] );

		return $this->deepMerge( $baseConfig, $config );
	}

	/**
	 * Mark paths in configuration with their source directory context
	 */
	private function markPathContexts( array &$config, string $sourceDir ): void {
		// If we're processing content from a URL extend, use the URL extend context
		$effectiveContext = $this->urlExtendContext ?? $sourceDir;

		// Mark test package paths
		if ( isset( $config['test_types'] ) ) {
			foreach ( $config['test_types'] as $type => $profiles ) {
				foreach ( $profiles as $profile => $settings ) {
					if ( isset( $settings['test_packages'] ) ) {
						foreach ( $settings['test_packages'] as $package ) {
							if ( $this->isLocalPackageReference( $package ) ) {
								$this->pathContexts[ $package ] = $effectiveContext;
								$this->debugLog( "Marked path context: '$package' => '$effectiveContext'" );
							}
						}
					}
				}
			}
		}

		// Mark setup_only paths
		if ( isset( $config['environments'] ) ) {
			foreach ( $config['environments'] as $envName => $env ) {
				if ( isset( $env['setup_only'] ) ) {
					foreach ( $env['setup_only'] as $package ) {
						if ( $this->isLocalPackageReference( $package ) ) {
							$this->pathContexts[ $package ] = $effectiveContext;
							$this->debugLog( "Marked path context: '$package' => '$effectiveContext'" );
						}
					}
				}
			}
		}
	}

	/**
	 * Get the source directory context for a path
	 */
	private function getPathContext( string $path ): string {
		return $this->pathContexts[ $path ] ?? $this->rootPath;
	}

	/**
	 * Resolve a path with its proper context directory
	 */
	private function resolvePathWithContext( string $path, string $context ): string {
		if ( strpos( $path, './' ) === 0 || strpos( $path, '../' ) === 0 ) {
			// Relative path - prepend the context directory
			return $context . '/' . $path;
		}

		// Absolute path - return as is
		return $path;
	}

	private function resolveExtendsPath( string $extends ): string {
		$this->debugLog( "Resolving extends path: $extends" );

		// Special test flag to simulate URL extends
		if ( strpos( $extends, './mimick-url-for-tests/' ) === 0 ) {
			// Extract the actual file path after the test prefix
			$actualPath   = str_replace( './mimick-url-for-tests/', './', $extends );
			$resolvedPath = $this->resolvePath( $actualPath );

			if ( ! file_exists( $resolvedPath ) ) {
				throw new \RuntimeException( "Extended config file not found: $actualPath" );
			}

			// Read the file content and create a temp file to simulate URL behavior
			$contents = file_get_contents( $resolvedPath );
			$tempFile = tempnam( sys_get_temp_dir(), 'qit_extends_test_' );
			file_put_contents( $tempFile, $contents );

			// Mark that this is a URL-based extend (simulated)
			$this->urlExtendContext = $this->rootPath;
			$this->debugLog( 'Simulated URL extend detected, marking context as: ' . $this->rootPath );

			return $tempFile;
		}

		if ( filter_var( $extends, FILTER_VALIDATE_URL ) ) {
			$tempFile = tempnam( sys_get_temp_dir(), 'qit_extends_' );
			$contents = file_get_contents( $extends );
			file_put_contents( $tempFile, $contents );

			// Mark that this is a URL-based extend
			$this->urlExtendContext = $this->rootPath;
			$this->debugLog( 'URL extend detected, marking context as: ' . $this->rootPath );

			return $tempFile;
		}

		// Reset URL extend context for non-URL extends
		$this->urlExtendContext = null;

		// Check if extends is just the filename without path
		if ( basename( $extends ) === $extends ) {
			// It's just a filename, resolve from current directory
			$path = $this->rootPath . '/' . $extends;
		} else {
			// For relative paths, resolve from current root
			$path = $this->resolvePath( $extends );
		}

		$this->debugLog( "Resolved path: $path" );

		if ( ! file_exists( $path ) ) {
			throw new \RuntimeException( "Extended config file not found: $extends" );
		}

		return $path;
	}

	/**
	 * Normalize a path for comparison (remove . and .. components)
	 * This is only used for circular dependency detection
	 */
	private function normalizePath( string $path ): string {
		$parts      = explode( '/', str_replace( '\\', '/', $path ) );
		$normalized = [];

		foreach ( $parts as $part ) {
			if ( $part === '.' ) {
				continue;
			}
			if ( $part === '..' && count( $normalized ) > 0 && end( $normalized ) !== '..' ) {
				array_pop( $normalized );
			} else {
				$normalized[] = $part;
			}
		}

		return implode( '/', $normalized );
	}

	/**
	 * Debug logging helper
	 */
	private function debugLog( string $message ): void {
		$logDir = '/tmp/qit';
		if ( ! is_dir( $logDir ) ) {
			mkdir( $logDir, 0777, true );
		}

		$logFile   = $logDir . '/qit_debug.log';
		$timestamp = date( 'Y-m-d H:i:s' );
		file_put_contents( $logFile, "[$timestamp] $message\n", FILE_APPEND );
	}

	private function processSut( array $sut ): array {
		// Validate source paths exist without modifying them
		if ( isset( $sut['source'] ) ) {
			$this->validateSutSource( $sut['source'] );
		}

		return $sut;
	}

	private function validateSutSource( array $source ): void {
		switch ( $source['type'] ) {
			case 'local':
			case 'directory':
				if ( isset( $source['path'] ) ) {
					$path = $this->resolvePath( $source['path'] );
					if ( ! is_dir( $path ) ) {
						throw new \RuntimeException( "SUT directory not found: {$source['path']}" );
					}
				}
				break;

			case 'zip':
				if ( isset( $source['path'] ) ) {
					$path = $this->resolvePath( $source['path'] );
					if ( ! file_exists( $path ) ) {
						throw new \RuntimeException( "SUT zip file not found: {$source['path']}" );
					}
				}
				break;
		}
	}

	private function resolveEnvironmentExtends( array $environments ): array {
		return $this->resolveNestedExtends( $environments, 'environments' );
	}

	private function resolveTestTypeExtends( array $testTypes ): array {
		foreach ( $testTypes as $type => &$profiles ) {
			$profiles = $this->resolveNestedExtends( $profiles, "test type '$type'" );
		}

		return $testTypes;
	}

	private function resolveNestedExtends( array $items, string $context ): array {
		$resolved = [];
		$pending  = $items;

		while ( ! empty( $pending ) ) {
			$progress = false;

			foreach ( $pending as $name => $config ) {
				if ( ! isset( $config['extends'] ) ) {
					$resolved[ $name ] = $config;
					unset( $pending[ $name ] );
					$progress = true;
				} elseif ( isset( $resolved[ $config['extends'] ] ) ) {
					$base = $resolved[ $config['extends'] ];
					unset( $config['extends'] );
					$resolved[ $name ] = $this->deepMerge( $base, $config );
					unset( $pending[ $name ] );
					$progress = true;
				}
			}

			if ( ! $progress && ! empty( $pending ) ) {
				throw new \RuntimeException(
					"Circular or missing extends in $context: " . implode( ', ', array_keys( $pending ) )
				);
			}
		}

		return $resolved;
	}

	private function validateCrossReferences( array $config ): void {
		$this->debugLog( '=== validateCrossReferences called ===' );
		$this->debugLog( 'Config keys: ' . json_encode( array_keys( $config ) ) );

		// Validate test_types reference existing environments and test packages
		if ( isset( $config['test_types'] ) ) {
			$this->debugLog( 'Validating test_types' );
			foreach ( $config['test_types'] as $type => $profiles ) {
				foreach ( $profiles as $profile => $settings ) {
					// Validate environment references only if environments exist
					if ( isset( $settings['environment'] ) && isset( $config['environments'] ) ) {
						if ( ! isset( $config['environments'][ $settings['environment'] ] ) ) {
							throw new \RuntimeException(
								"Environment '{$settings['environment']}' referenced in test type '$type:$profile' not found"
							);
						}
					}

					// Validate test package references exist (only for local files)
					if ( isset( $settings['test_packages'] ) ) {
						$this->debugLog( "Checking test packages for $type:$profile" );
						foreach ( $settings['test_packages'] as $packageRef ) {
							$this->debugLog( "Checking package reference: $packageRef" );
							if ( $this->isLocalPackageReference( $packageRef ) ) {
								$this->debugLog( "Package is local reference: $packageRef" );
								$context = $this->getPathContext( $packageRef );
								$path    = $this->resolvePathWithContext( $packageRef, $context );
								$this->debugLog( "Resolved path with context: $path (context: $context)" );
								$this->debugLog( 'File exists: ' . ( file_exists( $path ) ? 'yes' : 'no' ) );
								if ( ! file_exists( $path ) ) {
									$this->debugLog( 'ERROR: Local package file not found!' );
									throw new \RuntimeException(
										"Test package file not found: $packageRef in '$type:$profile'"
									);
								}
							} else {
								$this->debugLog( "Package is remote reference: $packageRef" );
							}
						}
					}
				}
			}
		}

		// Validate groups reference existing test_types
		if ( isset( $config['groups'] ) && isset( $config['test_types'] ) ) {
			$this->debugLog( 'Validating groups' );
			foreach ( $config['groups'] as $group => $tests ) {
				foreach ( $tests as $testType => $profiles ) {
					if ( ! isset( $config['test_types'][ $testType ] ) ) {
						throw new \RuntimeException( "Test type '$testType' in group '$group' not found" );
					}
					foreach ( $profiles as $profile ) {
						if ( ! isset( $config['test_types'][ $testType ][ $profile ] ) ) {
							throw new \RuntimeException(
								"Profile '$profile' for test type '$testType' in group '$group' not found"
							);
						}
					}
				}
			}
		}

		// Validate setup_only package references in environments
		if ( isset( $config['environments'] ) ) {
			$this->debugLog( 'Validating environment setup_only packages' );
			foreach ( $config['environments'] as $envName => $env ) {
				if ( isset( $env['setup_only'] ) ) {
					foreach ( $env['setup_only'] as $packageRef ) {
						$this->debugLog( "Checking setup_only package: $packageRef" );
						if ( $this->isLocalPackageReference( $packageRef ) ) {
							$context = $this->getPathContext( $packageRef );
							$path    = $this->resolvePathWithContext( $packageRef, $context );
							$this->debugLog( "Setup package resolved path: $path (context: $context)" );
							if ( ! file_exists( $path ) ) {
								throw new \RuntimeException(
									"Setup package file not found: $packageRef in environment '$envName'"
								);
							}
						}
					}
				}
			}
		}

		$this->debugLog( '=== validateCrossReferences completed ===' );
	}

	/**
	 * Resolve a path relative to the root directory
	 * This is used for validation only, not for modifying the config
	 */
	protected function resolvePath( string $path ): string {
		if ( strpos( $path, './' ) === 0 || strpos( $path, '../' ) === 0 ) {
			// Relative path - prepend the root path
			return $this->rootPath . '/' . $path;
		}

		// Absolute path - return as is
		return $path;
	}

	// Public accessors
	public function getConfig(): array {
		return $this->parsedConfig;
	}

	public function getEnvironment( string $name ): array {
		if ( ! isset( $this->parsedConfig['environments'][ $name ] ) ) {
			throw new \RuntimeException( "Environment '$name' not found" );
		}

		return $this->parsedConfig['environments'][ $name ];
	}

	public function getTestConfig( string $testType, string $profile ): array {
		return $this->parsedConfig['test_types'][ $testType ][ $profile ] ?? [];
	}
}
