<?php

// In ToolRegistry.php

namespace QIT_AI_Webserver;

use Exception;
use QIT_AI_Webserver\Tools\BaseTool;
use QIT_AI_Webserver\Tools\ParsePhpTool;
use QIT_AI_Webserver\Tools\ReadFileTool;
use QIT_AI_Webserver\Tools\ListFilesTool;
use QIT_AI_Webserver\Tools\SearchStringsTool;
use QIT_AI_Webserver\Tools\FindHooksTool;
use QIT_AI_Webserver\Tools\ListFactsTool;
use QIT_AI_Webserver\Tools\SearchFactsTool;
use QIT_AI_Webserver\Tools\TreeDirectoryTool;
use QIT_AI_Webserver\ToolContext;
use QIT_AI_Webserver\PathContextProvider;

class ToolRegistry {
	private array $tools = [];
	private string $workDirectory;
	private string $sutDirectory;

	public function __construct( string $work_directory = '', string $sut_directory = '' ) {
		if ( empty( $work_directory ) ) {
			throw new Exception( 'Work directory must be specified' );
		}

		$this->workDirectory = rtrim( $work_directory, '/\\' );
		$this->sutDirectory = rtrim( $sut_directory, '/\\' );

		if ( ! is_dir( $this->workDirectory ) ) {
			throw new Exception( "Work directory does not exist: {$this->workDirectory}" );
		}

		$this->register_default_tools();
	}

	private function register_default_tools(): void {
		// ❶ Get path context using PathContextProvider (not registered as a tool)
		$pathContextProvider = new PathContextProvider( $this->workDirectory, $this->sutDirectory );

		try {
			$ctxData = $pathContextProvider->getPathContext();
			$context = new ToolContext( $ctxData['wp_root'], $ctxData['sut'], $ctxData['deps'] );
		} catch ( \RuntimeException $e ) {
			// If path context fails, continue without context
			$context = null;
		}

		// ❂ Register tools – now **context‑aware** but PathContextTool is NOT registered
		$this->registerTool( new ReadFileTool( $this->workDirectory, $this->sutDirectory, $context ) );
		$this->registerTool( new ListFilesTool( $this->workDirectory, $this->sutDirectory, $context ) );
		$this->registerTool( new SearchStringsTool( $this->workDirectory, $this->sutDirectory, $context ) );
		$this->registerTool( new FindHooksTool( $this->workDirectory, $this->sutDirectory, $context ) );
		$this->registerTool( new ParsePhpTool( $this->workDirectory, $this->sutDirectory, $context ) );
		$this->registerTool( new ListFactsTool( $this->workDirectory, $this->sutDirectory, $context ) );
		$this->registerTool( new SearchFactsTool( $this->workDirectory, $this->sutDirectory, $context ) );
		$this->registerTool( new TreeDirectoryTool( $this->workDirectory, $this->sutDirectory, $context ) );
	}

	public function registerTool( BaseTool $tool ): void {
		$this->tools[ $tool->getName() ] = $tool;
	}

	public function getTool( string $name ): ?BaseTool {
		return $this->tools[ $name ] ?? null;
	}

	public function getTools(): array {
		return $this->tools;
	}

	public function execute_tool( string $name, array $params ): array {
		$tool = $this->getTool( $name );
		if ( ! $tool ) {
			return [ 'error' => "Unknown tool: $name" ];
		}

		try {
			return $tool->execute( $params );
		} catch ( Exception $e ) {
			return [ 'error' => $e->getMessage() ];
		}
	}
}
