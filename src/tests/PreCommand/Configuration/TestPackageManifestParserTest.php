<?php

namespace QIT_CLI_Tests\PreCommand\Configuration;

use PHPUnit\Framework\TestCase;
use QIT_CLI\PreCommand\Configuration\TestPackageManifestParser;
use Spatie\Snapshots\MatchesSnapshots;

class TestPackageManifestParserTest extends TestCase {
	use MatchesSnapshots;

	private $tempDir = '/tmp/test_package_test';
	private $parser;

	protected function setUp(): void {
		parent::setUp();
		$this->deleteDir( $this->tempDir );
		mkdir( $this->tempDir, 0777, true );
		$this->parser = new TestPackageManifestParser();
	}

	protected function tearDown(): void {
		$this->deleteDir( $this->tempDir );
		parent::tearDown();
	}

	public function testMinimalValidManifest(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e"
}
JSON;
		file_put_contents( $manifestFile, $manifest );

		$parsed = $this->parser->parse( $manifestFile );

		$this->assertEquals( 'e2e', $parsed['test_type'] );
		$this->assertArrayHasKey( '$schema', $parsed );
		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function testCompleteManifest(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
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
        "environment": {
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
		mkdir( $this->tempDir . '/tests', 0777, true );
		mkdir( $this->tempDir . '/mu-plugins', 0777, true );
		file_put_contents( $this->tempDir . '/cleanup.sh', '#!/bin/bash' );
		chmod( $this->tempDir . '/cleanup.sh', 0755 );

		file_put_contents( $manifestFile, $manifest );

		$parsed = $this->parser->parse( $manifestFile );

		// Basic properties
		$this->assertEquals( 'e2e', $parsed['test_type'] );
		$this->assertEquals( './tests', $parsed['test_dir'] );
		$this->assertEquals( 'Comprehensive E2E tests for checkout flow', $parsed['description'] );
		$this->assertEquals( [ 'checkout', 'payments', 'critical' ], $parsed['tags'] );

		// Requirements
		$this->assertArrayHasKey( 'requires', $parsed );
		$this->assertEquals( '^8.0.0', $parsed['requires']['plugins']['woocommerce'] );
		$this->assertEquals( [ 'STRIPE_API_KEY', 'PAYPAL_CLIENT_ID' ], $parsed['requires']['secrets'] );

		// Lifecycle
		$this->assertCount( 2, $parsed['lifecycle']['environment']['setup'] );
		$this->assertEquals( 'composer install', $parsed['lifecycle']['environment']['setup'][0] );
		$this->assertIsArray( $parsed['lifecycle']['environment']['setup'][1] );
		$this->assertEquals( 300, $parsed['lifecycle']['environment']['setup'][1]['timeout'] );

		// Test results paths should remain relative
		$this->assertEquals( './results/junit.xml', $parsed['test_results']['junit'] );
		$this->assertEquals( './results/test-results.json', $parsed['test_results']['json'] );

		// MU plugins paths should remain relative
		$this->assertEquals( './mu-plugins/test-helper.php', $parsed['mu_plugins'][0] );
		$this->assertEquals( './mu-plugins/mock-payments.php', $parsed['mu_plugins'][1] );

		// Environment variables should be converted to strings
		$this->assertIsString( $parsed['env_vars']['WP_DEBUG'] );
		$this->assertEquals( 'true', $parsed['env_vars']['WP_DEBUG'] );
		$this->assertEquals( 'false', $parsed['env_vars']['SCRIPT_DEBUG'] );
		$this->assertEquals( '30000', $parsed['env_vars']['TEST_TIMEOUT'] );
		$this->assertEquals( 'testing', $parsed['env_vars']['ENVIRONMENT'] );

		// Timeout and retry
		$this->assertEquals( 1800, $parsed['timeout'] );
		$this->assertEquals( 3, $parsed['retry']['times'] );
		$this->assertEquals( 10, $parsed['retry']['delay'] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function testLifecycleNormalization(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "unit",
    "lifecycle": {
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
		mkdir( $this->tempDir . '/scripts', 0777, true );
		file_put_contents( $this->tempDir . '/scripts/setup.sh', '#!/bin/bash' );
		chmod( $this->tempDir . '/scripts/setup.sh', 0755 );

		file_put_contents( $manifestFile, $manifest );

		$parsed = $this->parser->parse( $manifestFile );

		$this->assertCount( 3, $parsed['lifecycle']['test']['setup'] );

		// String commands remain strings
		$this->assertIsString( $parsed['lifecycle']['test']['setup'][0] );
		$this->assertEquals( 'composer install', $parsed['lifecycle']['test']['setup'][0] );

		// Object commands remain objects
		$this->assertIsArray( $parsed['lifecycle']['test']['setup'][1] );
		$this->assertEquals( './scripts/setup.sh', $parsed['lifecycle']['test']['setup'][1]['command'] );
		$this->assertEquals( 'host', $parsed['lifecycle']['test']['setup'][1]['runs_on'] );

		$this->assertIsArray( $parsed['lifecycle']['test']['setup'][2] );
		$this->assertTrue( $parsed['lifecycle']['test']['setup'][2]['continue_on_error'] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function testEnvVarTypeConversion(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "security",
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
		file_put_contents( $manifestFile, $manifest );

		$parsed = $this->parser->parse( $manifestFile );

		// All values should be strings
		foreach ( $parsed['env_vars'] as $key => $value ) {
			$this->assertIsString( $value, "env_vars[$key] should be a string" );
		}

		$this->assertEquals( 'true', $parsed['env_vars']['DEBUG'] );
		$this->assertEquals( 'false', $parsed['env_vars']['VERBOSE'] );
		$this->assertEquals( '5', $parsed['env_vars']['MAX_RETRIES'] );
		$this->assertEquals( '30.5', $parsed['env_vars']['TIMEOUT'] );
		$this->assertEquals( 'abc123', $parsed['env_vars']['API_KEY'] );
		$this->assertEquals( '', $parsed['env_vars']['EMPTY_STRING'] );
		$this->assertEquals( '0', $parsed['env_vars']['ZERO'] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function testPathsRemainRelative(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "integration",
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
		mkdir( $this->tempDir . '/src/tests', 0777, true );
		mkdir( $this->tempDir . '/output/coverage', 0777, true );
		mkdir( $this->tempDir . '/plugins', 0777, true );

		file_put_contents( $manifestFile, $manifest );

		$parsed = $this->parser->parse( $manifestFile );

		// All paths should remain relative as specified in the manifest
		$this->assertEquals( './src/tests', $parsed['test_dir'] );

		$this->assertEquals( './output/junit.xml', $parsed['test_results']['junit'] );
		$this->assertEquals( './output/coverage/index.html', $parsed['test_results']['coverage'] );

		$this->assertEquals( './plugins/helper.php', $parsed['mu_plugins'][0] );
		$this->assertEquals( './plugins/mocks.php', $parsed['mu_plugins'][1] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function testMissingRequiredField(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "description": "Missing test_type field"
}
JSON;
		file_put_contents( $manifestFile, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Schema validation failed' );

		$this->parser->parse( $manifestFile );
	}

	public function testInvalidSchema(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "invalid_field": "This field is not in the schema"
}
JSON;
		file_put_contents( $manifestFile, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Schema validation failed' );

		$this->parser->parse( $manifestFile );
	}

	public function testMissingTestDir(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "test_dir": "./nonexistent"
}
JSON;
		file_put_contents( $manifestFile, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Test directory not found' );

		$this->parser->parse( $manifestFile );
	}

	public function testMissingLifecycleScript(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "e2e",
    "lifecycle": {
        "test": {
            "setup": [
                {"command": "./scripts/missing.sh"}
            ]
        }
    }
}
JSON;
		file_put_contents( $manifestFile, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Lifecycle script not found: ./scripts/missing.sh' );

		$this->parser->parse( $manifestFile );
	}

	public function testEmptyLifecycleCommands(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "test_type": "phpstan",
    "lifecycle": {
        "test": {
            "setup": [],
            "run": ["phpstan analyze"],
            "teardown": []
        }
    }
}
JSON;
		file_put_contents( $manifestFile, $manifest );

		$parsed = $this->parser->parse( $manifestFile );

		$this->assertCount( 0, $parsed['lifecycle']['test']['setup'] );
		$this->assertCount( 1, $parsed['lifecycle']['test']['run'] );
		$this->assertCount( 0, $parsed['lifecycle']['test']['teardown'] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function testInvalidJson(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		file_put_contents( $manifestFile, '{ invalid json' );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Invalid JSON' );

		$this->parser->parse( $manifestFile );
	}

	public function testMissingFile(): void {
		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'File not found' );

		$this->parser->parse( $this->tempDir . '/nonexistent.json' );
	}

	public function testWrongSchema(): void {
		$manifestFile = $this->tempDir . '/manifest.json';
		$manifest     = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/qit",
    "test_type": "e2e"
}
JSON;
		file_put_contents( $manifestFile, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Schema validation failed' );

		$this->parser->parse( $manifestFile );
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