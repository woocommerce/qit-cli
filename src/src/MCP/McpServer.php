<?php

namespace QIT_CLI\MCP;

use QIT_CLI\App;

class McpServer {
	private ToolRegistry $tools;
	private bool $should_exit = false;

	public function __construct( ToolRegistry $tools ) {
		$this->tools = $tools;
	}

	/**
	 * @param array<string,mixed> $message
	 * @return array<string,mixed>|null
	 */
	public function handle( array $message ): ?array {
		$id              = $message['id'] ?? null;
		$has_id          = array_key_exists( 'id', $message );
		$method          = $message['method'] ?? null;
		$is_notification = ! $has_id;

		if ( $is_notification ) {
			if ( is_string( $method ) && $method !== '' ) {
				$this->handle_notification( $method );
			}

			return null;
		}

		if ( ! is_string( $method ) || $method === '' ) {
			return $this->error_response( $id, -32600, 'Invalid Request' );
		}

		switch ( $method ) {
			case 'initialize':
				return $this->success_response( $id, [
					'protocolVersion' => '2025-06-18',
					'capabilities'    => [
						'tools' => new \stdClass(),
					],
					'serverInfo'      => [
						'name'    => 'qit',
						'title'   => 'Quality Insights Toolkit',
						'version' => App::getVar( 'CLI_VERSION', 'dev' ),
					],
				] );
			case 'ping':
				return $this->success_response( $id, new \stdClass() );
			case 'tools/list':
				return $this->success_response( $id, [
					'tools' => $this->tools->list_tools(),
				] );
			case 'tools/call':
				return $this->handle_tool_call( $id, $message['params'] ?? [] );
			case 'shutdown':
				$this->should_exit = true;
				return $this->success_response( $id, null );
			default:
				return $this->error_response( $id, -32601, 'Method not found', [
					'method' => $method,
				] );
		}
	}

	public function should_exit(): bool {
		return $this->should_exit;
	}

	/**
	 * @param mixed $id
	 * @param mixed $result
	 * @return array<string,mixed>
	 */
	private function success_response( $id, $result ): array {
		return [
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => $result,
		];
	}

	/**
	 * @param mixed               $id
	 * @param array<string,mixed> $params
	 * @return array<string,mixed>
	 */
	private function handle_tool_call( $id, array $params ): array {
		$name      = $params['name'] ?? null;
		$arguments = $params['arguments'] ?? [];

		if ( ! is_string( $name ) || $name === '' ) {
			return $this->error_response( $id, -32602, 'Invalid params', [
				'message' => 'tools/call requires a string params.name.',
			] );
		}

		if ( ! is_array( $arguments ) ) {
			return $this->error_response( $id, -32602, 'Invalid params', [
				'message' => 'tools/call params.arguments must be an object.',
			] );
		}

		try {
			$result = $this->tools->call( $name, $arguments );

			return $this->success_response( $id, [
				'content'           => [
					[
						'type' => 'text',
						'text' => $this->encode_json_for_text( $result ),
					],
				],
				'structuredContent' => $result,
			] );
		} catch ( McpToolException $e ) {
			$payload = [
				'error'   => $e->getMessage(),
				'details' => $e->get_details(),
			];

			return $this->success_response( $id, [
				'isError' => true,
				'content' => [
					[
						'type' => 'text',
						'text' => $this->encode_json_for_text( $payload ),
					],
				],
			] );
		} catch ( \Throwable $e ) {
			$payload = [
				'error' => $e->getMessage(),
			];

			return $this->success_response( $id, [
				'isError' => true,
				'content' => [
					[
						'type' => 'text',
						'text' => $this->encode_json_for_text( $payload ),
					],
				],
			] );
		}
	}

	private function handle_notification( string $method ): void {
		if ( $method === 'exit' ) {
			$this->should_exit = true;
		}
	}

	/**
	 * @param mixed               $id
	 * @param int                 $code
	 * @param string              $message
	 * @param array<string,mixed> $data
	 * @return array<string,mixed>
	 */
	public function error_response( $id, int $code, string $message, array $data = [] ): array {
		$error = [
			'code'    => $code,
			'message' => $message,
		];

		if ( ! empty( $data ) ) {
			$error['data'] = $data;
		}

		return [
			'jsonrpc' => '2.0',
			'id'      => $id,
			'error'   => $error,
		];
	}

	/**
	 * @param mixed $value
	 */
	private function encode_json_for_text( $value ): string {
		$json = json_encode(
			$value,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR
		);

		return is_string( $json ) ? $json : '{}';
	}
}
