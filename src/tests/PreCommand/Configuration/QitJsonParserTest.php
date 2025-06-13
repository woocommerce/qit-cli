<?php

namespace QIT_CLI_Tests\PreCommand\Configuration;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Configuration\QitJsonParser;
use Spatie\Snapshots\MatchesSnapshots;

class QitJsonParserTest extends TestCase {
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
            "type": "local",
            "path": "./"
        }
    }
}
JSON;
		mkdir( $this->tempDir . '/plugin', 0777, true );
		file_put_contents( $configFile, $config );

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
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
            "type": "local",
            "path": "./"
        }
    },
    "environments": {
        "default": {
            "php_version": "7.4",
            "wp_version": "6.0",
            "plugins": [
                {"slug": "woocommerce", "from": "wccom"},
                {"slug": "test-plugin", "from": "local", "path": "./"}
            ],
            "themes": [
                "storefront"
            ]
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
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
            "type": "local",
            "path": "./"
        }
    },
    "test_types": {
        "e2e": {
            "default": {
                "test_packages": ["woocommerce/e2e:stable"]
            }
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'test_types', $parsedConfig );
		$this->assertArrayHasKey( 'e2e', $parsedConfig['test_types'] );
		$this->assertArrayHasKey( 'default', $parsedConfig['test_types']['e2e'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testConfigWithGroups(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "local",
            "path": "./"
        }
    },
    "test_types": {
        "e2e": {
            "default": {
                "test_packages": ["woocommerce/e2e:stable"]
            }
        },
        "security": {
            "default": {}
        }
    },
    "groups": {
        "pre_release": {
            "e2e": ["default"],
            "security": ["default"]
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'groups', $parsedConfig );
		$this->assertArrayHasKey( 'pre_release', $parsedConfig['groups'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
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
		$this->expectExceptionMessage( "Schema validation failed" );
		new \QIT_CLI\PreCommand\Configuration\QitJsonParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseConfigMissingFile(): void {
		$configFile = $this->tempDir . '/nonexistent.json';
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "File not found: $configFile" );
		new \QIT_CLI\PreCommand\Configuration\QitJsonParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseInvalidJson(): void {
		$configFile = $this->tempDir . '/qit.json';
		file_put_contents( $configFile, '{invalid json' );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Invalid JSON' );
		new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
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
		$this->expectExceptionMessage( "The 'sut' property is required in the final configuration" );
		new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseSutInvalidType(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "invalid",
        "slug": "test-plugin",
        "source": {
            "type": "local",
            "path": "./"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Schema validation failed" );
		new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
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

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
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
            "type": "local",
            "path": "./"
        }
    },
    "environments": {
        "default": {
            "php_version": "8.0"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
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
            "type": "local",
            "path": "./"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Circular dependency detected' );
		new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testParseTestPackageManifest(): void {
		$configFile = $this->tempDir . '/test-package.json';
		$config     = <<<'JSON'
{
    "test_type": "e2e",
    "test_dir": "./tests/e2e",
    "description": "E2E tests for checkout",
    "lifecycle": {
        "test": {
            "run": ["npm run playwright"]
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		// Since we're testing a test package manifest directly, we need to test it through
		// a qit.json that references it
		$qitConfigFile = $this->tempDir . '/qit.json';
		$qitConfig     = <<<JSON
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "local",
            "path": "./"
        }
    },
    "test_types": {
        "e2e": {
            "default": {
                "test_packages": ["./test-package.json"]
            }
        }
    }
}
JSON;
		file_put_contents( $qitConfigFile, $qitConfig );

		$parser       = new QitJsonParser( $qitConfigFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		// Test packages are processed during test execution, not during config parsing
		$this->assertArrayHasKey( 'test_types', $parsedConfig );
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

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
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

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
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

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertEquals( 'wporg', $parsedConfig['sut']['source']['type'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testEnvironmentWithEnvVars(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "local",
            "path": "./"
        }
    },
    "environments": {
        "default": {
            "php_version": "8.0",
            "env_vars": {
                "QIT_DEBUG": "true",
                "WP_DEBUG": "false"
            }
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'env_vars', $parsedConfig['environments']['default'] );
		$this->assertEquals( 'true', $parsedConfig['environments']['default']['env_vars']['QIT_DEBUG'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
	}

	public function testEnvironmentSetupOnly(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "local",
            "path": "./"
        }
    },
    "environments": {
        "default": {
            "setup_only": ["woocommerce/minimal:stable"]
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		$this->assertArrayHasKey( 'setup_only', $parsedConfig['environments']['default'] );
		$this->assertEquals( [ 'woocommerce/minimal:stable' ], $parsedConfig['environments']['default']['setup_only'] );
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
            "type": "local",
            "path": "./"
        }
    },
    "environments": {
        "default": {
            "invalid_key": "value"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Schema validation failed" );
		new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testInvalidGroupReference(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "local",
            "path": "./"
        }
    },
    "test_types": {
        "e2e": {
            "default": {}
        }
    },
    "groups": {
        "pre_release": {
            "e2e": ["nonexistent"]
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Profile 'nonexistent' for test type 'e2e' in group 'pre_release' not found" );
		new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
	}

	public function testEnvironmentExtendsResolution(): void {
		$configFile = $this->tempDir . '/qit.json';
		$config     = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin",
        "source": {
            "type": "local",
            "path": "./"
        }
    },
    "environments": {
        "base": {
            "php_version": "8.2",
            "wp_version": "stable",
            "woo_version": "stable",
            "object_cache": true
        },
        "legacy": {
            "extends": "base",
            "php_version": "7.4",
            "woo_version": "6.1"
        }
    }
}
JSON;
		file_put_contents( $configFile, $config );

		$parser       = new QitJsonParser( $configFile, $GLOBALS['qit_application'] );
		$parsedConfig = $parser->parsed_config;

		// Check that legacy inherits from base but overrides specific values
		$this->assertEquals( '7.4', $parsedConfig['environments']['legacy']['php_version'] );
		$this->assertEquals( '6.1', $parsedConfig['environments']['legacy']['woo_version'] );
		$this->assertEquals( 'stable', $parsedConfig['environments']['legacy']['wp_version'] );
		$this->assertTrue( $parsedConfig['environments']['legacy']['object_cache'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsedConfig, JSON_PRETTY_PRINT ) );
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