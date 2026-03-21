<?php

namespace QIT_CLI_Tests\BreakingChanges\Extraction;

use PHPUnit\Framework\TestCase;
use QIT_CLI\BreakingChanges\Extraction\FileParser;

class FileParserTest extends TestCase {
	private FileParser $parser;

	protected function setUp(): void {
		parent::setUp();
		$this->parser = new FileParser();
	}

	public function test_parses_valid_php_file(): void {
		$fixture = __DIR__ . '/../fixtures/sample-plugin-v1/sample-plugin.php';
		$ast     = $this->parser->parse( $fixture );

		$this->assertNotNull( $ast );
		$this->assertIsArray( $ast );
		$this->assertNotEmpty( $ast );
	}

	public function test_returns_null_for_nonexistent_file(): void {
		$ast = $this->parser->parse( '/nonexistent/file.php' );

		$this->assertNull( $ast );
	}

	public function test_returns_null_for_invalid_php(): void {
		$tmp = tempnam( sys_get_temp_dir(), 'php_test' );
		file_put_contents( $tmp, '<?php class { invalid syntax' );

		try {
			$ast = $this->parser->parse( $tmp );
			$this->assertNull( $ast );
		} finally {
			unlink( $tmp );
		}
	}

	public function test_parse_code_with_valid_php(): void {
		$code = '<?php function foo() { return 42; }';
		$ast  = $this->parser->parse_code( $code );

		$this->assertNotNull( $ast );
		$this->assertIsArray( $ast );
	}

	public function test_parse_code_with_invalid_php(): void {
		$code = '<?php function { broken';
		$ast  = $this->parser->parse_code( $code );

		$this->assertNull( $ast );
	}

	public function test_parses_class_with_namespace(): void {
		$fixture = __DIR__ . '/../fixtures/sample-plugin-v1/includes/class-sample-manager.php';
		$ast     = $this->parser->parse( $fixture );

		$this->assertNotNull( $ast );
		$this->assertNotEmpty( $ast );
	}
}
