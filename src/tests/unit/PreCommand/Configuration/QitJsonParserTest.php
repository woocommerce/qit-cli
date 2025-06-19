<?php

namespace QIT_CLI_Tests\PreCommand\Configuration;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Configuration\Parser\QitJsonParser;
use Spatie\Snapshots\MatchesSnapshots;

class QitJsonParserTest extends TestCase {
	use MatchesSnapshots;

	private $temp_dir = '/tmp/qit_test_fixed';

	protected function setUp(): void {
		parent::setUp();
		// Clean up and recreate the fixed temp directory
		$this->delete_dir( $this->temp_dir );
		mkdir( $this->temp_dir, 0777, true );
	}

	protected function tearDown(): void {
		// Clean up the fixed temp directory
		$this->delete_dir( $this->temp_dir );
		parent::tearDown();
	}

	public function test_minimal_valid_config(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		mkdir( $this->temp_dir . '/plugin', 0777, true );
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertArrayHasKey( 'sut', $parsed_config );
		$this->assertEquals( 'plugin', $parsed_config['sut']['type'] );
		$this->assertEquals( 'test-plugin', $parsed_config['sut']['slug'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_plugins_and_themes(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertArrayHasKey( 'environments', $parsed_config );
		$this->assertEquals( '7.4', $parsed_config['environments']['default']['php_version'] );
		$this->assertCount( 2, $parsed_config['environments']['default']['plugins'] );
		$this->assertCount( 1, $parsed_config['environments']['default']['themes'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_test_types(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertArrayHasKey( 'test_types', $parsed_config );
		$this->assertArrayHasKey( 'e2e', $parsed_config['test_types'] );
		$this->assertArrayHasKey( 'default', $parsed_config['test_types']['e2e'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_config_with_groups(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertArrayHasKey( 'groups', $parsed_config );
		$this->assertArrayHasKey( 'pre_release', $parsed_config['groups'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_missing_source_in_sut(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
{
    "sut": {
        "type": "plugin",
        "slug": "test-plugin"
    }
}
JSON;
		file_put_contents( $config_file, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Schema validation failed" );
		$parser = new QitJsonParser();
		$parser->parse( $config_file );
	}

	public function test_parse_config_missing_file(): void {
		$config_file = $this->temp_dir . '/nonexistent.json';
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "File not found: $config_file" );
		$parser = new QitJsonParser();
		$parser->parse( $config_file );
	}

	public function test_parse_invalid_json(): void {
		$config_file = $this->temp_dir . '/qit.json';
		file_put_contents( $config_file, '{invalid json' );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Invalid JSON' );
		$parser = new QitJsonParser();
		$parser->parse( $config_file );
	}

	public function test_parse_missing_sut(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
{
    "environments": {
        "default": {
            "php_version": "7.4"
        }
    }
}
JSON;
		file_put_contents( $config_file, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "The 'sut' property is required in the configuration" );
		$parser = new QitJsonParser();
		$parser->parse( $config_file );
	}

	public function test_parse_sut_invalid_type(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Schema validation failed" );
		$parser = new QitJsonParser();
		$parser->parse( $config_file );
	}

	public function test_parse_source_build(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertEquals( 'build', $parsed_config['sut']['source']['type'] );
		$this->assertEquals( 'npm run build', $parsed_config['sut']['source']['command'] );
		$this->assertStringEndsWith( 'plugin.zip', $parsed_config['sut']['source']['output'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_parse_extends(): void {
		$base_file   = $this->temp_dir . '/base.json';
		$base_config = <<<'JSON'
{
    "environments": {
        "default": {
            "php_version": "7.4",
            "wp_version": "6.0"
        }
    }
}
JSON;
		file_put_contents( $base_file, $base_config );

		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertArrayHasKey( 'environments', $parsed_config );
		$this->assertEquals( '8.0', $parsed_config['environments']['default']['php_version'] );
		$this->assertEquals( '6.0', $parsed_config['environments']['default']['wp_version'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_parse_circular_extends(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
{
    "extends": "./qit.json",
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
		file_put_contents( $config_file, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Circular dependency detected' );
		$parser = new QitJsonParser();
		$parser->parse( $config_file );
	}

	public function test_parse_test_package_reference(): void {
		// Create test package manifest
		$package_file   = $this->temp_dir . '/test-package.json';
		$package_config = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
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
		mkdir( $this->temp_dir . '/tests/e2e', 0777, true );
		file_put_contents( $package_file, $package_config );

		// Create qit.json that references it
		$qit_config_file = $this->temp_dir . '/qit.json';
		$qit_config      = <<<JSON
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
		file_put_contents( $qit_config_file, $qit_config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $qit_config_file );

		// Verify the test package reference is stored
		$this->assertArrayHasKey( 'test_types', $parsed_config );
		$this->assertEquals( [ './test-package.json' ], $parsed_config['test_types']['e2e']['default']['test_packages'] );

		// Test lazy loading of the package
		$packages = $parser->get_test_packages_for_profile( 'e2e', 'default' );
		$this->assertArrayHasKey( './test-package.json', $packages );
		$this->assertEquals( 'e2e', $packages['./test-package.json']['test_type'] );
		$this->assertEquals( 'E2E tests for checkout', $packages['./test-package.json']['description'] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_parse_source_other_types(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertEquals( 'url', $parsed_config['sut']['source']['type'] );
		$this->assertEquals( 'https://example.com/plugin.zip', $parsed_config['sut']['source']['url'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );

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
		file_put_contents( $this->temp_dir . '/plugin.zip', 'dummy' );
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertEquals( 'zip', $parsed_config['sut']['source']['type'] );
		$this->assertStringEndsWith( 'plugin.zip', $parsed_config['sut']['source']['path'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );

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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertEquals( 'wporg', $parsed_config['sut']['source']['type'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_environment_with_env_vars(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertArrayHasKey( 'env_vars', $parsed_config['environments']['default'] );
		$this->assertEquals( 'true', $parsed_config['environments']['default']['env_vars']['QIT_DEBUG'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_environment_setup_only(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		$this->assertArrayHasKey( 'setup_only', $parsed_config['environments']['default'] );
		$this->assertEquals( [ 'woocommerce/minimal:stable' ], $parsed_config['environments']['default']['setup_only'] );

		// Test lazy loading of setup packages
		$setup_packages = $parser->get_setup_packages_for_environment( 'default' );
		$this->assertArrayHasKey( 'woocommerce/minimal:stable', $setup_packages );
		$this->assertTrue( $setup_packages['woocommerce/minimal:stable']['remote'] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_invalid_environment_key(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Schema validation failed" );
		$parser = new QitJsonParser();
		$parser->parse( $config_file );
	}

	public function test_invalid_group_reference(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Profile 'nonexistent' for test type 'e2e' in group 'pre_release' not found" );
		$parser = new QitJsonParser();
		$parser->parse( $config_file );
	}

	public function test_environment_extends_resolution(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
		file_put_contents( $config_file, $config );

		$parser        = new QitJsonParser();
		$parsed_config = $parser->parse( $config_file );

		// Check that legacy inherits from base but overrides specific values
		$this->assertEquals( '7.4', $parsed_config['environments']['legacy']['php_version'] );
		$this->assertEquals( '6.1', $parsed_config['environments']['legacy']['woo_version'] );
		$this->assertEquals( 'stable', $parsed_config['environments']['legacy']['wp_version'] );
		$this->assertTrue( $parsed_config['environments']['legacy']['object_cache'] );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed_config, JSON_PRETTY_PRINT ) );
	}

	public function test_remote_package_reference(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
                "test_packages": ["woocommerce/checkout:stable", "akismet/default:latest"]
            }
        }
    }
}
JSON;
		file_put_contents( $config_file, $config );

		$parser = new QitJsonParser();
		$parser->parse( $config_file );

		// Test getting remote packages
		$packages = $parser->get_test_packages_for_profile( 'e2e', 'default' );

		$this->assertCount( 2, $packages );
		$this->assertArrayHasKey( 'woocommerce/checkout:stable', $packages );
		$this->assertArrayHasKey( 'akismet/default:latest', $packages );

		// Check remote package structure
		$woo_package = $packages['woocommerce/checkout:stable'];
		$this->assertEquals( 'woocommerce', $woo_package['vendor'] );
		$this->assertEquals( 'checkout', $woo_package['package'] );
		$this->assertEquals( 'stable', $woo_package['version'] );
		$this->assertTrue( $woo_package['remote'] );
	}

	public function test_missing_local_package_file(): void {
		$config_file = $this->temp_dir . '/qit.json';
		$config      = <<<'JSON'
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
                "test_packages": ["./tests/nonexistent.json"]
            }
        }
    }
}
JSON;
		file_put_contents( $config_file, $config );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( "Test package file not found: ./tests/nonexistent.json" );

		$parser = new QitJsonParser();
		$parser->parse( $config_file );
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