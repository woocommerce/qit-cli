<?php

// Register our 'qit_json' filter.
if ( ! stream_filter_register( 'qit_json', \QIT_JSON_Filter::class ) ) {
	exit( 151 );
}

/**
 * Stream filter that only passes valid JSON, collecting non-JSON for error reporting.
 */
class QIT_JSON_Filter extends \php_user_filter {
	/** @var string */
	private static $non_json_buffer = '';
	/** @var bool */
	private static $has_json_output = false;
	/** @var bool */
	private static $initialized = false;

	public function onCreate(): bool {
		if ( ! self::$initialized ) {
			self::$initialized = true;
			register_shutdown_function( [ self::class, 'handle_shutdown' ] );
		}

		return true;
	}

	public function filter( $in, $out, &$consumed, $closing ): int {
		while ( $bucket = stream_bucket_make_writeable( $in ) ) { //phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			if ( null !== json_decode( $bucket->data ) ) {
				// Data is valid JSON, pass it on.
				$consumed += $bucket->datalen;
				stream_bucket_append( $out, $bucket );
				self::$has_json_output = true;
			} else {
				// Not JSON - buffer it
				self::$non_json_buffer .= $bucket->data;
				$consumed              += $bucket->datalen;
			}

			// Optionally log all output, including JSONs and non-jsons to a file.
			if ( ! empty( getenv( 'QIT_NON_JSON_OUTPUT' ) ) ) {
				file_put_contents( getenv( 'QIT_NON_JSON_OUTPUT' ), $bucket->data, FILE_APPEND );
			}
		}

		// No special handling here - we'll handle it in shutdown

		return PSFS_PASS_ON;
	}

	public static function handle_shutdown(): void {
		// Only output error JSON if:
		// 1. We have buffered non-JSON content AND
		// 2. We haven't seen any valid JSON output (indicating a complete failure)
		if ( ! empty( trim( self::$non_json_buffer ) ) && ! self::$has_json_output ) {
			// No JSON output but have non-JSON - this is likely an error
			echo json_encode( [
				'error'  => 'Command failed with non-JSON output',
				'output' => trim( self::$non_json_buffer ),
			] ) . "\n";
		}

		// Reset for next use
		self::$non_json_buffer = '';
		self::$has_json_output = false;
	}
}
