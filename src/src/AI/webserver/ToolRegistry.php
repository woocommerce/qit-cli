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

/**
 * Registry for all AI tools
 */
class ToolRegistry {
	private ToolContext $context;
	/** @var array<string, \QIT_AI_Webserver\Tools\BaseTool> */
	private array $tools = [];

	public function __construct( ToolContext $context ) {
		$this->context = $context;
		$this->register_tools();
	}

	private function register_tools(): void {
		$base_path = dirname( __DIR__ ) . '/Tools';
		$work_dir  = $this->context->wpRoot;
		$sut_dir   = $this->context->sutDir;

		// Register all available tools
		$tool_classes = [
			'ListFilesTool',
			'ReadFileTool',
			'SearchStringsTool',
			'TreeDirectoryTool',
			'ParsePhpTool',
			'FindHooksTool',
			'ListFactsTool',
			'SearchFactsTool',
		];

		foreach ( $tool_classes as $class_name ) {
			$full_class = "\\QIT_AI_Webserver\\Tools\\{$class_name}";
			if ( class_exists( $full_class ) ) {
				$tool = new $full_class( $work_dir, $sut_dir, $this->context );
				$this->tools[ $tool->get_name() ] = $tool;
			}
		}

		// Add path context to the registry (not as a tool, but for context)
		$path_provider = new PathContextProvider( $work_dir, $sut_dir );
		$path_context  = $path_provider->get_path_context();
		
		// Store path context for use by tools if needed
		$this->context->path_context = $path_context;
	}

	public function register_tool( BaseTool $tool ): void {
		$this->tools[ $tool->get_name() ] = $tool;
	}

	public function get_tool( string $name ): ?BaseTool {
		return $this->tools[ $name ] ?? null;
	}

	/**
	 * Get all available tools
	 * @return array<string, \QIT_AI_Webserver\Tools\BaseTool>
	 */
	public function get_tools(): array {
		return $this->tools;
	}

	/**
	 * Get all available tools (camelCase alias)
	 * @return array<string, \QIT_AI_Webserver\Tools\BaseTool>
	 */
	public function getTools(): array {
		return $this->get_tools();
	}

	/**
	 * Get tool by name (camelCase alias)
	 */
	public function getTool( string $name ): ?BaseTool {
		return $this->get_tool( $name );
	}

	/**
	 * Execute a tool by name
	 * @param array<string, mixed> $params
	 * @return array<string, mixed>
	 */
	public function execute_tool( string $tool_name, array $params ): array {
		if ( ! isset( $this->tools[ $tool_name ] ) ) {
			throw new \InvalidArgumentException( "Tool not found: {$tool_name}" );
		}

		return $this->tools[ $tool_name ]->execute( $params );
	}
}
