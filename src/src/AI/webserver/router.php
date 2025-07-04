<?php
/**
 * QIT Node Router
 *
 * This file is a template that will be copied to temp directory
 * Placeholders will be replaced during runtime:
 * {{NODE_TOKEN}} - The node authentication token
 * {{LOG_FILE}} - The log file path
 * {{PROVIDER}} - The LLM provider
 * {{PROVIDER_CONFIG}} - The provider configuration JSON
 */

header( 'Content-Type: application/json' );

// Configure PHP error logging
ini_set( 'log_errors', 1 );
ini_set( 'error_log', '{{LOG_FILE}}' );
ini_set( 'display_errors', 0 );

// Simple SPL Autoloader for our classes
spl_autoload_register( function ( $class ) {
	// Only handle our namespace
	$prefix     = 'QIT_AI_Webserver\\';
	$prefix_len = strlen( $prefix );

	// Check if the class uses our namespace
	if ( strncmp( $prefix, $class, $prefix_len ) !== 0 ) {
		// Not our namespace, let other autoloaders handle it
		return;
	}

	// Get the relative class name
	$relative_class = substr( $class, $prefix_len );

	// Convert namespace separators to directory separators
	$relative_path = str_replace( '\\', '/', $relative_class );

	// Build the file path
	$file = __DIR__ . '/' . $relative_path . '.php';

	// If the file exists, require it
	if ( file_exists( $file ) ) {
		require_once $file;

		return;
	}

	// Also check if it's directly in the root (for backward compatibility)
	$filename  = basename( $relative_path );
	$root_file = __DIR__ . '/' . $filename . '.php';

	if ( file_exists( $root_file ) ) {
		require_once $root_file;

		return;
	}

	// Log if class file not found (for debugging)
	error_log( "[QIT Autoloader] Failed to load class: $class (tried: $file and $root_file)" );
} );

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
			'context_keys'                  => array_keys( $input['execution_context'] ),
			'has_symbol'                    => isset( $input['execution_context']['symbol'] ),
			'has_public_access'             => isset( $input['execution_context']['has_public_access'] ),
			'has_privilege_escalation_risk' => isset( $input['execution_context']['susceptible_to_privilege_escalation'] ),
			'has_wordpress_hooks'           => isset( $input['execution_context']['wordpress_hooks'] ),
			'has_entry_points'              => isset( $input['execution_context']['entry_points'] )
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

// Initialize NodeResponse for performance tracking
use QIT_AI_Webserver\NodeResponse;

NodeResponse::init();

// Boot LLPhant with provider config and per-request options
$provider       = '{{PROVIDER}}';
$providerConfig = json_decode( '{{PROVIDER_CONFIG}}', true ) + [
		'model'       => $input['model'] ?? null,
		'temperature' => $input['temperature'] ?? null,
		'max_tokens'  => $input['max_tokens'] ?? null,
	];

\QIT_AI_Webserver\Lib\LLPhantBootstrap::boot( $provider, $providerConfig );

// Route to appropriate endpoint
log_info( "Routing request", [ 'uri' => $uri, 'method' => $method ] );

// The autoloader will handle loading these classes automatically
// Just need to require helpers.php as it contains functions, not classes
require_once __DIR__ . '/Handlers/helpers.php';

use QIT_AI_Webserver\Endpoints\BasicPromptEndpoint;
use QIT_AI_Webserver\Endpoints\PromptWithToolsEndpoint;
use QIT_AI_Webserver\Endpoints\ZipExtractionEndpoint;
use QIT_AI_Webserver\Endpoints\FileReadingEndpoint;
use QIT_AI_Webserver\Endpoints\VulnerabilityScanEndpoint;

/* Endpoints no longer need provider/config arguments */
$endpoints = [
	new BasicPromptEndpoint(),
	new ZipExtractionEndpoint(),
	new PromptWithToolsEndpoint(),
	new FileReadingEndpoint(),
	new VulnerabilityScanEndpoint()
];

// Build route map from endpoints
$routeMap = [];
foreach ( $endpoints as $endpoint ) {
	$route              = $endpoint->get_route();
	$routeMap[ $route ] = $endpoint;
}

// Route to appropriate endpoint
if ( isset( $routeMap[ $uri ] ) ) {
	$endpoint      = $routeMap[ $uri ];
	$endpointClass = get_class( $endpoint );
	log_info( "Handling request with endpoint", [ 'endpoint' => $endpointClass, 'route' => $uri ] );
	$endpoint->handle( $input );
} else {
	log_warning( "Route not found", [ 'uri' => $uri ] );
	http_response_code( 404 );
	echo json_encode( [ 'error' => "Route $uri not found on Node." ] );
	exit;
}

// Cleanup old sessions periodically (1% chance)
if ( mt_rand( 1, 100 ) === 1 ) {
	log_info( "Running periodic cleanup of old sessions (1% chance)" );
	cleanup_old_sessions();
}
