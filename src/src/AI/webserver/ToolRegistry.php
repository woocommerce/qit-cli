<?php

// In ToolRegistry.php

namespace QIT_AI_Webserver;

use Exception;
use QIT_AI_Webserver\Tools\ToolInterface;
use QIT_AI_Webserver\Tools\ReadFileTool;
use QIT_AI_Webserver\Tools\ListFilesTool;
use QIT_AI_Webserver\Tools\SearchStringsTool;
use QIT_AI_Webserver\Tools\FindHooksTool;

class ToolRegistry {
	private array $tools = [];
	private string $workDirectory;

	public function __construct( string $work_directory = '' ) {
		if ( empty( $work_directory ) ) {
			throw new Exception( 'Work directory must be specified' );
		}

		$this->workDirectory = rtrim( $work_directory, '/\\' );

		if ( ! is_dir( $this->workDirectory ) ) {
			throw new Exception( "Work directory does not exist: {$this->workDirectory}" );
		}

		$this->register_default_tools();
	}

	private function register_default_tools(): void {
		// Create tool instances
		$this->registerTool( new ReadFileTool( $this->workDirectory ) );
		$this->registerTool( new ListFilesTool( $this->workDirectory ) );
		$this->registerTool( new SearchStringsTool( $this->workDirectory ) );
		$this->registerTool( new FindHooksTool( $this->workDirectory ) );
	}

	public function registerTool( ToolInterface $tool ): void {
		$this->tools[ $tool->getName() ] = $tool;
	}

	public function getTool( string $name ): ?ToolInterface {
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
