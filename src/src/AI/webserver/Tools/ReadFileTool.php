<?php

namespace QIT_AI_Webserver\Tools;

use Exception;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use QIT_AI_Webserver\Lib\FilePathResolver;

class ReadFileTool implements ToolInterface {
	private string $workDirectory;
	private FilePathResolver $resolver;

	public function __construct( string $workDirectory ) {
		$this->workDirectory = rtrim( $workDirectory, '/\\' );
		$this->resolver      = new FilePathResolver( $this->workDirectory );
	}

	public function getName(): string {
		return 'read_file';
	}

	public function getDescription(): string {
		return 'Read contents of a file';
	}

	public function read_file( string $path, ?int $start_line = null, ?int $end_line = null ): string {
		// Re-use your internal logic
		$result = $this->execute( [
			'path'       => $path,
			'start_line' => $start_line,
			'end_line'   => $end_line,
		] );

		/* LLPhant is happiest when the tool returns a JSON-serialised
		   string, so keep the encoding here. */

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	public function getFunctionInfo(): FunctionInfo {

		return new FunctionInfo(
			$this->getName(),
			$this,
			$this->getDescription(),
			[
				new Parameter( 'start_line', 'int', 'Starting line number (optional)' ),
				new Parameter( 'end_line', 'int', 'Ending line number (optional)' )
			],
			[
				new Parameter( 'path', 'string', 'Path to the file (required)' ),
			]
		);
	}

	public function execute( array $params ): array {
		try {
			$relPath = $this->resolver->canonRelative( $params['path'] ?? '' );
		} catch (\Throwable $e) {
			return [ 'success' => false, 'error' => $e->getMessage() ];
		}
		$path = $relPath;
		$start_line = $params['start_line'] ?? null;
		$end_line   = $params['end_line'] ?? null;

		if ( ! $path ) {
			return [ 'error' => 'Path is required' ];
		}

		try {
			$content = $this->resolver->readFile( $path );
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
				'path'        => $path,
				'lines_read'  => [ $start_line ?? 1, $end_line ?? substr_count( $content, "\n" ) + 1 ],
				'total_lines' => count( $lines )
			];
		} catch ( Exception $e ) {
			// The resolver already logged, but bubble up context to the caller
			return [
				'error'         => $e->getMessage(),
				'attemptedPath' => $path,
				'workDir'       => $this->workDirectory,
			];
		}
	}

	public function __invoke( string $path, ?int $start_line = null, ?int $end_line = null ): string {
		$result = $this->execute( [
			'path'       => $path,
			'start_line' => $start_line,
			'end_line'   => $end_line
		] );

		return json_encode( $result );
	}
}
