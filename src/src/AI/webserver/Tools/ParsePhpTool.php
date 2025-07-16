<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class ParsePhpTool extends BaseTool {

	public function get_name(): string {
		return 'parse_php';
	}

	public function get_description(): string {
		return 'Parse PHP file and extract AST information';
	}

	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter( 'file', 'string', 'PHP file to parse' ),
		];

		return new FunctionInfo(
			$this->get_name(),
			[ $this, 'parse_file' ],
			$this->get_description(),
			$params,
			[ 'file' ]       // required parameters
		);
	}

	public function get_function_info(): FunctionInfo {
		return $this->getFunctionInfo();
	}

	public function parse_file( string $file ): string {
		$result = $this->execute( compact( 'file' ) );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * @param array<string, mixed> $p
	 * @return array<string, mixed>
	 */
	protected function do( array $p ) {
		$file = $this->safe_path( $p['file'] );

		if ( ! file_exists( $file ) ) {
			throw new \InvalidArgumentException( "File does not exist: {$file}" );
		}

		$abs_path = $this->file_path_resolver->to_absolute( $file );

		try {
			$parser_factory = new \PhpParser\ParserFactory();
			$parser         = $parser_factory->create( \PhpParser\ParserFactory::PREFER_PHP7 );
			$code           = file_get_contents( $abs_path );
			$ast            = $parser->parse( $code );

			if ( ! $ast ) {
				throw new \RuntimeException( 'Failed to parse PHP file' );
			}

			// Extract useful information from AST
			$visitor   = new \PhpParser\NodeVisitor\NameResolver();
			$traverser = new \PhpParser\NodeTraverser();
			$traverser->addVisitor( $visitor );
			$ast = $traverser->traverse( $ast );

			$info = $this->extract_ast_info( $ast );

			return [
				'file'     => $file,
				'ast_info' => $info,
				'success'  => true,
			];
		} catch ( \Exception $e ) {
			throw new \RuntimeException( 'Error parsing PHP file: ' . $e->getMessage() );
		}
	}

	/**
	 * @param array<\PhpParser\Node> $ast
	 * @return array<string, mixed>
	 */
	private function extract_ast_info( array $ast ): array {
		$info = [
			'classes'    => [],
			'functions'  => [],
			'namespaces' => [],
			'uses'       => [],
		];

		$visitor = new class( $info ) extends \PhpParser\NodeVisitorAbstract {
			/** @var array<string, mixed> */
			private $info;

			/**
			 * @param array<string, mixed> $info
			 */
			public function __construct( &$info ) {
				$this->info = &$info;
			}

			public function enterNode( \PhpParser\Node $node ) {
				if ( $node instanceof \PhpParser\Node\Stmt\Class_ ) {
					$this->info['classes'][] = [
						'name' => $node->name ? $node->name->toString() : '<anonymous>',
						'line' => $node->getLine(),
					];
				} elseif ( $node instanceof \PhpParser\Node\Stmt\Function_ ) {
					$this->info['functions'][] = [
						'name' => $node->name->toString(),
						'line' => $node->getLine(),
					];
				} elseif ( $node instanceof \PhpParser\Node\Stmt\Namespace_ ) {
					$this->info['namespaces'][] = [
						'name' => $node->name ? $node->name->toString() : '',
						'line' => $node->getLine(),
					];
				} elseif ( $node instanceof \PhpParser\Node\Stmt\Use_ ) {
					foreach ( $node->uses as $use ) {
						$this->info['uses'][] = [
							'name'  => $use->name->toString(),
							'alias' => $use->alias ? $use->alias->toString() : null,
							'line'  => $node->getLine(),
						];
					}
				}
				return null;
			}
		};

		$traverser = new \PhpParser\NodeTraverser();
		$traverser->addVisitor( $visitor );
		$traverser->traverse( $ast );

		return $info;
	}
}
