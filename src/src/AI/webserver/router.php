<?php
/**
 * QIT Node Router
 *
 * This file is a template that will be copied to temp directory
 * Placeholders will be replaced during runtime:
 * {{NODE_TOKEN}} - The node authentication token
 * {{OLLAMA_API_URL}} - The Ollama API base URL
 * {{LOG_FILE}} - The log file path
 */

header( 'Content-Type: application/json' );

// Configure PHP error logging
ini_set( 'log_errors', 1 );
ini_set( 'error_log', '{{LOG_FILE}}' );
ini_set( 'display_errors', 0 );

// Include the ToolRegistry class
require_once __DIR__ . '/lib/ToolRegistry.php';

use QIT_CLI\AI\WebServer\ToolRegistry;

// Configure logging
$router_log_file = '{{LOG_FILE}}';

// Enhanced logging functions
function log_message( $level, $message, $context = [] ) {
	$timestamp         = date( 'Y-m-d H:i:s' );
	$formatted_message = "[$timestamp] [$level] [Router] $message";

	// Add context if available
	if ( ! empty( $context ) ) {
		$formatted_message .= " " . json_encode( $context, JSON_UNESCAPED_SLASHES );
	}

	// Write to log file
	global $router_log_file;
	file_put_contents( $router_log_file, $formatted_message . PHP_EOL, FILE_APPEND );

	// Also write to error_log for system logging
	error_log( "[QIT Node Router] $formatted_message" );
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

// Function to ensure model is available
function ensure_model_available( $model, $ollama_api_url ) {
	log_info( "Checking if model is available", [ 'model' => $model ] );

	// Check if model exists by trying to show it
	$ch = curl_init( $ollama_api_url . '/api/show' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( [ 'model' => $model ] ) );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ] );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );

	$response  = curl_exec( $ch );
	$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );

	if ( $http_code === 200 ) {
		log_info( "Model already exists", [ 'model' => $model ] );

		return true;
	}

	// Model doesn't exist, try to pull it
	log_info( "Model not found, attempting to pull", [ 'model' => $model ] );

	$ch = curl_init( $ollama_api_url . '/api/pull' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( [ 'model' => $model ] ) );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ] );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 1800 ); // 30 minutes timeout for pulling

	$response  = curl_exec( $ch );
	$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$error     = curl_error( $ch );
	curl_close( $ch );

	if ( $http_code !== 200 ) {
		log_error( "Failed to pull model", [
			'model'     => $model,
			'http_code' => $http_code,
			'error'     => $error,
			'response'  => substr( $response, 0, 500 )
		] );

		return false;
	}

	log_info( "Model pulled successfully", [ 'model' => $model ] );

	return true;
}

// Route handling
$uri    = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Log request details
$request_body = file_get_contents( 'php://input' );
$headers      = [];
foreach ( $_SERVER as $name => $value ) {
	if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
		$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
	}
}

log_info( "Received $method request to $uri", [
	'headers'     => $headers,
	'body_size'   => strlen( $request_body ),
	'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
] );

// Only accept POST requests
if ( $method !== 'POST' ) {
	log_warning( "Method not allowed: $method", [ 'uri' => $uri ] );
	http_response_code( 405 );
	echo json_encode( [ 'error' => 'Method not allowed' ] );
	exit;
}

// Validate node token for all routes
if ( ! isset( $headers['X-Node-Token'] ) ) {
	log_error( "Missing token", [ 'uri' => $uri, 'method' => $method ] );
	http_response_code( 403 );
	echo json_encode( [ 'error' => 'Unauthorized - missing token' ] );
	exit;
}

if ( $headers['X-Node-Token'] !== '{{NODE_TOKEN}}' ) {
	log_error( "Invalid token provided", [
		'uri'            => $uri,
		'method'         => $method,
		'provided_token' => substr( $headers['X-Node-Token'], 0, 8 ) . '...'
	] );
	http_response_code( 403 );
	echo json_encode( [ 'error' => 'Unauthorized - invalid token' ] );
	exit;
}

// Rate limiting
$rate_limit_key  = 'analyze_code_' . md5( $headers['X-Node-Token'] ?? '' );
$rate_limit_file = sys_get_temp_dir() . '/' . $rate_limit_key;

if ( file_exists( $rate_limit_file ) ) {
	$last_request    = filemtime( $rate_limit_file );
	$time_since_last = time() - $last_request;

	log_debug( "Rate limit check", [
		'time_since_last_request' => $time_since_last,
		'rate_limit_key'          => $rate_limit_key
	] );

	if ( $time_since_last < 0.1 ) { // 1 request per second
		log_warning( "Rate limit exceeded", [
			'uri'             => $uri,
			'method'          => $method,
			'time_since_last' => $time_since_last
		] );
		http_response_code( 429 );
		echo json_encode( [ 'error' => 'Rate limit exceeded' ] );
		exit;
	}
}
touch( $rate_limit_file );

log_debug( "Rate limit passed", [ 'uri' => $uri ] );

// Get JSON input
$input = json_decode( file_get_contents( 'php://input' ), true );

// Log input with appropriate level and sanitization
if ( $input ) {
	// Create a sanitized copy for logging (remove potentially sensitive data)
	$log_input = $input;
	if ( isset( $log_input['prompt'] ) ) {
		$log_input['prompt'] = substr( $log_input['prompt'], 0, 100 ) . ( strlen( $log_input['prompt'] ) > 100 ? '...' : '' );
	}

	log_info( "Received input for $uri", $log_input );

	// Debug execution context availability
	if ( isset( $input['execution_context'] ) ) {
		log_debug( "Execution context provided", [
			'context_keys'        => array_keys( $input['execution_context'] ),
			'has_symbol'          => isset( $input['execution_context']['symbol'] ),
			'has_public_access'   => isset( $input['execution_context']['has_public_access'] ),
			'has_privilege_escalation_risk' => isset( $input['execution_context']['susceptible_to_privilege_escalation'] ),
			'has_wordpress_hooks' => isset( $input['execution_context']['wordpress_hooks'] ),
			'has_entry_points'    => isset( $input['execution_context']['entry_points'] )
		] );
	} else {
		log_warning( "No execution context in request", [
			'uri'            => $uri,
			'input_keys'     => array_keys( $input ),
			'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
		] );
	}
} else {
	log_warning( "Invalid JSON input received", [
		'uri'      => $uri,
		'raw_size' => strlen( file_get_contents( 'php://input' ) )
	] );
}

// Route to appropriate handler
log_info( "Routing request", [ 'uri' => $uri, 'method' => $method ] );

// Include handler files
require_once __DIR__ . '/handlers/ai_process.php';
require_once __DIR__ . '/handlers/ai_tools.php';
require_once __DIR__ . '/handlers/code_analysis.php';
require_once __DIR__ . '/handlers/zip_extraction.php';
require_once __DIR__ . '/handlers/file_reader.php';
require_once __DIR__ . '/handlers/helpers.php';

switch ( $uri ) {
	case '/process':
		log_info( "Handling AI process request" );
		handle_ai_process( $input, '{{OLLAMA_API_URL}}' );
		break;

	case '/analyze-code':
		log_info( "Handling code analysis request" );
		handle_code_analysis( $input );
		break;

	case '/extract-zip':
		log_info( "Handling ZIP extraction request" );
		handle_zip_extraction( $input );
		break;

	case '/read-file':
		log_info( "Handling file reading request" );
		handle_file_reading( $input );
		break;

	default:
		log_warning( "Route not found", [ 'uri' => $uri ] );
		http_response_code( 404 );
		echo json_encode( [ 'error' => 'Not found' ] );
		exit;
}

// Cleanup old sessions periodically (1% chance)
if ( mt_rand( 1, 100 ) === 1 ) {
	log_info( "Running periodic cleanup of old sessions (1% chance)" );
	cleanup_old_sessions();
}
