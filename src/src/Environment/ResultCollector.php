<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use RuntimeException;

/**
 * Handles result collection and CTRF report merging
 *
 * CTRF results are merged into unified reports for analysis.
 * Allure results are collected raw in per-package directories for CI workflow processing.
 */
class ResultCollector {
	private NodeDependencyManager $node_deps;
	private Docker $docker;
	private CTRFValidator $ctrf_validator;

	/**
	 * @var array{total_packages: int, packages_with_allure: int, packages_without_allure: array<string>}|null
	 */
	private ?array $allure_tracking = null;

	/**
	 * @var array{total_packages: int, packages_with_blob: int, packages_without_blob: array<string>}|null
	 */
	private ?array $blob_tracking = null;

	/**
	 * @var array<string, bool> Track which packages have been counted for blob
	 */
	private array $blob_counted = [];

	/**
	 * @var array<string, bool> Track which packages have been counted for allure
	 */
	private array $allure_counted = [];

	/**
	 * @var array<string, bool> Static map of package ID to isLocal status
	 */
	private static array $package_local_map = [];

	public function __construct( Docker $docker, NodeDependencyManager $node_deps, CTRFValidator $ctrf_validator ) {
		$this->node_deps      = $node_deps;
		$this->docker         = $docker;
		$this->ctrf_validator = $ctrf_validator;
	}

	/**
	 * Get Allure configuration tracking data
	 *
	 * @return array{total_packages: int, packages_with_allure: int, packages_without_allure: array<string>}|null
	 */
	public function get_allure_tracking(): ?array {
		return $this->allure_tracking;
	}

	/**
	 * Get Blob configuration tracking data
	 *
	 * @return array{total_packages: int, packages_with_blob: int, packages_without_blob: array<string>}|null
	 */
	public function get_blob_tracking(): ?array {
		return $this->blob_tracking;
	}

	/**
	 * Reset tracking for new test run
	 */
	public function reset_tracking(): void {
		$this->allure_tracking = null;
		$this->blob_tracking   = null;
		$this->blob_counted    = [];
		$this->allure_counted  = [];
	}

	/**
	 * Set the package local map for the current run
	 *
	 * @param array<string, bool> $map Map of package ID to isLocal status.
	 */
	public static function set_package_local_map( array $map ): void {
		self::$package_local_map = $map;
	}

	/**
	 * Get isLocal status for a package
	 *
	 * @param string $package_id The package ID.
	 * @return bool|null True if local, false if remote, null if unknown
	 */
	private static function get_package_is_local( string $package_id ): ?bool {
		return self::$package_local_map[ $package_id ] ?? null;
	}

	/**
	 * Collect artifacts from a test package after it finishes running
	 */
	public function collect(
		E2EEnvInfo $env,
		string $slug,
		TestPackageManifest $mf,
		string $dir,
		string $phase = 'run'
	): void {

		// --------- 1️⃣  collect CTRF ------------------------------------------
		$this->collect_ctrf(
			$env,
			$slug,
			$mf,
			$dir,
			/* mandatory = */ $phase === 'run',   // ← only "run" is mandatory
			$phase
		);

		// --------- 2️⃣  collect Allure (never mandatory) ----------------------
		$has_allure = $this->collect_allure( $env, $slug, $mf, $dir );

		// Track Allure configuration status (only count each package once)
		if ( ! isset( $this->allure_tracking ) ) {
			$this->allure_tracking = [
				'total_packages'          => 0,
				'packages_with_allure'    => 0,
				'packages_without_allure' => [],
			];
		}

		// Only count this package if we haven't counted it before
		if ( ! isset( $this->allure_counted[ $slug ] ) ) {
			$this->allure_counted[ $slug ] = true;
			++$this->allure_tracking['total_packages'];
			if ( $has_allure ) {
				++$this->allure_tracking['packages_with_allure'];
			} else {
				$this->allure_tracking['packages_without_allure'][] = basename( $slug );
			}
		}

		// --------- 3️⃣  collect Blob (optional, but track for warnings) ------
		$has_blob = $this->collect_blob(
			$env,
			$slug,
			$mf,
			$dir
		);

		// Track Blob configuration status (only count each package once)
		if ( ! isset( $this->blob_tracking ) ) {
			$this->blob_tracking = [
				'total_packages'        => 0,
				'packages_with_blob'    => 0,
				'packages_without_blob' => [],
			];
		}

		// Only count this package if we haven't counted it before
		if ( ! isset( $this->blob_counted[ $slug ] ) ) {
			$this->blob_counted[ $slug ] = true;
			++$this->blob_tracking['total_packages'];
			if ( $has_blob ) {
				++$this->blob_tracking['packages_with_blob'];
			} else {
				$this->blob_tracking['packages_without_blob'][] = basename( $slug );
			}
		}
	}

	private function collect_ctrf(
		E2EEnvInfo $env,
		string $slug,
		TestPackageManifest $mf,
		string $dir,
		bool $mandatory,
		string $phase
	): void {

		$rel = $mf->get_test_results()['ctrf-json'] ?? null;
		if ( ! $rel ) {
			if ( $mandatory ) {
				throw new RuntimeException( "manifest lacks ctrf-json for phase '{$phase}'" );
			}

			return;                 // optional → skip
		}

		$safe = ltrim( str_replace( [ '/', ':' ], '_', $slug ), '._' );
		// If slug is empty after sanitization (e.g., for "./"), use "local-package"
		if ( empty( $safe ) ) {
			$safe = 'local-package';
		}
		$dst      = $dir . '/ctrf/' . $safe . '.json';
		$dir_path = dirname( $dst );
		if ( ! is_dir( $dir_path ) ) {
			mkdir( $dir_path, 0755, true );
		}

		// Get isLocal from static map
		$is_local = self::get_package_is_local( $slug );

		/* 1 — host path ------------------------------------------------------- */
		$host_pkg = $env->test_packages_metadata[ $slug ]['path'] ?? '';

		// Path expansion is now done early in UpEnvironmentCommand, so paths should already be absolute
		// Just construct the full source path
		$host_src = rtrim( $host_pkg, '/' ) . '/' . ltrim( $rel, './' );

		if ( is_readable( $host_src ) ) {
			$copy_result = copy( $host_src, $dst );

			if ( ! $copy_result ) {
				throw new RuntimeException( "Failed to copy CTRF from $host_src to $dst" );
			}

			// Validate CTRF from test package
			$validation = $this->ctrf_validator->validate_file( $dst );
			if ( ! $validation['valid'] ) {
				throw new RuntimeException( "Test package CTRF validation failed for $slug: " . $validation['errors'] );
			}

			$this->tag_ctrf( $dst, $slug, $mf, $phase, $is_local );

			return;
		}

		/*
		2 — Container fallback ---------------------------------------------
		*/
		// Use manifest's package ID for container paths, not the slug
		$package_id     = $mf->get_package_id();
		$container_base = \QIT_CLI\PreCommand\Objects\TestPackageManifest::create_container_path( $package_id );
		$ctr_path       = $container_base . '/' . ltrim( $rel, './' );
		try {
			$this->docker->copy_from_docker( $env, $ctr_path, $dst, 'php' );

			// Validate CTRF from test package
			$validation = $this->ctrf_validator->validate_file( $dst );
			if ( ! $validation['valid'] ) {
				throw new RuntimeException( "Test package CTRF validation failed for $slug: " . $validation['errors'] );
			}

			$this->tag_ctrf( $dst, $slug, $mf, $phase, $is_local );
		} catch ( \RuntimeException $e ) {
			if ( $mandatory ) {
				throw $e;           // only fail for "run"
			}
			// optional → do nothing
		}
	}

	/**
	 * @return bool True if package has Allure configured, false otherwise
	 */
	private function collect_allure(
		E2EEnvInfo $env,
		string $slug,
		TestPackageManifest $mf,
		string $dir
	): bool {

		$rel = $mf->get_test_results()['allure-dir'] ?? null;
		if ( ! $rel ) {
			return false;
		}                     // no declaration → skip

		$host_pkg = $env->test_packages_metadata[ $slug ]['path'] ?? '';
		$host_src = rtrim( $host_pkg, '/' ) . '/' . trim( $rel, '/' );

		// Use sanitized package name for directory - use manifest's package ID, not the slug
		$package_id = $mf->get_package_id();
		$safe_name  = \QIT_CLI\PreCommand\Objects\TestPackageManifest::create_container_directory_name( $package_id );
		$dst        = $dir . '/allure/' . $safe_name;
		$dir_path   = dirname( $dst );
		if ( ! is_dir( $dir_path ) ) {
			mkdir( $dir_path, 0755, true );
		}

		/* host first */
		if ( is_dir( $host_src ) ) {
			// Use Symfony Filesystem mirror instead of custom implementation
			$fs = new Filesystem();
			$fs->mirror( $host_src, $dst );

			return true;
		}

		/*
		Container fallback
		*/
		// Use manifest's package ID for container paths, not the slug
		$package_id     = $mf->get_package_id();
		$container_base = \QIT_CLI\PreCommand\Objects\TestPackageManifest::create_container_path( $package_id );
		$ctr_path       = $container_base . '/' . trim( $rel, '/' );
		try {
			$this->docker->copy_from_docker( $env, $ctr_path, $dst, 'php' );
			return true;
		} catch ( \RuntimeException $e ) {
			// Never mandatory for allure collection - silently ignore failures
			unset( $e ); // Explicitly acknowledge the exception is not used
			return false;
		}
	}

	/**
	 * @return bool True if package has blob-dir configured and collected, false otherwise
	 */
	private function collect_blob(
		E2EEnvInfo $env,
		string $slug,
		TestPackageManifest $mf,
		string $dir
	): bool {

		$rel = $mf->get_test_results()['blob-dir'] ?? null;
		if ( ! $rel ) {
			return false;  // No blob-dir configured
		}

		$host_pkg = $env->test_packages_metadata[ $slug ]['path'] ?? '';
		$host_src = rtrim( $host_pkg, '/' ) . '/' . trim( $rel, '/' );

		// Use sanitized package name for directory - use manifest's package ID, not the slug
		$package_id = $mf->get_package_id();
		$safe_name  = \QIT_CLI\PreCommand\Objects\TestPackageManifest::create_container_directory_name( $package_id );
		$dst        = $dir . '/blob/' . $safe_name;
		$dir_path   = dirname( $dst );
		if ( ! is_dir( $dir_path ) ) {
			mkdir( $dir_path, 0755, true );
		}

		/* host first */
		if ( is_dir( $host_src ) ) {
			// Check if blob directory has valid content
			if ( $this->validate_blob_directory( $host_src ) ) {
				// Use Symfony Filesystem mirror instead of custom implementation
				$fs = new Filesystem();
				$fs->mirror( $host_src, $dst );
				return true;
			}
			// Blob directory exists but is empty/invalid - not fatal
			return false;
		}

		/*
		Container fallback
		*/
		// Use manifest's package ID for container paths, not the slug
		$package_id     = $mf->get_package_id();
		$container_base = \QIT_CLI\PreCommand\Objects\TestPackageManifest::create_container_path( $package_id );
		$ctr_path       = $container_base . '/' . trim( $rel, '/' );
		try {
			$this->docker->copy_from_docker( $env, $ctr_path, $dst, 'php' );
			// Validate after copying from container
			if ( $this->validate_blob_directory( $dst ) ) {
				return true;
			}
			// Blob directory copied but is empty/invalid - not fatal
			return false;
		} catch ( \RuntimeException $e ) {
			// Blob collection failures are not fatal
			unset( $e ); // Explicitly acknowledge the exception is not used
			return false;
		}
	}


	/**
	 * Validate blob directory structure
	 *
	 * @return bool True if directory contains valid blob files, false otherwise
	 */
	private function validate_blob_directory( string $blob_dir ): bool {
		if ( ! is_dir( $blob_dir ) ) {
			return false;
		}

		// Check for required blob reporter files
		$has_blob_files = false;
		$files          = scandir( $blob_dir );
		foreach ( $files as $file ) {
			// Playwright blob reporter creates .zip files with specific naming pattern
			if ( preg_match( '/\.zip$/', $file ) ) {
				$has_blob_files = true;
				break;
			}
		}

		return $has_blob_files;
	}

	/**
	 * Tag CTRF file with package metadata (host version)
	 */
	private function tag_ctrf( string $ctrf_path, string $slug, TestPackageManifest $mf, string $phase, ?bool $is_local = null ): void {
		if ( ! file_exists( $ctrf_path ) ) {
			return;
		}

		$data = json_decode( file_get_contents( $ctrf_path ), true );
		if ( is_array( $data ) && ! empty( $data['results']['tests'] ) && is_array( $data['results']['tests'] ) ) {
			foreach ( $data['results']['tests'] as &$test ) {
				if ( ! isset( $test['extra'] ) ) {
					$test['extra'] = [];
				}
				$test['extra']['packageSlug'] = $slug;
				$test['extra']['phase']       = $phase;
				$test['extra']['testType']    = $mf->get_test_type();
				$test['extra']['namespace']   = $mf->get_namespace();
				$test['extra']['packageId']   = $mf->get_package_id();

				// Add isLocal if provided
				if ( $is_local !== null ) {
					$test['extra']['isLocal'] = $is_local;
				}
			}
			file_put_contents( $ctrf_path, json_encode( $data, JSON_PRETTY_PRINT ) );
		}
	}

	public function merge_blob( string $artifacts_dir, SymfonyStyle $io, PackageOrchestrator $orchestrator ): void {
		$blob_dir = $artifacts_dir . '/blob';

		// Skip if no blob directories
		if ( ! is_dir( $blob_dir ) || empty( glob( $blob_dir . '/*', GLOB_ONLYDIR ) ) ) {
			return;
		}

		// Warn if some but not all packages have blob reports
		if ( $this->blob_tracking && $this->blob_tracking['packages_with_blob'] > 0
			&& $this->blob_tracking['packages_with_blob'] < $this->blob_tracking['total_packages'] ) {
			$orchestrator->post_processing_message(
				'<warning>⚠ HTML report may be incomplete: ' . count( $this->blob_tracking['packages_without_blob'] ) .
				' package(s) missing blob reports</warning>'
			);
		}

		// Ensure playwright is available via npx
		$npx_path = shell_exec( 'which npx' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
		if ( empty( $npx_path ) ) {
			throw new RuntimeException( 'npx not found. Please ensure Node.js and npm are installed.' );
		}

		$orchestrator->post_processing_message( 'Merging blob reports into HTML...' );

		// Create a temporary directory for merged output
		$merged_dir = $artifacts_dir . '/final/html-report';
		if ( ! is_dir( $merged_dir ) ) {
			mkdir( $merged_dir, 0755, true );
		}

		// Get all package directories containing blob reports
		// We already checked that the directory exists and has contents
		$package_dirs = glob( $blob_dir . '/*', GLOB_ONLYDIR );

		// Playwright merge-reports expects blob files in a single directory
		// We need to copy all blob files from package subdirectories to a temp directory
		$merge_input_dir = $artifacts_dir . '/blob-merge-temp';
		if ( ! is_dir( $merge_input_dir ) ) {
			mkdir( $merge_input_dir, 0755, true );
		}

		// Copy all blob .zip files to the merge directory
		$has_blob_files = false;
		foreach ( $package_dirs as $package_dir ) {
			$blob_files = glob( $package_dir . '/*.zip' );
			foreach ( $blob_files as $blob_file ) {
				$package_name = basename( $package_dir );
				$dest_file    = $merge_input_dir . '/' . $package_name . '-' . basename( $blob_file );
				copy( $blob_file, $dest_file );
				$has_blob_files = true;
			}
		}

		if ( ! $has_blob_files ) {
			$io->text( 'No blob report files found to merge.' );
			return;
		}

		// Create a merge config to handle different test directories
		$merge_config = [
			'reporter' => [ [ 'html' ] ],
			// Use a generic testDir to avoid conflicts
			'testDir'  => '.',
		];
		$merge_config_file = $merge_input_dir . '/merge.config.js';
		file_put_contents(
			$merge_config_file,
			'module.exports = ' . json_encode( $merge_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . ';'
		);

		// Build the merge command with config
		$cmd_parts = [ 'npx', 'playwright', 'merge-reports', '--config', $merge_config_file, $merge_input_dir ];

		$proc = new Process( $cmd_parts );
		$proc->setEnv( [
			'PLAYWRIGHT_HTML_REPORT'   => $merged_dir,
			'PW_TEST_HTML_REPORT_OPEN' => 'never',  // Don't auto-open the report
		] );
		$proc->setTimeout( 600 ); // 10 minutes timeout
		$proc->setWorkingDirectory( $artifacts_dir );

		$proc->run( function ( $type, $buf ) use ( $io ) {
			// Show output only in very verbose mode (-vvv)
			if ( $io->isVeryVerbose() ) {
				$io->write( $buf );
			}
		} );

		if ( ! $proc->isSuccessful() ) {
			$orchestrator->post_processing_message( 'Blob merge failed (HTML report unavailable)', false );
			// Don't throw - blob merge failure shouldn't fail the entire test run
			// This can happen with non-Playwright test packages or invalid blob files
			if ( $io->isVerbose() ) {
				$io->writeln( '<comment>Blob merge error: ' . $proc->getErrorOutput() . '</comment>' );
			}
			return;
		}

		$orchestrator->post_processing_message( 'HTML report generated' );
	}

	public function merge_ctrf( string $artifacts_dir, SymfonyStyle $io, PackageOrchestrator $orchestrator ): void {
		$ctrf_dir = $artifacts_dir . '/ctrf';

		// Check if CTRF directory exists
		if ( ! is_dir( $ctrf_dir ) ) {
			// No CTRF directory - this is OK only if no packages had run phases
			// or if all packages were utility packages
			$orchestrator->post_processing_message( 'No CTRF files to merge (no test results collected)' );
			return;
		}

		$ctrf_files = glob( $ctrf_dir . '/*.json' );
		if ( empty( $ctrf_files ) ) {
			// Empty CTRF directory - still OK for utility packages
			$orchestrator->post_processing_message( 'No CTRF files found (utility packages only)' );
			return;
		}

		// Ensure ctrf-cli is available. Locking to 0.0.1 since 0.0.2 was breaking and the package is being actively developed with features not currently needed by us.
		$bin_dir  = $this->node_deps->ensure_packages( [ 'ctrf-cli@0.0.1' ], $io );
		$ctrf_bin = $bin_dir . '/ctrf';

		$orchestrator->post_processing_message( 'Merging CTRF reports...' );

		$proc = new Process( [ $ctrf_bin, 'merge', $ctrf_dir ] );
		$proc->setTimeout( 300 );
		$proc->run( function ( $type, $buf ) use ( $io ) {
			// Show output only in very verbose mode (-vvv)
			if ( $io->isVeryVerbose() ) {
				$io->write( $buf );
			}
		} );

		if ( ! $proc->isSuccessful() ) {
			$orchestrator->post_processing_message( 'CTRF merge failed', false );
			throw new RuntimeException( 'CTRF merge failed: ' . $proc->getErrorOutput() );
		}

		// Move merged report to final location
		$final_dir = $artifacts_dir . '/final/ctrf';
		if ( ! is_dir( $final_dir ) ) {
			mkdir( $final_dir, 0755, true );
		}

		if ( file_exists( $ctrf_dir . '/ctrf-report.json' ) ) {
			// Remove existing file to prevent rename() failures on reruns
			$target_file = $final_dir . '/ctrf-report.json';
			if ( file_exists( $target_file ) ) {
				unlink( $target_file );
			}
			rename( $ctrf_dir . '/ctrf-report.json', $final_dir . '/ctrf-report.json' );

			// Add package metadata to the merged CTRF report
			$this->add_package_metadata_to_merged_ctrf( $final_dir . '/ctrf-report.json' );

			// Validate the merged CTRF
			$validation = $this->ctrf_validator->validate_file( $final_dir . '/ctrf-report.json' );
			if ( ! $validation['valid'] ) {
				throw new RuntimeException( 'Merged CTRF validation failed: ' . $validation['errors'] );
			}

			$orchestrator->post_processing_message( 'CTRF reports merged' );
		}
	}

	/**
	 * Save Allure reports to final location, preserving per-package structure
	 *
	 * @return array{has_allure: bool, packages_with_allure: int, packages_without_allure: array<string>}
	 */
	public function save_allure_to_final_location( string $artifacts_dir, SymfonyStyle $io, PackageOrchestrator $orchestrator ): array {
		$allure_dir = $artifacts_dir . '/allure';

		// Track Allure configuration status
		$allure_status = [
			'has_allure'              => false,
			'packages_with_allure'    => 0,
			'packages_without_allure' => [],
		];

		// Skip if no Allure directories
		if ( ! is_dir( $allure_dir ) || empty( glob( $allure_dir . '/*', GLOB_ONLYDIR ) ) ) {
			return $allure_status;
		}

		$allure_status['has_allure']           = true;
		$allure_status['packages_with_allure'] = count( glob( $allure_dir . '/*', GLOB_ONLYDIR ) );

		$orchestrator->post_processing_message( 'Saving Allure reports...' );

		try {
			// Try to save to final location using Symfony Filesystem mirror
			$final_dir = $artifacts_dir . '/final/allure';
			if ( ! is_dir( $final_dir ) ) {
				mkdir( $final_dir, 0755, true );
			}

			$fs = new Filesystem();
			$fs->mirror( $allure_dir, $final_dir );

			$orchestrator->post_processing_message( 'Allure reports saved' );

		} catch ( \Exception $e ) {
			// If saving to final location fails, reports remain in original location
			$io->text( 'Final location save failed: ' . $e->getMessage() );
			$io->text( "Allure reports remain available in original location: {$allure_dir}" );
			$io->text( 'Reports will be zipped from original location for CI processing' );
		}

		return $allure_status;
	}

	/**
	 * Map container result paths to host artifact directories
	 *
	 * @return array<array{container_path: string, host_path: string, type: string}>
	 */
	public function map_container_to_host_paths( TestPackageManifest $manifest, string $package_id, string $host_artifacts_dir ): array {
		$mappings = [];
		$results  = $manifest->get_test_results();

		foreach ( $results as $type => $container_path ) {
			// Handle relative paths
			if ( strpos( $container_path, './' ) === 0 ) {
				// Use centralized method for consistent container paths
				$container_base = \QIT_CLI\PreCommand\Objects\TestPackageManifest::create_container_path( $package_id );
				$container_path = $container_base . '/' . substr( $container_path, 2 );
			}

			$host_path = rtrim( $host_artifacts_dir, '/' ) . '/' . $package_id . '/' . $type;

			$mappings[] = [
				'container_path' => $container_path,
				'host_path'      => $host_path,
				'type'           => $type,
			];
		}

		return $mappings;
	}

	/**
	 * Tag CTRF with package metadata instead of plugin slug
	 *
	 * @return array{packageSlug: string, testType: string, namespace: string}
	 */
	public function tag_ctrf_with_package_metadata( string $package_id, TestPackageManifest $manifest ): array {
		return [
			'packageSlug' => $package_id,
			'testType'    => $manifest->get_test_type(),
			'namespace'   => $manifest->get_namespace(),
			'packageId'   => $manifest->get_package_id(),
		];
	}

	/**
	 * Add package metadata to the merged CTRF report's extra field
	 * This enriches the CTRF with QIT-specific package information while maintaining schema compliance
	 *
	 * @phan-suppress PhanTypeInvalidDimOffset, PhanTypePossiblyInvalidDimOffset
	 */
	private function add_package_metadata_to_merged_ctrf( string $ctrf_path ): void {
		if ( ! file_exists( $ctrf_path ) ) {
			return;
		}

		$ctrf = json_decode( file_get_contents( $ctrf_path ), true );
		if ( ! is_array( $ctrf ) || ! isset( $ctrf['results'] ) || ! is_array( $ctrf['results'] ) ) {
			return;
		}

		// Initialize results.extra if it doesn't exist
		if ( ! isset( $ctrf['results']['extra'] ) ) {
			$ctrf['results']['extra'] = [];
		}

		// Build package details from individual test metadata
		$package_details = [];
		$package_tests   = [];
		$package_order   = [];
		$order_counter   = 0;

		// Scan through all tests to gather package information
		if ( isset( $ctrf['results']['tests'] ) && is_array( $ctrf['results']['tests'] ) ) {
			foreach ( $ctrf['results']['tests'] as $index => $test ) {
				if ( isset( $test['extra']['packageSlug'] ) ) {
					$pkg_id = $test['extra']['packageSlug'];

					// Track execution order
					if ( ! isset( $package_order[ $pkg_id ] ) ) {
						$package_order[ $pkg_id ] = ++$order_counter;
					}

					// Initialize package entry if not seen before
					if ( ! isset( $package_details[ $pkg_id ] ) ) {
						// packageId MUST be present - it comes from the manifest
						if ( ! isset( $test['extra']['packageId'] ) ) {
							throw new \RuntimeException( "Missing packageId in test metadata for package: {$pkg_id}" );
						}

						// Get isLocal from static map or test metadata
						$is_local = $test['extra']['isLocal'] ?? self::get_package_is_local( $pkg_id );
						if ( $is_local === null ) {
							throw new \RuntimeException( "Missing isLocal in test metadata for package: {$pkg_id}" );
						}

						$package_details[ $pkg_id ] = [
							'packageId'      => $test['extra']['packageId'], // Always use manifest packageId - no fallback
							'namespace'      => $test['extra']['namespace'] ?? 'unknown',
							'testType'       => $test['extra']['testType'] ?? 'unknown',
							'hasRunPhase'    => ( $test['extra']['phase'] ?? '' ) === 'run',
							'testCount'      => 0,
							'packageType'    => 'utility', // Default to utility, will update if run phase found
							'executionOrder' => $package_order[ $pkg_id ],
							'firstSeen'      => $index,
							'duration'       => 0,
							'isLocal'        => $is_local, // Package source - deterministic from static map
						];
					}

					// Count tests and accumulate duration per package (only from run phase)
					if ( ( $test['extra']['phase'] ?? '' ) === 'run' ) {
						++$package_details[ $pkg_id ]['testCount'];
						$package_details[ $pkg_id ]['hasRunPhase'] = true;
						$package_details[ $pkg_id ]['packageType'] = 'test';

						// Add duration if available
						if ( isset( $test['duration'] ) && is_numeric( $test['duration'] ) ) {
							$package_details[ $pkg_id ]['duration'] += (int) $test['duration'];
						}
					}
				}
			}
		}

		// Add blob and allure tracking information
		if ( $this->blob_tracking ) {
			foreach ( $package_details as $pkg_id => &$details ) {
				$pkg_basename             = basename( $pkg_id );
				$details['hasBlobReport'] = ! in_array( $pkg_basename, $this->blob_tracking['packages_without_blob'], true );
			}
		}

		if ( $this->allure_tracking ) {
			foreach ( $package_details as $pkg_id => &$details ) {
				$pkg_basename               = basename( $pkg_id );
				$details['hasAllureReport'] = ! in_array( $pkg_basename, $this->allure_tracking['packages_without_allure'], true );
			}
		}

		// Keep original for test enhancement, convert to indexed for metadata
		$package_details_original = $package_details;
		$package_details_indexed  = array_values( $package_details );

		// Build the QIT package metadata structure
		$qit_metadata = [
			'version'  => '1.0.0',
			'packages' => $package_details_indexed,
		];

		// Add summary statistics
		$total_packages      = count( $package_details );
		$packages_with_tests = count( array_filter( $package_details, function ( $pkg ) {
			return $pkg['hasRunPhase'] && $pkg['testCount'] > 0;
		} ) );
		$utility_packages    = $total_packages - $packages_with_tests;

		$qit_metadata['summary'] = [
			'totalPackages'     => $total_packages,
			'packagesWithTests' => $packages_with_tests,
			'utilityPackages'   => $utility_packages,
		];

		// Add report completeness information
		if ( $this->blob_tracking || $this->allure_tracking ) {
			$qit_metadata['reportCompleteness'] = [];

			if ( $this->blob_tracking ) {
				$qit_metadata['reportCompleteness']['blob'] = [
					'complete'               => $this->blob_tracking['packages_with_blob'] === $packages_with_tests,
					'packagesWithBlob'       => $this->blob_tracking['packages_with_blob'],
					'totalPackagesWithTests' => $packages_with_tests,
					'missingFrom'            => $this->blob_tracking['packages_without_blob'],
				];
			}

			if ( $this->allure_tracking ) {
				$qit_metadata['reportCompleteness']['allure'] = [
					'complete'               => $this->allure_tracking['packages_with_allure'] === $packages_with_tests,
					'packagesWithAllure'     => $this->allure_tracking['packages_with_allure'],
					'totalPackagesWithTests' => $packages_with_tests,
					'missingFrom'            => $this->allure_tracking['packages_without_allure'],
				];
			}
		}

		// Add to results.extra
		$ctrf['results']['extra']['qitPackageMetadata'] = $qit_metadata;

		// Also add a marker to results.tool.extra to identify this as QIT orchestrated
		if ( isset( $ctrf['results']['tool'] ) ) {
			if ( ! isset( $ctrf['results']['tool']['extra'] ) ) {
				$ctrf['results']['tool']['extra'] = [];
			}
			$ctrf['results']['tool']['extra']['orchestrationType'] = 'test-packages';
		}

		// Now enhance each test with packageType and executionOrder
		if ( isset( $ctrf['results']['tests'] ) && is_array( $ctrf['results']['tests'] ) ) {
			foreach ( $ctrf['results']['tests'] as &$test ) {
				if ( isset( $test['extra']['packageSlug'] ) ) {
					$pkg_id = $test['extra']['packageSlug'];
					if ( isset( $package_details_original[ $pkg_id ] ) ) {
						$test['extra']['packageType']  = $package_details_original[ $pkg_id ]['packageType'];
						$test['extra']['packageOrder'] = $package_details_original[ $pkg_id ]['executionOrder'];
					}
				}
			}
		}

		// Write back the enhanced CTRF
		file_put_contents( $ctrf_path, json_encode( $ctrf, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	}
}
