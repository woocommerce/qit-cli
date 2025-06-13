<?php

namespace QIT_CLI_Tests\PreCommand\Configuration;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Configuration\QitJsonParser;

class QitJsonParserPathResolutionTest extends TestCase {
	private $tempDir = '/tmp/qit_path_test';

	protected function setUp(): void {
		parent::setUp();
		$this->deleteDir( $this->tempDir );
		mkdir( $this->tempDir, 0777, true );
	}

	protected function tearDown(): void {
		$this->deleteDir( $this->tempDir );
		parent::tearDown();
	}

	/**
	 * Test that paths are resolved correctly when extending from a subdirectory
	 */
	public function testPathResolutionWithExtendsInSubdirectory(): void {
		// Create directory structure
		mkdir( $this->tempDir . '/base', 0777, true );
		mkdir( $this->tempDir . '/project', 0777, true );
		mkdir( $this->tempDir . '/project/src', 0777, true );
		mkdir( $this->tempDir . '/shared', 0777, true );

		// Create base config in base directory
		$baseConfig = [
			'environments' => [
				'default' => [
					'php_version' => '8.0',
					'setup_only'  => [ './shared/setup.json' ]
				]
			]
		];
		file_put_contents( $this->tempDir . '/base/base.json', json_encode( $baseConfig ) );

		// Create shared setup package (relative to base)
		mkdir( $this->tempDir . '/base/shared', 0777, true );
		$setupPackage = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'setup'
		];
		file_put_contents( $this->tempDir . '/base/shared/setup.json', json_encode( $setupPackage ) );

		// Create project config that extends base
		$projectConfig = [
			'extends'    => '../base/base.json',
			'sut'        => [
				'type'   => 'plugin',
				'slug'   => 'my-plugin',
				'source' => [
					'type' => 'local',
					'path' => './src'
				]
			],
			'test_types' => [
				'e2e' => [
					'default' => [
						'test_packages' => [ './tests/e2e.json' ]
					]
				]
			]
		];
		file_put_contents( $this->tempDir . '/project/qit.json', json_encode( $projectConfig ) );

		// Create test package relative to project
		mkdir( $this->tempDir . '/project/tests', 0777, true );
		$e2ePackage = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'e2e',
			'test_dir'  => './e2e-tests'
		];
		file_put_contents( $this->tempDir . '/project/tests/e2e.json', json_encode( $e2ePackage ) );
		mkdir( $this->tempDir . '/project/tests/e2e-tests', 0777, true );

		// Parse the project config
		$parser = new QitJsonParser();
		$config = $parser->parse( $this->tempDir . '/project/qit.json' );

		// Verify paths remain relative
		$this->assertEquals( './src', $config['sut']['source']['path'] );
		$this->assertEquals( [ './shared/setup.json' ], $config['environments']['default']['setup_only'] );
		$this->assertEquals( [ './tests/e2e.json' ], $config['test_types']['e2e']['default']['test_packages'] );

		// Verify that validation passed (files were found using correct resolution)
		$this->assertTrue( true, 'Parsing succeeded, paths were resolved correctly for validation' );
	}

	/**
	 * Test nested extends with different relative paths
	 */
	public function testNestedExtendsPathResolution(): void {
		// Create complex directory structure
		mkdir( $this->tempDir . '/configs/base', 0777, true );
		mkdir( $this->tempDir . '/configs/shared', 0777, true );
		mkdir( $this->tempDir . '/projects/plugin-a', 0777, true );
		mkdir( $this->tempDir . '/projects/plugin-a/src', 0777, true );

		// Level 1: Root base config
		$rootBase = [
			'environments' => [
				'production' => [
					'php_version' => '8.2',
					'wp_version'  => 'latest'
				]
			]
		];
		file_put_contents( $this->tempDir . '/configs/base/root.json', json_encode( $rootBase ) );

		// Level 2: Shared config extending root
		$sharedConfig = [
			'extends'      => '../base/root.json',
			'environments' => [
				'production' => [
					'woo_version' => 'stable'
				],
				'testing'    => [
					'extends'     => 'production',
					'php_version' => '8.0'
				]
			]
		];
		file_put_contents( $this->tempDir . '/configs/shared/common.json', json_encode( $sharedConfig ) );

		// Level 3: Project config extending shared
		$projectConfig = [
			'extends'    => '../../configs/shared/common.json',
			'sut'        => [
				'type'   => 'plugin',
				'slug'   => 'plugin-a',
				'source' => [
					'type' => 'local',
					'path' => './src'
				]
			],
			'test_types' => [
				'unit' => [
					'default' => [
						'environment'   => 'testing',
						'test_packages' => [ './tests/unit.json' ]
					]
				]
			]
		];
		file_put_contents( $this->tempDir . '/projects/plugin-a/qit.json', json_encode( $projectConfig ) );

		// Create test package
		mkdir( $this->tempDir . '/projects/plugin-a/tests', 0777, true );
		$unitPackage = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'unit'
		];
		file_put_contents( $this->tempDir . '/projects/plugin-a/tests/unit.json', json_encode( $unitPackage ) );

		// Parse and verify
		$parser = new QitJsonParser();
		$config = $parser->parse( $this->tempDir . '/projects/plugin-a/qit.json' );

		// Check that all extends were resolved correctly
		$this->assertEquals( '8.0', $config['environments']['testing']['php_version'] );
		$this->assertEquals( 'latest', $config['environments']['testing']['wp_version'] );
		$this->assertEquals( 'stable', $config['environments']['testing']['woo_version'] );
	}

	/**
	 * Test that setup_only packages are validated with correct path resolution
	 */
	public function testSetupOnlyPathResolutionWithExtends(): void {
		// Create directory structure
		mkdir( $this->tempDir . '/parent', 0777, true );
		mkdir( $this->tempDir . '/parent/packages', 0777, true );
		mkdir( $this->tempDir . '/child', 0777, true );

		// Create parent config with setup_only
		$parentConfig = [
			'environments' => [
				'base' => [
					'php_version' => '8.1',
					'setup_only'  => [ './packages/db-setup.json' ]
				]
			]
		];
		file_put_contents( $this->tempDir . '/parent/config.json', json_encode( $parentConfig ) );

		// Create setup package relative to parent
		$setupPackage = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'setup',
			'lifecycle' => [
				'environment' => [
					'setup' => [ './scripts/init-db.sh' ]
				]
			]
		];
		file_put_contents( $this->tempDir . '/parent/packages/db-setup.json', json_encode( $setupPackage ) );
		mkdir( $this->tempDir . '/parent/packages/scripts', 0777, true );
		file_put_contents( $this->tempDir . '/parent/packages/scripts/init-db.sh', '#!/bin/bash' );
		chmod( $this->tempDir . '/parent/packages/scripts/init-db.sh', 0755 );

		// Create child config that extends parent
		$childConfig = [
			'extends'      => '../parent/config.json',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'child-plugin',
				'source' => [
					'type' => 'local',
					'path' => './'
				]
			],
			'environments' => [
				'dev' => [
					'extends'    => 'base',
					'setup_only' => [ './local-setup.json' ] // Add local setup in addition
				]
			]
		];
		file_put_contents( $this->tempDir . '/child/qit.json', json_encode( $childConfig ) );

		// Create local setup package
		$localSetup = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'setup'
		];
		file_put_contents( $this->tempDir . '/child/local-setup.json', json_encode( $localSetup ) );

		// Parse and verify
		$parser = new QitJsonParser();
		$config = $parser->parse( $this->tempDir . '/child/qit.json' );

		// Verify paths remain relative
		$this->assertEquals( [ './packages/db-setup.json' ], $config['environments']['base']['setup_only'] );
		$this->assertEquals( [ './local-setup.json' ], $config['environments']['dev']['setup_only'] );

		// Load setup packages to verify they resolve correctly
		$baseSetupPackages = $parser->getSetupPackagesForEnvironment( 'base' );
		$this->assertArrayHasKey( './packages/db-setup.json', $baseSetupPackages );

		$devSetupPackages = $parser->getSetupPackagesForEnvironment( 'dev' );
		$this->assertArrayHasKey( './local-setup.json', $devSetupPackages );
	}

	/**
	 * Test build source with output path resolution
	 */
	public function testBuildSourcePathResolution(): void {
		mkdir( $this->tempDir . '/project', 0777, true );
		mkdir( $this->tempDir . '/project/build', 0777, true );

		$config = [
			'sut' => [
				'type'   => 'plugin',
				'slug'   => 'built-plugin',
				'source' => [
					'type'    => 'build',
					'command' => 'npm run build',
					'output'  => './build/plugin.zip'
				]
			]
		];

		// Create the output file so validation passes
		file_put_contents( $this->tempDir . '/project/build/plugin.zip', 'dummy' );
		file_put_contents( $this->tempDir . '/project/qit.json', json_encode( $config ) );

		$parser = new QitJsonParser();
		$parsed = $parser->parse( $this->tempDir . '/project/qit.json' );

		// Output path should remain relative
		$this->assertEquals( './build/plugin.zip', $parsed['sut']['source']['output'] );
	}

	/**
	 * Test that missing files are caught with proper path resolution
	 */
	public function testMissingFileDetectionWithExtends(): void {
		mkdir( $this->tempDir . '/base', 0777, true );
		mkdir( $this->tempDir . '/project', 0777, true );

		// Create base config referencing non-existent package
		$baseConfig = [
			'environments' => [
				'default' => [
					'setup_only' => [ './missing/package.json' ]
				]
			]
		];
		file_put_contents( $this->tempDir . '/base/base.json', json_encode( $baseConfig ) );

		// Create project extending base
		$projectConfig = [
			'extends' => '../base/base.json',
			'sut'     => [
				'type'   => 'plugin',
				'slug'   => 'test',
				'source' => [
					'type' => 'local',
					'path' => './'
				]
			]
		];
		file_put_contents( $this->tempDir . '/project/qit.json', json_encode( $projectConfig ) );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Setup package file not found: ./missing/package.json' );

		$parser = new QitJsonParser();
		$parser->parse( $this->tempDir . '/project/qit.json' );
	}

	/**
	 * Test URL extends doesn't affect local path resolution
	 */
	public function testUrlExtendsWithLocalPaths(): void {
		// This would be a mock test since we can't actually fetch URLs in unit tests
		// But it demonstrates the test case structure
		$this->markTestSkipped( 'URL extends require network access' );
	}

	private function deleteDir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = "$dir/$file";
			is_dir( $path ) ? $this->deleteDir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}