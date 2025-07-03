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

	function parse_php( string $path ): string {
		$result = $this->execute( [ 'path' => $path ] );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	public function getFunctionInfo(): FunctionInfo {
		return new FunctionInfo(
			$this->getName(),
			$this,
			$this->getDescription(),
			[
				new Parameter( 'path', 'string', 'File to parse (relative)' ),
			]
		);
	}

	protected function do( array $p ) {
		$code   = file_get_contents( $this->r->toAbsolute( $p['path'] ) );
		$parser = ( new ParserFactory )
			->create( ParserFactory::PREFER_PHP7 );
		$ast    = $parser->parse( $code );

		return $ast;
	}
}
