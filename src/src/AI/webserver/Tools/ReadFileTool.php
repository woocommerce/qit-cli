<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class ReadFileTool extends BaseTool {

	public function get_name(): string {
		return 'read_file';
	}

	public function get_description(): string {
		return $this->base_description(
			'Read file contents with optional line range filtering',
			[
				'Read entire file: file="src/MyClass.php"',
				'Read specific lines: file="src/MyClass.php", start_line=10, end_line=50',
			]
		);
	}

	public function get_function_info(): FunctionInfo {
		$params = [
			new Parameter( 'file', 'string', 'File path to read' ),
			new Parameter( 'start_line', 'integer', 'Starting line number (1-based, optional)' ),
			new Parameter( 'end_line', 'integer', 'Ending line number (1-based, optional)' ),
		];

		return new FunctionInfo(
			$this->get_name(),
			[ $this, 'read_file_content' ],
			$this->get_description(),
			$params,
			[ 'file' ]       // required parameters
		);
	}

	public function read_file_content(
		string $file,
		?int $start_line = null,
		?int $end_line = null
	): string {
		$result = $this->execute( compact( 'file', 'start_line', 'end_line' ) );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * @param array<string, mixed> $params
	 * @return array<string, mixed>
	 */
	protected function do( array $params ) {
		$file       = $this->safe_path( $params['file'] );
		$start_line = $params['start_line'] ?? null;
		$end_line   = $params['end_line'] ?? null;

		$abs_path = $this->file_path_resolver->to_absolute( $file );

		if ( ! file_exists( $abs_path ) ) {
			throw new \InvalidArgumentException( "File does not exist: {$file}" );
		}

		if ( is_dir( $abs_path ) ) {
			throw new \InvalidArgumentException( "Path is a directory, not a file: {$file}" );
		}

		$content = file_get_contents( $abs_path );
		if ( $content === false ) {
			throw new \RuntimeException( "Failed to read file: {$file}" );
		}

		// If line range is specified, filter the content
		if ( $start_line !== null || $end_line !== null ) {
			$lines = explode( "\n", $content );
			$total_lines = count( $lines );

			$start = max( 1, $start_line ?? 1 ) - 1; // Convert to 0-based
			$end   = min( $total_lines, $end_line ?? $total_lines ) - 1; // Convert to 0-based

			if ( $start > $end || $start >= $total_lines ) {
				throw new \InvalidArgumentException( 'Invalid line range' );
			}

			$filtered_lines = array_slice( $lines, $start, $end - $start + 1 );
			$content = implode( "\n", $filtered_lines );

			return [
				'file'         => $file,
				'content'      => $content,
				'start_line'   => $start + 1,
				'end_line'     => $end + 1,
				'total_lines'  => $total_lines,
				'truncated'    => false,
			];
		}

		return [
			'file'       => $file,
			'content'    => $content,
			'total_lines' => substr_count( $content, "\n" ) + 1,
			'truncated'  => false,
		];
	}
}
