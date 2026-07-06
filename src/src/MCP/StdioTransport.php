<?php

namespace QIT_CLI\MCP;

class StdioTransport {
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
		fwrite( $this->output, json_encode( $message, JSON_UNESCAPED_SLASHES ) . "\n" );
	}

	private function write_error( string $message ): void {
		fwrite( $this->error_output, $message . "\n" );
	}
}
