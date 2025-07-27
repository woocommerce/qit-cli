<?php

use QIT\IntegrationTests\Traits\ScaffoldHelpers;
use Symfony\Component\Process\Process;

class TestPackageWorkflowTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;

	public function test_tp001_all_phases_execute_in_order(): void {
		$tempDir    = null;
		$packageDir = null;
		$configPath = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';

			// Scaffold a test package with full Playwright setup (not --only-manifest)
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=qit-test-plugin',
				'--package=tp001-all-phases',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $packageDir );
			$this->assertFileExists( $packageDir . '/manifest.json' );
			$this->assertFileExists( $packageDir . '/package.json' );
			$this->assertFileExists( $packageDir . '/playwright.config.js' );
			$this->assertFileExists( $packageDir . '/tests/example.spec.js' );

			// Modify manifest to include all phases with meaningful commands
			$manifest_path = $packageDir . '/manifest.json';
			$manifest      = json_decode( file_get_contents( $manifest_path ), true );

			// Modify manifest to include all phases with real commands
			// Note: Only include phases that have actual scripts created by scaffold
			$manifest['test']['phases'] = [
				'globalSetup'    => [ './bootstrap/global-setup.sh' ],
				'setup'          => [ './bootstrap/setup.sh' ],
				'run'            => [ 'npx playwright test' ], // Real Playwright test execution
				'teardown'       => [], // No teardown script is created by scaffold
				'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
			];

			file_put_contents( $manifest_path, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			// Create qit.json configuration
			$qit_json = [
				'$schema'      => 'https://qit.woo.com/json-schema/qit',
				'sut'          => [
					'type'   => 'plugin',
					'slug'   => 'woocommerce',
					'source' => [ 'type' => 'wporg' ]
				],
				'test_types'   => [
					'e2e' => [
						'default' => [
							'test_packages' => [ $packageDir ]
						]
					]
				],
				'environments' => [
					'without-setup' => [
						'php' => '8.2',
						'wp'  => 'stable'
					]
				]
			];

			$configPath = $tempDir . '/qit-config.json';
			file_put_contents( $configPath, json_encode( $qit_json, JSON_PRETTY_PRINT ) );

			// Run the test
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--environment=without-setup',
				'--config=' . $configPath
			], return_process: true );

			// Exit code assertion
			$this->assertSame( 0, $proc->getExitCode(),
				$proc->getErrorOutput() ?: $proc->getOutput() );

			$out = $proc->getOutput();

			// Phase order assertion using stdout parsing
			// Note: Empty phases (like teardown with []) are not executed and won't appear in output
			preg_match_all( '~\((globalSetup|setup|run|teardown|globalTeardown)\)~', $out, $m );
			$this->assertSame(
				[ 'globalSetup', 'setup', 'run', 'globalTeardown' ],
				$m[1],
				'Phases did not run in expected order'
			);

			// CTRF assertion - check merged report path in output
			if ( preg_match( '~CTRF merged →\s+(.+/ctrf-report\.json)~', $out, $ctrfMatch ) ) {
				$ctrfPath = $ctrfMatch[1];
				// The path might be relative to artifacts directory, so we need to find it
				// Let's look for the artifacts directory pattern in the output
				if ( preg_match( '~Allure reports saved to final location: (.+/final/allure)~', $out, $artifactsMatch ) ) {
					$artifactsBase  = dirname( $artifactsMatch[1] );
					$mergedCtfrPath = $artifactsBase . '/ctrf/ctrf-report.json';
					if ( file_exists( $mergedCtfrPath ) ) {
						$ctrf    = json_decode( file_get_contents( $mergedCtfrPath ), true );
						$summary = $ctrf['results']['summary'] ?? [];
						$this->assertIsArray( $summary, 'CTRF summary should be an array' );
						// The scaffolded test may fail due to site not being reachable, but we still check structure
						$this->assertArrayHasKey( 'tests', $summary, 'CTRF summary should have tests count' );
						$this->assertArrayHasKey( 'passed', $summary, 'CTRF summary should have passed count' );
						$this->assertArrayHasKey( 'failed', $summary, 'CTRF summary should have failed count' );
					} else {
						// Fallback to checking the package-local CTRF if merged one doesn't exist
						$packageCtfrPath = $packageDir . '/results/ctrf.json';
						if ( file_exists( $packageCtfrPath ) ) {
							$ctrf    = json_decode( file_get_contents( $packageCtfrPath ), true );
							$summary = $ctrf['results']['summary'] ?? [];
							$this->assertIsArray( $summary, 'CTRF summary should be an array' );
							$this->assertArrayHasKey( 'tests', $summary, 'CTRF summary should have tests count' );
							$this->assertArrayHasKey( 'passed', $summary, 'CTRF summary should have passed count' );
							$this->assertArrayHasKey( 'failed', $summary, 'CTRF summary should have failed count' );
						} else {
							$this->fail( 'Neither merged nor package-local CTRF report found' );
						}
					}
				}
			} else {
				// Fallback to package-local CTRF if merged path not found in output
				$packageCtfrPath = $packageDir . '/results/ctrf.json';
				if ( file_exists( $packageCtfrPath ) ) {
					$ctrf    = json_decode( file_get_contents( $packageCtfrPath ), true );
					$summary = $ctrf['results']['summary'] ?? [];
					$this->assertIsArray( $summary, 'CTRF summary should be an array' );
					$this->assertArrayHasKey( 'tests', $summary, 'CTRF summary should have tests count' );
					$this->assertArrayHasKey( 'passed', $summary, 'CTRF summary should have passed count' );
					$this->assertArrayHasKey( 'failed', $summary, 'CTRF summary should have failed count' );
				} else {
					$this->fail( 'Merged CTRF report path not found in output and package-local CTRF not found' );
				}
			}

		} finally {
			// Clean up resources
			if ( isset( $configPath ) && file_exists( $configPath ) ) {
				unlink( $configPath );
			}
			if ( isset( $packageDir ) && is_dir( $packageDir ) ) {
				$this->recursiveRemoveDirectory( $packageDir );
			}
			if ( isset( $tempDir ) && is_dir( $tempDir ) ) {
				$this->recursiveRemoveDirectory( $tempDir );
			}
		}
	}

	private function recursiveRemoveDirectory( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = "$dir/$file";
			if ( is_dir( $path ) ) {
				$this->recursiveRemoveDirectory( $path );
			} else {
				unlink( $path );
			}
		}
		rmdir( $dir );
	}
}
