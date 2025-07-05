<?php

namespace QIT_AI_Webserver\Tools;

use Exception;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;

class ReadFileTool extends BaseTool {

	public function getName(): string {
		return 'read_file';
	}

	public function getDescription(): string {
		return 'Read contents of a file';
	}

	public function read_file( string $file, ?int $start_line = null, ?int $end_line = null ): string {
		// Re-use your internal logic
		$result = $this->execute( [
			'file'       => $file,
			'start_line' => $start_line,
			'end_line'   => $end_line,
		] );

		/* LLPhant is happiest when the tool returns a JSON-serialised
		   string, so keep the encoding here. */

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	public function getFunctionInfo(): FunctionInfo {
		$params = [
			new Parameter( 'file', 'string', 'Path to the file (required)' ),
			new Parameter( 'start_line', 'integer', 'Starting line number (optional)' ),
			new Parameter( 'end_line', 'integer', 'Ending line number (optional)' ),
		];

		return new FunctionInfo(
			$this->getName(),
			[ $this, 'read_file' ],
			$this->getDescription(),
			$params,
			[ $params[0] ]              // pass a reference to the required parameters
		);
	}

	protected function do( array $params ) {
		$file       = $this->safePath( $params['file'] ?? '' );
		$start_line = $params['start_line'] ?? null;
		$end_line   = $params['end_line'] ?? null;

		if ( ! $file ) {
			throw new \InvalidArgumentException( 'File is required' );
		}

		// Treat 0 values as null (no filtering)
		if ( $start_line === 0 ) {
			$start_line = null;
		}
		if ( $end_line === 0 ) {
			$end_line = null;
		}

		$content = $this->file_path_resolver->readFile( $file );
		$lines   = explode( "\n", $content );

		// Apply line filtering if specified
		if ( $start_line !== null || $end_line !== null ) {
			$start          = max( 0, ( $start_line ?? 1 ) - 1 );
			$end            = min( count( $lines ), $end_line ?? count( $lines ) );
			$selected_lines = array_slice( $lines, $start, $end - $start );
			$content        = implode( "\n", $selected_lines );
		}

		return [
			'content'     => $content,
			'path'        => $file,
			'lines_read'  => [ $start_line ?? 1, $end_line ?? substr_count( $content, "\n" ) + 1 ],
			'total_lines' => count( $lines )
		];
	}

	public function __invoke( string $file, ?int $start_line = null, ?int $end_line = null ): string {
		$result = $this->execute( [
			'file'       => $file,
			'start_line' => $start_line,
			'end_line'   => $end_line
		] );

		return json_encode( $result );
	}
}
