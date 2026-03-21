<?php

namespace QIT_CLI\BreakingChanges\Extraction;

use PhpParser\ParserFactory;
use PhpParser\Parser;
use PhpParser\Node\Stmt;

class FileParser {
	private Parser $parser;

	public function __construct() {
		$this->parser = ( new ParserFactory() )->createForHostVersion();
	}

	/**
	 * Parse a PHP file and return its AST.
	 *
	 * @return Stmt[]|null AST nodes or null on parse error.
	 */
	public function parse( string $file_path ): ?array {
		if ( ! is_file( $file_path ) || ! is_readable( $file_path ) ) {
			return null;
		}

		$code = file_get_contents( $file_path );
		if ( $code === false ) {
			return null;
		}

		try {
			return $this->parser->parse( $code );
		} catch ( \PhpParser\Error $e ) {
			return null;
		}
	}

	/**
	 * Parse PHP code string and return its AST.
	 *
	 * @return Stmt[]|null AST nodes or null on parse error.
	 */
	public function parse_code( string $code ): ?array {
		try {
			return $this->parser->parse( $code );
		} catch ( \PhpParser\Error $e ) {
			return null;
		}
	}
}
