<?php

use QIT\IntegrationTests\Traits\ScaffoldHelpers;
use QIT\IntegrationTests\Traits\CtrfSnapshotNormalizer;
use Spatie\Snapshots\MatchesSnapshots;

class TestPackageWorkflowTest extends \PHPUnit\Framework\TestCase {
	use ScaffoldHelpers;
	use MatchesSnapshots;
	use CtrfSnapshotNormalizer;

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

	public function test_tp002_ctrf_result_collection(): void {
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
				'--package=tp002-ctrf',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $packageDir );
			$this->assertFileExists( $packageDir . '/manifest.json' );
			$this->assertFileExists( $packageDir . '/package.json' );
			$this->assertFileExists( $packageDir . '/playwright.config.js' );
			$this->assertFileExists( $packageDir . '/tests/example.spec.js' );

			// Modify manifest to include all phases with real commands
			// Note: Only include phases that have actual scripts created by scaffold
			$manifest_path              = $packageDir . '/manifest.json';
			$manifest                   = json_decode( file_get_contents( $packageDir . '/manifest.json' ), true );
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

			// CTRF collection validation
			$this->validateCtfrCollection( $out, $packageDir );

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

	public function test_tp003_local_vs_published_packages(): void {
		$tempDir              = null;
		$localPackageDir      = null;
		$downloadedPackageDir = null;
		$configPath           = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
			mkdir( $tempDir, 0755, true );

			// Create local test package
			$localPackageDir = $tempDir . '/local-test-package';
			qit( [
				'package:scaffold',
				$localPackageDir,
				'--namespace=qit-test-plugin',
				'--package=tp003-local',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $localPackageDir );
			$this->assertFileExists( $localPackageDir . '/manifest.json' );

			// Modify local package manifest
			$localManifest                   = json_decode( file_get_contents( $localPackageDir . '/manifest.json' ), true );
			$localManifest['test']['phases'] = [
				'globalSetup'    => [ './bootstrap/global-setup.sh' ],
				'setup'          => [ './bootstrap/setup.sh' ],
				'run'            => [ 'npx playwright test' ],
				'teardown'       => [],
				'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
			];
			file_put_contents( $localPackageDir . '/manifest.json', json_encode( $localManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			// Publish the package
			$publishProcess = qit( [
				'package:publish',
				$localPackageDir,
				'1.0.0',
				'--force'
			], return_process: true );

			// Check if publish was successful
			$this->assertSame( 0, $publishProcess->getExitCode(),
				'Package publish failed: ' . $publishProcess->getErrorOutput() ?: $publishProcess->getOutput() );

			$publishOutput = $publishProcess->getOutput();
			$this->assertStringContainsString( 'Package published successfully', $publishOutput );

			// Download the published package
			$downloadDir = $tempDir . '/downloaded-packages';
			mkdir( $downloadDir, 0755, true );

			$downloadProcess = qit( [
				'package:download',
				'qit-test-plugin/tp003-local:1.0.0',
				'--output-dir=' . $downloadDir,
				'--force'
			], return_process: true );

			// Check if download was successful
			$this->assertSame( 0, $downloadProcess->getExitCode(),
				'Package download failed: ' . $downloadProcess->getErrorOutput() ?: $downloadProcess->getOutput() );

			$downloadOutput = $downloadProcess->getOutput();
			$this->assertStringContainsString( 'All 1 package(s) downloaded successfully', $downloadOutput );

			// The downloaded package should be in a directory named after the package
			$downloadedPackageDir = $downloadDir . '/qit-test-plugin-tp003-local-1.0.0';
			$this->assertDirectoryExists( $downloadedPackageDir );
			$this->assertFileExists( $downloadedPackageDir . '/manifest.json' );

			// Make bootstrap scripts executable (they lose permissions during zip extraction)
			$bootstrapDir = $downloadedPackageDir . '/bootstrap';
			if ( is_dir( $bootstrapDir ) ) {
				foreach ( glob( $bootstrapDir . '/*.sh' ) as $script ) {
					chmod( $script, 0755 );
				}
			}

			// Create qit.json configuration with both local and "downloaded" (published) packages
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
							'test_packages' => [
								$localPackageDir,
								$downloadedPackageDir  // Use the downloaded package (which was published)
							]
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

			// Run the test with both local and downloaded packages
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

			// Verify both packages were processed
			$this->assertStringContainsString( 'Processing package: ' . $localPackageDir, $out );
			$this->assertStringContainsString( 'Processing package: ' . $downloadedPackageDir, $out );

			// Verify CTRF collection works for both package types
			$this->validateCtfrCollection( $out, $localPackageDir );

		} finally {
			// Clean up resources
			if ( isset( $configPath ) && file_exists( $configPath ) ) {
				unlink( $configPath );
			}
			if ( isset( $localPackageDir ) && is_dir( $localPackageDir ) ) {
				$this->recursiveRemoveDirectory( $localPackageDir );
			}
			if ( isset( $downloadedPackageDir ) && is_dir( $downloadedPackageDir ) ) {
				$this->recursiveRemoveDirectory( $downloadedPackageDir );
			}
			if ( isset( $tempDir ) && is_dir( $tempDir ) ) {
				$this->recursiveRemoveDirectory( $tempDir );
			}
		}
	}

	public function test_tp005_bootstrap_only_package_execution(): void {
		$tempDir    = null;
		$packageDir = null;
		$configPath = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';

			// Scaffold a test package with the default Playwright setup
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=qit-test-plugin',
				'--package=tp005-bootstrap-only',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $packageDir );
			$this->assertFileExists( $packageDir . '/manifest.json' );

			// Modify manifest so that only globalSetup has commands; all other phases are empty.
			$manifest_path              = $packageDir . '/manifest.json';
			$manifest                   = json_decode( file_get_contents( $manifest_path ), true );
			$manifest['test']['phases'] = [
				'globalSetup'    => [ './bootstrap/global-setup.sh' ],
				'setup'          => [],
				'run'            => [],
				'teardown'       => [],
				'globalTeardown' => []
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

			// Phase assertion: only globalSetup should appear
			preg_match_all( '~\\((globalSetup|setup|run|teardown|globalTeardown)\\)~', $out, $m );
			$this->assertSame( [ 'globalSetup' ], $m[1], 'Only globalSetup phase should run' );

			// Ensure other phases do not appear
			$this->assertStringNotContainsString( '(setup)', $out );
			$this->assertStringNotContainsString( '(run)', $out );
			$this->assertStringNotContainsString( '(teardown)', $out );
			$this->assertStringNotContainsString( '(globalTeardown)', $out );

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

	public function test_tp006_ctrf_result_collection_all_phases(): void {
		$tempDir    = null;
		$packageDir = null;
		$configPath = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';

			// Scaffold a test package with full Playwright setup
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=qit-test-plugin',
				'--package=tp006-ctrf-all-phases',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $packageDir );
			$this->assertFileExists( $packageDir . '/manifest.json' );
			$this->assertFileExists( $packageDir . '/package.json' );
			$this->assertFileExists( $packageDir . '/playwright.config.js' );
			$this->assertFileExists( $packageDir . '/tests/example.spec.js' );

			// Modify manifest to include all phases with real commands
			$manifest_path              = $packageDir . '/manifest.json';
			$manifest                   = json_decode( file_get_contents( $manifest_path ), true );
			$manifest['test']['phases'] = [
				'globalSetup'    => [ './bootstrap/global-setup.sh' ],
				'setup'          => [ './bootstrap/setup.sh' ],
				'run'            => [ 'npx playwright test' ],
				'teardown'       => [],
				'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
			];

			// Ensure results directory exists
			$resultsDir = $packageDir . '/results';
			if ( ! is_dir( $resultsDir ) ) {
				mkdir( $resultsDir, 0755, true );
			}

			// Add CTRF configuration explicitly for clarity
			if ( ! isset( $manifest['test']['results'] ) ) {
				$manifest['test']['results'] = [];
			}
			$manifest['test']['results']['ctrf-json'] = './results/ctrf.json';

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
			preg_match_all( '~\\((globalSetup|setup|run|teardown|globalTeardown)\\)~', $out, $m );
			$this->assertSame(
				[ 'globalSetup', 'setup', 'run', 'globalTeardown' ],
				$m[1],
				'Phases did not run in expected order'
			);

			// Validate CTRF collection
			$this->validateCtfrCollection( $out, $packageDir );

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

	public function test_tp007_ctrf_result_collection_published_package(): void {
		$tempDir              = null;
		$localPackageDir      = null;
		$downloadedPackageDir = null;
		$configPath           = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
			mkdir( $tempDir, 0755, true );

			// Step 1: Scaffold local package
			$localPackageDir = $tempDir . '/local-test-package';
			qit( [
				'package:scaffold',
				$localPackageDir,
				'--namespace=qit-test-plugin',
				'--package=tp007-ctrf-published',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $localPackageDir );
			$this->assertFileExists( $localPackageDir . '/manifest.json' );

			// Modify manifest to include all phases and CTRF reporting
			$manifestPath               = $localPackageDir . '/manifest.json';
			$manifest                   = json_decode( file_get_contents( $manifestPath ), true );
			$manifest['test']['phases'] = [
				'globalSetup'    => [ './bootstrap/global-setup.sh' ],
				'setup'          => [ './bootstrap/setup.sh' ],
				'run'            => [ 'npx playwright test' ],
				'teardown'       => [],
				'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
			];

			// Ensure results directory exists and add CTRF path
			$resultsDir = $localPackageDir . '/results';
			if ( ! is_dir( $resultsDir ) ) {
				mkdir( $resultsDir, 0755, true );
			}
			if ( ! isset( $manifest['test']['results'] ) ) {
				$manifest['test']['results'] = [];
			}
			$manifest['test']['results']['ctrf-json'] = './results/ctrf.json';

			file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			// Step 2: Publish the package
			$publishProcess = qit( [
				'package:publish',
				$localPackageDir,
				'1.0.0',
				'--force'
			], return_process: true );

			$this->assertSame( 0, $publishProcess->getExitCode(),
				'Package publish failed: ' . $publishProcess->getErrorOutput() ?: $publishProcess->getOutput() );

			$this->assertStringContainsString( 'Package published successfully', $publishProcess->getOutput() );

			// Step 3: Download the published package
			$downloadDir = $tempDir . '/downloaded';
			mkdir( $downloadDir, 0755, true );

			$downloadProcess = qit( [
				'package:download',
				'qit-test-plugin/tp007-ctrf-published:1.0.0',
				'--output-dir=' . $downloadDir,
				'--force'
			], return_process: true );

			$this->assertSame( 0, $downloadProcess->getExitCode(),
				'Package download failed: ' . $downloadProcess->getErrorOutput() ?: $downloadProcess->getOutput() );

			$this->assertStringContainsString( 'All 1 package(s) downloaded successfully', $downloadProcess->getOutput() );

			// Determine downloaded package directory path
			$downloadedPackageDir = $downloadDir . '/qit-test-plugin-tp007-ctrf-published-1.0.0';
			$this->assertDirectoryExists( $downloadedPackageDir );
			$this->assertFileExists( $downloadedPackageDir . '/manifest.json' );

			// Make bootstrap scripts executable (permissions lost during zip extraction)
			$bootstrapDir = $downloadedPackageDir . '/bootstrap';
			if ( is_dir( $bootstrapDir ) ) {
				foreach ( glob( $bootstrapDir . '/*.sh' ) as $script ) {
					chmod( $script, 0755 );
				}
			}

			// Step 4: Create qit.json configuration referencing the downloaded package
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
							'test_packages' => [ $downloadedPackageDir ]
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

			// Step 5: Run the test using the published package
			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--environment=without-setup',
				'--config=' . $configPath
			], return_process: true );

			$this->assertSame( 0, $proc->getExitCode(),
				$proc->getErrorOutput() ?: $proc->getOutput() );

			$out = $proc->getOutput();

			// Verify package was processed
			$this->assertStringContainsString( 'Processing package: ' . $downloadedPackageDir, $out );

			// Validate CTRF collection for the published package
			$this->validateCtfrCollection( $out, $downloadedPackageDir );

		} finally {
			if ( isset( $configPath ) && file_exists( $configPath ) ) {
				unlink( $configPath );
			}
			if ( isset( $localPackageDir ) && is_dir( $localPackageDir ) ) {
				$this->recursiveRemoveDirectory( $localPackageDir );
			}
			if ( isset( $downloadedPackageDir ) && is_dir( $downloadedPackageDir ) ) {
				$this->recursiveRemoveDirectory( $downloadedPackageDir );
			}
			if ( isset( $tempDir ) && is_dir( $tempDir ) ) {
				$this->recursiveRemoveDirectory( $tempDir );
			}
		}
	}

	public function test_tp008_ctrf_merging_multiple_packages(): void {
		$tempDir              = null;
		$localPackageDir      = null;
		$publishedPackageDir  = null;
		$downloadedPackageDir = null;
		$configPath           = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
			mkdir( $tempDir, 0755, true );

			/**
			 * Step 1: Create LOCAL package (will remain local)
			 */
			$localPackageDir = $tempDir . '/package-local';
			qit( [
				'package:scaffold',
				$localPackageDir,
				'--namespace=qit-test-plugin',
				'--package=tp008-local',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $localPackageDir );
			$this->assertFileExists( $localPackageDir . '/manifest.json' );

			// Modify local package manifest for full phases + CTRF
			$localManifestPath               = $localPackageDir . '/manifest.json';
			$localManifest                   = json_decode( file_get_contents( $localManifestPath ), true );
			$localManifest['test']['phases'] = [
				'globalSetup'    => [ './bootstrap/global-setup.sh' ],
				'setup'          => [ './bootstrap/setup.sh' ],
				'run'            => [ 'npx playwright test' ],
				'teardown'       => [],
				'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
			];

			// Ensure results directory
			$resultsDirLocal = $localPackageDir . '/results';
			if ( ! is_dir( $resultsDirLocal ) ) {
				mkdir( $resultsDirLocal, 0755, true );
			}
			if ( ! isset( $localManifest['test']['results'] ) ) {
				$localManifest['test']['results'] = [];
			}
			$localManifest['test']['results']['ctrf-json'] = './results/ctrf.json';
			file_put_contents( $localManifestPath, json_encode( $localManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			/**
			 * Step 2: Create PUBLISHED package (will be published then downloaded)
			 */
			$publishedPackageDir = $tempDir . '/package-to-publish';
			qit( [
				'package:scaffold',
				$publishedPackageDir,
				'--namespace=qit-test-plugin',
				'--package=tp008-published',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $publishedPackageDir );
			$this->assertFileExists( $publishedPackageDir . '/manifest.json' );

			// Modify manifest similarly
			$publishedManifestPath               = $publishedPackageDir . '/manifest.json';
			$publishedManifest                   = json_decode( file_get_contents( $publishedManifestPath ), true );
			$publishedManifest['test']['phases'] = [
				'globalSetup'    => [ './bootstrap/global-setup.sh' ],
				'setup'          => [ './bootstrap/setup.sh' ],
				'run'            => [ 'npx playwright test' ],
				'teardown'       => [],
				'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
			];
			// Ensure results dir and ctrf path
			$resultsDirPub = $publishedPackageDir . '/results';
			if ( ! is_dir( $resultsDirPub ) ) {
				mkdir( $resultsDirPub, 0755, true );
			}
			if ( ! isset( $publishedManifest['test']['results'] ) ) {
				$publishedManifest['test']['results'] = [];
			}
			$publishedManifest['test']['results']['ctrf-json'] = './results/ctrf.json';
			file_put_contents( $publishedManifestPath, json_encode( $publishedManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			// Publish package
			$publishProcess = qit( [
				'package:publish',
				$publishedPackageDir,
				'1.0.0',
				'--force'
			], return_process: true );

			$this->assertSame( 0, $publishProcess->getExitCode(),
				'Publish failed: ' . $publishProcess->getErrorOutput() ?: $publishProcess->getOutput() );

			// Download published package
			$downloadDir = $tempDir . '/downloaded';
			mkdir( $downloadDir, 0755, true );

			$downloadProcess = qit( [
				'package:download',
				'qit-test-plugin/tp008-published:1.0.0',
				'--output-dir=' . $downloadDir,
				'--force'
			], return_process: true );
			$this->assertSame( 0, $downloadProcess->getExitCode(),
				'Download failed: ' . $downloadProcess->getErrorOutput() ?: $downloadProcess->getOutput() );

			$downloadedPackageDir = $downloadDir . '/qit-test-plugin-tp008-published-1.0.0';
			$this->assertDirectoryExists( $downloadedPackageDir );
			$this->assertFileExists( $downloadedPackageDir . '/manifest.json' );

			// Fix permissions on scripts
			$bootstrapDirDownload = $downloadedPackageDir . '/bootstrap';
			if ( is_dir( $bootstrapDirDownload ) ) {
				foreach ( glob( $bootstrapDirDownload . '/*.sh' ) as $script ) {
					chmod( $script, 0755 );
				}
			}

			/**
			 * Step 3: Run both packages together
			 */
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
							'test_packages' => [
								$localPackageDir,
								$downloadedPackageDir
							]
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

			$proc = qit( [
				'run:e2e',
				'woocommerce',
				'--environment=without-setup',
				'--config=' . $configPath
			], return_process: true );

			$this->assertSame( 0, $proc->getExitCode(),
				$proc->getErrorOutput() ?: $proc->getOutput() );

			$out = $proc->getOutput();

			// Verify both packages processed
			$this->assertStringContainsString( 'Processing package: ' . $localPackageDir, $out );
			$this->assertStringContainsString( 'Processing package: ' . $downloadedPackageDir, $out );

			// Verify merged CTRF exists and contains at least two tests
			$this->assertMatchesRegularExpression( '~CTRF merged →\\s+.+/ctrf-report\\.json~', $out );

			if ( preg_match( '~CTRF merged →\\s+(.+/ctrf-report\\.json)~', $out, $ctrfMatch ) ) {
				if ( preg_match( '~Allure reports saved to final location: (.+/final/allure)~', $out, $artifactsMatch ) ) {
					$artifactsBase  = dirname( $artifactsMatch[1] );
					$mergedCtfrPath = $artifactsBase . '/ctrf/ctrf-report.json';

					$this->assertFileExists( $mergedCtfrPath );
					$ctrf_json = json_decode( file_get_contents( $mergedCtfrPath ), true );

					$this->assertIsArray( $ctrf_json );
					$this->assertArrayHasKey( 'results', $ctrf_json );
					$this->assertArrayHasKey( 'tests', $ctrf_json['results'] );
					$this->assertGreaterThanOrEqual( 2, count( $ctrf_json['results']['tests'] ), 'Merged CTRF should contain tests from both packages' );

					// Snapshot for stability
					$this->assertCtrfMatchesSnapshot( $ctrf_json );
				}
			}

		} finally {
			if ( isset( $configPath ) && file_exists( $configPath ) ) {
				unlink( $configPath );
			}
			foreach ( [ $localPackageDir, $publishedPackageDir, $downloadedPackageDir ] as $dir ) {
				if ( isset( $dir ) && is_dir( $dir ) ) {
					$this->recursiveRemoveDirectory( $dir );
				}
			}
			if ( isset( $tempDir ) && is_dir( $tempDir ) ) {
				$this->recursiveRemoveDirectory( $tempDir );
			}
		}
	}

	private function validateCtfrCollection( string $output, string $packageDir ): void {
		// Check that CTRF merged report path is in output
		$this->assertMatchesRegularExpression(
			'~CTRF merged →\s+.+/ctrf-report\.json~',
			$output,
			'CTRF merged report path should be in output'
		);

		// Extract merged CTRF path and validate its contents
		if ( preg_match( '~CTRF merged →\s+(.+/ctrf-report\.json)~', $output, $ctrfMatch ) ) {
			$mergedCtfrPath = $ctrfMatch[1];

			// Look for the artifacts directory pattern in the output
			if ( preg_match( '~Allure reports saved to final location: (.+/final/allure)~', $output, $artifactsMatch ) ) {
				$artifactsBase    = dirname( $artifactsMatch[1] );
				$actualMergedPath = $artifactsBase . '/ctrf/ctrf-report.json';

				// Validate merged CTRF file exists and has proper structure
				$this->assertFileExists( $actualMergedPath, 'Merged CTRF report should exist' );

				$ctrf = json_decode( file_get_contents( $actualMergedPath ), true );
				$this->assertIsArray( $ctrf, 'CTRF content should be valid JSON' );

				// Validate CTRF structure
				$this->assertArrayHasKey( 'results', $ctrf, 'CTRF should have results key' );
				$results = $ctrf['results'];

				$this->assertArrayHasKey( 'tool', $results, 'CTRF results should have tool info' );
				$this->assertArrayHasKey( 'summary', $results, 'CTRF results should have summary' );
				$this->assertArrayHasKey( 'tests', $results, 'CTRF results should have tests array' );

				$summary = $results['summary'];
				$this->assertArrayHasKey( 'tests', $summary, 'CTRF summary should have tests count' );
				$this->assertArrayHasKey( 'passed', $summary, 'CTRF summary should have passed count' );
				$this->assertArrayHasKey( 'failed', $summary, 'CTRF summary should have failed count' );

				// Validate that tests were actually executed (at least the scaffolded example test)
				$this->assertGreaterThanOrEqual( 1, $summary['tests'], 'At least one test should have been executed' );
			}
		}

		// Also validate package-local CTRF exists and has correct structure
		$packageCtfrPath = $packageDir . '/results/ctrf.json';
		$this->assertFileExists( $packageCtfrPath, 'Package-local CTRF report should exist' );

		$packageCtfr = json_decode( file_get_contents( $packageCtfrPath ), true );
		$this->assertIsArray( $packageCtfr, 'Package-local CTRF content should be valid JSON' );

		// Validate package-local CTRF structure
		$this->assertArrayHasKey( 'results', $packageCtfr, 'Package-local CTRF should have results key' );
		$packageResults = $packageCtfr['results'];

		$this->assertArrayHasKey( 'tool', $packageResults, 'Package-local CTRF results should have tool info' );
		$this->assertArrayHasKey( 'summary', $packageResults, 'Package-local CTRF results should have summary' );
		$this->assertArrayHasKey( 'tests', $packageResults, 'Package-local CTRF results should have tests array' );
	}

	public function test_tp004_multiple_test_packages_same_run(): void {
		$tempDir     = null;
		$packageDirs = [];
		$configPath  = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
			mkdir( $tempDir, 0755, true );

			// Create 3 local test packages to test "2+" requirement
			$packageNames = [ 'tp004-package-alpha', 'tp004-package-beta', 'tp004-package-gamma' ];

			foreach ( $packageNames as $index => $packageName ) {
				$packageDir    = $tempDir . '/' . $packageName;
				$packageDirs[] = $packageDir;

				// Scaffold test package
				qit( [
					'package:scaffold',
					$packageDir,
					'--namespace=qit-test-plugin',
					'--package=' . $packageName,
					'--framework=playwright',
					'--test-type=e2e',
					'--no-interaction'
				] );

				$this->assertDirectoryExists( $packageDir );
				$this->assertFileExists( $packageDir . '/manifest.json' );
				$this->assertFileExists( $packageDir . '/package.json' );
				$this->assertFileExists( $packageDir . '/playwright.config.js' );
				$this->assertFileExists( $packageDir . '/tests/example.spec.js' );

				// Modify manifest to include all phases and CTRF configuration
				$manifestPath               = $packageDir . '/manifest.json';
				$manifest                   = json_decode( file_get_contents( $manifestPath ), true );
				$manifest['test']['phases'] = [
					'globalSetup'    => [ './bootstrap/global-setup.sh' ],
					'setup'          => [ './bootstrap/setup.sh' ],
					'run'            => [ 'npx playwright test' ],
					'teardown'       => [],
					'globalTeardown' => [ './bootstrap/global-teardown.sh' ]
				];
				
				// Add CTRF configuration for bash script reporting
				if ( ! isset( $manifest['test']['results'] ) ) {
					$manifest['test']['results'] = [];
				}
				$manifest['test']['results']['ctrf-json'] = './results/ctrf.json';
				
				// Ensure results directory exists
				$resultsDir = $packageDir . '/results';
				if ( ! is_dir( $resultsDir ) ) {
					mkdir( $resultsDir, 0755, true );
				}
				
				file_put_contents( $manifestPath, json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

				// Modify bootstrap scripts to include WordPress option markers for tracking
				$this->modifyBootstrapScriptsWithWpOptions( $packageDir, $packageName );
			}

			// Create qit.json configuration with all packages
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
							'test_packages' => $packageDirs
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

			// Run the test with all packages
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

			// Verify all packages were processed
			foreach ( $packageDirs as $packageDir ) {
				$this->assertStringContainsString( 'Processing package: ' . $packageDir, $out,
					'Package ' . basename( $packageDir ) . ' should be processed' );
			}

			// Verify phases execute for each package
			// Count occurrences of each phase to ensure they run for all packages
			preg_match_all( '~\((globalSetup|setup|run|teardown|globalTeardown)\)~', $out, $phaseMatches );
			$phaseCounts = array_count_values( $phaseMatches[1] );

			// Each phase should run once per package (except teardown which is empty)
			$expectedCount = count( $packageDirs );
			$this->assertGreaterThanOrEqual( $expectedCount, $phaseCounts['globalSetup'] ?? 0,
				'globalSetup should run for all packages' );
			$this->assertGreaterThanOrEqual( $expectedCount, $phaseCounts['setup'] ?? 0,
				'setup should run for all packages' );
			$this->assertGreaterThanOrEqual( $expectedCount, $phaseCounts['run'] ?? 0,
				'run should run for all packages' );
			$this->assertGreaterThanOrEqual( $expectedCount, $phaseCounts['globalTeardown'] ?? 0,
				'globalTeardown should run for all packages' );

			// Verify CTRF results are collected from all packages
			$this->assertMatchesRegularExpression(
				'~CTRF merged →\s+.+/ctrf-report\.json~',
				$out,
				'CTRF merged report path should be in output'
			);

			// Verify merged CTRF contains results from all packages
			if ( preg_match( '~CTRF merged →\s+(.+/ctrf-report\.json)~', $out, $ctrfMatch ) ) {
				if ( preg_match( '~Allure reports saved to final location: (.+/final/allure)~', $out, $artifactsMatch ) ) {
					$artifactsBase  = dirname( $artifactsMatch[1] );
					$mergedCtfrPath = $artifactsBase . '/ctrf/ctrf-report.json';

					if ( file_exists( $mergedCtfrPath ) ) {
						$ctrf_json = json_decode( file_get_contents( $mergedCtfrPath ), true );
						$this->assertIsArray( $ctrf_json, 'Merged CTRF content should be valid JSON' );
						$this->assertCtrfMatchesSnapshot( $ctrf_json );
					} else {
						$this->fail( 'Merged CTRF report path not found in output or file does not exist: ' . $mergedCtfrPath );
					}
				}
			}

			// Verify no interference between packages by checking execution completed successfully
			$this->assertStringNotContainsString( 'FATAL ERROR', $out,
				'No fatal errors should occur during execution' );
			$this->assertStringNotContainsString( 'Package execution failed', $out,
				'All packages should execute successfully' );

		} finally {
			// Clean up resources
			if ( isset( $configPath ) && file_exists( $configPath ) ) {
				unlink( $configPath );
			}
			foreach ( $packageDirs as $packageDir ) {
				if ( is_dir( $packageDir ) ) {
					$this->recursiveRemoveDirectory( $packageDir );
				}
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

	public function test_tp009_php_debug_logs_captured(): void {
		$tempDir    = null;
		$packageDir = null;
		$configPath = null;

		try {
			$tempDir = sys_get_temp_dir() . '/qit_test_' . uniqid();
			mkdir( $tempDir, 0755, true );
			$packageDir = $tempDir . '/test-package';

			// Scaffold a test package
			qit( [
				'package:scaffold',
				$packageDir,
				'--namespace=qit-test-plugin',
				'--package=tp009-debug-logs',
				'--framework=playwright',
				'--test-type=e2e',
				'--no-interaction'
			] );

			$this->assertDirectoryExists( $packageDir );
			$this->assertFileExists( $packageDir . '/manifest.json' );

			// Create a custom plugin that generates PHP warnings
			$pluginDir = $packageDir . '/plugins';
			mkdir( $pluginDir, 0755, true );
			
			$pluginContent = '<?php
/**
 * Plugin Name: TP009 Debug Log Test Plugin
 * Description: Generates PHP warnings for testing debug log collection
 * Version: 1.0.0
 */

// Hook into WordPress init to generate PHP warnings
add_action( "init", function() {
	// Generate a PHP warning by accessing undefined array key
	$test_array = [ "existing_key" => "value" ];
	$undefined_value = $test_array["missing_key"]; // This will generate a PHP warning
	
	// Log a custom message to help identify our test
	error_log( "TP009: Custom plugin loaded and generated PHP warning" );
} );
';
			file_put_contents( $pluginDir . '/tp009-debug-test.php', $pluginContent );

			// Modify global-setup.sh to install and activate our custom plugin
			$globalSetupPath = $packageDir . '/bootstrap/global-setup.sh';
			if ( file_exists( $globalSetupPath ) ) {
				$content = file_get_contents( $globalSetupPath );
				$content = str_replace(
					'echo "[globalSetup] Done."',
					'# Install and activate our custom debug test plugin
cp /qit/packages/tp009-debug-logs/plugins/tp009-debug-test.php /var/www/html/wp-content/plugins/
wp plugin activate tp009-debug-test
echo "[globalSetup] Custom plugin activated"
echo "[globalSetup] Done."',
					$content
				);
				file_put_contents( $globalSetupPath, $content );
			}

			// Create a simple Playwright test that visits the site to trigger the plugin
			$testContent = 'const { test, expect } = require("@playwright/test");

test("trigger PHP warnings by visiting site", async ({ page }) => {
	// Visit the site homepage to trigger WordPress init and our plugin
	await page.goto("/");
	
	// Wait a moment to ensure the plugin code executes
	await page.waitForTimeout(1000);
	
	// Simple assertion to make the test pass
	expect(await page.title()).toBeTruthy();
});
';
			file_put_contents( $packageDir . '/tests/example.spec.js', $testContent );

			// Modify manifest to include our custom plugin in the volume mapping
			$manifest_path = $packageDir . '/manifest.json';
			$manifest      = json_decode( file_get_contents( $manifest_path ), true );

			// Add volume mapping for our custom plugin
			if ( ! isset( $manifest['test']['volumes'] ) ) {
				$manifest['test']['volumes'] = [];
			}
			$manifest['test']['volumes']['./plugins'] = '/qit/packages/tp009-debug-logs/plugins';

			// Ensure phases are properly configured
			$manifest['test']['phases'] = [
				'globalSetup' => [ './bootstrap/global-setup.sh' ],
				'setup'       => [ './bootstrap/setup.sh' ],
				'run'         => [ 'npx playwright test' ],
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
			], true );

			// The test should complete (exit code 0 or 3 for warnings are both acceptable)
			$exitCode = $proc->getExitCode();
			$this->assertContains( $exitCode, [ 0, 3 ],
				'Test should complete successfully or with warnings. Output: ' . $proc->getOutput() . ' Error: ' . $proc->getErrorOutput() );

			$output = $proc->getOutput();

			// Verify that debug log copying message appears in output
			$this->assertStringContainsString( 'Debug log copied from container', $output,
				'Debug log should be copied from container to results directory' );

			// Find the results directory from the output
			$resultsDir = null;
			if ( preg_match( '~Allure reports saved to final location: (.+/final/allure)~', $output, $matches ) ) {
				$resultsDir = dirname( $matches[1] );
			} elseif ( preg_match( '~CTRF merged →\s+(.+)/final/ctrf/ctrf-report\.json~', $output, $matches ) ) {
				$resultsDir = $matches[1];
			}

			$this->assertNotNull( $resultsDir, 'Should be able to determine results directory from output' );

			// Verify debug.log file exists in results directory
			$debugLogPath = $resultsDir . '/debug.log';
			$this->assertFileExists( $debugLogPath, 'Debug log should exist in results directory' );

			// Verify debug log contains our PHP warning
			$debugLogContent = file_get_contents( $debugLogPath );
			$this->assertNotEmpty( $debugLogContent, 'Debug log should not be empty' );
			
			// Check for PHP warning about undefined array key
			$this->assertStringContainsString( 'missing_key', $debugLogContent,
				'Debug log should contain PHP warning about undefined array key' );
			
			// Check for our custom log message
			$this->assertStringContainsString( 'TP009: Custom plugin loaded', $debugLogContent,
				'Debug log should contain our custom plugin log message' );

		} finally {
			// Cleanup
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

	/**
	 * Modify bootstrap scripts to include WordPress option markers for tracking test execution phases.
	 *
	 * @param string $packageDir The package directory path
	 * @param string $packageName The package name to use in option keys
	 */
	private function modifyBootstrapScriptsWithWpOptions( string $packageDir, string $packageName ): void {
		// Modify global-setup.sh
		$globalSetupPath = $packageDir . '/bootstrap/global-setup.sh';
		if ( file_exists( $globalSetupPath ) ) {
			$content = file_get_contents( $globalSetupPath );
			$content = str_replace(
				'echo "[globalSetup] Done."',
				"wp option update \"{$packageName}_global_setup_ran\" \"true\"\necho \"[globalSetup] Done.\"",
				$content
			);
			file_put_contents( $globalSetupPath, $content );
		}

		// Modify setup.sh
		$setupPath = $packageDir . '/bootstrap/setup.sh';
		if ( file_exists( $setupPath ) ) {
			$content = file_get_contents( $setupPath );
			$content = str_replace(
				'echo "[setup] Done."',
				"# Verify global setup ran and set setup marker\nif wp option get \"{$packageName}_global_setup_ran\" >/dev/null 2>&1; then\n    wp option update \"{$packageName}_setup_ran\" \"true\"\n    echo \"[setup] Global setup verified for {$packageName}\"\nelse\n    echo \"[setup] ERROR: Global setup did not run for {$packageName}\"\n    exit 1\nfi\necho \"[setup] Done.\"",
				$content
			);
			file_put_contents( $setupPath, $content );
		}

		// Modify global-teardown.sh
		$globalTeardownPath = $packageDir . '/bootstrap/global-teardown.sh';
		if ( file_exists( $globalTeardownPath ) ) {
			$content = file_get_contents( $globalTeardownPath );
			$content = str_replace(
				'echo "[globalTeardown] Done."',
				"# Check if phase markers exist (they may not persist across all phases)\nif wp option get \"{$packageName}_global_setup_ran\" >/dev/null 2>&1 || \\\n   wp option get \"{$packageName}_setup_ran\" >/dev/null 2>&1; then\n    echo \"[globalTeardown] Phase markers found for {$packageName} - cleaning up\"\n    # Clean up any existing markers\n    wp option delete \"{$packageName}_global_setup_ran\" 2>/dev/null || true\n    wp option delete \"{$packageName}_setup_ran\" 2>/dev/null || true\nelse\n    echo \"[globalTeardown] No phase markers found for {$packageName} (this is expected if database was reset)\"\nfi\necho \"[globalTeardown] Done.\"",
				$content
			);
			file_put_contents( $globalTeardownPath, $content );
		}
	}
}
