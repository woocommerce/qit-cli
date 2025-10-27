<?php

namespace QIT_CLI\Utils;

use Symfony\Component\Console\Output\OutputInterface;

class PrepareQMLog {
	/** @var OutputInterface */
	protected $output;

	/**
	 * @var string $sut_slug
	 */
	protected $sut_slug = '';

	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * Sets the SUT slug.
	 *
	 * @param string $sut_slug
	 */
	public function set_sut_slug( string $sut_slug ): void {
		$this->sut_slug = $sut_slug;
	}

	/**
	 * Reads all JSON files in a directory and returns the data.
	 *
	 * @param string $directory
	 * @return array<string,mixed>
	 */
	public function read_json_data( string $directory ): array {
		$data = [];

		// Ensure the directory ends with a slash.
		if ( substr( $directory, -1 ) !== DIRECTORY_SEPARATOR ) {
			$directory .= DIRECTORY_SEPARATOR;
		}

		if ( is_dir( $directory ) ) {

			// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, Generic.CodeAnalysis.AssignmentInCondition.Found
			if ( $dh = opendir( $directory ) ) {

				while ( ( $file = readdir( $dh ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition

					$file_path = $directory . $file;

					if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'json' ) {

						$json_content = file_get_contents( $file_path );

						if ( $json_content ) {
							$data[ $file ] = json_decode( $json_content, true );
						}
					}
				}

				closedir( $dh );
			} else {
				$this->output->writeln( "Could not open directory: $directory" );
			}
		} else {
			$this->output->writeln( "Diectory does not exist: $directory" );
		}

		return $data;
	}

	/**
	 * Returns a summary of the trace data.
	 *
	 * @param array<mixed> $traces
	 * @return array<int, array<string,mixed>>
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
	 * @return array<string>
	 */
	public function extract_fatal_errors_from_debug_file( string $file_path ): array {
		$lines  = [];
		$handle = fopen( $file_path, 'r' );

		if ( $handle ) {

			while ( ( $line = fgets( $handle ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				if ( str_contains( $line, 'PHP Fatal error:' ) || str_contains( $line, 'PHP Parse error:' ) ) {
					$lines[] = $line;
				}
			}

			fclose( $handle );
		} else {
			$this->output->writeln( 'Error opening the file.' );
		}

		return $lines;
	}

	/**
	 * Formats the fatal error array.
	 *
	 * @param array<string> $lines
	 * @return array<int,array<string,string>>
	 */
	public function extract_error_info( array $lines ): array {
		$fatal_errors = [];
		foreach ( $lines as $line ) {
			// First pattern: `on line X`.
			preg_match(
				'/PHP (Fatal|Parse) error: (.*?) in (.*?) on line (\d+)/',
				$line,
				$matches1
			);

			if (
				isset( $matches1[1] ) &&
				isset( $matches1[2] ) &&
				isset( $matches1[3] ) &&
				isset( $matches1[4] )
			) {
				$fatal_errors[] = [
					'message' => $matches1[2],
					'file'    => str_replace( '/var/www/html/', '', $matches1[3] ),
					'line'    => $matches1[4],
				];
				continue;
			}

			// Second pattern: `in path:line`.
			preg_match(
				'/PHP (Fatal|Parse) error: (.*?) in (.*?):(\d+)/',
				$line,
				$matches2
			);

			if (
				isset( $matches2[1] ) &&
				isset( $matches2[2] ) &&
				isset( $matches2[3] ) &&
				isset( $matches2[4] )
			) {
				$fatal_errors[] = [
					'message' => $matches2[2],
					'file'    => str_replace( '/var/www/html/', '', $matches2[3] ),
					'line'    => $matches2[4],
				];
				continue;
			}

			$this->output->writeln( "Error parsing line: $line" );
		}

		return $fatal_errors;
	}

	/**
	 * Reads every json file in  the QM logs directory and summarizes the data.
	 *
	 * @param string $directory
	 * @phan-suppress PhanTypePossiblyInvalidDimOffset
	 * @return array<string,mixed>
	 */
	public function summarize_qm_logs( string $directory ): array {
		$data            = $this->read_json_data( $directory );
		$summarized_data = [];

		if ( empty( $data ) ) {
			return [];
		}

		foreach ( $data as $hash => $logs ) {
			foreach ( $logs as $type => $type_logs ) {
				/**
				 * @param array{message?: string, file?: string, line?: string} $info
				 */
				foreach ( $type_logs as $info ) {
					if ( empty( $info['message'] ) ) {
						continue;
					}

					// Ignore some compatbility extension set issues.
					$info['message'] = strip_tags( $info['message'] );

					// Ignore a deprecation warning from Jetpack about itself.
					$is_jetpack_geo_deprecation = stripos( $info['message'], 'Class Jetpack_Geo_Location is' ) !== false;

					if ( $is_jetpack_geo_deprecation ) {
						continue;
					}

					/*
					 * Notices coming from Query Monitor 3.17.0.
					 * @todo: Remove after upgrading QM to 3.17.2 or later.
					 * 3.17.2 has a bug, so we need to wait until 3.17.3 at least.
					 * @see https://github.com/Automattic/compatibility-dashboard/pull/1206#issuecomment-2636908814
					 * @see https://wordpress.org/support/topic/for-some-reason-fatal-error-stopped-being-logged-after-3-17-1-and-3-17-2/#new-topic-0
					 */
					if ( stripos( $info['message'], 'setted_site_transient is deprecated' ) !== false ) {
						continue;
					}
					if ( stripos( $info['message'], 'setted_transient is deprecated' ) !== false ) {
						continue;
					}

					// Hide "translation loading too early" for plugins that are not the SUT.
					if ( preg_match( '/translation loading for the (.*) domain was triggered too early/i', $info['message'], $matches ) && ! empty( $matches[1] ) ) {
						$plugin_slug = strtolower( $matches[1] );

						if ( $plugin_slug !== $this->sut_slug ) {
							continue;
						}
					}

					if ( ! empty( $info['file'] ) ) {
						// If coming from query-monitor and we are not testing QM, ignore it.
						if ( strpos( $info['file'], '/wp-content/plugins/query-monitor/' ) !== false && $this->sut_slug !== 'query-monitor' ) {
							continue;
						}
					}

					// End compatibility extension set issues.

					$file_and_line = '';

					if ( ! empty( $info['file'] ) ) {
						$file_and_line = str_replace( '/var/www/html/', '', $info['file'] );
					}

					if ( ! empty( $info['line'] ) ) {
						$file_and_line .= ':' . $info['line'];
					}

					if ( empty( $file_and_line ) ) {
						$file_and_line = '-';
					}

					$trace = $info['trace'] ?? [];

					if ( is_string( $trace ) ) {
						$trace = json_decode( $trace, true );
						$trace = is_array( $trace ) ? $trace : [];
					}

					$info_summary = [
						'message'   => $info['message'],
						'type'      => $type,
						'file_line' => $file_and_line,
						'traces'    => $this->get_trace_summary( $trace ),
					];

					// Ensures that we don't have duplicate entries.
					$md5_key = md5( $info['message'] . $type . $file_and_line );

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
	 * @return array<string,mixed>
	 */
	public function summarize_debug_logs( string $file_path ): array {
		if ( ! file_exists( $file_path ) ) {
			return [];
		}

		$this->output->writeln( 'Reading fatal errors from debug.log...' );
		$fatal_error_lines = $this->extract_fatal_errors_from_debug_file( $file_path );

		$this->output->writeln( 'Extracting error info...' );
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
	 * @return array<string,mixed>
	 */
	public function prepare_qm_logs( string $results_dir ): array {
		$qm_logs_path   = $results_dir . '/logs';
		$debug_log_path = $results_dir . '/debug.log';
		$debug_log      = $this->summarize_debug_logs( $debug_log_path );
		$qm_log         = $this->summarize_qm_logs( $qm_logs_path );

		if ( empty( $debug_log ) && empty( $qm_log ) ) {
			return [];
		}

		return [
			'non_fatal' => $qm_log,
			'fatal'     => $debug_log,
		];
	}
}
