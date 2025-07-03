<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use QIT_AI_Webserver\Lib\FilePathResolver;
use QIT_AI_Webserver\Lib\DebugLogger;

class ListFilesTool implements ToolInterface {
	private string $workDirectory;
	private FilePathResolver $resolver;

	public function __construct( string $workDirectory ) {
		$this->workDirectory = rtrim( $workDirectory, '/\\' );
		$this->resolver      = new FilePathResolver( $this->workDirectory );
	}

	public function getName(): string {
		return 'list_files';
	}

	public function getDescription(): string {
		return 'List files and directories';
	}

	public function list_files(string $directory = '.'): string
	{
		$result = $this->execute(['directory' => $directory]);
		return json_encode($result, JSON_UNESCAPED_SLASHES);
	}

	public function getFunctionInfo(): FunctionInfo {
		$parameters = [
			new Parameter( 'directory', 'string', 'Directory to list (default: root)' )
		];

		return new FunctionInfo(
			$this->getName(),
			$this,
			$this->getDescription(),
			$parameters
		);
	}

	public function execute( array $params ): array {
		$directory = $params['directory'] ?? '.';

		// Normalize directory path
		$relativeDir = trim( $directory, '/' );
		if ( $relativeDir === '.' || $relativeDir === '' ) {
			$absoluteDir = $this->workDirectory;
		} else {
			$absoluteDir = $this->resolver->toAbsolute( $relativeDir );
		}

		// Verify directory is within bounds
		$realWorkDir = realpath( $this->workDirectory );
		$realDir     = realpath( $absoluteDir );

		if ( $realDir === false || strpos( $realDir, $realWorkDir ) !== 0 ) {
			DebugLogger::log('list_files_error', [
				'reason'        => 'directory_not_found_or_outside_bounds',
				'directory'     => $directory,
				'absolute_dir'  => $absoluteDir,
				'work_dir'      => $this->workDirectory,
				'dir_tree'      => DebugLogger::dirTree($this->workDirectory),
			]);
			return [
				'error' => 'Directory not found or outside bounds: ' . $directory,
				'attemptedPath' => $directory,
				'workDir' => $this->workDirectory,
			];
		}

		if ( ! is_dir( $absoluteDir ) ) {
			DebugLogger::log('list_files_error', [
				'reason'        => 'not_a_directory',
				'directory'     => $directory,
				'absolute_dir'  => $absoluteDir,
				'work_dir'      => $this->workDirectory,
				'dir_tree'      => DebugLogger::dirTree(dirname($absoluteDir)),
			]);
			return [
				'error' => 'Directory not found: ' . $directory,
				'attemptedPath' => $directory,
				'workDir' => $this->workDirectory,
			];
		}

		$files = [];
		$dirs  = [];

		$items = @scandir( $absoluteDir );
		if ( $items === false ) {
			DebugLogger::log('list_files_error', [
				'reason'        => 'cannot_read_directory',
				'directory'     => $directory,
				'absolute_dir'  => $absoluteDir,
				'work_dir'      => $this->workDirectory,
				'dir_tree'      => DebugLogger::dirTree(dirname($absoluteDir)),
			]);
			return [
				'error' => 'Cannot read directory: ' . $directory,
				'attemptedPath' => $directory,
				'workDir' => $this->workDirectory,
			];
		}

		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}

			$itemPath     = $absoluteDir . '/' . $item;
			$relativePath = $this->resolver->toRelative( $itemPath );

			if ( is_dir( $itemPath ) ) {
				$dirs[] = $relativePath;
			} else {
				$files[] = [
					'path'      => $relativePath,
					'size'      => filesize( $itemPath ),
					'extension' => pathinfo( $item, PATHINFO_EXTENSION )
				];
			}
		}

		return [
			'directory'         => $relativeDir === '' ? '.' : $relativeDir,
			'files'             => $files,
			'directories'       => $dirs,
			'total_files'       => count( $files ),
			'total_directories' => count( $dirs )
		];
	}

	public function __invoke( string $directory = '.' ): string {
		$result = $this->execute( [ 'directory' => $directory ] );

		return json_encode( $result );
	}
}
