<?php

/**
 * helpers.php
 */

if ( empty( getenv( 'QIT_NODE_DIR' ) ) ) {
	throw new RuntimeException( 'QIT_NODE_DIR environment variable is not set.' );
}

if ( ! empty( getenv( 'QIT_LOG_FILE' ) ) ) {
	$LOG_FILE = getenv( 'QIT_LOG_FILE' );
} else {
	$LOG_FILE = getenv( 'QIT_NODE_DIR' ) . '/qit.log';
}

// Configure logging
$router_log_file = $LOG_FILE;

// Enhanced logging functions
function log_message( $level, $message, $context = [] ) {
	$timestamp         = date( 'Y-m-d H:i:s' );
	$formatted_message = "[$timestamp] [$level] [Router] $message";

	// Add context if available
	if ( ! empty( $context ) ) {
		$formatted_message .= ' ' . json_encode( $context, JSON_UNESCAPED_SLASHES );
	}

	// Write to log file only (removed duplication to error_log)
	global $router_log_file;
	file_put_contents( $router_log_file, $formatted_message . PHP_EOL, FILE_APPEND );
}

function log_debug( $message, $context = [] ) {
	log_message( 'debug', $message, $context );
}

function log_info( $message, $context = [] ) {
	log_message( 'info', $message, $context );
}

function log_warning( $message, $context = [] ) {
	log_message( 'warning', $message, $context );
}

function log_error( $message, $context = [] ) {
	log_message( 'error', $message, $context );
}
