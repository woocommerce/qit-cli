<?php

function test_result_parser( string $json ): string {
	// Decode the original JSON
	$data = json_decode( $json, true );

	// Initialize an array to store the output JSONs
	$human_friendly_test_result = [];

	// Iterate over the top-level items and extract nested JSON strings
	foreach ( $data as $key => $value ) {
		if ( is_string( $value ) ) {
			$decodedValue = json_decode( $value, true );
			if ( json_last_error() == JSON_ERROR_NONE && is_array( $decodedValue ) ) {
				// Add the extracted item as a separate top-level item in the output array
				$human_friendly_test_result[] = [ $key => $decodedValue ];
				// Replace the original value with an indicator that the JSON has been extracted
				$data[ "{$key}_extracted" ] = '{EXTRACTED}';

				unset( $data[ $key ] );
			}
		}
	}

	// Add the modified original JSON as the first item in the output array
	array_unshift( $human_friendly_test_result, $data );

	return json_encode( $human_friendly_test_result, JSON_PRETTY_PRINT );
}

