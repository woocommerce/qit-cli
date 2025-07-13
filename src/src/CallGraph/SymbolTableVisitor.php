<?php

namespace CallGraph;

use PhpParser\{Node, NodeVisitorAbstract};

class SymbolTableVisitor extends NodeVisitorAbstract {
	private $file;
	private $symbols          = [];
	private $currentClass     = null;
	private $currentNamespace = null;

	public function __construct( string $file ) {
		$this->file = $file;
	}

	public function enterNode( Node $node ) {
		// Track namespace
		if ( $node instanceof Node\Stmt\Namespace_ ) {
			$this->currentNamespace = $node->name ? $node->name->toString() : null;
		}

		// Track class definitions
		if ( $node instanceof Node\Stmt\Class_ ) {
			$this->currentClass = $node->name->toString();
			$fullName           = $this->currentNamespace ? $this->currentNamespace . '\\' . $this->currentClass : $this->currentClass;

			$this->symbols[ $fullName ] = [
				'type'      => 'class',
				'name'      => $this->currentClass,
				'full_name' => $fullName,
				'file'      => $this->file,
				'line'      => $node->getStartLine(),
				'namespace' => $this->currentNamespace,
			];
		}

		// Track interface definitions
		if ( $node instanceof Node\Stmt\Interface_ ) {
			$interfaceName = $node->name->toString();
			$fullName      = $this->currentNamespace ? $this->currentNamespace . '\\' . $interfaceName : $interfaceName;

			$this->symbols[ $fullName ] = [
				'type'      => 'interface',
				'name'      => $interfaceName,
				'full_name' => $fullName,
				'file'      => $this->file,
				'line'      => $node->getStartLine(),
				'namespace' => $this->currentNamespace,
			];
		}

		// Track trait definitions
		if ( $node instanceof Node\Stmt\Trait_ ) {
			$traitName = $node->name->toString();
			$fullName  = $this->currentNamespace ? $this->currentNamespace . '\\' . $traitName : $traitName;

			$this->symbols[ $traitName ] = [
				'type'      => 'trait',
				'name'      => $traitName,
				'full_name' => $fullName,
				'file'      => $this->file,
				'line'      => $node->getStartLine(),
				'namespace' => $this->currentNamespace,
			];
		}

		// Track function definitions
		if ( $node instanceof Node\Stmt\Function_ ) {
			$functionName = $node->name->toString();
			$fullName     = $this->currentNamespace ? $this->currentNamespace . '\\' . $functionName : $functionName;

			$this->symbols[ $functionName ] = [
				'type'      => 'function',
				'name'      => $functionName,
				'full_name' => $fullName,
				'file'      => $this->file,
				'line'      => $node->getStartLine(),
				'namespace' => $this->currentNamespace,
				'params'    => $this->extractParameters( $node->params ),
			];
		}

		// Track method definitions
		if ( $node instanceof Node\Stmt\ClassMethod ) {
			$methodName = $node->name->toString();
			$fullName   = $this->currentClass ? $this->currentClass . '::' . $methodName : $methodName;

			$this->symbols[ $fullName ] = [
				'type'        => 'method',
				'name'        => $methodName,
				'full_name'   => $fullName,
				'class'       => $this->currentClass,
				'file'        => $this->file,
				'line'        => $node->getStartLine(),
				'namespace'   => $this->currentNamespace,
				'visibility'  => $this->getVisibility( $node ),
				'is_static'   => $node->isStatic(),
				'is_abstract' => $node->isAbstract(),
				'params'      => $this->extractParameters( $node->params ),
			];
		}

		// Track property definitions
		if ( $node instanceof Node\Stmt\Property ) {
			foreach ( $node->props as $prop ) {
				$propertyName = $prop->name->toString();
				$fullName     = $this->currentClass ? $this->currentClass . '::$' . $propertyName : '$' . $propertyName;

				$this->symbols[ $fullName ] = [
					'type'       => 'property',
					'name'       => $propertyName,
					'full_name'  => $fullName,
					'class'      => $this->currentClass,
					'file'       => $this->file,
					'line'       => $node->getStartLine(),
					'namespace'  => $this->currentNamespace,
					'visibility' => $this->getVisibility( $node ),
					'is_static'  => $node->isStatic(),
				];
			}
		}

		// Track constant definitions
		if ( $node instanceof Node\Stmt\ClassConst ) {
			foreach ( $node->consts as $const ) {
				$constName = $const->name->toString();
				$fullName  = $this->currentClass ? $this->currentClass . '::' . $constName : $constName;

				$this->symbols[ $fullName ] = [
					'type'       => 'constant',
					'name'       => $constName,
					'full_name'  => $fullName,
					'class'      => $this->currentClass,
					'file'       => $this->file,
					'line'       => $node->getStartLine(),
					'namespace'  => $this->currentNamespace,
					'visibility' => $this->getVisibility( $node ),
				];
			}
		}

		return null;
	}

	public function leaveNode( Node $node ) {
		// Reset current class when leaving class scope
		if ( $node instanceof Node\Stmt\Class_ ) {
			$this->currentClass = null;
		}

		// Reset namespace when leaving namespace scope
		if ( $node instanceof Node\Stmt\Namespace_ ) {
			$this->currentNamespace = null;
		}

		return null;
	}

	public function getSymbols(): array {
		return $this->symbols;
	}

	private function getVisibility( Node $node ): string {
		if ( $node->isPublic() ) {
			return 'public';
		} elseif ( $node->isProtected() ) {
			return 'protected';
		} elseif ( $node->isPrivate() ) {
			return 'private';
		}

		return 'public'; // Default visibility
	}

	private function extractParameters( array $params ): array {
		$parameters = [];

		foreach ( $params as $param ) {
			$paramInfo = [
				'name'         => $param->var->name,
				'type'         => null,
				'default'      => null,
				'is_variadic'  => $param->variadic,
				'is_reference' => $param->byRef,
			];

			// Extract type information
			if ( $param->type ) {
				if ( $param->type instanceof Node\Name ) {
					$paramInfo['type'] = $param->type->toString();
				} elseif ( $param->type instanceof Node\Identifier ) {
					$paramInfo['type'] = $param->type->name;
				}
			}

			// Extract default value (simplified)
			if ( $param->default ) {
				if ( $param->default instanceof Node\Scalar\String_ ) {
					$paramInfo['default'] = $param->default->value;
				} elseif ( $param->default instanceof Node\Scalar\LNumber ) {
					$paramInfo['default'] = $param->default->value;
				} elseif ( $param->default instanceof Node\Expr\ConstFetch ) {
					$paramInfo['default'] = $param->default->name->toString();
				}
			}

			$parameters[] = $paramInfo;
		}

		return $parameters;
	}
}
