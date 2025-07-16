<?php

namespace QIT_AI_Webserver\Lib;

use Exception;

class OutboundRequest {
	/** @var array<string, mixed> */
	private array $data;

	/** @var array<string, mixed> */
	private array $config;

	private string $url;
	private string $method;
	private string $type;

	/** @var array<string, mixed> */
	private array $default_config = [
		'timeout'          => 30,
		'max_retries'      => 3,
		'retry_delay'      => 1,
		'validate_ssl'     => true,
		'follow_redirects' => true,
	];

	private JsonSchemaValidator $validator;

	/**
	 * @param array<string, mixed> $data
	 * @param array<string, mixed> $config
	 */
	public function __construct( string $url, string $method, array $data = [], array $config = [], string $type = 'request' ) {
		$this->url       = $url;
		$this->method    = strtoupper( $method );
		$this->data      = $data;
		$this->config    = array_merge( $this->default_config, $config );
		$this->type      = $type;
		$this->validator = JsonSchemaValidator::getInstance();
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public static function callback( string $url, array $data = [] ): array {
		$request = new self( $url, 'POST', $data, [], 'callback' );

		return $request->send();
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public static function task_event( string $url, array $data = [] ): array {
		$request = new self( $url, 'POST', $data, [], 'task_event' );

		return $request->send();
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public static function heartbeat( string $url, array $data = [] ): array {
		$request = new self( $url, 'POST', $data, [], 'heartbeat' );

		return $request->send();
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public static function node_management( string $url, array $data = [] ): array {
		$request = new self( $url, 'POST', $data );

		return $request->send();
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array<string, mixed>
	 */
	public static function node_registration( string $url, array $payload = [] ): array {
		$request = new self( $url, 'POST', $payload );

		return $request->send();
	}

	/**
	 * Send the request
	 *
	 * @return array<string, mixed>
	 */
	public function send(): array {
		try {
			// Validate outbound data
			$validation_result = $this->validate_schema( 'outbound' );
			if ( ! $validation_result['valid'] ) {
				return [
					'success' => false,
					'error'   => 'Validation failed: ' . implode( ', ', $validation_result['errors'] ),
				];
			}

			// Send with retry logic
			return $this->send_with_retry();
		} catch ( Exception $e ) {
			return [
				'success' => false,
				'error'   => $e->getMessage(),
			];
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	private function validate_schema( string $direction ): array {
		if ( $direction === 'outbound' ) {
			return $this->validator->validate_outbound( $this->data, $this->type );
		} else {
			return $this->validator->validate_inbound( $this->data, $this->type );
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	private function send_with_retry(): array {
		$max_retries = $this->config['max_retries'];
		$retry_delay = $this->config['retry_delay'];
		$last_error  = null;

		for ( $attempt = 0; $attempt <= $max_retries; $attempt++ ) {
			try {
				$result = $this->send_single_request();

				if ( $result['success'] ) {
					return $result;
				}

				$last_error = $result['error'] ?? 'Unknown error';
			} catch ( Exception $e ) {
				$last_error = $e->getMessage();
			}

			// Don't sleep after the last attempt
			if ( $attempt < $max_retries ) {
				sleep( $retry_delay );
			}
		}

		$result = [
			'success' => false,
			'error'   => $last_error,
		];

		return $result;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function send_single_request(): array {
		$context_options = [
			'http' => [
				'method'  => $this->method,
				'header'  => [
					'Content-Type: application/json',
					'Accept: application/json',
				],
				'content' => json_encode( $this->data ),
				'timeout' => $this->config['timeout'],
			],
			'ssl'  => [
				'verify_peer'      => $this->config['validate_ssl'],
				'verify_peer_name' => $this->config['validate_ssl'],
			],
		];

		$context = stream_context_create( $context_options );
		$result  = file_get_contents( $this->url, false, $context );

		if ( $result === false ) {
			throw new Exception( 'Failed to send request' );
		}

		$response_data = json_decode( $result, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new Exception( 'Invalid JSON response: ' . json_last_error_msg() );
		}

		// Validate response if it's an array
		if ( is_array( $response_data ) ) {
			$validation_result = $this->validator->validate_inbound( $response_data, $this->type );
			if ( ! $validation_result['valid'] ) {
				return [
					'success' => false,
					'error'   => 'Response validation failed: ' . implode( ', ', $validation_result['errors'] ),
				];
			}
		}

		$response = $response_data ?? $result;

		return [
			'success'  => true,
			'response' => $response,
			'url'      => $this->url,
			'method'   => $this->method,
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_data(): array {
		return $this->data;
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public function set_data( array $data ): void {
		$this->data = $data;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_config(): array {
		return $this->config;
	}

	/**
	 * @param array<string, mixed> $config
	 */
	public function set_config( array $config ): void {
		$this->config = array_merge( $this->config, $config );
	}
}
