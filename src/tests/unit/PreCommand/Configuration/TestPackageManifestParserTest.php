<?php

namespace QIT_CLI_Tests\PreCommand\Configuration;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Configuration\Parser\TestPackageManifestParser;
use Spatie\Snapshots\MatchesSnapshots;

class TestPackageManifestParserTest extends TestCase {
	use MatchesSnapshots;

	private $temp_dir = '/tmp/test_package_test';
	private $parser;

	protected function setUp(): void {
		parent::setUp();
		$this->delete_dir( $this->temp_dir );
		mkdir( $this->temp_dir, 0777, true );
		$this->parser = new TestPackageManifestParser();
	}

	protected function tearDown(): void {
		$this->delete_dir( $this->temp_dir );
		parent::tearDown();
	}

	public function test_minimal_valid_manifest(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "lifecycle": {
        "global": {
            "setup": ["echo 'Global setup'"]
        },
        "test": {
            "run": ["echo 'Test run'"]
        }
    }
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$parsed = $this->parser->parse( $manifest_file );

		$this->assertEquals( 'e2e', $parsed->getTestType() );
		$this->assertTrue( $parsed->isE2E() );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function test_complete_manifest(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "test_dir": "./tests",
    "description": "Comprehensive E2E tests for checkout flow",
    "tags": ["checkout", "payments", "critical"],
    "requires": {
        "plugins": {
            "woocommerce": "^8.0.0",
            "woocommerce-payments": ">=5.0.0"
        },
        "themes": {
            "storefront": "*"
        },
        "wordpress": ">=6.0",
        "php": ">=7.4",
        "secrets": ["STRIPE_API_KEY", "PAYPAL_CLIENT_ID"],
        "external_services": ["Stripe API", "PayPal Sandbox"]
    },
    "lifecycle": {
        "global": {
            "setup": [
                "composer install",
                {"command": "npm install", "timeout": 300}
            ],
            "teardown": ["./cleanup.sh"]
        },
        "test": {
            "setup": ["npm run build"],
            "run": [
                {"command": "npm test", "env": {"NODE_ENV": "test"}}
            ],
            "teardown": ["rm -rf test-artifacts"]
        }
    },
    "test_results": {
        "junit": "./results/junit.xml",
        "json": "./results/test-results.json"
    },
    "mu_plugins": [
        "./mu-plugins/test-helper.php",
        "./mu-plugins/mock-payments.php"
    ],
    "env_vars": {
        "WP_DEBUG": true,
        "SCRIPT_DEBUG": false,
        "TEST_TIMEOUT": 30000,
        "ENVIRONMENT": "testing"
    },
    "timeout": 1800,
    "retry": {
        "times": 3,
        "delay": 10
    }
}
JSON;
		// Create required directories
		mkdir( $this->temp_dir . '/tests', 0777, true );
		mkdir( $this->temp_dir . '/mu-plugins', 0777, true );
		file_put_contents( $this->temp_dir . '/cleanup.sh', '#!/bin/bash' );
		chmod( $this->temp_dir . '/cleanup.sh', 0755 );

		file_put_contents( $manifest_file, $manifest );

		$parsed = $this->parser->parse( $manifest_file );

		// Basic properties
		$this->assertEquals( 'e2e', $parsed->getTestType() );
		$this->assertEquals( './tests', $parsed->getTestDir() );
		$this->assertEquals( 'Comprehensive E2E tests for checkout flow', $parsed->getDescription() );
		$this->assertEquals( [ 'checkout', 'payments', 'critical' ], $parsed->getTags() );

		// Requirements
		$requires = $parsed->getRequires();
		$this->assertArrayHasKey( 'plugins', $requires );
		$this->assertEquals( '^8.0.0', $requires['plugins']['woocommerce'] );
		$this->assertEquals( [ 'STRIPE_API_KEY', 'PAYPAL_CLIENT_ID' ], $requires['secrets'] );

		// Lifecycle
		$globalSetup = $parsed->getLifecycleCommands( 'global', 'setup' );
		$this->assertCount( 2, $globalSetup );
		$this->assertEquals( 'composer install', $globalSetup[0] );
		$this->assertIsArray( $globalSetup[1] );
		$this->assertEquals( 300, $globalSetup[1]['timeout'] );

		// Test results paths should remain relative
		$testResults = $parsed->getTestResults();
		$this->assertEquals( './results/junit.xml', $testResults['junit'] );
		$this->assertEquals( './results/test-results.json', $testResults['json'] );

		// MU plugins paths should remain relative
		$muPlugins = $parsed->getMuPlugins();
		$this->assertEquals( './mu-plugins/test-helper.php', $muPlugins[0] );
		$this->assertEquals( './mu-plugins/mock-payments.php', $muPlugins[1] );

		// Environment variables should be converted to strings
		$envVars = $parsed->getEnvVars();
		$this->assertIsString( $envVars['WP_DEBUG'] );
		$this->assertEquals( 'true', $envVars['WP_DEBUG'] );
		$this->assertEquals( 'false', $envVars['SCRIPT_DEBUG'] );
		$this->assertEquals( '30000', $envVars['TEST_TIMEOUT'] );
		$this->assertEquals( 'testing', $envVars['ENVIRONMENT'] );

		// Timeout and retry
		$this->assertEquals( 1800, $parsed->getTimeout() );
		$retry = $parsed->getRetry();
		$this->assertEquals( 3, $retry['times'] );
		$this->assertEquals( 10, $retry['delay'] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function test_lifecycle_normalization(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "lifecycle": {
        "global": {
            "setup": ["echo 'Global setup'"]
        },
        "test": {
            "setup": [
                "composer install",
                {"command": "./scripts/setup.sh", "runs_on": "host"},
                {"command": "npm test", "continue_on_error": true}
            ],
            "run": ["phpunit"]
        }
    }
}
JSON;
		// Create script file
		mkdir( $this->temp_dir . '/scripts', 0777, true );
		file_put_contents( $this->temp_dir . '/scripts/setup.sh', '#!/bin/bash' );
		chmod( $this->temp_dir . '/scripts/setup.sh', 0755 );

		file_put_contents( $manifest_file, $manifest );

		$parsed = $this->parser->parse( $manifest_file );

		$testSetup = $parsed->getLifecycleCommands( 'test', 'setup' );
		$this->assertCount( 3, $testSetup );

		// String commands remain strings
		$this->assertIsString( $testSetup[0] );
		$this->assertEquals( 'composer install', $testSetup[0] );

		// Object commands remain objects
		$this->assertIsArray( $testSetup[1] );
		$this->assertEquals( './scripts/setup.sh', $testSetup[1]['command'] );
		$this->assertEquals( 'host', $testSetup[1]['runs_on'] );

		$this->assertIsArray( $testSetup[2] );
		$this->assertTrue( $testSetup[2]['continue_on_error'] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function test_env_var_type_conversion(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "lifecycle": {
        "global": {
            "setup": ["echo 'Global setup'"]
        },
        "test": {
            "run": ["echo 'Test run'"]
        }
    },
    "env_vars": {
        "DEBUG": true,
        "VERBOSE": false,
        "MAX_RETRIES": 5,
        "TIMEOUT": 30.5,
        "API_KEY": "abc123",
        "EMPTY_STRING": "",
        "ZERO": 0
    }
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$parsed = $this->parser->parse( $manifest_file );

		// All values should be strings
		$envVars = $parsed->getEnvVars();
		foreach ( $envVars as $key => $value ) {
			$this->assertIsString( $value, "env_vars[$key] should be a string" );
		}

		$this->assertEquals( 'true', $envVars['DEBUG'] );
		$this->assertEquals( 'false', $envVars['VERBOSE'] );
		$this->assertEquals( '5', $envVars['MAX_RETRIES'] );
		$this->assertEquals( '30.5', $envVars['TIMEOUT'] );
		$this->assertEquals( 'abc123', $envVars['API_KEY'] );
		$this->assertEquals( '', $envVars['EMPTY_STRING'] );
		$this->assertEquals( '0', $envVars['ZERO'] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function test_paths_remain_relative(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "lifecycle": {
        "global": {
            "setup": ["echo 'Global setup'"]
        },
        "test": {
            "run": ["echo 'Test run'"]
        }
    },
    "test_dir": "./src/tests",
    "test_results": {
        "junit": "./output/junit.xml",
        "coverage": "./output/coverage/index.html"
    },
    "mu_plugins": [
        "./plugins/helper.php",
        "./plugins/mocks.php"
    ]
}
JSON;
		// Create directories
		mkdir( $this->temp_dir . '/src/tests', 0777, true );
		mkdir( $this->temp_dir . '/output/coverage', 0777, true );
		mkdir( $this->temp_dir . '/plugins', 0777, true );

		file_put_contents( $manifest_file, $manifest );

		$parsed = $this->parser->parse( $manifest_file );

		// All paths should remain relative as specified in the manifest
		$this->assertEquals( './src/tests', $parsed->getTestDir() );

		$testResults = $parsed->getTestResults();
		$this->assertEquals( './output/junit.xml', $testResults['junit'] );
		$this->assertEquals( './output/coverage/index.html', $testResults['coverage'] );

		$muPlugins = $parsed->getMuPlugins();
		$this->assertEquals( './plugins/helper.php', $muPlugins[0] );
		$this->assertEquals( './plugins/mocks.php', $muPlugins[1] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function test_missing_required_field(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "description": "Missing test_type field"
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Schema validation failed' );

		$this->parser->parse( $manifest_file );
	}

	public function test_invalid_schema(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "invalid_field": "This field is not in the schema"
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Schema validation failed' );

		$this->parser->parse( $manifest_file );
	}

	public function test_missing_test_dir(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "lifecycle": {
        "global": {
            "setup": ["echo 'Global setup'"]
        },
        "test": {
            "run": ["echo 'Test run'"]
        }
    },
    "test_dir": "./nonexistent"
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Test directory not found' );

		$this->parser->parse( $manifest_file );
	}

	public function test_missing_lifecycle_script(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "lifecycle": {
        "global": {
            "setup": ["echo 'Global setup'"]
        },
        "test": {
            "setup": [
                {"command": "./scripts/missing.sh"}
            ]
        }
    }
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Lifecycle script not found: ./scripts/missing.sh' );

		$this->parser->parse( $manifest_file );
	}

	public function test_empty_lifecycle_commands(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "lifecycle": {
        "global": {
            "setup": ["echo 'Global setup'"]
        },
        "test": {
            "setup": [],
            "run": ["phpstan analyze"],
            "teardown": []
        }
    }
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$parsed = $this->parser->parse( $manifest_file );

		$this->assertCount( 0, $parsed->getLifecycleCommands( 'test', 'setup' ) );
		$this->assertCount( 1, $parsed->getLifecycleCommands( 'test', 'run' ) );
		$this->assertCount( 0, $parsed->getLifecycleCommands( 'test', 'teardown' ) );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function test_invalid_json(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		file_put_contents( $manifest_file, '{ invalid json' );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Invalid JSON' );

		$this->parser->parse( $manifest_file );
	}

	public function test_missing_file(): void {
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'File not found' );

		$this->parser->parse( $this->temp_dir . '/nonexistent.json' );
	}

	public function test_wrong_schema(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/qit",
    "test_type": "e2e"
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Schema validation failed' );

		$this->parser->parse( $manifest_file );
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