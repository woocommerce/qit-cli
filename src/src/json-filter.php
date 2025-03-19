<?php

// Register our 'qit_json' filter.
if ( ! stream_filter_register( 'qit_json', \QIT_JSON_Filter::class ) ) {
	exit( 151 );
}

/**
 * Stream filter that only passes valid JSON, discarding or logging anything else.
 */
class QIT_JSON_Filter extends \php_user_filter {
	public function filter( $in, $out, &$consumed, $closing ): int {
		while ( $bucket = stream_bucket_make_writeable( $in ) ) { //phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			if ( null !== json_decode( $bucket->data ) ) {
				// Data is valid JSON, pass it on.
				$consumed += $bucket->datalen;
				stream_bucket_append( $out, $bucket );
			} else {
				// Not valid JSON, optionally log to file.
				if ( ! empty( getenv( 'QIT_NON_JSON_OUTPUT' ) ) ) {
					file_put_contents( getenv( 'QIT_NON_JSON_OUTPUT' ), $bucket->data, FILE_APPEND );
				}
			}
		}

		return PSFS_PASS_ON;
	}
}
