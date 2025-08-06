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

	public function __construct( Docker $docker, NodeDependencyManager $node_deps ) {
		$this->node_deps = $node_deps;
		$this->docker    = $docker;
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
		$this->collect_allure( $env, $slug, $mf, $dir );

		// --------- 3️⃣  collect Blob (mandatory for run phase) ----------------
		$this->collect_blob(
			$env,
			$slug,
			$mf,
			$dir,
			/* mandatory = */ $phase === 'run',   // ← only "run" is mandatory
			$phase
		);
	}

	private function collect_ctrf(
		E2EEnvInfo $env,
		string $slug,
		TestPackageManifest $mf,
		string $dir,
		bool $mandatory,
		string $phase
	): void {

		$rel = $mf->getTestResults()['ctrf-json'] ?? null;
		if ( ! $rel ) {
			if ( $mandatory ) {
				throw new RuntimeException( "manifest lacks ctrf-json for phase '{$phase}'" );
			}

			return;                 // optional → skip
		}

		$safe     = ltrim( str_replace( [ '/', ':' ], '_', $slug ), '._' );
		$dst      = $dir . '/ctrf/' . $safe . '.json';
		$dir_path = dirname( $dst );
		if ( ! is_dir( $dir_path ) ) {
			mkdir( $dir_path, 0755, true );
		}

		/* 1 — host path ------------------------------------------------------- */
		$host_pkg = $env->test_packages_metadata[ $slug ]['path'] ?? '';
		$host_src = rtrim( $host_pkg, '/' ) . '/' . ltrim( $rel, './' );
		if ( is_readable( $host_src ) ) {
			copy( $host_src, $dst );
			$this->tag_ctrf( $dst, $slug, $mf, $phase );

			return;
		}

		/* 2 — container fallback --------------------------------------------- */
		$ctr_path = '/qit/packages/' . basename( $slug ) . '/' . ltrim( $rel, './' );
		try {
			$this->docker->copy_from_docker( $env, $ctr_path, $dst, 'php' );
			$this->tag_ctrf( $dst, $slug, $mf, $phase );
		} catch ( \RuntimeException $e ) {
			if ( $mandatory ) {
				throw $e;           // only fail for "run"
			}
			// optional → do nothing
		}
	}

	private function collect_allure(
		E2EEnvInfo $env,
		string $slug,
		TestPackageManifest $mf,
		string $dir
	): void {

		$rel = $mf->getTestResults()['allure-dir'] ?? null;
		if ( ! $rel ) {
			return;
		}                     // no declaration → skip

		$host_pkg = $env->test_packages_metadata[ $slug ]['path'] ?? '';
		$host_src = rtrim( $host_pkg, '/' ) . '/' . trim( $rel, '/' );

		$dst      = $dir . '/allure/' . basename( $slug );
		$dir_path = dirname( $dst );
		if ( ! is_dir( $dir_path ) ) {
			mkdir( $dir_path, 0755, true );
		}

		/* host first */
		if ( is_dir( $host_src ) ) {
			// Use Symfony Filesystem mirror instead of custom implementation
			$fs = new Filesystem();
			$fs->mirror( $host_src, $dst );

			return;
		}

		/* container fallback */
		$ctr_path = '/qit/packages/' . basename( $slug ) . '/' . trim( $rel, '/' );
		try {
			$this->docker->copy_from_docker( $env, $ctr_path, $dst, 'php' );
		} catch ( \RuntimeException $e ) {
			// Never mandatory for allure collection - silently ignore failures
			unset( $e ); // Explicitly acknowledge the exception is not used
		}
	}

	private function collect_blob(
		E2EEnvInfo $env,
		string $slug,
		TestPackageManifest $mf,
		string $dir,
		bool $mandatory,
		string $phase
	): void {

		$rel = $mf->getTestResults()['blob-dir'] ?? null;
		if ( ! $rel ) {
			if ( $mandatory ) {
				throw new RuntimeException( "manifest lacks blob-dir for phase '{$phase}'" );
			}
			return;                 // optional → skip
		}

		$host_pkg = $env->test_packages_metadata[ $slug ]['path'] ?? '';
		$host_src = rtrim( $host_pkg, '/' ) . '/' . trim( $rel, '/' );

		$dst      = $dir . '/blob/' . basename( $slug );
		$dir_path = dirname( $dst );
		if ( ! is_dir( $dir_path ) ) {
			mkdir( $dir_path, 0755, true );
		}

		/* host first */
		if ( is_dir( $host_src ) ) {
			// Validate blob directory structure
			$this->validate_blob_directory( $host_src );

			// Use Symfony Filesystem mirror instead of custom implementation
			$fs = new Filesystem();
			$fs->mirror( $host_src, $dst );

			return;
		}

		/* container fallback */
		$ctr_path = '/qit/packages/' . basename( $slug ) . '/' . trim( $rel, '/' );
		try {
			$this->docker->copy_from_docker( $env, $ctr_path, $dst, 'php' );
			// Validate after copying from container
			$this->validate_blob_directory( $dst );
		} catch ( \RuntimeException $e ) {
			if ( $mandatory ) {
				throw $e;           // only fail for "run"
			}
			// optional → do nothing
		}
	}


	/**
	 * Validate blob directory structure
	 */
	private function validate_blob_directory( string $blob_dir ): void {
		if ( ! is_dir( $blob_dir ) ) {
			throw new RuntimeException( "Blob directory does not exist: $blob_dir" );
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

		if ( ! $has_blob_files ) {
			throw new RuntimeException( "No blob reporter files found in directory: $blob_dir. Expected .zip files from Playwright blob reporter." );
		}
	}

	/**
	 * Tag CTRF file with package metadata (host version)
	 */
	private function tag_ctrf( string $ctrf_path, string $slug, TestPackageManifest $mf, string $phase ): void {
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
				$test['extra']['testType']    = $mf->getTestType();
				$test['extra']['namespace']   = $mf->getNamespace();
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

		// Check if there are blob reports to merge
		$package_dirs = glob( $blob_dir . '/*', GLOB_ONLYDIR );
		if ( empty( $package_dirs ) ) {
			$io->text( 'No blob reports found to merge.' );
			return;
		}

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
			$orchestrator->post_processing_message( 'Blob merge failed', false );
			throw new RuntimeException( 'Blob merge failed: ' . $proc->getErrorOutput() );
		}

		$orchestrator->post_processing_message( 'HTML report generated' );
	}

	public function merge_ctrf( string $artifacts_dir, SymfonyStyle $io, PackageOrchestrator $orchestrator ): void {
		$ctrf_dir = $artifacts_dir . '/ctrf';

		// Skip if no CTRF files
		if ( ! is_dir( $ctrf_dir ) || empty( glob( $ctrf_dir . '/*.json' ) ) ) {
			return;
		}

		// Ensure ctrf-cli is available
		$bin_dir  = $this->node_deps->ensure_packages( [ 'ctrf-cli' ], $io );
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
			$orchestrator->post_processing_message( 'CTRF reports merged' );
		}
	}

	/**
	 * Save Allure reports to final location, preserving per-package structure
	 */
	public function save_allure_to_final_location( string $artifacts_dir, SymfonyStyle $io, PackageOrchestrator $orchestrator ): void {
		$allure_dir = $artifacts_dir . '/allure';

		// Skip if no Allure directories
		if ( ! is_dir( $allure_dir ) || empty( glob( $allure_dir . '/*', GLOB_ONLYDIR ) ) ) {
			return;
		}

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
	}

	/**
	 * Map container result paths to host artifact directories
	 *
	 * @return array<array{container_path: string, host_path: string, type: string}>
	 */
	public function map_container_to_host_paths( TestPackageManifest $manifest, string $package_id, string $host_artifacts_dir ): array {
		$mappings = [];
		$results  = $manifest->getTestResults();

		foreach ( $results as $type => $container_path ) {
			// Handle relative paths
			if ( strpos( $container_path, './' ) === 0 ) {
				$container_path = '/qit/packages/' . basename( $package_id ) . '/' . substr( $container_path, 2 );
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
			'testType'    => $manifest->getTestType(),
			'namespace'   => $manifest->getNamespace(),
		];
	}
}
