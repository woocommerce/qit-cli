<?php

namespace QIT_CLI\MCP;

class McpToolException extends \RuntimeException {
	/** @var array<string,mixed> */
	private array $details;

	/**
	 * @param string              $message
	 * @param array<string,mixed> $details
	 */
	public function __construct( string $message, array $details = [], int $code = 0, ?\Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
		$this->details = $details;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_details(): array {
		return $this->details;
	}
}
