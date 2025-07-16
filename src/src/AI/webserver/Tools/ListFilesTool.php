<?php

namespace QIT_AI_Webserver\Tools;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\Parameter;
use QIT_AI_Webserver\Lib\DebugLogger;

class ListFilesTool extends BaseTool {

	public function get_name(): string {
		return 'list_files';
	}

	public function get_description(): string {
		return 'List files and directories';
	}

	public function list_files( string $directory = '.' ): string {
		$result = $this->execute( [ 'directory' => $directory ] );

		return json_encode( $result, JSON_UNESCAPED_SLASHES );
	}

	public function get_function_info(): FunctionInfo {
		$params = [
			new Parameter( 'directory', 'string', 'Directory to list (default: root)' ),
		];

		return new FunctionInfo(
			$this->get_name(),
			[ $this, 'list_files' ],
			$this->get_description(),
			$params,
			[]           // no required parameters
		);
	}

	protected function do( array $params ) {
		$directory = $this->safe_path( $params['directory'] ?? '.' );

		// Normalize directory path
		$relative_dir = $directory;
		if ( $relative_dir === '.' || $relative_dir === '' ) {
			$absolute_dir = $this->work_dir;
		} else {
			$absolute_dir = $this->file_path_resolver->to_absolute( $relative_dir );
		}

		// Verify directory is within bounds
		$real_work_dir = realpath( $this->work_dir );
		$real_dir      = realpath( $absolute_dir );

		if ( $real_dir === false || strpos( $real_dir, $real_work_dir ) !== 0 ) {
			DebugLogger::log( 'list_files_error', [
				'reason'       => 'directory_not_found_or_outside_bounds',
				'directory'    => $directory,
				'absolute_dir' => $absolute_dir,
				'work_dir'     => $this->work_dir,
				'dir_tree'     => DebugLogger::dir_tree( $this->work_dir ),
			] );

			throw new \InvalidArgumentException( 'Directory not found or outside bounds: ' . $directory );
		}

		if ( ! is_dir( $absolute_dir ) ) {
			DebugLogger::log( 'list_files_error', [
				'reason'       => 'not_a_directory',
				'directory'    => $directory,
				'absolute_dir' => $absolute_dir,
				'work_dir'     => $this->work_dir,
				'dir_tree'     => DebugLogger::dir_tree( dirname( $absolute_dir ) ),
			] );

			throw new \InvalidArgumentException( 'Directory not found: ' . $directory );
		}

		$files = [];
		$dirs  = [];

		$items = @scandir( $absolute_dir );
		if ( $items === false ) {
			DebugLogger::log( 'list_files_error', [
				'reason'       => 'cannot_read_directory',
				'directory'    => $directory,
				'absolute_dir' => $absolute_dir,
				'work_dir'     => $this->work_dir,
				'dir_tree'     => DebugLogger::dir_tree( dirname( $absolute_dir ) ),
			] );

			throw new \RuntimeException( 'Cannot read directory: ' . $directory );
		}

		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}

			$item_path     = $absolute_dir . '/' . $item;
			$relative_path = $this->file_path_resolver->to_relative( $item_path );

			if ( is_dir( $item_path ) ) {
				$dirs[] = $relative_path;
			} else {
				$files[] = [
					'path'      => $relative_path,
					'size'      => filesize( $item_path ),
					'extension' => pathinfo( $item, PATHINFO_EXTENSION ),
				];
			}
		}

		return [
			'directory'         => $relative_dir === '' ? '.' : $relative_dir,
			'files'             => $files,
			'directories'       => $dirs,
			'total_files'       => count( $files ),
			'total_directories' => count( $dirs ),
		];
	}

	public function __invoke( string $directory = '.' ): string {
		$result = $this->execute( [ 'directory' => $directory ] );

		return json_encode( $result );
	}
}
