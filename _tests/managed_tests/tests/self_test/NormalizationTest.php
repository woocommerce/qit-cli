<?php

namespace QITE2E\self_test;

use QITE2E\QITE2ETestCase;

class NormalizationTest extends QITE2ETestCase {
	private $fixture_path;

	protected function tearDown(): void {
		if ( $this->fixture_path && file_exists( $this->fixture_path ) ) {
			unlink( $this->fixture_path );
		}

		parent::tearDown();
	}

	public function test_woo_e2e_noise_is_narrowly_normalized() {
		$this->fixture_path = tempnam( __DIR__ . '/../../woo-e2e/no_op', 'normalizer-' );
		$this->assertNotFalse( $this->fixture_path );

		$fixture = [
			[
				'ctrf_json' => [
					'results' => [
						'tests' => [
							[
								'name'  => 'wp plugin activate woocommerce',
								'extra' => [
									'output' => "Warning: Plugin 'woocommerce' is already active.\nSuccess: Plugin already activated.",
								],
							],
							[
								'name'  => 'can create a variable product',
								'stdout' => [ 'volatile output' ],
								'stderr' => [
									"(node:893) Warning: The 'NO_COLOR' env is ignored due to the 'FORCE_COLOR' env being set.\n(Use `node --trace-warnings ...` to show where the warning was created)\n",
									'Useful stderr',
								],
								'steps' => [
									[ 'name' => 'Type "Bespoke Steel Chicken" into the "Product name" input field.' ],
								],
							],
						],
					],
				],
			],
			[
				'debug_log' => [
					[
						'count'   => 1,
						'message' => 'PHP Fatal error: Maximum execution time of 30 seconds exceeded in /var/www/html/wp-includes/class-wp-hook.php on line 143',
					],
					[
						'count'   => 1,
						'message' => 'PHP Fatal error: Maximum execution time of 30 seconds exceeded in /var/www/html/wp-content/plugins/example/plugin.php on line 12',
					],
				],
			],
		];

		$this->assertNotFalse( file_put_contents( $this->fixture_path, json_encode( $fixture ) ) );

		$normalized = $this->validate_and_normalize( $this->fixture_path );

		$this->assertStringContainsString( 'Type \\"<PRODUCT_NAME>\\" into the \\"Product name\\" input field.', $normalized );
		$this->assertStringNotContainsString( 'Bespoke Steel Chicken', $normalized );
		$this->assertStringContainsString( '[IGNORED FOR WOO-E2E]', $normalized );
		$this->assertStringNotContainsString( 'NO_COLOR', $normalized );
		$this->assertStringContainsString( 'Useful stderr', $normalized );
		$this->assertStringContainsString( "Success: Plugin already activated.\\nWarning: Plugin 'woocommerce' is already active.", $normalized );
		$this->assertStringNotContainsString( 'wp-includes\\/class-wp-hook.php', $normalized );
		$this->assertStringContainsString( 'wp-content\\/plugins\\/example\\/plugin.php', $normalized );
	}

	public function test_ctrf_only_parser_split_succeeds() {
		$this->write_parsed_result_fixture( [
			'ctrf_json' => [
				'results' => [ 'tests' => [] ],
			],
		] );

		$normalized = $this->validate_and_normalize( $this->fixture_path );

		$this->assertStringContainsString( 'ctrf_json_extracted', $normalized );
		$this->assertStringContainsString( '"ctrf_json"', $normalized );
	}

	public function test_both_result_representations_blank_fails() {
		$this->write_parsed_result_fixture( [
			'test_result_json' => '   ',
			'ctrf_json'        => null,
		] );

		$this->expectException( \PHPUnit\Framework\AssertionFailedError::class );
		$this->expectExceptionMessage( 'at least one present, valid test_result_json or ctrf_json' );

		$this->validate_and_normalize( $this->fixture_path );
	}

	public function test_legacy_empty_array_with_blank_ctrf_succeeds() {
		$this->write_parsed_result_fixture( [
			'test_result_json' => [],
			'ctrf_json'        => '',
		] );

		$normalized = $this->validate_and_normalize( $this->fixture_path );

		$this->assertStringContainsString( 'test_result_json_extracted', $normalized );
		$this->assertStringContainsString( '"test_result_json": []', $normalized );
	}

	public function test_nonblank_malformed_alternate_still_fails() {
		$this->write_parsed_result_fixture( [
			'test_result_json' => '{malformed',
			'ctrf_json'        => [ 'results' => [ 'tests' => [] ] ],
		] );

		$this->expectException( \PHPUnit\Framework\AssertionFailedError::class );
		$this->expectExceptionMessage( 'Invalid value encountered for key "test_result_json"' );

		$this->validate_and_normalize( $this->fixture_path );
	}

	private function write_parsed_result_fixture( array $result ): void {
		$this->fixture_path = tempnam( sys_get_temp_dir(), 'qit-result-' );
		$this->assertNotFalse( $this->fixture_path );
		$this->assertNotFalse(
			file_put_contents( $this->fixture_path, \test_result_parser( json_encode( $result ) ) )
		);
	}
}
