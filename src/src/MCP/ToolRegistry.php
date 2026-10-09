<?php

namespace QIT_CLI\MCP;

class ToolRegistry {
	private QitReportingService $reporting;

	public function __construct( QitReportingService $reporting ) {
		$this->reporting = $reporting;
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function list_tools(): array {
		return [
			$this->tool( 'qit_get_run', 'Get normalized QIT test run metadata and decoded result fields.', [
				'test_run_id'            => [
					'type'    => 'integer',
					'minimum' => 1,
				],
				'include_sensitive_urls' => [
					'type'    => 'boolean',
					'default' => false,
				],
			], [ 'test_run_id' ] ),
			$this->tool( 'qit_get_results', 'Get CTRF results when available, otherwise legacy QIT result JSON.', [
				'test_run_id' => [
					'type'    => 'integer',
					'minimum' => 1,
				],
				'format'      => [
					'type'    => 'string',
					'enum'    => [ 'auto', 'ctrf', 'legacy' ],
					'default' => 'auto',
				],
			], [ 'test_run_id' ] ),
			$this->tool( 'qit_get_failures', 'Summarize failed/error tests, debug signals, and next inspection steps for a QIT run.', [
				'test_run_id'         => [
					'type'    => 'integer',
					'minimum' => 1,
				],
				'include_debug_log'   => [
					'type'    => 'boolean',
					'default' => true,
				],
				'max_debug_log_lines' => [
					'type'    => 'integer',
					'minimum' => 0,
					'maximum' => 1000,
					'default' => 100,
				],
			], [ 'test_run_id' ] ),
			$this->tool( 'qit_get_last_local_run_context', 'Read the last local QIT run context from last-run.json.', [
				'include_sensitive_urls' => [
					'type'    => 'boolean',
					'default' => false,
				],
			], [] ),
			$this->tool( 'qit_list_environments', 'List running local QIT environments.', [
				'env_id' => [
					'type' => 'string',
				],
			], [] ),
			$this->tool( 'qit_get_artifacts', 'List known QIT report URLs and local artifact paths for a run or the last local run.', [
				'test_run_id'            => [
					'type'    => 'integer',
					'minimum' => 1,
				],
				'source'                 => [
					'type' => 'string',
					'enum' => [ 'last_local_run', 'manager_run' ],
				],
				'include_sensitive_urls' => [
					'type'    => 'boolean',
					'default' => false,
				],
			], [] ),
		];
	}

	/**
	 * @param string              $name
	 * @param array<string,mixed> $arguments
	 * @return array<string,mixed>
	 */
	public function call( string $name, array $arguments ): array {
		switch ( $name ) {
			case 'qit_get_run':
				return $this->reporting->get_run(
					$this->required_int( $arguments, 'test_run_id' ),
					$this->bool_arg( $arguments, 'include_sensitive_urls', false )
				);
			case 'qit_get_results':
				return $this->reporting->get_results(
					$this->required_int( $arguments, 'test_run_id' ),
					$this->string_arg( $arguments, 'format', 'auto' )
				);
			case 'qit_get_failures':
				return $this->reporting->get_failures(
					$this->required_int( $arguments, 'test_run_id' ),
					$this->bool_arg( $arguments, 'include_debug_log', true ),
					$this->int_arg( $arguments, 'max_debug_log_lines', 100 )
				);
			case 'qit_get_last_local_run_context':
				return $this->reporting->get_last_local_run_context(
					$this->bool_arg( $arguments, 'include_sensitive_urls', false )
				);
			case 'qit_list_environments':
				return $this->reporting->list_environments(
					$this->nullable_string_arg( $arguments, 'env_id' )
				);
			case 'qit_get_artifacts':
				return $this->reporting->get_artifacts(
					isset( $arguments['test_run_id'] ) ? $this->int_arg( $arguments, 'test_run_id', 0 ) : null,
					$this->nullable_string_arg( $arguments, 'source' ),
					$this->bool_arg( $arguments, 'include_sensitive_urls', false )
				);
			default:
				throw new McpToolException( sprintf( 'Unknown tool "%s".', $name ) );
		}
	}

	/**
	 * @param string              $name
	 * @param string              $description
	 * @param array<string,mixed> $properties
	 * @param array<int,string>   $required
	 * @return array<string,mixed>
	 */
	private function tool( string $name, string $description, array $properties, array $required ): array {
		return [
			'name'        => $name,
			'description' => $description,
			'inputSchema' => [
				'type'                 => 'object',
				'properties'           => $properties,
				'required'             => $required,
				'additionalProperties' => false,
			],
		];
	}

	/**
	 * @param array<string,mixed> $arguments
	 */
	private function required_int( array $arguments, string $key ): int {
		if ( ! isset( $arguments[ $key ] ) ) {
			throw new McpToolException( sprintf( 'Missing required argument "%s".', $key ) );
		}

		return $this->int_arg( $arguments, $key, 0 );
	}

	/**
	 * @param array<string,mixed> $arguments
	 */
	private function int_arg( array $arguments, string $key, int $default_value ): int {
		if ( ! isset( $arguments[ $key ] ) ) {
			return $default_value;
		}

		if ( is_int( $arguments[ $key ] ) ) {
			return $arguments[ $key ];
		}

		if ( is_numeric( $arguments[ $key ] ) && intval( $arguments[ $key ] ) == $arguments[ $key ] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison,Universal.Operators.StrictComparisons.LooseEqual
			return (int) $arguments[ $key ];
		}

		throw new McpToolException( sprintf( 'Argument "%s" must be an integer.', $key ) );
	}

	/**
	 * @param array<string,mixed> $arguments
	 */
	private function bool_arg( array $arguments, string $key, bool $default_value ): bool {
		if ( ! isset( $arguments[ $key ] ) ) {
			return $default_value;
		}

		if ( is_bool( $arguments[ $key ] ) ) {
			return $arguments[ $key ];
		}

		throw new McpToolException( sprintf( 'Argument "%s" must be a boolean.', $key ) );
	}

	/**
	 * @param array<string,mixed> $arguments
	 */
	private function string_arg( array $arguments, string $key, string $default_value ): string {
		if ( ! isset( $arguments[ $key ] ) ) {
			return $default_value;
		}

		if ( is_string( $arguments[ $key ] ) ) {
			return $arguments[ $key ];
		}

		throw new McpToolException( sprintf( 'Argument "%s" must be a string.', $key ) );
	}

	/**
	 * @param array<string,mixed> $arguments
	 */
	private function nullable_string_arg( array $arguments, string $key ): ?string {
		if ( ! array_key_exists( $key, $arguments ) || $arguments[ $key ] === null || $arguments[ $key ] === '' ) {
			return null;
		}

		if ( is_string( $arguments[ $key ] ) ) {
			return $arguments[ $key ];
		}

		throw new McpToolException( sprintf( 'Argument "%s" must be a string.', $key ) );
	}
}
