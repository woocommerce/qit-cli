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
    "namespace": "test",
    "package": "minimal-test",
    "test_type": "e2e",
    "test": {
        "phases": {
            "beforeAllPlugins": ["echo 'Before all plugins'"],
            "setup": [],
            "run": ["echo 'Test run'"],
            "teardown": [],
            "afterAllPlugins": []
        },
        "results": {
            "ctrf-json": "./test-results/ctrf-json/results.json"
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
    "namespace": "test",
    "package": "complete-test",
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
    "test": {
        "phases": {
            "beforeAllPlugins": [
                "composer install",
                {"command": "npm install", "timeout": 300}
            ],
            "setup": ["npm run build"],
            "run": [
                {"command": "npm test", "env": {"NODE_ENV": "test"}}
            ],
            "teardown": ["rm -rf test-artifacts"],
            "afterAllPlugins": ["./cleanup.sh"]
        },
        "results": {
            "ctrf-json": "./results/ctrf-json/results.json",
            "allure-dir": "./results/allure"
        }
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

		// Test new phases structure
		$phases = $parsed->getPhases();
		$this->assertArrayHasKey( 'beforeAllPlugins', $phases );
		$this->assertArrayHasKey( 'setup', $phases );
		$this->assertArrayHasKey( 'run', $phases );
		$this->assertArrayHasKey( 'teardown', $phases );
		$this->assertArrayHasKey( 'afterAllPlugins', $phases );

		// Test phase commands
		$beforeAllPlugins = $parsed->getPhaseCommands( 'beforeAllPlugins' );
		$this->assertCount( 2, $beforeAllPlugins );
		$this->assertEquals( 'composer install', $beforeAllPlugins[0] );
		$this->assertIsArray( $beforeAllPlugins[1] );
		$this->assertEquals( 300, $beforeAllPlugins[1]['timeout'] );

		// Test backward compatibility - shim should map phases to lifecycle
		$globalSetup = $parsed->getLifecycleCommands( 'global', 'setup' );
		$this->assertCount( 2, $globalSetup );
		$this->assertEquals( 'composer install', $globalSetup[0] );
		$this->assertIsArray( $globalSetup[1] );
		$this->assertEquals( 300, $globalSetup[1]['timeout'] );

		// Test results paths should remain relative
		$testResults = $parsed->getTestResults();
		$this->assertEquals( './results/ctrf-json/results.json', $testResults['ctrf-json'] );
		$this->assertEquals( './results/allure', $testResults['allure-dir'] );

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

	public function test_phase_normalization(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "namespace": "test",
    "package": "phase-normalization-test",
    "test_type": "e2e",
    "test": {
        "phases": {
            "beforeAllPlugins": ["echo 'Before all plugins'"],
            "setup": [
                "composer install",
                {"command": "./scripts/setup.sh", "runs_on": "host"},
                {"command": "npm test", "continue_on_error": true}
            ],
            "run": ["phpunit"],
            "teardown": [],
            "afterAllPlugins": []
        },
        "results": {
            "ctrf-json": "./test-results/ctrf-json/results.json"
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

		// Test new phase commands method
		$setupCommands = $parsed->getPhaseCommands( 'setup' );
		$this->assertCount( 3, $setupCommands );

		// String commands remain strings
		$this->assertIsString( $setupCommands[0] );
		$this->assertEquals( 'composer install', $setupCommands[0] );

		// Object commands remain objects
		$this->assertIsArray( $setupCommands[1] );
		$this->assertEquals( './scripts/setup.sh', $setupCommands[1]['command'] );
		$this->assertEquals( 'host', $setupCommands[1]['runs_on'] );

		$this->assertIsArray( $setupCommands[2] );
		$this->assertTrue( $setupCommands[2]['continue_on_error'] );

		// Test backward compatibility - shim should map phases to lifecycle
		$testSetup = $parsed->getLifecycleCommands( 'test', 'setup' );
		$this->assertCount( 3, $testSetup );
		$this->assertEquals( 'composer install', $testSetup[0] );

		$this->assertMatchesJsonSnapshot( json_encode( $parsed, JSON_PRETTY_PRINT ) );
	}

	public function test_env_var_type_conversion(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "namespace": "test",
    "package": "env-var-test",
    "test_type": "e2e",
    "test": {
        "phases": {
            "beforeAllPlugins": ["echo 'Before all plugins'"],
            "setup": [],
            "run": ["echo 'Test run'"],
            "teardown": [],
            "afterAllPlugins": []
        },
        "results": {
            "ctrf-json": "./test-results/ctrf-json/results.json"
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
    "namespace": "test",
    "package": "paths-test",
    "test_type": "e2e",
    "test_dir": "./src/tests",
    "test": {
        "phases": {
            "beforeAllPlugins": ["echo 'Before all plugins'"],
            "setup": [],
            "run": ["echo 'Test run'"],
            "teardown": [],
            "afterAllPlugins": []
        },
        "results": {
            "ctrf-json": "./output/ctrf-json/results.json",
            "allure-dir": "./output/allure"
        }
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
		$this->assertEquals( './output/ctrf-json/results.json', $testResults['ctrf-json'] );
		$this->assertEquals( './output/allure', $testResults['allure-dir'] );

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
    "namespace": "test",
    "package": "invalid-schema-test",
    "test_type": "e2e",
    "test": {
        "phases": {
            "beforeAllPlugins": [],
            "setup": [],
            "run": ["echo 'Test run'"],
            "teardown": [],
            "afterAllPlugins": []
        },
        "results": {
            "ctrf-json": "./test-results/ctrf-json/results.json"
        }
    },
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
    "namespace": "test",
    "package": "missing-test-dir",
    "test_type": "e2e",
    "test_dir": "./nonexistent",
    "test": {
        "phases": {
            "beforeAllPlugins": [],
            "setup": [],
            "run": ["echo 'Test run'"],
            "teardown": [],
            "afterAllPlugins": []
        },
        "results": {
            "ctrf-json": "./test-results/ctrf-json/results.json"
        }
    }
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Test directory not found' );

		$this->parser->parse( $manifest_file );
	}

	public function test_missing_phase_script(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "namespace": "test",
    "package": "missing-phase-script",
    "test_type": "e2e",
    "test": {
        "phases": {
            "beforeAllPlugins": [],
            "setup": [
                {"command": "./scripts/missing.sh"}
            ],
            "run": ["echo 'Test run'"],
            "teardown": [],
            "afterAllPlugins": []
        },
        "results": {
            "ctrf-json": "./test-results/ctrf-json/results.json"
        }
    }
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$this->expectException( \RuntimeException::class );
		$this->expectExceptionMessage( 'Phase script not found: ./scripts/missing.sh' );

		$this->parser->parse( $manifest_file );
	}

	public function test_empty_phase_commands(): void {
		$manifest_file = $this->temp_dir . '/manifest.json';
		$manifest      = <<<'JSON'
{
    "$schema": "https://qit.woo.com/json-schema/test-package",
    "namespace": "test",
    "package": "empty-phase-commands",
    "test_type": "e2e",
    "test": {
        "phases": {
            "beforeAllPlugins": [],
            "setup": [],
            "run": ["phpstan analyze"],
            "teardown": [],
            "afterAllPlugins": []
        },
        "results": {
            "ctrf-json": "./test-results/ctrf-json/results.json"
        }
    }
}
JSON;
		file_put_contents( $manifest_file, $manifest );

		$parsed = $this->parser->parse( $manifest_file );

		// Test new phase commands methods
		$this->assertCount( 0, $parsed->getPhaseCommands( 'setup' ) );
		$this->assertCount( 1, $parsed->getPhaseCommands( 'run' ) );
		$this->assertCount( 0, $parsed->getPhaseCommands( 'teardown' ) );

		// Test backward compatibility - shim should work
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
    "namespace": "test",
    "package": "wrong-schema-test",
    "test_type": "e2e",
    "test": {
        "phases": {
            "beforeAllPlugins": [],
            "setup": [],
            "run": ["echo 'Test run'"],
            "teardown": [],
            "afterAllPlugins": []
        },
        "results": {
            "ctrf-json": "./test-results/ctrf-json/results.json"
        }
    }
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
