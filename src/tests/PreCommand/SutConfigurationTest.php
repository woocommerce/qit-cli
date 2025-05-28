<?php

namespace QIT_CLI_Tests\PreCommand;

use Spatie\Snapshots\MatchesSnapshots;

class SutConfigurationTest extends PreCommandTestCase {
	use MatchesSnapshots;

	public function setUp(): void {
		parent::setUp();
		$this->mockWpOrgPlugin( 'woocommerce', '8.0.0', 'https://downloads.wordpress.org/plugin/woocommerce.zip' );
	}

	/**
	 * @dataProvider sourceTypeProvider
	 */
	public function test_sut_source_types( string $source_type, array $source_config ): void {
		$temp_dir = $this->temp_dir;

		// Create temporary files/directories for local, zip, directory, and build sources
		if ( in_array( $source_type, [ 'local', 'directory', 'zip', 'build' ], true ) ) {
			if ( $source_type === 'directory' || $source_type === 'local' ) {
				$path = "$temp_dir/plugin-folder";
				mkdir( $path, 0777, true );
				file_put_contents( "$path/awesome-plugin.php", "<?php\n// Plugin Name: Awesome Plugin" );
				$source_config['path'] = $path;
			} elseif ( $source_type === 'zip' ) {
				$path = "$temp_dir/plugin.zip";
				$zip  = new \ZipArchive();
				$zip->open( $path, \ZipArchive::CREATE );
				$zip->addFromString( 'awesome-plugin/awesome-plugin.php', "<?php\n// Plugin Name: Awesome Plugin" );
				$zip->close();
				$source_config['path'] = $path;
			} elseif ( $source_type === 'build' ) {
				$path = "$temp_dir/plugin.zip";
				$zip  = new \ZipArchive();
				$zip->open( $path, \ZipArchive::CREATE );
				$zip->addFromString( 'awesome-plugin/awesome-plugin.php', "<?php\n// Plugin Name: Awesome Plugin" );
				$zip->close();
				$source_config['output'] = $path;
			}
			$this->to_delete[] = $path;
		}

		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => 'awesome-plugin',
				'source' => array_merge( [ 'type' => $source_type ], $source_config ),
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		// Mock remote requests
		if ( $source_type === 'url' ) {
			$this->mockDownloadUrl( $source_config['url'], 'mocked-zip-content' );
		} elseif ( $source_type === 'wporg' ) {
			$this->mockWpOrgPlugin( 'awesome-plugin', '1.0.0', 'https://downloads.wordpress.org/plugin/awesome-plugin.zip' );
		} elseif ( $source_type === 'wccom' ) {
			$this->mockWooComDownloadUrls( [ 'awesome-plugin' => 'https://qit.woo.com/downloads/awesome-plugin.zip' ] );
			$this->mockDownloadUrl( 'https://qit.woo.com/downloads/awesome-plugin.zip', 'mocked-zip-content' );
		}

		$env_info = $this->run_unit_test( $config );
		$this->assertArrayHasKey( 'sut', $env_info, 'env_info is missing the sut key' );
		$this->assertEquals( 'plugin', $env_info['sut']['type'] );
		$this->assertEquals( 'awesome-plugin', $env_info['sut']['slug'] );
		$this->assertEquals( $source_type, $env_info['sut']['source']['type'] );
		foreach ( $source_config as $key => $value ) {
			$this->assertEquals( $value, $env_info['sut']['source'][ $key ], "SUT source key '$key' does not match" );
		}
		$this->assertMatchesJsonSnapshot( json_encode( $env_info, JSON_PRETTY_PRINT ) );
	}

	public function sourceTypeProvider(): array {
		return [
			'build'     => [
				'source_type'   => 'build',
				'source_config' => [
					'command' => 'npm run build',
					'output'  => '/normalized/path/plugin.zip',
				],
			],
			'directory' => [
				'source_type'   => 'directory',
				'source_config' => [
					'path' => '/normalized/path/plugin-folder',
				],
			],
			'url'       => [
				'source_type'   => 'url',
				'source_config' => [
					'url' => 'https://example.com/plugin.zip',
				],
			],
			'zip'       => [
				'source_type'   => 'zip',
				'source_config' => [
					'path' => '/normalized/path/plugin.zip',
				],
			],
			'local'     => [
				'source_type'   => 'local',
				'source_config' => [
					'path' => '/normalized/path/plugin-folder',
				],
			],
			'wporg'     => [
				'source_type'   => 'wporg',
				'source_config' => [
					'slug'    => 'awesome-plugin',
					'version' => 'stable',
				],
			],
			'wccom'     => [
				'source_type'   => 'wccom',
				'source_config' => [
					'slug'    => 'awesome-plugin',
					'version' => 'stable',
				],
			],
		];
	}

	public function test_sut_missing_source(): void {
		$config = [
			'sut'          => [
				'type' => 'plugin',
				'slug' => 'awesome-plugin',
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'sut must contain a "source" key', $error );
	}

	public function test_sut_invalid_type(): void {
		$config = [
			'sut'          => [
				'type'   => 'invalid',
				'slug'   => 'awesome-plugin',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'Invalid sut type \'invalid\'', $error );
	}

	public function test_sut_empty_slug(): void {
		$config = [
			'sut'          => [
				'type'   => 'plugin',
				'slug'   => '',
				'source' => [
					'type' => 'directory',
					'path' => './plugin-folder',
				],
			],
			'environments' => [
				'default' => [
					'plugins' => [ 'woocommerce' ],
				],
			],
		];

		$error = $this->run_unit_test( $config, [], true );
		$this->assertStringContainsString( 'sut must contain a non-empty "slug" string', $error );
	}
}