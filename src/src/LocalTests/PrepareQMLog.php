<?php

namespace QIT_CLI\LocalTests;

class PrepareQMLog {

	/**
	 * Reads all JSON files in a directory and returns the data.
	 *
	 * @param string $directory
	 * @return array
	 */
	public function read_json_data( string $directory ): array {
		$data = [];

		// Ensure the directory ends with a slash.
		if ( substr( $directory, -1 ) !== DIRECTORY_SEPARATOR ) {
			$directory .= DIRECTORY_SEPARATOR;
		}

		// Check if the directory exists.
		if ( is_dir( $directory ) ) {
			$dh = opendir( $directory );

			if ( $dh ) {

				$file = readdir( $dh );

				while ( $file !== false ) {
					if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'json' ) {
						$file_path = $directory . $file;

						$json_content = file_get_contents( $file_path );

						if ( $json_content ) {
							$data[ $file ] = json_decode( $json_content, true );
						}
					}
				}

				closedir( $dh );
			} else {
				echo "Could not open directory: $directory"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		} else {
			echo "Directory does not exist: $directory"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $data;
	}

	/**
	 * Returns a summary of the trace data.
	 *
	 * @param array $traces
	 * @return array
	 */
	public function get_trace_summary( array $traces ): array {
		$summary = [];

		if ( empty( $traces ) ) {
			return $summary;
		}

		foreach ( $traces as $trace ) {
			$summary[] = [
				'file_line' => str_replace( '/var/www/html/', '', $trace['file'] ) . ':' . $trace['line'],
				'display'   => $trace['display'],
			];
		}

		return $summary;
	}

	/**
	 * Extracts fatal errors from the debug.log file.
	 *
	 * @param string $file_path
	 * @return array
	 */
	public function extract_fatal_errors_from_debug_file( string $file_path ): array {
		$lines  = [];
		$handle = fopen( $file_path, 'r' );
		if ( $handle ) {
			$line = fgets( $handle );

			while ( $line !== false ) {
				if ( str_contains( $line, 'PHP Fatal error:' ) ) {
					$lines[] = $line;
				}
			}
			fclose( $handle );
		} else {
			echo 'Error opening the file.';
		}

		return $lines;
	}

	/**
	 * Formats the fatal error array.
	 *
	 * @param array $lines
	 * @return array
	 */
	public function extract_error_info( array $lines ): array {
		$fatal_errors = [];
		foreach ( $lines as $line ) {
			preg_match( '/PHP Fatal error: (.*?) in (.*?) on line (\d+)/', $line, $matches1 );

			if (
				isset( $matches1[1] ) &&
				isset( $matches1[2] ) &&
				isset( $matches1[3] )
			) {
				$fatal_errors[] = [
					'message' => $matches1[1],
					'file'    => str_replace( '/var/www/html/', '', $matches1[2] ),
					'line'    => $matches1[3],
				];

				continue;
			}

			preg_match( '/PHP Fatal error: (.*?) in (.*?):(\d+)/', $line, $matches2 );

			if (
				isset( $matches2[1] ) &&
				isset( $matches2[2] ) &&
				isset( $matches2[3] )
			) {
				$fatal_errors[] = [
					'message' => $matches2[1],
					'file'    => str_replace( '/var/www/html/', '', $matches2[2] ),
					'line'    => $matches2[3],
				];

				continue;
			}

			echo "Error parsing line: $line\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $fatal_errors;
	}

	/**
	 * Reads every json file in  the QM logs directory and summarizes the data.
	 *
	 * @param string $directory
	 * @return array
	 */
	public function summarize_qm_logs( string $directory ): array {
		$data            = $this->read_json_data( $directory );
		$summarized_data = [];

		if ( empty( $data ) ) {
			return [];
		}

		foreach ( $data as $hash => $logs ) {
			foreach ( $logs as $type => $type_logs ) {
				foreach ( $type_logs as $info ) {
					$info_summary = [
						'message'   => $info['message'],
						'type'      => $type,
						'file_line' => str_replace( '/var/www/html/', '', $info['file'] ) . ':' . $info['line'],
						'traces'    => $this->get_trace_summary( $info['trace'] ),
					];

					// Ensures that we don't have duplicate entries.
					$md5_key = md5( $info['message'] . $type . $info['file'] . $info['line'] );

					if ( array_key_exists( $md5_key, $summarized_data ) ) {
						++$summarized_data[ $md5_key ]['count'];
					} else {
						$summarized_data[ $md5_key ]          = $info_summary;
						$summarized_data[ $md5_key ]['count'] = 1;
					}
				}
			}
		}

		return $summarized_data;
	}

	/**
	 * Returns only the fatal errors from the debug.log file.
	 *
	 * @param string $file_path
	 * @return array
	 */
	public function summarize_debug_logs( string $file_path ): array {
		if ( ! file_exists( $file_path ) ) {
			return [];
		}

		$fatal_error_lines = $this->extract_fatal_errors_from_debug_file( $file_path );
		$fatal_errors      = $this->extract_error_info( $fatal_error_lines );
		$summarized_errors = [];

		foreach ( $fatal_errors as $key => $error ) {
			$info_summary = [
				'message'   => $error['message'],
				'type'      => 'PHP Fatal',
				'file_line' => $error['file'] . ':' . $error['line'],
				'traces'    => [],
			];

			$md5_key = md5( $error['message'] . $info_summary['type'] . $error['file'] . $error['line'] );

			if ( array_key_exists( $md5_key, $summarized_errors ) ) {
				++$summarized_errors[ $md5_key ]['count'];
			} else {
				$summarized_errors[ $md5_key ]          = $info_summary;
				$summarized_errors[ $md5_key ]['count'] = 1;
			}
		}

		return $summarized_errors;
	}

	/**
	 * Returns a summary of the QM logs and debug logs.
	 *
	 * @param string $results_dir
	 * @return array[]
	 */
	public function prepare_qm_logs( string $results_dir ): array {
		$qm_logs_path = $results_dir . '/logs';
		$debug_log    = $results_dir . '/debug.log';
		$debug_log    = $this->summarize_debug_logs( $debug_log );
		$qm_log       = $this->summarize_qm_logs( $qm_logs_path );

		if ( ! empty( $debug_log ) && ! empty( $qm_log ) ) {
			return [];
		}

		return [
			'qm_logs' => [
				'non_fatal' => $qm_log,
				'fatal'     => $debug_log,
			],
		];
	}
}
