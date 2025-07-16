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
	private string $work_directory;
	private string $sut_directory;

	public function __construct( string $work_directory = '', string $sut_directory = '' ) {
		if ( empty( $work_directory ) ) {
			throw new Exception( 'Work directory must be specified' );
		}

		$this->work_directory = rtrim( $work_directory, '/\\' );
		$this->sut_directory  = rtrim( $sut_directory, '/\\' );

		if ( ! is_dir( $this->work_directory ) ) {
			throw new Exception( "Work directory does not exist: {$this->work_directory}" );
		}

		$this->register_default_tools();
	}

	private function register_default_tools(): void {
		// ❶ Get path context using PathContextProvider (not registered as a tool)
		$path_context_provider = new PathContextProvider( $this->work_directory, $this->sut_directory );

		try {
			$ctx_data = $path_context_provider->getPathContext();
			$context  = new ToolContext( $ctx_data['wp_root'], $ctx_data['sut'], $ctx_data['deps'] );
		} catch ( \RuntimeException $e ) {
			// If path context fails, continue without context
			$context = null;
		}

		// ❂ Register tools – now **context‑aware** but PathContextTool is NOT registered
		$this->register_tool( new ReadFileTool( $this->work_directory, $this->sut_directory, $context ) );
		$this->register_tool( new ListFilesTool( $this->work_directory, $this->sut_directory, $context ) );
		$this->register_tool( new SearchStringsTool( $this->work_directory, $this->sut_directory, $context ) );
		$this->register_tool( new FindHooksTool( $this->work_directory, $this->sut_directory, $context ) );
		$this->register_tool( new ParsePhpTool( $this->work_directory, $this->sut_directory, $context ) );
		$this->register_tool( new ListFactsTool( $this->work_directory, $this->sut_directory, $context ) );
		$this->register_tool( new TreeDirectoryTool( $this->work_directory, $this->sut_directory, $context ) );
		// Deep investigation only, disabled.
		// $this->register_tool( new SearchFactsTool( $this->work_directory, $this->sut_directory, $context ) );
	}

	public function register_tool( BaseTool $tool ): void {
		$this->tools[ $tool->get_name() ] = $tool;
	}

	public function get_tool( string $name ): ?BaseTool {
		return $this->tools[ $name ] ?? null;
	}

	public function get_tools(): array {
		return $this->tools;
	}

	public function execute_tool( string $name, array $params ): array {
		$tool = $this->get_tool( $name );
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
