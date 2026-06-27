<?php

namespace QIT_CLI_Tests;

use PHPUnit\Framework\TestCase;

class TestPackageManifestSchemaTest extends TestCase {
	public function test_schema_allows_compatibility_test_type(): void {
		$schema = json_decode(
			file_get_contents( __DIR__ . '/../../src/PreCommand/Schemas/test-package-manifest-schema.json' ),
			true
		);

		$this->assertContains( 'compatibility', $schema['properties']['test_type']['enum'] );
	}
}
