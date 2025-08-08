<?php

namespace QIT_CLI\Tests\Unit\Environment;

use PHPUnit\Framework\TestCase;
use QIT_CLI\Environment\SecretManager;
use RuntimeException;

/**
 * Test SecretManager functionality for secret validation, injection, and redaction.
 */
class SecretManagerTest extends TestCase {

	private SecretManager $manager;

	protected function setUp(): void {
		parent::setUp();
		$this->manager = new SecretManager();
		// Clear any existing environment variables
		putenv( 'TEST_SECRET' );
		putenv( 'STRIPE_SECRET_KEY' );
		putenv( 'API_TOKEN' );
	}

	protected function tearDown(): void {
		// Clean up environment
		putenv( 'TEST_SECRET' );
		putenv( 'STRIPE_SECRET_KEY' );
		putenv( 'API_TOKEN' );
		parent::tearDown();
	}

	/**
	 * Test validation passes when all required secrets are present.
	 */
	public function test_validation_passes_with_all_secrets_present(): void {
		// Set up environment
		putenv( 'TEST_SECRET=my-secret-value' );
		putenv( 'API_TOKEN=token123' );

		// Should not throw
		$this->manager->validate( [ 'TEST_SECRET', 'API_TOKEN' ] );

		// Verify secrets are tracked
		$this->assertEquals( 2, $this->manager->get_secret_count() );
	}

	/**
	 * Test validation fails when secrets are missing.
	 */
	public function test_validation_fails_with_missing_secrets(): void {
		// Set only one secret
		putenv( 'TEST_SECRET=value' );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Missing required secrets' );

		try {
			$this->manager->validate( [ 'TEST_SECRET', 'MISSING_SECRET', 'ANOTHER_MISSING' ] );
		} catch ( RuntimeException $e ) {
			// Verify the error message contains helpful information
			$this->assertStringContainsString( 'MISSING_SECRET', $e->getMessage() );
			$this->assertStringContainsString( 'ANOTHER_MISSING', $e->getMessage() );
			$this->assertStringContainsString( 'export MISSING_SECRET=', $e->getMessage() );
			$this->assertStringContainsString( '--env-file=.env.test', $e->getMessage() );
			throw $e;
		}
	}

	/**
	 * Test redaction of secret values from text.
	 */
	public function test_redacts_secret_values_from_text(): void {
		putenv( 'API_KEY=super-secret-key-123' );
		putenv( 'DATABASE_PASSWORD=p@ssw0rd!' );

		$this->manager->validate( [ 'API_KEY', 'DATABASE_PASSWORD' ] );

		$text = 'Connecting to API with key: super-secret-key-123 and password: p@ssw0rd!';
		$redacted = $this->manager->redact( $text );

		$this->assertStringNotContainsString( 'super-secret-key-123', $redacted );
		$this->assertStringNotContainsString( 'p@ssw0rd!', $redacted );
		$this->assertStringContainsString( '[REDACTED:API_KEY]', $redacted );
		$this->assertStringContainsString( '[REDACTED:DATABASE_PASSWORD]', $redacted );
	}

	/**
	 * Test redaction of URL-encoded secrets.
	 */
	public function test_redacts_url_encoded_secrets(): void {
		putenv( 'SECRET=hello world' );
		$this->manager->validate( [ 'SECRET' ] );

		$text = 'URL: https://api.com?token=hello+world&encoded=' . urlencode( 'hello world' );
		$redacted = $this->manager->redact( $text );

		$this->assertStringNotContainsString( 'hello world', $redacted );
		$this->assertStringNotContainsString( 'hello+world', $redacted );
		$this->assertStringNotContainsString( urlencode( 'hello world' ), $redacted );
	}

	/**
	 * Test that pattern-based redaction is NOT performed (only declared secrets).
	 */
	public function test_does_not_redact_patterns_without_declaration(): void {
		// These should NOT be redacted because they weren't declared as secrets
		$test_cases = [
			'GitHub token: ghp_EXAMPLE1234567890abcdefghijklmnopqr',
			'Stripe key: sk_test_EXAMPLE_NOT_A_REAL_KEY_123456',
			'AWS key: AKIAEXAMPLENOTREAL',
			'JWT: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
			'Bearer AbCdEf123456',
			'URL: https://user:pass@example.com',
		];

		foreach ( $test_cases as $input ) {
			$redacted = $this->manager->redact( $input );
			// Should remain unchanged since no secrets were declared
			$this->assertEquals( $input, $redacted, "Text should not be redacted without declaration: {$input}" );
		}
	}

	/**
	 * Test that high-entropy strings are NOT redacted without declaration.
	 */
	public function test_does_not_redact_high_entropy_strings_without_declaration(): void {
		$high_entropy = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
		$text = "Random key: {$high_entropy}";
		$redacted = $this->manager->redact( $text );

		// Should not be redacted without declaration
		$this->assertEquals( $text, $redacted );
	}

	/**
	 * Test Docker environment flag generation.
	 */
	public function test_generates_docker_env_flags(): void {
		putenv( 'SECRET1=value1' );
		putenv( 'SECRET2=value2' );

		$this->manager->validate( [ 'SECRET1', 'SECRET2' ] );

		$flags = $this->manager->get_docker_env_flags();
		$this->assertEquals( [ 'SECRET1', 'SECRET2' ], $flags );
	}

	/**
	 * Test environment array generation for process execution.
	 */
	public function test_generates_env_array(): void {
		putenv( 'MY_SECRET=secret-value' );
		putenv( 'API_KEY=key123' );

		$this->manager->validate( [ 'MY_SECRET', 'API_KEY' ] );

		$env = $this->manager->get_env_array();
		$this->assertEquals( [
			'MY_SECRET' => 'secret-value',
			'API_KEY' => 'key123',
		], $env );
	}

	/**
	 * Test base64 encoding redaction for declared secrets.
	 */
	public function test_redacts_base64_encoded_secrets(): void {
		putenv( 'API_SECRET=my-secret-value' );
		$this->manager->validate( [ 'API_SECRET' ] );

		$base64_value = base64_encode( 'my-secret-value' );
		$text = "Encoded secret: {$base64_value}";
		$redacted = $this->manager->redact( $text );

		$this->assertStringNotContainsString( $base64_value, $redacted );
		$this->assertStringContainsString( '[REDACTED:API_SECRET]', $redacted );
	}

	/**
	 * Test manual secret addition.
	 */
	public function test_manually_adds_secrets(): void {
		$this->manager->add_secret_value( 'MANUAL_SECRET', 'manual-value' );

		$text = 'The value is: manual-value';
		$redacted = $this->manager->redact( $text );

		$this->assertStringNotContainsString( 'manual-value', $redacted );
		$this->assertStringContainsString( '[REDACTED:MANUAL_SECRET]', $redacted );
	}

	/**
	 * Test clearing secrets.
	 */
	public function test_clears_secrets(): void {
		putenv( 'SECRET=value' );
		$this->manager->validate( [ 'SECRET' ] );
		$this->assertEquals( 1, $this->manager->get_secret_count() );

		$this->manager->clear();
		$this->assertEquals( 0, $this->manager->get_secret_count() );

		// Redaction should no longer work
		$text = 'The secret is: value';
		$redacted = $this->manager->redact( $text );
		$this->assertEquals( $text, $redacted );
	}

	/**
	 * Test that very short values are not redacted.
	 */
	public function test_does_not_redact_short_values(): void {
		putenv( 'SHORT=ab' );
		$this->manager->validate( [ 'SHORT' ] );

		$text = 'The value is: ab';
		$redacted = $this->manager->redact( $text );

		// Short values should not be redacted
		$this->assertEquals( $text, $redacted );
	}

	/**
	 * Test empty secret value handling.
	 */
	public function test_handles_empty_secret_values(): void {
		putenv( 'EMPTY_SECRET=' );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Missing required secrets' );

		$this->manager->validate( [ 'EMPTY_SECRET' ] );
	}
}