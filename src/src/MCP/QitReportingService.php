<?php

namespace QIT_CLI\MCP;

use QIT_CLI\Auth;
use QIT_CLI\Config;
use QIT_CLI\Environment\EnvironmentMonitor;
use QIT_CLI\RequestBuilder;
use function QIT_CLI\get_manager_url;

class QitReportingService {
	private EnvironmentMonitor $environment_monitor;
	private Auth $auth;

	public function __construct( EnvironmentMonitor $environment_monitor, Auth $auth ) {
		$this->environment_monitor = $environment_monitor;
		$this->auth                = $auth;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_run( int $test_run_id, bool $include_sensitive_urls = false ): array {
		$run = $this->fetch_run( $test_run_id );

		return $this->normalize_run( $run, $include_sensitive_urls );
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_results( int $test_run_id, string $format = 'auto' ): array {
		if ( ! in_array( $format, [ 'auto', 'ctrf', 'legacy' ], true ) ) {
			throw new McpToolException( 'Invalid results format.', [
				'format'  => $format,
				'allowed' => [ 'auto', 'ctrf', 'legacy' ],
			] );
		}

		$run = $this->fetch_run( $test_run_id );

		if ( $format !== 'legacy' && ! empty( $run['ctrf_json'] ) && is_array( $run['ctrf_json'] ) ) {
			return [
				'test_run_id' => $test_run_id,
				'source'      => 'ctrf_json',
				'results'     => $this->redact_value( $run['ctrf_json'], false ),
			];
		}

		if ( $format !== 'ctrf' && ! empty( $run['test_result_json'] ) && is_array( $run['test_result_json'] ) ) {
			return [
				'test_run_id' => $test_run_id,
				'source'      => 'test_result_json',
				'results'     => $this->redact_value( $run['test_result_json'], false ),
			];
		}

		throw new McpToolException( 'No test results are available for this run.', [
			'test_run_id' => $test_run_id,
			'format'      => $format,
			'status'      => $run['status'] ?? null,
		] );
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_failures( int $test_run_id, bool $include_debug_log = true, int $max_debug_log_lines = 100 ): array {
		if ( $max_debug_log_lines < 0 ) {
			throw new McpToolException( 'max_debug_log_lines must be zero or greater.' );
		}

		$run           = $this->fetch_run( $test_run_id );
		$failures      = $this->extract_failures( $run );
		$debug_signals = $include_debug_log ? $this->extract_debug_signals( $run['debug_log'] ?? null, $max_debug_log_lines ) : [];
		$next_steps    = $this->build_next_steps( $run, $failures, $debug_signals );
		$result_url    = isset( $run['test_results_manager_url'] ) ? $this->redact_url( (string) $run['test_results_manager_url'], false ) : null;

		return [
			'test_run_id'     => $test_run_id,
			'status'          => $run['status'] ?? null,
			'update_complete' => $run['update_complete'] ?? null,
			'test_type'       => $run['test_type'] ?? null,
			'summary'         => $run['test_summary'] ?? null,
			'result_url'      => $result_url,
			'failures'        => $this->redact_value( $failures, false ),
			'debug_signals'   => $this->redact_value( $debug_signals, false ),
			'next_steps'      => $next_steps,
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_last_local_run_context( bool $include_sensitive_urls = false ): array {
		$path = $this->last_run_path();

		if ( ! file_exists( $path ) ) {
			return [
				'found' => false,
				'path'  => $path,
			];
		}

		$data = $this->read_json_file( $path );

		return [
			'found'   => true,
			'path'    => $path,
			'context' => $this->redact_value( $data, $include_sensitive_urls ),
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	public function list_environments( ?string $env_id = null ): array {
		$running = $this->environment_monitor->get();

		if ( $env_id !== null ) {
			if ( ! isset( $running[ $env_id ] ) ) {
				throw new McpToolException( 'Environment not found.', [
					'env_id' => $env_id,
				] );
			}
			$running = [ $env_id => $running[ $env_id ] ];
		}

		return [
			'environments' => array_values( $this->redact_value( $running, false ) ),
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_artifacts( ?int $test_run_id, ?string $source, bool $include_sensitive_urls = false ): array {
		if ( $test_run_id !== null && $test_run_id > 0 ) {
			$source = 'manager_run';
		}

		if ( $source === null ) {
			$source = 'last_local_run';
		}

		if ( ! in_array( $source, [ 'last_local_run', 'manager_run' ], true ) ) {
			throw new McpToolException( 'Invalid artifact source.', [
				'source'  => $source,
				'allowed' => [ 'last_local_run', 'manager_run' ],
			] );
		}

		if ( $source === 'manager_run' ) {
			if ( $test_run_id === null || $test_run_id <= 0 ) {
				throw new McpToolException( 'test_run_id is required when source is manager_run.' );
			}

			$run = $this->fetch_run( $test_run_id );

			return [
				'source'      => 'manager_run',
				'test_run_id' => $test_run_id,
				'artifacts'   => $this->redact_value( $this->collect_run_artifacts( $run ), $include_sensitive_urls ),
			];
		}

		$context = $this->get_last_local_run_context( $include_sensitive_urls );

		if ( empty( $context['found'] ) ) {
			return [
				'source'    => 'last_local_run',
				'found'     => false,
				'artifacts' => [],
			];
		}

		$local_context = is_array( $context['context'] ) ? $context['context'] : [];

		return [
			'source'    => 'last_local_run',
			'found'     => true,
			'artifacts' => $this->collect_local_artifacts( $local_context ),
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	private function fetch_run( int $test_run_id ): array {
		if ( $test_run_id <= 0 ) {
			throw new McpToolException( 'test_run_id must be a positive integer.' );
		}

		try {
			$json = ( new RequestBuilder( get_manager_url() . '/wp-json/cd/v1/get-single' ) )
				->with_method( 'POST' )
				->with_post_body( [
					'test_run_id' => $test_run_id,
				] )
				->with_retry( 3 )
				->request();
		} catch ( \Throwable $e ) {
			throw new McpToolException( 'Unable to fetch QIT test run.', [
				'test_run_id' => $test_run_id,
				'message'     => $e->getMessage(),
			], 0, $e );
		}

		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			throw new McpToolException( 'Manager returned invalid JSON for test run.', [
				'test_run_id' => $test_run_id,
				'json_error'  => json_last_error_msg(),
			] );
		}

		foreach ( [ 'ctrf_json', 'test_result_json', 'debug_log' ] as $field ) {
			if ( array_key_exists( $field, $data ) ) {
				$data[ $field ] = $this->decode_json_value( $data[ $field ] );
			}
		}

		return $data;
	}

	/**
	 * @param array<string,mixed> $run
	 * @return array<string,mixed>
	 */
	private function normalize_run( array $run, bool $include_sensitive_urls ): array {
		return [
			'test_run_id'       => $run['test_run_id'] ?? $run['run_id'] ?? null,
			'run_id'            => $run['run_id'] ?? null,
			'status'            => $run['status'] ?? null,
			'update_complete'   => $run['update_complete'] ?? null,
			'test_type'         => $run['test_type'] ?? null,
			'test_type_display' => $run['test_type_display'] ?? null,
			'versions'          => [
				'wordpress'   => $run['wordpress_version'] ?? null,
				'woocommerce' => $run['woocommerce_version'] ?? null,
				'php'         => $run['php_version'] ?? null,
			],
			'extension'         => $this->redact_value( $run['woo_extension'] ?? null, $include_sensitive_urls ),
			'summary'           => $run['test_summary'] ?? null,
			'result_url'        => isset( $run['test_results_manager_url'] ) ? $this->redact_url( (string) $run['test_results_manager_url'], $include_sensitive_urls ) : null,
			'created_at'        => $run['created_at'] ?? null,
			'results'           => [
				'ctrf_json'        => $this->redact_value( $run['ctrf_json'] ?? null, $include_sensitive_urls ),
				'test_result_json' => $this->redact_value( $run['test_result_json'] ?? null, $include_sensitive_urls ),
				'debug_log'        => $this->redact_value( $run['debug_log'] ?? null, $include_sensitive_urls ),
			],
			'artifacts'         => $this->redact_value( $this->collect_run_artifacts( $run ), $include_sensitive_urls ),
		];
	}

	/**
	 * @param array<string,mixed> $run
	 * @return array<int,array<string,mixed>>
	 */
	private function extract_failures( array $run ): array {
		$failures = [];

		if ( ! empty( $run['ctrf_json'] ) && is_array( $run['ctrf_json'] ) ) {
			$failures = array_merge( $failures, $this->extract_ctrf_failures( $run['ctrf_json'] ) );
		}

		if ( empty( $failures ) && ! empty( $run['test_result_json'] ) && is_array( $run['test_result_json'] ) ) {
			$failures = array_merge( $failures, $this->extract_legacy_failures( $run['test_result_json'] ) );
		}

		return $failures;
	}

	/**
	 * @param array<string,mixed> $ctrf
	 * @return array<int,array<string,mixed>>
	 */
	private function extract_ctrf_failures( array $ctrf ): array {
		$tests = $ctrf['results']['tests'] ?? [];
		if ( ! is_array( $tests ) ) {
			return [];
		}

		$failures = [];
		foreach ( $tests as $test ) {
			if ( ! is_array( $test ) ) {
				continue;
			}

			$status = strtolower( (string) ( $test['status'] ?? '' ) );
			if ( ! in_array( $status, [ 'failed', 'failure', 'error', 'timedout', 'timed_out' ], true ) ) {
				continue;
			}

			$failures[] = [
				'source'   => 'ctrf',
				'name'     => $test['name'] ?? null,
				'status'   => $test['status'] ?? null,
				'message'  => $test['message'] ?? $test['reason'] ?? null,
				'trace'    => $test['trace'] ?? null,
				'file'     => $test['filePath'] ?? $test['file'] ?? null,
				'line'     => $test['line'] ?? null,
				'duration' => $test['duration'] ?? null,
			];
		}

		return $failures;
	}

	/**
	 * @param array<string,mixed> $results
	 * @return array<int,array<string,mixed>>
	 */
	private function extract_legacy_failures( array $results ): array {
		$failures = [];

		if ( isset( $results['files'] ) && is_array( $results['files'] ) ) {
			foreach ( $results['files'] as $file => $file_result ) {
				if ( ! is_array( $file_result ) || empty( $file_result['messages'] ) || ! is_array( $file_result['messages'] ) ) {
					continue;
				}

				foreach ( $file_result['messages'] as $message ) {
					if ( ! is_array( $message ) ) {
						continue;
					}

					$failures[] = [
						'source'   => 'legacy_files',
						'file'     => $file,
						'line'     => $message['line'] ?? null,
						'column'   => $message['column'] ?? null,
						'status'   => strtolower( (string) ( $message['type'] ?? 'warning' ) ),
						'message'  => $message['message'] ?? null,
						'rule'     => $message['source'] ?? null,
						'severity' => $message['severity'] ?? null,
					];
				}
			}
		}

		if ( isset( $results['testResults'] ) && is_array( $results['testResults'] ) ) {
			foreach ( $results['testResults'] as $result ) {
				if ( ! is_array( $result ) || empty( $result['tests'] ) || ! is_array( $result['tests'] ) ) {
					continue;
				}

				foreach ( $result['tests'] as $suite => $tests ) {
					if ( ! is_array( $tests ) ) {
						continue;
					}

					foreach ( $tests as $test ) {
						if ( ! is_array( $test ) ) {
							continue;
						}

						$status = strtolower( (string) ( $test['status'] ?? '' ) );
						if ( ! in_array( $status, [ 'failed', 'failure', 'error', 'timedout', 'timed_out' ], true ) ) {
							continue;
						}

						$failures[] = [
							'source'  => 'legacy_test_results',
							'suite'   => is_string( $suite ) ? $suite : null,
							'name'    => $test['title'] ?? $test['name'] ?? null,
							'status'  => $test['status'] ?? null,
							'message' => $test['message'] ?? $test['error'] ?? null,
							'trace'   => $test['trace'] ?? null,
						];
					}
				}
			}
		}

		return $failures;
	}

	/**
	 * @param mixed $debug_log
	 * @return array<string,mixed>
	 */
	private function extract_debug_signals( $debug_log, int $max_lines ): array {
		$lines = $this->debug_log_to_lines( $debug_log );

		if ( $max_lines === 0 ) {
			return [
				'total_lines'    => count( $lines ),
				'matching_lines' => 0,
				'lines'          => [],
			];
		}

		$hits = [];

		foreach ( $lines as $line ) {
			if ( preg_match( '/fatal error|parse error|warning|notice|deprecated|uncaught|exception/i', $line ) ) {
				$hits[] = $line;
			}
		}

		$matching_lines = count( $hits );

		if ( $max_lines > 0 && $matching_lines > $max_lines ) {
			$hits = array_slice( $hits, -1 * $max_lines );
		}

		return [
			'total_lines'    => count( $lines ),
			'matching_lines' => $matching_lines,
			'lines'          => $hits,
		];
	}

	/**
	 * @param mixed $debug_log
	 * @return array<int,string>
	 */
	private function debug_log_to_lines( $debug_log ): array {
		if ( empty( $debug_log ) ) {
			return [];
		}

		if ( is_array( $debug_log ) ) {
			$lines = [];
			foreach ( $debug_log as $key => $value ) {
				if ( $key === 'debug_log' ) {
					$lines = array_merge( $lines, $this->debug_log_to_lines( $value ) );
					continue;
				}
				if ( is_array( $value ) || is_object( $value ) ) {
					$encoded = json_encode( $value, JSON_UNESCAPED_SLASHES );
					if ( is_string( $encoded ) ) {
						$lines[] = $encoded;
					}
				} elseif ( is_scalar( $value ) ) {
					$lines[] = (string) $value;
				}
			}

			return array_values( array_filter( $lines, function ( string $line ): bool {
				return $line !== '';
			} ) );
		}

		if ( ! is_string( $debug_log ) ) {
			return [];
		}

		$decoded = json_decode( $debug_log, true );
		if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
			return $this->debug_log_to_lines( $decoded );
		}

		return preg_split( '/\r\n|\r|\n/', $debug_log ) ?: [];
	}

	/**
	 * @param array<string,mixed>            $run
	 * @param array<int,array<string,mixed>> $failures
	 * @param array<string,mixed>            $debug_signals
	 * @return array<int,string>
	 */
	private function build_next_steps( array $run, array $failures, array $debug_signals ): array {
		$steps = [];

		if ( ( $run['update_complete'] ?? null ) !== true ) {
			$steps[] = 'The run does not appear to be complete yet. Re-check the run after QIT finishes updating results.';
		}

		if ( ! empty( $failures ) ) {
			$steps[] = 'Start with the listed failed/error test entries; they are the most direct signals from the result payload.';
		}

		if ( ! empty( $debug_signals['lines'] ) ) {
			$steps[] = 'Inspect the debug log signals, especially fatal errors or uncaught exceptions, before chasing downstream test assertions.';
		}

		if ( ! empty( $run['test_results_manager_url'] ) ) {
			$steps[] = 'Open the QIT report URL for screenshots, traces, and full logs if the structured payload is not enough.';
		}

		if ( empty( $steps ) ) {
			$steps[] = 'No obvious failed tests or debug log signals were found in the available payload. Inspect the full report for runner-level issues or missing artifacts.';
		}

		return $steps;
	}

	/**
	 * @param array<string,mixed> $run
	 * @return array<int,array<string,mixed>>
	 */
	private function collect_run_artifacts( array $run ): array {
		$artifacts = [];

		foreach ( [
			'test_results_manager_url' => 'qit_report',
			'test_result_aws_url'      => 'remote_result',
		] as $field => $type ) {
			if ( ! empty( $run[ $field ] ) && is_string( $run[ $field ] ) ) {
				$artifacts[] = [
					'type'  => $type,
					'url'   => $run[ $field ],
					'field' => $field,
				];
			}
		}

		if ( ! empty( $run['test_media'] ) && is_array( $run['test_media'] ) ) {
			foreach ( $run['test_media'] as $media ) {
				$artifacts[] = [
					'type'  => 'test_media',
					'value' => $media,
				];
			}
		}

		return $artifacts;
	}

	/**
	 * @param array<string,mixed> $context
	 * @return array<int,array<string,mixed>>
	 */
	private function collect_local_artifacts( array $context ): array {
		$artifacts = [];

		if ( ! empty( $context['remote_report'] ) && is_string( $context['remote_report'] ) ) {
			$artifacts[] = [
				'type' => 'remote_report',
				'url'  => $context['remote_report'],
			];
		}

		if ( ! empty( $context['artifacts']['reports'] ) && is_array( $context['artifacts']['reports'] ) ) {
			foreach ( $context['artifacts']['reports'] as $report ) {
				if ( is_array( $report ) ) {
					$artifacts[] = $report;
				}
			}
		}

		return $artifacts;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	private function decode_json_value( $value ) {
		if ( ! is_string( $value ) || trim( $value ) === '' ) {
			return $value;
		}

		$decoded = json_decode( $value, true );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return $decoded;
		}

		$base64 = base64_decode( $value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- QIT result payloads may be gzipped/base64 encoded.
		if ( is_string( $base64 ) ) {
			$uncompressed = @gzuncompress( $base64 );
			if ( is_string( $uncompressed ) ) {
				$decoded = json_decode( $uncompressed, true );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					return $decoded;
				}
			}

			$decoded = json_decode( $base64, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				return $decoded;
			}
		}

		return $value;
	}

	private function last_run_path(): string {
		return rtrim( Config::get_qit_dir(), '/' ) . '/last-run.json';
	}

	/**
	 * @return array<string,mixed>
	 */
	private function read_json_file( string $path ): array {
		$contents = file_get_contents( $path );
		if ( $contents === false ) {
			throw new McpToolException( 'Unable to read JSON file.', [
				'path' => $path,
			] );
		}

		$data = json_decode( $contents, true );
		if ( ! is_array( $data ) ) {
			throw new McpToolException( 'JSON file is malformed.', [
				'path'       => $path,
				'json_error' => json_last_error_msg(),
			] );
		}

		return $data;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	private function redact_value( $value, bool $include_sensitive_urls ) {
		if ( is_array( $value ) ) {
			$redacted = [];
			foreach ( $value as $key => $child ) {
				if ( $this->is_sensitive_key( (string) $key ) ) {
					$redacted[ $key ] = '[REDACTED]';
					continue;
				}
				$redacted[ $key ] = $this->redact_value( $child, $include_sensitive_urls );
			}

			return $redacted;
		}

		if ( is_object( $value ) ) {
			if ( $value instanceof \JsonSerializable ) {
				return $this->redact_value( $value->jsonSerialize(), $include_sensitive_urls );
			}

			return $this->redact_value( get_object_vars( $value ), $include_sensitive_urls );
		}

		if ( is_string( $value ) ) {
			$value = $this->redact_known_secrets( $value );

			return $this->redact_urls_in_text( $value, $include_sensitive_urls );
		}

		return $value;
	}

	private function is_sensitive_key( string $key ): bool {
		return preg_match( '/secret|token|password|app_pass|manager_secret|partner_app_pass|authorization/i', $key ) === 1;
	}

	private function redact_known_secrets( string $value ): string {
		$secrets = [];

		$manager_secret = $this->auth->get_manager_secret();
		if ( is_string( $manager_secret ) && $manager_secret !== '' ) {
			$secrets[] = $manager_secret;
		}

		$partner_auth = $this->auth->get_partner_auth();
		if ( is_string( $partner_auth ) && $partner_auth !== '' ) {
			$secrets[] = $partner_auth;
			$decoded   = base64_decode( $partner_auth, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Partner auth is base64 user:token and must be redacted when present.
			if ( is_string( $decoded ) && $decoded !== '' ) {
				$secrets[] = $decoded;
			}
		}

		foreach ( $secrets as $secret ) {
			$value = str_replace( $secret, '[REDACTED]', $value );
			$value = str_replace( rawurlencode( $secret ), '[REDACTED]', $value );
		}

		return $value;
	}

	private function redact_urls_in_text( string $value, bool $include_sensitive_urls ): string {
		$redacted = preg_replace_callback(
			'#https?://\S+#i',
			function ( array $matches ) use ( $include_sensitive_urls ): string {
				return $this->redact_url( $matches[0], $include_sensitive_urls );
			},
			$value
		);

		return is_string( $redacted ) ? $redacted : $value;
	}

	private function redact_url( string $url, bool $include_sensitive_urls ): string {
		if ( $include_sensitive_urls ) {
			return $this->redact_known_secrets( $url );
		}

		$parts = parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return $this->redact_known_secrets( $url );
		}

		$path = $parts['path'] ?? '';
		if ( strpos( $path, '/results/' ) !== false ) {
			$path = preg_replace( '#/results/[^/]+#', '/results/[REDACTED]', $path, 1 ) ?? $path;
		}

		$redacted = $parts['scheme'] . '://' . $parts['host'];
		if ( isset( $parts['port'] ) ) {
			$redacted .= ':' . $parts['port'];
		}
		$redacted .= $path;

		if ( isset( $parts['query'] ) ) {
			$redacted .= '?[REDACTED]';
		}

		return $redacted;
	}
}
