<?php

namespace QIT_AI_Webserver\Lib;

/**
 * Centralized outbound request handler with built-in JSON schema validation
 *
 * Acts as an "exit point" for all outbound HTTP requests from the AI webserver,
 * ensuring consistent validation, logging, and error handling.
 */
class OutboundRequest {
	private string $url;
	private array $data;
	private string $schema_type;
	private array $config;

	// Default configuration
	private array $default_config = [
		'method'                 => 'POST',
		'content_type'           => 'application/json', // or 'application/x-www-form-urlencoded'
		'max_retries'            => 1,
		'retry_strategy'         => 'exponential', // 'exponential' or 'exponential_jitter'
		'additional_headers'     => [],
		'validate_schema'        => true,
		'log_requests'           => true,
		'retry_on_client_errors' => false, // Don't retry 4xx except 429
		'success_codes'          => [ 200, 201, 202 ],
	];

	/**
	 * Constructor
	 *
	 * @param string $url Target URL
	 * @param array  $data Request data
	 * @param string $schema_type Schema type for validation
	 * @param array  $config Configuration overrides
	 */
	public function __construct( string $url, array $data, string $schema_type, array $config = [] ) {
		$this->url                  = $url;
		$this->data                 = $data;
		$this->schema_type          = $schema_type;
		$this->config               = array_merge( $this->default_config, $config );
		$this->config['auth_token'] = getenv( 'QIT_NODE_TOKEN' );
		$this->config['node_id']    = getenv( 'QIT_NODE_ID' );
	}

	/**
	 * Create a callback request (form-encoded, X-Node-Token auth)
	 */
	public static function callback( string $url, array $data, string $schema_type ): self {
		return new self( $url, $data, $schema_type, [
			'content_type' => 'application/x-www-form-urlencoded',
			'max_retries'  => 3,
		] );
	}

	/**
	 * Create a task event request (JSON, Bearer auth, idempotency)
	 */
	public static function taskEvent( string $url, array $data, string $schema_type ): self {
		return new self( $url, $data, $schema_type, [
			'content_type'       => 'application/json',
			'max_retries'        => 5,
			'retry_strategy'     => 'exponential_jitter',
			'additional_headers' => [
				'Idempotency-Key' => self::generateIdempotencyKey(),
			],
		] );
	}

	/**
	 * Create a heartbeat request (JSON, no auth, no retries)
	 */
	public static function heartbeat( string $url, array $data, string $schema_type ): self {
		return new self( $url, $data, $schema_type, [
			'content_type' => 'application/json',
			'max_retries'  => 0, // Fire-and-forget
		] );
	}

	/**
	 * Create a node registration/unregistration request (JSON, no retries by default)
	 */
	public static function nodeManagement( string $url, array $data, string $schema_type ): self {
		return new self( $url, $data, $schema_type, [
			'content_type' => 'application/json',
			'max_retries'  => 3,
		] );
	}

	/**
	 * Create a node registration request (JSON, with retries)
	 */
	public static function nodeRegistration( string $url, array $payload ): self {
		return new self(
			$url,
			$payload,
			'node-registration',   // schema alias
			[
				'content_type' => 'application/json',
				'max_retries'  => 3,
				'method'       => 'POST',
			]
		);
	}

	/**
	 * Send the request
	 *
	 * @return array Result with 'success' boolean, 'status_code', 'response', 'error'
	 */
	public function send(): array {
		// Validate against schema if enabled
		if ( $this->config['validate_schema'] ) {
			$validation_result = $this->validateSchema();
			if ( ! $validation_result['valid'] ) {
				if ( $this->config['log_requests'] ) {
					log_info( 'Outbound request schema validation failed', [
						'url'         => $this->url,
						'schema_type' => $this->schema_type,
						'errors'      => $validation_result['errors'],
					] );
				}
				// Continue anyway for backward compatibility, but log the issue
			}
		}

		// Log outbound request
		if ( $this->config['log_requests'] ) {
			log_info( 'Outbound request', [
				'url'          => $this->url,
				'method'       => $this->config['method'],
				'schema_type'  => $this->schema_type,
				'content_type' => $this->config['content_type'],
				'max_retries'  => $this->config['max_retries'],
			] );
		}

		// Send with retry logic
		return $this->sendWithRetry();
	}

	/**
	 * Validate data against schema
	 */
	private function validateSchema(): array {
		$validator = JsonSchemaValidator::getInstance();

		return $validator->validateOutbound( $this->data, $this->schema_type );
	}

	/**
	 * Send request with retry logic
	 */
	private function sendWithRetry(): array {
		$attempt     = 0;
		$max_retries = $this->config['max_retries'];

		while ( $attempt <= $max_retries ) {
			$result = $this->sendSingleRequest( $attempt + 1 );

			// Success
			if ( $result['success'] ) {
				return $result;
			}

			// Don't retry if we've reached max attempts
			if ( $attempt >= $max_retries ) {
				break;
			}

			// Don't retry client errors (except 429 Too Many Requests)
			if ( ! $this->config['retry_on_client_errors'] &&
				$result['status_code'] >= 400 &&
				$result['status_code'] < 500 &&
				$result['status_code'] !== 429 ) {

				if ( $this->config['log_requests'] ) {
					log_info( 'Outbound request failed with client error, not retrying', [
						'url'         => $this->url,
						'status_code' => $result['status_code'],
						'response'    => $result['response'],
					] );
				}
				break;
			}

			// Apply backoff strategy
			$this->applyBackoff( $attempt );
			++$attempt;
		}

		// Log final failure
		if ( $this->config['log_requests'] ) {
			log_info( 'Outbound request failed after all retries', [
				'url'               => $this->url,
				'max_retries'       => $max_retries,
				'final_status_code' => $result['status_code'] ?? null,
				'final_error'       => $result['error'] ?? null,
			] );
		}

		return $result;
	}

	/**
	 * Send a single HTTP request
	 */
	private function sendSingleRequest( int $attempt ): array {
		try {
			$ch = curl_init( $this->url );

			// Basic curl options
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );

			// Method and data
			if ( $this->config['method'] === 'POST' ) {
				curl_setopt( $ch, CURLOPT_POST, true );

				if ( $this->config['content_type'] === 'application/json' ) {
					curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $this->data ) );
				} else {
					curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $this->data ) );
				}
			}

			// Headers
			$headers = [ 'Content-Type: ' . $this->config['content_type'] ];

			// Authentication
			if ( ! empty( $this->config['auth_token'] ) ) {
				$headers[] = 'X-Node-Token: ' . $this->config['auth_token'];
			}

			if ( ! empty( $this->config['node_id'] ) ) {
				$headers[] = 'X-Node-ID: ' . $this->config['node_id'];
			}

			// Additional headers
			foreach ( $this->config['additional_headers'] as $key => $value ) {
				$headers[] = $key . ': ' . $value;
			}

			// Verbose logging of the exact request being sent (headers + body).
			if ( $this->config['log_requests'] ) {
				$payload_preview = $this->config['content_type'] === 'application/json'
					? json_encode( $this->data )
					: http_build_query( $this->data );

				log_info( 'Outbound request payload', [
					'url'     => $this->url,
					'headers' => $headers,
					'body'    => substr( $payload_preview, 0, 1000 ), // avoid huge spam
				] );
			}

			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

			// Execute request
			$response    = curl_exec( $ch );
			$status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			$error       = curl_error( $ch );
			curl_close( $ch );

			// Log individual attempt
			if ( $this->config['log_requests'] ) {
				log_info( 'Outbound request attempt', [
					'url'           => $this->url,
					'attempt'       => $attempt,
					'status_code'   => $status_code,
					'response_size' => strlen( $response ?? '' ),
					'error'         => $error ?: null,
				] );
			}

			// Check if successful
			$success = in_array( $status_code, $this->config['success_codes'], true );

			return [
				'success'     => $success,
				'status_code' => $status_code,
				'response'    => $response,
				'error'       => $error ?: null,
				'attempt'     => $attempt,
			];

		} catch ( \Throwable $e ) {
			if ( $this->config['log_requests'] ) {
				log_info( 'Outbound request exception', [
					'url'       => $this->url,
					'attempt'   => $attempt,
					'exception' => $e->getMessage(),
				] );
			}

			return [
				'success'     => false,
				'status_code' => 0,
				'response'    => null,
				'error'       => $e->getMessage(),
				'attempt'     => $attempt,
			];
		}
	}

	/**
	 * Apply backoff strategy between retries
	 */
	private function applyBackoff( int $attempt ): void {
		if ( $this->config['retry_strategy'] === 'exponential_jitter' ) {
			// Exponential backoff with jitter (used by TaskEventPusher)
			$backoff = pow( 2, $attempt ) + rand( 0, 1000 ) / 1000;
			usleep( $backoff * 1000000 ); // Convert to microseconds
		} else {
			// Simple exponential backoff (used by CallbackSender)
			$backoff = pow( 2, $attempt );
			sleep( $backoff );
		}
	}

	/**
	 * Generate UUID v4 for idempotency keys
	 */
	private static function generateIdempotencyKey(): string {
		$data    = random_bytes( 16 );
		$data[6] = chr( ord( $data[6] ) & 0x0f | 0x40 );
		$data[8] = chr( ord( $data[8] ) & 0x3f | 0x80 );

		return vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split( bin2hex( $data ), 4 ) );
	}

	/**
	 * Get the URL for this request
	 */
	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * Get the data for this request
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * Get the schema type for this request
	 */
	public function getSchemaType(): string {
		return $this->schema_type;
	}

	/**
	 * Get the configuration for this request
	 */
	public function getConfig(): array {
		return $this->config;
	}
}
