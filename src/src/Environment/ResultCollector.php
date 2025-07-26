<?php

namespace QIT_CLI\Environment;

use QIT_CLI\Environment\Environments\E2E\E2EEnvInfo;
use QIT_CLI\PreCommand\Objects\TestPackageManifest;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Style\SymfonyStyle;
use RuntimeException;

/**
 * Handles result collection and merging for CTRF and Allure reports
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

		$safe = ltrim( str_replace( [ '/', ':' ], '_', $slug ), '._' );
		$dst  = $dir . '/ctrf/' . $safe . '.json';
		@mkdir( dirname( $dst ), 0755, true );

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

		$dst = $dir . '/allure/' . basename( $slug );
		@mkdir( dirname( $dst ), 0755, true );

		/* host first */
		if ( is_dir( $host_src ) ) {
			$this->recursive_copy( $host_src, $dst );

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

	/**
	 * Recursively copy a directory from source to destination
	 */
	private function recursive_copy( string $src, string $dst ): void {
		if ( ! is_dir( $src ) ) {
			return;
		}

		if ( ! is_dir( $dst ) ) {
			mkdir( $dst, 0755, true );
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $src, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $item ) {
			$target = $dst . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
			if ( $item->isDir() ) {
				if ( ! is_dir( $target ) ) {
					mkdir( $target, 0755, true );
				}
			} else {
				copy( $item, $target );
			}
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

	/**
	 * Tag CTRF file inside the container with package metadata
	 *
	 * @phpstan-ignore-next-line method.unused
	 */
	private function tag_ctrf_in_container( E2EEnvInfo $env_info, string $container_ctrf_path, string $package_id, TestPackageManifest $manifest, string $phase ): void {
		$tag_script = sprintf(
			'if [ -f "%s" ]; then
                php -r "
                    \$data = json_decode(file_get_contents(\"%s\"), true);
                    if (is_array(\$data) && !empty(\$data[\"results\"][\"tests\"]) && is_array(\$data[\"results\"][\"tests\"])) {
                        foreach (\$data[\"results\"][\"tests\"] as &\$test) {
                            if (!isset(\$test[\"extra\"])) {
                                \$test[\"extra\"] = [];
                            }
                            \$test[\"extra\"][\"packageSlug\"] = \"%s\";
                            \$test[\"extra\"][\"phase\"] = \"%s\";
                            \$test[\"extra\"][\"testType\"] = \"%s\";
                            \$test[\"extra\"][\"namespace\"] = \"%s\";
                        }
                        file_put_contents(\"%s\", json_encode(\$data, JSON_PRETTY_PRINT));
                    }
                ";
            fi',
			$container_ctrf_path,
			$container_ctrf_path,
			$package_id,
			$phase,
			$manifest->getTestType(),
			$manifest->getNamespace(),
			$container_ctrf_path
		);

		try {
			$this->docker->run_inside_docker(
				$env_info,
				[ '/bin/bash', '-c', $tag_script ],
				[],
				null,
				30,
				'php'
			);
		} catch ( RuntimeException $e ) {
			// Tagging failed - continue anyway
			unset( $e ); // Explicitly acknowledge the exception is not used
		}
	}

	/**
	 * Merge all collected artifacts into final reports
	 */
	public function merge_all_artifacts( string $artifacts_dir, SymfonyStyle $io ): void {
		$this->merge_ctrf( $artifacts_dir, $io );
		$this->merge_allure( $artifacts_dir, $io );
	}

	private function merge_ctrf( string $artifacts_dir, SymfonyStyle $io ): void {
		$ctrf_dir = $artifacts_dir . '/ctrf';

		// Skip if no CTRF files
		if ( ! is_dir( $ctrf_dir ) || empty( glob( $ctrf_dir . '/*.json' ) ) ) {
			return;
		}

		// Ensure ctrf-cli is available
		$bin_dir  = $this->node_deps->ensure_packages( [ 'ctrf-cli' ], $io );
		$ctrf_bin = $bin_dir . '/ctrf';

		$io->text( 'Merging CTRF reports...' );

		$proc = new Process( [ $ctrf_bin, 'merge', $ctrf_dir ] );
		$proc->setTimeout( 300 );
		$proc->run( function ( $type, $buf ) use ( $io ) {
			if ( ! $io->isQuiet() ) {
				$io->write( $buf );
			}
		} );

		if ( ! $proc->isSuccessful() ) {
			throw new RuntimeException( 'CTRF merge failed: ' . $proc->getErrorOutput() );
		}

		// Move merged report to final location
		$final_dir = $artifacts_dir . '/final/ctrf';
		if ( ! is_dir( $final_dir ) ) {
			mkdir( $final_dir, 0755, true );
		}

		if ( file_exists( $ctrf_dir . '/ctrf-report.json' ) ) {
			// Remove existing file to prevent rename() failures on reruns
			@unlink( $final_dir . '/ctrf-report.json' );
			rename( $ctrf_dir . '/ctrf-report.json', $final_dir . '/ctrf-report.json' );
		}
	}

	/**
	 * Merge Allure results from multiple test packages into a unified structure
	 */
	private function merge_allure( string $artifacts_dir, SymfonyStyle $io ): void {
		$allure_dir = $artifacts_dir . '/allure';

		// Skip if no Allure directories
		if ( ! is_dir( $allure_dir ) || empty( glob( $allure_dir . '/*', GLOB_ONLYDIR ) ) ) {
			return;
		}

		$io->text( 'Merging Allure reports...' );

		// Create merged directory
		$merged_dir = $artifacts_dir . '/allure-merged';
		if ( ! is_dir( $merged_dir ) ) {
			mkdir( $merged_dir, 0755, true );
		}

		// Find all plugin-specific allure directories
		$plugin_dirs = glob( $allure_dir . '/*', GLOB_ONLYDIR );

		foreach ( $plugin_dirs as $plugin_dir ) {
			if ( is_dir( $plugin_dir ) ) {
				$this->recursive_copy( $plugin_dir, $merged_dir );
			}
		}

		// Replace the original allure directory with merged results
		if ( is_dir( $merged_dir ) && ! empty( glob( $merged_dir . '/*' ) ) ) {
			// Remove original segmented directory
			$this->remove_directory( $allure_dir );
			// Move merged results to expected location
			rename( $merged_dir, $allure_dir );
		}
	}

	/**
	 * Recursively remove a directory and all its contents
	 */
	private function remove_directory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isDir() ) {
				rmdir( $file->getPathname() );
			} else {
				unlink( $file->getPathname() );
			}
		}
		rmdir( $dir );
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
