<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use PhpParser\ParserFactory;

class ParsePhpTool extends BaseTool {
	public function getName(): string {
		return 'parse_php';
	}

	public function getDescription(): string {
		return 'Return a AST of a PHP file using nikic/PHP-Parser.';
	}

	function parse_php( string $file ): string {
		$result = $this->execute( [ 'file' => $file ] );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter( 'file', 'string', 'File to parse (relative) (required)' ),
		];

		return new FunctionInfo(
			$this->getName(),
			[$this, 'parse_php'],
			$this->getDescription(),
			$params,
			[ $params[0] ]              // pass a reference to the required parameters
		);
	}

	protected function do( array $p ) {
		$file = $this->safePath( $p['file'] ?? '' );

		if ( ! $file ) {
			throw new \InvalidArgumentException( 'File is required' );
		}

		$path = $this->file_path_resolver->toAbsolute( $file );

		if ( ! file_exists( $path ) ) {
			throw new \InvalidArgumentException( "File does not exist: {$path}" );
		}

		$code   = file_get_contents( $path );
		$parser = ( new ParserFactory() )->createForNewestSupportedVersion();
		try {
			$ast = $parser->parse( $code );
		} catch ( Error $error ) {
			echo "Parse error: {$error->getMessage()}\n";

			return '';
		}

		return $ast;
	}
}
