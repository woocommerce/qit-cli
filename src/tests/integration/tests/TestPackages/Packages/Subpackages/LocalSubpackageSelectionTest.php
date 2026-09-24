<?php

namespace QIT\IntegrationTests\TestPackages\Packages\Subpackages;

use PHPUnit\Framework\TestCase;
use function qit;

/**
 * Test validation of the --subpackage option for run:e2e.
 *
 * The --subpackage option selects subpackages of a single LOCAL test package.
 * These tests cover the fail-fast validation, which happens before any
 * environment setup - so they don't need Docker.
 */
class LocalSubpackageSelectionTest extends TestCase {

	private string $fixturesDir;
	private string $originalCwd;

	protected function setUp(): void {
		parent::setUp();
		$this->fixturesDir = QIT_INTEGRATION_TESTS_ROOT . '/fixtures/test-packages';
		$this->originalCwd = getcwd();
	}

	protected function tearDown(): void {
		chdir( $this->originalCwd );
		parent::tearDown();
	}

	/**
	 * Test that an unknown subpackage ID fails fast, listing the available IDs.
	 */
	public function test_unknown_subpackage_id_fails_with_available_list(): void {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/subpackages-parent',
			'--subpackage=woocommerce/qit-integration-test-nonexistent',
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput() . "\n" . $proc->getErrorOutput();

		$this->assertStringContainsString(
			"Subpackage 'woocommerce/qit-integration-test-nonexistent' not found in test package 'woocommerce/qit-integration-test-e2e-suite'",
			$output
		);
		// Available subpackages should be listed.
		$this->assertStringContainsString( 'woocommerce/qit-integration-test-checkout', $output );
		$this->assertStringContainsString( 'woocommerce/qit-integration-test-cart', $output );
		$this->assertStringContainsString( 'woocommerce/qit-integration-test-account', $output );

		// Validation must fail before any environment work starts.
		$this->assertStringNotContainsString( 'Creating environment', $output );
	}

	/**
	 * Test that short subpackage names are rejected - full IDs only.
	 */
	public function test_short_subpackage_name_is_rejected(): void {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/subpackages-parent',
			'--subpackage=qit-integration-test-checkout',
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput() . "\n" . $proc->getErrorOutput();

		$this->assertStringContainsString(
			"Subpackage 'qit-integration-test-checkout' not found",
			$output
		);
	}

	/**
	 * Test that combining --subpackage with a remote package reference fails.
	 */
	public function test_subpackage_with_remote_reference_fails(): void {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/subpackages-parent',
			'--test-package=woocommerce/some-remote-package:latest',
			'--subpackage=woocommerce/qit-integration-test-checkout',
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput() . "\n" . $proc->getErrorOutput();

		$this->assertStringContainsString(
			'Remote test package references are not allowed when using --subpackage',
			$output
		);
		$this->assertStringContainsString( 'woocommerce/some-remote-package:latest', $output );
	}

	/**
	 * Test that --subpackage with no local test package fails.
	 */
	public function test_subpackage_without_local_package_fails(): void {
		// Run from a directory WITHOUT a qit-test.json and pass no --test-package.
		chdir( sys_get_temp_dir() );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--subpackage=woocommerce/qit-integration-test-checkout',
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput() . "\n" . $proc->getErrorOutput();

		$this->assertStringContainsString(
			'The --subpackage option requires a local test package',
			$output
		);
	}

	/**
	 * Test that --subpackage with multiple local test packages fails.
	 */
	public function test_subpackage_with_multiple_local_packages_fails(): void {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/subpackages-parent',
			'--test-package=' . $this->fixturesDir . '/regular-test-package-one',
			'--subpackage=woocommerce/qit-integration-test-checkout',
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput() . "\n" . $proc->getErrorOutput();

		$this->assertStringContainsString(
			'requires exactly ONE local test package, but 2 were found',
			$output
		);
	}

	/**
	 * Test that --subpackage against a package with no subpackages fails.
	 */
	public function test_subpackage_against_package_without_subpackages_fails(): void {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/regular-test-package-one',
			'--subpackage=woocommerce/qit-integration-test-checkout',
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput() . "\n" . $proc->getErrorOutput();

		$this->assertStringContainsString( 'does not define any subpackages in qit-test.json', $output );
	}

	/**
	 * Test that the current directory counts as the local package (auto-detect).
	 */
	public function test_cwd_auto_detection_counts_as_local_package(): void {
		// Run from inside the fixture - no --test-package needed.
		chdir( $this->fixturesDir . '/subpackages-parent' );

		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--subpackage=woocommerce/qit-integration-test-nonexistent',
		], expected_exit_code: 1, return_process: true );

		$output = $proc->getOutput() . "\n" . $proc->getErrorOutput();

		// The 'not found' error (rather than 'requires a local test package')
		// proves the cwd was auto-detected as the local parent package.
		$this->assertStringContainsString(
			"Subpackage 'woocommerce/qit-integration-test-nonexistent' not found in test package 'woocommerce/qit-integration-test-e2e-suite'",
			$output
		);
	}

	/**
	 * Test that validation errors are emitted as JSON in --json mode.
	 */
	public function test_subpackage_validation_error_in_json_mode(): void {
		$proc = qit( [
			'run:e2e',
			'woocommerce',
			'--test-package=' . $this->fixturesDir . '/subpackages-parent',
			'--subpackage=woocommerce/qit-integration-test-nonexistent',
			'--json',
		], expected_exit_code: 1, return_process: true );

		$output = trim( $proc->getOutput() );

		// The error payload is a JSON object on stdout; tolerate any preamble lines.
		$json_start = strpos( $output, '{"error"' );
		$this->assertNotFalse( $json_start, 'Output should contain a JSON error payload. Output: ' . $output );

		$data = json_decode( substr( $output, $json_start ), true );
		$this->assertNotNull( $data, 'Error payload should be valid JSON. Output: ' . $output );
		$this->assertEquals( 'invalid_subpackage', $data['error'] ?? null );
		$this->assertStringContainsString( 'not found in test package', $data['message'] ?? '' );
	}
}
