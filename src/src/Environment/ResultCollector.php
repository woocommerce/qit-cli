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
		$this->collectCtrf(
			$env,
			$slug,
			$mf,
			$dir,
			/* mandatory = */ $phase === 'run',   // ← only "run" is mandatory
			$phase
		);

		// --------- 2️⃣  collect Allure (never mandatory) ----------------------
		$this->collectAllure( $env, $slug, $mf, $dir, $phase );
	}

	private function collectCtrf(
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
		$hostPkg = $env->test_packages_metadata[ $slug ]['path'] ?? '';
		$hostSrc = rtrim( $hostPkg, '/' ) . '/' . ltrim( $rel, './' );
		if ( is_readable( $hostSrc ) ) {
			copy( $hostSrc, $dst );
			$this->tagCtrf( $dst, $slug, $mf, $phase );

			return;
		}

		/* 2 — container fallback --------------------------------------------- */
		$ctrPath = '/qit/packages/' . basename( $slug ) . '/' . ltrim( $rel, './' );
		try {
			$this->docker->copy_from_docker( $env, $ctrPath, $dst, 'php' );
			$this->tagCtrf( $dst, $slug, $mf, $phase );
		} catch ( \RuntimeException $e ) {
			if ( $mandatory ) {
				throw $e;           // only fail for "run"
			}
			// optional → do nothing
		}
	}

	private function collectAllure(
		E2EEnvInfo $env,
		string $slug,
		TestPackageManifest $mf,
		string $dir,
		string $phase
	): void {

		$rel = $mf->getTestResults()['allure-dir'] ?? null;
		if ( ! $rel ) {
			return;
		}                     // no declaration → skip

		$hostPkg = $env->test_packages_metadata[ $slug ]['path'] ?? '';
		$hostSrc = rtrim( $hostPkg, '/' ) . '/' . trim( $rel, '/' );

		$dst = $dir . '/allure/' . basename( $slug );
		@mkdir( dirname( $dst ), 0755, true );

		/* host first */
		if ( is_dir( $hostSrc ) ) {
			$this->recursiveCopy( $hostSrc, $dst );

			return;
		}

		/* container fallback */
		$ctrPath = '/qit/packages/' . basename( $slug ) . '/' . trim( $rel, '/' );
		try {
			$this->docker->copy_from_docker( $env, $ctrPath, $dst, 'php' );
		} catch ( \RuntimeException $e ) {
			// never mandatory – just ignore
		}
	}

	/**
	 * Recursively copy a directory from source to destination
	 */
	private function recursiveCopy( string $src, string $dst ): void {
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
	private function tagCtrf( string $ctrfPath, string $slug, TestPackageManifest $mf, string $phase ): void {
		if ( ! file_exists( $ctrfPath ) ) {
			return;
		}

		$data = json_decode( file_get_contents( $ctrfPath ), true );
		if ( is_array( $data ) && ! empty( $data["results"]["tests"] ) && is_array( $data["results"]["tests"] ) ) {
			foreach ( $data["results"]["tests"] as &$test ) {
				if ( ! isset( $test["extra"] ) ) {
					$test["extra"] = [];
				}
				$test["extra"]["packageSlug"] = $slug;
				$test["extra"]["phase"]       = $phase;
				$test["extra"]["testType"]    = $mf->getTestType();
				$test["extra"]["namespace"]   = $mf->getNamespace();
			}
			file_put_contents( $ctrfPath, json_encode( $data, JSON_PRETTY_PRINT ) );
		}
	}

	/**
	 * Tag CTRF file inside the container with package metadata
	 */
	private function tagCtrfInContainer( E2EEnvInfo $env_info, string $container_ctrf_path, string $package_id, TestPackageManifest $manifest, string $phase ): void {
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
				false
			);
		} catch ( RuntimeException $e ) {
			// Tagging failed - continue anyway
		}
	}

	/**
	 * Merge all collected artifacts into final reports
	 */
	public function mergeAllArtifacts( string $artifactsDir, SymfonyStyle $io ): void {
		$this->mergeCtrf( $artifactsDir, $io );
		$this->mergeAllure( $artifactsDir, $io );
	}

	private function mergeCtrf( string $artifactsDir, SymfonyStyle $io ): void {
		$ctrfDir = $artifactsDir . '/ctrf';

		// Skip if no CTRF files
		if ( ! is_dir( $ctrfDir ) || empty( glob( $ctrfDir . '/*.json' ) ) ) {
			return;
		}

		// Ensure ctrf-cli is available
		$bin_dir  = $this->node_deps->ensurePackages( [ 'ctrf-cli' ], $io );
		$ctrf_bin = $bin_dir . '/ctrf';

		$io->text( 'Merging CTRF reports...' );

		$proc = new Process( [ $ctrf_bin, 'merge', $ctrfDir ] );
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
		$final_dir = $artifactsDir . '/final/ctrf';
		if ( ! is_dir( $final_dir ) ) {
			mkdir( $final_dir, 0755, true );
		}

		if ( file_exists( $ctrfDir . '/ctrf-report.json' ) ) {
			// Remove existing file to prevent rename() failures on reruns
			@unlink( $final_dir . '/ctrf-report.json' );
			rename( $ctrfDir . '/ctrf-report.json', $final_dir . '/ctrf-report.json' );
		}
	}

	/**
	 * Merge Allure results from multiple test packages into a unified structure
	 */
	private function mergeAllure( string $artifactsDir, SymfonyStyle $io ): void {
		$allureDir = $artifactsDir . '/allure';
		
		// Skip if no Allure directories
		if ( ! is_dir( $allureDir ) || empty( glob( $allureDir . '/*', GLOB_ONLYDIR ) ) ) {
			return;
		}
		
		$io->text( 'Merging Allure reports...' );
		
		// Create merged directory
		$mergedDir = $artifactsDir . '/allure-merged';
		if ( ! is_dir( $mergedDir ) ) {
			mkdir( $mergedDir, 0755, true );
		}
		
		// Find all plugin-specific allure directories
		$pluginDirs = glob( $allureDir . '/*', GLOB_ONLYDIR );
		
		foreach ( $pluginDirs as $pluginDir ) {
			if ( is_dir( $pluginDir ) ) {
				$this->recursiveCopy( $pluginDir, $mergedDir );
			}
		}
		
		// Replace the original allure directory with merged results
		if ( is_dir( $mergedDir ) && ! empty( glob( $mergedDir . '/*' ) ) ) {
			// Remove original segmented directory
			$this->removeDirectory( $allureDir );
			// Move merged results to expected location
			rename( $mergedDir, $allureDir );
		}
	}

	/**
	 * Recursively remove a directory and all its contents
	 */
	private function removeDirectory( string $dir ): void {
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
	 */
	public function tag_ctrf_with_package_metadata( string $package_id, TestPackageManifest $manifest ): array {
		return [
			'packageSlug' => $package_id,
			'testType'    => $manifest->getTestType(),
			'namespace'   => $manifest->getNamespace(),
		];
	}
}
