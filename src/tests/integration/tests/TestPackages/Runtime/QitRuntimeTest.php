<?php

namespace integration\tests\TestPackages\Runtime;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function qit;

/**
 * End-to-end integration test for @woocommerce/qit-runtime.
 *
 * Proves the full chain works:
 *   manifest "actions" field → CLI generates actions-manifest.json + package-map.json
 *   → env vars set → runtime reads them → qit.actions() returns callable
 *   → qit.package() returns exports
 */
#[Group( 'docker' )]
class QitRuntimeTest extends TestCase {

	/**
	 * Test that qit.actions() and qit.package() work end-to-end
	 * with a provider package and a consumer package.
	 */
	public function test_actions_and_package_discovery(): void {
		$fixtures = __DIR__ . '/fixtures';

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package',
			$fixtures . '/consumer-package',
			'--test-package',
			$fixtures . '/provider-package',
		], return_process: true );

		$output    = $proc->getOutput();
		$exit_code = $proc->getExitCode();

		// The consumer's verify-runtime.js should pass all checks
		$this->assertEquals( 0, $exit_code, 'QIT runtime integration test failed. Output: ' . $output );
		$this->assertStringContainsString( 'passed', $output );
	}
}
