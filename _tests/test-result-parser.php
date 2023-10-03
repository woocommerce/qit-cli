<?php

function test_result_parser( string $json, string $remove_from_snapshot = '' ): string {
	if ( ! empty( $remove_from_snapshot ) ) {
		$remove_from_snapshot = array_map( 'trim', explode( ',', $remove_from_snapshot ) );
	} else {
		$remove_from_snapshot = [];
	}

	// Decode the original JSON
	$data = json_decode( $json, true );

	// Initialize an array to store the output JSONs
	$human_friendly_test_result = [];

	// Iterate over the top-level items and extract nested JSON strings
	foreach ( $data as $key => $value ) {
		if ( in_array( $key, $remove_from_snapshot ) ) {
			unset( $data[ $key ] );
			continue;
		}
		if ( is_string( $value ) ) {
			$decodedValue = json_decode( $value, true );
			if ( json_last_error() == JSON_ERROR_NONE && is_array( $decodedValue ) ) {
				// Add the extracted item as a separate top-level item in the output array
				$human_friendly_test_result[] = [ $key => $decodedValue ];
				// Replace the original value with an indicator that the JSON has been extracted
				$data["{$key}_extracted"] = '{EXTRACTED}';

				unset( $data[ $key ] );
			}
		}
	}

	// Add the modified original JSON as the first item in the output array
	array_unshift( $human_friendly_test_result, $data );

	/*
	 * Normalize PHP debug logs captured during test runs.
	 *
	 * The normalization process is focused on the 'count' key within the debug logs.
	 * This allows for some flexibility in test runs where slight variations in log counts
	 * might occur due to uncontrollable conditions like AJAX requests firing or not firing.
	 *
	 * Normalization rules for 'count':
	 * - Exact values are retained for counts below 50.
	 * - Counts between 50 and 100 are rounded to the nearest 5.
	 * - Counts above 100 are rounded to the nearest 10.
	 *
	 * Additionally, certain known failure messages (e.g., WordPress.org connectivity issues)
	 * are conditionally removed from the logs.
	 */
	foreach ( $human_friendly_test_result as $k => &$v ) {
		foreach ( $v as $key => &$value ) {
			if ( $key === 'debug_log' && is_array( $value ) ) {
				foreach ( $value as $k2 => &$v2 ) {
					foreach ( $v2 as $key_log => &$value_log ) {
						if ( $key_log === 'count' ) {
							$value_log = (int) $value_log;

							if ( $value_log < 50 ) {
								// No-op. Exact match for counts below 50.
							} elseif ( $value_log < 100 ) {
								if ( $value_log % 5 === 0 ) {
									echo "Skipping normalization as it's already divisible by 5\n";
								} else {
									echo "Normalizing debug_log.count from $value_log to ";
									$value_log = round( $value_log / 5 ) * 5;  // Round to the closest 5 if not already divisible by 5.
									echo "$value_log\n";
								}
							} else {
								if ( $value_log % 10 === 0 ) {
									echo "Skipping normalization as it's already divisible by 10\n";
								} else {
									echo "Normalizing debug_log.count from $value_log to ";
									$value_log = round( $value_log / 10 ) * 10;  // Round to the closest 10 if not already divisible by 10.
									echo "$value_log\n";
								}
							}
						}

						if ( $key_log === 'message' ) {
							// Sometimes the test site might fail to contact WP.org, this is beyond our control.
							if ( stripos( $value_log, 'Something may be wrong with WordPress.org' ) !== false ) {
								// If it happens only a few times, ignore it.
								if ( $value['count'] <= 3 ) {
									echo "Removing 'Something may be wrong with WordPress.org' from debug_log.message\n";
									unset( $human_friendly_test_result[ $key ][ $key_log ] );
								}
							}
						}
					}
				}
			}
		}
	}

	return json_encode( $human_friendly_test_result, JSON_PRETTY_PRINT );
}

