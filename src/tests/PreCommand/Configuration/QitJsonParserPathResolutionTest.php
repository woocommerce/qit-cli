<?php

namespace QIT_CLI_Tests\PreCommand\Configuration;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Configuration\QitJsonParser;

class QitJsonParserPathResolutionTest extends TestCase {
	private $temp_dir = '/tmp/qit_path_test';

	protected function setUp(): void {
		parent::setUp();
		$this->delete_dir( $this->temp_dir );
		mkdir( $this->temp_dir, 0777, true );
	}

	protected function tearDown(): void {
		$this->delete_dir( $this->temp_dir );
		parent::tearDown();
	}

	/**
	 * Test that paths are resolved correctly when extending from a subdirectory
	 */
	public function test_path_resolution_with_extends_in_subdirectory(): void {
		// Create directory structure
		mkdir( $this->temp_dir . '/base', 0777, true );
		mkdir( $this->temp_dir . '/project', 0777, true );
		mkdir( $this->temp_dir . '/project/src', 0777, true );
		mkdir( $this->temp_dir . '/shared', 0777, true );

		// Create base config in base directory
		$base_config = [
			'environments' => [
				'default' => [
					'php_version' => '8.0',
					'setup_only'  => [ './shared/setup.json' ]
				]
			]
		];
		file_put_contents( $this->temp_dir . '/base/base.json', json_encode( $base_config ) );

		// Create shared setup package (relative to base)
		mkdir( $this->temp_dir . '/base/shared', 0777, true );
		$setup_package = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'setup'
		];
		file_put_contents( $this->temp_dir . '/base/shared/setup.json', json_encode( $setup_package ) );

		// Create project config that extends base
		$project_config = [
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
		file_put_contents( $this->temp_dir . '/project/qit.json', json_encode( $project_config ) );

		// Create test package relative to project
		mkdir( $this->temp_dir . '/project/tests', 0777, true );
		$e2e_package = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'e2e',
			'test_dir'  => './e2e-tests'
		];
		file_put_contents( $this->temp_dir . '/project/tests/e2e.json', json_encode( $e2e_package ) );
		mkdir( $this->temp_dir . '/project/tests/e2e-tests', 0777, true );

		// Parse the project config
		$parser = new QitJsonParser();
		$config = $parser->parse( $this->temp_dir . '/project/qit.json' );

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
	public function test_nested_extends_path_resolution(): void {
		// Create complex directory structure
		mkdir( $this->temp_dir . '/configs/base', 0777, true );
		mkdir( $this->temp_dir . '/configs/shared', 0777, true );
		mkdir( $this->temp_dir . '/projects/plugin-a', 0777, true );
		mkdir( $this->temp_dir . '/projects/plugin-a/src', 0777, true );

		// Level 1: Root base config
		$root_base = [
			'environments' => [
				'production' => [
					'php_version' => '8.2',
					'wp_version'  => 'latest'
				]
			]
		];
		file_put_contents( $this->temp_dir . '/configs/base/root.json', json_encode( $root_base ) );

		// Level 2: Shared config extending root
		$shared_config = [
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
		file_put_contents( $this->temp_dir . '/configs/shared/common.json', json_encode( $shared_config ) );

		// Level 3: Project config extending shared
		$project_config = [
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
		file_put_contents( $this->temp_dir . '/projects/plugin-a/qit.json', json_encode( $project_config ) );

		// Create test package
		mkdir( $this->temp_dir . '/projects/plugin-a/tests', 0777, true );
		$unit_package = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'unit'
		];
		file_put_contents( $this->temp_dir . '/projects/plugin-a/tests/unit.json', json_encode( $unit_package ) );

		// Parse and verify
		$parser = new QitJsonParser();
		$config = $parser->parse( $this->temp_dir . '/projects/plugin-a/qit.json' );

		// Check that all extends were resolved correctly
		$this->assertEquals( '8.0', $config['environments']['testing']['php_version'] );
		$this->assertEquals( 'latest', $config['environments']['testing']['wp_version'] );
		$this->assertEquals( 'stable', $config['environments']['testing']['woo_version'] );
	}

	/**
	 * Test that setup_only packages are validated with correct path resolution
	 */
	public function test_setup_only_path_resolution_with_extends(): void {
		// Create directory structure
		mkdir( $this->temp_dir . '/parent', 0777, true );
		mkdir( $this->temp_dir . '/parent/packages', 0777, true );
		mkdir( $this->temp_dir . '/child', 0777, true );

		// Create parent config with setup_only
		$parent_config = [
			'environments' => [
				'base' => [
					'php_version' => '8.1',
					'setup_only'  => [ './packages/db-setup.json' ]
				]
			]
		];
		file_put_contents( $this->temp_dir . '/parent/config.json', json_encode( $parent_config ) );

		// Create setup package relative to parent
		$setup_package = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'setup',
			'lifecycle' => [
				'environment' => [
					'setup' => [ './scripts/init-db.sh' ]
				]
			]
		];
		file_put_contents( $this->temp_dir . '/parent/packages/db-setup.json', json_encode( $setup_package ) );
		mkdir( $this->temp_dir . '/parent/packages/scripts', 0777, true );
		file_put_contents( $this->temp_dir . '/parent/packages/scripts/init-db.sh', '#!/bin/bash' );
		chmod( $this->temp_dir . '/parent/packages/scripts/init-db.sh', 0755 );

		// Create child config that extends parent
		$child_config = [
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
		file_put_contents( $this->temp_dir . '/child/qit.json', json_encode( $child_config ) );

		// Create local setup package
		$local_setup = [
			'$schema'   => 'https://qit.woo.com/json-schema/test-package',
			'test_type' => 'setup'
		];
		file_put_contents( $this->temp_dir . '/child/local-setup.json', json_encode( $local_setup ) );

		// Parse and verify
		$parser = new QitJsonParser();
		$config = $parser->parse( $this->temp_dir . '/child/qit.json' );

		// Verify paths remain relative
		$this->assertEquals( [ './packages/db-setup.json' ], $config['environments']['base']['setup_only'] );
		$this->assertEquals( [ './local-setup.json' ], $config['environments']['dev']['setup_only'] );

		// Load setup packages to verify they resolve correctly
		$base_setup_packages = $parser->get_setup_packages_for_environment( 'base' );
		$this->assertArrayHasKey( './packages/db-setup.json', $base_setup_packages );

		$dev_setup_packages = $parser->get_setup_packages_for_environment( 'dev' );
		$this->assertArrayHasKey( './local-setup.json', $dev_setup_packages );
	}

	/**
	 * Test build source with output path resolution
	 */
	public function test_build_source_path_resolution(): void {
		mkdir( $this->temp_dir . '/project', 0777, true );
		mkdir( $this->temp_dir . '/project/build', 0777, true );

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
		file_put_contents( $this->temp_dir . '/project/build/plugin.zip', 'dummy' );
		file_put_contents( $this->temp_dir . '/project/qit.json', json_encode( $config ) );

		$parser = new QitJsonParser();
		$parsed = $parser->parse( $this->temp_dir . '/project/qit.json' );

		// Output path should remain relative
		$this->assertEquals( './build/plugin.zip', $parsed['sut']['source']['output'] );
	}

	/**
	 * Test that missing files are caught with proper path resolution
	 */
	public function test_missing_file_detection_with_extends(): void {
		mkdir( $this->temp_dir . '/base', 0777, true );
		mkdir( $this->temp_dir . '/project', 0777, true );

		// Create base config referencing non-existent package
		$base_config = [
			'environments' => [
				'default' => [
					'setup_only' => [ './missing/package.json' ]
				]
			]
		];
		file_put_contents( $this->temp_dir . '/base/base.json', json_encode( $base_config ) );

		// Create project extending base
		$project_config = [
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
		file_put_contents( $this->temp_dir . '/project/qit.json', json_encode( $project_config ) );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Setup package file not found: ./missing/package.json' );

		$parser = new QitJsonParser();
		$parser->parse( $this->temp_dir . '/project/qit.json' );
	}

	/**
	 * Test URL extends doesn't affect local path resolution
	 */
	public function test_url_extends_with_local_paths(): void {
		// Create directory structure
		mkdir( $this->temp_dir . '/remote-configs', 0777, true );
		mkdir( $this->temp_dir . '/project', 0777, true );

		// Create a "remote" config that would be downloaded via URL
		$remote_config = [
			'environments' => [
				'default' => [
					'php_version' => '8.0',
					'setup_only'  => [ './setup/remote-setup.json' ] // Path in "remote" config
				]
			],
			'test_types'   => [
				'integration' => [
					'default' => [
						'test_packages' => [ './tests/integration.json' ] // Another path in "remote" config
					]
				]
			]
		];
		file_put_contents( $this->temp_dir . '/remote-configs/base.json', json_encode( $remote_config ) );

		// Create the main project config that extends from "URL" using test flag
		$project_config = [
			'extends'      => './mimick-url-for-tests/../remote-configs/base.json',
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'my-plugin',
				'source' => [
					'type' => 'local',
					'path' => './'
				]
			],
			'test_types'   => [
				'e2e' => [
					'default' => [
						'test_packages' => [ './e2e/tests.json' ] // Local path
					]
				]
			],
			'environments' => [
				'staging' => [
					'extends'    => 'default',
					'setup_only' => [ './local-setup.json' ]
				]
			]
		]; // Another local path
		file_put_contents( $this->temp_dir . '/project/qit.json', json_encode( $project_config ) );

		// Create all referenced files relative to PROJECT directory
		// (not relative to remote-configs directory)
		mkdir( $this->temp_dir . '/project/setup', 0777, true );
		$remote_setup_package = [
			'$schema'     => 'https://qit.woo.com/json-schema/test-package',
			'test_type'   => 'setup',
			'description' => 'Setup from remote config'
		];
		file_put_contents( $this->temp_dir . '/project/setup/remote-setup.json', json_encode( $remote_setup_package ) );

		mkdir( $this->temp_dir . '/project/tests', 0777, true );
		$integration_package = [
			'$schema'     => 'https://qit.woo.com/json-schema/test-package',
			'test_type'   => 'integration',
			'description' => 'Integration tests from remote config'
		];
		file_put_contents( $this->temp_dir . '/project/tests/integration.json', json_encode( $integration_package ) );

		mkdir( $this->temp_dir . '/project/e2e', 0777, true );
		$e2e_package = [
			'$schema'     => 'https://qit.woo.com/json-schema/test-package',
			'test_type'   => 'e2e',
			'description' => 'Local E2E tests'
		];
		file_put_contents( $this->temp_dir . '/project/e2e/tests.json', json_encode( $e2e_package ) );

		$local_setup_package = [
			'$schema'     => 'https://qit.woo.com/json-schema/test-package',
			'test_type'   => 'setup',
			'description' => 'Local setup package'
		];
		file_put_contents( $this->temp_dir . '/project/local-setup.json', json_encode( $local_setup_package ) );

		// Parse the config
		$parser = new QitJsonParser();
		$config = $parser->parse( $this->temp_dir . '/project/qit.json' );

		// Verify paths remain relative
		$this->assertEquals( [ './setup/remote-setup.json' ], $config['environments']['default']['setup_only'] );
		$this->assertEquals( [ './tests/integration.json' ], $config['test_types']['integration']['default']['test_packages'] );
		$this->assertEquals( [ './e2e/tests.json' ], $config['test_types']['e2e']['default']['test_packages'] );
		$this->assertEquals( [ './local-setup.json' ], $config['environments']['staging']['setup_only'] );

		// Verify all packages can be loaded (paths resolved correctly relative to project)
		$default_setup_packages = $parser->get_setup_packages_for_environment( 'default' );
		$this->assertArrayHasKey( './setup/remote-setup.json', $default_setup_packages );
		$this->assertEquals( 'Setup from remote config', $default_setup_packages['./setup/remote-setup.json']['description'] );

		$integration_packages = $parser->get_test_packages_for_profile( 'integration', 'default' );
		$this->assertArrayHasKey( './tests/integration.json', $integration_packages );
		$this->assertEquals( 'Integration tests from remote config', $integration_packages['./tests/integration.json']['description'] );

		$e2e_packages = $parser->get_test_packages_for_profile( 'e2e', 'default' );
		$this->assertArrayHasKey( './e2e/tests.json', $e2e_packages );
		$this->assertEquals( 'Local E2E tests', $e2e_packages['./e2e/tests.json']['description'] );

		$staging_setup_packages = $parser->get_setup_packages_for_environment( 'staging' );
		$this->assertArrayHasKey( './local-setup.json', $staging_setup_packages );
		$this->assertEquals( 'Local setup package', $staging_setup_packages['./local-setup.json']['description'] );
	}

	private function delete_dir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$files = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $files as $file ) {
			$path = "$dir/$file";
			is_dir( $path ) ? $this->delete_dir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}
}