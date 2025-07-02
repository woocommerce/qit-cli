<?php

// Lib/Tools/ToolInterface.php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;

interface ToolInterface {
	/**
	 * Get the tool name
	 */
	public function getName(): string;

	/**
	 * Get the tool description
	 */
	public function getDescription(): string;

	/**
	 * Get the FunctionInfo object for LLPhant
	 */
	public function getFunctionInfo(): FunctionInfo;

	/**
	 * Execute the tool with given parameters
	 */
	public function execute( array $params ): array;
}