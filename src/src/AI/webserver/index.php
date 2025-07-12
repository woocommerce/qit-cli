<?php
/**
 * QIT Node Webserver Index
 *
 * Simple status endpoint for the root URL
 */

header( 'Content-Type: application/json' );

echo json_encode( [
	"status"    => "QIT Node Active",
	"endpoints" => [
		"/basic-prompt"               => "Basic AI prompting endpoint",
		"/analyze-code"               => "Code analysis endpoint",
		"/extract-zip"                => "ZIP extraction endpoint",
		"/read-file"                  => "File content reading endpoint",
	],
	"version"   => "1.0.0"
] );
