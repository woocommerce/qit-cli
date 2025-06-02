<?php

namespace QIT_CLI_Tests\PreCommand\Parsers;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Parsers\ConfigParser;
use Spatie\Snapshots\MatchesSnapshots;

class ConfigParserTest extends TestCase {
	use MatchesSnapshots;

	private $tempDir = '/tmp/qit_test_fixed';

	protected function setUp(): void {
		parent::setUp();
		// Clean up and recreate the fixed temp directory
		$this->deleteDir( $this->tempDir );
		mkdir( $this->tempDir, 0777, true );
	}

	protected function tearDown(): void {
		// Clean up the fixed temp directory
		$this->deleteDir( $this->tempDir );
		parent::tearDown();
	}

	public function testMinimalValidConfig(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'sut', $parsedConfig );
		$this->assertEquals( 'plugin', $parsedConfig['sut']['type'] );
		$this->assertEquals( 'test-plugin', $parsedConfig['sut']['slug'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testConfigWithPluginsAndThemes(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "environments": {
        "default": {
            "php_version": "7.4",
            "wp_version": "6.0",
            "plugins": [
                {"slug": "woocommerce", "source": {"type": "wccom"}},
                {"slug": "test-plugin", "source": {"type": "directory", "path": "./plugin"}}
            ],
            "themes": [
                {"slug": "storefront", "source": {"type": "wporg"}}
            ]
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'environments', $parsedConfig );
		$this->assertEquals( '7.4', $parsedConfig['environments']['default']['php_version'] );
		$this->assertCount( 2, $parsedConfig['environments']['default']['plugins'] );
		$this->assertCount( 1, $parsedConfig['environments']['default']['themes'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testConfigWithTestTypes(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "test_types": {
        "e2e": {
            "default": {
                "run": {
                    "test_packages": ["local/test-package"]
                }
            }
        }
    },
    "test_packages": [
        {
            "type": "e2e",
            "name": "test-package",
            "file": "test.json"
        }
    ]
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $this->tempDir . '/test.json', json_encode( [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0.0',
			'author'       => 'Test Author',
			'test_command' => 'npm run test',
		] ) );
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'test_types', $parsedConfig );
		$this->assertArrayHasKey( 'e2e', $parsedConfig['test_types'] );
		$this->assertArrayHasKey( 'test_packages', $parsedConfig );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testInvalidEnvironmentKey(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "environments": {
        "default": {
            "invalid_key": "value"
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Unknown key 'invalid_key' in environment 'default' configuration." );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testMissingSourceInSut(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin"
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "SUT 'test-plugin' must specify a 'source' object." );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseConfigMissingFile(): void {
		$configFile = $this->tempDir . '/nonexistent.json';
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Config file '$configFile' not found." );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseInvalidJson(): void {
		$configFile = $this->tempDir . '/qit.json';
		file_put_contents( $configFile, '{invalid json' );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Invalid qit.json format. Must be a JSON object.' );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseMissingSut(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "environments": {
        "default": {
            "php_version": "7.4"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'SUT configuration is required.' );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseSutInvalidType(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "invalid",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Invalid SUT type 'invalid'. Must be one of: plugin, theme" );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseSourceBuild(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "build",
            "command": "npm run build",
            "output": "./plugin.zip"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertEquals( 'build', $parsedConfig['sut']['source']['type'] );
		$this->assertEquals( 'npm run build', $parsedConfig['sut']['source']['command'] );
		$this->assertStringEndsWith( 'plugin.zip', $parsedConfig['sut']['source']['output'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testParseExtends(): void {
		$baseFile   = $this->tempDir . '/base.json';
		$baseConfig = <<<'JSON'
{
    "environments": {
        "default": {
            "php_version": "7.4",
            "wp_version": "6.0"
        }
    }
}
JSON;
		file_put_contents( $baseFile, $baseConfig );

		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "extends": "base.json",
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "environments": {
        "default": {
            "php_version": "8.0"
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'environments', $parsedConfig );
		$this->assertEquals( '8.0', $parsedConfig['environments']['default']['php_version'] );
		$this->assertEquals( '6.0', $parsedConfig['environments']['default']['wp_version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testParseCircularExtends(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "extends": "qit.json",
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Circular dependency detected in qit.json configuration' );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseTestPackages(): void {
		$configFile = $this->tempDir . '/qit.json';
		$testFile   = $this->tempDir . '/test.json';
		$testConfig = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "version": "1.0.0",
    "author": "Test Author",
    "test_command": "npm run test"
}
JSON;
		file_put_contents( $testFile, $testConfig );

		$config = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "test_packages": [
        {
            "type": "e2e",
            "name": "test-package",
            "file": "test.json"
        }
    ]
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'test_packages', $parsedConfig );
		$this->assertArrayHasKey( 'e2e', $parsedConfig['test_packages'] );
		$this->assertArrayHasKey( 'test-package', $parsedConfig['test_packages']['e2e'] );
		$this->assertEquals( '1.0.0', $parsedConfig['test_packages']['e2e']['test-package']['config']['version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testParseSourceOtherTypes(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "url",
            "url": "https://example.com/plugin.zip"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertEquals( 'url', $parsedConfig['sut']['source']['type'] );
		$this->assertEquals( 'https://example.com/plugin.zip', $parsedConfig['sut']['source']['url'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );

		// Test zip source
		$config = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "zip",
            "path": "./plugin.zip"
        }
    }
}
JSON;
		file_put_contents( $this->tempDir . '/plugin.zip', 'dummy' );
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertEquals( 'zip', $parsedConfig['sut']['source']['type'] );
		$this->assertStringEndsWith( 'plugin.zip', $parsedConfig['sut']['source']['path'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );

		// Test wporg source
		$config = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "wporg"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertEquals( 'wporg', $parsedConfig['sut']['source']['type'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testInvalidSutConsistency(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "environments": {
        "default": {
            "plugins": [
                {"slug": "test-plugin", "source": {"type": "zip", "path": "./wrong.zip"}}
            ]
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $this->tempDir . '/wrong.zip', 'dummy' );
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "SUT configuration mismatch between main config and environment 'default' for plugin 'test-plugin'" );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testInvalidTestPackage(): void {
		$configFile = $this->tempDir . '/qit.json';
		$testFile   = $this->tempDir . '/test.json';
		file_put_contents( $testFile, json_encode( [
			// Missing $schema
			'version' => '1.0.0',
			'author'  => 'Test Author'
		] ) );

		$config = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "test_packages": [
        {
            "type": "e2e",
            "name": "test-package",
            "file": "test.json"
        }
    ]
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Test package 'e2e:test-package' must have \$schema set to 'https://qit.woo.com/json-schema/test-package'." );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testExtendsMissingBase(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "extends": "nonexistent.json",
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Base config file 'nonexistent.json' not found." );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testInvalidSourceType(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "invalid"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Invalid source type 'invalid' for sut.source. Must be one of: build, directory, url, zip, wccom, wporg" );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testEnvironmentSetupTestPackages(): void {
		$configFile = $this->tempDir . '/qit.json';
		$testFile   = $this->tempDir . '/test.json';
		file_put_contents( $testFile, json_encode( [
			'$schema'      => 'https://qit.woo.com/json-schema/test-package',
			'version'      => '1.0.0',
			'author'       => 'Test Author',
			'test_command' => 'npm run test'
		] ) );

		$config = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "environments": {
        "default": {
            "setup": {
                "test_packages": ["e2e:test-package@1.0.0"]
            }
        }
    },
    "test_packages": [
        {
            "type": "e2e",
            "name": "test-package",
            "file": "test.json"
        }
    ]
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$parser       = new ConfigParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'setup', $parsedConfig['environments']['default'] );
		$this->assertEquals( [ 'e2e:test-package@1.0.0' ], $parsedConfig['environments']['default']['setup']['test_packages'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );

		// Test invalid format
		$config = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "environments": {
        "default": {
            "setup": {
                "test_packages": ["invalid_package"]
            }
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Invalid test package format 'invalid_package'" );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testInvalidTestTypeProfile(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "directory",
            "path": "./plugin"
        }
    },
    "test_types": {
        "e2e": {
            "default": {
                "run": {}
            }
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "run in 'e2e:default' must be an array with a 'test_packages' array." );
		new ConfigParser( $configFile, $GLOBALS['qit_application'] );
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