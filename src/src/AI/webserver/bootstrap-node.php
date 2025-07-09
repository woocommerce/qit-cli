<?php
/*  bootstrap-node.php
 *  Common runtime bootstrap for all QIT node routers
 *  ------------------------------------------------- */

/**
 * Helper: fetch an env‑var or throw.
 */
function env_or_throw( string $name ): string {
	$value = getenv( $name );
	if ( $value === false || $value === '' ) {
		throw new RuntimeException( "Required env var {$name} is not set or empty" );
	}

	return $value;
}

# ── 1. runtime configuration (must be provided by NodeStartCommand) ─────
$NODE_TOKEN       = env_or_throw( 'QIT_NODE_TOKEN' );
$LOG_FILE         = env_or_throw( 'QIT_LOG_FILE' );
$NODE_DIR         = rtrim( env_or_throw( 'QIT_NODE_DIR' ), '/' ) . '/';
$AI_DIR           = rtrim( env_or_throw( 'QIT_AI_DIR' ), '/' ) . '/';
$PROVIDER         = env_or_throw( 'QIT_PROVIDER' );
$PROVIDER_CFG_RAW = env_or_throw( 'QIT_PROVIDER_CFG' );
$DB_PATH          = env_or_throw( 'QIT_DB_PATH' );
$PHP_ERR_LOG      = $NODE_DIR . 'php-errors.log';

$PROVIDER_CONFIG = json_decode( $PROVIDER_CFG_RAW, true );
if ( json_last_error() !== JSON_ERROR_NONE ) {
	throw new RuntimeException( 'QIT_PROVIDER_CFG is not valid JSON: ' . json_last_error_msg() );
}

# ── 2. set up PHP error log separate from router log ────────────────────

header( 'Content-Type: application/json' );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', $PHP_ERR_LOG );   // PHP notices/warnings
ini_set( 'display_errors', 0 );

define( 'QIT_NODE_DIR', $NODE_DIR );
define( 'QIT_AI_DIR', $AI_DIR );
define( 'QIT_DB_PATH', $DB_PATH );

# ── 3. autoloader + structured logging helpers ──────────────────────────
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
$router_log_file = $LOG_FILE;

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

# ── 4. request parsing, token check, rate‑limit, LLPhant bootstrap ──────
// Route handling
global $uri, $method;
$uri = $_SERVER['REQUEST_URI'];

// Convert "//extract-zip" to "/extract-zip" if it starts with double slashes
if ( strpos( $uri, '//' ) === 0 ) {
	$uri = '/' . ltrim( $uri, '/' );
}

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

// Validate node token for all routes
if ( ! isset( $headers['X-Node-Token'] ) ) {
	log_error( "Missing token", [ 'uri' => $uri, 'method' => $method ] );
	http_response_code( 403 );
	echo json_encode( [ 'error' => 'Unauthorized - missing token' ] );
	exit;
}

if ( $headers['X-Node-Token'] !== $NODE_TOKEN ) {
	log_error( "Invalid token provided", [
		'uri'            => $uri,
		'method'         => $method,
		'expected_token' => $NODE_TOKEN,
		'provided_token' => $headers['X-Node-Token'],
	] );
	http_response_code( 403 );
	echo json_encode( [ 'error' => 'Unauthorized - invalid token' ] );
	exit;
}

// Rate limiting
$rate_limit_key = sprintf(
    '%s_%s_%s',
    strtolower($method),
    trim(parse_url($uri, PHP_URL_PATH), '/'),   // run-one or process
    md5($headers['X-Node-Token'] ?? '')
);
// Store rate limit files in the node directory
$rateLimitDir = $NODE_DIR . 'rate-limits';
if ( ! is_dir( $rateLimitDir ) ) {
	mkdir( $rateLimitDir, 0700, true );
}
$rate_limit_file = $rateLimitDir . '/' . $rate_limit_key;

if ( file_exists( $rate_limit_file ) ) {
	$last_request    = filemtime( $rate_limit_file );
	$time_since_last = time() - $last_request;

	log_debug( "Rate limit check", [
		'time_since_last_request' => $time_since_last,
		'rate_limit_key'          => $rate_limit_key
	] );

	// Skip rate limiting for internal calls (from localhost)
	$is_internal = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || isset($headers['X-Internal-Call']));

	// Use different thresholds based on the endpoint
	$threshold = 0.005; // 5ms default threshold for most requests

	// For /run-one endpoint, use a more lenient threshold
	if (trim(parse_url($uri, PHP_URL_PATH), '/') === 'run-one') {
		$threshold = 0.02; // 20ms for worker's own limiter
	}

	if (!$is_internal && $time_since_last < $threshold) {
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

// Check for empty request body
if ( $request_body === '' ) {
    log_error( 'Empty request body' );
    http_response_code( 400 );
    echo json_encode( ['error' => 'Empty request body'] );
    exit;
}

// Get JSON input
$input = json_decode( $request_body, true );

// Check for JSON parsing errors
if ( json_last_error() !== JSON_ERROR_NONE ) {
    log_error( 'Malformed JSON', ['error' => json_last_error_msg()] );
    http_response_code( 400 );
    echo json_encode( ['error' => 'Malformed JSON: ' . json_last_error_msg()] );
    exit;
}

// Log input with appropriate level and sanitization
// Create a sanitized copy for logging (remove potentially sensitive data)
$log_input = $input;
if ( isset( $log_input['prompt'] ) ) {
	$log_input['prompt'] = substr( $log_input['prompt'], 0, 100 ) . ( strlen( $log_input['prompt'] ) > 100 ? '...' : '' );
}

log_info( "Received input for $uri", $log_input );

// Initialize NodeResponse for performance tracking
use QIT_AI_Webserver\NodeResponse;
use QIT_AI_Webserver\Persistence\SleekTaskRepository;

NodeResponse::init();

// Boot LLPhant with provider config
$provider       = $PROVIDER;
$providerConfig = $PROVIDER_CONFIG + [
		'temperature' => $input['temperature'] ?? null,
		'max_tokens'  => $input['max_tokens'] ?? null,
	];

\QIT_AI_Webserver\Lib\LLPhantBootstrap::boot( $provider, $providerConfig );

// Set model using the new unified method
if ( isset( $input['model'] ) ) {
	try {
		\QIT_AI_Webserver\Lib\LLPhantBootstrap::setModel( $input['model'], $provider );
	} catch ( \InvalidArgumentException $e ) {
		log_error( "Model setup failed", [
			'error'       => $e->getMessage(),
			'provider'    => $provider,
			'model_input' => $input['model']
		] );
		http_response_code( 400 );
		echo json_encode( [ 'error' => $e->getMessage() ] );
		exit;
	}
}

/* NOTE: helpers.php is still required only by router.worker.php */
