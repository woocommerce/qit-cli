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

	// Fields that contain test result data (may arrive as string or already-decoded array).
	$json_result_fields = [ 'test_result_json', 'ctrf_json', 'debug_log' ];

	// Iterate over the top-level items and extract nested JSON strings
	foreach ( $data as $key => $value ) {
		if ( in_array( $key, $remove_from_snapshot ) ) {
			unset( $data[ $key ] );
			continue;
		}
		// Handle already-decoded arrays (from CLI --json decoding).
		if ( is_array( $value ) && in_array( $key, $json_result_fields ) ) {
			$human_friendly_test_result[] = [ $key => $value ];
			$data[ "{$key}_extracted" ] = '{EXTRACTED}';
			unset( $data[ $key ] );
			continue;
		}
		// Handle stringified JSON (legacy format).
		if ( is_string( $value ) ) {
			$decodedValue = json_decode( $value, true );
			if ( json_last_error() == JSON_ERROR_NONE && is_array( $decodedValue ) ) {
				$human_friendly_test_result[] = [ $key => $decodedValue ];
				$data[ "{$key}_extracted" ] = '{EXTRACTED}';
				unset( $data[ $key ] );
			}
		}
	}

	// Add the modified original JSON as the first item in the output array
	array_unshift( $human_friendly_test_result, $data );

	return json_encode( $human_friendly_test_result, JSON_PRETTY_PRINT );
}

