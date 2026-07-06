<?php

namespace QIT_CLI\MCP;

class StdioTransport {
	private const JSON_ENCODE_FLAGS = JSON_UNESCAPED_SLASHES
		| JSON_INVALID_UTF8_SUBSTITUTE
		| JSON_PARTIAL_OUTPUT_ON_ERROR;

	/** @var resource */
	private $input;

	/** @var resource */
	private $output;

	/** @var resource */
	private $error_output;

	/**
	 * @param resource|null $input
	 * @param resource|null $output
	 * @param resource|null $error_output
	 */
	public function __construct( $input = null, $output = null, $error_output = null ) {
		$this->input        = $input ?? STDIN;
		$this->output       = $output ?? STDOUT;
		$this->error_output = $error_output ?? STDERR;
	}

	public function run( McpServer $server ): int {
		while ( ! feof( $this->input ) ) {
			$line = fgets( $this->input );

			if ( $line === false ) {
				break;
			}

			$line = trim( $line );
			if ( $line === '' ) {
				continue;
			}

			$message = json_decode( $line, true );
			if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $message ) ) {
				$this->write( $server->error_response( null, -32700, 'Parse error', [
					'json_error' => json_last_error_msg(),
				] ) );
				continue;
			}

			try {
				$response = $server->handle( $message );
				if ( is_array( $response ) ) {
					$this->write( $response );
				}
			} catch ( \Throwable $e ) {
				$this->write_error( $e->getMessage() );
				$this->write( $server->error_response( $message['id'] ?? null, -32603, 'Internal error', [
					'message' => $e->getMessage(),
				] ) );
			}

			if ( $server->should_exit() ) {
				break;
			}
		}

		return 0;
	}

	/**
	 * @param array<string,mixed> $message
	 */
	private function write( array $message ): void {
		$json = json_encode( $message, self::JSON_ENCODE_FLAGS );

		if ( ! is_string( $json ) ) {
			$this->write_error( sprintf( 'Failed to encode MCP response: %s', json_last_error_msg() ) );

			$id = $message['id'] ?? null;
			if ( ! is_int( $id ) && ! is_string( $id ) && $id !== null ) {
				$id = null;
			}

			$json = json_encode( [
				'jsonrpc' => '2.0',
				'id'      => $id,
				'error'   => [
					'code'    => -32603,
					'message' => 'Internal error',
					'data'    => [
						'message' => 'Failed to encode MCP response.',
					],
				],
			], self::JSON_ENCODE_FLAGS );
		}

		if ( ! is_string( $json ) ) {
			$json = '{"jsonrpc":"2.0","id":null,"error":{"code":-32603,"message":"Internal error"}}';
		}

		fwrite( $this->output, $json . "\n" );
	}

	private function write_error( string $message ): void {
		fwrite( $this->error_output, $message . "\n" );
	}
}
